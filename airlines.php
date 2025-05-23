<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get search term if submitted
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get airlines (filtered if search term exists)
$airlines = getAirlines($searchTerm);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking - Airlines</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
    <style>
   /* Enhanced Airline Card Styling with Modern UI */
.airline-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
    padding: 25px;
}

.airline-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(0,0,0,0.05);
    position: relative;
}

.airline-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.12);
}

.airline-card-header {
    background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
    color: white;
    padding: 20px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.airline-card-header::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
    transform: rotate(30deg);
}

.airline-card-header h3 {
    position: relative;
    margin: 0;
    font-size: 1.4rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.airline-card-body {
    padding: 25px;
    text-align: center;
    background: #f9fbfd;
}

.airline-logo {
    max-width: 120px;
    max-height: 80px;
    margin: 0 auto 20px;
    display: block;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    transition: transform 0.3s ease;
}

.airline-card:hover .airline-logo {
    transform: scale(1.05);
}

.airline-code {
    display: inline-block;
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
    padding: 5px 12px;
    border-radius: 20px;
    font-family: 'Courier New', monospace;
    font-weight: bold;
    margin-top: 8px;
    font-size: 0.9rem;
    border: 1px solid rgba(52, 152, 219, 0.2);
}

.btn-view-flights {
    display: inline-block;
    background: linear-gradient(to right, #2ecc71, #27ae60);
    color: white;
    padding: 10px 25px;
    border-radius: 30px;
    text-decoration: none;
    margin-top: 20px;
    transition: all 0.3s ease;
    font-weight: 500;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 8px rgba(46, 204, 113, 0.2);
    border: none;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.btn-view-flights:hover {
    background: linear-gradient(to right, #27ae60, #219653);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(46, 204, 113, 0.3);
}

.btn-view-flights:active {
    transform: translateY(0);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px 25px;
    border-bottom: 1px solid rgba(0,0,0,0.08);
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}

.page-header h1 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.8rem;
    font-weight: 700;
}

.user-welcome {
    color: #7f8c8d;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
}

.logout-link {
    color: #e74c3c;
    text-decoration: none;
    margin-left: 12px;
    padding: 6px 12px;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-weight: 500;
}

.logout-link:hover {
    background: rgba(231, 76, 60, 0.1);
    color: #c0392b;
}

.container {
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
}

/* Search Bar Styles */
.search-container {
    margin-bottom: 30px;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}

.search-form {
    display: flex;
    max-width: 600px;
    margin: 0 auto;
}

.search-input {
    flex: 1;
    padding: 12px 20px;
    border: 1px solid #ddd;
    border-radius: 30px 0 0 30px;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

.search-button {
    padding: 12px 25px;
    background: linear-gradient(to right, #3498db, #2980b9);
    color: white;
    border: none;
    border-radius: 0 30px 30px 0;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-button:hover {
    background: linear-gradient(to right, #2980b9, #3498db);
}

.clear-search {
    display: inline-block;
    margin-left: 15px;
    color: #e74c3c;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.clear-search:hover {
    text-decoration: underline;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
    font-size: 1.2rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        padding: 15px;
    }
    
    .user-welcome {
        margin-top: 10px;
    }
    
    .airline-list {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 15px 0;
    }
    
    .search-form {
        flex-direction: column;
    }
    
    .search-input {
        border-radius: 30px;
        margin-bottom: 10px;
    }
    
    .search-button {
        border-radius: 30px;
    }
}
</style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>Available Airlines</h1>
            <div class="user-welcome">
                Welcome, <?php echo $_SESSION['username']; ?>!
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>
        
        <div class="search-container">
            <form class="search-form" method="get" action="">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Search airlines by name..." 
                    value="<?php echo htmlspecialchars($searchTerm); ?>"
                >
                <button type="submit" class="search-button">Search</button>
                <?php if (!empty($searchTerm)): ?>
                    <a href="airlines.php" class="clear-search">Clear search</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="airline-list">
            <?php if (empty($airlines)): ?>
                <div class="no-results">
                    No airlines found matching "<?php echo htmlspecialchars($searchTerm); ?>"
                </div>
            <?php else: ?>
                <?php foreach ($airlines as $airline): ?>
                    <div class="airline-card">
                        <div class="airline-card-header">
                            <h3><?php echo $airline['airline_name']; ?></h3>
                        </div>
                        <div class="airline-card-body">
                            <?php if (!empty($airline['logo_url'])): ?>
                                <img src="<?php echo $airline['logo_url']; ?>" alt="<?php echo $airline['airline_name']; ?>" class="airline-logo">
                            <?php endif; ?>
                            <p>Code: <span class="airline-code"><?php echo $airline['airline_code']; ?></span></p>
                            <a href="flights.php?airline_id=<?php echo $airline['airline_id']; ?>" class="btn-view-flights">View Flights</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
