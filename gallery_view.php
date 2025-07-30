<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.html");
    exit;
}
require 'db_connect.php';

/* ---------- CONFIG ---------- */
$uploadDir = 'uploads/gallery/';
$maxSize   = 5 * 1024 * 1024;
$allowed   = ['image/jpeg','image/png','image/gif','image/webp'];

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

function cleanFile($name){
    return preg_replace('/[^a-z0-9\-_\.]/i','_', strtolower(pathinfo($name, PATHINFO_FILENAME)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = mysqli_real_escape_string($conn, trim($_POST['caption'] ?? ''));
    if ($caption === '') $caption = 'Untitled';

    if (empty($_FILES['images']['name'][0])) {
        $error = "Please select at least one image!";
    } else {
        $saved = [];
        $group_id = time(); // unique group ID to keep 3 images in one set

        foreach ($_FILES['images']['name'] as $i => $orig) {
            if ($_FILES['images']['error'][$i] !== 0) {
                $error = "Error in $orig (code " . $_FILES['images']['error'][$i] . ")";
                continue;
            }
            if ($_FILES['images']['size'][$i] > $maxSize) {
                $error = "$orig is larger than 5MB.";
                continue;
            }
            if (!in_array($_FILES['images']['type'][$i], $allowed)) {
                $error = "$orig skipped (invalid type)";
                continue;
            }

            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $base = cleanFile($orig);
            $new = $group_id . '_' . uniqid() . '_' . $base . '.' . $ext;

            if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadDir . $new)) {
                $stmt = $conn->prepare("INSERT INTO gallery_photos (caption, image_path, group_id) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssi", $caption, $new, $group_id);
                    $stmt->execute();
                    $saved[] = $new;
                } else {
                    $error = "DB Error: " . $conn->error;
                }
            }
        }

        if ($saved) {
            $success = count($saved) . " image(s) uploaded!";
        } else {
            $error = $error ?? "Upload failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Gallery View | Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #f5f7fa; margin: 0; }
header { display: flex; align-items: center; justify-content: space-between; padding: 14px 24px; background: linear-gradient(120deg, #a83232, #ff5d5d); color: #fff; }
header .logo { height: 60px; }
nav { background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,.08); }
nav ul { display: flex; justify-content: center; list-style: none; padding: 10px; flex-wrap: wrap; gap: 10px; }
nav a { text-decoration: none; padding: 10px 16px; color: #333; font-weight: 500; border-radius: 8px; transition: 0.3s; }
nav a:hover, nav a.active { background: #a83232; color: white; }

.container { max-width: 1200px; margin: 30px auto; padding: 0 50px; }
.card { background: #fff; padding: 30px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.08); }

.gallery-grid { display: none; }
footer { background: #a83232; color: #ccc; padding: 30px 20px; font-size: 0.95rem; margin-top: 60px; }
.footer-container { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 40px; max-width: 1200px; margin: auto; }
.footer-col h4 { color: #ffcb6b; margin-bottom: 16px; font-size: 1.1rem; }
.footer-col ul { list-style: none; padding: 0; }
.footer-col li { margin-bottom: 10px; }
.footer-col a { color: #ccc; text-decoration: none; transition: 0.3s; }
.footer-col a:hover { color: white; }
.footer-bottom { text-align: center; margin-top: 30px; font-size: 0.9rem; color: #aaa; }
</style>
</head>
<body>

<header>
  <img src="images/Logo.png" class="logo" alt="Logo">
  <h1>R.R. Business – Gallery</h1>
  <span id="clock"></span>
</header>

<nav><ul>
  <li><a href="dashboard.php">Dashboard</a></li>
  <li><a href="admin_pannel.php">Products</a></li>
  <li><a href="inventory.php">Inventory</a></li>
  <li><a href="generate_bill.php">Billing</a></li>
  <li><a href="reports.php">Reports</a></li>
  <li><a href="view_order.php">Orders</a></li>
  <li><a href="admin_add_barcode.php">Barcode</a></li>
  <li><a href="customer_queries.php">Queries</a></li>
  <li><a class="active" href="gallery_view.php">Gallery</a></li>
  <li><a href="logout.html">Logout</a></li>
</ul></nav>

<div class="container">
  <h2 class="text-center mb-4">📸 Add Gallery Images</h2>

  <?php if(isset($success)): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <div class="card">
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Caption</label>
        <input type="text" class="form-control" name="caption" placeholder="Enter caption (optional)">
      </div>
      <div class="mb-3">
        <label class="form-label">Select Images*</label>
        <input type="file" class="form-control" name="images[]" multiple required accept="image/*">
      </div>
      <button type="submit" class="btn btn-danger w-100"><i class="fa fa-upload"></i> Upload Photo(s)</button>
    </form>
  </div>
</div>

<footer>
  <div class="footer-container">
    <div class="footer-col"><h4>📞 Contact Us</h4>
      <ul>
        <li><i class="fa fa-phone"></i> +91 76788 53017</li>
        <li><i class="fa fa-envelope"></i> support@rrbusiness.com</li>
        <li><i class="fa fa-envelope-open"></i> care@rrbusiness.com</li>
      </ul>
    </div>
    <div class="footer-col"><h4>🔗 Quick Links</h4>
      <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="admin_pannel.php">Manage Products</a></li>
        <li><a href="view_order.php">Orders</a></li>
        <li><a href="customer_queries.php">Queries</a></li>
      </ul>
    </div>
    <div class="footer-col"><h4>📱 Follow Us</h4>
      <ul>
        <li><a href="https://www.facebook.com/profile.php?id=61577939099049" target="_blank"><i class="fab fa-facebook"></i> Facebook</a></li>
        <li><a href="https://www.instagram.com/rrbusiness2025" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
        <li><a href="https://wa.me/917678853017" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    &copy; <?=date('Y')?> R.R. Business — All Rights Reserved<br>
    🚀 Developed by <strong>V.G Technologies Pvt. Ltd.</strong>
  </div>
</footer>

<script>
const clk=()=>document.getElementById('clock').innerText=new Date().toLocaleString();
setInterval(clk,1000);clk();
</script>
</body>
</html>
