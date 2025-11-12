<?php
session_start();

$site_title = "Admin Panel";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog</title>
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
  * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* Floating Particles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: floatParticles 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes floatParticles {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-20px) scale(1.1);
            }
        }

        /* Waves Animation */
        body::after {
            content: '';
            position: fixed;
            bottom: 0;
            left: 0;
            width: 200%;
            height: 200px;
            background: linear-gradient(to top, rgba(255,255,255,0.1), transparent);
            animation: wave 10s linear infinite;
            pointer-events: none;
            z-index: 1;
        }

        @keyframes wave {
            0% {
                transform: translateX(0) translateY(0);
            }
            50% {
                transform: translateX(-25%) translateY(-10px);
            }
            100% {
                transform: translateX(-50%) translateY(0);
            }
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(33, 37, 41, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .container-fluid {
            max-width: 100%;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            color: #ffffff;
            text-decoration: none;
            font-size: 1.25rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: opacity 0.3s ease;
        }

        .navbar-brand:hover {
            opacity: 0.8;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .btn-logout {
            background-color: transparent;
            color: #f8f9fa;
            border: 1px solid #f8f9fa;
            padding: 0.4rem 1rem;
            border-radius: 0.25rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background-color: #f8f9fa;
            color: #212529;
        }

        /* Sidebar Styles */
        .sidebar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-left: 1px solid rgba(222, 226, 230, 0.5);
            box-shadow: -4px 0 20px rgba(0,0,0,0.1);
            min-height: calc(100vh - 60px);
            width: 250px;
            position: fixed;
            top: 60px;
            right: 0;
            overflow-y: auto;
            padding-bottom: 60px;
            z-index: 999;
        }

        .sidebar-sticky {
            padding-top: 1rem;
        }

        .sidebar-heading {
            padding: 0.5rem 1rem;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            color: #6c757d;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin-bottom: 1rem;
        }

        .nav-item {
            margin: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #495057;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border-right: 3px solid transparent;
        }

        .nav-link:hover {
            background-color: #f8f9fa;
            color: #212529;
        }

        .nav-link.active {
            background-color: #e7f3ff;
            color: #0d6efd;
            border-right-color: #0d6efd;
            font-weight: 500;
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-right: 260px;
            margin-top: 74px;
            padding: 2rem;
            min-height: calc(100vh - 120px);
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin-left: 1rem;
            margin-bottom: 70px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        /* categories */
            /* Page Title */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid rgba(0,0,0,0.1);
    }

    .page-header h1 {
        font-size: 1.75rem;
        color: #333;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Alert Messages */
    .alert {
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .btn-close {
        background: transparent;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.5;
        transition: opacity 0.3s;
    }

    .btn-close:hover {
        opacity: 1;
    }

    /* Card Styles */
    .card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        font-weight: 600;
    }

    .card-header h5 {
        margin: 0;
        font-size: 1.1rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Form Styles */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1.5fr 0.5fr;
        gap: 1rem;
        margin-bottom: 0;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #555;
        font-size: 0.9rem;
    }

    .form-control {
        padding: 0.6rem;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 0.95rem;
        transition: all 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Button Styles */
    .btn {
        padding: 0.6rem 1.2rem;
        border: none;
        border-radius: 6px;
        font-size: 0.95rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
        text-decoration: none;
        font-weight: 500;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

    .w-100 {
        width: 100%;
    }

    /* Table Styles */
    .table-responsive {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        background: white;
    }

    .table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .table th,
    .table td {
        padding: 1rem;
        text-align: right;
        border-bottom: 1px solid #e0e0e0;
    }

    .table th {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .table tbody tr {
        transition: background 0.3s;
    }

    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    .table td {
        color: #555;
    }

    .actions-cell {
        display: flex;
        gap: 0.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

        /* Footer Styles */
        footer {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 10px;
            background: rgba(33, 37, 41, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            width: calc(100% - 250px);
            bottom: 0;
            left: 0;
            color: white;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.3);
            z-index: 999;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
                top: 60px;
            }

            .main-content {
                margin-right: 0;
                padding: 1rem;
            }

            footer {
                width: 100%;
                
            }

            .container-fluid {
                flex-direction: column;
                gap: 1rem;
            }

            .navbar-actions {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>

</head>
<body>

