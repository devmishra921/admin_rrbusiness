<?php
session_start();

// ✅ Hardcoded credentials
$validID = 'admin';
$validPass = '1234';

// ✅ POST data from JS fetch
$user = $_POST['username'] ?? '';
$pass = $_POST['password'] ?? '';

// ✅ Compare
if ($user === $validID && $pass === $validPass) {
    $_SESSION['admin_logged'] = true;
    echo 'success';
} else {
    echo 'invalid';
}
?>
