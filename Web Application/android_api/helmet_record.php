<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/conn.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    if (!is_array($data) || !isset($data['user_id'], $data['statusList'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON format or missing required fields."
        ]);
        exit();
    }

    $user_id = intval($data['user_id']);
    $statusList = $data['statusList'];

    try {
        $database = Database::getInstance();
        $db = $database->prepare("INSERT INTO alert_records 
            (user_id, alert_type_id, alert_message, alert_time, alert_end_time) 
            VALUES (?, ?, ?, ?, ?)");

        $alert_type_id = 1;
        $alert_message = "Strap not fastened";

        $hasRecord = false;
        $startTime = null;
        $endTime = null;

        foreach ($statusList as $index => $record) {
            $strapFastened = $record['strapFastened'];
            $alert_time = $record['timestamp'];
        
            if (!$strapFastened) {
                if (!$hasRecord) {
                    $startTime = $alert_time;
                    $hasRecord = true;
                }
            } else {
                if ($hasRecord) {
                    $endTime = $alert_time;
                    $db->bind_param("iisss", $user_id, $alert_type_id, $alert_message, $startTime, $endTime);
                    $db->execute();
                    $hasRecord = false;
                    $startTime = null;
                    $endTime = null;
                }
            }
        
            if ($index === count($statusList) - 1 && $hasRecord) {
                $endTime = $alert_time;
                $db->bind_param("iisss", $user_id, $alert_type_id, $alert_message, $startTime, $endTime);
                $db->execute();
            }
        }

        $db->close();
        echo json_encode([
            "success" => true,
            "message" => "Alerts saved"
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Helmet Record Error: " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Internal server error."
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Unsupported request method"
    ]);
}