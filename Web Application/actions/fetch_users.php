<?php
require_once('../config/conn.php');

function getAllUsers($search = '', $limit = 10, $offset = 0) {
    $db = Database::getInstance();
    $query = "SELECT user_ID, username, email, phone_number, account_status, user_role, created_at, updated_at FROM users";

    // Add a WHERE clause if a search term is provided
    if (!empty($search)) {
        $query .= " WHERE username LIKE ? OR email LIKE ? OR phone_number LIKE ?";
        $query .= " LIMIT ? OFFSET ?";
        $stmt = $db->prepare($query);
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
    } else {
        $query .= " LIMIT ? OFFSET ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getUserByID($userID) {
    $db = Database::getInstance();
    $query = "SELECT user_ID, username, email, phone_number, account_status, user_role, created_at, updated_at FROM users WHERE user_ID = ?";
    $result = $db->query($query, [$userID]);
    return $result ? $result[0] : null;
}

function getTotalUsers($search = '') {
    $db = Database::getInstance();
    $query = "SELECT COUNT(*) as total FROM users";

    if (!empty($search)) {
        $query .= " WHERE username LIKE ? OR email LIKE ? OR phone_number LIKE ?";
        $stmt = $db->prepare($query);
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    } else {
        $stmt = $db->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}
?>