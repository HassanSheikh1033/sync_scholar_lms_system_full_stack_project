<?php
header('Content-Type: application/json');
require_once 'config/db_config.php';

try {
    // Get database information
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    
    while ($row = $result->fetch_array()) {
        $tableName = $row[0];
        
        // Get table structure
        $structure = array();
        $columns = $conn->query("SHOW COLUMNS FROM `$tableName`");
        while ($col = $columns->fetch_assoc()) {
            $structure[] = $col;
        }
        
        // Get row count
        $count = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
        $rowCount = $count->fetch_assoc()['count'];
        
        $tables[$tableName] = array(
            'structure' => $structure,
            'row_count' => $rowCount
        );
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Connected to database successfully',
        'database' => DB_NAME,
        'tables' => $tables
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 