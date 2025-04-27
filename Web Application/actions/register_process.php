<?php
session_start();
require_once '../config/conn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($username) || empty($phone_number) || empty($email) || empty($password)) {
        $_SESSION['status'] = "All fields are required!";
        $_SESSION['status_type'] = "error";
        header("Location: ../public/register.php");
        exit();
    }

    $db = Database::getInstance();

    // Check if the email address is already registered
    $existingUser = $db->query("SELECT user_ID FROM users WHERE email = ?", [$email]);
    if (!empty($existingUser)) {
        $_SESSION['status'] = "This email address has been registered!";
        $_SESSION['status_type'] = "error";
        $_SESSION['highlight_email'] = true;
        header("Location: ../public/register.php");
        exit();
    }

    // Encrypt password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user data
    $query = "INSERT INTO users (username, phone_number, email, password_hash) VALUES (?, ?, ?, ?)";
    $db->query($query, [$username, $phone_number, $email, $hashed_password]);

    $_SESSION['status'] = "Registration successful! Redirecting to home...";
    $_SESSION['status_type'] = "success";
    $_SESSION['success_redirect'] = true; // Set redirect flag
    header("Location: ../public/register.php");
    exit();
} else {
    $_SESSION['status'] = "Invalid request method!";
    $_SESSION['status_type'] = "error";
    header("Location: ../public/register.php");
    exit();
}
?>