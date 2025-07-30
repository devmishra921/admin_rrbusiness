<?php
// db_connect.php
$host = 'db4free.net';
$user = 'rradmin';           // your MySQL username
$pass = 'RrBiz@2025';        // your password
$db   = 'rrbusinessdb';      // your database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>
