<?php
// dashboard.php - Main dashboard with wallet, quick buy/sell
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Handle quick deposit/withdraw (dummy)
if ($_POST && isset($_POST['action'])) {
    $currency = $_POST['currency'];
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE user_id = ? AND currency = ?");
        $stmt->execute([$amount * (isset($_POST['withdraw']) ? -1 : 1), $user_id, $currency]);
        // Log transaction (dummy tx_hash)
        $type = $_POST['withdraw'] ? 'withdraw' : 'deposit';
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, currency, amount, usd_value, tx_hash) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $type, $currency, $amount, $amount * 60000, 'dummy_tx_' . time()]);  // Assume $60k/BTC
    }
}
 
// Fetch user data and balances
$stmt = $pdo->prepare("SELECT email, two_factor_enabled, kyc_status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
 
$stmt = $pdo->prepare("SELECT currency, balance, address FROM wallets WHERE user_id = ?");
$stmt->execute([$user_id]);
$wallets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CoinBase Clone</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; }
        header { background: rgba(0,0,0,0.8); padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: white; text-decoration: none; margin: 0 1rem; }
        .dashboard { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .balance-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .balance-card { background: rgba(255,255,255,0.9); border-radius: 15px; padding: 1.5rem; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .balance-value { font-size: 2rem; color: #00ff88; }
        .quick-actions { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .action-form { background: rgba(255,255,255,0.9); padding: 1rem; border-radius: 10px; flex: 1; min-width: 200px; }
        select, input { width: 100%; padding: 0.5rem; margin: 0.5rem 0; border-radius: 5px; border: 1px solid #ddd; }
        .btn { background: #4CAF50; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #45a049; }
        #portfolio-chart { max-width: 600px; margin: 2rem auto; background: rgba(255,255,255,0.9); border-radius: 15px; padding: 1rem; }
        @media (max-width: 768px) { .quick-actions { flex-direction: column; } }
    </style>
</head>
<body>
    <header>
        <h2>CoinBase Clone - Dashboard</h2>
        <nav>
            <a href="buy.php">Buy</a>
            <a href="sell.php">Sell</a>
            <a href="history.php">History</a>
            <a href="portfolio.php">Portfolio</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($user['email']); ?>!</h1>
        <p>2FA: <?php echo $user['two_factor_enabled'] ? 'Enabled' : 'Disabled'; ?> | KYC: <?php echo $user['kyc_status']; ?></p>
        <div class="balance-grid">
            <?php foreach ($wallets as $wallet): ?>
                <div class="balance-card">
                    <h3><?php echo $wallet['currency']; ?></h3>
                    <div class="balance-value"><?php echo number_format($wallet['balance'], 8); ?></div>
                    <p>Address: <?php echo htmlspecialchars($wallet['address']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="quick-actions">
            <form class="action-form" method="POST">
                <h3>Quick Deposit</h3>
                <select name="currency">
                    <option value="BTC">BTC</option>
                    <option value="ETH">ETH</option>
                </select>
                <input type="number" name="amount" placeholder="Amount" step="0.00000001" required>
                <button type="submit" class="btn" name="action" value="deposit">Deposit</button>
            </form>
            <form class="action-form" method="POST">
                <h3>Quick Withdraw</h3>
                <select name="currency">
                    <option value="BTC">BTC</option>
                    <option value="ETH">ETH</option>
                </select>
                <input type="number" name="amount" placeholder="Amount" step="0.00000001" required>
                <button type="submit" class="btn" name="action" value="withdraw">Withdraw</button>
            </form>
        </div>
        <div id="portfolio-chart">
            <canvas id="walletChart"></canvas>
        </div>
    </div>
    <script>
        // Pie chart for balances (dummy values for demo)
        const ctx = document.getElementById('walletChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['BTC', 'ETH'],
                datasets: [{ data: [<?php echo $wallets[0]['balance'] ?? 0; ?>, <?php echo $wallets[1]['balance'] ?? 0; ?>], backgroundColor: ['#f7931a', '#627eea'] }]
            },
            options: { responsive: true }
        });
    </script>
</body>
</html>
