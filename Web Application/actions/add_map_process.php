<?php
session_start();
include('../config/conn.php');

// 检查是否通过表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_map'])) {
    // 验证用户是否已登录
    if (!isset($_SESSION['verified_user_id'])) {
        $_SESSION['status'] = "Unauthorized access. Please log in.";
        header("Location: ../public/login.php");
        exit();
    }

    // 获取用户输入的数据
    $user_id = intval($_SESSION['verified_user_id']);
    $worksite_id = intval($_POST['worksite_id']);
    $question = trim($_POST['question']); 
    $option_a = trim($_POST['option_a']); 
    $option_b = trim($_POST['option_b']); 
    $option_c = trim($_POST['option_c']); 
    $correct_answer = trim($_POST['correct_answer']); 

    // 验证所有必填字段
    if ($worksite_id <= 0 || empty($question) || empty($option_a) || empty($option_b) || empty($option_c) || empty($correct_answer)) {
        $_SESSION['status'] = "Please fill in all required fields.";
        header("Location: ../public/add-map.php");
        exit();
    }

    // 文件上传路径
    $target_dir = "../map_image/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // 如果目录不存在，创建目录
    }

    // 检查是否上传了文件
    if (!isset($_FILES['map_image']) || $_FILES['map_image']['error'] != UPLOAD_ERR_OK) {
        $_SESSION['status'] = "No file uploaded or an error occurred during file upload.";
        header("Location: ../public/add-map.php");
        exit();
    }

    // 获取文件信息
    $file_name = basename($_FILES['map_image']['name']);
    $file_size = $_FILES['map_image']['size'];
    $file_tmp = $_FILES['map_image']['tmp_name'];
    $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // 验证文件类型
    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_type, $allowed_types)) {
        $_SESSION['status'] = "Only JPG, JPEG, and PNG files are supported for uploading!";
        header("Location: ../public/add-map.php");
        exit();
    }

    // 验证文件大小（限制为 5MB）
    $max_file_size = 5 * 1024 * 1024; // 5MB
    if ($file_size > $max_file_size) {
        $_SESSION['status'] = "The file size exceeds the maximum limit of 5MB.";
        header("Location: ../public/add-map.php");
        exit();
    }

    // 重命名文件，确保文件名唯一
    $new_file_name = 'map_' . $user_id . '_' . time() . '.' . $file_type; // 生成唯一文件名
    $target_file = $target_dir . $new_file_name;

    // 将文件移动到目标目录
    if (move_uploaded_file($file_tmp, $target_file)) {
        // 保存数据到数据库
        $db = Database::getInstance();
        $query = "INSERT INTO map_images (user_id, worksite_id, image_path, question, option_a, option_b, option_c, correct_answer, uploaded_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        try {
            $db->query($query, [$user_id, $worksite_id, $target_file, $question, $option_a, $option_b, $option_c, $correct_answer]);
            $_SESSION['status'] = "Map uploaded successfully!";
            header("Location: ../public/map-list.php");
            exit();
        } catch (Exception $e) {
            // 数据库操作失败
            $_SESSION['status'] = "Map upload failed: " . $e->getMessage();
            // 删除已上传的文件
            if (file_exists($target_file)) {
                unlink($target_file);
            }
            header("Location: ../public/add-map.php");
            exit();
        }
    } else {
        // 文件移动失败
        $_SESSION['status'] = "File upload failed. Please try again.";
        header("Location: ../public/add-map.php");
        exit();
    }
} else {
    // 非法访问
    $_SESSION['status'] = "Unauthorized access!";
    header("Location: ../public/add-map.php");
    exit();
}