<?php
// api/register.php - Handle user registration
require_once '../config.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

// Validate input
if (empty($username) || empty($email) || empty($password)) {
  echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
  exit();
}

// Validate username
if (strlen($username) < 3 || strlen($username) > 50) {
  echo json_encode(['success' => false, 'message' => 'Username must be between 3 and 50 characters']);
  exit();
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
  echo json_encode(['success' => false, 'message' => 'Username can only contain letters, numbers, and underscores']);
  exit();
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['success' => false, 'message' => 'Invalid email address']);
  exit();
}

// Validate password
if (strlen($password) < 6) {
  echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
  exit();
}

try {
  // Check if username already exists
  $checkQuery = "SELECT id FROM users WHERE username = ?";
  $checkStmt = $conn->prepare($checkQuery);
  $checkStmt->bind_param("s", $username);
  $checkStmt->execute();
  $checkResult = $checkStmt->get_result();
  
  if ($checkResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username already taken']);
    exit();
  }
  $checkStmt->close();
  
  // Check if email already exists
  $checkEmailQuery = "SELECT id FROM users WHERE email = ?";
  $checkEmailStmt = $conn->prepare($checkEmailQuery);
  $checkEmailStmt->bind_param("s", $email);
  $checkEmailStmt->execute();
  $checkEmailResult = $checkEmailStmt->get_result();
  
  if ($checkEmailResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit();
  }
  $checkEmailStmt->close();
  
  // Hash password
  $passwordHash = password_hash($password, PASSWORD_DEFAULT);
  
  // Insert new user
  $insertQuery = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
  $insertStmt = $conn->prepare($insertQuery);
  $insertStmt->bind_param("sss", $username, $email, $passwordHash);
  
  if ($insertStmt->execute()) {
    $userId = $insertStmt->insert_id;
    
    // Set session variables (auto-login)
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['avatar_url'] = null;
    $_SESSION['bio'] = null;
  
    echo json_encode([
      'success' => true,
      'message' => 'Account created successfully',
      'user' => [
        'id' => $userId,
        'username' => $username,
        'email' => $email
      ]
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to create account']);
  }
  
  $insertStmt->close();
  
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>