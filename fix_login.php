<?php
// Fix login script to run directly
// Usage: php fix_login.php

$dbHost = 'localhost';
$dbName = 'chleghem';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update a specific user's role to admin (role 1)
    $userEmail = 'aziz@aziz.com'; // Change this to the user you want to update
    
    $stmt = $pdo->prepare("UPDATE utilisateur SET roleID = 1 WHERE email = :email");
    $stmt->execute([':email' => $userEmail]);
    
    $rowCount = $stmt->rowCount();
    if ($rowCount > 0) {
        echo "User role updated successfully.\n";
    } else {
        echo "No user found with that email or already has that role.\n";
    }
    
    // Verify roles exist
    $stmt = $pdo->query("SELECT * FROM role");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nAvailable roles:\n";
    foreach ($roles as $role) {
        echo "- Role ID: " . $role['roleID'] . ", Name: " . $role['roleNom'] . "\n";
    }
    
    // Show details about the specific user
    $stmt = $pdo->prepare("SELECT u.ID, u.email, u.motdepasse, r.roleID, r.roleNom 
                        FROM utilisateur u
                        LEFT JOIN role r ON u.roleID = r.roleID
                        WHERE u.email = :email");
    $stmt->execute([':email' => $userEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "\nUser details for $userEmail:\n";
        echo "- User ID: " . $user['ID'] . 
             ", Email: " . $user['email'] . 
             ", Password: " . $user['motdepasse'] . 
             ", Role ID: " . $user['roleID'] . 
             ", Role Name: " . $user['roleNom'] . "\n";
    } else {
        echo "\nUser not found with email: $userEmail\n";
    }
    
    // Show all users and their roles
    $stmt = $pdo->query("SELECT u.ID, u.email, u.motdepasse, r.roleID, r.roleNom 
                        FROM utilisateur u
                        LEFT JOIN role r ON u.roleID = r.roleID
                        ORDER BY u.ID LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nAll users and their roles:\n";
    foreach ($users as $user) {
        echo "- User ID: " . $user['ID'] . 
             ", Email: " . $user['email'] . 
             ", Password: " . substr($user['motdepasse'], 0, 3) . "..." . 
             ", Role ID: " . $user['roleID'] . 
             ", Role Name: " . $user['roleNom'] . "\n";
    }
    
    echo "\nUpdate complete.\n";
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} 