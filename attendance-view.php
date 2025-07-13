<?php
session_start();
require_once 'config/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance - SyncScholar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="./css/styles.css" rel="stylesheet">
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
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        @media (max-width: 768px) {
            .page-header {
                padding: 1.2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/teacher-navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="page-header animate__animated animate__fadeIn">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                <h1 class="h2"><i class="fas fa-clipboard-check me-2"></i>View Attendance</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary animate__animated animate__bounceIn animate__delay-1s" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload"></i> Upload CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card animate__animated animate__fadeInUp mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-6">
                        <label for="courseSelect" class="form-label">Course</label>
                        <select class="form-select" id="courseSelect" required>
                            <option value="">Select Course</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="statusSelect" class="form-label">Status</label>
                        <select class="form-select" id="statusSelect">
                            <option value="">All Status</option>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button type="button" class="btn btn-secondary" id="resetFilters">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="card animate__animated animate__fadeInUp">
            <div class="card-body">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover" id="attendanceTable">
                            <thead>
                                <tr>
                                    <th>Roll Number</th>
                                    <th>Student Name</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceList">
                                <!-- Attendance records will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="noRecords" class="text-center d-none">
                    <p class="text-muted">No attendance records found</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" inert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Attendance CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm">
                        <div class="mb-3">
                            <label for="courseSelect" class="form-label">Select Course</label>
                            <select class="form-select" id="uploadCourseSelect" required>
                                <option value="">Select Course</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Select CSV File</label>
                            <input type="file" class="form-control" id="csvFile" accept=".csv" required>
                        </div>
                        <div class="mb-3">
                            <p class="text-muted">
                                <small>
                                    The CSV file should have the following columns:<br>
                                    student_id, status, remarks<br>
                                    Status values: present, absent, late
                                </small>
                            </p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="uploadButton">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation classes to card and table when they come into view
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
        });
    </script>
    <script src="./js/attendance-view.js"></script>
</body>
</html>


