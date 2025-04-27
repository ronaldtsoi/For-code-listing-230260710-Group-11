<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/conn.php';

// Ensure the user is logged in before proceeding
if (!isset($_SESSION['verified_user_id'])) {
    $_SESSION['status'] = "You must be logged in to update a form.";
    header("Location: ../public/form-list.php");
    exit();
}

// Check if the form was submitted
if (isset($_POST['update_form'])) {
    $formID = intval($_POST['form_id']);
    $title = trim($_POST['title']);
    $newsDate = trim($_POST['news_date']);
    $content = trim($_POST['content']);
    $question = trim($_POST['news_question']);
    $optionA = trim($_POST['ans_a']);
    $optionB = trim($_POST['ans_b']);
    $optionC = trim($_POST['ans_c']);
    $correctAnswer = trim($_POST['correct_answer']);
    $updatedBy = $_SESSION['verified_user_id'];

    // Validate required fields
    if (empty($title) || empty($newsDate) || empty($content) || empty($question) || empty($optionA) || empty($optionB) || empty($optionC) || empty($correctAnswer)) {
        $_SESSION['status'] = "All fields are required.";
        header("Location: ../public/form-edit.php?id=" . $formID);
        exit();
    }

    // Validate correct answer
    if (!in_array($correctAnswer, ['option_a', 'option_b', 'option_c'])) {
        $_SESSION['status'] = "Invalid correct answer selection.";
        header("Location: ../public/form-edit.php?id=" . $formID);
        exit();
    }

    try {
        $database = Database::getInstance();
        $query = "UPDATE news 
                  SET title = ?, news_date = ?, content = ?, question = ?, 
                      option_a = ?, option_b = ?, option_c = ?, correct_answer = ?, updated_by = ? 
                  WHERE news_id = ?";
        $params = [
            $title,
            $newsDate,
            $content,
            $question,
            $optionA,
            $optionB,
            $optionC,
            $correctAnswer,
            $updatedBy,
            $formID
        ];

        $stmt = $database->query($query, $params);

        $_SESSION['status'] = "Form updated successfully!";
        header("Location: ../public/form-list.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['status'] = "Error updating form: " . $e->getMessage();
        header("Location: ../public/form-edit.php?id=" . $formID);
        exit();
    }
} else {
    $_SESSION['status'] = "Invalid request.";
    header("Location: ../public/form-list.php");
    exit();
}
?>