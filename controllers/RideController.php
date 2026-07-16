<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/User.php';

class RideController {
    private $rideModel;
    private $userModel;

    public function __construct($conn) {
        $this->rideModel = new Ride($conn);
        $this->userModel = new User($conn);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $origin       = trim($_POST['origin']       ?? '');
        $destination  = trim($_POST['destination']  ?? '');
        $date         = trim($_POST['date']         ?? '');
        $date         = str_replace('T', ' ', $date);
        $seats        = intval($_POST['seats']       ?? 0);
        $price        = floatval($_POST['price']     ?? 0);
        $vehicle_type = trim($_POST['vehicle_type'] ?? '');
        $driver_id    = $_SESSION['user_id'];

        $errors = [];
        if (empty($origin))      $errors[] = 'Origin is required.';
        if (empty($destination)) $errors[] = 'Destination is required.';
        if (empty($date))        $errors[] = 'Date & time is required.';
        if ($seats < 1)          $errors[] = 'Seats must be at least 1.';
        if ($price < 0)          $errors[] = 'Price must be a positive number.';
        if (!in_array($vehicle_type, ['car', 'motorbike'])) $errors[] = 'Invalid vehicle type.';

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: /ridemate/views/rides/create.php');
            exit;
        }

        $rideId = $this->rideModel->create($driver_id, $origin, $destination, $date, $seats, $price, $vehicle_type);

        if ($rideId) {
            $message = sprintf('%s posted a ride from %s to %s', $_SESSION['user_name'], $origin, $destination);
            $passengers = $this->userModel->getUsersByRole('passenger');
            foreach ($passengers as $passenger) {
                createNotification($passenger['id'], $message, 'info');
            }

            $_SESSION['success'] = 'Ride created successfully!';
            header('Location: /ridemate/driver/dashboard.php');
        } else {
            $_SESSION['error'] = 'Failed to create ride. Please try again.';
            header('Location: /ridemate/views/rides/create.php');
        }
        exit;
    }

    public function search() {
        $filters = [
            'origin'       => trim($_GET['origin']       ?? ''),
            'destination'  => trim($_GET['destination']  ?? ''),
            'date'         => trim($_GET['date']         ?? ''),
            'vehicle_type' => trim($_GET['vehicle_type'] ?? ''),
        ];
        // Remove empty filters
        $filters = array_filter($filters, fn($v) => $v !== '');
        return $this->rideModel->search($filters);
    }

    public function getDetail($id) {
        return $this->rideModel->getById(intval($id));
    }

    public function getDriverRides($driver_id) {
        return $this->rideModel->getByDriver(intval($driver_id));
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $ride_id   = intval($_POST['ride_id'] ?? 0);
        $driver_id = $_SESSION['user_id'];

        if ($this->rideModel->delete($ride_id, $driver_id)) {
            $_SESSION['success'] = 'Ride deleted successfully.';
        } else {
            $_SESSION['error'] = 'Could not delete ride.';
        }
        header('Location: /ridemate/driver/dashboard.php');
        exit;
    }
}
