<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.html");
    exit;
}
require 'db_connect.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $desc = $_POST['description'];
    $image = $_FILES['image'];

    if ($image['error'] == 0) {
        $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
        $newName = 'gallery_' . time() . '.' . $ext;
        $target = 'uploads/gallery/' . $newName;

        if (move_uploaded_file($image['tmp_name'], $target)) {
            $stmt = $conn->prepare("INSERT INTO gallery (image, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $newName, $desc);
            $stmt->execute();
            $msg = "Photo added successfully!";
        } else {
            $msg = "Image upload failed.";
        }
    } else {
        $msg = "Image not selected or has error.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Add Gallery Photo</title>
  <link rel="stylesheet" href="your-style.css"> <!-- include your CSS -->
</head>
<body>
<?php include "header.php"; ?>
<h2>Add Photo to Gallery</h2>
<form method="post" enctype="multipart/form-data">
  <label>Photo:</label><br>
  <input type="file" name="image" required><br><br>
  
  <label>Description:</label><br>
  <textarea name="description" rows="4" cols="50"></textarea><br><br>
  
  <button type="submit">Add to Gallery</button>
</form>
<p style="color:green;"><?= $msg ?></p>
<?php include "footer.php"; ?>
</body>
</html>
