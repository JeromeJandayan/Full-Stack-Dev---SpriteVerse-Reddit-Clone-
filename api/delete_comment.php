<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$comment_id = $data['comment_id'] ?? null;

if (!$comment_id) {
    echo json_encode(['success' => false, 'message' => 'Comment ID required']);
    exit;
}

try {
    // Check if user owns the comment or has moderation rights
    $stmt = $pdo->prepare("
        SELECT c.user_id, p.community_id, cm.role
        FROM comments c
        JOIN posts p ON c.post_id = p.id
        LEFT JOIN community_members cm ON cm.community_id = p.community_id 
            AND cm.user_id = ?
        WHERE c.id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $comment_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        exit;
    }

    // Check permissions
    $can_delete = ($comment['user_id'] == $_SESSION['user_id']) || 
                  ($comment['role'] === 'Admin') || 
                  ($comment['role'] === 'Moderator');

    if (!$can_delete) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Delete the comment
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);

    echo json_encode(['success' => true, 'message' => 'Comment deleted successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>