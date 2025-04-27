<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/conn.php';

try {
    // Get the JSON payload from the request body
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        http_response_code(400); // Bad Request
        echo json_encode([
            "success" => false,
            "message" => "Invalid or missing JSON payload."
        ]);
        exit;
    }

    // Validate required fields
    if (!isset($input['checkInAt']) || 
        !isset($input['latitude']) || 
        !isset($input['longitude']) || 
        !isset($input['userId']) || 
        !isset($input['worksite_name'])) {
        http_response_code(400); // Bad Request
        echo json_encode([
            "success" => false,
            "message" => "Missing required fields: 'userId', 'worksite_name', 'checkInAt', 'latitude', or 'longitude'."
        ]);
        exit;
    }

    // Extract data from the input
    $userId = $input['userId'];
    $worksiteName = $input['worksite_name'];
    $checkInAt = $input['checkInAt'];
    $latitude = $input['latitude'];
    $longitude = $input['longitude'];

    // Get database instance
    $db = Database::getInstance();

    // Find the worksite_id, latitude, and longitude for the given worksite_name
    $query = "SELECT worksite_id, latitude, longitude FROM worksites WHERE worksite_name = ?";
    $worksite = $db->query($query, [$worksiteName]);

    if (empty($worksite)) {
        http_response_code(404); // Not Found
        echo json_encode([
            "success" => false,
            "message" => "Worksite not found."
        ]);
        exit;
    }

    // Get the worksite details from the query result
    $worksiteId = $worksite[0]['worksite_id'];
    $dbLatitude = $worksite[0]['latitude'];
    $dbLongitude = $worksite[0]['longitude'];

    // Validate latitude and longitude
    if (abs($latitude - $dbLatitude) > 0.0001 || abs($longitude - $dbLongitude) > 0.0001) {
        http_response_code(400); // Bad Request
        echo json_encode([
            "success" => false,
            "message" => "The provided latitude and longitude do not match the worksite location."
        ]);
        exit;
    }

    // Check if the user already has a record for today
    $dateOnly = explode(' ', $checkInAt)[0]; // Extract the date (YYYY-MM-DD)
    $checkQuery = "SELECT id FROM check_in_record WHERE user_id = ? AND DATE(checkIn_at) = ?";
    $existingRecord = $db->query($checkQuery, [$userId, $dateOnly]);

    if (!empty($existingRecord)) {
        http_response_code(409); // Conflict
        echo json_encode([
            "success" => false,
            "message" => "You have already checked in today."
        ]);
        exit;
    }

    // Insert the check-in record into the check_in_record table
    $insertQuery = "INSERT INTO check_in_record (user_id, worksite_id, checkIn_at) VALUES (?, ?, ?)";
    $db->query($insertQuery, [$userId, $worksiteId, $checkInAt]);

    // Get the last inserted ID (check-in record ID)
    $lastInsertId = $db->lastInsertId();

    // Respond with success
    http_response_code(201); // Created
    echo json_encode([
        "success" => true,
        "message" => "Check-in record uploaded successfully.",
        "check_in_record_id" => $lastInsertId
    ]);
} catch (Exception $e) {
    // Handle any errors
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "success" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}