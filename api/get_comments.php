<?php
require_once '../config.php';

header('Content-Type: application/json');

$post_id = $_GET['post_id'] ?? null;

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Post ID required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.content,
            c.created_at,
            u.id as user_id,
            u.username,
            u.avatar_url
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format timestamps
    foreach ($comments as &$comment) {
        $comment['time_ago'] = timeAgo($comment['created_at']);
    }

    echo json_encode([
        'success' => true,
        'comments' => $comments,
        'current_user_id' => $_SESSION['user_id'] ?? null
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

// Helper function for time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>