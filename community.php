<?php
// community.php - Individual community page
require_once 'config.php';

// Get community ID from URL
$communityId = intval($_GET['id'] ?? 0);

if ($communityId <= 0) {
  header('Location: communities.php');
  exit();
}

try {
  // Fetch community details
  $stmt = $pdo->prepare("
    SELECT 
      c.id,
      c.name,
      c.description,
      c.icon_url,
      c.created_at,
      c.created_by,
      u.username as creator_username,
      COUNT(DISTINCT cm.id) as member_count,
      COUNT(DISTINCT p.id) as post_count
    FROM communities c
    JOIN users u ON c.created_by = u.id
    LEFT JOIN community_members cm ON c.id = cm.community_id
    LEFT JOIN posts p ON c.id = p.community_id
    WHERE c.id = ?
    GROUP BY c.id
  ");
  
  $stmt->execute([$communityId]);
  $community = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$community) {
    header('Location: communities.php');
    exit();
  }

  // Check if current user is a member and get their role
  $userRole = null;
  $isMember = false;
  
  if (isLoggedIn()) {
    $stmt = $pdo->prepare("
      SELECT role FROM community_members 
      WHERE community_id = ? AND user_id = ?
    ");
    $stmt->execute([$communityId, getCurrentUserId()]);
    $memberData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($memberData) {
      $isMember = true;
      $userRole = $memberData['role'];
    }
  }

  // Fetch posts in this community
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
      COUNT(DISTINCT pv.id) as vote_count,
      COUNT(DISTINCT c.id) as comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN post_votes pv ON p.id = pv.post_id AND pv.vote_type = 'upvote'
    LEFT JOIN comments c ON p.id = c.post_id
    WHERE p.community_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
  ");
  
  $stmt->execute([$communityId]);
  $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>c/<?php echo htmlspecialchars($community['name']); ?> - SpriteVerse</title>
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/community.css">
  <link rel="stylesheet" href="css/feed.css">
  <link rel="stylesheet" href="css/modal.css">
</head>
<body <?php echo isLoggedIn() ? 'data-logged-in="true"' : 'data-logged-in="false"'; ?>>
  <?php include 'navbar.php'; ?>
  
  <main class="community-container">
    <!-- Community Header -->
    <section class="community-header">
      <div class="community-banner">
        <div class="community-icon-large">
          <?php if ($community['icon_url']): ?>
            <img src="<?php echo htmlspecialchars($community['icon_url']); ?>" alt="<?php echo htmlspecialchars($community['name']); ?>">
          <?php else: ?>
            <span class="icon-placeholder-xlarge">
              <?php echo strtoupper(substr($community['name'], 0, 1)); ?>
            </span>
          <?php endif; ?>
        </div>
      </div>

      <div class="community-info">
        <div class="community-main">
          <h1 class="community-name">c/<?php echo htmlspecialchars($community['name']); ?></h1>
          
          <?php if (isLoggedIn()): ?>
            <?php if ($isMember): ?>
              <button class="btn-joined-large" onclick="leaveCommunity(<?php echo $community['id']; ?>)">
                <span class="btn-icon">✓</span>
                <span>Joined</span>
                <small class="btn-hint">Click to leave</small>
              </button>
            <?php else: ?>
              <button class="btn-join-large" onclick="joinCommunity(<?php echo $community['id']; ?>, this)">
                <span class="btn-icon">+</span>
                <span>Join Community</span>
              </button>
            <?php endif; ?>
          <?php else: ?>
            <button class="btn-join-large" onclick="window.location.href='auth.php'">
              <span class="btn-icon">+</span>
              <span>Join Community</span>
            </button>
          <?php endif; ?>
        </div>

        <p class="community-description"><?php echo nl2br(htmlspecialchars($community['description'])); ?></p>

        <div class="community-stats">
          <div class="stat-item">
            <span class="stat-value"><?php echo formatNumber($community['member_count']); ?></span>
            <span class="stat-label">Members</span>
          </div>
          <div class="stat-item">
            <span class="stat-value"><?php echo formatNumber($community['post_count']); ?></span>
            <span class="stat-label">Posts</span>
          </div>
          <div class="stat-item">
            <span class="stat-value"><?php echo date('M Y', strtotime($community['created_at'])); ?></span>
            <span class="stat-label">Created</span>
          </div>
        </div>

        <div class="community-creator">
          Created by 
          <a href="profile.php?username=<?php echo urlencode($community['creator_username']); ?>" class="creator-link">
            u/<?php echo htmlspecialchars($community['creator_username']); ?>
          </a>
          <?php if ($userRole): ?>
            <span class="user-role-badge role-<?php echo strtolower($userRole); ?>">
              <?php echo $userRole; ?>
            </span>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Community Content -->
    <div class="community-content">
      <!-- Main Feed -->
      <section class="community-feed">
        <!-- Create Post Button (for members) -->
        <?php if ($isMember): ?>
          <div class="create-post-prompt" onclick="openCreatePostModal(<?php echo $community['id']; ?>)">
            <div class="user-avatar-small">
              <?php if (!empty($_SESSION['avatar_url'])): ?>
                <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" alt="Avatar">
              <?php else: ?>
                <span class="avatar-placeholder">
                  <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </span>
              <?php endif; ?>
            </div>
            <div class="create-input-fake">
              Share something with c/<?php echo htmlspecialchars($community['name']); ?>
            </div>
            <button class="create-quick-btn">📝 Post</button>
          </div>
        <?php endif; ?>

        <!-- Posts List -->
        <div class="posts-container">
          <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
              <article class="post-card">
                <!-- Post Header -->
                <div class="post-header">
                  <div class="post-meta">
                    <a href="profile.php?username=<?php echo urlencode($post['username']); ?>" class="post-author">
                      <div class="author-avatar-tiny">
                        <?php if ($post['avatar_url']): ?>
                          <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" alt="<?php echo htmlspecialchars($post['username']); ?>">
                        <?php else: ?>
                          <span class="avatar-placeholder-tiny">
                            <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                          </span>
                        <?php endif; ?>
                      </div>
                      u/<?php echo htmlspecialchars($post['username']); ?>
                    </a>
                    <span class="post-dot">•</span>
                    <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                  </div>
                </div>

                <!-- Post Content -->
                <div class="post-body">
                  <h2 class="post-title">
                    <a href="post.php?id=<?php echo $post['id']; ?>">
                      <?php echo htmlspecialchars($post['title']); ?>
                    </a>
                  </h2>
                  <?php if ($post['content']): ?>
                    <p class="post-content">
                      <?php 
                      $content = htmlspecialchars($post['content']);
                      echo strlen($content) > 200 ? substr($content, 0, 200) . '...' : nl2br($content);
                      ?>
                    </p>
                  <?php endif; ?>
                  <?php if ($post['image_url']): ?>
                    <div class="post-image">
                      <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post image" loading="lazy">
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Post Actions -->
                <div class="post-actions">
                  <button class="action-btn upvote-btn" data-post-id="<?php echo $post['id']; ?>">
                    <span class="icon">⬆️</span>
                    <span class="count"><?php echo formatNumber($post['vote_count']); ?></span>
                  </button>
                  <button class="action-btn comment-btn" onclick="window.location.href='post.php?id=<?php echo $post['id']; ?>'">
                    <span class="icon">💬</span>
                    <span class="count"><?php echo formatNumber($post['comment_count']); ?></span>
                  </button>
                  <button class="action-btn share-btn">
                    <span class="icon">🔗</span>
                    <span>Share</span>
                  </button>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="empty-state-card">
              <div class="empty-icon">📭</div>
              <h3>No posts yet</h3>
              <p>Be the first to post in this community!</p>
              <?php if ($isMember): ?>
                <button class="cta-btn" onclick="openCreatePostModal(<?php echo $community['id']; ?>)">Create Post</button>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Right Sidebar -->
      <aside class="community-sidebar">
        <div class="sidebar-card">
          <h3 class="sidebar-title">📖 About Community</h3>
          <p class="about-text"><?php echo nl2br(htmlspecialchars($community['description'])); ?></p>
          
          <div class="sidebar-stats">
            <div class="stat-row">
              <span class="stat-label">👥 Members:</span>
              <span class="stat-value"><?php echo formatNumber($community['member_count']); ?></span>
            </div>
            <div class="stat-row">
              <span class="stat-label">📝 Posts:</span>
              <span class="stat-value"><?php echo formatNumber($community['post_count']); ?></span>
            </div>
            <div class="stat-row">
              <span class="stat-label">📅 Created:</span>
              <span class="stat-value"><?php echo date('M j, Y', strtotime($community['created_at'])); ?></span>
            </div>
          </div>

          <?php if (!$isMember && isLoggedIn()): ?>
            <button class="sidebar-btn" onclick="joinCommunity(<?php echo $community['id']; ?>, this)">
              Join Community
            </button>
          <?php endif; ?>
        </div>

        <div class="sidebar-card">
          <h3 class="sidebar-title">📜 Community Rules</h3>
          <ol class="rules-list">
            <li>Be respectful to all members</li>
            <li>Stay on topic</li>
            <li>No spam or self-promotion</li>
            <li>Follow SpriteVerse guidelines</li>
          </ol>
        </div>
      </aside>
    </div>
  </main>

  <!-- Create Post Modal -->
  <div class="modal-overlay" id="createPostModal">
    <div class="modal-container">
      <div class="modal-header">
        <h2 class="modal-title">📝 Create Post in c/<?php echo htmlspecialchars($community['name']); ?></h2>
        <button class="modal-close" onclick="closeCreatePostModal()">✕</button>
      </div>
      
      <form id="createPostForm" onsubmit="handleCreatePost(event)" enctype="multipart/form-data">
        <input type="hidden" name="community_id" value="<?php echo $community['id']; ?>">
        
        <div class="modal-body">
          <!-- Post Title -->
          <div class="form-group">
            <label for="post_title" class="form-label">
              <span class="label-icon">📌</span>
              Post Title
            </label>
            <input 
              type="text" 
              id="post_title" 
              name="title" 
              class="form-input" 
              placeholder="Enter an engaging title..."
              maxlength="255"
              required
            >
          </div>

          <!-- Post Content -->
          <div class="form-group">
            <label for="post_content" class="form-label">
              <span class="label-icon">✍️</span>
              Content (Optional)
            </label>
            <textarea 
              id="post_content" 
              name="content" 
              class="form-textarea" 
              placeholder="Share your thoughts..."
              rows="6"
            ></textarea>
          </div>

          <!-- Image Upload -->
          <div class="form-group">
            <label for="post_image" class="form-label">
              <span class="label-icon">🖼️</span>
              Upload Image (Optional)
            </label>
            <div class="file-input-wrapper">
              <input 
                type="file" 
                id="post_image" 
                name="image" 
                class="file-input" 
                accept="image/*"
                onchange="previewImage(event)"
              >
              <label for="post_image" class="file-input-label">
                <span class="file-icon">📁</span>
                <span class="file-text">Choose an image</span>
              </label>
            </div>
            <div id="imagePreview" class="image-preview" style="display: none;">
              <img id="previewImg" src="" alt="Preview">
              <button type="button" class="remove-image" onclick="removeImage()">✕ Remove</button>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-secondary" onclick="closeCreatePostModal()">
            Cancel
          </button>
          <button type="submit" class="btn-primary" id="createPostSubmitBtn">
            <span class="btn-text">Create Post</span>
            <span class="btn-loading" style="display: none;">
              <span class="spinner"></span> Creating...
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
  
  <script src="js/navbar.js"></script>
  <script src="js/feed.js"></script>
  <script src="js/modal.js"></script>
  <script src="js/community.js"></script>
</body>
</html>