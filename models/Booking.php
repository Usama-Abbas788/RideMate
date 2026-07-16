<?php
require_once __DIR__ . '/../config/database.php';

class Booking {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Create a new booking — status defaults to 'pending'
     */
    public function create($ride_id, $passenger_id) {
        $stmt = $this->conn->prepare(
            "INSERT INTO bookings (ride_id, passenger_id, status) VALUES (?, ?, 'pending')"
        );
        $stmt->bind_param("ii", $ride_id, $passenger_id);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /**
     * Update booking status: pending | accepted | rejected | cancelled
     */
    public function updateStatus($id, $status) {
        $allowed = ['pending', 'accepted', 'rejected', 'cancelled'];
        if (!in_array($status, $allowed)) return false;

        $stmt = $this->conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /**
     * Get bookings for a specific ride (driver view)
     */
    public function getByRide($ride_id) {
        $stmt = $this->conn->prepare(
            "SELECT b.*, u.name as passenger_name, u.phone as passenger_phone, u.email as passenger_email
             FROM bookings b
             JOIN users u ON b.passenger_id = u.id
             WHERE b.ride_id = ?
             ORDER BY b.created_at DESC"
        );
        $stmt->bind_param("i", $ride_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get all bookings by a passenger
     */
    public function getByPassenger($passenger_id) {
        $stmt = $this->conn->prepare(
            "SELECT b.*, r.origin, r.destination, r.date, r.price, r.vehicle_type, r.seats,
                    u.name as driver_name, u.phone as driver_phone, u.email as driver_email
             FROM bookings b
             JOIN rides r ON b.ride_id = r.id
             JOIN users u ON r.driver_id = u.id
             WHERE b.passenger_id = ?
             ORDER BY b.created_at DESC"
        );
        $stmt->bind_param("i", $passenger_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if a passenger already booked a specific ride
     */
    public function alreadyBooked($ride_id, $passenger_id) {
        $stmt = $this->conn->prepare(
            "SELECT id FROM bookings WHERE ride_id = ? AND passenger_id = ? AND status != 'cancelled'"
        );
        $stmt->bind_param("ii", $ride_id, $passenger_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function getReportSummary($days) {
        $days = intval($days);
        $sql = "SELECT 
                COUNT(*) AS total_bookings, 
                SUM(CASE WHEN b.status = 'accepted' THEN 1 ELSE 0 END) AS accepted_bookings, 
                SUM(CASE WHEN b.status = 'accepted' THEN r.price ELSE 0 END) AS revenue 
             FROM bookings b 
             JOIN rides r ON b.ride_id = r.id 
             WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)";

        $result = $this->conn->query($sql);
        if (!$result) {
            return ['total_bookings' => 0, 'accepted_bookings' => 0, 'revenue' => 0];
        }
        $summary = $result->fetch_assoc();
        return [
            'total_bookings' => intval($summary['total_bookings'] ?? 0),
            'accepted_bookings' => intval($summary['accepted_bookings'] ?? 0),
            'revenue' => floatval($summary['revenue'] ?? 0),
        ];
    }

    /**
     * Get a single booking by ID
     */
    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT b.*, r.driver_id, r.origin, r.destination, r.date, r.price, r.vehicle_type,
                    u.name as passenger_name
             FROM bookings b
             JOIN rides r ON b.ride_id = r.id
             JOIN users u ON b.passenger_id = u.id
             WHERE b.id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getTotalCount() {
        $result = $this->conn->query("SELECT COUNT(*) as cnt FROM bookings");
        return $result->fetch_assoc()['cnt'];
    }

    public function getAll() {
        $result = $this->conn->query(
            "SELECT b.*, u.name as passenger_name, r.origin, r.destination, r.date
             FROM bookings b
             JOIN users u ON b.passenger_id = u.id
             JOIN rides r ON b.ride_id = r.id
             ORDER BY b.created_at DESC"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function countByStatus($status) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM bookings WHERE status = ?");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['cnt'];
    }
}
