<?php
include "../config/database.php";
include "../inc/header.php";
include "../content/nav.php";
include "../inc/sideBar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management</title>
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
            margin: 0;
        }

        .btn-primary {
            background: #0A0A0AFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition:all .4s ease;
            transition: background 0.3s;
        }

        .btn-primary:hover {
            background: #FFFFFFFF;
            color: #000;
            border:1px solid black;
        }

        .table-responsive {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 15px 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-size: 14px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border-radius: 3px;
            text-decoration: none;
            margin-right: 5px;
            display: inline-block;
        }

        .btn-edit {
            background: #28a745;
            color: white;
        }

        .btn-edit:hover {
            background: #218838;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            color: white;
        }

        .category-name {
            font-weight: 500;
            color: #007bff;
        }

        .no-data {
            text-align: center;
            padding: 160px;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
            
            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
                margin-top: 47px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-tags"></i> Categories Management
            </h1>
            <a href="createCategory.php" class="btn-primary">
                <i class="fas fa-plus"></i> Add New Category
            </a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-tag"></i> Name</th>
                        <th><i class="fas fa-align-left"></i> Description</th>
                        <th><i class="fas fa-calendar"></i> Created At</th>
                        <th><i class="fas fa-cogs"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt = $conn->prepare("SELECT * FROM categories ORDER BY id DESC");
                        $stmt->execute();
                        $categories = $stmt->fetchAll();

                        if (empty($categories)) {
                            echo '<tr><td colspan="5" class="no-data">
                                <i class="fas fa-inbox" style="font-size: 48px; color: #6c757d; margin-bottom: 10px;"></i>
                                <p>No categories found</p>
                            </td></tr>';
                        } else {
                            foreach ($categories as $row) {
                    ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td>
                                    <span class="category-name"><?= htmlspecialchars($row['name']) ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['description'] ?? 'No description') ?>
                                </td>
                                <td>
                                    <?= date('Y-m-d H:i', strtotime($row['created_at'])) ?>
                                </td>
                                <td>
                                    <a href="editCtegory.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="deleteCategory.php?id=<?= $row['id'] ?>" 
                                       class="btn-sm btn-delete"
                                       onclick="return confirm('Are you sure you want to delete this category?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                    <?php
                            }
                        }
                    } catch (PDOException $e) {
                        echo '<tr><td colspan="5" style="color: red; text-align: center; padding: 20px;">
                            Error: ' . $e->getMessage() . '
                        </td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include "../inc/footer.php"; ?>
</body>
</html>
اعمله ريسبونسف