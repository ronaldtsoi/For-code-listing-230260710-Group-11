<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../functions/auth_check.php');
require_once('../actions/fetch_maps_process.php'); // Include your database functions

if (isset($_POST['delete_map'])) {
    $map_id = $_POST['map_id'];

    if (!empty($map_id)) {
        $result = deleteMap($map_id); // Delete the map from the database
        if ($result) {
            $_SESSION['status'] = "Map deleted successfully.";
        } else {
            $_SESSION['status'] = "Failed to delete map.";
        }
    } else {
        $_SESSION['status'] = "Invalid map ID.";
    }
    header("Location: ../public/map-list.php");
    exit();
} else {
    $_SESSION['status'] = "Invalid request.";
    header("Location: ../public/map-list.php");
    exit();
}

?>