<!-- ../inc/sideBar.php -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Admin Dashboard</h3>
    </div>
    
    <ul class="sidebar-menu">
        <li class="menu-header">MAIN MENU</li>
        <li><a href="/Onsite/second-project/admin/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':''; ?>"><i class="fa-solid fa-house"></i><span>Dashboard</span></a></li>

        <li class="menu-header">CONTENT</li>
        <li><a href="/Onsite/second-project/admin/content/posts.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='posts.php'?'active':''; ?>"><i class="fa-solid fa-file-lines"></i><span>Posts</span></a></li>
        <li><a href="/Onsite/second-project/admin/content/categories.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='categories.php'?'active':''; ?>"><i class="fa-solid fa-tags"></i><span>Categories</span></a></li>
        <li><a href="/Onsite/second-project/admin/content/commints.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='commints.php'?'active':''; ?>"><i class="fa-solid fa-comments"></i><span>Comments</span></a></li>

        <li class="menu-header">MANAGEMENT</li>
        <li><a href="/Onsite/second-project/admin/users.php"><i class="fa-solid fa-users"></i><span>Users</span></a></li>

        <li class="menu-header">OTHER</li>
        <li><a href="/Onsite/second-project/index.php" target="_blank"><i class="fa-solid fa-globe"></i><span>View Site</span></a></li>
    </ul>
</nav>

<!-- Overlay للموبايل -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
    :root {
        --sidebar-width: 250px;
        --primary: #007bff;
        --text: #212529;
        --text-light: #6c757d;
        --border: #dee2e6;
        --hover: #f8f9fa;
    }

    /* السايد بار على اليمين */
    .sidebar {
        position: fixed;
        top: 0;
        right: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background: #ffffff;
        border-left: 1px solid var(--border);
        z-index: 999;
        font-family: 'Segoe UI', sans-serif;
        color: var(--text);
        box-shadow: -2px 0 10px rgba(0,0,0,0.05);
        transition: transform 0.3s ease;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid var(--border);
        background: #f8f9fa;
        text-align: center;
        font-size: 18px;
        font-weight: 600;
    }

    .sidebar-menu { list-style: none; padding: 0; margin: 0; }
    .menu-header {
        padding: 15px 20px 8px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-light);
    }

    .sidebar-menu li a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: var(--text);
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 14px;
    }

    .sidebar-menu li a i {
        width: 20px;
        margin-right: 12px;
        font-size: 16px;
        color: var(--text-light);
    }

    .sidebar-menu li a:hover {
        background: var(--hover);
        padding-right: 25px;
    }

    .sidebar-menu li a.active {
        background: #e7f3ff;
        color: var(--primary);
        border-right: 4px solid var(--primary);
        font-weight: 600;
    }

    .sidebar-menu li a.active i { color: var(--primary); }

    /* المحتوى يبعد عن اليمين */
    .main-content {
        margin-right: var(--sidebar-width);
        margin-left: 0 !important;
        min-height: 100vh;
        background: #f8f9fa;
        padding-top: 60px;
        transition: margin-right 0.3s ease;
    }

    /* Overlay */
    .sidebar-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 998;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s;
    }
    .sidebar-overlay.active { opacity: 1; visibility: visible; }

    /* ==================== RESPONSIVE ==================== */
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(100%);
        }
        .sidebar.active {
            transform: translateX(0);
        }
        .main-content {
            margin-right: 0 !important;
        }
    }

    @media (max-width: 576px) {
        .sidebar { width: 280px; }
        .sidebar-header { font-size: 16px; padding: 16px; }
        .sidebar-menu li a { font-size: 13px; padding: 14px 16px; }
    }
</style>