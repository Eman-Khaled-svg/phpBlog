<?php
session_start();
include "../config/database.php";

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid comment ID";
    header("Location: commints.php");
    exit();
}

$comment_id = (int)$_GET['id'];

try {
    // تحديث الحالة لـ approved + وقت الموافقة
    $stmt = $conn->prepare("UPDATE comments SET status = 'approved', approved_at = NOW() WHERE id = ?");
    $stmt->execute([$comment_id]);

    // جلب post_id عشان نروحله
    $stmt = $conn->prepare("SELECT post_id FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();

    if ($comment && $comment['post_id']) {
        $post_id = $comment['post_id'];
        
        // رسالة نجاح + ريدايركت للبوست مع هاشتاج التعليق
        $_SESSION['success'] = "تمت الموافقة على التعليق وهو الآن ظاهر تحت المقال!";
        header("Location: ../readMore.php?id=$post_id#comment-$comment_id");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "خطأ في الموافقة";
}

header("Location: commints.php");
exit();
?>