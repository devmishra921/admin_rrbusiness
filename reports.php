<?php
// reports.php - Fixed with proper JSON fetch and advanced chart filter
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.html'); exit; }
require 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RR Business | Reports</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root{--brand:#a83232;--accent:#ffcb6b;--bg:#f5f7fa;--card-bg:rgba(255,255,255,0.8);--shadow:0 10px 30px rgba(0,0,0,.08);--radius:18px}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:var(--bg);color:#333;line-height:1.45}
    /* ===== HEADER ===== */
    header{position:sticky;top:0;z-index:999;display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:linear-gradient(120deg,var(--brand),#ff5d5d);color:#fff;box-shadow:var(--shadow)}
    header .logo{height:60px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3))}
    header h1{font-size:1.5rem;font-weight:600;letter-spacing:1px;text-shadow:0 1px 2px rgba(0,0,0,.3)}
    header #clock{font-family:'Poppins',sans-serif;font-weight:500}
    nav{background:#fff;box-shadow:var(--shadow)}
    nav ul{display:flex;flex-wrap:wrap;justify-content:center;gap:6px;list-style:none;padding:10px 12px;max-width:1200px;margin:auto}
    nav a{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;font-weight:500;text-decoration:none;color:#333;transition:.3s}
    nav a:hover,nav a.active{background:var(--brand);color:#fff}
    .container {
  width: calc(100% - 80px); /* Full width with 40px margin on both sides */
  margin: 48px auto;
  padding: 40px;
  background: var(--card-bg);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  animation: fadeIn .6s ease;
}
    .controls{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:24px}
    select,input[type="date"]{padding:8px 12px;border:1px solid #ccc;border-radius:8px}
    .report-card{background:rgba(255,255,255,.6);backdrop-filter:blur(8px);padding:24px;border-radius:var(--radius);box-shadow:var(--shadow)}
    h2.section-title{font-size:1.6rem;font-weight:600;text-align:center;margin-bottom:24px}
    footer {
  background:rgb(143, 51, 51);
  color: #ccc;
  padding: 30px 20px 30px;
  font-size: 0.95rem;
  margin-top: 80px;
}

.footer-container {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  max-width: 1200px;
  margin: auto;
  gap: 60px;
}

.footer-col {
  flex: 1 1 50px;
  min-width: 240px;
}

.footer-col h4 {
  color: #ffcb6b;
  margin-bottom: 16px;
  font-size: 1.1rem;
}

.footer-col ul {
  list-style: none;
  padding: 0;
}

.footer-col ul li {
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.footer-col a {
  color: #ccc;
  text-decoration: none;
  transition: 0.3s;
}

.footer-col a:hover {
  color: #fff;
  text-shadow: 0 0 4px #fff;
}

.footer-bottom {
  text-align: center;
  margin-top: 40px;
  font-size: 0.9rem;
  color: #aaa;
}

.footer-bottom p {
  margin: 4px 0;
}
  </style>
</head>
<body>
<header>
  <img src="images/Logo.png" alt="R.R. Business Logo" class="logo" />
  <h1>R.R. Business Reports</h1>
  <span id="clock"></span>
  </header>
<nav>
  <ul>
    <li><a href="dashboard.php"><i class="fa fa-chart-line"></i>Dashboard</a></li>
    <li><a href="admin_pannel.php"><i class="fa fa-box"></i>Products</a></li>
    <li><a href="inventory.php"><i class="fa fa-warehouse"></i>Inventory</a></li>
    <li><a href="generate_bill.php"><i class="fa fa-file-invoice"></i>Billing</a></li>
    <li><a class="active" href="reports.php"><i class="fa fa-chart-bar"></i>Report</a></li>
    <li><a href="view_order.php"><i class="fa fa-receipt"></i>Orders</a></li>
    <li><a href="admin_add_barcode.php"><i class="fa fa-barcode"></i>Barcode</a></li>
    <li><a href="customer_queries.php"><i class="fa fa-comments"></i>Queries</a></li>
    <li><a href="gallery_view.php"><i class="fa fa-image"></i>Gallery</a></li>
    <li><a href="logout.html"><i class="fa fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</nav>

<main class="container">
  <h2 class="section-title">📊 Sales Report</h2>
  <div class="controls">
    <select id="rangeSelect">
      <option value="today">Today</option>
      <option value="7">Last 7 Days</option>
      <option value="30">Last 30 Days</option>
      <option value="90">Last 3 Months</option>
      <option value="365">Last 1 Year</option>
      <option value="custom">Custom Range</option>
    </select>
    <input type="date" id="fromDate" style="display:none">
    <input type="date" id="toDate" style="display:none">
    <button id="applyBtn">Apply</button>
    <button id="downloadBtn"><i class="fa fa-download"></i> Download Chart</button>
  </div>
  <div class="report-card">
    <canvas id="salesChart" style="max-height:400px;"></canvas>
  </div>
</main>


<footer>
  <div class="footer-container">
    <div class="footer-col">
      <h4>📞 Contact Us</h4>
      <ul>
        <li><i class="fa fa-phone"></i> +91 76788 53017</li>
        <li><i class="fa fa-envelope"></i> support@rrbusiness.com</li>
        <li><i class="fa fa-envelope-open"></i> care@rrbusiness.com</li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>🔗 Quick Links</h4>
      <ul>
        <li><a href="dashboard.php"> Dashboard</a></li>
        <li><a href="admin_pannel.php"> Manage Products</a></li>
        <li><a href="view_order.php"> Orders</a></li>
        <li><a href="customer_queries.php"> Queries</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>📱 Follow Us</h4>
      <ul>
        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
        <li><a href="https://www.instagram.com/rrbusiness2025" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
        <li><a href="https://wa.me/917678853017"><i class="fab fa-whatsapp"></i> WhatsApp</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; <?=date('Y')?> <strong>R.R. Business</strong> — All Rights Reserved</p>
    <p>🚀 Developed by <strong>V.G Technologies Pvt. Ltd.</strong></p>
  </div>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
function updateClock() {
  const now = new Date();
  document.getElementById('clock').textContent = now.toLocaleTimeString();
}
setInterval(updateClock, 1000); updateClock();

const ctx = document.getElementById('salesChart').getContext('2d');
let chart = null;
function renderChart(labels, values) {
  if(chart) chart.destroy();
  chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Sales ₹',
        data: values,
        backgroundColor: 'rgba(168, 50, 50, 0.8)',
        borderRadius: 8,
        barThickness: 30
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } }
      }
    }
  });
}

async function fetchData(range, from=null, to=null) {
  try {
    const params = new URLSearchParams({range});
    if(range==='custom'){ params.append('from', from); params.append('to', to); }
    const res = await fetch('reports_data.php?' + params.toString());
    const json = await res.json();
    renderChart(json.labels, json.data);
  } catch (e) {
    alert('Error loading data: ' + e.message);
  }
}

document.getElementById('rangeSelect').addEventListener('change', e => {
  const show = e.target.value === 'custom';
  document.getElementById('fromDate').style.display = show ? 'inline-block' : 'none';
  document.getElementById('toDate').style.display = show ? 'inline-block' : 'none';
});

document.getElementById('applyBtn').addEventListener('click', () => {
  const range = document.getElementById('rangeSelect').value;
  if(range==='custom'){
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;
    if(!from || !to){ alert('Please select both dates'); return; }
    fetchData('custom', from, to);
  } else {
    fetchData(range);
  }
});

document.getElementById('downloadBtn').addEventListener('click', () => {
  const a = document.createElement('a');
  a.href = ctx.canvas.toDataURL('image/png');
  a.download = 'sales_chart.png';
  a.click();
});

// Load default chart data
fetchData('7');
</script>

</body>
</html>
