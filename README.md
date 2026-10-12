# Flight Ticket Booking System

A web-based flight booking application built with **PHP, MySQL, HTML, CSS, and JavaScript**. Users can create an account, browse airlines and their flights, choose available seats, and view a booking confirmation with ticket details.

> This is an academic project intended for local development and demonstration. It does not integrate with a live airline inventory or payment gateway.

## Features

- **User accounts:** Sign up, log in, and log out with session-based authentication.
- **Airline browsing:** View available airlines and search by airline name or code.
- **Flight listings:** Explore flights associated with an airline, including route, schedule, duration, and price.
- **Seat selection:** View seat availability and select seats for a flight.
- **Booking confirmation:** See booking details, route, departure and arrival times, selected seats, and total amount.
- **Printable ticket:** Use the browser's print dialog from the confirmation page.
- **Relational database:** MySQL tables connect users, airlines, airports, flights, seats, bookings, and booking-seat records.

## Tech Stack

| Layer | Technology |
| --- | --- |
| Frontend | HTML, CSS, JavaScript |
| Server-side | PHP |
| Database | MySQL |
| Database access | PHP PDO |
| Local development | XAMPP (or another PHP/MySQL environment) |

## Project Structure

```text
flight-ticket-booking/
├── airlines.php        # Airline listing and search
├── flights.php         # Flights for a selected airline
├── seats.php           # Seat selection and booking creation
├── booking.php         # Booking confirmation and printable ticket
├── login.php           # Login and account registration
├── logout.php          # Ends the current session
├── db.php              # PDO database connection settings
├── functions.php       # Shared database and booking helper functions
├── flight booking.sql  # Database schema and sample records
└── README.md
```

The PHP files currently reference shared files in a sibling `includes/` directory (for example, `../includes/db.php` and `../includes/functions.php`). Review those paths before running the application and make sure they point to the intended shared files in your local setup. The repository also contains a root-level `db.php`; configure the application so its database connection is loaded consistently.

## Prerequisites

- PHP with the **PDO MySQL** extension enabled
- MySQL or MariaDB
- A local web server such as Apache (included with XAMPP)
- A browser and a database administration tool such as phpMyAdmin (optional)

## Setup and Installation

### 1. Clone the repository

Open a terminal in your web-server directory (for example, `htdocs` in XAMPP) and run:

```bash
git clone https://github.com/AdarsH-Pixel123/flight-ticket-booking.git
```

Alternatively, download the repository as a ZIP and extract it into the web-server directory.

### 2. Start Apache and MySQL

In the XAMPP Control Panel, start **Apache** and **MySQL**. If your MySQL server uses a port other than `3306`, use that port in the database configuration.

### 3. Create and seed the database

1. Open phpMyAdmin, usually at `http://localhost/phpmyadmin`.
2. Select **Import** and choose the repository's `flight booking.sql` file, or open a MySQL console and execute that file.
3. The script creates a database named `flight_booking` and adds sample airlines, airports, flights, and a small set of seats.

The sample flight dates are historical, so update the sample data in the SQL file if you want to demonstrate upcoming flights.

### 4. Configure the database connection

Update the connection settings in the database configuration used by the application:

```php
$host = 'localhost';
$port = '3306';
$dbname = 'flight_booking';
$username = 'root';
$password = ''; // Set this to your local MySQL password
```

Use the correct credentials and port for your environment. Avoid committing production credentials or secrets to version control.

### 5. Check shared include paths

The page scripts include files using paths such as:

```php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
```

Make sure the referenced files exist at those locations and contain the shared connection, helper functions, and any configuration used by the pages (such as `$base_url`). If you intend to use the repository's root-level `db.php` and `functions.php`, align the `require_once` paths accordingly and avoid duplicate/conflicting definitions.

### 6. Run the application

With the project placed under your web-server document root, visit:

```text
http://localhost/flight-ticket-booking/login.php
```

If you use a different folder name or virtual host, adjust the URL to match your local setup. Register a new account on the login page, then browse airlines, select a flight, choose seats, and continue to the booking confirmation.

## Database Schema

The SQL file defines these tables:

| Table | Purpose |
| --- | --- |
| `users` | Registered users and password hashes |
| `airlines` | Airline names, codes, and optional logo URLs |
| `airports` | Airport codes, names, cities, and countries |
| `flights` | Flight numbers, routes, timings, durations, and prices |
| `seats` | Seat numbers, cabin class, and booking availability |
| `bookings` | Booking owner, flight, amount, date, and payment status |
| `booking_seats` | Links a booking to one or more selected seats |

The `booking_seats` table is a junction table that supports bookings containing multiple seats.

## Typical User Flow

1. Register an account or log in.
2. Browse or search the airline list.
3. Open an airline to see its flights.
4. Choose a flight and select available seats.
5. Submit the selection to create a booking.
6. Review the booking confirmation and print the ticket if needed.

## Notes and Limitations

- This project is designed for learning and local demonstrations, not production booking.
- The included SQL contains sample data only; it does not connect to airline systems or provide live flight availability.
- A real payment processor is not integrated. The payment status in the database is a sample status field.
- Before deployment, review the include paths and configuration, validate user input and seat ownership/availability on the server, and add appropriate security protections (including CSRF protection, session hardening, and production-safe error handling).
- The sample data inserts seats for only one flight; add seat records for other flights to make their seat maps usable.

## Future Improvements

- Add flight search by origin, destination, and travel date.
- Add booking history and booking cancellation.
- Improve concurrency handling so the same seat cannot be booked by two requests.
- Move configuration and database credentials into environment-specific settings.
- Add automated tests and deployment instructions.

## License

No license is currently specified in this repository. Unless a license is added, reuse and redistribution are subject to the repository owner's rights.
