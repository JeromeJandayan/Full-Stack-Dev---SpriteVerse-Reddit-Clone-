<?php
// auth.php - Authentication page (Login/Register)
require_once 'config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Initialize error message
$error = '';
$success = '';

// Check if there's a message from redirect
if (isset($_GET['message'])) {
    if ($_GET['message'] === 'login_required') {
        $error = 'Please login to continue';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpriteVerse - Login / Register</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body <?php echo isLoggedIn() ? 'data-logged-in="true"' : 'data-logged-in="false"'; ?>>
    <?php include 'navbar.php'; ?>
    
    <main class="auth-container">
        <div class="auth-wrapper">
            <!-- Left Side - Branding -->
            <div class="auth-branding">
                <div class="branding-content">
                    <h1 class="brand-title">🎮 SpriteVerse</h1>
                    <p class="brand-subtitle">The Ultimate 2D Game Community</p>
                    <div class="brand-features">
                        <div class="feature-item">
                            <span class="feature-icon">🎨</span>
                            <span class="feature-text">Share Pixel Art</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">💬</span>
                            <span class="feature-text">Join Discussions</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">🚀</span>
                            <span class="feature-text">Connect with Devs</span>
                        </div>
                        <div class="feature-item">
                            <span class="feature-icon">⭐</span>
                            <span class="feature-text">Discover Games</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Auth Forms -->
            <div class="auth-forms">
                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-error" id="alertMessage">
                        <span class="alert-icon">⚠️</span>
                        <span class="alert-text"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" id="alertMessage">
                        <span class="alert-icon">✅</span>
                        <span class="alert-text"><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Tab Buttons -->
                <div class="auth-tabs">
                    <button class="auth-tab active" id="loginTab" onclick="switchTab('login')">
                        Login
                    </button>
                    <button class="auth-tab" id="registerTab" onclick="switchTab('register')">
                        Register
                    </button>
                </div>

                <!-- Login Form -->
                <div class="auth-form-container active" id="loginForm">
                    <h2 class="form-title">Welcome Back!</h2>
                    <p class="form-subtitle">Login to continue your journey</p>
                    
                    <form id="loginFormElement" onsubmit="handleLogin(event)">
                        <div class="form-group">
                            <label for="login_username" class="form-label">
                                <span class="label-icon">👤</span>
                                Username or Email
                            </label>
                            <input 
                                type="text" 
                                id="login_username" 
                                name="username" 
                                class="form-input" 
                                placeholder="Enter your username or email"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="login_password" class="form-label">
                                <span class="label-icon">🔒</span>
                                Password
                            </label>
                            <div class="password-input-wrapper">
                                <input 
                                    type="password" 
                                    id="login_password" 
                                    name="password" 
                                    class="form-input" 
                                    placeholder="Enter your password"
                                    required
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword('login_password')">
                                    👁️
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="loginSubmitBtn">
                            <span class="btn-text">Login</span>
                            <span class="btn-loading" style="display: none;">
                                <span class="spinner"></span> Logging in...
                            </span>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>Don't have an account? <a href="#" onclick="switchTab('register'); return false;">Register here</a></p>
                    </div>
                </div>

                <!-- Register Form -->
                <div class="auth-form-container" id="registerForm">
                    <h2 class="form-title">Join SpriteVerse</h2>
                    <p class="form-subtitle">Create your account and start exploring</p>
                    
                    <form id="registerFormElement" onsubmit="handleRegister(event)">
                        <div class="form-group">
                            <label for="register_username" class="form-label">
                                <span class="label-icon">👤</span>
                                Username
                            </label>
                            <input 
                                type="text" 
                                id="register_username" 
                                name="username" 
                                class="form-input" 
                                placeholder="Choose a unique username"
                                minlength="3"
                                maxlength="50"
                                pattern="[a-zA-Z0-9_]+"
                                title="Username can only contain letters, numbers, and underscores"
                                required
                            >
                            <small class="input-hint">3-50 characters, letters, numbers, and underscores only</small>
                        </div>

                        <div class="form-group">
                            <label for="register_email" class="form-label">
                                <span class="label-icon">📧</span>
                                Email
                            </label>
                            <input 
                                type="email" 
                                id="register_email" 
                                name="email" 
                                class="form-input" 
                                placeholder="your.email@example.com"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="register_password" class="form-label">
                                <span class="label-icon">🔒</span>
                                Password
                            </label>
                            <div class="password-input-wrapper">
                                <input 
                                    type="password" 
                                    id="register_password" 
                                    name="password" 
                                    class="form-input" 
                                    placeholder="Create a strong password"
                                    minlength="6"
                                    required
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword('register_password')">
                                    👁️
                                </button>
                            </div>
                            <small class="input-hint">Minimum 6 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="register_confirm_password" class="form-label">
                                <span class="label-icon">🔒</span>
                                Confirm Password
                            </label>
                            <div class="password-input-wrapper">
                                <input 
                                    type="password" 
                                    id="register_confirm_password" 
                                    name="confirm_password" 
                                    class="form-input" 
                                    placeholder="Re-enter your password"
                                    minlength="6"
                                    required
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword('register_confirm_password')">
                                    👁️
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="registerSubmitBtn">
                            <span class="btn-text">Create Account</span>
                            <span class="btn-loading" style="display: none;">
                                <span class="spinner"></span> Creating account...
                            </span>
                        </button>
                    </form>

                    <div class="form-footer">
                        <p>Already have an account? <a href="#" onclick="switchTab('login'); return false;">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="js/navbar.js"></script>
    <script src="js/auth.js"></script>
</body>
</html>