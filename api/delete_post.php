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
$post_id = $data['post_id'] ?? null;

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Post ID required']);
    exit;
}

try {
    // Check if user owns the post or is admin/moderator of the community
    $stmt = $pdo->prepare("
        SELECT p.user_id, p.community_id, p.image_url,
               cm.role
        FROM posts p
        LEFT JOIN community_members cm ON cm.community_id = p.community_id 
            AND cm.user_id = ?
        WHERE p.id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit;
    }

    // Check permissions
    $can_delete = ($post['user_id'] == $_SESSION['user_id']) || 
                  ($post['role'] === 'Admin') || 
                  ($post['role'] === 'Moderator');

    if (!$can_delete) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Delete the post (cascade will handle comments and votes)
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);

    // Delete image file if exists
    if ($post['image_url'] && file_exists('../' . $post['image_url'])) {
        unlink('../' . $post['image_url']);
    }

    echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>