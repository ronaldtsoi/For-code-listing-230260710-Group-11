<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../config/conn.php');

// Ensure the user is authenticated
if (!isset($_SESSION['verified_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in!']);
    exit();
}

try {
    $db = Database::getInstance();

    // Query to get the number of worksites
    $worksiteCountQuery = "SELECT COUNT(*) AS count FROM worksites";
    $worksiteCountResult = $db->query($worksiteCountQuery);
    $worksiteCount = $worksiteCountResult[0]['count'] ?? 0;

    // Query to get the number of login users
    $loginUsersCountQuery = "SELECT COUNT(*) AS count FROM check_in_record";
    $loginUsersCountResult = $db->query($loginUsersCountQuery);
    $loginUsersCount = $loginUsersCountResult[0]['count'] ?? 0;

    // Query to get the number of alerts
    $alertCountQuery = "SELECT COUNT(*) AS count FROM alert_records";
    $alertCountResult = $db->query($alertCountQuery);
    $alertCount = $alertCountResult[0]['count'] ?? 0;

    // Return the statistics as JSON
    echo json_encode([
        'success' => true,
        'data' => [
            'worksiteCount' => $worksiteCount,
            'loginUsersCount' => $loginUsersCount,
            'alertCount' => $alertCount,
        ]
    ]);
    exit();
} catch (Exception $e) {
    // Handle errors
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit();
}
?>