<?php
// api/add_comment.php - Handle adding comments
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Please login to comment']);
  exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$postId = intval($input['post_id'] ?? 0);
$content = trim($input['content'] ?? '');
$userId = getCurrentUserId();

// Validate input
if ($postId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
  exit();
}

if (empty($content)) {
  echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
  exit();
}

try {
  // Check if post exists
  $stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
  $stmt->execute([$postId]);
  
  if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit();
  }
  
  // Insert comment
  $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
  
  if ($stmt->execute([$postId, $userId, $content])) {
    $commentId = $pdo->lastInsertId();
    
    echo json_encode([
      'success' => true,
      'message' => 'Comment added successfully',
      'comment_id' => $commentId
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
  }
  
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>