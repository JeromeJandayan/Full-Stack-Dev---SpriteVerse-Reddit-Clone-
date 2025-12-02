// feed.js - Feed page functionality

document.addEventListener('DOMContentLoaded', function() {
    
    // ========== Upvote/Downvote Functionality ==========
    const upvoteButtons = document.querySelectorAll('.upvote-btn');
    
    upvoteButtons.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const postId = this.getAttribute('data-post-id');
            
            // Check if user is logged in
            if (!isUserLoggedIn()) {
                alert('Please login to vote on posts');
                window.location.href = 'auth.php';
                return;
            }
            
            try {
                const response = await fetch('api/vote.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        post_id: postId,
                        vote_type: 'upvote'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update vote count
                    const countSpan = this.querySelector('.count');
                    countSpan.textContent = formatNumber(data.vote_count);
                    
                    // Toggle active state
                    this.classList.toggle('active');
                    
                    // Add animation
                    this.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                } else {
                    alert(data.message || 'Failed to vote');
                }
            } catch (error) {
                console.error('Vote error:', error);
                alert('An error occurred while voting');
            }
        });
    });
    
    // ========== Share Functionality ==========
    const shareButtons = document.querySelectorAll('.share-btn');
    
    shareButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const postCard = this.closest('.post-card');
            const postTitle = postCard.querySelector('.post-title a').textContent;
            const postUrl = postCard.querySelector('.post-title a').href;
            
            // Check if Web Share API is available
            if (navigator.share) {
                navigator.share({
                    title: postTitle,
                    url: postUrl
                }).catch(err => console.log('Share cancelled'));
            } else {
                // Fallback: Copy to clipboard
                copyToClipboard(postUrl);
                showNotification('Link copied to clipboard!');
            }
        });
    });
    
    // ========== Post Card Click (Go to post detail) ==========
    const postCards = document.querySelectorAll('.post-card');
    
    postCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't navigate if clicking on buttons or links
            if (e.target.closest('button') || e.target.closest('a')) {
                return;
            }
            
            const postLink = this.querySelector('.post-title a');
            if (postLink) {
                window.location.href = postLink.href;
            }
        });
        
        // Add cursor pointer style
        card.style.cursor = 'pointer';
    });
    
    // ========== Infinite Scroll (Optional) ==========
    let page = 1;
    let loading = false;
    let hasMore = true;
    
    window.addEventListener('scroll', function() {
        if (loading || !hasMore) return;
        
        const scrollPosition = window.innerHeight + window.scrollY;
        const threshold = document.documentElement.scrollHeight - 500;
        
        if (scrollPosition >= threshold) {
            loadMorePosts();
        }
    });
    
    async function loadMorePosts() {
        loading = true;
        page++;
        
        try {
            const response = await fetch(`api/get_posts.php?page=${page}`);
            const data = await response.json();
            
            if (data.success && data.posts.length > 0) {
                appendPosts(data.posts);
            } else {
                hasMore = false;
            }
        } catch (error) {
            console.error('Error loading posts:', error);
        } finally {
            loading = false;
        }
    }
    
    function appendPosts(posts) {
        const container = document.querySelector('.posts-container');
        
        posts.forEach(post => {
            const postElement = createPostElement(post);
            container.appendChild(postElement);
        });
    }
    
    function createPostElement(post) {
        // This would create a post card element dynamically
        // Implementation depends on post structure
        const div = document.createElement('div');
        div.className = 'post-card';
        div.innerHTML = `
            <!-- Post content would go here -->
        `;
        return div;
    }
    
    // ========== Image Lazy Loading Enhancement ==========
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('.post-image img').forEach(img => {
            imageObserver.observe(img);
        });
    }
});

// ========== Helper Functions ==========

function isUserLoggedIn() {
    // Check if user is logged in (this would be set by PHP in a meta tag or data attribute)
    return document.body.dataset.loggedIn === 'true';
}

function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
}

function showNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: linear-gradient(135deg, var(--neon-blue), var(--neon-purple));
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 212, 255, 0.4);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}