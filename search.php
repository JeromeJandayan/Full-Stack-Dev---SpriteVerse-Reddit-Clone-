<?php
// search.php - Search results page
require_once 'config.php';

// Get search query from URL
$searchQuery = trim($_GET['q'] ?? '');
$searchType = $_GET['type'] ?? 'all'; // all, posts, communities, users

// Initialize results arrays
$posts = [];
$communities = [];
$users = [];

if (!empty($searchQuery)) {
  try {
    // Search Posts
    if ($searchType === 'all' || $searchType === 'posts') {
      $stmt = $pdo->prepare("
        SELECT 
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
        WHERE p.title LIKE ? OR p.content LIKE ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
        LIMIT 20
      ");
      $searchTerm = '%' . $searchQuery . '%';
      $stmt->execute([$searchTerm, $searchTerm]);
      $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search Communities
    if ($searchType === 'all' || $searchType === 'communities') {
      $stmt = $pdo->prepare("
        SELECT 
          c.id,
          c.name,
          c.description,
          c.icon_url,
          c.created_at,
          u.username as creator_username,
          COUNT(DISTINCT cm.id) as member_count,
          COUNT(DISTINCT p.id) as post_count
        FROM communities c
        LEFT JOIN users u ON c.created_by = u.id
        LEFT JOIN community_members cm ON c.id = cm.community_id
        LEFT JOIN posts p ON c.id = p.community_id
        WHERE c.name LIKE ? OR c.description LIKE ?
        GROUP BY c.id
        ORDER BY member_count DESC
        LIMIT 20
      ");
      $searchTerm = '%' . $searchQuery . '%';
      $stmt->execute([$searchTerm, $searchTerm]);
      $communities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search Users
    if ($searchType === 'all' || $searchType === 'users') {
      $stmt = $pdo->prepare("
        SELECT 
          u.id,
          u.username,
          u.avatar_url,
          u.bio,
          u.created_at,
          COUNT(DISTINCT p.id) as post_count
        FROM users u
        LEFT JOIN posts p ON u.id = p.user_id
        WHERE u.username LIKE ?
        GROUP BY u.id
        ORDER BY post_count DESC
        LIMIT 20
      ");
      $searchTerm = '%' . $searchQuery . '%';
      $stmt->execute([$searchTerm]);
      $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

  } catch (PDOException $e) {
    $error = "Search error: " . $e->getMessage();
  }
}

// Calculate total results
$totalResults = count($posts) + count($communities) + count($users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search: <?php echo htmlspecialchars($searchQuery); ?> - SpriteVerse</title>
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/search.css">
  <link rel="stylesheet" href="css/feed.css">
</head>
<body <?php echo isLoggedIn() ? 'data-logged-in="true"' : 'data-logged-in="false"'; ?>>
  <?php include 'navbar.php'; ?>
  
  <main class="search-container">
    <!-- Search Header -->
    <section class="search-header">
      <h1 class="search-title">
        🔍 Search Results
        <?php if (!empty($searchQuery)): ?>
          for "<span class="search-query"><?php echo htmlspecialchars($searchQuery); ?></span>"
        <?php endif; ?>
      </h1>
      <p class="search-count">
        <?php if ($totalResults > 0): ?>
          Found <?php echo $totalResults; ?> result<?php echo $totalResults !== 1 ? 's' : ''; ?>
        <?php else: ?>
          No results found
        <?php endif; ?>
      </p>
    </section>

    <?php if (empty($searchQuery)): ?>
      <!-- Empty Search State -->
      <div class="empty-search">
        <div class="empty-icon">🔍</div>
        <h2>Start Your Search</h2>
        <p>Enter a search term to find posts, communities, and users</p>
      </div>
    <?php elseif ($totalResults === 0): ?>
      <!-- No Results State -->
      <div class="no-results">
        <div class="empty-icon">😕</div>
        <h2>No Results Found</h2>
        <p>Try different keywords or browse our <a href="communities.php">communities</a></p>
      </div>
    <?php else: ?>
      <!-- Search Filters -->
      <div class="search-filters">
        <button class="filter-btn <?php echo $searchType === 'all' ? 'active' : ''; ?>" 
                onclick="filterSearch('all')">
          All (<?php echo $totalResults; ?>)
        </button>
        <button class="filter-btn <?php echo $searchType === 'posts' ? 'active' : ''; ?>" 
                onclick="filterSearch('posts')">
          Posts (<?php echo count($posts); ?>)
        </button>
        <button class="filter-btn <?php echo $searchType === 'communities' ? 'active' : ''; ?>" 
                onclick="filterSearch('communities')">
          Communities (<?php echo count($communities); ?>)
        </button>
        <button class="filter-btn <?php echo $searchType === 'users' ? 'active' : ''; ?>" 
                onclick="filterSearch('users')">
          Users (<?php echo count($users); ?>)
        </button>
      </div>

      <!-- Search Results -->
      <div class="search-results">
        <!-- Posts Results -->
        <?php if (($searchType === 'all' || $searchType === 'posts') && !empty($posts)): ?>
          <section class="results-section">
            <h2 class="section-title">📝 Posts</h2>
            <div class="posts-grid">
              <?php foreach ($posts as $post): ?>
                <article class="post-card">
                  <div class="post-header">
                    <a href="community.php?id=<?php echo $post['community_id']; ?>" class="community-badge">
                      c/<?php echo htmlspecialchars($post['community_name']); ?>
                    </a>
                    <div class="post-meta">
                      <a href="profile.php?username=<?php echo urlencode($post['username']); ?>" class="post-author">
                        u/<?php echo htmlspecialchars($post['username']); ?>
                      </a>
                      <span class="post-dot">•</span>
                      <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                    </div>
                  </div>

                  <div class="post-body">
                    <h3 class="post-title">
                      <a href="post.php?id=<?php echo $post['id']; ?>">
                        <?php echo htmlspecialchars($post['title']); ?>
                      </a>
                    </h3>
                    <?php if ($post['content']): ?>
                      <p class="post-content">
                        <?php 
                        $content = htmlspecialchars($post['content']);
                        echo strlen($content) > 150 ? substr($content, 0, 150) . '...' : nl2br($content);
                        ?>
                      </p>
                    <?php endif; ?>
                    <?php if ($post['image_url']): ?>
                      <div class="post-image">
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post image" loading="lazy">
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="post-actions">
                    <span class="action-btn">
                      <span class="icon">⬆️</span>
                      <span class="count"><?php echo formatNumber($post['vote_count']); ?></span>
                    </span>
                    <span class="action-btn">
                      <span class="icon">💬</span>
                      <span class="count"><?php echo formatNumber($post['comment_count']); ?></span>
                    </span>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <!-- Communities Results -->
        <?php if (($searchType === 'all' || $searchType === 'communities') && !empty($communities)): ?>
          <section class="results-section">
            <h2 class="section-title">🏘️ Communities</h2>
            <div class="communities-grid">
              <?php foreach ($communities as $community): ?>
                <article class="community-card">
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
                    <h3 class="community-name">
                      <a href="community.php?id=<?php echo $community['id']; ?>">
                        c/<?php echo htmlspecialchars($community['name']); ?>
                      </a>
                    </h3>
                    <p class="community-description">
                      <?php echo htmlspecialchars(substr($community['description'], 0, 100)); ?>
                      <?php if (strlen($community['description']) > 100) echo '...'; ?>
                    </p>
                    <div class="community-stats">
                      <span class="stat">
                        <span class="icon">👥</span>
                        <?php echo formatNumber($community['member_count']); ?> members
                      </span>
                      <span class="stat">
                        <span class="icon">📝</span>
                        <?php echo formatNumber($community['post_count']); ?> posts
                      </span>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <!-- Users Results -->
        <?php if (($searchType === 'all' || $searchType === 'users') && !empty($users)): ?>
          <section class="results-section">
            <h2 class="section-title">👤 Users</h2>
            <div class="users-grid">
              <?php foreach ($users as $user): ?>
                <article class="user-card">
                  <a href="profile.php?username=<?php echo urlencode($user['username']); ?>" class="user-link">
                    <div class="user-avatar">
                      <?php if ($user['avatar_url']): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="<?php echo htmlspecialchars($user['username']); ?>">
                      <?php else: ?>
                        <span class="avatar-placeholder">
                          <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </span>
                      <?php endif; ?>
                    </div>
                    <div class="user-info">
                      <h3 class="user-name">u/<?php echo htmlspecialchars($user['username']); ?></h3>
                      <?php if ($user['bio']): ?>
                        <p class="user-bio">
                          <?php echo htmlspecialchars(substr($user['bio'], 0, 80)); ?>
                          <?php if (strlen($user['bio']) > 80) echo '...'; ?>
                        </p>
                      <?php endif; ?>
                      <div class="user-stats">
                        <span class="stat">📝 <?php echo formatNumber($user['post_count']); ?> posts</span>
                        <span class="stat">📅 Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                      </div>
                    </div>
                  </a>
                </article>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif; ?>
      </div>
    <?php endif; ?>
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
  <script src="js/search.js"></script>
  <script src="js/modal.js"></script>
  <script src="js/feed.js"></script>
</body>
</html>