<?php
session_start();
require_once 'includes/config.php';
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    // In a real system, you would validate against a users table with hashed passwords
    if($username === 'admin' && $password === 'admin123') {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'Admin';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Exit Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --secondary: #f8fafc;
            --accent: #8b5cf6;
            --text: #1e293b;
            --light-text: #64748b;
            --error: #ef4444;
            --success: #10b981;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text);
            line-height: 1.6;
        }
        
        .login-container {
            background-color: rgba(255, 255, 255, 0.98);
            padding: 2.5rem 3rem;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.15);
            width: 100%;
            max-width: 420px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .login-container:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(99, 102, 241, 0.2);
        }
        
        .logo {
            width: 90px;
            height: 90px;
            margin: 0 auto 1.8rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
            transform: rotate(5deg);
        }
        
        h2 {
            color: var(--primary);
            margin-bottom: 1.8rem;
            font-weight: 600;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
        }
        
        .welcome-text {
            color: var(--light-text);
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.6rem;
            text-align: left;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 0.6rem;
            color: var(--text);
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        input {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: var(--secondary);
            font-family: 'Poppins', sans-serif;
        }
        
        input:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }
        
        button {
            background: linear-gradient(to right, var(--primary), var(--accent));
            border: none;
            color: white;
            padding: 1.1rem 0;
            width: 100%;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 0.8rem;
            letter-spacing: 0.5px;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0.1), rgba(255,255,255,0.3));
            transform: translateX(-100%);
            transition: transform 0.4s ease;
        }
        
        button:hover::after {
            transform: translateX(100%);
        }
        
        .error-message {
            color: var(--error);
            background-color: #fef2f2;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.8rem;
            font-size: 0.9rem;
            border: 1px solid #fee2e2;
            animation: fadeIn 0.3s ease;
        }
        
        .footer-text {
            margin-top: 2rem;
            color: var(--light-text);
            font-size: 0.85rem;
        }
        
        .footer-text a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
        }
        
        .footer-text a:hover {
            color: var(--accent);
        }
        
        .footer-text a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: width 0.3s ease;
        }
        
        .footer-text a:hover::after {
            width: 100%;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-container {
                padding: 2rem 1.5rem;
                margin: 0 1rem;
            }
            
            .logo {
                width: 80px;
                height: 80px;
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">EMS</div>
        <h2>Exit Management System</h2>
        <p class="welcome-text">Welcome back! Please login to continue</p>
        
        <!-- Error message placeholder (would be shown conditionally in PHP) -->
        <!-- <div class="error-message">Invalid username or password</div> -->
        
        <form method="POST" action="#">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            <button type="submit" name="login">Login →</button>
        </form>
        
        <div class="footer-text">
            Need help? <a href="#">Contact support</a>
        </div>
    </div>
</body>
</html>