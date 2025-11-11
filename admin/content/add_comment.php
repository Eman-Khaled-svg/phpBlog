<?php
include "database.php";
include "header.php";
include "nav.php";
include "sideBar.php";


$message='';
$message_type='';
if (isset($_GET['status'])) {
    $message_type='success';
    $message=match($_GET['status']) {
        'approved' => "Comment approved successfully!",
        'rejected' => "Comment rejected successfully!",
        'deleted' => "Comment deleted successfully!",
        default => ""
    }
    ; // Add a semicolon here
} else if (isset($_GET['error'])) {
    $message_type='error';
    $message="An error occurred. Please try again.";
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-content {
            padding: 20px;
            min-height: 100vh;
            background-color: #f8f9fa;
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

        .table-responsive {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pending {
            background: #ffeeba;
            color: #856404;
        }

        .badge-approved {
            background: #d4edda;
            color: #155724;
        }

        .badge-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            margin-right: 5px;
            cursor: pointer;
            border: none;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-delete {
            background: #6c757d;
            color: white;
        }

        .comment-text {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-comments"></i> Comments Management
            </h1>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-user"></i> User</th>
                        <th><i class="fas fa-file-alt"></i> Post</th>
                        <th><i class="fas fa-comment"></i> Comment</th>
                        <th><i class="fas fa-calendar"></i> Date</th>
                        <th><i class="fas fa-check-circle"></i> Status</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $conn->query("
                            SELECT c.*, p.title as post_title 
                            FROM comments c 
                            LEFT JOIN posts p ON c.post_id = p.id 
                            ORDER BY c.created_at DESC
                        ");
                        $comments = $stmt->fetchAll();

                        if (empty($comments)) {
                            echo '<tr><td colspan="8" style="text-align: center; padding: 30px;">
                                <i class="fas fa-comments" style="font-size: 48px; color: #6c757d; margin-bottom: 10px;"></i>
                                <p>No comments found</p>
                            </td></tr>';
                        } else {
                            foreach ($comments as $comment) {
                                $statusClass = match($comment['status']) {
                                    'approved' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    default => 'badge-pending'
                                };
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($comment['id']) ?></td>
                                    <td><?= htmlspecialchars($comment['post_title']) ?></td>
                                    <td><?= htmlspecialchars($comment['name']) ?></td>
                                    <td><?= htmlspecialchars($comment['email']) ?></td>

                                    <td class="comment-text"><?= htmlspecialchars($comment['comment']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($comment['status']) ?></span></td>
                                    <td>
                                        <?php if ($comment['status'] !== 'approved'): ?>
                                            <a href="approve_comment.php?id=<?= $comment['id'] ?>" class="btn btn-approve">
                                                <i class="fas fa-check"></i> Approve
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($comment['status'] !== 'rejected'): ?>
                                            <a href="reject_comment.php?id=<?= $comment['id'] ?>" class="btn btn-reject">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php endif; ?>
                                        <a href="delete_comment.php?id=<?= $comment['id'] ?>" 
                                           class="btn btn-delete" 
                                           onclick="return confirm('Are you sure you want to delete this comment?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    } catch (PDOException $e) {
                        echo '<tr><td colspan="7" style="color: red; text-align: center;">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include "footer.php"; ?>
</body>
</html>