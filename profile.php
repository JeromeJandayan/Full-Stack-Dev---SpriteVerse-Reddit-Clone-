<?php
// profile.php - User profile page
require_once 'config.php';

// Get username from URL parameter
$viewUsername = $_GET['username'] ?? null;

// If no username provided, redirect to own profile if logged in
if (!$viewUsername) {
  if (isLoggedIn()) {
    header('Location: profile.php?username=' . urlencode(getCurrentUsername()));
    exit();
  } else {
    header('Location: auth.php');
    exit();
  }
}

// Fetch user data
$userQuery = "SELECT id, username, email, avatar_url, bio, created_at FROM users WHERE username = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("s", $viewUsername);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows === 0) {
  // User not found
  header('Location: index.php');
  exit();
}

$profileUser = $userResult->fetch_assoc();
$userStmt->close();

// Check if viewing own profile
$isOwnProfile = isLoggedIn() && getCurrentUserId() == $profileUser['id'];

// Fetch user's posts
$postsQuery = "SELECT 
                p.id,
                p.title,
                p.content,
                p.image_url,
                p.created_at,
                c.name as community_name,
                c.id as community_id,
                COUNT(DISTINCT pv.id) as vote_count,
                COUNT(DISTINCT cm.id) as comment_count
              FROM posts p
              JOIN communities c ON p.community_id = c.id
              LEFT JOIN post_votes pv ON p.id = pv.post_id
              LEFT JOIN comments cm ON p.id = cm.post_id
              WHERE p.user_id = ?
              GROUP BY p.id
              ORDER BY p.created_at DESC";

$postsStmt = $conn->prepare($postsQuery);
$postsStmt->bind_param("i", $profileUser['id']);
$postsStmt->execute();
$postsResult = $postsStmt->get_result();

$userPosts = [];
while ($row = $postsResult->fetch_assoc()) {
  $userPosts[] = $row;
}
$postsStmt->close();

// Fetch user's communities
$communitiesQuery = "SELECT 
                      c.id,
                      c.name,
                      c.icon_url,
                      cm.role,
                      cm.joined_at
                    FROM community_members cm
                    JOIN communities c ON cm.community_id = c.id
                    WHERE cm.user_id = ?
                    ORDER BY cm.joined_at DESC";

$commStmt = $conn->prepare($communitiesQuery);
$commStmt->bind_param("i", $profileUser['id']);
$commStmt->execute();
$commResult = $commStmt->get_result();

$userCommunities = [];
while ($row = $commResult->fetch_assoc()) {
  $userCommunities[] = $row;
}
$commStmt->close();

// Get stats
$postCount = count($userPosts);
$communityCount = count($userCommunities);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($profileUser['username']); ?> - SpriteVerse</title>
  <link rel="stylesheet" href="css/navbar.css">
  <link rel="stylesheet" href="css/profile.css">
  <link rel="stylesheet" href="css/modal.css">
</head>
<body <?php echo isLoggedIn() ? 'data-logged-in="true"' : 'data-logged-in="false"'; ?>>
  <?php include 'navbar.php'; ?>
  
  <main class="profile-container">
    <!-- Profile Header -->
    <section class="profile-header">
      <div class="profile-banner">
        <div class="profile-avatar">
          <?php if ($profileUser['avatar_url']): ?>
            <img src="<?php echo htmlspecialchars($profileUser['avatar_url']); ?>" alt="<?php echo htmlspecialchars($profileUser['username']); ?>">
          <?php else: ?>
            <span class="avatar-placeholder-large">
              <?php echo strtoupper(substr($profileUser['username'], 0, 1)); ?>
            </span>
          <?php endif; ?>
        </div>
      </div>

      <div class="profile-info">
        <div class="profile-main">
          <h1 class="profile-username">u/<?php echo htmlspecialchars($profileUser['username']); ?></h1>
          <?php if ($isOwnProfile): ?>
            <button class="btn-edit-profile" onclick="openEditProfileModal()">
              <span class="btn-icon">✏️</span>
              <span>Edit Profile</span>
            </button>
          <?php endif; ?>
        </div>

        <?php if ($profileUser['bio']): ?>
          <p class="profile-bio"><?php echo nl2br(htmlspecialchars($profileUser['bio'])); ?></p>
        <?php endif; ?>

        <div class="profile-stats">
          <div class="stat-item">
            <span class="stat-value"><?php echo number_format($postCount); ?></span>
            <span class="stat-label">Posts</span>
          </div>
          <div class="stat-item">
            <span class="stat-value"><?php echo number_format($communityCount); ?></span>
            <span class="stat-label">Communities</span>
          </div>
          <div class="stat-item">
            <span class="stat-value"><?php echo date('M Y', strtotime($profileUser['created_at'])); ?></span>
            <span class="stat-label">Joined</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Profile Content -->
    <div class="profile-content">
      <!-- Left Sidebar - Communities -->
      <aside class="profile-sidebar">
        <div class="sidebar-section">
          <h3 class="section-title">🏘️ Communities</h3>
          <?php if (!empty($userCommunities)): ?>
            <div class="communities-list">
              <?php foreach ($userCommunities as $community): ?>
                <a href="community.php?id=<?php echo $community['id']; ?>" class="community-item">
                  <div class="community-icon-small">
                    <?php if ($community['icon_url']): ?>
                      <img src="<?php echo htmlspecialchars($community['icon_url']); ?>" alt="<?php echo htmlspecialchars($community['name']); ?>">
                    <?php else: ?>
                      <span class="icon-placeholder-small">
                        <?php echo strtoupper(substr($community['name'], 0, 1)); ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="community-info-small">
                    <span class="community-name-small"><?php echo htmlspecialchars($community['name']); ?></span>
                    <span class="community-role"><?php echo htmlspecialchars($community['role']); ?></span>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="empty-text">No communities yet</p>
          <?php endif; ?>
        </div>

        <?php if ($isOwnProfile): ?>
          <div class="sidebar-section danger-zone">
            <h3 class="section-title">⚠️ Danger Zone</h3>
            <p class="danger-text">Once you delete your account, there is no going back.</p>
            <button class="btn-delete-account" onclick="confirmDeleteAccount()">
              Delete Account
            </button>
          </div>
        <?php endif; ?>
      </aside>

      <!-- Main Content - Posts -->
      <section class="profile-posts">
        <h2 class="posts-title">📝 Posts</h2>
        
        <?php if (!empty($userPosts)): ?>
          <div class="posts-list">
            <?php foreach ($userPosts as $post): ?>
              <article class="post-card">
                <div class="post-header">
                  <a href="community.php?id=<?php echo $post['community_id']; ?>" class="post-community">
                    c/<?php echo htmlspecialchars($post['community_name']); ?>
                  </a>
                  <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                </div>

                <h3 class="post-title">
                  <a href="post.php?id=<?php echo $post['id']; ?>">
                    <?php echo htmlspecialchars($post['title']); ?>
                  </a>
                </h3>

                <?php if ($post['content']): ?>
                  <p class="post-content">
                    <?php 
                    $content = htmlspecialchars($post['content']);
                    echo strlen($content) > 200 ? substr($content, 0, 200) . '...' : $content;
                    ?>
                  </p>
                <?php endif; ?>

                <?php if ($post['image_url']): ?>
                  <div class="post-image-small">
                    <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post image">
                  </div>
                <?php endif; ?>

                <div class="post-stats">
                  <span class="stat">⬆️ <?php echo number_format($post['vote_count']); ?></span>
                  <span class="stat">💬 <?php echo number_format($post['comment_count']); ?></span>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>No posts yet</h3>
            <p><?php echo $isOwnProfile ? "You haven't" : "This user hasn't"; ?> created any posts.</p>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <?php if ($isOwnProfile): ?>
    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="editProfileModal">
      <div class="modal-container">
        <div class="modal-header">
          <h2 class="modal-title">✏️ Edit Profile</h2>
          <button class="modal-close" onclick="closeEditProfileModal()">✕</button>
        </div>
        
        <form id="editProfileForm" onsubmit="handleEditProfile(event)" enctype="multipart/form-data">
          <div class="modal-body">
            <!-- Avatar Upload -->
            <div class="form-group">
              <label for="profile_avatar" class="form-label">
                <span class="label-icon">👤</span>
                Profile Avatar
              </label>
              <div class="current-avatar">
                <?php if ($profileUser['avatar_url']): ?>
                  <img src="<?php echo htmlspecialchars($profileUser['avatar_url']); ?>" alt="Current avatar" id="currentAvatarPreview">
                <?php else: ?>
                  <span class="avatar-placeholder-large" id="currentAvatarPreview">
                    <?php echo strtoupper(substr($profileUser['username'], 0, 1)); ?>
                  </span>
                <?php endif; ?>
              </div>
              <div class="file-input-wrapper">
                <input 
                  type="file" 
                  id="profile_avatar" 
                  name="avatar" 
                  class="file-input" 
                  accept="image/*"
                  onchange="previewAvatar(event)"
                >
                <label for="profile_avatar" class="file-input-label">
                  <span class="file-icon">📁</span>
                  <span class="file-text-avatar">Choose new avatar</span>
                </label>
              </div>
            </div>

            <!-- Bio -->
            <div class="form-group">
              <label for="profile_bio" class="form-label">
                <span class="label-icon">📝</span>
                Bio
              </label>
              <textarea 
                id="profile_bio" 
                name="bio" 
                class="form-textarea" 
                placeholder="Tell us about yourself..."
                rows="5"
              ><?php echo htmlspecialchars($profileUser['bio'] ?? ''); ?></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeEditProfileModal()">
              Cancel
            </button>
            <button type="submit" class="btn-primary" id="editProfileSubmitBtn">
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
  <script src="js/modal.js"></script>
  <script src="js/profile.js"></script>
</body>
</html>
