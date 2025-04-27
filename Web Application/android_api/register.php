<?php
// register.php
session_start();
require_once '../config/conn.php';

header("Content-Type: application/json");
// header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    // Input validation
    $requiredFields = ['username', 'email', 'password', 'phone'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Please fill in all required fields"
            ]);
            exit();
        }
    }

    $username = trim($input['username']);
    $email = trim($input['email']);
    $password = $input['password'];
    $phone_number = trim($input['phone']);

    // Email format verification
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "The email format is incorrect"
        ]);
        exit();
    }

    // Mobile phone number verification 
    if (!preg_match('/^\d{8}$/', $phone_number)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Please enter a valid 8-digit mobile phone number"
        ]);
        exit();
    }

    try {
        $database = Database::getInstance();

        // Check if the email address is registered
        $checkQuery = "SELECT user_ID FROM users WHERE email = ?";
        $existingUser = $database->query($checkQuery, [$email]);
        
        if (!empty($existingUser)) {
            http_response_code(409);
            echo json_encode([
                "success" => false,
                "message" => "This email address has been registered"
            ]);
            exit();
        }

        // Encryption password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert New User
        $insertQuery = "INSERT INTO users 
            (username, email, password_hash, phone_number) 
            VALUES (?, ?, ?, ?)";

        $params = [$username, $email, $passwordHash, $phone_number];
        $database->query($insertQuery, $params);

        // Get a new user ID
        $newUserId = $database->lastInsertId();

        // Returns a successful response
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Successful registration",
            "user" => [
                "user_ID" => $newUserId,
                "username" => $username,
                "email" => $email,
                "role" => "user"
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        error_log("Registration Error: " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Internal server error"
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Unsupported request method"
    ]);
}