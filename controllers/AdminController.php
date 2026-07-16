<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/Booking.php';

class AdminController {
    private $userModel;
    private $rideModel;
    private $bookingModel;

    public function __construct($conn) {
        $this->userModel = new User($conn);
        $this->rideModel = new Ride($conn);
        $this->bookingModel = new Booking($conn);
    }

    public function getDashboardData() {
        $weeklyReport  = $this->bookingModel->getReportSummary(7);
        $monthlyReport = $this->bookingModel->getReportSummary(30);

        return [
            'totalUsers' => count($this->userModel->getAllUsers()),
            'totalDrivers' => $this->userModel->countByRole('driver'),
            'totalPassengers' => $this->userModel->countByRole('passenger'),
            'totalRides' => $this->rideModel->getTotalCount(),
            'totalBookings' => $this->bookingModel->getTotalCount(),
            'pendingBookings' => $this->bookingModel->countByStatus('pending'),
            'weeklyReport' => $weeklyReport,
            'monthlyReport' => $monthlyReport,
            'allUsers' => $this->userModel->getAllUsers(),
            'allRides' => $this->rideModel->getAll(),
            'allBookings' => $this->bookingModel->getAll()
        ];
    }

    public function deleteUser($id) {
        return $this->userModel->deleteUser($id);
    }
}
