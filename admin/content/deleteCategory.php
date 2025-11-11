<?php
include "./config/database.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id > 0) {
        try {
            $check_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
            $check_stmt->execute([$id]);
            $category = $check_stmt->fetch();
            
            if ($category) {
                $delete_stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                $result = $delete_stmt->execute([$id]);
                
                if ($result) {
                    header("Location: categories.php?success=Category '" . htmlspecialchars($category['name']) . "' deleted successfully!");
                } else {
                    header("Location: categories.php?error=Failed to delete category");
                }
            } else {
                header("Location: categories.php?error=Category not found");
            }
        } catch (PDOException $e) {
            header("Location: categories.php?error=Database error: " . $e->getMessage());
        }
    } else {
        header("Location: categories.php?error=Invalid category ID");
    }
} else {
    header("Location: categories.php?error=No category ID provided");
}
exit();
?>