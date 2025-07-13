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

// Only allow students to access this page
if ($userRole !== 'student') {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grades - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #6366f1;
            --accent: #8b5cf6;
            --light: #f8fafc;
            --dark: #1e293b;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-light: #f1f5f9;
            --gray: #94a3b8;
            --transition: all 0.3s ease;
        }
        
        body {
            background-color: var(--light);
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            margin: 0 0 2rem 0;
            padding: 2rem 1.5rem;
            color: white;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.3);
        }
        
        .page-header h1 {
            margin-bottom: 0;
            font-weight: 600;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .grade-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: var(--gray-light);
            border-radius: 10px;
            transition: var(--transition);
        }
        
        .grade-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .grade-score {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .grade-details {
            flex: 1;
            margin-left: 1rem;
        }
        
        .grade-course {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        
        .grade-assignment {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .grade-date {
            color: var(--gray);
            font-size: 0.8rem;
        }
        
        .stats-card {
            text-align: center;
            padding: 2rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            color: var(--gray);
            font-weight: 500;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 1.2rem 1rem;
            }
            
            .grade-item {
                flex-direction: column;
                text-align: center;
            }
            
            .grade-details {
                margin-left: 0;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/student-navbar.php'; ?>

    <div class="container mt-4">
        <div class="page-header animate__animated animate__fadeIn">
            <h1><i class="fas fa-chart-line me-2"></i>My Grades</h1>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                <div class="card stats-card">
                    <div class="stat-number" id="gpa">3.8</div>
                    <div class="stat-label">Current GPA</div>
                </div>
            </div>
            <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <div class="card stats-card">
                    <div class="stat-number" id="totalAssignments">12</div>
                    <div class="stat-label">Total Assignments</div>
                </div>
            </div>
            <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                <div class="card stats-card">
                    <div class="stat-number" id="completedAssignments">10</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
            <div class="col-md-3 animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                <div class="card stats-card">
                    <div class="stat-number" id="averageGrade">85%</div>
                    <div class="stat-label">Average Grade</div>
                </div>
            </div>
        </div>

        <!-- Grades List -->
        <div class="card animate__animated animate__fadeInUp" style="animation-delay: 0.5s">
            <div class="card-body">
                <h3 class="mb-4"><i class="fas fa-list me-2"></i>Recent Grades</h3>
                
                <div id="gradesList">
                    <!-- Sample grades - in a real application, these would come from the database -->
                    <div class="grade-item">
                        <div class="grade-score">A</div>
                        <div class="grade-details">
                            <div class="grade-course">Data Structures & Algorithms</div>
                            <div class="grade-assignment">Final Project - Binary Search Tree Implementation</div>
                            <div class="grade-date">Submitted: Dec 15, 2024</div>
                        </div>
                    </div>
                    
                    <div class="grade-item">
                        <div class="grade-score">B+</div>
                        <div class="grade-details">
                            <div class="grade-course">Database Management Systems</div>
                            <div class="grade-assignment">SQL Query Optimization Assignment</div>
                            <div class="grade-date">Submitted: Dec 10, 2024</div>
                        </div>
                    </div>
                    
                    <div class="grade-item">
                        <div class="grade-score">A-</div>
                        <div class="grade-details">
                            <div class="grade-course">Web Development</div>
                            <div class="grade-assignment">E-commerce Website Project</div>
                            <div class="grade-date">Submitted: Dec 8, 2024</div>
                        </div>
                    </div>
                    
                    <div class="grade-item">
                        <div class="grade-score">B</div>
                        <div class="grade-details">
                            <div class="grade-course">Software Engineering</div>
                            <div class="grade-assignment">Requirements Analysis Document</div>
                            <div class="grade-date">Submitted: Dec 5, 2024</div>
                        </div>
                    </div>
                </div>
                
                <!-- Empty state (hidden by default) -->
                <div id="emptyState" class="empty-state" style="display: none;">
                    <div class="empty-state-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>No Grades Available</h4>
                    <p class="text-muted">Your grades will appear here once assignments are graded by your teachers.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add any JavaScript functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // You can add AJAX calls to fetch real grades from the server
            console.log('Grades page loaded');
        });
    </script>
</body>
</html> 