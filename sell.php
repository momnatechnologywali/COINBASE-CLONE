<?php
// sell.php - Sell crypto similar to buy.php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Handle sell order (similar to buy, but deduct from wallet)
if ($_POST && isset($_POST['place_order'])) {
    $type = $_POST['order_type'];
    $currency = $_POST['currency'];
    $amount = floatval($_POST['amount']);
    $price = $type === 'limit' ? floatval($_POST['limit_price']) : null;
    $side = 'sell';
 
    $current_price = 60000;  // Dummy
 
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, type, side, currency, amount, price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $type, $side, $currency, $amount, $price]);
 
    if ($type === 'market') {
        // Deduct from wallet
        $stmt = $pdo->prepare("UPDATE wallets SET balance = balance - ? WHERE user_id = ? AND currency = ?");
        if ($stmt->execute([$amount, $user_id, $currency]) && $pdo->rowCount() > 0) {
            // Log transaction
            $usd_value = $amount * $current_price;
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, currency, amount, usd_value) VALUES (?, 'sell', ?, ?, ?)");
            $stmt->execute([$user_id, $currency, $amount, $usd_value]);
        }
    }
}
 
$current_prices = ['BTC' => 60000, 'ETH' => 3000];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Crypto - CoinBase Clone</title>
    <style>
        /* Same styles as buy.php for consistency */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; padding: 2rem; }
        .order-form { background: rgba(255,255,255,0.95); max-width: 500px; margin: 0 auto; padding: 2rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        h1 { text-align: center; margin-bottom: 1rem; }
        select, input { width: 100%; padding: 0.8rem; margin: 0.5rem 0; border: 1px solid #ddd; border-radius: 5px; }
        .radio-group { display: flex; gap: 1rem; margin: 1rem 0; }
        .radio-group input { width: auto; }
        .btn { width: 100%; background: #f44336; color: white; padding: 1rem; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1rem; margin-top: 1rem; }
        .btn:hover { background: #da190b; }
        .price-display { text-align: center; font-size: 1.2rem; margin: 1rem 0; color: #ff5722; }
        a { display: block; text-align: center; margin-top: 1rem; color: #667eea; text-decoration: none; }
        @media (max-width: 480px) { body { padding: 1rem; } .radio-group { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="order-form">
        <h1>Sell Crypto</h1>
        <p class="price-display">Current Prices: BTC $<?php echo $current_prices['BTC']; ?>, ETH $<?php echo $current_prices['ETH']; ?></p>
        <form method="POST">
            <select name="currency">
                <option value="BTC">Bitcoin (BTC)</option>
                <option value="ETH">Ethereum (ETH)</option>
            </select>
            <input type="number" name="amount" placeholder="Amount to Sell" step="0.00000001" required>
            <div class="radio-group">
                <label><input type="radio" name="order_type" value="market" checked> Market Order</label>
                <label><input type="radio" name="order_type" value="limit"> Limit Order</label>
            </div>
            <input type="number" name="limit_price" placeholder="Limit Price (USD)" step="0.01" style="display: none;" id="limitPrice">
            <button type="submit" name="place_order" class="btn">Place Sell Order</button>
        </form>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
    <script>
        // Same JS as buy.php
        document.querySelectorAll('input[name="order_type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                document.getElementById('limitPrice').style.display = e.target.value === 'limit' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
