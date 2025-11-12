<?php
include "../config/database.php";
include "../inc/header.php";
include "../content/nav.php";
include "../inc/sideBar.php";

try {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as category_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
            --light: #f8f9fa;
            --gray: #6c757d;
            --border: #dee2e6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: var(--light); }

        .main-content {
            padding: 20px;
            min-height: 100vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border);
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-title {
            font-size: 26px;
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .btn-primary {
            background: #000;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-primary:hover {
            background: transparent;
            color: #000;
            border: 2px solid #000;
            transform: translateY(-3px);
        }

        /* الجدول المتجاوب */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        th {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 16px 15px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            vertical-align: middle;
        }

        tr:hover {
            background-color: #f8f9fa;
            transform: scale(1.005);
            transition: all 0.3s ease;
        }

        /* Badges */
        .badge-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 90px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        }

        .badge-published {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .badge-draft {
            background: linear-gradient(135deg, #bdc3c7 0%, #95a5a6 100%);
            color: white;
        }

        .badge-archived {
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            color: white;
        }

        .post-title {
            font-weight: 600;
            color: var(--primary);
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .post-image {
            width: 70px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-sm {
            padding: 8px 14px;
            font-size: 12px;
            border-radius: 8px;
            text-decoration: none;
            margin: 3px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: var(--success);
            color: white;
        }

        .btn-edit:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .no-data {
            text-align: center;
            padding: 80px 20px;
            color: var(--gray);
            font-size: 18px;
        }

        .no-data i {
            font-size: 70px;
            margin-bottom: 15px;
            opacity: 0.4;
        }


        @media (max-width: 992px) {
            table { min-width: 800px; }
            th, td { padding: 12px 10px; font-size: 13px; }
            .post-title { max-width: 150px; }
            .post-image { width: 60px; height: 45px; }
        }

        @media (max-width: 768px) {
            .main-content { padding: 15px; }
            .page-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                margin-top:47px;
            }
            .btn-primary {
                width: 100%;
                justify-content: center;
                padding: 14px;
            }
            table { min-width: 700px; }
            th, td { padding: 10px 8px; font-size: 12.5px; }
        }

        @media (max-width: 576px) {
            .main-content { padding: 10px; }
            .page-title { font-size: 22px; }
            table { min-width: 600px; }
            .btn-sm { 
                padding: 10px 12px; 
                font-size: 11px; 
                width: 100%; 
                justify-content: center; 
                margin: 5px 0;
            }
        }

    
        .table-wrapper::-webkit-scrollbar {
            height: 10px;
        }
        .table-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .table-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                Posts Management
            </h1>
            <a href="addPost.php" class="btn-primary">
                Add New Post
            </a>
        </div>

        <div class="table-container">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Image</th>
                            <th>Category</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="10" class="no-data">
                                    <i class="fas fa-inbox"></i>
                                    <p>No posts found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $row): ?>
                                <tr>
                                    <td><strong>#<?= htmlspecialchars($row['id']) ?></strong></td>
                                    <td>
                                        <div class="post-title" title="<?= htmlspecialchars($row['title']) ?>">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars(substr(strip_tags($row['content']), 0, 80)) ?>...</small>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="../upload/<?= htmlspecialchars($row['image']) ?>" 
                                                 alt="" class="post-image">
                                        <?php else: ?>
                                            <span style="color:#999">No img</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($row['category_name'] ?? '—') ?></strong></td>
                                    <td><strong><?= number_format((int)$row['views']) ?></strong></td>
                                    <td>
                                        <?php
                                        $status = strtolower($row['status'] ?? 'draft');
                                        $badge = $status === 'published' ? 'badge-published' : 
                                                ($status === 'archived' ? 'badge-archived' : 'badge-draft');
                                        ?>
                                        <span class="badge-status <?= $badge ?>">
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d', strtotime($row['created_at'])) ?></td>
                                    <td><?= $row['updated_at'] ? date('M d', strtotime($row['updated_at'])) : '—' ?></td>
                                    <td>
                                        <a href="updatePost.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">
                                            Edit
                                        </a>
                                        <a href="deletePost.php?id=<?= $row['id'] ?>" 
                                           class="btn-sm btn-delete"
                                           onclick="return confirm('Are you sure?')">
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
    </div>

    <?php include "../inc/footer.php"; ?>
</body>
</html>