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
    <title>Upload Assignment - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            justify-content: center;
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
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.1);
        }
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white !important;
            border-bottom: none;
            padding: 1.5rem 1.5rem 1rem 1.5rem;
            border-radius: 15px 15px 0 0;
        }
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--gray-light);
            transition: var(--transition);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-primary, .btn-dark {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            color: #fff;
            font-weight: 500;
            transition: var(--transition);
        }
        .btn-primary:hover, .btn-dark:hover {
            background: linear-gradient(90deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
        }
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
            border: none;
        }
        .modal-header.bg-success, .modal-header.bg-danger {
            border-radius: 15px 15px 0 0;
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
        #loadingModal .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        @media (max-width: 768px) {
            .page-header {
                padding: 1.2rem 1rem;
            }
            .card-header {
                padding: 1rem 1rem 0.7rem 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/teacher-navbar.php'; ?>
    
    <div class="container mt-4">
        
       
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i>Upload New Assignment</h4>
                    </div>
                    <div class="card-body">
                        <form id="assignmentForm">
                            <div class="mb-3">
                                <label for="title" class="form-label">Assignment Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="courseSelect" class="form-label">Select Course</label>
                                <select class="form-select" id="courseSelect" name="course_id" required>
                                    <option value="">Select a course...</option>
                                </select>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="dueDate" class="form-label">Due Date</label>
                                    <input type="datetime-local" class="form-control" id="dueDate" name="due_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="totalMarks" class="form-label">Total Marks</label>
                                    <input type="number" class="form-control" id="totalMarks" name="total_marks" min="1" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="assignmentFile" class="form-label">Assignment File (Optional)</label>
                                <input type="file" class="form-control" id="assignmentFile" name="assignment_file" accept=".pdf,.doc,.docx,.txt">
                                <div class="form-text">Allowed file types: PDF, DOC, DOCX, TXT</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-dark animate__animated animate__bounceIn animate__delay-1s">Upload Assignment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner Modal -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="modal-title">Uploading Assignment...</h5>
                    <p class="mb-0">Please wait while we process your request.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Success</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Assignment has been uploaded successfully!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="assignment-show.html" class="btn btn-success">View Assignments</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Error</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="errorMessage">An error occurred while uploading the assignment.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        // Initialize Bootstrap modals
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));

        // Set minimum date for due date input to today
        const today = new Date();
        today.setMinutes(today.getMinutes() - today.getTimezoneOffset());
        document.getElementById('dueDate').min = today.toISOString().slice(0, 16);

        // Fetch courses when page loads
        document.addEventListener('DOMContentLoaded', function() {
            fetchCourses();
            // Add animation classes to card when it comes into view
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.card');
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

        // Fetch courses for the logged-in teacher
        function fetchCourses() {
            fetch('handlers/get_teacher_courses.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch courses');
                    }
                    return response.json();
                })
                .then(data => {
                    const courseSelect = document.getElementById('courseSelect');
                    if (data.status === 'success') {
                        data.courses.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = `${course.course_code} - ${course.course_name}`;
                            courseSelect.appendChild(option);
                        });
                    } else {
                        throw new Error(data.message || 'Failed to load courses');
                    }
                })
                .catch(error => {
                    document.getElementById('errorMessage').textContent = error.message;
                    errorModal.show();
                });
        }

        // Handle form submission
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading modal
            loadingModal.show();
            
            // Create FormData object
            const formData = new FormData(this);
            
            // Submit form
            fetch('handlers/assignment_upload_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Upload failed');
                    });
                }
                return response.json();
            })
            .then(data => {
                loadingModal.hide();
                if (data.status === 'success') {
                    successModal.show();
                    this.reset();
                } else {
                    throw new Error(data.message || 'Upload failed');
                }
            })
            .catch(error => {
                loadingModal.hide();
                document.getElementById('errorMessage').textContent = error.message;
                errorModal.show();
            });
        });
    </script>
</body>
</html>
