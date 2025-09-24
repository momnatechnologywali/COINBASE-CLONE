<?php
// signup.php - User signup with 2FA setup
session_start();
include 'db.php';
 
$error = '';
$success = '';
 
if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
 
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Generate dummy wallet address and 2FA secret (Base32 for TOTP)
            $two_factor_secret = strtoupper(base_convert(random_int(0, 999999), 10, 32));  // Simple Base32
 
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name, phone, two_factor_secret) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$email, $password_hash, $first_name, $last_name, $phone, $two_factor_secret])) {
                $user_id = $pdo->lastInsertId();
                // Create wallets for BTC and ETH
                $wallets = [
                    ['BTC', '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa' . $user_id],
                    ['ETH', '0x742d35Cc6634C0532925a3b8D4a1D4e4A5b6c7d8' . $user_id]
                ];
                $stmt = $pdo->prepare("INSERT INTO wallets (user_id, currency, address) VALUES (?, ?, ?)");
                foreach ($wallets as $w) {
                    $stmt->execute([$user_id, $w[0], $w[1]]);
                }
                $success = 'Account created! Enable 2FA in settings after login.';
                $_SESSION['signup_success'] = true;
            } else {
                $error = 'Signup failed';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - CoinBase Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { background: rgba(255,255,255,0.95); padding: 2rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        input { width: 100%; padding: 0.8rem; margin: 0.5rem 0; border: 1px solid #ddd; border-radius: 5px; }
        .btn { width: 100%; background: #4CAF50; color: white; padding: 0.8rem; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; }
        .btn:hover { background: #45a049; }
        .error { color: red; text-align: center; margin: 1rem 0; }
        .success { color: green; text-align: center; margin: 1rem 0; }
        a { text-align: center; display: block; margin-top: 1rem; color: #667eea; text-decoration: none; }
        @media (max-width: 480px) { .form-container { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Sign Up</h2>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
        <form method="POST">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password (min 8 chars)" required>
            <input type="tel" name="phone" placeholder="Phone (optional)">
            <button type="submit" class="btn">Sign Up</button>
        </form>
        <a href="login.php">Already have an account? Login</a>
        <a href="index.php">Back to Home</a>
    </div>
    <script>
        // JS redirection example if needed
        if (window.location.search.includes('success')) {
            alert('Signup successful!');
        }
    </script>
</body>
</html>
