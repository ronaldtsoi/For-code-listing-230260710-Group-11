<?php
session_start();
require_once '../config/conn.php';

if (isset($_POST['add_worksite'])) {
    try {
        $database = Database::getInstance();

        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];
        $worksite_name = $_POST['worksite_name'];

        // Insert data into MySQL
        $query = "INSERT INTO worksites (latitude, longitude, worksite_name) 
                  VALUES (?, ?, ?)";

        $stmt = $database->query($query, [
            $latitude,
            $longitude,
            $worksite_name
        ]);

        $_SESSION['status'] = "Worksite added successfully!";
        header("Location: ../public/worksite-list.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['status'] = "Error: " . $e->getMessage();
        header("Location: ../public/create-worksite.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request!";
    header("Location: ../public/create-worksite.php");
    exit();
}
?>
