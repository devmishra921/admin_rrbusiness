<?php
require 'db_connect.php';

/* ---------- ADD PRODUCT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
  $name  = trim($_POST['new_name']);
  $desc  = trim($_POST['new_description']);
  $price = (float)$_POST['new_price'];
  $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
  $stored  = [];
  $uploaddir = __DIR__ . '/images/';
  foreach ($_FILES['new_images']['tmp_name'] as $i => $tmp) {
    if ($i > 4) break;
    if ($_FILES['new_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
    $ext = strtolower(pathinfo($_FILES['new_images']['name'][$i], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) continue;
    $fname = uniqid('prod_', true) . "." . $ext;
    move_uploaded_file($tmp, $uploaddir . $fname);
    $stored[] = 'images/' . $fname;
  }
  $csv = implode(',', $stored);
  $stmt = $conn->prepare("INSERT INTO products(name,description,price,images) VALUES (?,?,?,?)");
  $stmt->bind_param('ssds', $name, $desc, $price, $csv);
  $stmt->execute();
  $stmt->close();
  header('Location: admin_pannel.php?msg=added');
  exit;
}

/* ---------- DELETE PRODUCT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
  $id = (int)$_POST['id'];
  $imgRes = $conn->query("SELECT images FROM products WHERE id=$id");
  if ($imgRes && $imgRes->num_rows > 0) {
    $imgs = explode(',', $imgRes->fetch_assoc()['images']);
    foreach ($imgs as $imgPath) {
      if (file_exists($imgPath)) unlink($imgPath);
    }
  }
  $conn->query("DELETE FROM products WHERE id=$id");
  header('Location: admin_pannel.php?msg=deleted');
  exit;
}

/* ---------- UPDATE PRODUCT ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  $id    = (int)$_POST['id'];
  $name  = trim($_POST['name']);
  $desc  = trim($_POST['description']);
  $price = (float)$_POST['price'];
  $extraCsv = '';

  if (!empty($_FILES['upd_images']['name'][0])) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $stored  = [];
    $uploaddir = __DIR__ . '/images/';
    foreach ($_FILES['upd_images']['tmp_name'] as $i => $tmp) {
      if ($i > 4) break;
      if ($_FILES['upd_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
      $ext = strtolower(pathinfo($_FILES['upd_images']['name'][$i], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed)) continue;
      $fname = uniqid('prod_', true) . "." . $ext;
      move_uploaded_file($tmp, $uploaddir . $fname);
      $stored[] = 'images/' . $fname;
    }
    if ($stored) {
      $old  = ($conn->query("SELECT images FROM products WHERE id=$id")->fetch_assoc()['images'] ?? '');
      $imgs = array_filter(array_merge(explode(',', $old), $stored));
      $extraCsv = implode(',', $imgs);
    }
  }

  if ($extraCsv) {
    $stmt = $conn->prepare("UPDATE products SET name=?,description=?,price=?,images=? WHERE id=?");
    $stmt->bind_param('ssdsi', $name, $desc, $price, $extraCsv, $id);
  } else {
    $stmt = $conn->prepare("UPDATE products SET name=?,description=?,price=? WHERE id=?");
    $stmt->bind_param('ssdi', $name, $desc, $price, $id);
  }
  $stmt->execute();
  $stmt->close();
  header('Location: admin_pannel.php?msg=updated');
  exit;
}

$result = $conn->query("SELECT * FROM products ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Products | RR Business</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
  <style>
    :root{
      --brand:#a83232;--accent:#ffcb6b;--bg:#f5f7fa;--card-bg:#fff;--shadow:0 8px 20px rgba(0,0,0,.08);--radius:18px;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Poppins',sans-serif;background:var(--bg);color:#333}
    header{position:sticky;top:0;z-index:999;display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:linear-gradient(120deg,var(--brand),#ff5d5d);color:#fff;box-shadow:var(--shadow)}
    header .logo{height:60px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3))}
    header h1 {
  font-size: 1.5rem;
  font-weight: 600;
  letter-spacing: 1px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, .3);
  margin: 0;
}
 .alert { background:#d4edda; color:#155724; padding:10px; border-radius:6px; text-align:center; max-width:600px; margin:20px auto; font-weight:500; display:none; }
    .update-btn { background:#28a745; color:#fff; padding:8px 16px; border:none; border-radius:4px; cursor:pointer; display:block; width:100%; margin-bottom:6px; }
    .delete-btn { background:#dc3545; color:#fff; padding:8px 16px; border:none; border-radius:4px; cursor:pointer; display:block; width:100%; }

#clock {
  font-family: 'Poppins', sans-serif;
  font-weight: 500;
}
    nav{background:#fff;box-shadow:var(--shadow)}
    nav ul{display:flex;flex-wrap:wrap;justify-content:center;gap:6px;list-style:none;padding:10px 12px;margin:0}
    nav a{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;font-weight:500;text-decoration:none;color:#333;transition:.3s}
    nav a:hover,nav a.active{background:var(--brand);color:#fff}
    main {
    width: calc(100% - 80px); /* full width with 40px margin left and right */
    margin: 40px auto;
    padding: 32px;
    border-radius: var(--radius);
    background: var(--card-bg);
    box-shadow: var(--shadow);
    }
    @media (max-width: 500px) {
    main {
    width: calc(100% - 30px);
    padding: 20px;
    }
    }
    h2{color:var(--brand);margin-bottom:18px}
    table{width:100%;border-collapse:collapse;margin-bottom:28px}
    th,td{padding:10px;border:1px solid #ddd;text-align:left}
    th{background:var(--brand);color:#fff}
    tr:nth-child(even){background:#fafafa}
    input,textarea{width:100%;padding:6px;border:1px solid #ccc;border-radius:4px;font-family:inherit}
    input[type=submit]{background:var(--brand);color:#fff;border:none;padding:8px 18px;border-radius:4px;cursor:pointer;transition:.3s}
    input[type=submit]:hover{background:#902020}
    .thumb-stack img{max-width:55px;border-radius:4px;margin-right:5px}
    .alert{background:#d4edda;color:#155724;padding:10px;border-radius:6px;margin:20px auto;text-align:center;max-width:600px;font-weight:500}footer {
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
}       .copyright,.developed-by{text-align:center;margin-top:26px;font-size:.8rem}
    .developed-by strong{color:var(--accent)}
    h2{
      text-align: center;
    }
  </style>
  <script>
    window.onload = function() {
      const msg = new URLSearchParams(window.location.search).get('msg');
      if (msg) {
        const alertBox = document.getElementById('alert-msg');
        if (msg === 'added') alertBox.textContent = '✔ Product added successfully';
        else if (msg === 'updated') alertBox.textContent = '✔ Product updated successfully';
        else if (msg === 'deleted') alertBox.textContent = '✔ Product deleted successfully';
        alertBox.style.display = 'block';
        setTimeout(() => alertBox.style.display = 'none', 3000);
      }
    }
  </script>
</head>
<body>
<header>
  <img src="images/Logo.png" alt="R.R. Business Logo" class="logo" />
  <h1>R.R. Business Admin Panel</h1>
  <span id="clock"></span>
</header>
<nav>
  <ul>
    <li><a href="dashboard.php"><i class="fa fa-chart-line"></i>Dashboard</a></li>
    <li><a class="active" href="admin_pannel.php"><i class="fa fa-box"></i>Products</a></li>
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

<!-- Success Message Div -->
<?php if(isset($_GET['msg'])): ?>
<div id="alert-msg" class="alert">
  ✔ 
  <?php
    if ($_GET['msg'] === 'added') echo 'New product added';
    elseif ($_GET['msg'] === 'updated') echo 'Product updated';
    elseif ($_GET['msg'] === 'deleted') echo 'Product deleted';
  ?>
</div>
<script>
  // Auto-hide success message after 3 seconds
  setTimeout(() => document.getElementById('alert-msg').style.display = 'none', 3000);
</script>
<?php endif; ?>
<main>
  <h2>Product List</h2>
  <table border="1" cellspacing="0" cellpadding="10">
  <tr><th>ID</th><th>Name</th><th>Description</th><th>Price</th><th>Image Upload</th><th>Action</th></tr>
  <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <form method="post" enctype="multipart/form-data">
        <td><?= $row['id'] ?><input type="hidden" name="id" value="<?= $row['id'] ?>"></td>
        <td><input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>"></td>
        <td><textarea name="description"><?= htmlspecialchars($row['description']) ?></textarea></td>
        <td><input type="number" step="0.01" name="price" value="<?= $row['price'] ?>"></td>
        <td><input type="file" name="upd_images[]" multiple></td>
        <td style="display:flex;flex-direction:column;gap:6px;">
          <input type="submit" name="update" value="Update" class="update-btn">
          <button type="submit" name="delete" class="delete-btn" onclick="return confirm('Delete this product?')">Delete</button>
        </td>
      </form>
    </tr>
  <?php endwhile; ?>
</table>ss

  <h2>Add New Product</h2>
  <form method="post" enctype="multipart/form-data">
    <table>
      <tr>
        <td><input type="text" name="new_name" placeholder="Product Name" required></td>
        <td><textarea name="new_description" placeholder="Description" required></textarea></td>
        <td><input type="number" step="0.01" name="new_price" placeholder="Price ₹" required></td>
        <td><input type="file" name="new_images[]" multiple required></td>
        <td><input type="submit" name="add" value="Add Product"></td>
      </tr>
    </table>
  </form>
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
<script>
  function updateClock() {
    document.getElementById('clock').textContent = new Date().toLocaleString();
  }
  updateClock();
  setInterval(updateClock, 1000);
</script>
</body>
</html>
<?php $conn->close(); ?>
