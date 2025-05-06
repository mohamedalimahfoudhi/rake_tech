<?php
// Reset password script to run directly
// Usage: php reset_password.php

$dbHost = 'localhost';
$dbName = 'chleghem';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Reset password for a specific user
    $userEmail = 'aziz@aziz.com';
    $newPassword = '123456789'; // New plaintext password
    
    // Update the password
    $stmt = $pdo->prepare("UPDATE utilisateur SET motdepasse = :password WHERE email = :email");
    $stmt->execute([
        ':password' => $newPassword,
        ':email' => $userEmail
    ]);
    
    $rowCount = $stmt->rowCount();
    if ($rowCount > 0) {
        echo "Password reset successfully for user $userEmail.\n";
    } else {
        echo "No user found with email $userEmail or password was already set to that value.\n";
    }
    
    // Verify the password was updated
    $stmt = $pdo->prepare("SELECT motdepasse FROM utilisateur WHERE email = :email");
    $stmt->execute([':email' => $userEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "Current password in database: " . $user['motdepasse'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} 