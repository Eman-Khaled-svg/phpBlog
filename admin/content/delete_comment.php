<?php
require_once "../config/database.php";


if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: commints.php?status=deleted");
        exit();
    } catch (PDOException $e) {
        header("Location: commints.php?error=true");
        exit();
    }
}