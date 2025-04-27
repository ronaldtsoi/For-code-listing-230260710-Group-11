<?php
require_once('../config/conn.php');

// Function to fetch alert records (with pagination and search)
function getAllAlertRecord($limit, $offset, $search = '') {
    $db = Database::getInstance();
    $query = "SELECT 
        alert_records.id, 
        alert_records.user_id, 
        alert_types.type_name AS alert_type, 
        alert_records.alert_message, 
        alert_records.alert_time,
        alert_records.alert_end_time,  
        users.username
    FROM alert_records
    JOIN users ON alert_records.user_id = users.user_ID
    JOIN alert_types ON alert_records.alert_type_id = alert_types.id";

    // Add WHERE clause if search keyword is provided
    if (!empty($search)) {
        $query .= " WHERE 
            alert_types.type_name LIKE ? OR 
            users.username LIKE ? OR 
            alert_records.alert_message LIKE ?";
    }

    $query .= " LIMIT ? OFFSET ?";

    $stmt = $db->prepare($query);

    // Bind parameters
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $limit, $offset);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Function to get total alert record count (with search support)
function getAlertRecordCount($search = '') {
    $db = Database::getInstance();
    $query = "SELECT COUNT(*) AS total FROM alert_records
        JOIN users ON alert_records.user_id = users.user_ID
        JOIN alert_types ON alert_records.alert_type_id = alert_types.id";

    // Add WHERE clause if search keyword is provided
    if (!empty($search)) {
        $query .= " WHERE 
            alert_types.type_name LIKE ? OR 
            users.username LIKE ? OR 
            alert_records.alert_message LIKE ?";
    }

    $stmt = $db->prepare($query);

    // Bind parameters
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}
?>