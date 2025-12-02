# SpriteVerse - Complete File Structure

## 📂 Project Directory: `htdocs/SpriteVerse/`

```
SpriteVerse/
│
├── api/                          # Backend API endpoints
│   ├── login.php                 # Handle user login
│   ├── register.php              # Handle user registration
│   ├── logout.php                # Handle user logout
│   └── vote.php                  # Handle post voting
│
├── assets/                       # Static assets
│   ├── logo.png                  # Website logo (36px height)
│   └── (other images/icons)
│
├── css/                          # Stylesheets
│   ├── navbar.css                # Navbar styling
│   ├── feed.css                  # Feed/Index page styling
│   └── auth.css                  # Authentication page styling
│
├── js/                           # JavaScript files
│   ├── navbar.js                 # Navbar functionality
│   ├── feed.js                   # Feed page functionality
│   └── auth.js                   # Authentication page functionality
│
├── uploads/                      # User-uploaded content
│   └── (images uploaded by users - auto-created)
│
├── config.php                    # Database config & helper functions
├── navbar.php                    # Navbar component (included in pages)
├── index.php                     # Main feed/home page
├── auth.php                      # Login/Register page
├── database.sql                  # Database schema & sample data
└── README.md                     # Project documentation
```

## 📝 File Descriptions

### Root Directory Files (Main PHP Pages)

| File | Description | URL |
|------|-------------|-----|
| `index.php` | Main feed showing all posts | `http://localhost/SpriteVerse/` |
| `auth.php` | Login/Register page | `http://localhost/SpriteVerse/auth.php` |
| `config.php` | Database connection & helper functions | (included in other files) |
| `navbar.php` | Navigation bar component | (included in other files) |
| `database.sql` | Database schema | (imported via phpMyAdmin) |

### Future Pages (To Be Created)
- `community.php` - Individual community page
- `post.php` - Individual post detail page
- `profile.php` - User profile page
- `search.php` - Search results page
- `communities.php` - List all communities
- `settings.php` - User settings page

### API Directory (`api/`)
All backend logic and AJAX endpoints go here.

| File | Method | Purpose |
|------|--------|---------|
| `login.php` | POST | Authenticate user login |
| `register.php` | POST | Create new user account |
| `logout.php` | GET | Destroy user session |
| `vote.php` | POST | Handle post upvote/downvote |

### CSS Directory (`css/`)
All stylesheets with neon dark mode theme.

| File | Purpose |
|------|---------|
| `navbar.css` | Navbar styling + CSS variables (theme) |
| `feed.css` | Feed/index page styling |
| `auth.css` | Authentication page styling |

### JavaScript Directory (`js/`)
All client-side JavaScript functionality.

| File | Purpose |
|------|---------|
| `navbar.js` | Theme toggle, dropdowns, search |
| `feed.js` | Post interactions, voting, sharing |
| `auth.js` | Form handling, validation |

### Assets Directory (`assets/`)
Static resources like logos, icons, default images.

### Uploads Directory (`uploads/`)
User-generated content. Created automatically by `config.php`.

## 🔗 File Relationships

### How Files Connect:

```
index.php
├── includes: config.php (database connection)
├── includes: navbar.php (navigation bar)
├── links to: css/navbar.css
├── links to: css/feed.css
├── links to: js/navbar.js
└── links to: js/feed.js

auth.php
├── includes: config.php
├── includes: navbar.php
├── links to: css/navbar.css
├── links to: css/auth.css
├── links to: js/navbar.js
└── links to: js/auth.js

navbar.php
├── uses: config.php functions (isLoggedIn())
└── links to: api/logout.php

js/feed.js
└── calls: api/vote.php

js/auth.js
├── calls: api/login.php
└── calls: api/register.php
```

## 📥 Installation Structure

When setting up the project:

1. Create folder: `htdocs/SpriteVerse/`
2. Place all files according to structure above
3. Import `database.sql` in phpMyAdmin
4. Add logo to `assets/logo.png`
5. Access: `http://localhost/SpriteVerse/`

## ✅ Current Progress

**Completed Files:**

Root:
- ✅ `config.php`
- ✅ `navbar.php`
- ✅ `index.php`
- ✅ `auth.php`
- ✅ `database.sql`

API:
- ✅ `api/login.php`
- ✅ `api/register.php`
- ✅ `api/logout.php`
- ✅ `api/vote.php`

CSS:
- ✅ `css/navbar.css`
- ✅ `css/feed.css`
- ✅ `css/auth.css`

JS:
- ✅ `js/navbar.js`
- ✅ `js/feed.js`
- ✅ `js/auth.js`

**To Be Created:**
- ⏳ Create Post Modal (in modals or as part of index.php)
- ⏳ Create Community Modal
- ⏳ User Profile Page
- ⏳ Community Page
- ⏳ Post Detail Page
- ⏳ Search Page

## 🚀 Access URLs

- **Home/Feed:** `http://localhost/SpriteVerse/`
- **Login/Register:** `http://localhost/SpriteVerse/auth.php`
- **Logout:** `http://localhost/SpriteVerse/api/logout.php`

## 📌 Important Notes

1. **Main PHP files** are in the **root directory**, NOT in a `php/` folder
2. **All API endpoints** go in the `api/` folder
3. **Components** (like navbar.php) can be in root or a `components/` folder
4. **Session data** is set in `config.php` and used throughout
5. **Theme colors** are defined in `css/navbar.css` as CSS variables