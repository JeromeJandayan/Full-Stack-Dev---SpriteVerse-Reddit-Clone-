<?php
// api/create_community.php - Handle community creation
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Please login to create a community']);
  exit();
}

// Get form data
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$userId = getCurrentUserId();

// Validate input
if (empty($name)) {
  echo json_encode(['success' => false, 'message' => 'Please enter a community name']);
  exit();
}

if (strlen($name) > 100) {
  echo json_encode(['success' => false, 'message' => 'Community name is too long (max 100 characters)']);
  exit();
}

if (!preg_match('/^[a-zA-Z0-9 _-]+$/', $name)) {
  echo json_encode(['success' => false, 'message' => 'Community name can only contain letters, numbers, spaces, hyphens, and underscores']);
  exit();
}

if (empty($description)) {
  echo json_encode(['success' => false, 'message' => 'Please enter a description']);
  exit();
}

try {
  // Check if community name already exists
  $checkQuery = "SELECT id FROM communities WHERE name = ?";
  $checkStmt = $conn->prepare($checkQuery);
  $checkStmt->bind_param("s", $name);
  $checkStmt->execute();
  $checkResult = $checkStmt->get_result();
  
  if ($checkResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'A community with this name already exists']);
    exit();
  }
  $checkStmt->close();
  
  // Handle icon upload
  $iconUrl = null;
  if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    $fileType = $_FILES['icon']['type'];
    $fileSize = $_FILES['icon']['size'];
    
    if (!in_array($fileType, $allowedTypes)) {
      echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']);
      exit();
    }
    
    if ($fileSize > $maxSize) {
      echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 2MB']);
      exit();
    }
    
    // Generate unique filename
    $extension = pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION);
    $filename = 'community_' . uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = '../uploads/' . $filename;
    
    if (move_uploaded_file($_FILES['icon']['tmp_name'], $uploadPath)) {
      $iconUrl = 'uploads/' . $filename;
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to upload icon']);
      exit();
    }
  }
  
  // Insert community
  $insertQuery = "INSERT INTO communities (name, description, icon_url, created_by) VALUES (?, ?, ?, ?)";
  $insertStmt = $conn->prepare($insertQuery);
  $insertStmt->bind_param("sssi", $name, $description, $iconUrl, $userId);
  
  if ($insertStmt->execute()) {
    $communityId = $insertStmt->insert_id;
    
    // Add creator as Admin member
    $memberQuery = "INSERT INTO community_members (community_id, user_id, role) VALUES (?, ?, 'Admin')";
    $memberStmt = $conn->prepare($memberQuery);
    $memberStmt->bind_param("ii", $communityId, $userId);
    $memberStmt->execute();
    $memberStmt->close();
    
    echo json_encode([
      'success' => true,
      'message' => 'Community created successfully',
      'community_id' => $communityId
    ]);
  } else {
    // Clean up uploaded icon if community creation fails
    if ($iconUrl && file_exists('../' . $iconUrl)) {
      unlink('../' . $iconUrl);
    }
    
    echo json_encode(['success' => false, 'message' => 'Failed to create community']);
  }
  
  $insertStmt->close();
  
} catch (Exception $e) {
  // Clean up uploaded icon on error
  if (isset($iconUrl) && $iconUrl && file_exists('../' . $iconUrl)) {
    unlink('../' . $iconUrl);
  }
  
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>