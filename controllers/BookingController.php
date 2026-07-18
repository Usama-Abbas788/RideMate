<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/User.php';

class BookingController {
    private $bookingModel;
    private $rideModel;
    private $userModel;

    public function __construct($conn) {
        $this->bookingModel = new Booking($conn);
        $this->rideModel    = new Ride($conn);
        $this->userModel    = new User($conn);
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

        if (strtotime($ride['date']) < time()) {
            $_SESSION['error'] = 'This ride has expired and cannot be booked.';
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
            $passenger = $this->userModel->findById($passenger_id);
            $driver    = $this->userModel->findById($ride['driver_id']);
            $message   = sprintf('%s requested to book your ride from %s to %s on %s',
                $passenger['name'], $ride['origin'], $ride['destination'], date('M j, Y g:i A', strtotime($ride['date']))
            );
            createNotification($ride['driver_id'], $message, 'success');

            $adminUsers = $this->userModel->getUsersByRole('admin');
            foreach ($adminUsers as $admin) {
                $adminMessage = sprintf(
                    '%s requested a booking for ride %s → %s on %s (%s, PKR %s, 1 seat requested) by %s (%s). Passenger contact: %s / %s.',
                    $passenger['name'], $ride['origin'], $ride['destination'], date('M j, Y g:i A', strtotime($ride['date'])),
                    ucfirst($ride['vehicle_type']), number_format($ride['price'], 0), $driver['name'], $driver['phone'],
                    $passenger['phone'], $passenger['email']
                );
                createNotification($admin['id'], $adminMessage, 'info');
            }

            if (!empty($driver['email'])) {
                $subject = 'RideMate booking request';
                $body = '<p>Hi ' . htmlspecialchars($driver['name']) . ',</p>' .
                        '<p>' . htmlspecialchars($passenger['name']) . ' has requested to book your ride from <strong>' .
                        htmlspecialchars($ride['origin']) . '</strong> to <strong>' . htmlspecialchars($ride['destination']) . '</strong> on <strong>' .
                        date('M j, Y g:i A', strtotime($ride['date'])) . '</strong>.</p>' .
                        '<p>Vehicle: ' . htmlspecialchars(ucfirst($ride['vehicle_type'])) . '</p>' .
                        '<p>Seats requested: 1<br>Price: PKR ' . number_format($ride['price'], 0) . '</p>' .
                        '<p>Passenger phone: ' . htmlspecialchars($passenger['phone']) . '<br>Passenger email: ' . htmlspecialchars($passenger['email']) . '</p>' .
                        '<p>Please review the booking in your driver dashboard.</p>';
                sendMail($driver['email'], $driver['name'], $subject, $body);
            }

            if (!empty($passenger['email'])) {
                $subject = 'RideMate booking request sent';
                $body = '<p>Hi ' . htmlspecialchars($passenger['name']) . ',</p>' .
                        '<p>Your booking request for the ride from <strong>' . htmlspecialchars($ride['origin']) . '</strong> to <strong>' .
                        htmlspecialchars($ride['destination']) . '</strong> on <strong>' . date('M j, Y g:i A', strtotime($ride['date'])) . '</strong> has been sent to the driver.</p>' .
                        '<p>Driver: ' . htmlspecialchars($driver['name']) . '<br>Driver phone: ' . htmlspecialchars($driver['phone']) . '</p>' .
                        '<p>Price: PKR ' . number_format($ride['price'], 0) . '</p>' .
                        '<p>You will be notified when the driver accepts or rejects your request.</p>';
                sendMail($passenger['email'], $passenger['name'], $subject, $body);
            }

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
        $user_id    = $_SESSION['user_id'];
        $role       = $_SESSION['user_role'] ?? '';

        $booking = $this->bookingModel->getById($booking_id);
        if (!$booking) {
            $_SESSION['error'] = 'Booking not found.';
            header('Location: /ridemate/' . ($role === 'driver' ? 'driver/dashboard.php' : 'passenger/dashboard.php'));
            exit;
        }

        $driverOwnsBooking = $booking['driver_id'] == $user_id;
        $passengerOwnsBooking = $booking['passenger_id'] == $user_id;

        if ($status === 'accepted' || $status === 'rejected') {
            if (!$driverOwnsBooking) {
                $_SESSION['error'] = 'Unauthorized action.';
                header('Location: /ridemate/driver/dashboard.php');
                exit;
            }
        }

        if ($status === 'closed') {
            if (!$driverOwnsBooking) {
                $_SESSION['error'] = 'Only the driver can mark rides completed.';
                header('Location: /ridemate/' . ($role === 'driver' ? 'driver/dashboard.php' : 'passenger/dashboard.php'));
                exit;
            }
            if ($booking['status'] !== 'accepted') {
                $_SESSION['error'] = 'Only accepted rides can be closed.';
                header('Location: /ridemate/' . ($role === 'driver' ? 'driver/dashboard.php' : 'passenger/dashboard.php'));
                exit;
            }
        }

        if (!$this->bookingModel->updateStatus($booking_id, $status)) {
            $_SESSION['error'] = 'Failed to update status.';
            header('Location: /ridemate/' . ($role === 'driver' ? 'driver/dashboard.php' : 'passenger/dashboard.php'));
            exit;
        }

        $passenger = $this->userModel->findById($booking['passenger_id']);
        $driver    = $this->userModel->findById($booking['driver_id']);
        $ride      = $this->rideModel->getById($booking['ride_id']);
        $rideLabel = sprintf('%s → %s on %s', $ride['origin'], $ride['destination'], date('M j, Y g:i A', strtotime($ride['date'])));

        if ($status === 'accepted') {
            $this->rideModel->decrementSeats($booking['ride_id']);
            createNotification($booking['passenger_id'], sprintf('%s accepted your booking for %s', $driver['name'], $rideLabel), 'success');
            createNotification($booking['driver_id'], sprintf('You accepted booking from %s for %s', $passenger['name'], $rideLabel), 'success');

            $subject = 'RideMate booking accepted';
            $body = '<p>Hi ' . htmlspecialchars($passenger['name']) . ',</p>' .
                    '<p>Your booking request for the ride from <strong>' . htmlspecialchars($ride['origin']) . '</strong> to <strong>' . htmlspecialchars($ride['destination']) . '</strong> on <strong>' . date('M j, Y g:i A', strtotime($ride['date'])) . '</strong> has been <strong>accepted</strong> by ' . htmlspecialchars($driver['name']) . '.</p>' .
                    '<p>Driver contact: ' . htmlspecialchars($driver['phone']) . '<br>Driver email: ' . htmlspecialchars($driver['email']) . '</p>' .
                    '<p>Ride details: ' . htmlspecialchars($rideLabel) . '</p>' .
                    '<p>Thank you for using RideMate.</p>';
            sendMail($passenger['email'], $passenger['name'], $subject, $body);

            $subject = 'RideMate booking accepted by you';
            $body = '<p>Hi ' . htmlspecialchars($driver['name']) . ',</p>' .
                    '<p>You accepted the booking from <strong>' . htmlspecialchars($passenger['name']) . '</strong> for the ride from <strong>' . htmlspecialchars($ride['origin']) . '</strong> to <strong>' . htmlspecialchars($ride['destination']) . '</strong> on <strong>' . date('M j, Y g:i A', strtotime($ride['date'])) . '</strong>.</p>' .
                    '<p>Passenger contact: ' . htmlspecialchars($passenger['phone']) . '<br>Passenger email: ' . htmlspecialchars($passenger['email']) . '</p>' .
                    '<p>Ride details: ' . htmlspecialchars($rideLabel) . '</p>' .
                    '<p>Thank you for using RideMate.</p>';
            sendMail($driver['email'], $driver['name'], $subject, $body);

            $_SESSION['success'] = 'Booking accepted.';
        } elseif ($status === 'rejected') {
            if ($booking['status'] === 'accepted') {
                $this->rideModel->incrementSeats($booking['ride_id']);
            }
            createNotification($booking['passenger_id'], sprintf('%s rejected your booking for %s', $driver['name'], $rideLabel), 'warning');
            createNotification($booking['driver_id'], sprintf('You rejected booking from %s for %s', $passenger['name'], $rideLabel), 'warning');

            $subject = 'RideMate booking rejected';
            $body = '<p>Hi ' . htmlspecialchars($passenger['name']) . ',</p>' .
                    '<p>Your booking request for the ride from <strong>' . htmlspecialchars($ride['origin']) . '</strong> to <strong>' . htmlspecialchars($ride['destination']) . '</strong> on <strong>' . date('M j, Y g:i A', strtotime($ride['date'])) . '</strong> has been <strong>rejected</strong> by ' . htmlspecialchars($driver['name']) . '.</p>' .
                    '<p>Driver contact: ' . htmlspecialchars($driver['phone']) . '<br>Driver email: ' . htmlspecialchars($driver['email']) . '</p>' .
                    '<p>Ride details: ' . htmlspecialchars($rideLabel) . '</p>' .
                    '<p>Thank you for using RideMate.</p>';
            sendMail($passenger['email'], $passenger['name'], $subject, $body);

            $subject = 'RideMate booking rejected by you';
            $body = '<p>Hi ' . htmlspecialchars($driver['name']) . ',</p>' .
                    '<p>You rejected the booking from <strong>' . htmlspecialchars($passenger['name']) . '</strong> for the ride from <strong>' . htmlspecialchars($ride['origin']) . '</strong> to <strong>' . htmlspecialchars($ride['destination']) . '</strong> on <strong>' . date('M j, Y g:i A', strtotime($ride['date'])) . '</strong>.</p>' .
                    '<p>Passenger contact: ' . htmlspecialchars($passenger['phone']) . '<br>Passenger email: ' . htmlspecialchars($passenger['email']) . '</p>' .
                    '<p>Ride details: ' . htmlspecialchars($rideLabel) . '</p>' .
                    '<p>Thank you for using RideMate.</p>';
            sendMail($driver['email'], $driver['name'], $subject, $body);

            $_SESSION['success'] = 'Booking rejected.';
        } elseif ($status === 'closed') {
            createNotification($booking['passenger_id'], sprintf('Ride %s was marked complete by %s', $rideLabel, $_SESSION['user_name']), 'success');
            createNotification($booking['driver_id'], sprintf('Ride %s was marked complete by %s', $rideLabel, $_SESSION['user_name']), 'success');

            $subject = 'RideMate ride completed';
            $body = '<p>Hi ' . htmlspecialchars($passenger['name']) . ',</p>' .
                    '<p>The ride from <strong>' . htmlspecialchars($ride['origin']) . '</strong> to <strong>' . htmlspecialchars($ride['destination']) . '</strong> on <strong>' . date('M j, Y g:i A', strtotime($ride['date'])) . '</strong> has been marked as <strong>completed</strong>.</p>' .
                    '<p>Driver: ' . htmlspecialchars($driver['name']) . ' (' . htmlspecialchars($driver['phone']) . ')<br>' .
                    'Passenger: ' . htmlspecialchars($passenger['name']) . ' (' . htmlspecialchars($passenger['phone']) . ')</p>' .
                    '<p>Thank you for using RideMate.</p>';
            sendMail($passenger['email'], $passenger['name'], $subject, $body);
            sendMail($driver['email'], $driver['name'], $subject, $body);

            $_SESSION['success'] = 'Ride marked as completed.';
        }

        header('Location: /ridemate/' . ($role === 'driver' ? 'driver/dashboard.php' : 'passenger/dashboard.php'));
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

            $driver    = $this->userModel->findById($booking['driver_id']);
            $passenger = $this->userModel->findById($passenger_id);
            $ride      = $this->rideModel->getById($booking['ride_id']);

            $message = sprintf('%s cancelled the booking for your ride from %s to %s on %s',
                $passenger['name'], $ride['origin'], $ride['destination'], date('M j, Y g:i A', strtotime($ride['date']))
            );
            createNotification($ride['driver_id'], $message, 'warning');

            $adminUsers = $this->userModel->getUsersByRole('admin');
            foreach ($adminUsers as $admin) {
                $adminMessage = sprintf(
                    '%s cancelled booking for ride %s → %s on %s posted by %s (%s).',
                    $passenger['name'], $ride['origin'], $ride['destination'], date('M j, Y g:i A', strtotime($ride['date'])),
                    $driver['name'], $driver['phone']
                );
                createNotification($admin['id'], $adminMessage, 'warning');
            }

            if (!empty($driver['email'])) {
                $subject = 'RideMate booking cancelled';
                $body = '<p>Hi ' . htmlspecialchars($driver['name']) . ',</p>' .
                        '<p>' . htmlspecialchars($passenger['name']) . ' has cancelled their booking for your ride from <strong>' .
                        htmlspecialchars($ride['origin']) . '</strong> to <strong>' . htmlspecialchars($ride['destination']) . '</strong> scheduled for <strong>' .
                        date('M j, Y g:i A', strtotime($ride['date'])) . '</strong>.</p>' .
                        '<p>Passenger phone: ' . htmlspecialchars($passenger['phone']) . '<br>Passenger email: ' . htmlspecialchars($passenger['email']) . '</p>';
                sendMail($driver['email'], $driver['name'], $subject, $body);
            }

            if (!empty($passenger['email'])) {
                $subject = 'RideMate cancellation confirmed';
                $body = '<p>Hi ' . htmlspecialchars($passenger['name']) . ',</p>' .
                        '<p>Your cancellation for the ride from <strong>' . htmlspecialchars($ride['origin']) . '</strong> to <strong>' .
                        htmlspecialchars($ride['destination']) . '</strong> on <strong>' . date('M j, Y g:i A', strtotime($ride['date'])) . '</strong> has been processed.</p>' .
                        '<p>Driver: ' . htmlspecialchars($driver['name']) . '<br>Driver phone: ' . htmlspecialchars($driver['phone']) . '</p>';
                sendMail($passenger['email'], $passenger['name'], $subject, $body);
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

    public function getPassengerBookingForRide($ride_id, $passenger_id) {
        return $this->bookingModel->getByRideAndPassenger(intval($ride_id), intval($passenger_id));
    }

    public function getRideBookings($ride_id) {
        return $this->bookingModel->getByRide(intval($ride_id));
    }
}
