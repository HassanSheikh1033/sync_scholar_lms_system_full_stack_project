<?php
require_once 'config/db_config.php';

try {
    // Query to check roles
    $result = $conn->query("SELECT * FROM roles");
    
    if ($result) {
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        echo json_encode(['status' => 'success', 'roles' => $roles]);
    } else {
        throw new Exception("Error querying roles: " . $conn->error);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?> 