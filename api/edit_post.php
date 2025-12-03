<?php
// api/edit_post.php - Handle post editing
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$post_id = intval($_POST['post_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$user_id = getCurrentUserId();

// Validate input
if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

if (strlen($title) > 255) {
    echo json_encode(['success' => false, 'message' => 'Title is too long (max 255 characters)']);
    exit;
}

try {
    // Check if user owns the post
    $stmt = $pdo->prepare("SELECT user_id, image_url FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit;
    }

    if ($post['user_id'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    // Handle new image upload (optional)
    $imageUrl = $post['image_url']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        $fileType = $_FILES['image']['type'];
        $fileSize = $_FILES['image']['size'];
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type']);
            exit;
        }
        
        if ($fileSize > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File is too large (max 5MB)']);
            exit;
        }
        
        // Delete old image
        if ($post['image_url'] && file_exists('../' . $post['image_url'])) {
            unlink('../' . $post['image_url']);
        }
        
        // Upload new image
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'post_' . uniqid() . '_' . time() . '.' . $extension;
        $uploadPath = '../uploads/' . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imageUrl = 'uploads/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
            exit;
        }
    }

    // Update the post
    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, image_url = ? WHERE id = ?");
    $stmt->execute([$title, $content, $imageUrl, $post_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Post updated successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>