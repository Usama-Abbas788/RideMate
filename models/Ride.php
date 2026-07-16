<?php
require_once __DIR__ . '/../config/database.php';

class Ride {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Create a new ride
     * Types: i=driver_id, s=origin, s=destination, s=date, i=seats, d=price, s=vehicle_type
     */
    public function create($driver_id, $origin, $destination, $date, $seats, $price, $vehicle_type) {
        $stmt = $this->conn->prepare(
            "INSERT INTO rides (driver_id, origin, destination, date, seats, price, vehicle_type)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssids", $driver_id, $origin, $destination, $date, $seats, $price, $vehicle_type);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function search($filters = []) {
        $sql = "SELECT r.*, u.name as driver_name
                FROM rides r
                JOIN users u ON r.driver_id = u.id
                WHERE 1=1";

        $params = [];
        $types  = "";

        if (!empty($filters['origin'])) {
            $sql    .= " AND r.origin LIKE ?";
            $params[] = '%' . $filters['origin'] . '%';
            $types  .= "s";
        }
        if (!empty($filters['destination'])) {
            $sql    .= " AND r.destination LIKE ?";
            $params[] = '%' . $filters['destination'] . '%';
            $types  .= "s";
        }
        if (!empty($filters['date'])) {
            $sql    .= " AND DATE(r.date) = ?";
            $params[] = $filters['date'];
            $types  .= "s";
        }
        if (!empty($filters['vehicle_type'])) {
            $sql    .= " AND r.vehicle_type = ?";
            $params[] = $filters['vehicle_type'];
            $types  .= "s";
        }

        $sql .= " ORDER BY r.date ASC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT r.*, u.name as driver_name, u.phone as driver_phone, u.email as driver_email
             FROM rides r JOIN users u ON r.driver_id = u.id WHERE r.id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByDriver($driver_id) {
        $stmt = $this->conn->prepare(
            "SELECT r.*,
             (SELECT COUNT(*) FROM bookings b WHERE b.ride_id = r.id AND b.status = 'accepted') as accepted_count,
             (SELECT COUNT(*) FROM bookings b WHERE b.ride_id = r.id AND b.status = 'pending')  as pending_count
             FROM rides r WHERE r.driver_id = ? ORDER BY r.date DESC"
        );
        $stmt->bind_param("i", $driver_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function decrementSeats($id) {
        $stmt = $this->conn->prepare("UPDATE rides SET seats = seats - 1 WHERE id = ? AND seats > 0");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function incrementSeats($id) {
        $stmt = $this->conn->prepare("UPDATE rides SET seats = seats + 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function delete($id, $driver_id) {
        $stmt = $this->conn->prepare("DELETE FROM rides WHERE id = ? AND driver_id = ?");
        $stmt->bind_param("ii", $id, $driver_id);
        return $stmt->execute();
    }

    public function getTotalCount() {
        $result = $this->conn->query("SELECT COUNT(*) as cnt FROM rides");
        return $result->fetch_assoc()['cnt'];
    }

    public function getAll() {
        $result = $this->conn->query(
            "SELECT r.*, u.name as driver_name
             FROM rides r JOIN users u ON r.driver_id = u.id
             ORDER BY r.created_at DESC"
        );
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
