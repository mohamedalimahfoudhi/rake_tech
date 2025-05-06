<?php
// Simple script to check user credentials
// Usage: php scripts/check_user.php

try {
    $dbHost = 'localhost';
    $dbName = 'chleghem';
    $dbUser = 'root';
    $dbPass = '';
    
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n\n";
    
    // User to check
    $email = 'aziz@aziz.com';
    $password = '123456789';
    
    // Get user
    $stmt = $pdo->prepare("SELECT ID, email, motdepasse, roleID FROM utilisateur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "User not found\n";
        exit;
    }
    
    echo "User found:\n";
    echo "ID: " . $user['ID'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Password in DB: " . $user['motdepasse'] . "\n";
    echo "Password length: " . strlen($user['motdepasse']) . "\n";
    
    // Compare passwords
    echo "\nPassword match test:\n";
    echo "Password from form: " . $password . "\n";
    echo "Password length: " . strlen($password) . "\n";
    
    if ($user['motdepasse'] === $password) {
        echo "MATCH: Passwords are identical\n";
    } else {
        echo "MISMATCH: Passwords are different\n";
        
        // Check character by character
        echo "\nCharacter comparison:\n";
        $dbPass = $user['motdepasse'];
        $maxLen = max(strlen($dbPass), strlen($password));
        
        for ($i = 0; $i < $maxLen; $i++) {
            $dbChar = isset($dbPass[$i]) ? $dbPass[$i] : 'N/A';
            $inputChar = isset($password[$i]) ? $password[$i] : 'N/A';
            $dbOrd = isset($dbPass[$i]) ? ord($dbPass[$i]) : 'N/A';
            $inputOrd = isset($password[$i]) ? ord($password[$i]) : 'N/A';
            $match = ($dbChar === $inputChar) ? 'MATCH' : 'DIFF';
            
            printf("Position %2d: DB='%s' (ASCII: %s) | Input='%s' (ASCII: %s) | %s\n",
                   $i, $dbChar, $dbOrd, $inputChar, $inputOrd, $match);
        }
    }
    
    // Check role
    $stmt = $pdo->prepare("SELECT roleID, roleNom FROM role WHERE roleID = ?");
    $stmt->execute([$user['roleID']]);
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($role) {
        echo "\nRole Information:\n";
        echo "Role ID: " . $role['roleID'] . "\n";
        echo "Role Name: " . $role['roleNom'] . "\n";
    } else {
        echo "\nNo role found for this user\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} 