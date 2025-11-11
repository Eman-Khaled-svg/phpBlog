<?php
require_once "database.php";
include "header.php";
include "nav.php";
include "sideBar.php";

$message = '';
$message_type = '';
$post = null;

// Get post ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: posts.php");
    exit();
}

$id = (int)$_GET['id'];

// Get categories for select
try {
    $cstmt = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $cstmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Get existing post data
try {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header("Location: posts.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: posts.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $views = isset($_POST['views']) ? (int)$_POST['views'] : 0;
    
    $errors = [];
    
    if (empty($title)) $errors[] = "Title is required";
    if (strlen($title) < 3) $errors[] = "Title must be at least 3 characters";
    if ($category_id <= 0) $errors[] = "Please select a category";

    // Handle image upload if new image provided
    $imageName = $post['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Image upload error";
        } elseif (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image type. Allowed: " . implode(', ', $allowed);
        } else {
            // Generate new filename
            $imageName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $uploadPath = __DIR__ . '/../../upload/' . $imageName;
            
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $errors[] = "Failed to save image";
                $imageName = $post['image']; // Keep old image on error
            } else {
                // Delete old image if exists
                if (!empty($post['image'])) {
                    $oldImage = __DIR__ . '/../../upload/' . $post['image'];
                    if (file_exists($oldImage)) unlink($oldImage);
                }
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE posts SET 
                title = ?, 
                content = ?, 
                image = ?, 
                category_id = ?, 
                views = ?, 
                status = ?,
                updated_at = NOW()
                WHERE id = ?");
                
            $stmt->execute([$title, $content, $imageName, $category_id, $views, $status, $id]);
            
            $message = "Post updated successfully!";
            $message_type = "success";
            
            // Refresh post data
            $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            
        } catch (PDOException $e) {
            $message = "Error updating post: " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = "danger";
    }
}
?>
<style>
      .file-upload {
        border: 2px dashed #ddd;
        border-radius: 4px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: border 0.3s ease;
    }
    .file-upload:hover {
        border-color: #666;
    }
       .file-upload i {
        font-size: 2em;
        color: #666;
        margin-bottom: 10px;
        display: block;
    }

    .file-upload input[type="file"] {
        display: none;
    }
  .file-upload span {
        color: #666;
    }

    #file-name {
        margin-top: 10px;
        color: #666;
    }
    
    .current-image-preview {
        margin-bottom: 15px;
    }
      .current-image-preview img {
        max-width: 200px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<div class="main-content">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-edit"></i> Edit Post
        </h1>
        <a href="posts.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Posts
        </a>
    </div>

    <div class="form-container">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control"  value="<?= htmlspecialchars($post['title']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control "><?= htmlspecialchars($post['content']) ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category_id" required class="form-control">
                    <option value="" class="form-conrtol">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $post['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
<div class="form-group">
    <label class="form-label">Upload Image</label>
    <?php if (!empty($post['image'])): ?>
        <div class="current-image-preview mb-3">
            <p class="text-muted mb-4 mt-4">Current image:</p>
            <img src="../../upload/<?= htmlspecialchars($post['image']) ?>" alt="Current image">
        </div>
    <?php endif; ?>
    <div class="file-upload mb-3">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <span>Click or drag file to upload</span>
        <input type="file" name="image" id="file" accept="image/*">
    </div>
    <p id="file-name" class="text-center"></p>
</div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="draft"  class="form-control" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Views</label>
                <input type="number" name="views" class="form-control"  value="<?= (int)$post['views'] ?>" min="0">
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="posts.php" class="btn btn-secondary">Cancel</a><br>
                <button type="submit" class="btn btn-primary mt-5" style="margin-top:10px;">Update Post</button><br><br>
            </div>
        </form>
    </div>
</div>
<script>
    document.getElementById('file').onchange = function() {
        var fileName = this.files[0]?.name;
        document.getElementById('file-name').textContent = fileName || '';
    };

    // Make the whole div clickable
    document.querySelector('.file-upload').addEventListener('click', function() {
        document.getElementById('file').click();
    });
</script>

<?php include "footer.php"; ?>