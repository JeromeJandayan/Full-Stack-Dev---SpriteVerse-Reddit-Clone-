# 🎮 SpriteVerse - 2D Game Community Forum

A modern, Reddit-style community forum built specifically for 2D game enthusiasts, pixel artists, and game developers. Features a stunning neon dark mode design with full CRUD functionality.

## 🌟 Features

### Core Functionality
- **User Authentication** - Register, login, and secure session management
- **Communities** - Create and join topic-based communities
- **Posts** - Create, edit, and delete posts with image uploads
- **Comments** - Real-time commenting system with edit/delete capabilities
- **Voting System** - Upvote/downvote posts to surface quality content
- **User Profiles** - Customizable profiles with avatars and bios
- **Search** - Advanced search across posts, communities, and users
- **Theme Toggle** - Switch between neon dark mode and light mode

### Design Highlights
- 🎨 **Neon Dark Mode** - Eye-catching cyberpunk aesthetic with vibrant neon accents
- 📱 **Fully Responsive** - Mobile-first design that works on all devices
- ⚡ **Smooth Animations** - Polished UI with transitions and hover effects
- 🚀 **Modern UI/UX** - Inspired by Reddit with enhanced visual appeal

## 📂 Project Structure

```
SpriteVerse/
│
├── api/                          # Backend API endpoints
│   ├── add_comment.php           # Add comment to post
│   ├── create_community.php      # Create new community
│   ├── create_post.php           # Create new post
│   ├── delete_account.php        # Delete user account
│   ├── delete_comment.php        # Delete comment
│   ├── delete_post.php           # Delete post
│   ├── edit_comment.php          # Edit comment
│   ├── edit_post.php             # Edit post
│   ├── get_comments.php          # Fetch post comments
│   ├── join_community.php        # Join community
│   ├── leave_community.php       # Leave community
│   ├── login.php                 # User login
│   ├── logout.php                # User logout
│   ├── register.php              # User registration
│   ├── update_profile.php        # Update user profile
│   └── vote.php                  # Vote on posts
│
├── assets/                       # Static assets
│   └── logo/
│       └── SpriteVerse logo - darkmode.svg
│
├── css/                          # Stylesheets
│   ├── auth.css                  # Authentication pages
│   ├── communities.css           # All communities page
│   ├── community.css             # Single community page
│   ├── feed.css                  # Home feed page
│   ├── modal.css                 # Modal components
│   ├── navbar.css                # Navigation bar + CSS variables
│   ├── post.css                  # Post detail page
│   ├── profile.css               # User profile page
│   └── search.css                # Search results page
│
├── js/                           # JavaScript files
│   ├── auth.js                   # Authentication logic
│   ├── communities.js            # Communities page interactions
│   ├── community.js              # Single community functionality
│   ├── feed.js                   # Feed page interactions
│   ├── modal.js                  # Modal management
│   ├── navbar.js                 # Navbar functionality
│   ├── post.js                   # Post detail page logic
│   ├── profile.js                # Profile page functionality
│   └── search.js                 # Search page interactions
│
├── uploads/                      # User-uploaded content
│   └── (auto-created, .gitignore)
│
├── auth.php                      # Login/Register page
├── communities.php               # All communities listing
├── community.php                 # Single community view
├── config.php                    # Database config & helpers
├── database.sql                  # Database schema
├── index.php                     # Home feed
├── navbar.php                    # Navbar component
├── post.php                      # Post detail page
├── profile.php                   # User profile page
├── search.php                    # Search results page
└── README.md                     # This file
```

## 🗄️ Database Schema

### Tables

**users**
- `id` - Primary key
- `username` - Unique username (3-50 chars)
- `email` - Unique email address
- `password_hash` - Bcrypt hashed password
- `avatar_url` - Profile picture path
- `bio` - User biography
- `created_at` - Registration timestamp

**communities**
- `id` - Primary key
- `name` - Community name (unique)
- `description` - Community description
- `icon_url` - Community icon path
- `created_by` - Foreign key to users
- `created_at` - Creation timestamp

**community_members**
- `id` - Primary key
- `community_id` - Foreign key to communities
- `user_id` - Foreign key to users
- `role` - ENUM: 'Admin', 'Moderator', 'Member'
- `joined_at` - Join timestamp

**posts**
- `id` - Primary key
- `community_id` - Foreign key to communities
- `user_id` - Foreign key to users
- `title` - Post title (max 255 chars)
- `content` - Post content (text)
- `image_url` - Post image path
- `created_at` - Post timestamp
- `updated_at` - Last edit timestamp

**comments**
- `id` - Primary key
- `post_id` - Foreign key to posts
- `user_id` - Foreign key to users
- `content` - Comment text
- `created_at` - Comment timestamp
- `updated_at` - Last edit timestamp

**post_votes**
- `id` - Primary key
- `post_id` - Foreign key to posts
- `user_id` - Foreign key to users
- `vote_type` - ENUM: 'upvote', 'downvote'
- `created_at` - Vote timestamp
- UNIQUE constraint on (post_id, user_id)

## 🚀 Installation

### Prerequisites
- **XAMPP** (or any PHP 7.4+ environment)
- **MySQL 5.7+** / **MariaDB 10.2+**
- **Web browser** (Chrome, Firefox, Edge, Safari)

### Setup Steps

1. **Install XAMPP**
   - Download from [apachefriends.org](https://www.apachefriends.org/)
   - Install and start Apache and MySQL services

2. **Clone/Download Project**
   ```bash
   # Place the SpriteVerse folder in htdocs
   C:\xampp\htdocs\SpriteVerse\
   ```

3. **Create Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Click "New" to create a database
   - Name it: `spriteverse_db`
   - Go to "Import" tab
   - Select `database.sql` from the project
   - Click "Go" to import

4. **Configure Database Connection**
   - Open `config.php`
   - Update credentials if needed (default XAMPP settings work):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'spriteverse_db');
   ```

5. **Add Logo**
   - Place your logo at: `assets/logo/SpriteVerse logo - darkmode.svg`
   - Recommended height: 36px

6. **Set Permissions**
   - Ensure `uploads/` folder has write permissions
   - On Windows: Already set by default
   - On Linux/Mac: `chmod 755 uploads/`

7. **Access Application**
   - Open browser and go to: `http://localhost/SpriteVerse/`
   - Default test account (from database.sql):
     - Username: `admin` or `gamer123`
     - Password: `password`

## 🎯 Usage Guide

### For Users

**Registration**
1. Click "Login" in the navbar
2. Switch to "Register" tab
3. Fill in username (3-50 chars, letters/numbers/underscores only)
4. Enter email and password (min 6 chars)
5. Click "Create Account"

**Creating a Community**
1. Click the "+" button in navbar
2. Select "Create Community"
3. Enter community name and description
4. (Optional) Upload community icon
5. Click "Create Community"
6. You become the Admin automatically

**Joining Communities**
1. Go to "Communities" page
2. Browse available communities
3. Click "Join" button
4. You can now post in that community

**Creating a Post**
1. Join a community first
2. Click "+" button → "Create Post"
3. Select community from dropdown
4. Enter title and content
5. (Optional) Upload an image
6. Click "Create Post"

**Interacting with Posts**
- **Upvote**: Click ⬆️ to upvote
- **Comment**: Click on post, scroll to comment box
- **Share**: Click 🔗 to share (copies link)
- **Edit/Delete**: Click buttons (only on your own posts)

**Profile Management**
1. Click avatar in navbar → "Profile"
2. Click "Edit Profile" button
3. Upload avatar and update bio
4. Click "Save Changes"

### For Developers

**API Endpoints**

All API endpoints are in `api/` folder and return JSON responses:

```javascript
// Example: Join Community
fetch('api/join_community.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ community_id: 1 })
})
```

**Adding New Features**

1. **Database Changes**
   - Modify `database.sql`
   - Add migration queries
   - Update schema comments

2. **API Endpoint**
   - Create new file in `api/`
   - Follow existing patterns
   - Always return JSON with `success` flag

3. **Frontend**
   - Add corresponding JavaScript function
   - Update UI with new elements
   - Add CSS styles if needed

**Helper Functions**

Available in `config.php`:
- `isLoggedIn()` - Check if user is authenticated
- `getCurrentUserId()` - Get current user ID
- `getCurrentUsername()` - Get current username
- `timeAgo($datetime)` - Format timestamps
- `formatNumber($num)` - Format large numbers (1.2K, 1.5M)

## 🎨 Theme Customization

### CSS Variables

All theme colors are defined in `css/navbar.css`:

```css
:root {
  /* Dark Mode (Default) */
  --bg-primary: #0a0e27;
  --bg-secondary: #1a1f3a;
  --bg-hover: #252b4a;
  --text-primary: #e0e6ff;
  --text-secondary: #a0a8cc;
  --neon-blue: #00d4ff;
  --neon-purple: #b84fff;
  --neon-pink: #ff2e97;
  --neon-green: #00ff88;
}

/* Light Mode */
body.light-mode {
  --bg-primary: #ffffff;
  --bg-secondary: #f5f7fa;
  /* ... etc */
}
```

**Customizing Colors**

1. Open `css/navbar.css`
2. Modify CSS variables in `:root`
3. Save and refresh browser

## 📱 Responsive Breakpoints

- **Desktop**: > 1200px
- **Tablet**: 768px - 1199px
- **Mobile**: < 767px

## 🔒 Security Features

- **Password Hashing**: Bcrypt with `PASSWORD_DEFAULT`
- **SQL Injection Prevention**: Prepared statements (PDO & MySQLi)
- **XSS Prevention**: `htmlspecialchars()` on all output
- **CSRF Protection**: Session-based authentication
- **File Upload Validation**: Type and size restrictions
- **Input Validation**: Server-side validation on all forms

## 🐛 Troubleshooting

### Common Issues

**"Connection failed" error**
- Check if MySQL is running in XAMPP
- Verify database name is `spriteverse_db`
- Check credentials in `config.php`

**Images not uploading**
- Check `uploads/` folder exists
- Verify write permissions on `uploads/`
- Check file size (max 5MB for posts, 2MB for avatars)

**"Page not found" errors**
- Ensure mod_rewrite is enabled in Apache
- Check .htaccess file (if using)
- Verify file paths are correct

**Session issues**
- Clear browser cookies
- Check `session_start()` is called in `config.php`
- Verify session save path has permissions

**Styling not loading**
- Clear browser cache (Ctrl+F5)
- Check CSS file paths in HTML
- Verify CSS files exist in `css/` folder

## 🚀 Performance Tips

1. **Enable OPcache** in php.ini
2. **Optimize Images** before uploading
3. **Add Indexes** to frequently queried columns
4. **Enable Gzip Compression** in Apache
5. **Use CDN** for static assets in production

## 📝 Default Login Credentials

After importing `database.sql`:

**Admin Account**
- Username: `admin`
- Email: `admin@spriteverse.com`
- Password: `password`

**Test User**
- Username: `gamer123`
- Email: `gamer@example.com`
- Password: `password`

⚠️ **Change these passwords before deploying to production!**

## 🤝 Contributing

This is a learning project. Feel free to:
- Fork the repository
- Add new features
- Fix bugs
- Improve documentation
- Enhance UI/UX

## 📄 License

This project is open source and available for educational purposes.

## 👨‍💻 Credits

**Developer**: Built as a comprehensive web development learning project

**Design Inspiration**: Reddit, Discord, modern gaming forums

**Color Scheme**: Cyberpunk neon aesthetic

## 🔮 Future Enhancements

Potential features to add:
- [ ] Direct messaging between users
- [ ] Post categories/tags
- [ ] User reputation system
- [ ] Community roles and permissions
- [ ] Markdown support in posts
- [ ] Email notifications
- [ ] Password reset functionality
- [ ] Advanced search filters
- [ ] Trending posts algorithm
- [ ] User blocking/reporting
- [ ] Post bookmarking
- [ ] Dark/Light/Auto theme
- [ ] Multi-language support

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review the database schema
3. Inspect browser console for errors
4. Check PHP error logs in XAMPP

---

**Built with ❤️ for the 2D gaming community**

*Happy coding! 🎮✨*