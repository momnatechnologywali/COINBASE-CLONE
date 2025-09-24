<?php
// portfolio.php - Portfolio tracking with P/L
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Fetch wallets and calculate total value (dummy prices)
$stmt = $pdo->prepare("SELECT currency, balance FROM wallets WHERE user_id = ?");
$stmt->execute([$user_id]);
$wallets = $stmt->fetchAll();
$total_value = 0;
$prices = ['BTC' => 60000, 'ETH' => 3000];
foreach ($wallets as $w) {
    $total_value += $w['balance'] * $prices[$w['currency']];
}
 
// Fetch transactions for P/L (simple: sum buys - sells)
$stmt = $pdo->prepare("SELECT SUM(usd_value) as total_in FROM transactions WHERE user_id = ? AND type IN ('buy', 'deposit')");
$stmt->execute([$user_id]);
$total_in = $stmt->fetchColumn() ?: 0;
 
$stmt = $pdo->prepare("SELECT SUM(usd_value) as total_out FROM transactions WHERE user_id = ? AND type IN ('sell', 'withdraw')");
$stmt->execute([$user_id]);
$total_out = $stmt->fetchColumn() ?: 0;
 
$pl = $total_value + $total_out - $total_in;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - CoinBase Clone</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; padding: 2rem; }
        .portfolio-container { background: rgba(255,255,255,0.95); max-width: 800px; margin: 0 auto; padding: 2rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        h1 { text-align: center; margin-bottom: 1rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: #f9f9f9; padding: 1rem; border-radius: 10px; text-align: center; }
        .stat-value { font-size: 1.5rem; font-weight: bold; color: <?php echo $pl >= 0 ? '#4CAF50' : '#f44336'; ?>; }
        #pl-chart { max-width: 600px; margin: 2rem auto; }
        a { display: block; text-align: center; margin-top: 1rem; color: #667eea; text-decoration: none; }
        @media (max-width: 768px) { .stats { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="portfolio-container">
        <h1>Portfolio Overview</h1>
        <div class="stats">
            <div class="stat-card">
                <h3>Total Value</h3>
                <div class="stat-value">$<?php echo number_format($total_value, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Invested</h3>
                <div class="stat-value">$<?php echo number_format($total_in, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Profit/Loss</h3>
                <div class="stat-value"><?php echo $pl >= 0 ? '+' : ''; ?>$<?php echo number_format($pl, 2); ?></div>
            </div>
        </div>
        <canvas id="plChart"></canvas>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
    <script>
        const ctx = document.getElementById('plChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Invested', 'Current Value'],
                datasets: [{ label: 'USD', data: [<?php echo $total_in; ?>, <?php echo $total_value; ?>], backgroundColor: ['#2196F3', '#4CAF50'] }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
