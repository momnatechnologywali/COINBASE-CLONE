<?php
// history.php - Transaction history
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - CoinBase Clone</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; padding: 2rem; }
        .history-container { background: rgba(255,255,255,0.95); max-width: 800px; margin: 0 auto; padding: 2rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        h1 { text-align: center; margin-bottom: 2rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f4f4f4; font-weight: bold; }
        .type-buy { color: #4CAF50; }
        .type-sell { color: #f44336; }
        a { display: block; text-align: center; margin-top: 1rem; color: #667eea; text-decoration: none; }
        @media (max-width: 768px) { table { font-size: 0.9rem; } th, td { padding: 0.5rem; } }
    </style>
</head>
<body>
    <div class="history-container">
        <h1>Transaction History</h1>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Currency</th>
                    <th>Amount</th>
                    <th>USD Value</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td class="type-<?php echo $tx['type']; ?>"><?php echo ucfirst($tx['type']); ?></td>
                        <td><?php echo $tx['currency']; ?></td>
                        <td><?php echo number_format($tx['amount'], 8); ?></td>
                        <td>$<?php echo number_format($tx['usd_value'], 2); ?></td>
                        <td><?php echo ucfirst($tx['status']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($tx['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($transactions)): ?><tr><td colspan="6">No transactions yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>
