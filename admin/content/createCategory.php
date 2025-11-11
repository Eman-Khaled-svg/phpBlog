<?php
include "../config/database.php";
include "../inc/header.php";
include "../content/nav.php";
include "../inc/sideBar.php";

// Initialize variables
$message = '';
$message_type = '';

// Handle form submission
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Category name is required!";
    }
    
    if (strlen($name) < 3) {
        $errors[] = "Category name must be at least 3 characters!";
    }
    
    if (strlen($name) > 100) {
        $errors[] = "Category name cannot exceed 100 characters!";
    }
    
    // Check if category already exists
    $check_stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");
    $check_stmt->execute([$name]);
    if ($check_stmt->rowCount() > 0) {
        $errors[] = "Category name already exists!";
    }
    
    if (empty($errors)) {
        try {
            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())");
            // Execute with parameters
            $stmt->execute([$name, $description]);
            // Success message
            $message = "Category '$name' added successfully!";
            $message_type = "success";
            // Clear form
            $_POST = [];
        } catch (PDOException $e) {
            $message = "Error adding category: " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Category</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .main-content {
            /* margin-left: 250px; */
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

        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #131313FF;
            color: white;
        }

        .btn-primary:hover {
            background: #FFFFFFFF;
            color: #000;
            border:1px solid #000;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
            color: white;
        }

        .form-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
          height: 410px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
            margin-top:43px;
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
            max-height: 200px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i> Add New Category
            </h1>
            <a href="categories.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Categories
            </a>
        </div>

        <div class="form-container">
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <i class="fas <?= $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?>"></i>
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-tag"></i> Category Name *
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                        placeholder="Enter category name"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        placeholder="Enter category description (optional)"
                    ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="categories.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" name="add_category" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include "../inc/footer.php"; ?>

    <script>
        // Real-time validation
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value.trim();
            const length = name.length;
            
            if (length < 3 && length > 0) {
                this.style.borderColor = '#dc3545';
            } else if (length > 100) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#ced4da';
            }
        });

        // Form submission confirmation
        document.querySelector('form').addEventListener('submit', function() {
            if (confirm('Are you sure you want to add this category?')) {
                return true;
            }
            return false;
        });
    </script>
</body>
</html>