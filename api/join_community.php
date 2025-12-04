<?php
// api/join_community.php - Handle joining a community
require_once '../config.php';

header('Content-Type: application/json');

// Optional: suppress HTML-based warnings
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to join communities']);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$communityId = intval($input['community_id'] ?? 0);
$userId = getCurrentUserId();

// Validate
if ($communityId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid community ID']);
    exit;
}

try {
    // Check if community exists
    $stmt = $pdo->prepare("SELECT id FROM communities WHERE id = :id");
    $stmt->execute([':id' => $communityId]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Community not found']);
        exit;
    }

    // Check if already a member
    $stmt = $pdo->prepare("
        SELECT id FROM community_members 
        WHERE community_id = :community_id AND user_id = :user_id
    ");
    $stmt->execute([
        ':community_id' => $communityId,
        ':user_id' => $userId
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already a member of this community']);
        exit;
    }

    // Insert membership
    $stmt = $pdo->prepare("
        INSERT INTO community_members (community_id, user_id, role)
        VALUES (:community_id, :user_id, 'Member')
    ");

    if ($stmt->execute([
        ':community_id' => $communityId,
        ':user_id' => $userId
    ])) {
        echo json_encode([
            'success' => true,
            'message' => 'Successfully joined the community'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to join community']);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
