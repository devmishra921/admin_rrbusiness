<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = 'uploads/gallery/';
        $filename = basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $filename;

        // Check if file already exists
        if (file_exists($targetPath)) {
            $_SESSION['upload_error'] = "File already exists.";
        } else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $stmt = $conn->prepare("INSERT INTO gallery_photos (filename) VALUES (?)");
                $stmt->bind_param("s", $filename);
                if ($stmt->execute()) {
                    $_SESSION['upload_success'] = "Image uploaded successfully.";
                } else {
                    $_SESSION['upload_error'] = "Database error.";
                }
                $stmt->close();
            } else {
                $_SESSION['upload_error'] = "Failed to upload file.";
            }
        }
    } else {
        $_SESSION['upload_error'] = "Invalid file.";
    }
}

header("Location: gallery_view.php");
exit;
