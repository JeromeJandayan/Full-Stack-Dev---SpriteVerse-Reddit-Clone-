// navbar.js - Navigation bar functionality

document.addEventListener('DOMContentLoaded', function() {
    
    // ========== Theme Toggle ==========
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = document.querySelector('.theme-icon');
    
    // Check for saved theme preference or default to dark mode
    const currentTheme = localStorage.getItem('theme') || 'dark';
    if (currentTheme === 'light') {
        document.body.classList.add('light-mode');
        themeIcon.textContent = '☀️';
    }
    
    themeToggle?.addEventListener('click', function() {
        document.body.classList.toggle('light-mode');
        
        const isLightMode = document.body.classList.contains('light-mode');
        themeIcon.textContent = isLightMode ? '☀️' : '🌙';
        localStorage.setItem('theme', isLightMode ? 'light' : 'dark');
    });

    // ========== Create Dropdown ==========
    const createBtn = document.getElementById('createBtn');
    const createDropdown = document.getElementById('createDropdown');
    const createPostBtn = document.getElementById('createPostBtn');
    const createCommunityBtn = document.getElementById('createCommunityBtn');
    
    createBtn?.addEventListener('click', function(e) {
        e.stopPropagation();
        createDropdown.classList.toggle('show');
        // Close user dropdown if open
        userDropdown?.classList.remove('show');
    });

    // Create Post Button
    createPostBtn?.addEventListener('click', function() {
        createDropdown.classList.remove('show');
        openCreatePostModal();
    });

    // Create Community Button
    createCommunityBtn?.addEventListener('click', function() {
        createDropdown.classList.remove('show');
        openCreateCommunityModal();
    });

    // ========== User Avatar Dropdown ==========
    const userAvatarBtn = document.getElementById('userAvatarBtn');
    const userDropdown = document.getElementById('userDropdown');
    
    userAvatarBtn?.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
        // Close create dropdown if open
        createDropdown?.classList.remove('show');
    });

    // ========== Search Functionality ==========
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    
    // Search on button click
    searchBtn?.addEventListener('click', function() {
        performSearch();
    });

    // Search on Enter key
    searchInput?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    function performSearch() {
        const query = searchInput.value.trim();
        if (query.length > 0) {
            // Redirect to search results page with query parameter
            window.location.href = `search.php?q=${encodeURIComponent(query)}`;
        }
    }

    // ========== Active Link Highlighting ==========
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        link.classList.remove('active');
        
        if (linkPage === currentPage || 
            (currentPage === '' && linkPage === 'index.php') ||
            (currentPage === 'index.php' && linkPage === 'index.php')) {
            link.classList.add('active');
        }
    });

    // ========== Close Dropdowns on Outside Click ==========
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.create-dropdown')) {
            createDropdown?.classList.remove('show');
        }
        if (!e.target.closest('.user-dropdown')) {
            userDropdown?.classList.remove('show');
        }
    });

    // ========== Close Dropdowns on Escape Key ==========
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            createDropdown?.classList.remove('show');
            userDropdown?.classList.remove('show');
        }
    });

    // ========== Navbar Scroll Effect (Optional) ==========
    let lastScroll = 0;
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > lastScroll && currentScroll > 100) {
            // Scrolling down
            navbar.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScroll = currentScroll;
    });
});

// ========== Modal Functions (to be defined when modals are created) ==========
function openCreatePostModal() {
  const modal = document.getElementById('createPostModal');
  if (modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
  }
}

function openCreateCommunityModal() {
  const modal = document.getElementById('createCommunityModal');
  if (modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
  }
}