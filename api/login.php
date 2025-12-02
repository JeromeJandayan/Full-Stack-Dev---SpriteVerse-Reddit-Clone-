<?php
// api/login.php - Handle user login
require_once '../config.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

// Validate input
if (empty($username) || empty($password)) {
  echo json_encode(['success' => false, 'message' => 'Please provide username and password']);
  exit();
}

try {
  // Check if user exists by username or email
  $query = "SELECT id, username, email, password_hash, avatar_url, bio FROM users WHERE username = ? OR email = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("ss", $username, $username);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    exit();
  }
  
  $user = $result->fetch_assoc();
  $stmt->close();
  
  // Verify password
  if (!password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    exit();
  }
  
  // Set session variables
  $_SESSION['user_id'] = $user['id'];
  $_SESSION['username'] = $user['username'];
  $_SESSION['email'] = $user['email'];
  $_SESSION['avatar_url'] = $user['avatar_url'];
  $_SESSION['bio'] = $user['bio'];
  
  echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
      'id' => $user['id'],
      'username' => $user['username'],
      'email' => $user['email']
    ]
]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>