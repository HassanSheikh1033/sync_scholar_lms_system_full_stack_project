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
    <title>View Assignments</title>
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
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .page-header h2 {
            margin-bottom: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .page-header i {
            margin-right: 12px;
        }
        .assignment-card {
            transition: var(--transition);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 1.5rem;
            background: #fff;
        }
        .assignment-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.1);
        }
        .assignment-card .card-body {
            padding: 1.5rem;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 0.9rem;
            padding: 0.4em 1em;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .description-truncate {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            color: var(--gray);
        }
        .filter-section {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 1.5rem 1rem;
            margin-bottom: 2rem;
        }
        #loadingSpinner .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        #noResults, #errorMessage {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            padding: 2rem 1rem;
        }
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
            border: none;
        }
        .modal-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-bottom: none;
            padding: 1.5rem;
            border-radius: 15px 15px 0 0;
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
        .form-select, .form-control {
            border-radius: 8px;
            border: 1px solid var(--gray-light);
            transition: var(--transition);
        }
        .form-select:focus, .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        @media (max-width: 768px) {
            .page-header {
                padding: 1.2rem 1rem;
            }
            .assignment-card .card-body {
                padding: 1rem;
            }
            .filter-section {
                padding: 1rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php if ($userRole === 'student'): ?>
        <?php include 'includes/student-navbar.php'; ?>
    <?php elseif ($userRole === 'teacher'): ?>
        <?php include 'includes/teacher-navbar.php'; ?>
    <?php endif; ?>

    <div class="container mt-4">
        <div class="page-header animate__animated animate__fadeIn">
            <h2><i class="fas fa-file-alt me-2"></i>Assignments</h2>
            <?php if ($userRole === 'teacher'): ?>
            <a href="assignment-upload.php" class="btn btn-primary animate__animated animate__bounceIn animate__delay-1s">
                <i class="fas fa-plus"></i> Create New Assignment
            </a>
            <?php endif; ?>
        </div>
        <!-- Filter Section -->
        <div class="filter-section animate__animated animate__fadeInUp mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <select class="form-select" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="submitted">Submitted</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search assignments...">
                </div>
            </div>
        </div>
        <!-- Loading Spinner -->
        <div id="loadingSpinner" class="text-center d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <!-- Assignments Container -->
        <div class="row" id="assignmentsContainer">
            <!-- Assignments will be dynamically loaded here -->
        </div>
        <!-- No Results Message -->
        <div id="noResults" class="text-center mt-4 d-none animate__animated animate__fadeIn">
            <p class="text-muted">No assignments found matching your criteria.</p>
        </div>
        <!-- Error Message -->
        <div id="errorMessage" class="alert alert-danger mt-4 d-none animate__animated animate__fadeIn">
            <p>Error loading assignments. Please try again later.</p>
        </div>
    </div>

    <!-- Assignment Details Modal -->
    <div class="modal fade" id="assignmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assignment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="assignmentDetails">
                        <!-- Assignment details will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to format date
        function formatDate(dateString) {
            const options = { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return new Date(dateString).toLocaleDateString(undefined, options);
        }

        // Function to create assignment card
        function createAssignmentCard(assignment) {
            const dueDate = new Date(assignment.due_date);
            const now = new Date();
            const isOverdue = dueDate < now;
            
            return `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card assignment-card">
                        <div class="card-body">
                            <span class="badge ${isOverdue ? 'bg-danger' : 'bg-success'} status-badge">
                                ${isOverdue ? 'Overdue' : 'Active'}
                            </span>
                            <h5 class="card-title">${assignment.title}</h5>
                            <p class="card-text description-truncate">${assignment.description}</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">Due: ${formatDate(assignment.due_date)}</small>
                                <div class="btn-group">
                                   
                                    ${assignment.file_path ? `
                                        <a href="${assignment.file_path}" class="btn btn-secondary btn-sm" target="_blank">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Function to view assignment details
        function viewAssignment(assignmentId) {
            const spinner = document.getElementById('loadingSpinner');
            spinner.classList.remove('d-none');

            console.log('Fetching assignment details for ID:', assignmentId);

            fetch(`api/assignments/get_assignment.php?id=${assignmentId}`)
                .then(response => {
                    console.log('Raw response:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('API Response:', data);
                    spinner.classList.add('d-none');
                    
                    if (data.status === 'success' && data.data && data.data.assignment) {
                        const assignment = data.data.assignment;
                        console.log('Assignment data:', assignment);
                        
                        const modalContent = `
                            <div class="assignment-details">
                                <h4 class="mb-4">${assignment.title}</h4>
                                
                                <div class="mb-4">
                                    <h5>Description</h5>
                                    <p>${assignment.description}</p>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h5>Due Date</h5>
                                        <p>${formatDate(assignment.due_date)}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Teacher</h5>
                                        <p>${assignment.teacher_name}</p>
                                    </div>
                                </div>

                                ${assignment.file_path ? `
                                    <div class="mb-4">
                                        <h5>Assignment File</h5>
                                        <a href="${assignment.file_path}" class="btn btn-primary" target="_blank">
                                            <i class="fas fa-download"></i> Download Assignment
                                        </a>
                                    </div>
                                ` : ''}

                                ${assignment.submission ? `
                                    <div class="mb-4">
                                        <h5>Your Submission</h5>
                                        <div class="card">
                                            <div class="card-body">
                                                <p><strong>Submitted on:</strong> ${formatDate(assignment.submission.submission_date)}</p>
                                                <a href="${assignment.submission.submission_file}" class="btn btn-primary" target="_blank">
                                                    <i class="fas fa-download"></i> Download Submission
                                                </a>
                                                ${assignment.submission.grade ? `
                                                    <div class="mt-3">
                                                        <p><strong>Grade:</strong> ${assignment.submission.grade}</p>
                                                        <p><strong>Feedback:</strong> ${assignment.submission.feedback || 'No feedback provided'}</p>
                                                    </div>
                                                ` : '<p class="mt-3"><em>Not graded yet</em></p>'}
                                            </div>
                                        </div>
                                    </div>
                                ` : `
                                    <div class="mb-4">
                                        <h5>Submit Assignment</h5>
                                        <form id="submissionForm" class="mt-3">
                                            <div class="mb-3">
                                                <label for="submissionFile" class="form-label">Upload your submission</label>
                                                <input type="file" class="form-control" id="submissionFile" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> Submit Assignment
                                            </button>
                                        </form>
                                    </div>
                                `}
                            </div>
                        `;
                        document.getElementById('assignmentDetails').innerHTML = modalContent;
                        
                        // Initialize the modal
                        const modal = new bootstrap.Modal(document.getElementById('assignmentModal'));
                        modal.show();

                        // Add form submission handler if submission form exists
                        const submissionForm = document.getElementById('submissionForm');
                        if (submissionForm) {
                            submissionForm.addEventListener('submit', function(e) {
                                e.preventDefault();
                                submitAssignment(assignmentId, this);
                            });
                        }
                    } else {
                        console.error('Invalid response format:', data);
                        alert('Error loading assignment details. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    spinner.classList.add('d-none');
                    alert('Error loading assignment details. Please try again.');
                });
        }

        // Function to submit assignment
        function submitAssignment(assignmentId, form) {
            const formData = new FormData(form);
            formData.append('assignment_id', assignmentId);

            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';

            fetch('api/assignments/submit_assignment.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Assignment submitted successfully!');
                    location.reload();
                } else {
                    throw new Error(data.message || 'Failed to submit assignment');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting assignment: ' + error.message);
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-upload"></i> Submit Assignment';
            });
        }

        // Function to load assignments
        function loadAssignments() {
            const spinner = document.getElementById('loadingSpinner');
            const noResults = document.getElementById('noResults');
            const errorMessage = document.getElementById('errorMessage');
            
            spinner.classList.remove('d-none');
            noResults.classList.add('d-none');
            errorMessage.classList.add('d-none');

            fetch('api/assignments/get_assignments.php')
                .then(response => response.json())
                .then(data => {
                    spinner.classList.add('d-none');
                    console.log('API Response:', data);
                    
                    if (data.status === 'success' && data.data && data.data.assignments) {
                        const container = document.getElementById('assignmentsContainer');
                        const assignments = data.data.assignments;
                        
                        if (assignments.length === 0) {
                            noResults.classList.remove('d-none');
                            container.innerHTML = '';
                        } else {
                            container.innerHTML = assignments.map(assignment => 
                                createAssignmentCard(assignment)
                            ).join('');
                        }
                    } else {
                        errorMessage.classList.remove('d-none');
                        console.error('API Error:', data.message || 'Invalid response format');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    spinner.classList.add('d-none');
                    errorMessage.classList.remove('d-none');
                });
        }

        // Load assignments when page loads
        document.addEventListener('DOMContentLoaded', loadAssignments);

        // Add event listeners for filters
        document.getElementById('statusFilter').addEventListener('change', loadAssignments);
        document.getElementById('searchInput').addEventListener('input', loadAssignments);

        document.addEventListener('DOMContentLoaded', function() {
            // Add animation classes to assignment cards when they come into view
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.assignment-card');
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
</body>
</html>

