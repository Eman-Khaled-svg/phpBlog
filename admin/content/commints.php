<?php
include "../config/database.php";
include "../inc/header.php";
include "../content/nav.php";
include "../inc/sideBar.php";

try {
    $stmt = $conn->query("
        SELECT c.*, p.title as post_title 
        FROM comments c 
        LEFT JOIN posts p ON c.post_id = p.id 
        ORDER BY c.created_at DESC
    ");
    $comments = $stmt->fetchAll();
} catch (PDOException $e) {
    $comments = [];
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --gray: #6c757d;
            --light: #f8f9fa;
            --dark: #343a40;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
        }

        .page-title {
            font-size: 28px;
            color: #333;
            display:flex;
            align-items:center;
            gap: 10px;
        }

        /* Desktop Table */
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            padding: 15px 12px;
            text-align: left;
            font-size: 14px;
            color: #495057;
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 2px solid #dee2e6;
        }

        td {
            padding: 14px 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 14px;
        }

        .comment-text {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-approved { background: #d4edda; color: #155724; }
        .badge-rejected { background: #f8d7da; color: #721c24; }

        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
            margin: 2px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .btn-approve { background: var(--success); color: white; }
        .btn-approve:hover { background: #218838; }

        .btn-reject { background: var(--danger); color: white; }
        .btn-reject:hover { background: #c82333; }

        .btn-delete { background: var(--gray); color: white; }
        .btn-delete:hover { background: #5a6268; }

        /* Mobile Cards */
        .cards-container {
            display: none;
            flex-direction: column;
            gap: 15px;
        }

        .comment-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-id {
            font-weight: bold;
            color: var(--primary);
        }

        .card-post {
            font-size: 15px;
            color: #333;
            font-weight: 600;
        }

        .card-body {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 8px 15px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .card-label {
            color: #6c757d;
            font-weight: 500;
        }

        .card-value {
            color: #333;
            overflow-wrap: anywhere;
        }

        .card-comment {
            grid-column: 1 / -1;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.5;
        }

        .card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Responsive Breakpoints */
        @media screen and (max-width: 992px) {
            .table-container {
                display: none;
            }
            .cards-container {
                display: flex;
            }
            .page-title {
                font-size: 24px;
            }
        }

        @media screen and (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            .page-title {
                font-size: 22px;
            }
            .btn span {
                display: none;
            }
            .btn {
                padding: 10px;
            }
        }

        @media screen and (max-width: 576px) {
            .main-content {
                padding: 10px;
            }
            .page-title {
                font-size: 20px;
            }
            .comment-card {
                padding: 14px;
            }
            .card-body {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            .card-label {
                font-size: 13px;
            }
        }

        /* Smooth scroll for table on tablets */
        .table-wrapper::-webkit-scrollbar {
            height: 8px;
        }

        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        /* Print */
        @media print {
            .btn, .card-actions { display: none; }
            .comment-card, .table-container { box-shadow: none; border: 1px solid #ddd; }
        }

        .no-data {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .no-data i {
            font-size: 64px;
            margin-bottom: 15px;
            opacity: 0.5;
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

        <!-- Desktop Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Post</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($comments)): ?>
                            <tr>
                                <td colspan="8" class="no-data">
                                    <i class="fas fa-comments"></i>
                                    <p>No comments found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($comments as $comment):
                                $statusClass = match($comment['status']) {
                                    'approved' => 'badge-approved',
                                    'rejected' => 'badge-rejected',
                                    default => 'badge-pending'
                                };
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($comment['id']) ?></td>
                                    <td><?= htmlspecialchars($comment['post_title'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($comment['name']) ?></td>
                                    <td><?= htmlspecialchars($comment['email']) ?></td>
                                    <td class="comment-text" title="<?= htmlspecialchars($comment['comment']) ?>">
                                        <?= htmlspecialchars($comment['comment']) ?>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($comment['status'])) ?></span></td>
                                    <td>
                                        <?php if ($comment['status'] !== 'approved'): ?>
                                            <a href="approve_comment.php?id=<?= $comment['id'] ?>" class="btn btn-approve">
                                                <i class="fas fa-check"></i> <span>Approve</span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($comment['status'] !== 'rejected'): ?>
                                            <a href="reject_comment.php?id=<?= $comment['id'] ?>" class="btn btn-reject">
                                                <i class="fas fa-times"></i> <span>Reject</span>
                                            </a>
                                        <?php endif; ?>
                                        <a href="delete_comment.php?id=<?= $comment['id'] ?>" 
                                           class="btn btn-delete" 
                                           onclick="return confirm('Are you sure you want to delete this comment?')">
                                            <i class="fas fa-trash"></i> <span>Delete</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="cards-container">
            <?php if (empty($comments)): ?>
                <div class="no-data">
                    <i class="fas fa-comments"></i>
                    <p>No comments found</p>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment):
                    $statusClass = match($comment['status']) {
                        'approved' => 'badge-approved',
                        'rejected' => 'badge-rejected',
                        default => 'badge-pending'
                    };
                    $statusColor = match($comment['status']) {
                        'approved' => '#28a745',
                        'rejected' => '#dc3545',
                        default => '#ffc107'
                    };
                ?>
                    <div class="comment-card">
                        <div class="card-header">
                            <div class="card-id text-dark">#<?= htmlspecialchars($comment['id']) ?></div>
                            <div class="card-post"><?= htmlspecialchars($comment['post_title'] ?? 'N/A') ?></div>
                            <span class="badge <?= $statusClass ?>" style="margin-left: auto;">
                                <?= ucfirst(htmlspecialchars($comment['status'])) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="card-label">Name:</div>
                            <div class="card-value"><?= htmlspecialchars($comment['name']) ?></div>

                            <div class="card-label">Email:</div>
                            <div class="card-value"><?= htmlspecialchars($comment['email']) ?></div>

                            <div class="card-label">Date:</div>
                            <div class="card-value"><?= date('M d, Y H:i', strtotime($comment['created_at'])) ?></div>

                            <div class="card-label">Comment:</div>
                            <div class="card-comment"><?= nl2br(htmlspecialchars($comment['comment'])) ?></div>
                        </div>
                        <div class="card-actions">
                            <?php if ($comment['status'] !== 'approved'): ?>
                                <a href="approve_comment.php?id=<?= $comment['id'] ?>" class="btn btn-approve">
                                    <i class="fas fa-check"></i> <span>Approve</span>
                                </a>
                            <?php endif; ?>
                            <?php if ($comment['status'] !== 'rejected'): ?>
                                <a href="reject_comment.php?id=<?= $comment['id'] ?>" class="btn btn-reject">
                                    <i class="fas fa-times"></i> <span>Reject</span>
                                </a>
                            <?php endif; ?>
                            <a href="delete_comment.php?id=<?= $comment['id'] ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this comment?')">
                                <i class="fas fa-trash"></i> <span>Delete</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include "../inc/footer.php"; ?>
</body>
</html>