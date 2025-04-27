<?php
session_start();
require_once '../config/conn.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $database = Database::getInstance();

        // Get parameters from query string
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
        $date = isset($_GET['date']) ? $_GET['date'] : null;
        $worksite_name = isset($_GET['worksite_name']) ? $_GET['worksite_name'] : null;

        if ($user_id <= 0) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Invalid or missing user_id"
            ]);
            exit;
        }

        // Base query with join
        $query = "SELECT w.worksite_name, c.checkIn_at 
                  FROM check_in_record c
                  JOIN worksites w ON c.worksite_id = w.worksite_id
                  WHERE c.user_id = ?";
        $params = [$user_id];

        // Add date filters if provided
        if ($date) {
            $query .= " AND DATE(c.checkIn_at) = ?";
            $params[] = $date;  // 'YYYY-MM-DD' format
        }
        // Add worksite name filter if provided
        if ($worksite_name) {
            $query .= " AND w.worksite_name LIKE ?";
            $params[] = '%' . $worksite_name . '%';
        }

        $query .= " ORDER BY c.checkIn_at DESC";

        // Prepare and execute the query
        $stmt = $database->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $database->conn->error);
        }

        // Dynamically bind parameters
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } elseif (is_string($param)) {
                $types .= 's';
            } else {
                $types .= 'b';
            }
        }
        $stmt->bind_param($types, ...$params);

        // Execute the query
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $records = $result->fetch_all(MYSQLI_ASSOC);

        if ($records) {
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Check-in history fetched successfully",
                "count" => count($records),
                "data" => $records
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "No check-in records found"
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Check-in History API Error: " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Internal server error",
            "error" => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
}
?>