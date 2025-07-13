<?php
session_start();
require_once('./config/db_config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Management - SyncScholar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="./css/styles.css" rel="stylesheet">
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
        #sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--dark), #0f172a);
            padding-top: 1rem;
            transition: var(--transition);
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
        }
        #sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.8rem 1.2rem;
            margin: 0.4rem 0.8rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            transition: var(--transition);
        }
        #sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        #sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }
        #sidebar .nav-link:hover i {
            transform: scale(1.2);
        }
        #sidebar .nav-link.active {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        main {
            padding: 1.5rem;
            transition: var(--transition);
        }
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            margin: -1.5rem -1.5rem 1.5rem -1.5rem;
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
        .table-container {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background-color: var(--gray-light);
            color: var(--dark);
            font-weight: 600;
            border-top: none;
            padding: 1rem;
            white-space: nowrap;
        }
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        .table tr {
            transition: var(--transition);
        }
        .table tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.05);
        }
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
        }
        .btn-secondary {
            background-color: var(--gray-light);
            color: var(--dark);
            border: none;
        }
        .btn-secondary:hover {
            background-color: var(--gray);
            color: white;
        }
        .btn-danger {
            background: linear-gradient(90deg, var(--danger), #f87171);
            border: none;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .btn-danger:hover {
            background: linear-gradient(90deg, #dc2626, var(--danger));
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4);
        }
        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }
        .modal-title {
            font-weight: 600;
        }
        .modal-body {
            padding: 1.5rem;
        }
        .modal-footer {
            border-top: 1px solid var(--gray-light);
            padding: 1.2rem 1.5rem;
        }
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--gray-light);
            transition: var(--transition);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            margin: 0 3px;
        }
        .action-btn-edit {
            background-color: rgba(99, 102, 241, 0.1);
            color: var(--secondary);
        }
        .action-btn-edit:hover {
            background-color: var(--secondary);
            color: white;
        }
        .action-btn-delete {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        .action-btn-delete:hover {
            background-color: var(--danger);
            color: white;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        .slide-up {
            animation: slideInUp 0.5s ease-in-out;
        }
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
        }
        .empty-state-icon {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            #sidebar {
                min-height: auto;
            }
            main {
                margin-top: 1rem;
            }
            .page-header {
                margin: -1.5rem -1.5rem 1.5rem -1.5rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="">
    <?php include 'includes/teacher-navbar.php'; ?>
        <div class="row">
          
          

            <!-- Main content -->
            <main class=" ms-sm-auto  px-md-4">
                <div class="page-header animate__animated animate__fadeIn">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h1 class="h2"><i class="fas fa-user-graduate me-2"></i>Enrollment Management</h1>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <button type="button" class="btn btn-primary animate__animated animate__bounceIn animate__delay-1s" data-bs-toggle="modal" data-bs-target="#enrollmentModal">
                                <i class="fas fa-plus"></i> Add New Enrollment
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Course Filter -->
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="courseFilterSelect" class="form-label">Filter by Course</label>
                                <select class="form-select" id="courseFilterSelect">
                                    <option value="">All Courses</option>
                                    <!-- Course options will be loaded here -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="statusFilterSelect" class="form-label">Filter by Status</label>
                                <select class="form-select" id="statusFilterSelect">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="dropped">Dropped</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Table -->
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-body">
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-hover" id="enrollmentTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Course ID</th>
                                            <th>Course Name</th>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Status</th>
                                            <th>Enrollment Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="enrollmentList">
                                        <!-- Enrollment records will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="noEnrollments" class="empty-state d-none animate__animated animate__fadeIn">
                            <div class="empty-state-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h4>No Enrollments Found</h4>
                            <p class="text-muted">Get started by adding your first enrollment</p>
                            <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#enrollmentModal">
                                <i class="fas fa-plus"></i> Add New Enrollment
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Enrollment Modal -->
    <div class="modal fade" id="enrollmentModal" tabindex="-1" aria-labelledby="enrollmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enrollmentModalLabel"><i class="fas fa-edit me-2"></i>Add New Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="enrollmentForm">
                        <input type="hidden" id="enrollmentId" value="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="courseSelect" class="form-label">Course</label>
                                <select class="form-select" id="courseSelect" required>
                                    <option value="">Select Course</option>
                                    <!-- Course options will be loaded here -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="studentSelect" class="form-label">Student</label>
                                <select class="form-select" id="studentSelect" required>
                                    <option value="">Select Student</option>
                                    <!-- Student options will be loaded here -->
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="statusSelect" class="form-label">Status</label>
                                <select class="form-select" id="statusSelect" required>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="dropped">Dropped</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" id="saveEnrollmentBtn">
                        <i class="fas fa-save"></i> Save Enrollment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-trash-alt text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-center">Are you sure you want to delete this enrollment? This action cannot be undone.</p>
                    <input type="hidden" id="deleteId" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.card, .btn, .table-container');
                elements.forEach(element => {
                    const position = element.getBoundingClientRect();
                    if(position.top < window.innerHeight) {
                        element.classList.add('animate__animated', 'animate__fadeInUp');
                    }
                });
            };
            animateOnScroll();
            window.addEventListener('scroll', animateOnScroll);
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                    this.style.boxShadow = '0 0 10px rgba(0,0,0,0.1)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
    <script src="./js/enrollment-management.js"></script>
</body>
</html>