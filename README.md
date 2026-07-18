# RideMate — University Ride Sharing System

Campus ride-sharing for students built with PHP MVC, MySQL, Bootstrap, and custom CSS.

## Design Colors
- `#050708` — Black
- `#ffffff` — White
- `#16a34a` — Green
- `#22c55e` — Green Accent

---

## Setup

### 1. Database
1. Open phpMyAdmin → `http://localhost/phpmyadmin`
2. Create database `ridemate`
3. Import `database.sql`

If you already have an older database, also import `migrate_otp.sql`.

### 2. Database config
Edit `config/database.php` if needed:
```php
$conn = new mysqli("localhost", "root", "", "ridemate");
```

### 3. Twilio (SMS OTP)
Set environment variables for production SMS:
- `TWILIO_ACCOUNT_SID`
- `TWILIO_AUTH_TOKEN`
- `TWILIO_FROM_PHONE`

Without Twilio, OTP still works in **dev mode**: the code is shown on the verify screen.

### 4. Run
Place the folder in `xampp/htdocs/ridemate`, start Apache + MySQL, visit:
`http://localhost/ridemate`

---

## Demo Accounts

Password for all: `password`

| Role      |                         Email/Password                    |
|-----------|---------------------------------------------------|
| Admin     | `admin@ridemate.com` / admin123                   |
| Driver    | `danishriasat792@gmail.com` / 1234567             |
| Passenger | `fa22-bcs-219@students.cuisahiwal.edu.pk`/ 123456 |

Phone format everywhere: **4 digits – 7 digits** (e.g. `0300-1234567`).

---

## Auth Flow (Phone OTP)

1. **Register** → phone + password (+ optional email for notifications) → SMS OTP → account activated
2. **Login** → phone + password → SMS OTP → session starts
3. **Forgot password** → phone → SMS OTP → set new password

Email is **not** used for login/verification. It is optional and only used for ride/booking notification emails (`config/mail.php`).

---

## Features

- Role-based dashboards (driver / passenger / admin)
- Post, search, and book rides
- Accept / reject / cancel bookings with seat updates
- In-app notifications + optional email notifications
- Admin stats and PDF export
