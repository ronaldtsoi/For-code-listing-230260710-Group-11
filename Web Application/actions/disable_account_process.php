<?php
session_start();
require_once '../config/conn.php';

if (isset($_POST['disable_account_btn'])) {
    try {
        $database = Database::getInstance();

        $targetUserId = intval($_POST['disable_account_btn']);
        $currentUserId = $_SESSION['verified_user_id'];

        // Ensure the target user ID is valid
        if ($targetUserId  <= 0) {
            throw new Exception("Invalid user ID.");
        }

        // Prevent users from disabling their own account
        if ($targetUserId === $currentUserId) {
            $_SESSION['status'] = "You cannot disable your own account.";
            header("Location: ../public/user-list.php");
            exit();
        }
        
        // Check if the account is already disabled
        $checkQuery = "SELECT account_status FROM users WHERE user_ID = ?";
        $result = $database->query($checkQuery, [$targetUserId]);

        if (empty($result)) {
            throw new Exception("User not found.");
        }

        // Check the current status of the account
        $accountStatus = $result[0]['account_status'];
        if ($accountStatus === 'Disable') {
            $_SESSION['status'] = "This account is already disabled.";
            header("Location: ../public/user-list.php");
            exit();
        }

        // Update the account status to 'Disable' in the database
        $query = "UPDATE users SET account_status = 'Disable' WHERE user_ID = ?";
        $database->query($query, [$targetUserId]);
        $_SESSION['status'] = "User account disabled successfully!";
        header("Location: ../public/user-list.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['status'] = "Error: " . $e->getMessage();
        header("Location: ../public/user-list.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request!";
    header("Location: ../public/user-list.php");
    exit();
}