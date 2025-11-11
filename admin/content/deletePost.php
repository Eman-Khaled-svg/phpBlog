<?php
require_once '../config/database.php';


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: posts.php");
    exit();
}

$id = (int)$_GET['id'];

try {
    // Get image filename before deletion
    $stmt = $conn->prepare("SELECT image FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if ($post) {
        // Delete the post
        $delete = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $delete->execute([$id]);

        // If post had an image, delete it
        if (!empty($post['image'])) {
            $imagePath = __DIR__ . '/../../upload/' . $post['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $_SESSION['message'] = "Post deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Post not found!";
        $_SESSION['message_type'] = "danger";
    }

} catch (PDOException $e) {
    $_SESSION['message'] = "Error deleting post: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

// Redirect back to posts list
header("Location: posts.php");
exit();