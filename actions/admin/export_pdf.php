<?php
/**
 * Admin PDF Report Export
 * Generates a downloadable PDF report for the admin (weekly or monthly).
 *
 * Usage: /ridemate/actions/admin/export_pdf.php?period=weekly
 *        /ridemate/actions/admin/export_pdf.php?period=monthly
 */

session_start();

// --- Auth Guard ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    die('Access Denied.');
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/fpdf/fpdf.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Ride.php';
require_once __DIR__ . '/../../models/Booking.php';

// --- Determine period ---
$period = trim($_GET['period'] ?? 'weekly');
if (!in_array($period, ['weekly', 'monthly'])) {
    $period = 'weekly';
}

$days  = $period === 'weekly' ? 7 : 30;
$label = $period === 'weekly' ? 'Weekly' : 'Monthly';
$dateFrom = date('Y-m-d', strtotime("-{$days} days"));
$dateTo   = date('Y-m-d');

// --- Fetch Data ---
$userModel    = new User($conn);
$rideModel    = new Ride($conn);
$bookingModel = new Booking($conn);

$totalUsers      = count($userModel->getAllUsers());
$totalDrivers    = $userModel->countByRole('driver');
$totalPassengers = $userModel->countByRole('passenger');
$totalRides      = $rideModel->getTotalCount();
$totalBookings   = $bookingModel->getTotalCount();
$pendingBookings = $bookingModel->countByStatus('pending');
$report          = $bookingModel->getReportSummary($days);

// Fetch recent bookings in the period
$recentBookings  = $bookingModel->getAll();
$periodBookings  = array_filter($recentBookings, function($b) use ($dateFrom) {
    return isset($b['created_at']) && date('Y-m-d', strtotime($b['created_at'])) >= $dateFrom;
});
$periodBookings  = array_values($periodBookings);

// Fetch recent rides in the period
$allRides        = $rideModel->getAll();
$periodRides     = array_filter($allRides, function($r) use ($dateFrom) {
    // rides table uses 'date' as the ride departure date
    return isset($r['date']) && date('Y-m-d', strtotime($r['date'])) >= $dateFrom;
});
$periodRides     = array_values($periodRides);

// --- Build PDF ---
class ReportPDF extends FPDF {
    protected $label;
    protected $dateFrom;
    protected $dateTo;

    function setMeta($label, $dateFrom, $dateTo) {
        $this->label    = $label;
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;
    }

    function Header() {
        // Header background strip
        $this->SetFillColor(16, 185, 129); // Green brand color
        $this->Rect(0, 0, 210, 28, 'F');

        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 7);
        $this->Cell(0, 12, 'RideMate - ' . $this->label . ' Report', 0, 1, 'L');

        $this->SetFont('Arial', '', 9);
        $this->SetXY(10, 18);
        $this->Cell(0, 6, 'Period: ' . $this->dateFrom . '  to  ' . $this->dateTo, 0, 1, 'L');

        $this->SetTextColor(0, 0, 0);
        $this->Ln(8);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 10, 'Generated on ' . date('d M Y, H:i') . '  |  Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function sectionTitle($title) {
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(240, 240, 240);
        $this->SetTextColor(30, 30, 30);
        $this->Cell(0, 8, $title, 0, 1, 'L', true);
        $this->Ln(2);
    }

    function statRow($label, $value, $alt = false) {
        $this->SetFont('Arial', '', 10);
        if ($alt) {
            $this->SetFillColor(248, 250, 252);
        } else {
            $this->SetFillColor(255, 255, 255);
        }
        $this->SetTextColor(60, 60, 60);
        $this->Cell(120, 7, '  ' . $label, 0, 0, 'L', true);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(16, 185, 129);
        $this->Cell(0, 7, $value, 0, 1, 'L', true);
    }

    function tableHeader($cols, $widths) {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(16, 185, 129);
        $this->SetTextColor(255, 255, 255);
        foreach ($cols as $i => $col) {
            $this->Cell($widths[$i], 7, $col, 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
    }

    function tableRow($cells, $widths, $alt = false) {
        $this->SetFont('Arial', '', 8);
        $this->SetFillColor($alt ? 248 : 255, $alt ? 250 : 255, $alt ? 252 : 255);
        $this->SetTextColor(50, 50, 50);
        foreach ($cells as $i => $cell) {
            $text = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $cell);
            $this->Cell($widths[$i], 6, $text, 1, 0, 'L', true);
        }
        $this->Ln();
    }
}

$pdf = new ReportPDF('P', 'mm', 'A4');
$pdf->setMeta($label, $dateFrom, $dateTo);
$pdf->AliasNbPages();
$pdf->AddPage();

// ── SECTION 1: Platform Summary ──
$pdf->sectionTitle('  Platform Overview (All Time)');
$pdf->Ln(2);
$pdf->statRow('Total Registered Users', $totalUsers, false);
$pdf->statRow('Total Drivers', $totalDrivers, true);
$pdf->statRow('Total Passengers', $totalPassengers, false);
$pdf->statRow('Total Rides Posted', $totalRides, true);
$pdf->statRow('Total Bookings', $totalBookings, false);
$pdf->statRow('Pending Bookings', $pendingBookings, true);
$pdf->Ln(6);

// ── SECTION 2: Period Report ──
$pdf->sectionTitle('  ' . $label . ' Summary (Last ' . $days . ' Days)');
$pdf->Ln(2);
$pdf->statRow('Bookings in Period', $report['total_bookings'] ?? 0, false);
$pdf->statRow('Accepted Bookings', $report['accepted_bookings'] ?? 0, true);
$rejected = ($report['total_bookings'] ?? 0) - ($report['accepted_bookings'] ?? 0);
$pdf->statRow('Other/Pending Bookings', $rejected >= 0 ? $rejected : 0, false);
$pdf->statRow('Estimated Revenue (PKR)', number_format($report['revenue'] ?? 0, 0), true);
$pdf->statRow('Rides Posted in Period', count($periodRides), false);
$pdf->Ln(6);

// ── SECTION 3: Recent Rides Table ──
if (count($periodRides) > 0) {
    $pdf->sectionTitle('  Rides Posted in This Period');
    $pdf->Ln(2);
    $cols   = ['#', 'From', 'To', 'Date', 'Seats', 'Price (PKR)', 'Type'];
    $widths = [10, 40, 40, 30, 16, 26, 22];
    $pdf->tableHeader($cols, $widths);
    foreach (array_slice($periodRides, 0, 30) as $i => $ride) {
        $pdf->tableRow([
            $ride['id'] ?? '-',
            mb_strimwidth($ride['origin'] ?? '-', 0, 20, '..'),
            mb_strimwidth($ride['destination'] ?? '-', 0, 20, '..'),
            isset($ride['date']) ? date('d M y', strtotime($ride['date'])) : '-',
            $ride['seats'] ?? '-',
            number_format($ride['price'] ?? 0, 0),
            ucfirst($ride['vehicle_type'] ?? '-'),
        ], $widths, $i % 2 === 1);
    }
    $pdf->Ln(6);
}

// ── SECTION 4: Recent Bookings Table ──
if (count($periodBookings) > 0) {
    $pdf->sectionTitle('  Bookings in This Period');
    $pdf->Ln(2);
    $cols   = ['#', 'Ride ID', 'Passenger', 'From', 'To', 'Status', 'Date'];
    $widths = [10, 18, 35, 30, 30, 22, 25];
    $pdf->tableHeader($cols, $widths);
    foreach (array_slice($periodBookings, 0, 30) as $i => $b) {
        $pdf->tableRow([
            $b['id'] ?? '-',
            $b['ride_id'] ?? '-',
            mb_strimwidth($b['passenger_name'] ?? '-', 0, 20, '..'),
            mb_strimwidth($b['origin'] ?? '-', 0, 18, '..'),
            mb_strimwidth($b['destination'] ?? '-', 0, 18, '..'),
            ucfirst($b['status'] ?? '-'),
            isset($b['created_at']) ? date('d M y', strtotime($b['created_at'])) : '-',
        ], $widths, $i % 2 === 1);
    }
}

// --- Output PDF ---
$filename = 'RideMate_' . $label . '_Report_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename);
exit;
