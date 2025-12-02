<?php
// api/vote.php - Handle post voting
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Please login to vote']);
  exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$postId = intval($input['post_id'] ?? 0);
$voteType = $input['vote_type'] ?? 'upvote';
$userId = getCurrentUserId();

// Validate input
if ($postId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
  exit();
}

if (!in_array($voteType, ['upvote', 'downvote'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid vote type']);
  exit();
}

try {
  // Check if user already voted on this post
  $checkQuery = "SELECT id, vote_type FROM post_votes WHERE post_id = ? AND user_id = ?";
  $checkStmt = $conn->prepare($checkQuery);
  $checkStmt->bind_param("ii", $postId, $userId);
  $checkStmt->execute();
  $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
      // User already voted
      $existingVote = $result->fetch_assoc();
        
      if ($existingVote['vote_type'] === $voteType) {
        // Remove vote (toggle off)
        $deleteQuery = "DELETE FROM post_votes WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bind_param("i", $existingVote['id']);
        $deleteStmt->execute();
        $deleteStmt->close();
      } else {
        // Change vote type
        $updateQuery = "UPDATE post_votes SET vote_type = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("si", $voteType, $existingVote['id']);
        $updateStmt->execute();
        $updateStmt->close();
      }
    } else {
      // Add new vote
      $insertQuery = "INSERT INTO post_votes (post_id, user_id, vote_type) VALUES (?, ?, ?)";
      $insertStmt = $conn->prepare($insertQuery);
      $insertStmt->bind_param("iis", $postId, $userId, $voteType);
      $insertStmt->execute();
      $insertStmt->close();
    }
    
    $checkStmt->close();
    
    // Get updated vote count
    $countQuery = "SELECT COUNT(*) as vote_count FROM post_votes WHERE post_id = ? AND vote_type = 'upvote'";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param("i", $postId);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $voteData = $countResult->fetch_assoc();
    $countStmt->close();
    
    echo json_encode([
      'success' => true,
      'vote_count' => intval($voteData['vote_count'])
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>