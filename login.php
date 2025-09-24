<?php
// login.php - User login with 2FA
session_start();
include 'db.php';
 
$error = '';
$show_2fa = false;
$two_factor_code = '';
 
if ($_POST) {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Standard login
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $stmt = $pdo->prepare("SELECT id, password_hash, two_factor_secret, two_factor_enabled FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['two_factor_enabled']) {
                $_SESSION['pending_user_id'] = $user['id'];
                $show_2fa = true;
            } else {
                $_SESSION['user_id'] = $user['id'];
                echo "<script>window.location.href='dashboard.php';</script>";
                exit;
            }
        } else {
            $error = 'Invalid credentials';
        }
    } elseif (isset($_POST['two_factor_code'])) {
        // 2FA verification
        $user_id = $_SESSION['pending_user_id'] ?? 0;
        if ($user_id) {
            $stmt = $pdo->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $secret = $stmt->fetchColumn();
            if ($secret && generateTOTP($secret) === $_POST['two_factor_code']) {
                $_SESSION['user_id'] = $user_id;
                unset($_SESSION['pending_user_id']);
                echo "<script>window.location.href='dashboard.php';</script>";
                exit;
            } else {
                $error = 'Invalid 2FA code';
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
    <title>Login - CoinBase Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { background: rgba(255,255,255,0.95); padding: 2rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        input { width: 100%; padding: 0.8rem; margin: 0.5rem 0; border: 1px solid #ddd; border-radius: 5px; }
        .btn { width: 100%; background: #2196F3; color: white; padding: 0.8rem; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; }
        .btn:hover { background: #0b7dda; }
        .error { color: red; text-align: center; margin: 1rem 0; }
        .twofa { margin-top: 1rem; font-size: 0.9rem; color: #666; }
        a { text-align: center; display: block; margin-top: 1rem; color: #667eea; text-decoration: none; }
        @media (max-width: 480px) { .form-container { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2><?php echo $show_2fa ? '2FA Verification' : 'Login'; ?></h2>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($show_2fa): ?>
            <form method="POST">
                <input type="text" name="two_factor_code" placeholder="Enter 6-digit code" maxlength="6" required>
                <p class="twofa">Use Google Authenticator app to scan your secret: <?php echo $_SESSION['pending_user_id'] ?? ''; ?> (Demo: use generateTOTP in console)</p>
                <button type="submit" class="btn">Verify</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="btn">Login</button>
            </form>
        <?php endif; ?>
        <a href="signup.php">Don't have an account? Sign Up</a>
        <a href="index.php">Back to Home</a>
    </div>
    <script>
        // For demo 2FA, you can compute in console: generateTOTP('your_secret')
    </script>
</body>
</html>
