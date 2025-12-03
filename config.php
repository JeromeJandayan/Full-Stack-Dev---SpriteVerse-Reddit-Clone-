<?php
// config.php - Database configuration and session management
session_start();

// Database credentials (XAMPP defaults)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'spriteverse_db');

// Create PDO connection (PRIMARY - for most queries)
try {
  $pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
    DB_USER,
    DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false
    ]
  );
} catch (PDOException $e) {
  die("PDO Connection failed: " . $e->getMessage());
}

// Create MySQLi connection (BACKUP - for backward compatibility)
try {
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  
  if ($conn->connect_error) {
    die("MySQLi Connection failed: " . $conn->connect_error);
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

// Helper function to get current user avatar
function getCurrentUserAvatar() {
  return $_SESSION['avatar_url'] ?? null;
}

// Helper function to sanitize input
function sanitizeInput($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

// Helper function to format time ago
function timeAgo($datetime) {
  $time = strtotime($datetime);
  $now = time();
  $diff = $now - $time;
  
  if ($diff < 60) {
    return 'just now';
  } elseif ($diff < 3600) {
    $mins = floor($diff / 60);
    return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
  } elseif ($diff < 86400) {
    $hours = floor($diff / 3600);
    return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
  } elseif ($diff < 604800) {
    $days = floor($diff / 86400);
    return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
  } else {
    return date('M j, Y', $time);
  }
}

// Helper function to format numbers
function formatNumber($num) {
  if ($num >= 1000000) {
    return round($num / 1000000, 1) . 'M';
  } elseif ($num >= 1000) {
    return round($num / 1000, 1) . 'K';
  }
  return $num;
}

// Create uploads directory if it doesn't exist
$uploadsDir = __DIR__ . '/uploads';
if (!file_exists($uploadsDir)) {
  mkdir($uploadsDir, 0777, true);
}
?>