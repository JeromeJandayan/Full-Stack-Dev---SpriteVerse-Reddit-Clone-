<?php
// api/join_community.php - Handle joining a community
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
  echo json_encode(['success' => false, 'message' => 'Please login to join communities']);
  exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$communityId = intval($input['community_id'] ?? 0);
$userId = getCurrentUserId();

// Validate input
if ($communityId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid community ID']);
  exit();
}

try {
  // Check if community exists
  $checkQuery = "SELECT id FROM communities WHERE id = ?";
  $checkStmt = $conn->prepare($checkQuery);
  $checkStmt->bind_param("i", $communityId);
  $checkStmt->execute();
  $checkResult = $checkStmt->get_result();
  
  if ($checkResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Community not found']);
    exit();
  }
  $checkStmt->close();
  
  // Check if user is already a member
  $memberQuery = "SELECT id FROM community_members WHERE community_id = ? AND user_id = ?";
  $memberStmt = $conn->prepare($memberQuery);
  $memberStmt->bind_param("ii", $communityId, $userId);
  $memberStmt->execute();
  $memberResult = $memberStmt->get_result();
  
  if ($memberResult->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You are already a member of this community']);
    exit();
  }
  $memberStmt->close();
  
  // Add user as a member (default role: Member)
  $insertQuery = "INSERT INTO community_members (community_id, user_id, role) VALUES (?, ?, 'Member')";
  $insertStmt = $conn->prepare($insertQuery);
  $insertStmt->bind_param("ii", $communityId, $userId);
  
  if ($insertStmt->execute()) {
    echo json_encode([
      'success' => true,
      'message' => 'Successfully joined the community'
    ]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Failed to join community']);
  }
  
  $insertStmt->close();
  
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>