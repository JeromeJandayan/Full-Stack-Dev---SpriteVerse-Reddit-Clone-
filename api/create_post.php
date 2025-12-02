<?php
// api/create_post.php - Handle post creation
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Please login to create a post']);
  exit();
}

// Get form data
$communityId = intval($_POST['community_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$userId = getCurrentUserId();

// Validate input
if ($communityId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Please select a community']);
  exit();
}

if (empty($title)) {
  echo json_encode(['success' => false, 'message' => 'Please enter a post title']);
  exit();
}

if (strlen($title) > 255) {
  echo json_encode(['success' => false, 'message' => 'Title is too long (max 255 characters)']);
  exit();
}

try {
  // Check if user is a member of the community
  $memberQuery = "SELECT id FROM community_members WHERE community_id = ? AND user_id = ?";
  $memberStmt = $conn->prepare($memberQuery);
  $memberStmt->bind_param("ii", $communityId, $userId);
  $memberStmt->execute();
  $memberResult = $memberStmt->get_result();
  
  if ($memberResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You must join this community to post']);
    exit();
  }
  $memberStmt->close();
  
  // Handle image upload
  $imageUrl = null;
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    $fileType = $_FILES['image']['type'];
    $fileSize = $_FILES['image']['size'];
    
    if (!in_array($fileType, $allowedTypes)) {
      echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']);
      exit();
    }
    
    if ($fileSize > $maxSize) {
      echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5MB']);
      exit();
    }
    
    // Generate unique filename
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = 'post_' . uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = '../uploads/' . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
      $imageUrl = 'uploads/' . $filename;
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
      exit();
    }
  }
  
  // Insert post
  $insertQuery = "INSERT INTO posts (community_id, user_id, title, content, image_url) VALUES (?, ?, ?, ?, ?)";
  $insertStmt = $conn->prepare($insertQuery);
  $insertStmt->bind_param("iisss", $communityId, $userId, $title, $content, $imageUrl);
  
  if ($insertStmt->execute()) {
    $postId = $insertStmt->insert_id;
    
    echo json_encode([
      'success' => true,
      'message' => 'Post created successfully',
      'post_id' => $postId
    ]);
  } else {
    // Clean up uploaded image if post creation fails
    if ($imageUrl && file_exists('../' . $imageUrl)) {
      unlink('../' . $imageUrl);
    }
    
    echo json_encode(['success' => false, 'message' => 'Failed to create post']);
  }
  
  $insertStmt->close();
  
} catch (Exception $e) {
  // Clean up uploaded image on error
  if (isset($imageUrl) && $imageUrl && file_exists('../' . $imageUrl)) {
    unlink('../' . $imageUrl);
  }
  
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>