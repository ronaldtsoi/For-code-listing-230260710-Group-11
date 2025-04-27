<?php
require_once('../config/conn.php');

function getAllForms() {
    $db = Database::getInstance();
    return $db->query("SELECT news_id, title, news_date, created_at, updated_at FROM news");
}

function getFormByID($formID) {
    $db = Database::getInstance();
    $query = "SELECT news_id, title, news_date, content, question, option_a, option_b, option_c, correct_answer, created_at, updated_at FROM news WHERE news_id = ?";
    $result = $db->query($query, [$formID]);
    return $result ? $result[0] : null;
}

// Get all Safety Forms records
function getSafetyForms() {
    $database = Database::getInstance();
    $result = $database->query("SELECT news.*, users.username AS updated_by_user 
                                FROM news 
                                LEFT JOIN users ON news.updated_by = users.user_ID 
                                ORDER BY news.news_id DESC");

    return $result;
}

// Get the total number of Safety Forms
function getTotalSafetyForms() {
    $database = Database::getInstance();
    $result = $database->query("SELECT COUNT(*) as count FROM news");

    if (!empty($result)) {
        return $result[0]['count'];
    }
    return 0;
}
?>