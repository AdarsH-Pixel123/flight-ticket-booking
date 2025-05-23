<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAirlines($searchTerm = null) {
    global $pdo;
    
    $sql = "SELECT * FROM airlines";
    $params = [];
    
    if (!empty($searchTerm)) {
        $sql .= " WHERE airline_name LIKE :search OR airline_code LIKE :search";
        $params[':search'] = '%' . $searchTerm . '%';
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFlightsByAirline($airline_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT f.*, a1.airport_code as departure_code, a1.city as departure_city, 
                          a2.airport_code as arrival_code, a2.city as arrival_city
                          FROM flights f
                          JOIN airports a1 ON f.departure_airport_id = a1.airport_id
                          JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
                          WHERE f.airline_id = ?");
    $stmt->execute([$airline_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFlightDetails($flight_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT f.*, al.airline_name, al.airline_code, 
                          a1.airport_code as departure_code, a1.airport_name as departure_airport, a1.city as departure_city,
                          a2.airport_code as arrival_code, a2.airport_name as arrival_airport, a2.city as arrival_city
                          FROM flights f
                          JOIN airlines al ON f.airline_id = al.airline_id
                          JOIN airports a1 ON f.departure_airport_id = a1.airport_id
                          JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
                          WHERE f.flight_id = ?");
    $stmt->execute([$flight_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAvailableSeats($flight_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM seats WHERE flight_id = ? AND is_booked = 0");
    $stmt->execute([$flight_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createBooking($user_id, $flight_id, $seat_ids, $total_amount) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Create booking record
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, flight_id, total_amount) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $flight_id, $total_amount]);
        $booking_id = $pdo->lastInsertId();
        
        // Mark seats as booked and create booking_seat records
        foreach ($seat_ids as $seat_id) {
            $stmt = $pdo->prepare("UPDATE seats SET is_booked = 1 WHERE seat_id = ?");
            $stmt->execute([$seat_id]);
            
            $stmt = $pdo->prepare("INSERT INTO booking_seats (booking_id, seat_id) VALUES (?, ?)");
            $stmt->execute([$booking_id, $seat_id]);
        }
        
        $pdo->commit();
        return $booking_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}
?>
