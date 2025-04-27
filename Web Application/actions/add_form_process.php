<?php
session_start();
require_once '../config/conn.php';

if (isset($_POST['save_form'])) {
    try {
        $database = Database::getInstance();
        
        $title = $_POST['title'];
        $news_date = $_POST['news_date'];
        $content = $_POST['content'];
        $question = $_POST['question'];
        $option_a = $_POST['option_a'];
        $option_b = $_POST['option_b'];
        $option_c = $_POST['option_c'];
        $correct_answer = $_POST['correct_answer'];
        $updated_by = $_SESSION['verified_user_id'];
        
        // Insert data into MySQL
        $query = "INSERT INTO news (title, news_date, content, question, option_a, option_b, option_c, correct_answer, updated_by) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $database->query($query, [$title, $news_date, $content, $question, $option_a, $option_b, $option_c, $correct_answer, $updated_by]);
        
        $_SESSION['status'] = "Form added successfully!";
        header("Location: ../public/form-list.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['status'] = "Error: " . $e->getMessage();
        header("Location: ../public/add-form.php");
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request!";
    header("Location: ../public/add-form.php");
    exit();
}
?>