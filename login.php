<?php
// Absolute path configuration
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Start session
session_start();

// Initialize variables
$error = '';
$success = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);
    
    try {
        $stmt = $pdo->prepare("SELECT user_id, username, password FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: airlines.php");
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}

// Process signup form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);
    $email = sanitizeInput($_POST['email']);
    $full_name = sanitizeInput($_POST['full_name']);
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already exists';
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $email, $full_name]);
            
            $success = 'Account created successfully! Please login.';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking | Login</title>
    <style>
        /* Modern CSS styling */
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --success: #2ecc71;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .auth-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 400px;
            padding: 30px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background: #fadbd8;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark);
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: var(--primary-dark);
        }
        
        .btn-success {
            background: var(--success);
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
        }
        
        .form-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .form-toggle {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .form-toggle button {
            flex: 1;
            background: none;
            border: none;
            padding: 10px;
            font-size: 16px;
            cursor: pointer;
            color: #95a5a6;
            position: relative;
        }
        
        .form-toggle button.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        .form-toggle button.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="form-toggle">
            <button class="active" onclick="showForm('login')">Login</button>
            <button onclick="showForm('signup')">Sign Up</button>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form method="POST" action="login.php" class="auth-form active" id="loginForm">
            <div class="form-header">
                <h2>Login</h2>
                <p>Access your booking account</p>
            </div>
            
            <div class="form-group">
                <label for="login_username">Username</label>
                <input type="text" id="login_username" name="username" required 
                       value="<?php echo isset($_POST['login']) ? htmlspecialchars($_POST['username'] ?? '') : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="login_password">Password</label>
                <input type="password" id="login_password" name="password" required>
            </div>
            
            <button type="submit" name="login" class="btn">Login</button>
            
            <div class="form-footer">
                <a href="#">Forgot password?</a>
            </div>
        </form>
        
        <!-- Signup Form -->
        <form method="POST" action="login.php" class="auth-form" id="signupForm">
            <div class="form-header">
                <h2>Create Account</h2>
                <p>Start booking your flights</p>
            </div>
            
            <div class="form-group">
                <label for="signup_username">Username</label>
                <input type="text" id="signup_username" name="username" required 
                       value="<?php echo isset($_POST['signup']) ? htmlspecialchars($_POST['username'] ?? '') : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="signup_password">Password</label>
                <input type="password" id="signup_password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['signup']) ? htmlspecialchars($_POST['email'] ?? '') : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?php echo isset($_POST['signup']) ? htmlspecialchars($_POST['full_name'] ?? '') : ''; ?>">
            </div>
            
            <button type="submit" name="signup" class="btn btn-success">Sign Up</button>
            
            <div class="form-footer">
                Already have an account? <a href="#" onclick="showForm('login')">Login</a>
            </div>
        </form>
    </div>
    
    <script>
        function showForm(formType) {
            // Update toggle buttons
            document.querySelectorAll('.form-toggle button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Show selected form
            document.getElementById('loginForm').classList.remove('active');
            document.getElementById('signupForm').classList.remove('active');
            document.getElementById(formType + 'Form').classList.add('active');
        }
        
        // Show signup form if there was a signup error
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])): ?>
            showForm('signup');
        <?php endif; ?>
    </script>
</body>
</html>
