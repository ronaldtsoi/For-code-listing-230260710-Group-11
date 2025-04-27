<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/conn.php';

// Set headers
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = Database::getInstance();
        $input = json_decode(file_get_contents("php://input"), true);

        // Validate required parameters
        if (!$input || !isset($input['worsite_name'], $input['latitude'], $input['longitude'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing required parameters."]);
            exit;
        }

        $worksite_name = $input['worsite_name'];
        $latitude = $input['latitude'];
        $longitude = $input['longitude'];
        
        // Check if the worksite exists
        $query = "SELECT worksite_id FROM worksites WHERE worksite_name = ? AND latitude = ? AND longitude = ?";
        $worksite = $database->query($query, [$worksite_name, $latitude, $longitude]);

        if (!empty($worksite)) {
            // Worksite exists, fetch the worksite ID
            $worksite_id = $worksite[0]['worksite_id'];
    
            // Fetch the associated map image data
            $query = "SELECT question, option_a, option_b, option_c, correct_answer, image_path 
                      FROM map_images WHERE worksite_id = ?";
            $map_image = $database->query($query, [$worksite_id]);
    
            if (!empty($map_image)) {
                $base_url = "https://james.sl94.i.ng/";
                $map_image[0]['image_path'] = $base_url . ltrim($map_image[0]['image_path'], './');

                $response = [
                    "success" => true,
                    "data" => $map_image[0]
                ];
                echo json_encode($response);
            } else {
                // No map image data found
                http_response_code(404); // Not Found
                echo json_encode(["success" => false, "message" => "No map image data found for the given worksite."]);
            }
        } else {
            // Worksite not found
            http_response_code(404); // Not Found
            echo json_encode(["success" => false, "message" => "Worksite not found."]);
        }
    } catch (Exception $e) {
        // Handle unexpected errors
        http_response_code(500); // Internal Server Error
        echo json_encode(["success" => false, "message" => "An unexpected error occurred: " . $e->getMessage()]);
    }
}