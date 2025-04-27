<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../config/conn.php');

if (isset($_POST['delete_worksite_btn'])) {
    try {
        $database = Database::getInstance();

        // Get and validate the worksite ID
        $worksite_id = intval($_POST['worksite_id']);
        if ($worksite_id <= 0) {
            throw new Exception("Invalid worksite ID.");
        }

        // Check if the worksite exists
        $checkQuery = "SELECT worksite_id FROM worksites WHERE worksite_id = ?";
        $result = $database->query($checkQuery, [$worksite_id]);

        if (empty($result)) {
            throw new Exception("Worksite not found.");
        }

        // Perform the delete operation
        $deleteQuery = "DELETE FROM worksites WHERE worksite_id = ?";
        $database->query($deleteQuery, [$worksite_id]);

        $_SESSION['status'] = "Worksite deleted successfully!";
        header("Location: ../public/worksite-list.php");
        exit();
    } catch (Exception $e) {
        // Handle errors and redirect
        $_SESSION['status'] = "Error: " . $e->getMessage();
        header("Location: ../public/worksite-list.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request!";
    header("Location: ../public/worksite-list.php");
    exit();
}
?>