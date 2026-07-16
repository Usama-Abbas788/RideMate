<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Ride.php';

class BookingController {
    private $bookingModel;
    private $rideModel;

    public function __construct($conn) {
        $this->bookingModel = new Booking($conn);
        $this->rideModel    = new Ride($conn);
    }

    public function book() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $ride_id      = intval($_POST['ride_id'] ?? 0);
        $passenger_id = $_SESSION['user_id'];

        // Prevent driver from booking own ride
        $ride = $this->rideModel->getById($ride_id);
        if (!$ride) {
            $_SESSION['error'] = 'Ride not found.';
            header('Location: /ridemate/views/rides/search.php');
            exit;
        }

        if ($_SESSION['user_role'] !== 'passenger') {
            $_SESSION['error'] = 'Only passengers can book rides.';
            header('Location: /ridemate/views/rides/detail.php?id=' . $ride_id);
            exit;
        }

        if ($ride['driver_id'] == $passenger_id) {
            $_SESSION['error'] = 'You cannot book your own ride.';
            header('Location: /ridemate/views/rides/detail.php?id=' . $ride_id);
            exit;
        }

        if ($ride['seats'] <= 0) {
            $_SESSION['error'] = 'No seats available.';
            header('Location: /ridemate/views/rides/detail.php?id=' . $ride_id);
            exit;
        }

        if ($this->bookingModel->alreadyBooked($ride_id, $passenger_id)) {
            $_SESSION['error'] = 'You have already booked this ride.';
            header('Location: /ridemate/views/rides/detail.php?id=' . $ride_id);
            exit;
        }

        $bookingId = $this->bookingModel->create($ride_id, $passenger_id);

        if ($bookingId) {
            $message = sprintf('%s booked your ride', $_SESSION['user_name']);
            createNotification($ride['driver_id'], $message, 'success');

            $_SESSION['success'] = 'Booking request sent! Waiting for driver approval.';
            header('Location: /ridemate/passenger/dashboard.php');
        } else {
            $_SESSION['error'] = 'Failed to book ride. Please try again.';
            header('Location: /ridemate/views/rides/detail.php?id=' . $ride_id);
        }
        exit;
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $booking_id = intval($_POST['booking_id'] ?? 0);
        $status     = trim($_POST['status']       ?? '');
        $driver_id  = $_SESSION['user_id'];

        $booking = $this->bookingModel->getById($booking_id);

        if (!$booking) {
            $_SESSION['error'] = 'Booking not found.';
            header('Location: /ridemate/driver/dashboard.php');
            exit;
        }

        // Verify the booking belongs to driver's ride
        if ($booking['driver_id'] != $driver_id) {
            $_SESSION['error'] = 'Unauthorized action.';
            header('Location: /ridemate/driver/dashboard.php');
            exit;
        }

        if ($this->bookingModel->updateStatus($booking_id, $status)) {
            if ($status === 'accepted') {
                $this->rideModel->decrementSeats($booking['ride_id']);
            } elseif ($status === 'rejected' && $booking['status'] === 'accepted') {
                $this->rideModel->incrementSeats($booking['ride_id']);
            }

            $message = sprintf('%s %s your booking', $_SESSION['user_name'], $status === 'accepted' ? 'accepted' : 'rejected');
            createNotification($booking['passenger_id'], $message, $status === 'accepted' ? 'success' : 'warning');

            $_SESSION['success'] = 'Booking status updated to ' . $status . '.';
        } else {
            $_SESSION['error'] = 'Failed to update status.';
        }

        header('Location: /ridemate/driver/dashboard.php');
        exit;
    }

    public function cancel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $booking_id   = intval($_POST['booking_id'] ?? 0);
        $passenger_id = $_SESSION['user_id'];

        $booking = $this->bookingModel->getById($booking_id);

        if (!$booking || $booking['passenger_id'] != $passenger_id) {
            $_SESSION['error'] = 'Unauthorized or booking not found.';
            header('Location: /ridemate/passenger/dashboard.php');
            exit;
        }

        if ($this->bookingModel->updateStatus($booking_id, 'cancelled')) {
            // Restore seat if booking was accepted
            if ($booking['status'] === 'accepted') {
                $this->rideModel->incrementSeats($booking['ride_id']);
            }
            $_SESSION['success'] = 'Booking cancelled successfully.';
        } else {
            $_SESSION['error'] = 'Failed to cancel booking.';
        }

        header('Location: /ridemate/passenger/dashboard.php');
        exit;
    }

    public function getPassengerBookings($passenger_id) {
        return $this->bookingModel->getByPassenger(intval($passenger_id));
    }

    public function getRideBookings($ride_id) {
        return $this->bookingModel->getByRide(intval($ride_id));
    }
}
