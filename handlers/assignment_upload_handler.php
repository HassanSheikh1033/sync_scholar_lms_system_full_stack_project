<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Allow database access
define('ALLOW_ACCESS', true);

try {
    require_once '../config/db_config.php';

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get and validate input data
    $title = isset($_POST['title']) ? $conn->real_escape_string($_POST['title']) : '';
    $description = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    $courseId = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $dueDate = isset($_POST['due_date']) ? $conn->real_escape_string($_POST['due_date']) : '';
    $totalMarks = isset($_POST['total_marks']) ? floatval($_POST['total_marks']) : 0;
    $teacherId = $_SESSION['user_id'];

    // Validate required fields
    if (empty($title) || empty($description) || empty($courseId) || empty($dueDate) || empty($totalMarks)) {
        throw new Exception('All fields are required');
    }

    // Validate course exists and belongs to teacher
    $courseCheck = $conn->prepare("SELECT id FROM courses WHERE id = ? AND teacher_id = ?");
    if (!$courseCheck) {
        throw new Exception('Database error: ' . $conn->error);
    }
    
    $courseCheck->bind_param("ii", $courseId, $teacherId);
    if (!$courseCheck->execute()) {
        throw new Exception('Error checking course: ' . $courseCheck->error);
    }
    
    if ($courseCheck->get_result()->num_rows === 0) {
        throw new Exception('Invalid course selected');
    }
    $courseCheck->close();

    // Handle file upload if present
    $filePath = null;
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/assignments/';
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception('Failed to create upload directory');
            }
        }

        // Generate unique filename
        $fileExtension = strtolower(pathinfo($_FILES['assignment_file']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx', 'txt'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception('Invalid file type. Allowed types: PDF, DOC, DOCX, TXT');
        }

        $fileName = uniqid('assignment_') . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['assignment_file']['tmp_name'], $targetPath)) {
            throw new Exception('Failed to upload file');
        }

        $filePath = 'uploads/assignments/' . $fileName;
    }

    // Start transaction
    if (!$conn->begin_transaction()) {
        throw new Exception('Failed to start transaction');
    }

    try {
        // Insert assignment
        $stmt = $conn->prepare("INSERT INTO assignments (title, description, course_id, teacher_id, due_date, total_marks, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }

        $stmt->bind_param("ssiisds", $title, $description, $courseId, $teacherId, $dueDate, $totalMarks, $filePath);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create assignment: ' . $stmt->error);
        }

        $assignmentId = $stmt->insert_id;
        $stmt->close();

        // Get all students enrolled in the course
        $stmt = $conn->prepare("
            INSERT INTO student_assignments (assignment_id, student_id, status)
            SELECT ?, student_id, 'pending'
            FROM course_enrollments
            WHERE course_id = ? AND status = 'active'
        ");
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }

        $stmt->bind_param("ii", $assignmentId, $courseId);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create student assignments: ' . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        if (!$conn->commit()) {
            throw new Exception('Failed to commit transaction');
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Assignment created successfully',
            'assignment_id' => $assignmentId
        ]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 




<!-- Like express-session in Node.js, this starts a session. It gives access to $_SESSION, which stores data like req.session. -->
<!-- define sets a constant to allow access (used for security gating includes). -->
<!-- require_once is like a one-time import — loads DB connection settings from another file. -->
<!-- $_POST is like req.body in Express. -->
<!-- real_escape_string() prevents SQL injection. -->
<!-- $courseCheck->bind_param("ii", $courseId, $teacherId); -->
<!-- "ii" means two integers are being bound.
$stmt = $conn->prepare("INSERT INTO assignments (...) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssiisds", $title, $description, $courseId, $teacherId, $dueDate, $totalMarks, $filePath);
Inserts the assignment into assignments table.
ssiisds = string, string, int, int, string, double, string. -->


<!-- Transactions -->
<!-- $conn->begin_transaction();     // Start a transaction

try {
    // Multiple SQL operations...
    $stmt = $conn->prepare(...); // Insert assignment
    $stmt->execute();

    $stmt = $conn->prepare(...); // Insert student_assignments
    $stmt->execute();

    $conn->commit();             // ✅ Commit everything
} catch (Exception $e) {
    $conn->rollback();          // ❌ If any step failed, undo all changes
    throw $e;
} -->
<!-- 
What’s inside $_FILES['assignment_file']:

name – Original filename on the user's computer
type – MIME type (e.g., application/pdf)
tmp_name – Temporary filename on the server
error – Error code (0 means no error)
size – File size in bytes -->