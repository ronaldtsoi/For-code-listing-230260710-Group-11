<?php
session_start();
require_once('../config/conn.php');

if (isset($_POST['worksite_id'])) {
    $worksite_id = $_POST['worksite_id'];
    $worksite_name = $_POST['worksite_name'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    if (empty(trim($worksite_name))) {
        $_SESSION['status'] = "Worksite name cannot be empty.";
        header("Location: ../public/edit-worksite.php?id=$worksite_id");
        exit();
    }

    if (!is_numeric($latitude) || !is_numeric($longitude)) {
        $_SESSION['status'] = "Invalid latitude or longitude format.";
        header("Location: ../public/edit-worksite.php?id=$worksite_id");
        exit();
    }

    try {
        $database = Database::getInstance();
        $query = "UPDATE worksites SET worksite_name = ?, latitude = ?, longitude = ? WHERE worksite_id = ?";
        $stmt = $database->query($query, [
            $worksite_name,
            $latitude,
            $longitude,
            $worksite_id
        ]);

        $_SESSION['status'] = "Worksite updated successfully!";
        header("Location: ../public/worksite-list.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['status'] = "Error: " . $e->getMessage();
        header("Location: ../public/edit-worksite.php?id=$worksite_id");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request!";
    header("Location: ../public/worksite-list.php");
    exit();
}

