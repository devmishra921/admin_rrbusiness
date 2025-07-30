<?php
// db_connect.php
$host = 'sql110.infinityfree.com';
$user = 'if0_39236898';
$pass = 'Rrbusiness2025';
$db   = 'if0_39236898_rrbusiness';  // जो तूने बनाया है

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
