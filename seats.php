<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$query = "SELECT * FROM flights WHERE airline_id = ?";

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['flight_id'])) {
    header('Location: airlines.php');
    exit();
}

$flight_id = $_GET['flight_id'];
$flight = getFlightDetails($flight_id);
$seats = getAvailableSeats($flight_id);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_seats'])) {
    $selected_seats = $_POST['selected_seats'];
    $total_amount = count($selected_seats) * $flight['price'];
    
    $booking_id = createBooking($_SESSION['user_id'], $flight_id, $selected_seats, $total_amount);
    
    if ($booking_id) {
        header("Location: booking.php?booking_id=$booking_id");
        exit();
    } else {
        $error = "Failed to create booking. Please try again.";
    }
}

// Create an array of booked seat numbers for quick lookup
$booked_seats = array();
$stmt = $pdo->prepare("SELECT seat_number, is_booked FROM seats WHERE flight_id = ?");
$stmt->execute([$flight_id]);
$all_seats = $stmt->fetchAll();

foreach ($all_seats as $s) {
    if ($s['is_booked']) {
        $booked_seats[] = $s['seat_number'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking - Seat Selection</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    <style>
    /* Modern Seat Selection Styles */
    .container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 25px;
    }
    
    header {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    header h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    
    header p {
        margin: 10px 0 0;
        font-size: 1rem;
        color: rgba(255,255,255,0.9);
    }
    
    header a {
        color: white;
        text-decoration: none;
        font-weight: 500;
        margin-left: 10px;
    }
    
    header a:hover {
        text-decoration: underline;
    }
    
    h2 {
        color: #2c3e50;
        font-size: 1.6rem;
        margin-bottom: 15px;
    }
    
    .back-btn {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        text-decoration: none;
        border-radius: 30px;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(52, 152, 219, 0.2);
        margin-bottom: 30px;
    }
    
    .back-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
    }
    
    .flight-details {
        background: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-left: 5px solid #3498db;
    }
    
    .flight-details p {
        margin: 0 0 10px;
        font-size: 1.05rem;
        color: #34495e;
    }
    
    .flight-details p:last-child {
        margin-bottom: 0;
    }
    
    .flight-details strong {
        color: #2c3e50;
        font-weight: 600;
    }
    
    .error {
        background: #e74c3c;
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-weight: 500;
    }
    
    .seat-selection {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    
    .seat-selection h3 {
        margin-top: 0;
        color: #2c3e50;
        font-size: 1.4rem;
    }
    
    .seat-legend {
        display: flex;
        gap: 30px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }
    
    .seat-legend div {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        color: #7f8c8d;
    }
    
    .seat {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        line-height: 1;
        text-align: center;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .seat.available {
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: white;
    }
    
    .seat.booked {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    .seat.selected {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
    }
    
    input[type="checkbox"] {
        position: absolute;
        opacity: 0;
    }
    
    input[type="checkbox"]:focus-visible + .seat {
        outline: 2px solid #f39c12;
        outline-offset: 2px;
    }
    
    .form-group {
        text-align: center;
        margin-top: 30px;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 30px;
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
        text-decoration: none;
        border-radius: 30px;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        font-weight: 600;
        font-size: 1.1rem;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 8px rgba(243, 156, 18, 0.3);
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(243, 156, 18, 0.4);
        background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    }
    
    .btn:active {
        transform: translateY(0);
    }
    
    /* Aircraft Layout Styles */
    .aircraft-layout {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 25px;
        margin: 30px 0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .cabin-section {
        margin-bottom: 30px;
    }
    
    .business-class {
        background: rgba(41, 128, 185, 0.05);
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #2980b9;
    }
    
    .business-class h3 {
        color: #2980b9;
        margin-top: 0;
        font-size: 1.3rem;
        padding-bottom: 10px;
        border-bottom: 1px dashed #2980b9;
    }
    
    .economy-class {
        padding: 20px;
    }
    
    .class-divider {
        display: flex;
        align-items: center;
        margin: 20px 0;
        color: #7f8c8d;
        font-weight: 600;
    }
    
    .divider-line {
        flex: 1;
        height: 1px;
        background: #ddd;
    }
    
    .divider-text {
        padding: 0 15px;
    }
    
    .seat-row {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
        justify-content: center;
    }
    
    .aisle-gap {
        width: 40px;
    }
    
    .business-seat {
        background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%) !important;
    }
    
    .economy-seat.available {
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%) !important;
    }
    
    /* Aircraft visualization */
    .aircraft-visual {
        position: relative;
        height: 20px;
        background: #34495e;
        margin-bottom: 30px;
        border-radius: 30px 30px 0 0;
    }
    
    .aircraft-visual::before {
        content: "";
        position: absolute;
        top: -15px;
        left: 20%;
        width: 60%;
        height: 30px;
        background: #2c3e50;
        border-radius: 50% 50% 0 0;
    }
    
    .seat-container {
        position: relative;
        text-align: center;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container {
            padding: 0 15px;
        }
        
        .seat-row {
            gap: 5px;
        }
        
        .aisle-gap {
            width: 20px;
        }
        
        .business-class,
        .economy-class {
            padding: 15px;
        }
        
        .seat {
            width: 40px;
            height: 40px;
            font-size: 0.9rem;
        }
        
        header {
            padding: 20px;
        }
        
        header h1 {
            font-size: 1.6rem;
        }
    }
    
    @media (max-width: 480px) {
        .seat-legend {
            gap: 15px;
        }
        
        .flight-details {
            padding: 20px;
        }
        
        .seat-selection {
            padding: 20px;
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
        
        <h2>Seat Selection for Flight <?php echo $flight['flight_number']; ?></h2>
        <a href="flights.php?airline_id=<?php echo $flight['airline_id']; ?>" class="back-btn">← Back to Flights</a>
        
        <div class="flight-details">
            <p><strong>Airline:</strong> <?php echo $flight['airline_name']; ?> (<?php echo $flight['airline_code']; ?>)</p>
            <p><strong>Route:</strong> <?php echo $flight['departure_city']; ?> (<?php echo $flight['departure_code']; ?>) → 
                                      <?php echo $flight['arrival_city']; ?> (<?php echo $flight['arrival_code']; ?>)</p>
            <p><strong>Departure:</strong> <?php echo date('M j, Y H:i', strtotime($flight['departure_time'])); ?></p>
            <p><strong>Arrival:</strong> <?php echo date('M j, Y H:i', strtotime($flight['arrival_time'])); ?></p>
            <p><strong>Price per seat:</strong> ₹<?php echo number_format($flight['price'], 2); ?></p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="seats.php?flight_id=<?php echo $flight_id; ?>" method="post">
            <div class="seat-selection">
                <h3>Select Your Seats</h3>
                <div class="seat-legend">
                    <div><span class="seat available"></span> Available</div>
                    <div><span class="seat booked"></span> Booked</div>
                    <div><span class="seat business-seat"></span> Business Class</div>
                    <div><span class="seat" style="background:#3498db"></span> Selected</div>
                </div>
                
                <div class="aircraft-visual"></div>
                
                <div class="aircraft-layout">
                    <div class="cabin-section business-class">
                        <h3>Business Class</h3>
                        <div class="seat-map">
                            <?php
                            $business_rows = 3;
                            $business_cols = ['A', 'B', '', 'C', 'D']; // Empty string creates the aisle
                            
                            for ($i = 1; $i <= $business_rows; $i++): ?>
                                <div class="seat-row">
                                    <?php foreach ($business_cols as $col): 
                                        if ($col === '') {
                                            echo '<div class="aisle-gap"></div>';
                                            continue;
                                        }
                                        
                                        $seat_number = $i . $col;
                                        $is_booked = in_array($seat_number, $booked_seats);
                                        $seat_id = null;
                                        
                                        foreach ($seats as $seat) {
                                            if ($seat['seat_number'] == $seat_number) {
                                                $seat_id = $seat['seat_id'];
                                                break;
                                            }
                                        }
                                    ?>
                                    
                                    <div class="seat-container">
                                        <?php if (!$is_booked && $seat_id): ?>
                                            <input type="checkbox" 
                                                   id="seat_<?php echo $seat_id; ?>" 
                                                   name="selected_seats[]" 
                                                   value="<?php echo $seat_id; ?>">
                                            <label for="seat_<?php echo $seat_id; ?>" 
                                                   class="seat available business-seat">
                                                <?php echo $seat_number; ?>
                                            </label>
                                        <?php elseif ($is_booked): ?>
                                            <span class="seat booked business-seat">
                                                <?php echo $seat_number; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php endforeach; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="class-divider">
                        <div class="divider-line"></div>
                        <div class="divider-text">Economy Class</div>
                        <div class="divider-line"></div>
                    </div>
                    
                    <div class="cabin-section economy-class">
                        <div class="seat-map">
                            <?php
                            $economy_rows = 15;
                            $economy_cols = ['A', 'B', '', 'C', 'D', '', 'E', 'F']; // Two aisles
                            
                            for ($i = $business_rows + 1; $i <= $economy_rows + $business_rows; $i++): ?>
                                <div class="seat-row">
                                    <?php foreach ($economy_cols as $col): 
                                        if ($col === '') {
                                            echo '<div class="aisle-gap"></div>';
                                            continue;
                                        }
                                        
                                        $seat_number = $i . $col;
                                        $is_booked = in_array($seat_number, $booked_seats);
                                        $seat_id = null;
                                        
                                        foreach ($seats as $seat) {
                                            if ($seat['seat_number'] == $seat_number) {
                                                $seat_id = $seat['seat_id'];
                                                break;
                                            }
                                        }
                                    ?>
                                    
                                    <div class="seat-container">
                                        <?php if (!$is_booked && $seat_id): ?>
                                            <input type="checkbox" 
                                                   id="seat_<?php echo $seat_id; ?>" 
                                                   name="selected_seats[]" 
                                                   value="<?php echo $seat_id; ?>">
                                            <label for="seat_<?php echo $seat_id; ?>" 
                                                   class="seat available economy-seat">
                                                <?php echo $seat_number; ?>
                                            </label>
                                        <?php elseif ($is_booked): ?>
                                            <span class="seat booked economy-seat">
                                                <?php echo $seat_number; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php endforeach; ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">Continue to Checkout</button>
            </div>
        </form>
    </div>
    
    <script>
        // Add visual feedback for seat selection
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.nextElementSibling;
                if (this.checked) {
                    label.classList.add('selected');
                } else {
                    label.classList.remove('selected');
                }
            });
        });
    </script>
</body>
</html>
