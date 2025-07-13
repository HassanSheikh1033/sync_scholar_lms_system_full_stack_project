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
    <title>Upload Roll No Slip | Sync Scholars</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom styles -->
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../gallery2.avif');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
        }
        .form-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .form-section:hover {
            transform: translateY(-5px);
        }
        .upload-icon {
            font-size: 4rem;
            color: #6610f2;
        }
        .btn-upload {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
        }
        .btn-upload:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.4);
        }
        .file-upload {
            position: relative;
            overflow: hidden;
            margin: 10px 0;
        }
        .file-upload input[type=file] {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 100%;
            min-height: 100%;
            font-size: 100px;
            text-align: right;
            filter: alpha(opacity=0);
            opacity: 0;
            outline: none;
            cursor: pointer;
            display: block;
        }
        .floating-card {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .info-card {
            border-left: 4px solid #6610f2;
            background-color: rgba(106, 17, 203, 0.05);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6610f2;
            box-shadow: 0 0 0 0.25rem rgba(106, 17, 203, 0.25);
        }
    </style>
</head>
<body>
    <?php include 'includes/teacher-navbar.php'; ?>

    <section class="hero-section text-center animate__animated animate__fadeIn">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Roll No Slip Upload</h1>
            <p class="lead mb-0">Upload roll number slips for students' exam registration</p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-section p-5 animate__animated animate__fadeInUp">
                        <div class="text-center mb-4">
                            <div class="upload-icon mb-3 floating-card">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-card-heading" viewBox="0 0 16 16">
                                    <path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/>
                                    <path d="M3 8.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5zm0-5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5v-1z"/>
                                </svg>
                            </div>
                            <h2 class="h3 fw-bold">Upload Roll Number Slip</h2>
                            <p class="text-muted">Upload roll number slips for students' upcoming examinations</p>
                        </div>

                        <div class="info-card mb-4 animate__animated animate__fadeIn animate__delay-1s">
                            <h5 class="mb-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle-fill me-2" viewBox="0 0 16 16">
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                            </svg>Important Notice</h5>
                            <p class="mb-0">Please ensure the roll number slip is in PDF format and clearly visible. The file size should not exceed 5MB. Enter the student's roll number for proper identification.</p>
                        </div>

                        <form id="rollNoSlipForm" method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="roll_number" class="form-label fw-bold">Student Roll Number</label>
                                <input type="text" class="form-control form-control-lg" id="roll_number" name="roll_number" placeholder="e.g. 23-ARID-4569" required>
                            </div>

                            <div class="mb-4">
                                <label for="examType" class="form-label fw-bold">Exam Type</label>
                                <select class="form-select form-select-lg" id="examType" name="examType" required>
                                    <option value="" selected disabled>Select exam type...</option>
                                    <option value="midterm">Midterm Examination</option>
                                    <option value="final">Final Examination</option>
                                    <option value="special">Special Examination</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="semester" class="form-label fw-bold">Semester</label>
                                <select class="form-select form-select-lg" id="semester" name="semester" required>
                                    <option value="" selected disabled>Choose a semester...</option>
                                    <option value="Fall2023">Fall 2023</option>
                                    <option value="Spring2024">Spring 2024</option>
                                    <option value="Fall2024">Fall 2024</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Roll Number Slip (PDF)</label>
                                <div class="file-upload d-grid">
                                    <button type="button" class="btn btn-outline-primary btn-lg">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload me-2" viewBox="0 0 16 16">
                                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708z"/>
                                        </svg>
                                        Choose File
                                    </button>
                                    <input type="file" class="form-control" id="rollNoSlip" name="rollNoSlip" accept=".pdf" required>
                                </div>
                                <div id="fileHelp" class="form-text">Supported format: PDF (.pdf) only. Max size: 5MB</div>
                            </div>

                            <div class="mb-4">
                                <label for="comments" class="form-label fw-bold">Additional Comments (Optional)</label>
                                <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Any additional information about the student or slip..."></textarea>
                            </div>

                            <div class="d-grid gap-2 mt-5">
                                <button type="submit" class="btn btn-primary btn-lg btn-upload animate__animated animate__pulse animate__infinite">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cloud-arrow-up me-2" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M7.646 5.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 6.707V10.5a.5.5 0 0 1-1 0V6.707L6.354 7.854a.5.5 0 1 1-.708-.708z"/>
                                        <path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383"/>
                                    </svg>
                                    Upload Roll Number Slip
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h3 class="h5 mb-3">About Us</h3>
                    <p class="mb-0">We are dedicated to providing quality education and resources to help students succeed in their academic journey.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h3 class="h5 mb-3">Contact Info</h3>
                    <p class="mb-1">Email: hassan@gmail.com</p>
                    <p class="mb-1">Phone: 123 123 123</p>
                    <p class="mb-0">Address: HF Town</p>
                </div>
                <div class="col-md-4">
                    <h3 class="h5 mb-3">Quick Links</h3>
                    <p class="mb-1"><a href="index.html" class="text-white text-decoration-none">Home</a></p>
                    <p class="mb-1"><a href="attendance-show.html" class="text-white text-decoration-none">Attendance</a></p>
                    <p class="mb-0"><a href="assignment-show.html" class="text-white text-decoration-none">Assignments</a></p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 Sync Scholars. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Roll Number Slip Upload JavaScript -->
    <script src="js/rollno-upload.js"></script>
</body>
</html>

