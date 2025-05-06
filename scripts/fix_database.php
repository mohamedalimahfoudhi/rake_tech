<?php
// Fix database schema script
// Usage: php scripts/fix_database.php

// Database connection parameters
$dbHost = 'localhost';
$dbName = 'chleghem';
$dbUser = 'root';
$dbPass = '';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n";
    
    // 1. Check if tables exist
    $tables = ['utilisateur', 'role'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            echo "ERROR: Table '$table' does not exist\n";
            continue;
        }
        echo "Table '$table' exists\n";
        
        // Get table columns
        $stmt = $pdo->query("DESCRIBE $table");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Columns in $table: " . implode(', ', $columns) . "\n";
    }
    
    // 2. Check for specific column names that might need fixes
    $columnChecks = [
        'utilisateur' => [
            'numeroTelephone' => 'VARCHAR(50)', // Should be numeroTelephone not numero_telephone
            'photoProfil' => 'VARCHAR(255)',    // Should be photoProfil not photo_profil
            'nomOrganisation' => 'VARCHAR(255)' // Should be nomOrganisation not nom_organisation
        ]
    ];
    
    foreach ($columnChecks as $table => $columns) {
        echo "\nChecking columns in $table table:\n";
        foreach ($columns as $columnName => $columnType) {
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$columnName'");
            if ($stmt->rowCount() > 0) {
                echo "✓ Column '$columnName' exists in $table\n";
            } else {
                // Check if there's a snake_case version
                $snakeCase = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $columnName));
                $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$snakeCase'");
                if ($stmt->rowCount() > 0) {
                    echo "! Column exists but as '$snakeCase' instead of '$columnName'\n";
                    echo "  Do you want to rename it? (y/n): ";
                    $answer = trim(fgets(STDIN));
                    if (strtolower($answer) === 'y') {
                        $stmt = $pdo->prepare("ALTER TABLE `$table` CHANGE `$snakeCase` `$columnName` $columnType");
                        $stmt->execute();
                        echo "  Column renamed from '$snakeCase' to '$columnName'\n";
                    }
                } else {
                    echo "✗ Column '$columnName' does not exist in $table\n";
                }
            }
        }
    }
    
    echo "\nDatabase verification completed.\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} 