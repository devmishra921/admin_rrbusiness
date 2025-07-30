<?php
session_start();
require 'db_connect.php';

// Handle status transitions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $orderId = (int)$_POST['order_id'];
    $action  = $_POST['action'];

    if ($action === 'allocate') {
        $conn->query("UPDATE orders SET status='in_progress' WHERE id=$orderId");
    } elseif ($action === 'complete') {
        $conn->query("UPDATE orders SET status='completed' WHERE id=$orderId");
    } elseif ($action === 'cancel' && isset($_POST['reason'])) {
        $reason = $conn->real_escape_string(trim($_POST['reason']));
        $conn->query("UPDATE orders SET status='cancelled', cancel_reason='$reason' WHERE id=$orderId");
    }
    header('Location: view_order.php');
    exit;
}

// Fetch all orders
$result = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");

// Fetch cancelled order count
$cancel_result = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='cancelled'");
$cancel_row = $cancel_result->fetch_assoc();
$cancelledOrders = $cancel_row['total'] ?? 0;
?>

<!-- HTML START -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RR Business | Orders</title>
  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    :root {
      --brand:#a83232;
      --accent:#ffcb6b;
      --bg:#f5f7fa;
      --card-bg:rgba(255,255,255,0.8);
      --glass:rgba(255,255,255,0.6);
      --shadow:0 10px 30px rgba(0,0,0,.08);
      --radius:18px;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:var(--bg);color:#333;line-height:1.45;overflow-x:hidden}
    header{position:sticky;top:0;z-index:999;display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:linear-gradient(120deg,var(--brand),#ff5d5d);color:#fff;box-shadow:var(--shadow)}
    header .logo{height:60px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3))}
    header h1{font-size:1.5rem;font-weight:600;letter-spacing:1px;text-shadow:0 1px 2px rgba(0,0,0,.3)}
    #clock{font-family:'Poppins',sans-serif;font-weight:500}
    nav{background:#fff;box-shadow:var(--shadow)}
    nav ul{display:flex;flex-wrap:wrap;justify-content:center;gap:6px;list-style:none;padding:10px 12px;max-width:1200px;margin:auto}
    nav a{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;font-weight:500;text-decoration:none;color:#333;transition:.3s}
    nav a:hover,nav a.active{background:var(--brand);color:#fff}
    .container {
      width: calc(100% - 80px);
      margin: 48px auto;
      padding: 40px;
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      animation: fadeIn .6s ease;
    }
    @keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
    .orders-table{width:100%;border-collapse:collapse;margin-top:24px;font-size:.95rem}
    .orders-table th{background:var(--brand);color:#fff;padding:12px;border-top-left-radius:8px;border-top-right-radius:8px}
    .orders-table td{padding:12px;border-bottom:1px solid #eee;text-align:center}
    .orders-table tr:nth-child(even){background:#fafafa}
    .badge{padding:4px 8px;border-radius:4px;color:#fff;font-size:13px}
    .new{background:#ff6b6b}.prog{background:#ffb703}.done{background:#2a9d8f}.cancel{background:#6c757d}
    button{background:#28a745;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;transition:.3s}
    button:hover{background:#1e7e34}
    footer {background:rgb(143, 51, 51);color: #ccc;padding: 30px 20px 30px;font-size: 0.95rem;margin-top: 80px;}
    .footer-container {display: flex;justify-content: space-between;flex-wrap: wrap;max-width: 1200px;margin: auto;gap: 60px;}
    .footer-col {flex: 1 1 50px;min-width: 240px;}
    .footer-col h4 {color: #ffcb6b;margin-bottom: 16px;font-size: 1.1rem;}
    .footer-col ul {list-style: none;padding: 0;}
    .footer-col ul li {margin-bottom: 10px;display: flex;align-items: center;gap: 6px;}
    .footer-col a {color: #ccc;text-decoration: none;transition: 0.3s;}
    .footer-col a:hover {color: #fff;text-shadow: 0 0 4px #fff;}
    .footer-bottom {text-align: center;margin-top: 40px;font-size: 0.9rem;color: #aaa;}
    .footer-bottom p {margin: 4px 0;}
    .copyright,.developed-by{text-align:center;margin-top:28px;font-size:.8rem}
    .developed-by strong{color:var(--accent)}
    @media(max-width:600px){header h1{font-size:1.25rem}}
  </style>
</head>
<body>
<header><img src="images/Logo.png" alt="R.R. Business Logo" class="logo" /><h1>R.R. Business - Orders</h1><span id="clock"></span></header>
<nav><ul><li><a href="dashboard.php"><i class="fa fa-chart-line"></i>Dashboard</a></li><li><a href="admin_pannel.php"><i class="fa fa-box"></i>Products</a></li><li><a href="inventory.php"><i class="fa fa-warehouse"></i>Inventory</a></li><li><a href="generate_bill.php"><i class="fa fa-file-invoice"></i>Billing</a></li><li><a href="reports.php"><i class="fa fa-chart-bar"></i>Report</a></li><li><a class="active" href="view_order.php"><i class="fa fa-receipt"></i>Orders</a></li><li><a href="admin_add_barcode.php"><i class="fa fa-barcode"></i>Barcode</a></li><li><a href="customer_queries.php"><i class="fa fa-comments"></i>Queries</a></li><li><a href="gallery_view.php"><i class="fa fa-image"></i>Gallery</a></li><li><a href="logout.html"><i class="fa fa-sign-out-alt"></i>Logout</a></li></ul></nav>
<main class="container">
<h2 style="text-align:center;font-size:1.6rem;font-weight:600">Customer Orders</h2>
<div style="text-align: right; margin: 10px;">
  <a href="download_orders.php" class="btn btn-success" target="_blank">Download All Orders</a>
</div>
<table class="orders-table">
<thead>
<tr><th>#</th><th>Customer</th><th>Phone</th><th>Product</th><th>Qty</th><th>Address</th><th>Payment</th><th>TXN ID</th><th>Status</th><th>Action</th></tr>
</thead>
<tbody>
<?php
if ($result && $result->num_rows):
  $serial = 1;
  while($row = $result->fetch_assoc()):
    $badgeClass = $row['status']=='new' ? 'new' : ($row['status']=='in_progress' ? 'prog' : ($row['status']=='completed' ? 'done' : 'cancel'));
    $badgeText  = ucfirst(str_replace('_', ' ', $row['status']));
?>
<tr>
<td><?= $serial++ ?></td>
<td><?= htmlspecialchars($row['customer_name']) ?></td>
<td><?= htmlspecialchars($row['phone']) ?></td>
<td><?= htmlspecialchars($row['product_name']) ?></td>
<td><?= (int)$row['quantity'] ?></td>
<td><?= htmlspecialchars($row['address']) ?></td>
<td><?= ($row['payment_status'] == 1) ? '✅ Paid' : '❌ Unpaid' ?></td>
<td><?= htmlspecialchars($row['transaction_id'] ?? '-') ?></td>
<td><span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span></td>
<td>
<?php if($row['status']==='new'): ?>
  <form method="post" style="margin:4px 0;">
    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
    <input type="hidden" name="action" value="allocate">
    <button type="submit">Allocate</button>
  </form>
  <form method="post" style="margin:4px 0;">
    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
    <input type="hidden" name="action" value="cancel">
    <input type="text" name="reason" placeholder="Reason" required>
    <button type="submit" style="background:#d9534f;">Cancel</button>
  </form>

<?php elseif($row['status']==='in_progress'): ?>
  <form method="post" style="margin:4px 0;">
    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
    <input type="hidden" name="action" value="complete">
    <button type="submit">Mark Completed</button>
  </form>
  <form method="post" style="margin:4px 0;">
    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
    <input type="hidden" name="action" value="cancel">
    <input type="text" name="reason" placeholder="Reason" required>
    <button type="submit" style="background:#d9534f;">Cancel</button>
  </form>

<?php elseif($row['status']==='cancelled'): ?>
  ❌ Cancelled<br><small><?= htmlspecialchars($row['cancel_reason']) ?></small>

<?php elseif($row['status']==='completed'): ?>
  ✅ Completed

<?php else: ?>
  ⚠️ Unknown
<?php endif; ?>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="10">No orders found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</main>
<footer>
<div class="footer-container">
<div class="footer-col">
<h4>📞 Contact Us</h4>
<ul><li><i class="fa fa-phone"></i> +91 76788 53017</li><li><i class="fa fa-envelope"></i> support@rrbusiness.com</li><li><i class="fa fa-envelope-open"></i> care@rrbusiness.com</li></ul>
</div>
<div class="footer-col">
<h4>🔗 Quick Links</h4>
<ul><li><a href="dashboard.php"> Dashboard</a></li><li><a href="admin_pannel.php"> Manage Products</a></li><li><a href="view_order.php"> Orders</a></li><li><a href="customer_queries.php"> Queries</a></li></ul>
</div>
<div class="footer-col">
<h4>📱 Follow Us</h4>
<ul><li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li><li><a href="https://www.instagram.com/rrbusiness2025" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li><li><a href="https://wa.me/917678853017"><i class="fab fa-whatsapp"></i> WhatsApp</a></li></ul>
</div>
</div>
<div class="footer-bottom">
<p>&copy; <?=date('Y')?> <strong>R.R. Business</strong> — All Rights Reserved</p>
<p>🚀 Developed by <strong>V.G Technologies Pvt. Ltd.</strong></p>
</div>
</footer>
<script>
function updateClock(){document.getElementById('clock').textContent = new Date().toLocaleString();}
updateClock();setInterval(updateClock, 1000);
</script>
</body>
</html>
<?php $conn->close(); ?>
