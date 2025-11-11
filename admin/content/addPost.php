<?php
include "../config/database.php";
include "../inc/header.php";
include "../content/nav.php";
include "../inc/sideBar.php";

$message = '';
$message_type = '';

// get categories for select
$categories = [];
try {
    $cstmt = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $cstmt->fetchAll();
} catch (Exception $e) {
    // ignore, will show empty select
}

// handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $status = in_array($_POST['status'] ?? 'draft', ['draft','published']) ? $_POST['status'] : 'draft';
    $views = isset($_POST['views']) ? (int)$_POST['views'] : 0;

    $errors = [];

    if ($title === '') $errors[] = "Title is required.";
    if (strlen($title) < 3) $errors[] = "Title must be at least 3 characters.";
    if ($category_id <= 0) $errors[] = "Please choose a category.";

    // verify category exists
    if (empty($errors)) {
        $chk = $conn->prepare("SELECT id FROM categories WHERE id = ?");
        $chk->execute([$category_id]);
        if ($chk->rowCount() === 0) $errors[] = "Selected category does not exist.";
    }

    // handle image upload (optional)
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $img = $_FILES['image'];
        
        if ($img['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Image upload error.";
        } else {
            $allowed = ['jpg','jpeg','png','gif'];
            $info = getimagesize($img['tmp_name']);
            if ($info === false) {
                $errors[] = "Uploaded file is not a valid image.";
            } else {
                $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) $errors[] = "Allowed image types: jpg, jpeg, png, gif.";
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../upload/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $imageName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = $uploadDir . $imageName;
                if (!move_uploaded_file($img['tmp_name'], $dest)) {
                    $errors[] = "Failed to move uploaded image.";
                    $imageName = null;
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO posts (title, content, image, category_id, views, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$title, $content, $imageName, $category_id, $views, $status]);
            $message = "Post added successfully.";
            $message_type = "success";
            // clear POST values
            $_POST = [];
            
    
        } catch (PDOException $e) {
            $message = "Error adding post: " . $e->getMessage();
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
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        
        .file-upload {
            position: relative;
            background-color: #f8d7da;
            border: 2px dashed #f5b2b8;
            border-radius: 12px;
            text-align: center;
            padding: 30px;
            cursor: pointer;
            transition: 0.3s;
        }

        .file-upload:hover {
            background-color: #ffe6ea;
            border-color: #f28da1;
            transform: scale(1.02);
        }

        .file-upload i {
            font-size: 40px;
            color: #e05d5d;
            margin-bottom: 10px;
        }

        .file-upload span {
            display: block;
            color: #444;
            font-weight: 500;
            margin-top: 8px;
        }

        .file-upload input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        #file-name {
            margin-top: 10px;
            font-size: 15px;
            color: #555;
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="page-header">
        <h1 class="page-title"><i class="fas fa-plus-circle"></i> Add New Post</h1>
        <a href="posts.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Posts</a>
    </div>

    <div class="form-container">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type === 'success' ? 'success' : 'danger' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required maxlength="255" class="form-control">
            </div>

            <div class="form-group">
                <label>Content</label>
                <textarea name="content" class="form-control" row=7><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category_id"   class="form-control" required>
                    <option value="0"  class="form-control">-- Select category --</option><br><br>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
                <label class="form-label">Upload Image</label>
            <div class="file-upload mb-3">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Click or drag file to upload</span>
                <input type="file" name="image" id="file" >
            </div>
            <p id="file-name" class="text-center"></p>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'selected' : '' ?>  class="form-label">Draft</option>
                    <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>  class="form-control">Published</option>
                </select>
            </div>

            <div class="form-group">
                <label>Views (optional)</label>
                <input type="number" name="views"  class="form-control"  value="<?= htmlspecialchars($_POST['views'] ?? 0) ?>" min="0">
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="posts.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" style="margin-top:10px;">Add Post</button>
            </div>
        </form>
    </div>
</div>

<?php include "../inc/footer.php"; ?>
 <script>
        const fileInput = document.getElementById('file');
        const fileNameDisplay = document.getElementById('file-name');
        fileInput.addEventListener('change', function() {
            fileNameDisplay.textContent = this.files[0] ? "Selected file: " + this.files[0].name : "";
        });
    </script>

</body>
</html>