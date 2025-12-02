<?php
// navbar.php - Main navigation bar component
if (!isset($conn)) {
  require_once 'config.php';
}
?>

<nav class="navbar">
  <div class="navbar-container">
    <!-- Logo Section -->
    <div class="navbar-logo">
      <a href="index.php">
        <img src="assets/logo/SpriteVerse logo - darkmode.svg" alt="SpriteVerse Logo" class="logo-img">
      </a>
    </div>

    <!-- Navigation Links -->
    <div class="navbar-links">
      <a href="index.php" class="nav-link active">
        <i class="icon-home"></i>
        <span>Home</span>
      </a>
      <a href="communities.php" class="nav-link">
        <i class="icon-communities"></i>
        <span>Communities</span>
      </a>
    </div>

    <!-- Search Bar -->
    <div class="navbar-search">
      <input 
        type="text" 
        id="searchInput" 
        placeholder="Search posts, communities..." 
        class="search-input"
      >
      <button class="search-btn" id="searchBtn">
        <i class="icon-search">🔍</i>
      </button>
    </div>

    <!-- Right Side Actions -->
    <div class="navbar-actions">
      <!-- Theme Toggle -->
      <button class="theme-toggle" id="themeToggle" title="Toggle theme">
        <span class="theme-icon">🌙</span>
      </button>

      <?php if (isLoggedIn()): ?>
        <!-- Create Dropdown (only for logged-in users) -->
        <div class="create-dropdown">
          <button class="create-btn" id="createBtn">
            <span class="plus-icon">+</span>
          </button>
          <div class="dropdown-menu" id="createDropdown">
            <button class="dropdown-item" id="createPostBtn">
              <i class="icon">📝</i>
              <span>Create Post</span>
            </button>
            <button class="dropdown-item" id="createCommunityBtn">
              <i class="icon">👥</i>
              <span>Create Community</span>
            </button>
          </div>
        </div>

        <!-- User Avatar Dropdown -->
        <div class="user-dropdown">
          <button class="user-avatar" id="userAvatarBtn">
            <?php if (!empty($_SESSION['avatar_url'])): ?>
              <img src="<?php echo htmlspecialchars($_SESSION['avatar_url']); ?>" alt="Avatar">
            <?php else: ?>
              <span class="avatar-placeholder">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
              </span>
            <?php endif; ?>
          </button>
          <div class="dropdown-menu" id="userDropdown">
            <div class="dropdown-header">
              <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <button class="dropdown-item" onclick="window.location.href='profile.php'">
              <i class="icon">👤</i>
              <span>Profile</span>
            </button>
            <button class="dropdown-item" onclick="window.location.href='settings.php'">
              <i class="icon">⚙️</i>
              <span>Settings</span>
            </button>
            <div class="dropdown-divider"></div>
            <button class="dropdown-item logout-btn" onclick="window.location.href='api/logout.php'">
              <i class="icon">🚪</i>
              <span>Logout</span>
            </button>
          </div>
        </div>
      <?php else: ?>
        <!-- Login Button (for guests) -->
        <button class="login-btn" onclick="window.location.href='auth.php'">
          Login
        </button>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Add this to prevent layout shift -->
<div class="navbar-spacer"></div>