<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/conn.php';

// Ensure user is logged in before proceeding
if (!isset($_SESSION['verified_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in!']);
    exit();
}

/* Function to get the total number of maps in the map_images table. */
function getTotalMaps(){
    $database = Database::getInstance();
    $result = $database->query("SELECT COUNT(*) as count FROM map_images");
    if (!empty($result)) {
        return $result[0]['count'];
    }
    return 0;
}

/* Function to get all map information along with the associated username.*/
function getAllMapsInfo() {
    $database = Database::getInstance();
    $query = "
        SELECT map_images.id, users.username, map_images.image_path, map_images.uploaded_at
        FROM map_images 
        INNER JOIN users ON map_images.user_id = users.user_ID
    ";
    $result = $database->query($query);
    if (!empty($result)) {
        return $result;
    }
    return [];
}

function getMapById($id) {
    $database = Database::getInstance();

    $query = "SELECT * FROM map_images WHERE id = ?";
    $stmt = $database->prepare($query);  
    if (!$stmt) {
        die("Prepare failed: " . $database->conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        die("Query execution failed: " . $stmt->error);
    }
    return $result->fetch_assoc();
}

function updateMap($map_id, $image_path, $worksite_id, $question, $option_a, $option_b, $option_c, $correct_answer) {
    $database = Database::getInstance();

    $query = "UPDATE map_images SET 
                worksite_id = ?, 
                question = ?, 
                option_a = ?, 
                option_b = ?, 
                option_c = ?, 
                correct_answer = ?";
    if ($image_path) {
        $query .= ", image_path = ?";
    }
    $query .= " WHERE id = ?";

    $stmt = $database->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $database->conn->error);
    }

    // 绑定参数
    if ($image_path) {
        $stmt->bind_param("issssssi", $worksite_id, $question, $option_a, $option_b, $option_c, $correct_answer, $image_path, $map_id);
    } else {
        $stmt->bind_param("isssssi", $worksite_id, $question, $option_a, $option_b, $option_c, $correct_answer, $map_id);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    return $stmt->affected_rows > 0;
}

function deleteMap($id) {
    $database = Database::getInstance();

    $query = "DELETE FROM map_images WHERE id = ?";
    $stmt = $database->prepare($query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $database->conn->error);
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    return $stmt->affected_rows > 0;
}
?>