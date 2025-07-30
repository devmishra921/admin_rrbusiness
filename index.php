<?php
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_secure', '0');
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.html');
  exit;
}

function getCounts($conn){
  return [
    'total_products'    => $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0] ?? 0,
    'new_orders'        => $conn->query("SELECT COUNT(*) FROM orders WHERE status=\"new\"")->fetch_row()[0] ?? 0,
    'in_progress'       => $conn->query("SELECT COUNT(*) FROM orders WHERE status=\"in_progress\"")->fetch_row()[0] ?? 0,
    'completed_orders'  => $conn->query("SELECT COUNT(*) FROM orders WHERE status=\"completed\"")->fetch_row()[0] ?? 0,
    'cancelled_orders'  => $conn->query("SELECT COUNT(*) FROM orders WHERE status=\"cancelled\"")->fetch_row()[0] ?? 0,
    'pending_queries'   => $conn->query("SELECT COUNT(*) FROM message WHERE status=\"pending\"")->fetch_row()[0] ?? 0,
    'completed_queries' => $conn->query("SELECT COUNT(*) FROM message WHERE status=\"completed\"")->fetch_row()[0] ?? 0,
  ];
}

if (isset($_GET['counts'])) {
  header('Content-Type: application/json');
  echo json_encode(getCounts($conn));
  exit;
}

$init        = getCounts($conn);
$initJS      = json_encode($init);
$ordersRS    = $conn->query("SELECT * FROM (SELECT * FROM orders ORDER BY id DESC LIMIT 5) AS recent ORDER BY id ASC");
$recentOrders = $ordersRS ? $ordersRS->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>RR Business | Dashboard</title>

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===== DESIGN TOKENS ===== */
:root{--brand:#a83232;--accent:#ffcb6b;--bg:#f5f7fa;--card-bg:rgba(255,255,255,0.8);--glass:rgba(255,255,255,0.6);--shadow:0 10px 30px rgba(0,0,0,.08);--radius:18px;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:#333;line-height:1.45;overflow-x:hidden}

/* ===== HEADER ===== */
header{position:sticky;top:0;z-index:999;display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:linear-gradient(120deg,var(--brand),#ff5d5d);color:#fff;box-shadow:var(--shadow)}
header .logo{height:60px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3))}
header h1{font-size:1.5rem;font-weight:600;letter-spacing:1px;text-shadow:0 1px 2px rgba(0,0,0,.3)}
header #clock{font-family:'Poppins',sans-serif;font-weight:500}

/* ===== NAV ===== */
nav{background:#fff;box-shadow:var(--shadow)}
nav ul{display:flex;flex-wrap:wrap;justify-content:center;gap:6px;list-style:none;padding:10px 12px;max-width:1200px;margin:auto}
nav a{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;font-weight:500;text-decoration:none;color:#333;transition:.3s}
nav a:hover,nav a.active{background:var(--brand);color:#fff}

/* ===== DASHBOARD CARDS ===== */
.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 24px;
  margin-top: 24px;
  margin-right: 40px;
  margin-left: 40px;
}

/* separate 2 rows */
.dashboard-cards.row-top {
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.dashboard-cards.row-bottom {
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  margin-top: 30px;
}

.card {
  position: relative;
  padding: 44px;
  border-radius: var(--radius);
  background: var(--glass);
  box-shadow: var(--shadow);
  overflow: hidden;
  backdrop-filter: blur(12px);
  transition: transform 0.35s;
}

.card:hover {
  transform: translateY(-6px);
}

.card i {
  position: absolute;
  top: -10px;
  right: -10px;
  font-size: 64px;
  color: var(--brand);
  opacity: 0.12;
}

.card h3 {
  font-size: .95rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .4px;
  margin-bottom: 5px;
}

.card p {
  font-size: 2.2rem;
  font-weight: 700;
  color: var(--brand);
}

/* wide card override */
.card-large {
  grid-column: span 2;
  background: linear-gradient(135deg, var(--brand), #ff6b6b);
  color: #fff;
}
.card-large h3,
.card-large p {
  color: #fff;
}
.card-large i {
  color: rgba(255, 255, 255, .2);
}

@media (max-width: 768px) {
  .card-large {
    grid-column: auto;
  }
}

/* ===== CHART ===== */
.chart-container{max-width:420px;margin:48px auto}

/* ===== LATEST ORDERS TABLE ===== */
.orders-section h2{margin-top:52px;margin-right:40px;margin-left:40px;font-size:1.35rem;font-weight:600}
.orders-table{width:100%;border-collapse:collapse;margin-top:16px;margin-left:40px;margin-right:40px;font-size:.95rem}
.orders-table th{background:var(--brand);color:#fff;padding:10px;border-top-left-radius:8px;border-top-right-radius:5px}
.orders-table td{padding:12px;border-bottom:1px solid #eee;text-align:center}
.orders-table tr:nth-child(even){background:#fafafa}

/* ===== FOOTER ===== */
footer{background:rgb(143,51,51);color:#ccc;padding:30px 20px;font-size:.95rem;margin-top:80px}
.footer-container{display:flex;flex-wrap:wrap;justify-content:space-between;max-width:1200px;margin:auto;gap:60px}
.footer-col{flex:1 1 50px;min-width:240px}
.footer-col h4{color:var(--accent);margin-bottom:16px;font-size:1.1rem}
.footer-col ul{list-style:none;padding:0}
.footer-col li{margin-bottom:10px;display:flex;align-items:center;gap:6px}
.footer-col a{color:#ccc;text-decoration:none;transition:.3s}
.footer-col a:hover{color:#fff;text-shadow:0 0 4px #fff}
.footer-bottom{text-align:center;margin-top:40px;font-size:.9rem;color:#aaa}
</style>
</head>
<body>

<!-- ===== HEADER ===== -->
<header>
  <img src="images/Logo.png" class="logo" alt="R.R. Business Logo">
  <h1>R.R. Business – Dashboard</h1>
  <span id="clock"></span>
</header>

<!-- ===== NAV ===== -->
<nav>
  <ul>
    <li><a class="active" href="dashboard.php"><i class="fa fa-chart-line"></i>Dashboard</a></li>
    <li><a href="admin_pannel.php"><i class="fa fa-box"></i>Products</a></li>
    <li><a href="inventory.php"><i class="fa fa-warehouse"></i>Inventory</a></li>
    <li><a href="generate_bill.php"><i class="fa fa-file-invoice"></i>Billing</a></li>
    <li><a href="reports.php"><i class="fa fa-chart-bar"></i>Report</a></li>
    <li><a href="view_order.php"><i class="fa fa-receipt"></i>Orders</a></li>
    <li><a href="admin_add_barcode.php"><i class="fa fa-barcode"></i>Barcode</a></li>
    <li><a href="customer_queries.php"><i class="fa fa-comments"></i>Queries</a></li>
    <li><a href="gallery_view.php"><i class="fa fa-image"></i>Gallery</a></li>
    <li><a href="logout.html"><i class="fa fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</nav>

<!-- ===== MAIN ===== -->
<!-- Top row: only 4 cards -->
<section class="dashboard-cards row-top">
  <div class="card"><i class="fa fa-box"></i><h3>Total Products</h3><p id="total_products" data-count="<?=$init['total_products']?>"><?=$init['total_products']?></p>
</div>
  <div class="card"><i class="fa fa-cart-arrow-down"></i><h3>New Orders</h3><p id="new_orders" data-count="<?=$init['new_orders']?>"><?=$init['new_orders']?></p></div>
  <div class="card"><i class="fa fa-tasks"></i><h3>In Progress</h3><p id="in_progress" data-count="<?=$init['in_progress']?>"><?=$init['in_progress']?></p></div>
  <div class="card"><i class="fa fa-check-circle"></i><h3>Completed Orders</h3><p id="completed_orders" data-count="<?=$init['completed_orders']?>"><?=$init['completed_orders']?></p></div>
  <div class="card"><i class="fa fa-times-circle"></i><h3>Cancelled Orders</h3><p id="cancelled_orders" data-count="<?=$init['cancelled_orders']?>"><?=$init['cancelled_orders']?></p></div>
</section>

<!-- Bottom row: unchanged -->
<section class="dashboard-cards row-bottom">
  <div class="card card-large"><i class="fa fa-question-circle"></i><h3>Pending Queries</h3><p id="pending_queries" data-count="<?=$init['pending_queries']?>"><?=$init['pending_queries']?></p></div>
  <div class="card card-large"><i class="fa fa-envelope-open-text"></i><h3>Queries Done</h3><p id="completed_queries" data-count="<?=$init['completed_queries']?>"><?=$init['completed_queries']?></p></div>
</section>

  <!-- doughnut chart -->
  <div class="chart-container"><canvas id="ordersChart"></canvas></div>

  <!-- latest orders table -->
  <section class="orders-section">
    <h2>Latest 5 Orders</h2>
    <table class="orders-table">
      <thead><tr><th>ID</th><th>Customer</th><th>Amount (₹)</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php if($recentOrders): foreach($recentOrders as $o): ?>
        <tr>
          <td><?=$o['id']?></td>
          <td><?=htmlspecialchars($o['customer_name'] ?? $o['customer'] ?? '—')?></td>
          <td><?=$o['total_amount'] ?? $o['amount'] ?? '0'?></td>
          <td><?=ucfirst(str_replace('_',' ', $o['status']))?></td>
          <td><?php $d=$o['created_at'] ?? $o['order_date'] ?? ''; echo $d?date('d M Y',strtotime($d)):'—';?></td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="5">No recent orders</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </section>

</main>

<!-- ===== FOOTER ===== -->
<footer>
  <div class="footer-container">
    <div class="footer-col">
      <h4>📞 Contact Us</h4>
      <ul>
        <li><i class="fa fa-phone"></i> +91 76788 53017</li>
        <li><i class="fa fa-envelope"></i> support@rrbusiness.com</li>
        <li><i class="fa fa-envelope-open"></i> care@rrbusiness.com</li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>🔗 Quick Links</h4>
      <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="admin_pannel.php">Manage Products</a></li>
        <li><a href="view_order.php">Orders</a></li>
        <li><a href="customer_queries.php">Queries</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>📱 Follow Us</h4>
      <ul>
        <li><a href="https://www.facebook.com/profile.php?id=61577939099049" target="_blank"><i class="fab fa-facebook"></i> Facebook</a></li>
        <li><a href="https://www.instagram.com/rrbusiness2025" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
        <li><a href="https://wa.me/917678853017"><i class="fab fa-whatsapp" target="_blank"></i> WhatsApp</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <p>&copy; <?=date('Y')?> <strong>R.R. Business</strong> — All Rights Reserved</p>
    <p>🚀 Developed by <strong>V.G Technologies Pvt. Ltd.</strong></p>
  </div>
</footer>

<!-- ===== SCRIPTS ===== -->
<script>
/* ... other JS unchanged ... */
const initData = JSON.parse('<?=$initJS?>');
const ctx = document.getElementById('ordersChart');
let ordersChart;
function drawChart(d){
  if(ordersChart) ordersChart.destroy();
  ordersChart = new Chart(ctx,{
    type:'doughnut',
    data:{
      labels:['New','In Progress','Completed','Cancelled','Pending Queries','Queries Done'],
      datasets:[{data:[d.new_orders,d.in_progress,d.completed_orders,d.cancelled_orders,d.pending_queries,d.completed_queries],
                 backgroundColor:['#ff6b6b','#ffb703','#2a9d8f','#6c757d','#9c27b0','#03a9f4'],
                 hoverOffset:6}]
    },
    options:{plugins:{legend:{position:'bottom',labels:{usePointStyle:true}}}}
  });
}
drawChart(initData);

setInterval(()=>{
  fetch('dashboard.php?counts=1')
    .then(r=>r.json())
    .then(c=>{
      Object.entries(c).forEach(([k,v])=>{
        const el=document.getElementById(k);
        if(el){el.dataset.count=v;el.textContent=v;}
      });
      drawChart(c);
    }).catch(console.error);
},10000);
</script>
