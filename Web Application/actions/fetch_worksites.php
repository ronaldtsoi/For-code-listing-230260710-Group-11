<?php
require_once('../config/conn.php');

function getTotalWorksites($search = '') {
    $database = Database::getInstance();
    $query = "SELECT COUNT(*) as total FROM worksites";

    if (!empty($search)) {
        $query .= " WHERE worksite_name LIKE ?";
        $stmt = $database->prepare($query);
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param("s", $searchTerm);
    } else {
        $stmt = $database->prepare($query);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            return $row['total'];
        }
    }

    return 0;
}

function getPaginatedWorksites($search = '', $limit = 10, $offset = 0) {
    $database = Database::getInstance();
    $query = "SELECT * FROM worksites";
    if (!empty($search)) {
        $query .= " WHERE worksite_name LIKE ?";
        $query .= " LIMIT ? OFFSET ?";
        $stmt = $database->prepare($query);
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param("sii", $searchTerm, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $query .= " LIMIT ? OFFSET ?";
        $stmt = $database->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

function getWorksite($id) {
    $database = Database::getInstance();
    $query = "SELECT * FROM worksites WHERE worksite_id = ?";
    $result = $database->query($query, [$id]);
    return $result ? $result[0] : null;
    return 0;
}

function getWorksiteNameANDID() {
    $database = Database::getInstance();
    $result = $database->query("SELECT worksite_id, worksite_name FROM worksites");
    return $result;
}
?>