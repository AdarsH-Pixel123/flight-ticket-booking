<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['airline_id'])) {
    header('Location: airlines.php');
    exit();
}

$airline_id = $_GET['airline_id'];
$flights = getFlightsByAirline($airline_id);
$airline = $pdo->query("SELECT airline_name FROM airlines WHERE airline_id = $airline_id")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking - Flights</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    <style>
    /* Modern Flight Listing Styles */
    .flight-container {
        max-width: 1400px;
        margin: 40px auto;
        padding: 0 25px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        padding: 25px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-left: 5px solid #3498db;
    }
    
    .page-header h1 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.8rem;
        font-weight: 700;
        letter-spacing: -0.5px;
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
    }
    
    .back-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
    }
    
    .back-btn:active {
        transform: translateY(0);
    }
    
    .flight-list {
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .flight-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .flight-table th {
        background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
        color: white;
        padding: 16px 20px;
        text-align: left;
        font-weight: 600;
        position: sticky;
        top: 0;
    }
    
    .flight-table th:first-child {
        border-top-left-radius: 12px;
    }
    
    .flight-table th:last-child {
        border-top-right-radius: 12px;
    }
    
    .flight-table td {
        padding: 16px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: background 0.2s ease;
    }
    
    .flight-table tr:last-child td {
        border-bottom: none;
    }
    
    .flight-table tr:hover td {
        background-color: #f8fafc;
    }
    
    .flight-table tr:nth-child(even) {
        background-color: #f9fbfd;
    }
    
    .btn-select {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        color: white;
        text-decoration: none;
        border-radius: 30px;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(46, 204, 113, 0.2);
        white-space: nowrap;
    }
    
    .btn-select:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(46, 204, 113, 0.3);
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    }
    
    .btn-select:active {
        transform: translateY(0);
    }
    
    .duration-cell {
        white-space: nowrap;
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #7f8c8d;
    }
    
    .price-cell {
        font-weight: 700;
        color: #2c3e50;
        font-size: 1.1rem;
    }
    
    .no-flights {
        padding: 40px 20px;
        text-align: center;
        color: #7f8c8d;
        background: #f9fbfd;
    }
    
    .no-flights p {
        font-size: 1.1rem;
        margin: 0;
    }
    
    .flight-number {
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #3498db;
    }
    
    .route-info {
        display: flex;
        flex-direction: column;
    }
    
    .route-main {
        font-weight: 500;
        margin-bottom: 4px;
    }
    
    .route-codes {
        font-size: 0.85rem;
        color: #7f8c8d;
    }
    
    .time-display {
        display: flex;
        flex-direction: column;
    }
    
    .time-date {
        font-size: 0.9rem;
        color: #7f8c8d;
    }
    
    .time-hour {
        font-weight: 500;
    }
    
    @media (max-width: 992px) {
        .flight-container {
            padding: 0 15px;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 20px;
        }
        
        .back-btn {
            margin-top: 15px;
        }
    }
    
    @media (max-width: 768px) {
        .flight-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            border-radius: 8px;
        }
        
        .flight-table th {
            padding: 12px 15px;
        }
        
        .flight-table td {
            padding: 12px 15px;
        }
        
        .btn-select {
            padding: 8px 15px;
        }
    }
    
    @media (max-width: 576px) {
        .page-header {
            padding: 15px;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .flight-container {
            margin: 20px auto;
        }
    }
</style>
</head>
<body>
    <div class="flight-container">
        <div class="page-header">
            <h1><?php echo $airline['airline_name']; ?> Flights</h1>
            <a href="airlines.php" class="back-btn">← Back to Airlines</a>
        </div>
        
        <div class="flight-list">
            <?php if (empty($flights)): ?>
                <div class="no-flights">
                    <p>No flights available for this airline.</p>
                </div>
            <?php else: ?>
                <table class="flight-table">
                    <thead>
                        <tr>
                            <th>Flight Number</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th class="duration-cell">Duration</th>
                            <th class="price-cell">Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flights as $flight): ?>
                            <tr>
                                <td><?php echo $flight['flight_number']; ?></td>
                                <td>
                                    <?php echo $flight['departure_city']; ?> 
                                    (<?php echo $flight['departure_code']; ?>) → 
                                    <?php echo $flight['arrival_city']; ?> 
                                    (<?php echo $flight['arrival_code']; ?>)
                                </td>
                                <td><?php echo date('M j, Y H:i', strtotime($flight['departure_time'])); ?></td>
                                <td><?php echo date('M j, Y H:i', strtotime($flight['arrival_time'])); ?></td>
                                <td class="duration-cell">
                                    <?php echo floor($flight['duration'] / 60) . 'h ' . ($flight['duration'] % 60) . 'm'; ?>
                                </td>
                                <td class="price-cell">₹<?php echo number_format($flight['price'], 2); ?></td>
                                <td>
                                    <a href="seats.php?flight_id=<?php echo $flight['flight_id']; ?>" class="btn-select">
                                        Select Seats
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
