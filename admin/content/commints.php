<?php
session_start();
include "../config/database.php";

// Handle actions (approve, reject, delete) in the same file
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $post_id = (int)($_GET['post_id'] ?? 0);

    try {
        if ($_GET['action'] === 'approve') {
            $stmt = $conn->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['msg'] = "Comment approved successfully!";
            header("Location: readMore.php?id=$post_id#comment-$id");
            exit();
        }

        if ($_GET['action'] === 'reject') {
            $stmt = $conn->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['msg'] = "Comment rejected.";
        }

        if ($_GET['action'] === 'delete') {
            $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['msg'] = "Comment deleted permanently.";
        }
    } catch (Exception $e) {
        $_SESSION['msg'] = "Error: " . $e->getMessage();
    }

    if ($_GET['action'] !== 'approve') {
        header("Location: comments.php");
        exit();
    }
}

include "../inc/header.php";
include "../content/nav.php";
include "../inc/sideBar.php";

// Fetch comments
try {
    $stmt = $conn->prepare("
        SELECT c.*, p.title as post_title, p.id as post_id 
        FROM comments c 
        LEFT JOIN posts p ON c.post_id = p.id 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    $comments = $stmt->fetchAll();
} catch (Exception $e) {
    $comments = [];
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --gray: #6c757d;
            --light: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f3f5;
            margin: 0;
        }

        .main-content {
            padding: 20px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }

        .page-title {
            font-size: 28px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin: 20px 0;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .alert-success { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }

        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #343a40;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 600;
        }

        td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
        }

        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-rejected { background: #f8d7da; color: #721c24; }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            margin: 0 4px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.3s;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
        }

        .btn-approve { background: var(--success); }
        .btn-reject { background: var(--danger); }
        .btn-delete { background: var(--gray); }

        @media (max-width: 768px) {
            .main-content { padding: 15px; }
            .btn span { display: none; }
            .btn { padding: 12px; }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                Comments Management
            </h1>
        </div>

        <!-- Success/Error Message -->
        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['msg'] ?>
                <span style="float:right;cursor:pointer;" onclick="this.parentElement.remove()">×</span>
            </div>
            <?php unset($_SESSION['msg']); ?>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                Error loading comments: <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Post</th>
                        <th>Name</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($comments)): ?>
                        <tr>
                            <td colspan="7" style="padding:60px;font-size:18px;color:#666;">
                                No comments yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($comments as $c): 
                            $statusClass = $c['status'] === 'approved' ? 'badge-approved' : 
                                         ($c['status'] === 'rejected' ? 'badge-rejected' : 'badge-pending');
                        ?>
                            <tr style="<?= $c['status'] === 'pending' ? 'background:#fffbeb;' : '' ?>">
                                <td><strong>#<?= $c['id'] ?></strong></td>
                                <td>
                                    <a href="readMore.php?id=<?= $c['post_id'] ?>" target="_blank" style="color:#007bff;text-decoration:underline;">
                                        <?= htmlspecialchars($c['post_title'] ?? 'Deleted Post') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td style="max-width:300px;">
                                    <?= htmlspecialchars(substr($c['comment'], 0, 80)) ?>...
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($c['created_at'])) ?></td>
                                <td><span class="badge <?= $statusClass ?>"><?= ucfirst($c['status']) ?></span></td>
                                <td>
                                    <?php if ($c['status'] !== 'approved'): ?>
                                        <a href="commints.php?action=approve&id=<?= $c['id'] ?>&post_id=<?= $c['post_id'] ?>" 
                                           class="btn btn-approve" 
                                           onclick="return confirm('Approve this comment?')">
                                            Approve
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($c['status'] !== 'rejected'): ?>
                                        <a href="commints.php?action=reject&id=<?= $c['id'] ?>" 
                                           class="btn btn-reject" 
                                           onclick="return confirm('Reject this comment?')">
                                            Reject
                                        </a>
                                    <?php endif; ?>

                                    <a href="commints.php?action=delete&id=<?= $c['id'] ?>" 
                                       class="btn btn-delete" 
                                       onclick="return confirm('Delete permanently?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include "../inc/footer.php"; ?>
</body>
</html>