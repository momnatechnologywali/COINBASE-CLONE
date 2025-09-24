<?php
// index.php - Homepage with real-time crypto prices
session_start();
include 'db.php';
 
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard if logged in
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoinBase Clone - Home</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #333; min-height: 100vh; }
        header { background: rgba(0,0,0,0.8); padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: white; text-decoration: none; margin: 0 1rem; font-weight: bold; }
        .hero { text-align: center; padding: 4rem 2rem; color: white; }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; }
        .prices { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .price-card { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 15px; padding: 1.5rem; text-align: center; color: white; transition: transform 0.3s; }
        .price-card:hover { transform: scale(1.05); }
        .price-symbol { font-size: 1.5rem; font-weight: bold; }
        .price-value { font-size: 2rem; color: #00ff88; }
        .price-change { font-size: 0.9rem; }
        .btn { background: #f093fb; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 25px; cursor: pointer; font-weight: bold; transition: background 0.3s; margin: 0.5rem; }
        .btn:hover { background: #f5576c; }
        .btn-secondary { background: transparent; border: 2px solid white; }
        #chart-container { max-width: 800px; margin: 2rem auto; background: rgba(255,255,255,0.1); border-radius: 15px; padding: 1rem; }
        @media (max-width: 768px) { .hero h1 { font-size: 2rem; } .prices { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <h2 style="color: white;">CoinBase Clone</h2>
        <nav>
            <a href="signup.php">Sign Up</a>
            <a href="login.php">Login</a>
        </nav>
    </header>
    <section class="hero">
        <h1>Welcome to Your Crypto Gateway</h1>
        <p>Buy, Sell & Store Digital Currencies Securely</p>
        <button class="btn" onclick="document.querySelector('.hero').innerHTML += '<p>Explore now!</p>';">Get Started</button>
    </section>
    <section id="prices" class="prices">
        <!-- Prices will be populated by JS -->
    </section>
    <div id="chart-container">
        <canvas id="priceChart"></canvas>
    </div>
    <script>
        // Real-time prices using CoinGecko API
        async function fetchPrices() {
            try {
                const response = await fetch('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum&vs_currencies=usd&include_24hr_change=true');
                const data = await response.json();
                const pricesDiv = document.getElementById('prices');
                pricesDiv.innerHTML = `
                    <div class="price-card">
                        <div class="price-symbol">BTC</div>
                        <div class="price-value">$${data.bitcoin.usd}</div>
                        <div class="price-change">${data.bitcoin.usd_24h_change.toFixed(2)}%</div>
                    </div>
                    <div class="price-card">
                        <div class="price-symbol">ETH</div>
                        <div class="price-value">$${data.ethereum.usd}</div>
                        <div class="price-change">${data.ethereum.usd_24h_change.toFixed(2)}%</div>
                    </div>
                `;
            } catch (error) {
                console.error('Error fetching prices:', error);
            }
        }
 
        // Chart.js for historical prices (dummy data for demo)
        const ctx = document.getElementById('priceChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                datasets: [{ label: 'BTC Price', data: [40000, 45000, 42000, 50000, 48000], borderColor: '#f7931a', tension: 0.1 }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: false } } }
        });
 
        fetchPrices();
        setInterval(fetchPrices, 30000);  // Update every 30s
    </script>
</body>
</html>
