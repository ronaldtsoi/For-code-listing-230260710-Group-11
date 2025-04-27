<?php
session_start();
require_once('../config/conn.php');

if (isset($_POST['update_user'])) {
    $userID = intval($_POST['user_id']); 
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phoneNumber = trim($_POST['phone_number']);
    $accountStatus = trim($_POST['account_status']);
    $userRole = trim($_POST['user_role']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (!empty($password) && $password !== $confirmPassword) {
        $_SESSION['status'] = "Passwords do not match.";
        header("Location: ../public/user-edit.php?id=" . $userID);
        exit();
    }

    try {
        $db = Database::getInstance();

        $query = "UPDATE users SET";
        $params = [];
        
        // If the field is not empty, update
        if (!empty($username)) {
            $query .= " username = ?,";
            $params[] = $username;
        }
        if (!empty($email)) {
            $query .= " email = ?,";
            $params[] = $email;
        }
        if (!empty($phoneNumber)) {
            $query .= " phone_number = ?,";
            $params[] = $phoneNumber;
        }
        if (!empty($password)) {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT); 
            $query .= " password_hash = ?,";
            $params[] = $passwordHash;
        }
        $query .= " account_status = ?, user_role = ? WHERE user_ID = ?";
        $params[] = $accountStatus;
        $params[] = $userRole;
        $params[] = $userID;

        // Remove extra commas
        $query = str_replace(", WHERE", " WHERE", $query);

        // Performing Updates
        $stmt = $db->prepare($query);
        $stmt->execute($params);

        $_SESSION['status'] = "User updated successfully!";
        header("Location: ../public/user-list.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['status'] = "Error updating user: " . $e->getMessage();
        header("Location: ../public/user-edit.php?id=" . $userID);
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request.";
    header("Location: ../public/user-list.php");
    exit();
}
?>