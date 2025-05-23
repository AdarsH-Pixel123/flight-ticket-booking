-- Create the database
CREATE DATABASE IF NOT EXISTS flight_booking;
USE flight_booking;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Airlines table
CREATE TABLE airlines (
    airline_id INT AUTO_INCREMENT PRIMARY KEY,
    airline_name VARCHAR(100) NOT NULL,
    airline_code VARCHAR(5) NOT NULL UNIQUE,
    logo_url VARCHAR(255)
);

-- Airports table
CREATE TABLE airports (
    airport_id INT AUTO_INCREMENT PRIMARY KEY,
    airport_code VARCHAR(5) NOT NULL UNIQUE,
    airport_name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL
);

-- Flights table
CREATE TABLE flights (
    flight_id INT AUTO_INCREMENT PRIMARY KEY,
    airline_id INT NOT NULL,
    flight_number VARCHAR(10) NOT NULL,
    departure_airport_id INT NOT NULL,
    arrival_airport_id INT NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    duration INT NOT NULL, -- in minutes
    price DECIMAL(10,2) NOT NULL,
    total_seats INT NOT NULL,
    FOREIGN KEY (airline_id) REFERENCES airlines(airline_id),
    FOREIGN KEY (departure_airport_id) REFERENCES airports(airport_id),
    FOREIGN KEY (arrival_airport_id) REFERENCES airports(airport_id)
);

-- Seats table
CREATE TABLE seats (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    flight_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    class VARCHAR(20) NOT NULL, -- Economy, Business, First
    is_booked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id)
);

-- Bookings table
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('Pending', 'Paid', 'Cancelled') DEFAULT 'Pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id)
);

-- Booking_Seats table (junction table for bookings and seats)
CREATE TABLE booking_seats (
    booking_seat_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    seat_id INT NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
    FOREIGN KEY (seat_id) REFERENCES seats(seat_id)
);

-- Insert sample data
INSERT INTO airlines (airline_name, airline_code) VALUES 
('Air India', 'AI'),
('IndiGo', '6E'),
('SpiceJet', 'SG'),
('Vistara', 'UK'),
('GoAir', 'G8');

INSERT INTO airports (airport_code, airport_name, city, country) VALUES
('DEL', 'Indira Gandhi International Airport', 'Delhi', 'India'),
('BOM', 'Chhatrapati Shivaji Maharaj International Airport', 'Mumbai', 'India'),
('BLR', 'Kempegowda International Airport', 'Bangalore', 'India'),
('HYD', 'Rajiv Gandhi International Airport', 'Hyderabad', 'India'),
('CCU', 'Netaji Subhas Chandra Bose International Airport', 'Kolkata', 'India');

-- Insert sample flights (you can add more)
INSERT INTO flights (airline_id, flight_number, departure_airport_id, arrival_airport_id, departure_time, arrival_time, duration, price, total_seats) VALUES
(1, 'AI101', 1, 2, '2023-06-15 08:00:00', '2023-06-15 10:00:00', 120, 5000.00, 180),
(2, '6E202', 1, 3, '2023-06-15 09:30:00', '2023-06-15 12:00:00', 150, 4500.00, 180),
(3, 'SG303', 2, 4, '2023-06-15 11:00:00', '2023-06-15 12:30:00', 90, 3500.00, 180),
(4, 'UK404', 3, 5, '2023-06-15 13:00:00', '2023-06-15 15:30:00', 150, 5500.00, 180),
(5, 'G8505', 4, 1, '2023-06-15 14:00:00', '2023-06-15 16:00:00', 120, 4000.00, 180);

-- Insert sample seats for flight 1 (repeat for other flights)
INSERT INTO seats (flight_id, seat_number, class) VALUES
(1, '1A', 'Business'), (1, '1B', 'Business'), (1, '1C', 'Business'), (1, '1D', 'Business'),
(1, '2A', 'Business'), (1, '2B', 'Business'), (1, '2C', 'Business'), (1, '2D', 'Business'),
(1, '3A', 'Economy'), (1, '3B', 'Economy'), (1, '3C', 'Economy'), (1, '3D', 'Economy'),
(1, '4A', 'Economy'), (1, '4B', 'Economy'), (1, '4C', 'Economy'), (1, '4D', 'Economy');
-- (Add more seats as needed)
