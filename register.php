<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync Scholars</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .btn-primary, .btn-dark, .btn-lg {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            color: #fff;
            font-weight: 500;
            transition: var(--transition);
        }
        .btn-primary:hover, .btn-dark:hover, .btn-lg:hover {
            background: linear-gradient(90deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.4);
        }
        .section-title {
            background-color: var(--gray-light);
            padding: 10px 15px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
            border-radius: 4px;
            font-weight: 600;
            color: var(--primary);
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
<body class="bg-light">
  
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand fw-bold" href="#">Sync Scholars</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                        <li class="nav-item"><a class="nav-link active" href="register.html">Register</a></li>
                        <li class="nav-item"><a class="nav-link" href="login.html">Login</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container py-5">
        <div class="card animate__animated animate__fadeInUp">
            <div class="card-header">
                <h2 class="card-title text-center mb-0"><i class="fas fa-user-edit me-2"></i>Register Account</h2>
            </div>
            <div class="card-body p-4">
                <form action="register_handler.php" method="POST" id="registrationForm">
                    <h3 class="section-title h5">Personal Information</h3>
                    <div class="row mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dob" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <h3 class="section-title h5">Contact Information</h3>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                    
                 
                    
                    <h3 class="section-title h5">Account Setup</h3>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label">Register as</label>
                        <select class="form-select" id="role" name="role" required onchange="toggleRoleFields()">
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                        </select>
                    </div>
                    
                    <!-- Student-specific fields -->
                    <div id="studentFields" style="display: none;">
                        <h3 class="section-title h5">Student Information</h3>
                        <div class="mb-3">
                            <label for="rollNumber" class="form-label">Roll Number</label>
                            <input type="text" class="form-control" id="rollNumber" name="rollNumber" required>
                        </div>
                        <div class="mb-3">
                            <label for="program" class="form-label">Program</label>
                            <select class="form-select" id="program" name="program" required>
                                <option value="">Select Program</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Business">Business</option>
                                <option value="Arts">Arts & Humanities</option>
                                <option value="Science">Science</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="semester" class="form-label">Semester</label>
                                <input type="number" class="form-control" id="semester" name="semester" min="1" max="8" required>
                            </div>
                            <div class="col-md-6">
                                <label for="batchYear" class="form-label">Batch Year</label>
                                <input type="number" class="form-control" id="batchYear" name="batchYear" min="2000" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="prevSchool" class="form-label">Previous School</label>
                            <input type="text" class="form-control" id="prevSchool" name="prevSchool" required>
                        </div>
                        <div class="mb-4">
                            <label for="gpa" class="form-label">GPA</label>
                            <input type="number" class="form-control" id="gpa" name="gpa" step="0.01" min="0" max="4" required>
                        </div>
                    </div>

                    <!-- Teacher-specific fields -->
                    <div id="teacherFields" style="display: none;">
                        <h3 class="section-title h5">Teacher Information</h3>
                        <div class="mb-3">
                            <label for="employeeId" class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="employeeId" name="employeeId">
                        </div>
                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">Select Department</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Business">Business</option>
                                <option value="Arts">Arts & Humanities</option>
                                <option value="Science">Science</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="designation" class="form-label">Designation</label>
                            <select class="form-select" id="designation" name="designation">
                                <option value="">Select Designation</option>
                                <option value="Professor">Professor</option>
                                <option value="Associate Professor">Associate Professor</option>
                                <option value="Assistant Professor">Assistant Professor</option>
                                <option value="Lecturer">Lecturer</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="qualification" class="form-label">Qualification</label>
                            <input type="text" class="form-control" id="qualification" name="qualification">
                        </div>
                        <div class="mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization">
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="joiningDate" class="form-label">Joining Date</label>
                                <input type="date" class="form-control" id="joiningDate" name="joiningDate">
                            </div>
                            <div class="col-md-6">
                                <label for="experienceYears" class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" id="experienceYears" name="experienceYears" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-dark btn-lg animate__animated animate__bounceIn animate__delay-1s">Register</button>
                    </div>
                    
                    <div class="text-center">
                        <p class="mb-0">Already have an account? <a href="login.html" class="text-decoration-none text-dark">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer id="contact" class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h3 class="h5 mb-3">About Us</h3>
                    <p class="mb-0">We are dedicated to providing quality education and resources to help students succeed in their academic journey.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h3 class="h5 mb-3">Contact Info</h3>
                    <p class="mb-1">Email: hassan@gamil.com</p>
                    <p class="mb-1">Phone: 123 123 123</p>
                    <p class="mb-0">Address: HF Town</p>
                </div>
                <div class="col-md-4">
                    <h3 class="h5 mb-3">Quick Links</h3>
                    <p class="mb-1"><a href="index.html" class="text-white text-decoration-none">Home</a></p>
                    <p class="mb-1"><a href="register.html" class="text-white text-decoration-none">Register</a></p>
                    <p class="mb-0"><a href="login.html" class="text-white text-decoration-none">Login</a></p>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            const studentFields = document.getElementById('studentFields');
            const teacherFields = document.getElementById('teacherFields');
            
            // Hide all role-specific fields first
            studentFields.style.display = 'none';
            teacherFields.style.display = 'none';
            
            // Show fields based on selected role
            if (role === 'student') {
                studentFields.style.display = 'block';
                // Make student fields required
                document.querySelectorAll('#studentFields input, #studentFields select').forEach(input => {
                    input.required = true;
                });
                // Make teacher fields not required
                document.querySelectorAll('#teacherFields input, #teacherFields select').forEach(input => {
                    input.required = false;
                });
            } else if (role === 'teacher') {
                teacherFields.style.display = 'block';
                // Make teacher fields required
                document.querySelectorAll('#teacherFields input, #teacherFields select').forEach(input => {
                    input.required = true;
                });
                // Make student fields not required
                document.querySelectorAll('#studentFields input, #studentFields select').forEach(input => {
                    input.required = false;
                });
            }
        }

        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const jsonData = {};
            
            // Convert FormData to JSON object
            formData.forEach((value, key) => {
                // Convert empty strings to null
                jsonData[key] = value.trim() === '' ? null : value.trim();
            });
            
            // Log the data being sent
            console.log('Sending data:', jsonData);
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = 'Processing...';
            submitButton.disabled = true;
            
            // Submit form
            fetch('/LMS_System/sync_scholar/register_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(jsonData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        console.error('Server error:', errorData);
                        throw new Error(errorData.message || 'Registration failed');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);
                if (data.status === 'error') {
                    alert(data.message);
                } else {
                    alert('Registration successful! Redirecting to login page...');
                    window.location.href = data.data.redirect || 'login.html';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'An error occurred during registration. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            });
        });

        // Set current year as min for batch year
        document.getElementById('batchYear').min = new Date().getFullYear();

        document.addEventListener('DOMContentLoaded', function() {
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
    </script>
</body>
</html>




