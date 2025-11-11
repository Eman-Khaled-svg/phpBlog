<?php
session_start();
include "../config/database.php";

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) die('Invalid Post');

include "../inc/header.php";
include "../content/nav.php";
include "../inc/sideBar.php";

// جلب البوست
try {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) die('Post not found');

    // زيادة المشاهدات
    $conn->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post_id]);

    // جلب التعليقات الموافق عليها
    $stmt = $conn->prepare("
        SELECT * FROM comments 
        WHERE post_id = ? AND status = 'approved' 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border: #dee2e6;
        }


 body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f3f5;
        }

        .main-content {
            padding: 20px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            padding: 20px
        }

        .page-title {
            font-size: 28px;
            color: #333;
            display:flex;
            align-items:center;
            gap: 10px;
        }
        .post-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
            margin-bottom: 40px;
        }

 

        .category-badge {
            background: rgba(255,255,255,0.25);
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 600;
        }

        .post-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .post-content {
            padding: 40px;
            font-size: 18px;
            line-height: 1.8;
            color: #333;
        }

        .comments-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 28px;
            margin-bottom: 30px;
            color: #222;
        }

        /* رسائل النجاح */
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #d4edda;
            color: #155724;
            padding: 18px 40px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(40,167,69,0.3);
            z-index: 9999;
            font-weight: 600;
            animation: slideDown 0.6s ease, fadeOut 0.6s 3.5s ease forwards;
            border: 2px solid #c3e6cb;
        }

        /* تعليق مميز */
        .comment.highlight {
            animation: pulse 4s infinite, fadeInUp 0.8s ease;
            background: linear-gradient(90deg, #d4edda 0%, #f8f9fa 30%);
            border-left: 6px solid var(--success) !important;
            padding: 25px !important;
            border-radius: 16px;
            margin: 20px 0;
        }

        .comment {
            padding: 20px;
            border-bottom: 1px solid #eee;
            border-left: 4px solid transparent;
            transition: all 0.4s ease;
        }

        .comment:hover { background: #f8f9fa; border-left-color: var(--primary); }

        .comment-avatar {
            width: 50px; height: 50px; border-radius: 50%; float: right; margin-left: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .comment-body { overflow: hidden; }

        .comment-author {
            font-weight: 700; color: #333; font-size: 16px;
        }

        .comment-time {
            color: var(--gray); font-size: 14px; margin-right: 10px;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateX(-50%) translateY(-50px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }

        @keyframes fadeOut {
            to { opacity: 0; transform: translateX(-50%) translateY(-50px); }
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(40,167,69,0.4); }
            70% { box-shadow: 0 0 0 25px rgba(40,167,69,0); }
            100% { box-shadow: 0 0 0 0 rgba(40,167,69,0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .main-content { padding: 15px; }
            .post-title { font-size: 28px; }
            .post-header { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="main-content">

        <!-- رسالة الموافقة -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="toast">
                <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <article class="post-card">
            <header class="page-header">
                <h1 class="page-title"><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    <span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($post['created_at'])) ?></span>
                    <span><i class="fas fa-eye"></i> <?= number_format($post['views'] + 1) ?></span>
                    <a href="#" class="category-badge"><?= htmlspecialchars($post['category_name']) ?></a>
                </div>
            </header>

            <?php if ($post['image']): ?>
                <img src="upload/<?= htmlspecialchars($post['image']) ?>" alt="" class="post-image">
            <?php endif; ?>

            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
        </article>

        <section class="comments-section">
            <h2 class="section-title">التعليقات (<?= count($comments) ?>)</h2>

            <div class="comments-list">
                <?php foreach ($comments as $comment): 
                    $is_highlighted = (isset($_GET['id']) && strpos($_SERVER['REQUEST_URI'], "comment-{$comment['id']}") !== false);
                ?>
                    <div class="comment <?= $is_highlighted ? 'highlight' : '' ?>" id="comment-<?= $comment['id'] ?>">
                        <div class="comment-avatar">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($comment['name']) ?>&background=007bff&color=fff&size=128" width="90%"  style="border-radius: 50%" alt="">
                        </div>
                        <div class="comment-body">
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                <span class="comment-author"><?= htmlspecialchars($comment['name']) ?></span>
                                <span class="comment-time"><?= date('d M Y \ع\ن\d H:i', strtotime($comment['created_at'])) ?></span>
                            </div>
                            <p style="margin:0; color:#444; line-height:1.7;">
                                <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <?php include '../inc/footer.php'; ?>

    <script>
        // سكرول أوتوماتيك للتعليق الموافق عليه
        if (window.location.hash) {
            const target = document.querySelector(window.location.hash);
            if (target) {
                setTimeout(() => {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    target.style.animation = 'pulse 4s infinite';
                }, 800);
            }
        }

        // إخفاء الرسالة بعد 4 ثواني
        setTimeout(() => {
            const toast = document.querySelector('.toast');
            if (toast) toast.style.animation = 'fadeOut 0.6s ease forwards';
        }, 4000);
    </script>
</body>
</html>     