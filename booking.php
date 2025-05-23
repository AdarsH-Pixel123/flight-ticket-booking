    <?php
    session_start();
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/functions.php';


    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }

    if (!isset($_GET['booking_id'])) {
        header('Location: airlines.php');
        exit();
    }

    $booking_id = $_GET['booking_id'];
    $user_id = $_SESSION['user_id'];

    // Get booking details
    $stmt = $pdo->prepare("SELECT b.*, f.*, al.airline_name, 
                        a1.airport_code as departure_code, a1.city as departure_city,
                        a2.airport_code as arrival_code, a2.city as arrival_city
                        FROM bookings b
                        JOIN flights f ON b.flight_id = f.flight_id
                        JOIN airlines al ON f.airline_id = al.airline_id
                        JOIN airports a1 ON f.departure_airport_id = a1.airport_id
                        JOIN airports a2 ON f.arrival_airport_id = a2.airport_id
                        WHERE b.booking_id = ? AND b.user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();

    if (!$booking) {
        die("Booking not found or you don't have permission to view it.");
    }

    // Get booked seats
    $stmt = $pdo->prepare("SELECT s.seat_number 
                        FROM booking_seats bs
                        JOIN seats s ON bs.seat_id = s.seat_id
                        WHERE bs.booking_id = ?");
    $stmt->execute([$booking_id]);
    $seats = $stmt->fetchAll(PDO::FETCH_COLUMN);
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Flight Booking - Booking Confirmation</title>
        <link rel="stylesheet" href="css/style.css">
        <style>
        /* Modern Booking Confirmation Styles */
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #34495e;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 25px;
        }
        
        header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        header h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        header p {
            margin: 15px 0 0;
            font-size: 1.1rem;
            color: rgba(255,255,255,0.9);
        }
        
        header a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            margin-left: 10px;
            transition: all 0.2s ease;
        }
        
        header a:hover {
            text-decoration: underline;
            opacity: 0.9;
        }
        
        .booking-confirmation {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }
        
        .booking-confirmation h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-top: 0;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .booking-confirmation h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 2px;
        }
        
        .success {
            background: #2ecc71;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(46, 204, 113, 0.2);
        }
        
        .booking-details {
            background: #f9fbfd;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .booking-details h3 {
            color: #3498db;
            font-size: 1.4rem;
            margin-top: 25px;
            margin-bottom: 15px;
            position: relative;
            padding-left: 15px;
        }
        
        .booking-details h3::before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            height: 20px;
            width: 5px;
            background: #3498db;
            border-radius: 3px;
        }
        
        .booking-details p {
            margin: 0 0 12px;
            font-size: 1.05rem;
        }
        
        .booking-details strong {
            color: #2c3e50;
            font-weight: 600;
            min-width: 150px;
            display: inline-block;
        }
        
        .actions {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 30px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            font-weight: 600;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(52, 152, 219, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:nth-child(2) {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
        }
        
        .btn:nth-child(2):hover {
            box-shadow: 0 6px 12px rgba(243, 156, 18, 0.4);
        }
        
        /* Ticket-like styling for print */
        @media print {
            body {
                background: white;
                padding: 20px;
            }
            
            header, .actions {
                display: none;
            }
            
            .booking-confirmation {
                box-shadow: none;
                padding: 0;
            }
            
            .success {
                background: none;
                color: #2ecc71;
                padding: 0;
                margin-bottom: 20px;
                font-size: 1.2rem;
                box-shadow: none;
            }
            
            .booking-details {
                border: 2px solid #34495e;
                padding: 30px;
                position: relative;
            }
            
            .booking-details::before {
                content: 'E-TICKET';
                position: absolute;
                top: 10px;
                right: 20px;
                font-size: 1.8rem;
                font-weight: bold;
                color: rgba(52, 152, 219, 0.2);
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }
            
            header {
                padding: 25px 20px;
            }
            
            header h1 {
                font-size: 1.8rem;
            }
            
            .booking-confirmation {
                padding: 25px;
            }
            
            .booking-details {
                padding: 20px;
            }
            
            .actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .booking-details p {
                font-size: 1rem;
            }
            
            .booking-details strong {
                min-width: 120px;
                display: block;
                margin-bottom: 5px;
            }
            
            .success {
                font-size: 1rem;
                padding: 12px 15px;
            }
        }
    </style>
    </head>
    <body>
        <div class="container">
            <header>
                <h1>Flight Booking System</h1>
                <p>Welcome, <?php echo $_SESSION['username']; ?>! <a href="logout.php">Logout</a></p>
            </header>
            
            <div class="booking-confirmation">
                <h2>Booking Confirmation</h2>
                <p class="success">Your booking has been confirmed! Here are the details:</p>
                
                <div class="booking-details">
                    <h3>Booking Information</h3>
                    <p><strong>Booking ID:</strong> <?php echo $booking['booking_id']; ?></p>
                    <p><strong>Booking Date:</strong> <?php echo date('M j, Y H:i', strtotime($booking['booking_date'])); ?></p>
                    <p><strong>Total Amount:</strong> ₹<?php echo number_format($booking['total_amount'], 2); ?></p>
                    <p><strong>Status:</strong> <?php echo $booking['payment_status']; ?></p>
                    
                    <h3>Flight Information</h3>
                    <p><strong>Airline:</strong> <?php echo $booking['airline_name']; ?></p>
                    <p><strong>Flight Number:</strong> <?php echo $booking['flight_number']; ?></p>
                    <p><strong>Route:</strong> <?php echo $booking['departure_city']; ?> (<?php echo $booking['departure_code']; ?>) → 
                                            <?php echo $booking['arrival_city']; ?> (<?php echo $booking['arrival_code']; ?>)</p>
                    <p><strong>Departure:</strong> <?php echo date('M j, Y H:i', strtotime($booking['departure_time'])); ?></p>
                    <p><strong>Arrival:</strong> <?php echo date('M j, Y H:i', strtotime($booking['arrival_time'])); ?></p>
                    <p><strong>Duration:</strong> <?php echo floor($booking['duration'] / 60) . 'h ' . ($booking['duration'] % 60) . 'm'; ?></p>
                    
                    <h3>Seats Booked</h3>
                    <p><?php echo implode(', ', $seats); ?></p>
                </div>
                
                <div class="actions">
                    <a href="airlines.php" class="btn">Book Another Flight</a>
                    <button onclick="window.print()" class="btn">Print Ticket</button>
                </div>
            </div>
        </div>
        <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    </body>
    </html>
