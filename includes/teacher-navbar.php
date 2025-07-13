<?php
// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.html');
    exit();
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand animate__animated animate__fadeIn" href="../teacher/dashboard.php">LMS Teacher</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../teacher/dashboard.php"><i class="fas fa-home me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../course-management.html"><i class="fas fa-chalkboard me-1"></i> My Classes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../assignment-upload.php"><i class="fas fa-tasks me-1"></i> Assignments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../attendance-view.html"><i class="fas fa-calendar-check me-1"></i> Attendance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../enrollment-management.html"><i class="fas fa-user-graduate me-1"></i> Enrollments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../rollno-upload.php"><i class="fas fa-file-alt me-1"></i> Roll No Slips</a>
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