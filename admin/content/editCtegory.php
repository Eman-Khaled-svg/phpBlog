<?php

// 1. DATABASE CONNECTION (No output)

include "./config/database.php";


// 2. INITIALIZE VARIABLES

$message = '';
$message_type = '';
$errors = [];
$edit_category = null;

// =============================================
// 3. HANDLE FORM SUBMISSION (Before any output)
// =============================================
if (isset($_POST['update_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $edit_id = intval($_POST['edit_id']);
    
    // Validation
    if (empty($name)) {
        $errors[] = "Category name is required!";
    } elseif (strlen($name) < 3) {
        $errors[] = "Category name must be at least 3 characters!";
    } elseif (strlen($name) > 100) {
        $errors[] = "Category name cannot exceed 100 characters!";
    }
    
    // Check for duplicate name (excluding current category)
    if (empty($errors)) {
        $check_stmt = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $check_stmt->execute([$name, $edit_id]);
        if ($check_stmt->rowCount() > 0) {
            $errors[] = "Category name already exists!";
        }
    }
    
    // Process update if no errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $result = $stmt->execute([$name, $description, $edit_id]);
            
            if ($result) {
                // Verify the category still exists after update
                $verify_stmt = $conn->prepare("SELECT id FROM categories WHERE id = ?");
                $verify_stmt->execute([$edit_id]);
                
                if ($verify_stmt->rowCount() > 0) {
                    // ✅ Redirect BEFORE any HTML output
                    header("Location: categories.php?success=" . urlencode("Category '$name' updated successfully!"));
                    exit();
                } else {
                    $message = "Category not found after update!";
                    $message_type = "danger";
                }
            } else {
                $message = "Update query failed!";
                $message_type = "danger";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = "danger";
    }
}

// =============================================
// 4. FETCH CATEGORY DATA FOR EDITING
// =============================================
if (isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_category = $stmt->fetch();
        
        if (!$edit_category) {
            // Redirect BEFORE any HTML output
            header("Location: categories.php?error=Category not found");
            exit();
        }
    } catch (PDOException $e) {
        $message = "Error loading category: " . $e->getMessage();
        $message_type = "danger";
    }
} else {
    // Redirect BEFORE any HTML output
    header("Location: categories.php?error=No category ID provided");
    exit();
}


include "header.php";
include "nav.php";
include "sideBar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Admin Panel</title>

    <style>
        /* Main Layout */
        .main-content {
            padding: 20px;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Page Header */
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

        /* Buttons */
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
            text-align: center;
        }

        .btn-primary {
            background: #0A0A0A;
            color: white;
        }

        .btn-primary:hover {
            background: #FFFFFF;
            color: #000;
            border: 1px solid black;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-warning {
            background: #ffc107;
            color: #000;
            border: 1px solid #ffc107;
        }

        .btn-warning:hover {
            background: #e0a800;
            color: #000;
        }

        /* Form Container */
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 700px;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            position: relative;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                padding: 10px;
            }
            .page-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-edit"></i> 
                Edit Category: <?= htmlspecialchars($edit_category['name']) ?>
            </h1>
            <a href="categories.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <!-- Error/Success Messages -->
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <i class="fas <?= 
                        $message_type == 'success' ? 'fa-check-circle' : 
                        ($message_type == 'warning' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle') 
                    ?> me-2"></i>
                    <?= nl2br(htmlspecialchars($message)) ?>
                </div>
            <?php endif; ?>

            <!-- Edit Form -->
            <form method="POST">
                <input type="hidden" name="edit_id" value="<?= $edit_category['id'] ?>">
                
                <!-- Category Name -->
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-tag"></i> Category Name *
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?= htmlspecialchars($_POST['name'] ?? $edit_category['name']) ?>"
                           placeholder="Enter category name"
                           maxlength="100"
                           required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              placeholder="Enter category description (optional)"
                              maxlength="500"><?= htmlspecialchars($_POST['description'] ?? $edit_category['description'] ?? '') ?></textarea>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="categories.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" name="update_category" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include "footer.php"; ?>

    <!-- JavaScript -->
    <script>
        // Real-time validation for category name
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value.trim();
            const validLength = name.length >= 3 && name.length <= 100;
            
            if (!validLength && name.length > 0) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#ced4da';
            }
        });

        // Form submission confirmation
        document.querySelector('form').addEventListener('submit', function(e) {
            const nameField = document.getElementById('name');
            const name = nameField.value.trim();
            
            if (!confirm('Are you sure you want to update this category?')) {
                e.preventDefault();
                return false;
            }
            
            if (name.length < 3 || name.length > 100) {
                alert('Category name must be between 3-100 characters!');
                nameField.focus();
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>