<?php
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_secure', '0'); // या 1 अगर HTTPS use कर रहे हो
session_start(); // ✅ सबसे पहले
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($username === 'admin' && $password === '1234') {
    $_SESSION['admin_id'] = 'admin'; // ✅ session सेट हो रहा है
    echo 'success';
} else {
    echo 'fail';
}