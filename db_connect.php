<?php
// db_connect.php
$host = 'db4free.net';
$user = 'rr_business';           // your MySQL username
$pass = 'RrBiz@2025';        // your password
$db   = 'rrbusinessdb';      // your database name
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
