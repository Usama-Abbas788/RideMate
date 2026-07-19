# RideMate — University Ride Sharing System

RideMate is a smart campus ride-sharing platform tailored for university students. It enables drivers (students offering rides) and passengers (students seeking rides) to connect, split travel costs, optimize their commutes, and reduce campus traffic.

Built with a custom **PHP MVC-style architecture**, **MySQL**, **Bootstrap 5**, and custom **CSS styling**, RideMate features a responsive UI with dark aesthetics, modern glassmorphism, and seamless transactional workflows.

---

## 🎨 Design System & Aesthetics
RideMate is styled with custom modern layouts utilizing clean typography (Outfit & Inter fonts) and a dark primary theme:
- **Primary Background**: `#050708` — Pitch Black
- **Surface Panels**: `#111318` — Slate Dark
- **Accents / Brand Color**: `#16a34a` — Emerald Green
- **Glow & Active States**: `#22c55e` — Light Green Accent
- **Interactive UI**: Custom micro-animations, hover scaling transitions, responsive layout systems.

---

## 🚀 Key Features

### 🔐 Authentication & Security Flow
- **Email Verification & Activation**:
  - Registration captures name, email, phone number, password, and role.
  - Generates a cryptographically secure token and stores registration temporarily in the `pending_registrations` table.
  - Sends an activation link via SMTP email. Upon clicking (15-min expiration), the profile is moved to `users` and marked as active.
- **Forgot & Reset Password**:
  - Secure link sent via SMTP. Allows password updates securely (15-min expiration).
- **Interactive Password Change**:
  - Users can change passwords directly within their dashboard profile settings.

### 🚗 Driver Dashboard & Management
- **Post a Ride**: Specify origin, destination, departure date/time, vehicle type (Car 🚗 / Motorbike 🏍️), total seats, and price per seat (PKR).
- **Manage Rides**: Review all posted rides, track passenger seat requests, and delete active rides if needed.
- **Booking Decisions**: Drivers can individually `Accept` or `Reject` pending booking requests, which updates seat capacity in real-time.
- **Close Ride**: Mark a completed ride as `Closed`.

### 🎒 Passenger Dashboard & Browsing
- **Search & Filter**: Browse rides by origin, destination, departure date, and vehicle type.
- **Request Bookings**: Instantly request seats on rides. Passenger/Driver are immediately notified via in-app alerts and transactional emails.
- **Booking Status History**: Track status updates: `Pending`, `Accepted`, `Rejected`, `Cancelled`, `Closed` (Completed), or `Expired`.
- **Cancel Bookings**: Cancel a booking at any point. If accepted, the seat is automatically returned to the driver's ride pool.

### 🔔 Integrated Notifications
- **In-App Alerts**: Tracks unread alerts for booking requests, status approvals, cancellations, and completed trips.
- **SMTP Emails**: Structured transactional emails sent via PHPMailer for registration activation, password resets, bookings, and cancellation updates.

### ⚙️ Admin Command Center
- **System Statistics**: Real-time counter of total users, drivers, passengers, active rides, total bookings, and pending approvals.
- **User Directory**: List all registered users and roles, with capabilities to delete inactive/violating profiles.
- **FPDF Reports**: Generate and export weekly/monthly summaries of bookings, rides, and generated platform traffic to a clean, downloadable PDF format.

---

## 📂 Directory & File Structure

```
RideMate/
│
├── actions/                         # HTTP Request Processors & Form Handlers
│   ├── admin/
│   │   └── export_pdf.php           # Admin weekly/monthly PDF report generator
│   ├── admin_delete_user.php        # Deletes users from the database (admin only)
│   ├── book_ride.php                # Processes booking requests from passengers
│   ├── cancel_booking.php           # Processes cancellations from passengers
│   ├── change_password.php          # Processes dashboard-initiated password changes
│   ├── create_ride.php              # Handles ride creation submissions (drivers only)
│   ├── delete_ride.php              # Deletes specific rides (drivers only)
│   ├── forgot_password.php          # Triggers SMTP reset password token generation
│   ├── login.php                    # Authenticates users and initiates sessions
│   ├── logout.php                   # Destroys active user sessions
│   ├── notification_mark_all.php    # Marks all user notifications as read
│   ├── notification_mark_read.php   # Marks a single notification as read
│   ├── register.php                 # Submits registration into pending validation
│   ├── resend_otp.php               # Refreshes OTP (legacy logic fallback)
│   ├── resend_verification.php      # Resends pending email verification link
│   ├── reset_password.php           # Updates password from forgot-password flow
│   ├── update_booking.php           # Updates booking status (Accept/Reject/Close)
│   ├── verify_otp.php               # Performs verification checks (legacy logic fallback)
│   └── verify_resend.php            # Initiates resending verification (legacy logic)
│
├── admin/                           # Admin Panel Front-end Pages
│   ├── dashboard.php                # Admin command panel (analytics, user list)
│   └── login.php                    # Admin login page
│
├── assets/                          # Static Frontend Assets
│   ├── css/
│   │   └── style.css                # Global stylesheet containing root variables
│   ├── images/
│   │   └── hero.png                 # Main landing page visual graphic
│   └── js/
│       └── main.js                  # Global scripts for validation, animations
│
├── config/                          # Configuration Parameters & Libraries
│   ├── database.php                 # Global MySQL database connection configurations
│   ├── db.php                       # Central DB wrapper mapping to database.php
│   ├── helpers.php                  # Global Helper functions (token, normalization, phone formats)
│   ├── mail.php                     # SMTP parameters & PHPMailer connection settings
│   └── twilio.php                   # SMS configuration settings (environment fallbacks)
│
├── controllers/                     # Application Business Logic (Controllers)
│   ├── AdminController.php          # Handles system-wide stats and administrative actions
│   ├── AuthController.php           # Handles authentication, signups, verification, and resets
│   ├── BookingController.php        # Controls booking status sequences and seat allocations
│   ├── NotificationController.php   # Directs in-app notification queries
│   └── RideController.php           # Manages search filters and ride postings
│
├── driver/                          # Driver Portal Front-end Pages
│   └── dashboard.php                # Driver view (manage rides, passenger requests)
│
├── models/                          # Object Relational Models (Database Queries)
│   ├── Booking.php                  # Booking database transactions & report details
│   ├── Notification.php             # Alerts and in-app message transactions
│   ├── Ride.php                     # Ride lookup, search queries, and capacity trackers
│   └── User.php                     # Handles credentials, tokens, active/pending users
│
├── passenger/                       # Passenger Portal Front-end Pages
│   └── dashboard.php                # Passenger dashboard (bookings, search integration)
│
├── uploads/                         # Dynamic File Upload Repositories
│   └── profile_images/              # Directory structure for profile asset images
│
├── vendor/                          # Third-party Vendor Packages (Composer/Manual)
│   ├── fpdf/                        # FPDF library for building report sheets
│   └── phpmailer/                   # PHPMailer library for SMTP execution
│
├── views/                           # Frontend HTML/PHP Presentation Templates
│   ├── auth/                        # Registration, login, validation forms
│   │   ├── change_password.php
│   │   ├── forgot_password.php
│   │   ├── forgot_password_status.php
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── resend_verification.php
│   │   ├── verify_otp.php
│   │   └── verify_status.php
│   ├── info/                        # Platform policy pages
│   │   ├── community_guidelines.php
│   │   ├── privacy_policy.php
│   │   ├── safety_tips.php
│   │   └── terms_of_service.php
│   ├── layouts/                     # Master templates
│   │   ├── footer.php
│   │   └── header.php
│   ├── rides/                       # Ride search, creation, and detail views
│   │   ├── create.php
│   │   ├── detail.php
│   │   └── search.php
│   └── notifications.php            # Dedicated page for viewing in-app alerts
│
├── index.php                        # Home Landing Page & Main Router Guard
├── reset-password.php               # Reset password input page
├── verify.php                       # Verification email callback verification script
├── tmp_check_notifications.php      # Dev tool: Verify notification delivery
├── tmp_inspect_schema.php           # Dev tool: Inspect user table schema
└── tmp_schema_fix.php               # Dev tool: Fix database table schemas
```

---

## 🗄️ Database Setup & SQL Schema

Create a database named `ridemate` in your local environment. Run the following SQL schema to construct all application tables, indexes, and relations:

```sql
CREATE DATABASE IF NOT EXISTS `ridemate` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ridemate`;

-- 1. Create Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `verification_token` VARCHAR(100) NULL DEFAULT NULL,
  `verification_expires` DATETIME NULL DEFAULT NULL,
  `reset_token` VARCHAR(100) NULL DEFAULT NULL,
  `reset_expires` DATETIME NULL DEFAULT NULL,
  `otp_code` VARCHAR(10) NULL DEFAULT NULL,
  `otp_expires` DATETIME NULL DEFAULT NULL,
  `otp_attempts` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create Pending Registrations Table
CREATE TABLE IF NOT EXISTS `pending_registrations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `verification_token` VARCHAR(100) NULL DEFAULT NULL,
  `verification_expires` DATETIME NULL DEFAULT NULL,
  `otp_code` VARCHAR(10) NULL DEFAULT NULL,
  `otp_expires` DATETIME NULL DEFAULT NULL,
  `otp_attempts` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Create Rides Table
CREATE TABLE IF NOT EXISTS `rides` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `driver_id` INT NOT NULL,
  `origin` VARCHAR(255) NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `date` DATETIME NOT NULL,
  `seats` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `vehicle_type` ENUM('car', 'motorbike') NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_rides_driver` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Create Bookings Table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ride_id` INT NOT NULL,
  `passenger_id` INT NOT NULL,
  `status` ENUM('pending', 'accepted', 'rejected', 'cancelled', 'closed') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_bookings_ride` FOREIGN KEY (`ride_id`) REFERENCES `rides` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bookings_passenger` FOREIGN KEY (`passenger_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Create Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `message` VARCHAR(255) NOT NULL,
  `type` ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 👥 Demo Accounts
You can use the pre-configured accounts below to log in and test different system flows:

| Role | Email Address | Password | Details / Permissions |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@ridemate.com` | `admin123` | System oversight, full analytics view, delete users, FPDF report exports. |
| **Driver** | `danishriasat792@gmail.com` | `1234567` | Post new rides, accept/reject booking requests, manage vehicle seats. |
| **Passenger** | `fa22-bcs-219@students.cuisahiwal.edu.pk` | `123456` | Search and book rides, cancel requests, track booking histories. |

> [!NOTE]
> All users must conform to the required phone number format: **`03001234567`** (Not more than 11 digits).

To insert these demo users directly into your database, run:
```sql
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `email_verified`) VALUES
(1, 'Admin User', 'admin@ridemate.com', '03001111111', '$2y$10$GIJd2fnDr2MDWqrWEA01Du3LVSAon1ipOLiZnfW2c8GToQEHVwX7q', 'admin', 1),
(2, 'Driver User', 'danishriasat792@gmail.com', '03002222222', '$2y$10$eO3MNmgPNX0cWlt4SroCaeyLz20hM7P/u/UqcJTSgceSM5N65j.Ry', 'driver', 1),
(3, 'Passenger User', 'fa22-bcs-219@students.cuisahiwal.edu.pk', '03003333333', '$2y$10$wYINnU/rKi2IQP9dhO9nKOo2tmwpi9u2ZeiLm2mpVfR3rNOkv/mGK', 'passenger', 1);
```

---

## ⚙️ Installation & Local Setup

### 1. Requirements
Ensure you have the following installed:
- [XAMPP](https://www.apachefriends.org/) (with PHP 7.4+ and MySQL)
- [Composer](https://getcomposer.org/) (for managing backend dependencies)

### 2. Setup Files
1. Move the `RideMate` directory inside your XAMPP root web directory:
   `C:\xampp\htdocs\RideMate`
2. Start the **Apache** and **MySQL** services inside the XAMPP Control Panel.

### 3. Setup MySQL Database
1. Open phpMyAdmin at `http://localhost/phpmyadmin`.
2. Create a new database named `ridemate`.
3. Import the SQL commands provided in the **Database Setup & SQL Schema** section above.

### 4. Configure Database Credentials
Open `config/database.php` and verify the settings:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ridemate');
```

### 5. Configure Transactional Emails (SMTP Settings)
To allow email notifications and email token verifications, configure SMTP details in `config/mail.php`:
```php
define('MAIL_USERNAME', 'danishriasat792@gmail.com'); // Replace with your Gmail address
define('MAIL_PASSWORD', 'lauz xvwa kvtt upjo');       // Replace with your Google App Password
define('MAIL_FROM_ADDRESS', 'danishriasat792@gmail.com');
```

### 6. Configure Twilio (Optional SMS Fallback)
If you want to configure Twilio SMS capabilities in production, set the following environment variables:
- `TWILIO_ACCOUNT_SID`
- `TWILIO_AUTH_TOKEN`
- `TWILIO_FROM_PHONE`

### 7. Run the Application
Access the landing page of the application via your browser:
`http://localhost/ridemate`
