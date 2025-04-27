<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['verified_user_id'])) {
    $_SESSION['status'] = "Please login first!";
    header("Location: ../public/login.php");
    exit();
}
?>

<!-- include('../functions/auth_check.php'); -->