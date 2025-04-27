<?php
include('../functions/auth_check.php');
require_once('../actions/fetch_maps_process.php'); // Include database operations

if (isset($_POST['update_map'])) {
    $map_id = intval($_POST['map_id']);
    $worksite_id = intval($_POST['worksite_id']);
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $correct_answer = trim($_POST['correct_answer']);

    // Check if a new image is uploaded
    $image_path = null;
    if (isset($_FILES['map_image']) && $_FILES['map_image']['error'] == UPLOAD_ERR_OK) {
        // File upload path
        $target_dir = "../map_image/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
        }

        // Get file information
        $file_name = basename($_FILES['map_image']['name']);
        $file_size = $_FILES['map_image']['size'];
        $file_tmp = $_FILES['map_image']['tmp_name'];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['status'] = "Only JPG, JPEG, and PNG files are supported for uploading!";
            header("Location: ../public/edit-map.php?id=$map_id");
            exit();
        }

        // Validate file size (limit to 5MB)
        $max_file_size = 5 * 1024 * 1024; // 5MB
        if ($file_size > $max_file_size) {
            $_SESSION['status'] = "The file size exceeds the maximum limit of 5MB.";
            header("Location: ../public/edit-map.php?id=$map_id");
            exit();
        }

        // Rename file to ensure uniqueness
        $new_file_name = 'map_' . $map_id . '_' . time() . '.' . $file_type; // Generate a unique file name
        $target_file = $target_dir . $new_file_name;

        // Move the file to the target directory
        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_path = $target_file;
        } else {
            // File move failed
            $_SESSION['status'] = "File upload failed. Please try again.";
            header("Location: ../public/edit-map.php?id=$map_id");
            exit();
        }
    }

    // Call the update function
    $result = updateMap($map_id, $image_path, $worksite_id, $question, $option_a, $option_b, $option_c, $correct_answer);
    if ($result) {
        $_SESSION['status'] = "Map updated successfully.";
    } else {
        $_SESSION['status'] = "Failed to update map.";
    }

    header("Location: ../public/map-list.php");
    exit();
} else {
    $_SESSION['status'] = "Invalid request.";
    header("Location: ../public/map-list.php");
    exit();
}