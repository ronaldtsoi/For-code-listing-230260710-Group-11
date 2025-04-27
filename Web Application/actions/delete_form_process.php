<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('../config/conn.php');

if (isset($_POST['news_id'])) {
    try {
        $database = Database::getInstance();

        // Get and validate the form ID
        $form_id = intval($_POST['news_id']);
        if ($form_id <= 0) {
            throw new Exception("Invalid form ID.");
        }

        // Check if the form exists
        $checkQuery = "SELECT news_id FROM news WHERE news_id = ?";
        $result = $database->query($checkQuery, [$form_id]);

        if (empty($result)) {
            throw new Exception("Form not found.");
        }

        // Perform the delete operation
        $deleteQuery = "DELETE FROM news WHERE news_id = ?";
        $database->query($deleteQuery, [$form_id]);

        $_SESSION['status'] = "Form deleted successfully!";
        header("Location: ../public/form-list.php");
        exit();
    } catch (Exception $e) {
        // Handle errors and redirect
        $_SESSION['status'] = "Error deleting form: " . $e->getMessage();
        header("Location: ../public/form-list.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request!";
    header("Location: ../public/form-list.php");
    exit();
}
?>