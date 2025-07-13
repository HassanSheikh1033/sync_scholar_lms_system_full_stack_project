<?php
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #8b5cf6;
            --accent-color: #a78bfa;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
        }
        
        body {
            background-color: #f0f4f8;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, var(--dark-color), #2d3748) !important;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(90deg, var(--accent-color), var(--primary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 0.5px;
        }
        
        .nav-link {
            position: relative;
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            transition: all 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background-color: var(--accent-color);
            bottom: 0;
            left: 0;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after, .nav-link.active::after {
            width: 100%;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(124, 58, 237, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(30deg);
        }
        
        .dashboard-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            background: white;
        }
        
        .dashboard-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .dashboard-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .classes-icon {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            color: white;
        }
        
        .assignments-icon {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            color: white;
        }
        
        .attendance-icon {
            background: linear-gradient(135deg, #06b6d4, #0ea5e9);
            color: white;
        }
        
        .card-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
        }
        
        .card-text {
            color: #64748b;
            margin-bottom: 1.5rem;
        }
        
        .btn-card {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-classes {
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            color: white;
        }
        
        .btn-assignments {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            color: white;
        }
        
        .btn-attendance {
            background: linear-gradient(135deg, #06b6d4, #0ea5e9);
            color: white;
        }
        
        .btn-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }
        
        .stats-container {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }
        
        .stat-item {
            text-align: center;
            padding: 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            background-color: #f8fafc;
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-label {
            color: #64748b;
            font-weight: 500;
        }
        
        .student-progress {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }
        
        .progress-item {
            padding: 1rem;
            border-left: 3px solid var(--primary-color);
            margin-bottom: 1rem;
            background-color: #f8fafc;
            border-radius: 0 10px 10px 0;
            transition: all 0.3s ease;
        }
        
        .progress-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .progress-student {
            font-weight: 600;
            color: #1e293b;
        }
        
        .progress-course {
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .upcoming-classes {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }
        
        .class-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8fafc;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .class-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .class-time {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            font-weight: 600;
        }
        
        .class-details {
            flex: 1;
        }
        
        .class-title {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
            color: #1e293b;
        }
        
        .class-location {
            color: #64748b;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        
        footer {
            margin-top: 3rem;
            padding: 1.5rem 0;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeIn" href="#">LMS Teacher</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="manageClasses"><i class="fas fa-chalkboard me-1"></i> My Classes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="manageAssignments"><i class="fas fa-tasks me-1"></i> Assignments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="manageAttendance"><i class="fas fa-calendar-check me-1"></i> Attendance</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">
                        <i class="fas fa-user-tie me-1"></i> Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                    </span>
                    <a href="../logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="dashboard-header animate__animated animate__fadeIn">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2"><i class="fas fa-chalkboard-teacher me-2"></i>Teacher Dashboard</h1>
                    <p class="mb-0">Manage your classes, assignments, and track student progress all in one place.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="current-date">
                        <i class="far fa-calendar-alt me-2"></i>
                        <span id="currentDate"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                <div class="dashboard-card">
                    <div class="card-body text-center">
                        <div class="card-icon classes-icon mx-auto">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                        <h5 class="card-title">Classes</h5>
                        <p class="card-text">Manage your classes and students</p>
                        <a href="#" class="btn btn-card btn-classes" id="classesBtn">View Classes</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <div class="dashboard-card">
                    <div class="card-body text-center">
                        <div class="card-icon assignments-icon mx-auto">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h5 class="card-title">Assignments</h5>
                        <p class="card-text">Create and grade assignments</p>
                        <a href="#" class="btn btn-card btn-assignments" id="assignmentsBtn">Manage Assignments</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                <div class="dashboard-card">
                    <div class="card-body text-center">
                        <div class="card-icon attendance-icon mx-auto">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5 class="card-title">Attendance</h5>
                        <p class="card-text">Mark and view attendance</p>
                        <a href="#" class="btn btn-card btn-attendance" id="attendanceBtn">Manage Attendance</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-container animate__animated animate__fadeIn" style="animation-delay: 0.4s">
            <h3 class="mb-4"><i class="fas fa-chart-pie me-2"></i>Class Statistics</h3>
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number" id="classCount">5</div>
                        <div class="stat-label">Active Classes</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number" id="studentCount">87</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number" id="assignmentCount">12</div>
                        <div class="stat-label">Pending Assignments</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <div class="stat-number" id="attendanceRate">94%</div>
                        <div class="stat-label">Average Attendance</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="student-progress animate__animated animate__fadeIn" style="animation-delay: 0.5s">
                    <h3 class="mb-4"><i class="fas fa-user-graduate me-2"></i>Student Progress</h3>
                    <div class="progress-item">
                        <div class="progress-student">Ahmed Khan</div>
                        <div class="progress-course">Data Structures (CS301) - Needs assistance with final project</div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-student">Sara Ahmed</div>
                        <div class="progress-course">Algorithms (CS302) - Excellent performance on midterm</div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-student">Bilal Malik</div>
                        <div class="progress-course">Database Systems (CS401) - Missing recent assignments</div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-student">Ayesha Khan</div>
                        <div class="progress-course">Web Development (CS305) - Showing great improvement</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="upcoming-classes animate__animated animate__fadeIn" style="animation-delay: 0.6s">
                    <h3 class="mb-4"><i class="fas fa-clock me-2"></i>Upcoming Classes</h3>
                    <div class="class-item">
                        <div class="class-time">
                            <div>10:30</div>
                            <div>AM</div>
                        </div>
                        <div class="class-details">
                            <div class="class-title">Data Structures (CS301)</div>
                            <div class="class-location"><i class="fas fa-map-marker-alt me-2"></i>Room 201, Computer Science Building</div>
                        </div>
                    </div>
                    <div class="class-item">
                        <div class="class-time">
                            <div>01:15</div>
                            <div>PM</div>
                        </div>
                        <div class="class-details">
                            <div class="class-title">Algorithms (CS302)</div>
                            <div class="class-location"><i class="fas fa-map-marker-alt me-2"></i>Room 105, Main Building</div>
                        </div>
                    </div>
                    <div class="class-item">
                        <div class="class-time">
                            <div>03:00</div>
                            <div>PM</div>
                        </div>
                        <div class="class-details">
                            <div class="class-title">Database Systems (CS401)</div>
                            <div class="class-location"><i class="fas fa-map-marker-alt me-2"></i>Lab 3, Computer Science Building</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© 2023 Learning Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Display current date
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        // Add event listeners to buttons
        document.getElementById('classesBtn').addEventListener('click', function(e) {
            e.preventDefault();
            // Add your class view functionality here
            alert('Viewing classes...');
        });
        
        document.getElementById('assignmentsBtn').addEventListener('click', function(e) {
            e.preventDefault();
            // Add your assignments management functionality here
            alert('Managing assignments...');
        });
        
        document.getElementById('attendanceBtn').addEventListener('click', function(e) {
            e.preventDefault();
            // Add your attendance management functionality here
            alert('Managing attendance...');
        });
        
        // Add the same functionality to nav links
        document.getElementById('manageClasses').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Viewing classes...');
        });
        
        document.getElementById('manageAssignments').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Managing assignments...');
        });
        
        document.getElementById('manageAttendance').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Managing attendance...');
        });
    </script>
</body>
</html>