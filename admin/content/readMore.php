<?php
session_start();
include "../config/database.php";

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($post_id <= 0) {
    die('مقال غير موجود');
}

// معالجة إضافة تعليق جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $comment_text = trim($_POST['comment']);

    if (!empty($name) && !empty($comment_text)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO comments (post_id, name, email, comment, status, created_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$post_id, $name, $email, $comment_text]);

            $_SESSION['comment_sent'] = "تم إرسال تعليقك بنجاح وينتظر الموافقة!";
            header("Location: commints.php?id=$post_id#comment-form-section");
            exit();
        } catch (Exception $e) {
            $error = "فشل في إرسال التعليق";
        }
    } else {
        $error = "الاسم والتعليق مطلوبان";
    }
}

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

    if (!$post) die('المقال غير موجود');

    $conn->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post_id]);

    $stmt = $conn->prepare("
        SELECT * FROM comments 
        WHERE post_id = ? AND status = 'approved' 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll();

} catch (Exception $e) {
    die("خطأ في قاعدة البيانات");
}

include "../inc/header.php";
include "../content/nav.php";
include "../inc/sideBar.php";
?>

<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --light: #fefefe;
            --gray: #6c757d;
        }

        body { font-family: 'Cairo', 'Segoe UI', sans-serif; background: #f8f9fa; margin:0; }
        .main-content { padding: 30px; max-width: 900px; margin: 0 auto; }

        .post-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
            margin-bottom: 40px;
        }

        .post-header {
            padding: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .post-title {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .post-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 15px;
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
            line-height: 1.9;
            color: #333;
        }

        .comments-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 28px;
            margin-bottom: 30px;
            color: #222;
            text-align: center;
        }

        .success-toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #d4edda;
            color: #155724;
            padding: 18px 50px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(40,167,69,0.3);
            z-index: 9999;
            font-weight: 700;
            font-size: 17px;
            animation: slideDown 0.6s ease, fadeOut 0.6s 3.5s ease forwards;
            border: 2px solid #c3e6cb;
            max-width: 90%;
        }

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

        .comment:hover {
            background: #f8f9fa;
            border-left-color: var(--primary);
        }

        .comment-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            float: right;
            margin-left: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .comment-body {
            overflow: hidden;
        }

        .comment-author {
            font-weight: 700;
            color: #333;
            font-size: 17px;
        }

        .comment-time {
            color: var(--gray);
            font-size: 14px;
            margin-right: 10px;
        }

        /* فورم التعليقات */
        .comment-form-section {
            margin-top: 60px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 40px;
            border-radius: 20px;
            border: 3px dashed #ced4da;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .comment-form .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .comment-form .form-group.full {
            grid-column: 1 / -1;
        }

        .comment-form .form-control {
            width: 100%;
            padding: 18px 22px;
            border: 2px solid #dee2e6;
            border-radius: 16px;
            font-size: 17px;
            background: white;
            transition: all 0.4s ease;
        }

        .comment-form .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 6px rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }

        .comment-form textarea.form-control {
            min-height: 160px;
            resize: vertical;
        }

        .comment-form .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 60px;
            border: none;
            border-radius: 50px;
            font-size: 19px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.5s ease;
            box-shadow: 0 10px 30px rgba(102,126,234,0.4);
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 0 auto;
        }

        .comment-form .btn-submit:hover {
            transform: translateY(-6px) scale(1.05);
            box-shadow: 0 20px 40px rgba(102,126,234,0.5);
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
            .post-image { height: 300px; }
            .comment-form .form-grid { grid-template-columns: 1fr; }
            .comment-form-section { padding: 25px; }
        }
    </style>
</head>
<body>
    <div class="main-content">

     
        <?php if (isset($_SESSION['comment_sent'])): ?>
            <div class="success-toast">
                <?= $_SESSION['comment_sent'] ?>
            </div>
            <?php unset($_SESSION['comment_sent']); ?>
        <?php endif; ?>

        
        <?php if (isset($_SESSION['comment_approved'])): ?>
            <div class="success-toast">
           Approved
            </div>
            <?php unset($_SESSION['comment_approved']); ?>
        <?php endif; ?>

        <article class="post-card">
            <header class="post-header">
                <h1 class="post-title"><?= htmlspecialchars($post['title']) ?></h1>
                <div class="post-meta">
                    <span><i class="fas fa-calendar"></i> <?= date('d M Y', strtotime($post['created_at'])) ?></span>
                    <span><i class="fas fa-eye"></i> <?= number_format($post['views'] + 1) ?></span>
                    <a href="#" class="category-badge"><?= htmlspecialchars($post['category_name'] ?? 'غير مصنف') ?></a>
                </div>
            </header>

            <?php if ($post['image']): ?>
                <img src="../upload/<?= htmlspecialchars($post['image']) ?>" alt="" class="post-image">
            <?php endif; ?>

            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) ?>
            </div>
        </article>

        <section class="comments-section">
            <h2 class="section-title">comments (<?= count($comments) ?>)</h2>

            <div class="comments-list">
                <?php if (empty($comments)): ?>
                    <p style="text-align:center;color:#666;padding:60px;font-size:18px;background:#f8f9fa;border-radius:16px;">
                       there is no comments be the first!
                    </p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <?php 
                        $is_highlighted = (strpos($_SERVER['REQUEST_URI'], "comment-{$comment['id']}") !== false);
                        ?>
                        <div class="comment <?= $is_highlighted ? 'highlight' : '' ?>" id="comment-<?= $comment['id'] ?>">
                            <div class="comment-avatar">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($comment['name']) ?>&background=007bff&color=fff&size=128&bold=true" width="90%" style="border-radius:50% ;"     alt="">
                            </div>
                            <div class="comment-body">
                                <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                                    <span class="comment-author"><?= htmlspecialchars($comment['name']) ?></span>
                                    <span class="comment-time"><?= date('d M Y hour H:i', strtotime($comment['created_at'])) ?></span>
                                </div>
                                <p style="margin:0; color:#444; line-height:1.8; font-size:16px;">
                                    <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- فورم التعليقات -->
            <div class="comment-form-section" id="comment-form-section">
                <h3 class="section-title">Add ur comment </h3>

                <form method="post" action="" class="comment-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Name <span style="color:red;">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder=" Full name">
                        </div>
                        <div class="form-group">
                            <label>  ur email(not required)</label>
                            <input type="email" name="email" class="form-control" placeholder="email@example.com">
                        </div>
                        <div class="form-group full">
                            <label>the comment <span style="color:red;">*</span></label>
                            <textarea name="comment" class="form-control" rows="6" required placeholder=" Write ur comment here ...."></textarea>
                        </div>
                    </div>
                    <button type="submit" name="add_comment" class="btn-submit">
                        إرسال التعليق
                    </button>
                </form>
            </div>
        </section>
    </div>

    <?php include '../inc/footer.php'; ?>

    <script>
        // سكرول أوتوماتيك للتعليق الجديد
        if (window.location.hash) {
            const target = document.querySelector(window.location.hash);
            if (target) {
                setTimeout(() => {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    target.style.animation = 'pulse 4s infinite';
                }, 700);
            }
        }

        // إخفاء الرسائل بعد 4 ثواني
        setTimeout(() => {
            document.querySelectorAll('.success-toast').forEach(toast => {
                toast.style.animation = 'fadeOut 0.6s ease forwards';
            });
        }, 4000);
    </script>
</body>
</html>