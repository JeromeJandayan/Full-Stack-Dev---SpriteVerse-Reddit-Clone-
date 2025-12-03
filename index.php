<?php
// index.php - Main feed/home page
require_once 'config.php';

// Fetch all posts from communities with user and community info
$query = "SELECT 
            p.id,
            p.title,
            p.content,
            p.image_url,
            p.created_at,
            u.username,
            u.avatar_url,
            c.name as community_name,
            c.id as community_id,
            COUNT(DISTINCT pv.id) as vote_count,
            COUNT(DISTINCT cm.id) as comment_count
          FROM posts p
          JOIN users u ON p.user_id = u.id
          JOIN communities c ON p.community_id = c.id
          LEFT JOIN post_votes pv ON p.id = pv.post_id
          LEFT JOIN comments cm ON p.id = cm.post_id
          GROUP BY p.id
          ORDER BY p.created_at DESC";

$result = $conn->query($query);
$posts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Fetch popular communities for sidebar
$communityQuery = "SELECT 
                    c.id,
                    c.name,
                    c.description,
                    c.icon_url,
                    COUNT(cm.id) as member_count
                   FROM communities c
                   LEFT JOIN community_members cm ON c.id = cm.community_id
                   GROUP BY c.id
                   ORDER BY member_count DESC
                   LIMIT 5";

$communityResult = $conn->query($communityQuery);
$popularCommunities = [];
if ($communityResult) {
    while ($row = $communityResult->fetch_assoc()) {
        $popularCommunities[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpriteVerse - Home</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/feed.css">
    <link rel="stylesheet" href="css/modal.css">
</head>
<body <?php echo isLoggedIn() ? 'data-logged-in="true"' : 'data-logged-in="false"'; ?>>
    <?php include 'navbar.php'; ?>
    
    <main class="main-container">
        <!-- Left Sidebar - Communities -->
        <aside class="sidebar sidebar-left">
            <div class="sidebar-card">
                <h3 class="sidebar-title">🔥 Popular Communities</h3>
                <div class="community-list">
                    <?php if (!empty($popularCommunities)): ?>
                        <?php foreach ($popularCommunities as $community): ?>
                            <a href="community.php?id=<?php echo $community['id']; ?>" class="community-item">
                                <div class="community-icon">
                                    <?php if ($community['icon_url']): ?>
                                        <img src="<?php echo htmlspecialchars($community['icon_url']); ?>" alt="<?php echo htmlspecialchars($community['name']); ?>">
                                    <?php else: ?>
                                        <span class="icon-placeholder">
                                            <?php echo strtoupper(substr($community['name'], 0, 1)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="community-info">
                                    <div class="community-name"><?php echo htmlspecialchars($community['name']); ?></div>
                                    <div class="community-members"><?php echo number_format($community['member_count']); ?> members</div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="empty-state">No communities yet</p>
                    <?php endif; ?>
                </div>
                <a href="communities.php" class="view-all-btn">View All Communities →</a>
            </div>
        </aside>

        <!-- Main Feed -->
        <section class="feed-content">
            <!-- Welcome Banner (show only for guests) -->
            <?php if (!isLoggedIn()): ?>
                <div class="welcome-banner">
                    <h1>🎮 Welcome to SpriteVerse</h1>
                    <p>Join the ultimate 2D game community. Share your pixel art, discuss game dev, and connect with fellow gamers!</p>
                    <button class="cta-btn" onclick="window.location.href='auth.php'">
                        Join Now
                    </button>
                </div>
            <?php endif; ?>

            <!-- Create Post Prompt (for logged-in users) -->
            <?php if (isLoggedIn()): ?>
                <div class="create-post-prompt" onclick="openCreatePostModal()">
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
                        What's on your mind, <?php echo htmlspecialchars($_SESSION['username']); ?>?
                    </div>
                    <button class="create-quick-btn">📝 Post</button>
                </div>
            <?php endif; ?>

            <!-- Posts Feed -->
            <div class="posts-container">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <!-- Post Header -->
                            <div class="post-header">
                                <div class="post-community">
                                    <a href="community.php?id=<?php echo $post['community_id']; ?>" class="community-badge">
                                        c/<?php echo htmlspecialchars($post['community_name']); ?>
                                    </a>
                                </div>
                                <div class="post-meta">
                                    <a href="profile.php?username=<?php echo urlencode($post['username']); ?>" class="post-author">
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
                                    <p class="post-content"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
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
                                    <span class="count"><?php echo number_format($post['vote_count']); ?></span>
                                </button>
                                <button class="action-btn comment-btn" onclick="window.location.href='post.php?id=<?php echo $post['id']; ?>'">
                                    <span class="icon">💬</span>
                                    <span class="count"><?php echo number_format($post['comment_count']); ?></span>
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
                        <p>Be the first to create a post in a community!</p>
                        <?php if (isLoggedIn()): ?>
                            <button class="cta-btn" onclick="openCreatePostModal()">Create Post</button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Right Sidebar - Info -->
        <aside class="sidebar sidebar-right">
            <div class="sidebar-card">
                <h3 class="sidebar-title">ℹ️ About SpriteVerse</h3>
                <p class="about-text">
                    SpriteVerse is a community-driven forum for 2D game enthusiasts. 
                    Share your pixel art, discuss game development, and connect with fellow gamers!
                </p>
                <?php if (!isLoggedIn()): ?>
                    <button class="sidebar-btn" onclick="window.location.href='auth.php'">
                        Join Community
                    </button>
                <?php endif; ?>
            </div>

            <div class="sidebar-card">
                <h3 class="sidebar-title">📜 Community Rules</h3>
                <ol class="rules-list">
                    <li>Be respectful and kind</li>
                    <li>No spam or self-promotion</li>
                    <li>Stay on topic</li>
                    <li>No NSFW content</li>
                    <li>Give credit to original creators</li>
                </ol>
            </div>
        </aside>
    </main>

    <!-- Create Post Modal -->
    <div class="modal-overlay" id="createPostModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2 class="modal-title">📝 Create Post</h2>
          <button class="modal-close" onclick="closeCreatePostModal()">✕</button>
        </div>
        
        <form id="createPostForm" onsubmit="handleCreatePost(event)" enctype="multipart/form-data">
          <div class="modal-body">
            <!-- Community Selection -->
            <div class="form-group">
              <label for="post_community" class="form-label">
                <span class="label-icon">👥</span>
                Choose Community
              </label>
              <select id="post_community" name="community_id" class="form-input" required>
                <option value="">Select a community...</option>
                <?php
                // Fetch communities where user is a member
                if (isLoggedIn()) {
                  $userId = getCurrentUserId();
                  $commQuery = "SELECT DISTINCT c.id, c.name 
                                FROM communities c
                                INNER JOIN community_members cm ON c.id = cm.community_id
                                WHERE cm.user_id = ?
                                ORDER BY c.name";
                  $commStmt = $conn->prepare($commQuery);
                  $commStmt->bind_param("i", $userId);
                  $commStmt->execute();
                  $commResult = $commStmt->get_result();
                  
                  while ($community = $commResult->fetch_assoc()) {
                    echo '<option value="' . $community['id'] . '">' . htmlspecialchars($community['name']) . '</option>';
                  }
                  $commStmt->close();
                }
                ?>
              </select>
              <small class="input-hint">You can only post in communities you've joined</small>
            </div>

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
              <small class="input-hint">Max 255 characters</small>
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
                placeholder="Share your thoughts, ask a question, or start a discussion..."
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

    <!-- Create Community Modal -->
    <div class="modal-overlay" id="createCommunityModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2 class="modal-title">👥 Create Community</h2>
          <button class="modal-close" onclick="closeCommunityModal()">✕</button>
        </div>
        
        <form id="createCommunityForm" onsubmit="handleCreateCommunity(event)" enctype="multipart/form-data">
          <div class="modal-body">
            <!-- Community Name -->
            <div class="form-group">
              <label for="community_name" class="form-label">
                <span class="label-icon">🏷️</span>
                Community Name
              </label>
              <input 
                type="text" 
                id="community_name" 
                name="name" 
                class="form-input" 
                placeholder="Enter community name..."
                maxlength="100"
                pattern="[a-zA-Z0-9 _-]+"
                title="Only letters, numbers, spaces, hyphens, and underscores allowed"
                required
              >
              <small class="input-hint">Max 100 characters. Only letters, numbers, spaces, hyphens, and underscores</small>
            </div>

            <!-- Community Description -->
            <div class="form-group">
              <label for="community_description" class="form-label">
                <span class="label-icon">📄</span>
                Description
              </label>
              <textarea 
                id="community_description" 
                name="description" 
                class="form-textarea" 
                placeholder="Describe what your community is about..."
                rows="5"
                required
              ></textarea>
              <small class="input-hint">Help others understand your community's purpose</small>
            </div>

            <!-- Community Icon -->
            <div class="form-group">
              <label for="community_icon" class="form-label">
                <span class="label-icon">🎨</span>
                Community Icon (Optional)
              </label>
              <div class="file-input-wrapper">
                <input 
                  type="file" 
                  id="community_icon" 
                  name="icon" 
                  class="file-input" 
                  accept="image/*"
                  onchange="previewCommunityIcon(event)"
                >
                <label for="community_icon" class="file-input-label">
                  <span class="file-icon">📁</span>
                  <span class="file-text-community">Choose an icon</span>
                </label>
              </div>
              <div id="communityIconPreview" class="image-preview" style="display: none;">
                <img id="previewCommunityIcon" src="" alt="Preview">
                <button type="button" class="remove-image" onclick="removeCommunityIcon()">✕ Remove</button>
              </div>
              <small class="input-hint">Recommended: Square image (e.g., 200x200px)</small>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeCommunityModal()">
              Cancel
            </button>
            <button type="submit" class="btn-primary" id="createCommunitySubmitBtn">
              <span class="btn-text">Create Community</span>
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
</body>
</html>
