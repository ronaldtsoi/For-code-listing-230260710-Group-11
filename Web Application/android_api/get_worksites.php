<?php
session_start();
require_once '../config/conn.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $database = Database::getInstance();

        // Query to fetch all worksites
        $query = "SELECT worksite_name, latitude, longitude 
                  FROM worksites 
                  ORDER BY worksite_name DESC";
        $worksites = $database->query($query);

        if (!empty($worksites)) {
            // Format coordinates as numbers
            $formattedWorksites = array_map(function($worksite) {
                return [
                    'name' => $worksite['worksite_name'],
                    'coordinates' => [
                        'lat' => (float)$worksite['latitude'],
                        'lng' => (float)$worksite['longitude']
                    ]
                ];
            }, $worksites);

            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Worksites data fetched successfully",
                "count" => count($worksites),
                "data" => $formattedWorksites
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "No worksites found"
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Worksites API Error: " . $e->getMessage());
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
