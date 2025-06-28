<?php
// Check if session is already started before calling session_start()
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

function login($email, $password) {
    global $mysqli;
    
    $stmt = $mysqli->prepare("SELECT id, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['role'] = $user['is_admin'] ? 'admin' : 'user'; // Add role for admin.php compatibility
            $stmt->close();
            return true;
        }
    }
    $stmt->close();
    return false;
}

function register( $firstName, $lastName,$email, $password) {
    global $mysqli;
    
    // Check if email already exists
    $checkStmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $checkStmt->close();
        return false; // Email already exists
    }
    $checkStmt->close();
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $mysqli->prepare("INSERT INTO users (email, password, first_name, last_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $email, $hashedPassword, $firstName, $lastName);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: login.php');
        exit();
    }
}

function getCurrentUser() {
    global $mysqli;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $mysqli->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function logout() {
    session_destroy();
}
?>