<?php
// api/update_profile.php - Handle profile updates
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Please login to update profile']);
  exit();
}

// Get form data
$bio = trim($_POST['bio'] ?? '');
$userId = getCurrentUserId();

try {
  // Handle avatar upload
  $avatarUrl = null;
  if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    $fileType = $_FILES['avatar']['type'];
    $fileSize = $_FILES['avatar']['size'];
    
    if (!in_array($fileType, $allowedTypes)) {
      echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']);
      exit();
    }
    
    if ($fileSize > $maxSize) {
      echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 2MB']);
      exit();
    }
    
    // Get old avatar to delete later
    $oldAvatarQuery = "SELECT avatar_url FROM users WHERE id = ?";
    $oldAvatarStmt = $conn->prepare($oldAvatarQuery);
    $oldAvatarStmt->bind_param("i", $userId);
    $oldAvatarStmt->execute();
    $oldAvatarResult = $oldAvatarStmt->get_result();
    $oldAvatar = $oldAvatarResult->fetch_assoc()['avatar_url'];
    $oldAvatarStmt->close();
    
    // Generate unique filename
    $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
    $uploadPath = '../uploads/' . $filename;
    
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
      $avatarUrl = 'uploads/' . $filename;
      
      // Delete old avatar if exists
      if ($oldAvatar && file_exists('../' . $oldAvatar)) {
        unlink('../' . $oldAvatar);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Failed to upload avatar']);
      exit();
    }
  }
  
  // Update profile
  if ($avatarUrl) {
    // Update both bio and avatar
    $updateQuery = "UPDATE users SET bio = ?, avatar_url = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $bio, $avatarUrl, $userId);
  } else {
    // Update only bio
    $updateQuery = "UPDATE users SET bio = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $bio, $userId);
  }
  
  if ($updateStmt->execute()) {
    // Update session
    $_SESSION['bio'] = $bio;
    if ($avatarUrl) {
      $_SESSION['avatar_url'] = $avatarUrl;
    }
    
    echo json_encode([
      'success' => true,
      'message' => 'Profile updated successfully'
    ]);
  } else {
    // Clean up uploaded avatar if update fails
    if ($avatarUrl && file_exists('../' . $avatarUrl)) {
      unlink('../' . $avatarUrl);
    }
    
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
  }
  
  $updateStmt->close();
  
} catch (Exception $e) {
  // Clean up uploaded avatar on error
  if (isset($avatarUrl) && $avatarUrl && file_exists('../' . $avatarUrl)) {
    unlink('../' . $avatarUrl);
  }
  
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>