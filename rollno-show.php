<?php
session_start();
require_once 'config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user role
$userId = $_SESSION['user_id'];
$roleQuery = "SELECT r.name as role_name FROM users u 
             JOIN roles r ON u.role_id = r.id 
             WHERE u.id = ?";
$stmt = $conn->prepare($roleQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$roleResult = $stmt->get_result();
$roleData = $roleResult->fetch_assoc();
$userRole = $roleData['role_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Roll No Slip - Sync Scholars</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom styles -->
    <style>
        .page-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 30px;
        }
        .content-container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            padding: 30px;
            transition: all 0.3s ease;
        }
        .content-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
        }
        .btn-custom {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .slip-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        .slip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.15);
        }
        .slip-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 15px 20px;
        }
        .slip-body {
            padding: 20px;
        }
        .slip-footer {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid #e9ecef;
        }
        .status-badge {
            padding: 8px 12px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .status-verified {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }
        .status-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        .status-rejected {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        .pdf-preview {
            width: 100%;
            height: 500px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .student-info {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .info-label {
            font-weight: 600;
            min-width: 120px;
            color: #6c757d;
        }
        .info-value {
            font-weight: 500;
        }
        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            background-color: rgba(106, 17, 203, 0.1);
            color: #6a11cb;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 25px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-dot {
            position: absolute;
            left: -30px;
            top: 0;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #fff;
            border: 2px solid #6a11cb;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }
        .timeline-content {
            background-color: #fff;
            border-radius: 5px;
            padding: 15px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .timeline-date {
            color: #6c757d;
            font-size: 0.875rem;
            margin-bottom: 5px;
        }
        .timeline-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .timeline-text {
            color: #6c757d;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <?php if ($userRole === 'student'): ?>
        <?php include 'includes/student-navbar.php'; ?>
    <?php elseif ($userRole === 'teacher'): ?>
        <?php include 'includes/teacher-navbar.php'; ?>
    <?php endif; ?>

    <section class="page-header animate__animated animate__fadeIn">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Roll Number Slip</h1>
            <p class="lead">View and download your roll number slip for examinations</p>
        </div>
    </section>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="content-container animate__animated animate__fadeInUp animate__delay-1s">
                    <!-- Content will be loaded by JavaScript -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading roll number slips...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2023 Sync Scholars. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-white me-3">Privacy Policy</a>
                    <a href="#" class="text-white me-3">Terms of Service</a>
                    <a href="#" class="text-white">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/rollno-show.js"></script>
    <!-- Custom JS -->
    <script>
        // Add animation to elements when they come into view
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.animate__animated:not(.animate__fadeIn)');
            elements.forEach(element => {
                const position = element.getBoundingClientRect();
                if(position.top < window.innerHeight) {
                    element.classList.add('animate__fadeIn');
                }
            });
        };

        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>
</html>