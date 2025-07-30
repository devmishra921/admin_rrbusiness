<?php
require 'db_connect.php';

// AJAX: mark a query completed
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='complete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("UPDATE message SET status='completed' WHERE id=?");
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    header('Content-Type: application/json');
    echo json_encode(['success'=>$ok]);
    exit;
}

// Fetch list for display
$list   = $conn->query("SELECT * FROM message ORDER BY query_date DESC");
$serial = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Customer Queries - RR Business</title>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
  --brand:#a83232;
  --accent:#ffcb6b;
  --bg:#f5f7fa;
  --card-bg:#fff;
  --radius:18px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:#333;line-height:1.45;overflow-x:hidden}

header{
  position:sticky;top:0;z-index:999;
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 24px;
  background:linear-gradient(120deg,var(--brand),#ff5d5d);
  color:#fff;box-shadow:0 10px 30px rgba(0,0,0,.08)
}
header .logo{height:60px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3))}
header h1{font-size:1.5rem;font-weight:600;letter-spacing:1px;text-shadow:0 1px 2px rgba(0,0,0,.3)}
header #clock{font-family:'Poppins',sans-serif;font-weight:500}

nav{background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.08)}
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
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;background:#fff;margin-top:20px;font-size:0.95rem}
th,td{border:1px solid #ccc;padding:8px;text-align:center}
th{background:var(--brand);color:#fff}
button{background:#28a745;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer}
button:hover{background:#218838}
.badge{color:green;font-weight:bold}

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
  <img src="images/Logo.png" alt="RR Business Logo" class="logo">
  <h1>R.R. Business - Customer Queries</h1>
  <span id="clock"></span>
</header>

<nav>
  <ul>
    <li><a href="dashboard.php"><i class="fa fa-chart-line"></i>Dashboard</a></li>
    <li><a href="admin_pannel.php"><i class="fa fa-box"></i>Products</a></li>
    <li><a href="inventory.php"><i class="fa fa-warehouse"></i>Inventory</a></li>
    <li><a href="generate_bill.php"><i class="fa fa-file-invoice"></i>Billing</a></li>
    <li><a href="reports.php"><i class="fa fa-chart-bar"></i>Report</a></li>
    <li><a href="view_order.php"><i class="fa fa-receipt"></i>Orders</a></li>
    <li><a href="admin_add_barcode.php"><i class="fa fa-barcode"></i>Barcode</a></li>
    <li><a class="active" href="customer_queries.php"><i class="fa fa-comments"></i>Queries</a></li>
    <li><a href="gallery_view.php"><i class="fa fa-image"></i>Gallery</a></li>
    <li><a href="logout.html"><i class="fa fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</nav>


<div class="container">
  <h2>All Customer Queries</h2>
  <div class="table-wrap">
    <table id="qTbl">
      <thead>
        <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Message</th><th>Date</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php while($r=$list->fetch_assoc()): ?>
        <tr data-id="<?= $r['id'] ?>">
          <td><?= $serial++ ?></td>
          <td><?= htmlspecialchars($r['customer_name']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td><?= htmlspecialchars($r['phone']) ?></td>
          <td style="text-align:left"><?= nl2br(htmlspecialchars($r['query_text'])) ?></td>
          <td><?= $r['query_date'] ?></td>
          <td class="status"><?= $r['status'] ?></td>
          <td class="act">
            <?php if($r['status']!=='completed'): ?>
              <button onclick="resolve(this)">Mark Completed</button>
            <?php else: ?>
              <span class="badge">✔</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function updateClock(){document.getElementById('clock').textContent=new Date().toLocaleString();}
updateClock(); setInterval(updateClock,1000);

function resolve(btn){
  const row = btn.closest('tr');
  const id  = row.dataset.id;
  fetch('customer_queries.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:new URLSearchParams({action:'complete',id})
  })
  .then(r=>r.json())
  .then(j=>{
     if(j.success){
       row.querySelector('.status').textContent='completed';
       row.querySelector('.act').innerHTML='<span class="badge">✔</span>';
     }else alert('Update failed!');
  });
}
</script>

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


</body>
</html>
<?php $conn->close(); ?>
