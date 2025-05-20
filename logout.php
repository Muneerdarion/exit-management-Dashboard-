<?php
session_start();
require_once 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Initialize variables
$error = '';
$username = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Sanitize and validate inputs
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = $_POST['password']; // Don't sanitize passwords
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        try {
            // Get user from database
            $stmt = $conn->prepare("SELECT id, username, password, role, login_attempts, last_attempt FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check account lock status
            if ($user && $user['login_attempts'] >= 5 && strtotime($user['last_attempt']) > strtotime('-15 minutes')) {
                $error = 'Account locked. Try again later.';
            } 
            // Verify credentials
            elseif ($user && password_verify($password, $user['password'])) {
                // Successful login - reset attempts
                $conn->prepare("UPDATE users SET login_attempts = 0 WHERE id = ?")->execute([$user['id']]);
                
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // Set secure cookie if "Remember me" checked
                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + 86400 * 30; // 30 days
                    setcookie('remember_token', $token, $expiry, '/', '', true, true);
                    
                    // Store hashed token in database
                    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                    $conn->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?")
                         ->execute([$hashedToken, date('Y-m-d H:i:s', $expiry), $user['id']]);
                }
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                // Failed login - increment attempts
                if ($user) {
                    $conn->prepare("UPDATE users SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE id = ?")
                         ->execute([$user['id']]);
                }
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'A system error occurred. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRM System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .login-container { 
            max-width: 400px; 
            margin: 100px auto;
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-floating label { padding: 1rem 0.75rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="text-center mb-4">HRM System Login</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" autocomplete="off">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username); ?>" required>
                    <label for="username">Username</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <label for="password">Password</label>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary w-100 py-2">Login</button>
                
                <div class="mt-3 text-center">
                    <a href="forgot_password.php">Forgot password?</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Clear password field on page load (prevent autofill)
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('password').value = '';
        });
    </script>
</body>
</html>