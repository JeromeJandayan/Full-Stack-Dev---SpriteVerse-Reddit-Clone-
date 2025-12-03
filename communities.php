<?php
// communities.php - All communities page
require_once 'config.php';

// Fetch all communities with member count
$query = "SELECT 
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
          GROUP BY c.id
          ORDER BY member_count DESC, c.created_at DESC";

$result = $conn->query($query);
$communities = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $communities[] = $row;
  }
}

// Check which communities the user has joined
$joinedCommunities = [];
if (isLoggedIn()) {
  $userId = getCurrentUserId();
  $joinedQuery = "SELECT community_id FROM community_members WHERE user_id = ?";
  $joinedStmt = $conn->prepare($joinedQuery);
  $joinedStmt->bind_param("i", $userId);
  $joinedStmt->execute();
  $joinedResult = $joinedStmt->get_result();
  
  while ($row = $joinedResult->fetch_assoc()) {
    $joinedCommunities[] = $row['community_id'];
  }
  $joinedStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpriteVerse - Communities</title>
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/communities.css">
  <link rel="stylesheet" href="css/modal.css">
</head>
<body <?php echo isLoggedIn() ? 'data-logged-in="true"' : 'data-logged-in="false"'; ?>>
  <?php include 'navbar.php'; ?>
  
  <main class="communities-container">
    <!-- Header Section -->
    <section class="communities-header">
      <div class="header-content">
        <h1 class="page-title">🌟 All Communities</h1>
        <p class="page-subtitle">Discover and join communities that match your interests</p>
      </div>
      <?php if (isLoggedIn()): ?>
        <button class="create-community-btn" onclick="openCreateCommunityModal()">
          <span class="btn-icon">+</span>
          <span>Create Community</span>
        </button>
      <?php endif; ?>
    </section>

    <!-- Communities Grid -->
    <section class="communities-grid">
      <?php if (!empty($communities)): ?>
        <?php foreach ($communities as $community): ?>
          <article class="community-card">
            <!-- Community Icon -->
            <div class="community-card-icon">
              <?php if ($community['icon_url']): ?>
                <img src="<?php echo htmlspecialchars($community['icon_url']); ?>" alt="<?php echo htmlspecialchars($community['name']); ?>">
              <?php else: ?>
                <span class="icon-placeholder-large">
                  <?php echo strtoupper(substr($community['name'], 0, 1)); ?>
                </span>
              <?php endif; ?>
            </div>

            <!-- Community Info -->
            <div class="community-card-content">
              <h2 class="community-card-title">
                <a href="community.php?id=<?php echo $community['id']; ?>">
                  <?php echo htmlspecialchars($community['name']); ?>
                </a>
              </h2>
              <p class="community-card-description">
                <?php echo htmlspecialchars(substr($community['description'], 0, 120)); ?>
                <?php if (strlen($community['description']) > 120) echo '...'; ?>
              </p>
              
              <!-- Community Stats -->
              <div class="community-card-stats">
                <span class="stat-item">
                  <span class="stat-icon">👥</span>
                  <span class="stat-value"><?php echo number_format($community['member_count']); ?></span>
                  <span class="stat-label">members</span>
                </span>
                <span class="stat-item">
                  <span class="stat-icon">📝</span>
                  <span class="stat-value"><?php echo number_format($community['post_count']); ?></span>
                  <span class="stat-label">posts</span>
                </span>
              </div>

              <!-- Creator Info -->
              <div class="community-card-creator">
                Created by <span class="creator-name">u/<?php echo htmlspecialchars($community['creator_username']); ?></span>
              </div>
            </div>

            <!-- Join/View Button -->
            <div class="community-card-actions">
              <?php if (isLoggedIn()): ?>
                <?php if (in_array($community['id'], $joinedCommunities)): ?>
                  <button class="btn-joined" onclick="window.location.href='community.php?id=<?php echo $community['id']; ?>'">
                    <span class="btn-icon">✓</span>
                    <span>Joined</span>
                  </button>
                <?php else: ?>
                  <button class="btn-join" onclick="joinCommunity(<?php echo $community['id']; ?>, this)">
                    <span class="btn-icon">+</span>
                    <span>Join</span>
                  </button>
                <?php endif; ?>
              <?php else: ?>
                <button class="btn-view" onclick="window.location.href='community.php?id=<?php echo $community['id']; ?>'">
                  <span class="btn-icon">👁️</span>
                  <span>View</span>
                </button>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">🏝️</div>
          <h3>No communities yet</h3>
          <p>Be the first to create a community!</p>
          <?php if (isLoggedIn()): ?>
            <button class="cta-btn" onclick="openCreateCommunityModal()">
              Create Community
            </button>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
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
  <script src="js/modal.js"></script>
  <script src="js/communities.js"></script>
</body>
</html>