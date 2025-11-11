<?php
include "../config/database.php";

if (isset($_GET['id'])) {
    $comment_id = $_GET['id'];
    
    try {
        // Update comment status to rejected
        $stmt = $conn->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$comment_id]);
        
    } catch (PDOException $e) {
        // Error occurred
    }
}

// Redirect back to comments page
header("Location: commints.php");
exit();
?>