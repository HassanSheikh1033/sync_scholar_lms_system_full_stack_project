<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sync Scholars</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">My Courses</a></li>
                        <li class="nav-item"><a class="nav-link" href="#">Profile</a></li>
                    </ul>
                    <div class="d-flex">
                        <span class="navbar-text me-3">
                            Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </span>
                        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Profile Overview</h5>
                        <p class="card-text">Username: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <a href="#" class="btn btn-dark">Edit Profile</a>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">View Course Schedule</a>
                            <a href="#" class="list-group-item list-group-item-action">Submit Assignments</a>
                            <a href="#" class="list-group-item list-group-item-action">Check Grades</a>
                            <a href="#" class="list-group-item list-group-item-action">Access Learning Resources</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
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
                    <p class="mb-1"><a href="#" class="text-white text-decoration-none">Dashboard</a></p>
                    <p class="mb-1"><a href="#" class="text-white text-decoration-none">My Courses</a></p>
                    <p class="mb-0"><a href="#" class="text-white text-decoration-none">Profile</a></p>
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
</body>
</html> 