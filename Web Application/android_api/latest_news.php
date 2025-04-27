<?php
session_start();
require_once '../config/conn.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $database = Database::getInstance();

        // Query to fetch the latest news
        $query = "SELECT title, news_date, content, question, option_a, option_b, option_c ,correct_answer
                  FROM news ORDER BY news_date DESC LIMIT 1";
        $latestNews = $database->query($query);

        if (!empty($latestNews)) {
            // Return the latest news data
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "Latest news fetched successfully",
                "news" => [
                    "title" => $latestNews[0]['title'],
                    "news_date" => $latestNews[0]['news_date'],
                    "content" => $latestNews[0]['content'],
                    "question" => $latestNews[0]['question'],
                    "option_a" => $latestNews[0]['option_a'],
                    "option_b" => $latestNews[0]['option_b'],
                    "option_c" => $latestNews[0]['option_c'],
                    "correct_answer" => $latestNews[0]['correct_answer']
                ]
            ]);
        } else {
            // If no news is found
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "No news available"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Latest News Error: " . $e->getMessage()); // Record server logs
        echo json_encode(["success" => false, "message" => "Internal server error"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Unsupported request method"]);
}