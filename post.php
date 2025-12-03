<?php
// post.php - Individual post detail page
require_once 'config.php';

// Get post ID from URL
$postId = intval($_GET['id'] ?? 0);

if ($postId <= 0) {
  header('Location: index.php');
  exit();
}

try {
  // Fetch post details with user and community info
  $stmt = $pdo->prepare("
    SELECT 
      p.id,
      p.title,
      p.content,
      p.image_url,
      p.created_at,
      p.user_id,
      u.username,
      u.avatar_url,
      c.name as community_name,
      c.id as community_id,
      COUNT(DISTINCT pv.id) as vote_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    JOIN communities c ON p.community_id = c.id
    LEFT JOIN post_votes pv ON p.id = pv.post_id AND pv.vote_type = 'upvote'
    WHERE p.id = ?
    GROUP BY p.id
  ");
  
  $stmt->execute([$postId]);
  $post = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$post) {
    header('Location: index.php');
    exit();
  }

  // Check if current user is the post owner
  $isOwner = isLoggedIn() && getCurrentUserId() == $post['user_id'];

// Fetch comments
  $stmt = $pdo->prepare("
    SELECT 
      c.id,
      c.content,
      c.created_at,
      c.user_id,
      u.username,
      u.avatar_url
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
  ");
  
  $stmt->execute([$postId]);
  $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($post['title']); ?> - SpriteVerse</title>
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/post.css">
  <link rel="stylesheet" href="css/feed.css">
  <link rel="stylesheet" href="css/modal.css">
</head>
<body <?php echo isLoggedIn() ? 'data-logged-in="true"' : 'data-logged-in="false"'; ?>>
  <?php include 'navbar.php'; ?>
  
  <main class="post-container">
    <div class="post-wrapper" data-post-id="<?php echo $post['id']; ?>">
      <!-- Post Card -->
      <article class="post-detail-card">
        <!-- Post Header -->
        <div class="post-detail-header">
          <div class="post-meta-info">
            <a href="community.php?id=<?php echo $post['community_id']; ?>" class="community-badge">
              c/<?php echo htmlspecialchars($post['community_name']); ?>
            </a>
            <div class="post-author-info">
              <a href="profile.php?username=<?php echo urlencode($post['username']); ?>" class="author-link">
                <div class="author-avatar-small">
                  <?php if ($post['avatar_url']): ?>
                    <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" alt="<?php echo htmlspecialchars($post['username']); ?>">
                  <?php else: ?>
                    <span class="avatar-placeholder-tiny">
                      <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                    </span>
                  <?php endif; ?>
                </div>
                <span class="author-name">u/<?php echo htmlspecialchars($post['username']); ?></span>
              </a>
              <span class="post-dot">•</span>
              <span class="post-timestamp"><?php echo timeAgo($post['created_at']); ?></span>
            </div>
          </div>
          
          <?php if ($isOwner): ?>
            <div class="post-owner-actions">
              <button class="btn-edit-post" onclick="openEditPostModal()">
                <span class="icon">✏️</span>
                <span>Edit</span>
              </button>
              <button class="btn-delete-post" onclick="confirmDeletePost(<?php echo $post['id']; ?>)">
                <span class="icon">🗑️</span>
                <span>Delete</span>
              </button>
            </div>
          <?php endif; ?>
        </div>

        <!-- Post Content -->
        <div class="post-detail-content">
          <h1 class="post-detail-title"><?php echo htmlspecialchars($post['title']); ?></h1>
          
          <?php if ($post['content']): ?>
            <div class="post-detail-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>
          <?php endif; ?>
          
          <?php if ($post['image_url']): ?>
            <div class="post-detail-image">
              <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post image">
            </div>
          <?php endif; ?>
        </div>

        <!-- Post Actions -->
        <div class="post-detail-actions">
          <button class="action-btn upvote-btn" data-post-id="<?php echo $post['id']; ?>">
            <span class="icon">⬆️</span>
            <span class="count"><?php echo number_format($post['vote_count']); ?></span>
            <span class="label">Upvote</span>
          </button>
          <div class="action-btn comment-indicator">
            <span class="icon">💬</span>
            <span class="count"><?php echo count($comments); ?></span>
            <span class="label">Comments</span>
          </div>
          <button class="action-btn share-btn" onclick="sharePost()">
            <span class="icon">🔗</span>
            <span class="label">Share</span>
          </button>
        </div>
      </article>

      <!-- Comments Section -->
      <section class="comments-section">
        <h2 class="comments-title">💬 Comments (<?php echo count($comments); ?>)</h2>

        <!-- Add Comment Form (for logged-in users) -->
        <?php if (isLoggedIn()): ?>
          <div class="add-comment-form">
            <div class="comment-avatar">
              <?php if (!empty($_SESSION['avatar_url'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" alt="Your avatar">
              <?php else: ?>
                <span class="avatar-placeholder-small">
                  <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </span>
              <?php endif; ?>
            </div>
            <div class="comment-input-wrapper">
              <textarea 
                id="commentText" 
                class="comment-textarea" 
                placeholder="What are your thoughts?"
                rows="3"
              ></textarea>
              <div class="comment-actions">
                <button class="btn-cancel" onclick="clearComment()">Cancel</button>
                <button class="btn-submit-comment" onclick="submitComment(<?php echo $post['id']; ?>)">
                  <span class="btn-text">Comment</span>
                  <span class="btn-loading" style="display: none;">
                    <span class="spinner"></span>
                  </span>
                </button>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="login-prompt">
            <p>Please <a href="auth.php">login</a> to leave a comment.</p>
          </div>
        <?php endif; ?>

        <!-- Comments List -->
        <div class="comments-list" id="commentsList">
          <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
              <div class="comment-card" data-comment-id="<?php echo $comment['id']; ?>">
                <div class="comment-avatar">
                  <?php if ($comment['avatar_url']): ?>
                    <img src="<?php echo htmlspecialchars($comment['avatar_url']); ?>" alt="<?php echo htmlspecialchars($comment['username']); ?>">
                  <?php else: ?>
                    <span class="avatar-placeholder-small">
                      <?php echo strtoupper(substr($comment['username'], 0, 1)); ?>
                    </span>
                  <?php endif; ?>
                </div>
                <div class="comment-content">
                  <div class="comment-header">
                    <a href="profile.php?username=<?php echo urlencode($comment['username']); ?>" class="comment-author">
                      u/<?php echo htmlspecialchars($comment['username']); ?>
                    </a>
                    <span class="comment-dot">•</span>
                    <span class="comment-time"><?php echo timeAgo($comment['created_at']); ?></span>
                    
                    <?php if (isLoggedIn() && getCurrentUserId() == $comment['user_id']): ?>
                      <button class="btn-delete-comment" onclick="deleteComment(<?php echo $comment['id']; ?>)">
                        Delete
                      </button>
                    <?php endif; ?>
                  </div>
                  <div class="comment-text">
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="no-comments">
              <div class="no-comments-icon">💭</div>
              <p>No comments yet. Be the first to comment!</p>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </main>

  <!-- Edit Post Modal -->
  <?php if ($isOwner): ?>
    <div class="modal-overlay" id="editPostModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2 class="modal-title">✏️ Edit Post</h2>
          <button class="modal-close" onclick="closeEditPostModal()">✕</button>
        </div>
        
        <form id="editPostForm" onsubmit="handleEditPost(event)" enctype="multipart/form-data">
          <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
          
          <div class="modal-body">
            <!-- Post Title -->
            <div class="form-group">
              <label for="edit_post_title" class="form-label">
                <span class="label-icon">📌</span>
                Post Title
              </label>
              <input 
                type="text" 
                id="edit_post_title" 
                name="title" 
                class="form-input" 
                value="<?php echo htmlspecialchars($post['title']); ?>"
                maxlength="255"
                required
              >
            </div>

            <!-- Post Content -->
            <div class="form-group">
              <label for="edit_post_content" class="form-label">
                <span class="label-icon">✍️</span>
                Content
              </label>
              <textarea 
                id="edit_post_content" 
                name="content" 
                class="form-textarea" 
                rows="6"
              ><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>

            <!-- Current Image -->
            <?php if ($post['image_url']): ?>
            <div class="form-group">
              <label class="form-label">Current Image</label>
              <div class="current-image-preview">
                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Current image">
              </div>
            </div>
            <?php endif; ?>

            <!-- New Image Upload -->
            <div class="form-group">
              <label for="edit_post_image" class="form-label">
                <span class="label-icon">🖼️</span>
                Upload New Image (Optional)
              </label>
              <div class="file-input-wrapper">
                <input 
                  type="file" 
                  id="edit_post_image" 
                  name="image" 
                  class="file-input" 
                  accept="image/*"
                  onchange="previewEditImage(event)"
                >
                <label for="edit_post_image" class="file-input-label">
                  <span class="file-icon">📁</span>
                  <span class="file-text-edit">Choose new image</span>
                </label>
              </div>
              <div id="editImagePreview" class="image-preview" style="display: none;">
                <img id="previewEditImg" src="" alt="Preview">
                <button type="button" class="remove-image" onclick="removeEditImage()">✕ Remove</button>
              </div>
              <small class="input-hint">Leave empty to keep current image</small>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeEditPostModal()">
              Cancel
            </button>
            <button type="submit" class="btn-primary" id="editPostSubmitBtn">
              <span class="btn-text">Save Changes</span>
              <span class="btn-loading" style="display: none;">
                <span class="spinner"></span> Saving...
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

    <script src="js/navbar.js"></script>
    <script src="js/post.js"></script>
  </body>
  </html>
  
  <script src="js/navbar.js"></script>
  <script src="js/post.js"></script>
  <script src="js/feed.js"></script>
  <script src="js/modal.js"></script>
</body>
</html>