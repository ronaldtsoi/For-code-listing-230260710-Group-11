<?php
session_start();
require_once '../config/conn.php';

if (!isset($_POST['login_btn'])) {
    $_SESSION['status'] = "Unauthorized Access!";
    header("Location: ../public/login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    $_SESSION['status'] = "All fields are required!";
    header("Location: ../public/login.php");
    exit();
}

try {
    $db = Database::getInstance();

    // Fetch user details
    $query = "SELECT user_ID, username, password_hash, user_role FROM users WHERE email = ?";
    $user = $db->query($query, [$email]);

    if (empty($user)) {
        $_SESSION['status'] = "No account found with this email!";
        header("Location: ../public/login.php");
        exit();
    }

    $user = $user[0];

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        $_SESSION['status'] = "Invalid email or password!";
        header("Location: ../public/login.php");
        exit();
    }

    // Check user role
    if ($user['user_role'] !== 'admin') {
        $_SESSION['status'] = "Access denied. Only admins can log in.";
        header("Location: ../public/login.php");
        exit();
    }
    
    // Check if the account is disabled
    if ($user['account_status'] === 'Disable') {
        $_SESSION['status'] = "Your account is disabled. Please contact the administrator.";
        header("Location: ../public/login.php");
        exit();
    }

    // Update user's last login timestamp
    $db->query("UPDATE users SET updated_at = NOW() WHERE user_ID = ?", [$user['user_ID']]);

    // Set session variables for the logged-in admin
    $_SESSION['verified_user_id'] = $user['user_ID'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['status'] = "Login successful! Welcome to the admin dashboard.";
    header("Location: ../public/home.php");
    exit();

} catch (Exception $e) {
    $_SESSION['status'] = "Something went wrong! Please try again.";
    header("Location: ../public/login.php");
    exit();
}
?>