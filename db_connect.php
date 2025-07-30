<?php
// db_connect.php
$host = 'localhost';
$user = 'root';
$pass = '';          // ← your MySQL password
$db   = 'rr_business';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
