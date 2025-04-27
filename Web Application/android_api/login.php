<?php
session_start();
require_once '../config/conn.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    // Input validation
    if (!isset($input['email']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Please enter your email and password"]);
        exit();
    }

    $email = trim($input['email']);
    $password = $input['password'];

    // Email format verification
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "The email format is incorrect"]);
        exit();
    }

    try {
        $database = Database::getInstance();

        $query = "SELECT user_ID, username, password_hash, account_status, user_role 
                 FROM users WHERE email = ?";
        $user = $database->query($query, [$email]);

        if (!empty($user)) {
            // Check account status
            if ($user[0]['account_status'] === 'Disable') {
                http_response_code(403);
                echo json_encode(["success" => false, "message" => "Account has been disabled"]);
                exit();
            }

            // Verify Password
            if (password_verify($password, $user[0]['password_hash'])) {
                
                $updateQuery = "UPDATE users SET updated_at = NOW() WHERE user_ID = ?";
                $database->query($updateQuery, [$user[0]['user_ID']]);
                
                // Session Management
                $_SESSION['verified_user_id'] = $user[0]['user_ID'];
                $_SESSION['user_role'] = $user[0]['user_role'];
                
                // Return to more user information
                http_response_code(201);
                echo json_encode([
                    "success" => true,
                    "message" => "Login successful",
                    "user" => [
                        "user_ID" => $user[0]['user_ID'],
                        "username" => $user[0]['username'],
                        "email" => $email,
                        "role" => $user[0]['user_role']
                    ]
                ]);
                exit();
            }
        }

        // Unified return of ambiguous error messages
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Wrong email or password"]);
        
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Login Error: " . $e->getMessage()); // Record server logs
        echo json_encode(["success" => false, "message" => "Internal server error"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Unsupported request method"]);
}