<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.html'); exit; }
require 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Generate Bill | R.R. Business</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet" />
<style>
:root {
  --brand: #a83232;
  --accent: #ffcb6b;
  --bg: #f5f7fa;
  --card-bg: #fff;
  --shadow: 0 10px 30px rgba(0,0,0,.08);
  --radius: 14px;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Inter', sans-serif;
  background: var(--bg);
  color: #333;
  line-height: 1.4;
}

/* ---------- HEADER + NAV ---------- */
header {
  background: linear-gradient(120deg, var(--brand), #ff5d5d);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  box-shadow: var(--shadow);
}
header .logo { height: 60px; filter: drop-shadow(0 2px 4px rgba(0,0,0,.3)); }
header h1 { font-size: 1.5rem; font-weight: 600; }
header #clock { font-family: 'Poppins', sans-serif; font-weight: 500; }

nav { background: #fff; box-shadow: var(--shadow); }
nav ul {
  display: flex; flex-wrap: wrap; justify-content: center;
  gap: 6px; list-style: none; padding: 10px 12px; max-width: 1200px; margin: auto;
}
nav a {
  display: flex; align-items: center; gap: 6px;
  padding: 8px 14px; border-radius: 10px; font-weight: 500;
  text-decoration: none; color: #333; transition: .3s;
}
nav a:hover, nav a.active { background: var(--brand); color: #fff; }

/* ---------- FORM CONTAINER ---------- */
.container {
  width: calc(100% - 80px); /* Full width with 40px margin on both sides */
  margin: 48px auto;
  padding: 40px;
  background: var(--card-bg);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  animation: fadeIn .6s ease;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(30px); }
  to   { opacity: 1; transform: translateY(0); }
}
h2.title {
  text-align: center;
  font-size: 1.6rem;
  margin-bottom: 32px;
  color: var(--brand);
  font-weight: 600;
}

/* ---------- FORM LAYOUT ---------- */
form {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px 28px;
}
form label {
  font-weight: 600;
  margin-bottom: 6px;
  display: block;
}
form input, form select {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
  width: 100%;
}
input[readonly] { background: #f0f0f0; font-weight: 600; }
form button {
  grid-column: span 2;
  padding: 14px;
  font-size: 1rem;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}
#calcBtn { background: #ff9800; color: #fff; }
#submitBtn { background: #4caf50; color: #fff; }
button:disabled { background: #bbb; cursor: not-allowed; }

/* ---------- FOOTER ---------- */
footer {
  background: rgb(143, 51, 51);
  color: #ccc;
  padding: 30px 20px;
  font-size: 0.95rem;
  margin-top: 96px;
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
.footer-bottom p { margin: 4px 0; }
</style>
</head>
<body>
<header>
  <img src="images/Logo.png" alt="R.R. Business Logo" class="logo" />
  <h1>R.R. Business - Generate Bill</h1>
  <span id="clock"></span>
</header>

<nav>
  <ul>
    <li><a href="dashboard.php"><i class="fa fa-chart-line"></i>Dashboard</a></li>
    <li><a href="admin_pannel.php"><i class="fa fa-box"></i>Products</a></li>
    <li><a href="inventory.php"><i class="fa fa-warehouse"></i>Inventory</a></li>
    <li><a class="active" href="generate_bill.php"><i class="fa fa-file-invoice"></i>Billing</a></li>
    <li><a href="reports.php"><i class="fa fa-chart-bar"></i>Report</a></li>
    <li><a href="view_order.php"><i class="fa fa-receipt"></i>Orders</a></li>
    <li><a href="admin_add_barcode.php"><i class="fa fa-barcode"></i>Barcode</a></li>
    <li><a href="customer_queries.php"><i class="fa fa-comments"></i>Queries</a></li>
    <li><a href="gallery_view.php"><i class="fa fa-image"></i>Gallery</a></li>
    <li><a href="logout.html"><i class="fa fa-sign-out-alt"></i>Logout</a></li>
  </ul>
</nav>

<div class="container">
  <h2 class="title">Generate Customer Bill</h2>
  <form method="POST" action="process_bill.php" id="billForm">
  <div>
    <label>Customer Name</label>
    <input type="text" name="customer_name" required />
  </div>
  <div>
    <label>Mobile Number</label>
    <input type="text" name="phone" pattern="[0-9]{10}" required />
  </div>
  <div style="grid-column: span 2;">
    <label>Address</label>
    <input type="text" name="address" required />
  </div>

  <!-- Product Dropdown -->
  <div>
    <label>Select Product</label>
    <select name="product_id" id="product" required>
      <option value="">-- Select Product --</option>
      <?php
        $rs = $conn->query("SELECT id,name,price FROM products");
        while($row = $rs->fetch_assoc()){
          $price = (float)$row['price'];
          echo "<option value='{$row['id']}' data-price='{$price}'>".
               htmlspecialchars($row['name'])."</option>\n";
        }
      ?>
    </select>
  </div>

  <div>
    <label>Quantity</label>
    <input type="number" id="quantity" name="quantity" value="1" min="1" required />
  </div>

  <div>
    <label>GST %</label>
    <select name="gst_percent" id="gst" required>
      <option value="">-- GST --</option>
      <option value="0">0%</option>
      <option value="5">5%</option>
      <option value="12">12%</option>
      <option value="18">18%</option>
      <option value="28">28%</option>
    </select>
  </div>

  <div>
    <label>Unit Price (₹)</label>
    <input type="text" id="unitPrice" readonly />
  </div>
  <div>
    <label>Amount (₹)</label>
    <input type="text" id="amount" name="amount" readonly />
  </div>
  <div>
    <label>GST Amount (₹)</label>
    <input type="text" id="gstAmt" name="gst_amount" readonly />
  </div>
  <div>
    <label>Total Amount (₹)</label>
    <input type="text" id="totalAmt" name="total_amount" readonly />
  </div>

  <div style="grid-column: span 2;">
    <button type="submit" id="submitBtn" disabled>Generate Bill</button>
  </div>
</form>



</div>

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

<script>
const form        = document.getElementById('billForm');
const prodSel     = document.getElementById('product');
const qtyInput    = document.getElementById('quantity');
const gstSel      = document.getElementById('gst');

const unitPriceFld= document.getElementById('unitPrice');
const amountFld   = document.getElementById('amount');
const gstFld      = document.getElementById('gstAmt');
const totalFld    = document.getElementById('totalAmt');
const submitBtn   = document.getElementById('submitBtn');

function num(v){
  const n = parseFloat(v);
  return isNaN(n) ? null : n;
}

function calc() {
  const opt = prodSel.selectedIndex > 0 ? prodSel.selectedOptions[0] : null;
  const unitPrice = opt ? num(opt.dataset.price) : null;
  const quantity  = num(qtyInput.value);
  const gstPct    = num(gstSel.value);

  if (unitPrice === null || quantity === null || quantity < 1) {
    unitPriceFld.value = amountFld.value = gstFld.value = totalFld.value = '';
    submitBtn.disabled = true;
    return false;
  }

  const amount = unitPrice * quantity;
  unitPriceFld.value = unitPrice.toFixed(2);
  amountFld.value    = amount.toFixed(2);

  if (gstPct !== null) {
    const gstAmt = amount * gstPct / 100;
    const total = amount + gstAmt;
    gstFld.value   = gstAmt.toFixed(2);
    totalFld.value = total.toFixed(2);
  } else {
    gstFld.value   = '';
    totalFld.value = '';
  }

  submitBtn.disabled = false;
  return true;
}

// Auto update on change
['change','input'].forEach(evt => {
  prodSel.addEventListener(evt, calc);
  qtyInput.addEventListener(evt, calc);
  gstSel.addEventListener(evt, calc);
});

// Final check before submit
form.addEventListener('submit', function (e) {
  if (!calc()) {
    e.preventDefault();
    alert('Please select valid product, quantity, and GST.');
  }
});
</script>
</body>
</html>
