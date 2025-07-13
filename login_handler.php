<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/db_config.php';

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception('Only POST method is allowed');
    }

    $username = isset($_POST['username']) ? $conn->real_escape_string($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate input
    if (empty($username) || empty($password)) {
        throw new Exception('Username and password are required');
    }

    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT u.id, u.username, u.password, u.first_name, u.last_name, u.is_active, 
            r.name as role_name, r.id as role_id 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.username = ?";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check if user is active
        // if (!$user['is_active']) {
        //     throw new Exception('Your account has been deactivated. Please contact administrator.');
        // }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['role_id'] = $user['role_id'];
            
            // Determine redirect based on role
            $redirect = 'dashboard.php';
            switch($user['role_name']) {
                case 'admin':
                    $redirect = 'admin/dashboard.php';
                    break;
                case 'teacher':
                    $redirect = 'teacher/dashboard.php';
                    break;
                case 'student':
                    $redirect = 'student/dashboard.php';
                    break;
            }
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'redirect' => $redirect,
                'role' => $user['role_name']
            ]);
        } else {
            throw new Exception('Invalid username or password');
        }
    } else {
        throw new Exception('Invalid username or password');
    }
    $stmt->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'errors' => [$e->getMessage()]
    ]);
}

$conn->close();
?> 