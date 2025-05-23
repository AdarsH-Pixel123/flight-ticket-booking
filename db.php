<?php
$host = 'localhost';      // or '127.0.0.1'
$port = '3306';          // Your custom MySQL port
$dbname = 'flight_booking';
$username = 'root';      // or your MySQL username
$password = '';          // your MySQL password (empty if none)

$query = "SELECT * FROM flights WHERE airline_id = ?";
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>
