<?php
// config.php - Database configuration and session management
session_start();

// Database credentials (XAMPP defaults)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spriteverse_db');

// Connect to database
try {
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  // Set charset to UTF-8
  $conn->set_charset("utf8mb4");
  
} catch (Exception $e) {
  die("Database connection error: " . $e->getMessage());
}

// Helper function to check if user is logged in
function isLoggedIn() {
  return isset($_SESSION['user_id']);
}

// Helper function to get current user ID
function getCurrentUserId() {
  return $_SESSION['user_id'] ?? null;
}

// Helper function to get current username
function getCurrentUsername() {
  return $_SESSION['username'] ?? null;
}

// Helper function to sanitize input
function sanitizeInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Create uploads directory if it doesn't exist
$uploadsDir = __DIR__ . '/uploads';
if (!file_exists($uploadsDir)) {
  mkdir($uploadsDir, 0777, true);
}
?>