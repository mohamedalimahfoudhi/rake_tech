<?php
// Test password validation script
// Usage: php scripts/test_password.php

// Database connection parameters
$dbHost = 'localhost';
$dbName = 'chleghem';
$dbUser = 'root';
$dbPass = '';

// User credentials to test
$testEmail = 'aziz@aziz.com';
$testPassword = '123456789'; // The password you're trying to use

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n\n";
    
    // Get the user by email
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => $testEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "ERROR: User with email '$testEmail' not found\n";
        exit;
    }
    
    echo "===== USER INFORMATION =====\n";
    foreach ($user as $key => $value) {
        echo "$key: '$value'\n";
    }
    
    // Check if passwords match (plaintext comparison)
    if ($user['motdepasse'] === $testPassword) {
        echo "\n===== PASSWORD VALIDATION =====\n";
        echo "RESULT: MATCH ✓\n";
        echo "The provided password matches the one in the database.\n";
        echo "Authentication should succeed.\n";
    } else {
        echo "\n===== PASSWORD VALIDATION =====\n";
        echo "RESULT: MISMATCH ✗\n";
        echo "The provided password does not match the one in the database.\n";
        echo "Database password: '" . $user['motdepasse'] . "'\n";
        echo "Provided password: '" . $testPassword . "'\n";
        echo "Length database: " . strlen($user['motdepasse']) . " characters\n";
        echo "Length provided: " . strlen($testPassword) . " characters\n";
        
        echo "\nCharacter analysis of database password:\n";
        for ($i = 0; $i < strlen($user['motdepasse']); $i++) {
            $char = $user['motdepasse'][$i];
            $ord = ord($char);
            echo "Position $i: '$char' (ASCII: $ord)\n";
        }
    }
    
    // Get the role information
    if ($user['roleID']) {
        $stmt = $pdo->prepare("SELECT * FROM role WHERE roleID = :roleID");
        $stmt->execute(['roleID' => $user['roleID']]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            echo "\n===== ROLE INFORMATION =====\n";
            foreach ($role as $key => $value) {
                echo "$key: '$value'\n";
            }
        } else {
            echo "\nWARNING: Role with ID " . $user['roleID'] . " not found\n";
        }
    } else {
        echo "\nWARNING: User has no role assigned\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} 