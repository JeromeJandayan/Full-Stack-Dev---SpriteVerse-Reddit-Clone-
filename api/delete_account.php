<?php
// api/delete_account.php - Handle account deletion
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Please login to delete account']);
  exit();
}

$userId = getCurrentUserId();

try {
  // Get user's avatar and post images to delete
  $imagesQuery = "SELECT avatar_url FROM users WHERE id = ?
                  UNION
                  SELECT image_url FROM posts WHERE user_id = ?";
  $imagesStmt = $conn->prepare($imagesQuery);
  $imagesStmt->bind_param("ii", $userId, $userId);
  $imagesStmt->execute();
  $imagesResult = $imagesStmt->get_result();
  
  $imagesToDelete = [];
  while ($row = $imagesResult->fetch_assoc()) {
    if ($row['avatar_url']) {
      $imagesToDelete[] = $row['avatar_url'];
    }
  }
  $imagesStmt->close();
  
  // Delete user (cascades to posts, comments, votes, community_members)
  $deleteQuery = "DELETE FROM users WHERE id = ?";
  $deleteStmt = $conn->prepare($deleteQuery);
  $deleteStmt->bind_param("i", $userId);
  
  if ($deleteStmt->execute()) {
    // Delete uploaded images
    foreach ($imagesToDelete as $imagePath) {
      if ($imagePath && file_exists('../' . $imagePath)) {
        unlink('../' . $imagePath);
      }
    }
    
    // Destroy session
    session_destroy();
    
    echo json_encode([
      'success' => true,
      'message' => 'Account deleted successfully'
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
  }
  
  $deleteStmt->close();
  
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>