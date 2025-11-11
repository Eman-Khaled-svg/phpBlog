<?php
require_once './config/database.php';
include './inc/header.php';
include './content/nav.php';
include './inc/sideBar.php';

// ------------------------------------------------------------------
// 1. Fetch posts **once** – only the columns we really need
// ------------------------------------------------------------------
try {
    $stmt = $conn->prepare(
        "SELECT id, title, content, image, created_at 
         FROM posts 
         WHERE status='published'
         ORDER BY created_at DESC"
    );
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In a production site you would log the error, not echo it
    error_log($e->getMessage());
    $posts = [];
    $error = 'Unable to load posts at the moment.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Latest Posts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    /* ---- Layout ------------------------------------------------ */
    .main-content {
        padding: 20px;
        min-height: 100vh;
        background: #f8f9fa;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .page-title {
        font-size: 24px;
        color: #333;
    }

    /* ---- Post list --------------------------------------------- */
    .post-list {
        border-top: 1px solid #e6e6e6;
    }

    .post-item {
        display: flex;
        gap: 14px;
        padding: 18px 0;
        border-bottom: 1px solid #eee;
        align-items: flex-start;
    }

    .post-thumb {
        flex: 0 0 110px;
    }

    .post-thumb img {
        width: 110px;
        height: 70px;
        object-fit: cover;
        border-radius: 6px;
        display: block;
    }

    .post-body {
        flex: 1;
    }

    .post-title {
        font-size: 18px;
        margin: 0 0 6px;
        color: #0b5ed7;
    }

    .post-meta {
        font-size: 12px;
        color: #777;
        margin-bottom: 8px;
    }

    .post-excerpt {
        color: #555;
        margin-bottom: 10px;
        line-height: 1.45;
    }

    .read-more {
        display: inline-block;
        padding: 6px 10px;
        background: #0d6efd;
        color: #fff;
        border-radius: 4px;
        text-decoration: none;
        font-size: 13px;
    }

    .read-more:hover {
        background: #0b5ed7;
    }

    @media(max-width:600px) {
        .post-item {
            flex-direction: column;
        }

        .post-thumb {
            width: 100%;
        }

        .post-thumb img {
            width: 100%;
            height: auto;
        }
    }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fa-solid fa-newspaper"></i> Latest Posts
            </h1>
        </div>

        <?php if (isset($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php elseif (empty($posts)): ?>
        <p>No posts found.</p>
        <?php else: ?>
        <div class="post-list">
            <?php foreach ($posts as $post):
                // ---- excerpt ------------------------------------------------
                $excerpt = mb_strlen($post['content']) > 100
                    ? mb_substr($post['content'], 0, 100) . '…'
                    : $post['content'];

                // ---- safe image path (fallback) -----------------------------
            $imageSrc = './content/upload/' . htmlspecialchars($post['image']);
                ?>
            <article class="post-item">
                <div class="post-thumb">
                    <a href="post.php?id=<?= urlencode($post['id']) ?>">
                        <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($post['title']) ?>">
                    </a>
                </div>

                <div class="post-body">
                    <h3 class="post-title">
                        <a href="post.php?id=<?= urlencode($post['id']) ?>" style="color:inherit;text-decoration:none">
                            <?= htmlspecialchars($post['title']) ?>
                        </a>
                    </h3>
                    <div class="post-meta">
                        <?= date('F j, Y', strtotime($post['created_at'])) ?>
                    </div>
                    <p class="post-excerpt">
                        <?= htmlspecialchars($excerpt) ?>
                    </p>
                    <a class="read-more" href="./content/readMore.php?id=<?= urlencode($post['id']) ?>">Read More</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php include './inc/footer.php'; ?>
</body>

</html>