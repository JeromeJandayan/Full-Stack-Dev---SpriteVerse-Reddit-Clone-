// post.js - Post detail page functionality

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadComments();
    
    // ========== Upvote Functionality ==========
    const upvoteBtn = document.querySelector('.upvote-btn');
    
    upvoteBtn?.addEventListener('click', async function() {
        const postId = this.getAttribute('data-post-id');
        
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
                const countSpan = this.querySelector('.count');
                countSpan.textContent = formatNumber(data.vote_count);
                
                this.classList.toggle('active');
                
                this.style.transform = 'scale(1.1)';
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

// ========== Comment Functions ==========

// Submit Comment
async function submitComment(postId) {
    const textarea = document.getElementById('commentText');
    const content = textarea.value.trim();
    
    if (!content) {
        alert('Please enter a comment');
        return;
    }
    
    const submitBtn = document.querySelector('.btn-submit-comment');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');
    
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoading.style.display = 'flex';
    
    try {
        const response = await fetch('api/add_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                post_id: postId,
                content: content
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Clear textarea
            textarea.value = '';
            
            // Reload comments instead of entire page
            await loadComments();
            
            // Update comment count
            const commentCount = document.querySelectorAll('.comment-card').length;
            document.querySelector('.comments-title').textContent = `💬 Comments (${commentCount})`;
            const commentIndicator = document.querySelector('.comment-indicator .count');
            if (commentIndicator) {
                commentIndicator.textContent = commentCount;
            }
            
            showNotification('Comment added successfully!');
        } else {
            alert(data.message || 'Failed to add comment');
        }
        
        submitBtn.disabled = false;
        btnText.style.display = 'block';
        btnLoading.style.display = 'none';
        
    } catch (error) {
        console.error('Add comment error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        btnText.style.display = 'block';
        btnLoading.style.display = 'none';
    }
}

// Clear Comment
function clearComment() {
    const textarea = document.getElementById('commentText');
    textarea.value = '';
    textarea.blur();
}

// Delete Comment
async function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }
    
    try {
        const response = await fetch('api/delete_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                comment_id: commentId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove comment from DOM
            const commentCard = document.querySelector(`[data-comment-id="${commentId}"]`);
            if (commentCard) {
                commentCard.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    commentCard.remove();
                    
                    // Update comment count
                    const commentCount = document.querySelectorAll('.comment-card').length;
                    document.querySelector('.comments-title').textContent = `💬 Comments (${commentCount})`;
                    const commentIndicator = document.querySelector('.comment-indicator .count');
                    if (commentIndicator) {
                        commentIndicator.textContent = commentCount;
                    }
                    
                    // Show no comments message if empty
                    if (commentCount === 0) {
                        const commentsList = document.getElementById('commentsList');
                        commentsList.innerHTML = `
                            <div class="no-comments">
                                <div class="no-comments-icon">💭</div>
                                <p>No comments yet. Be the first to comment!</p>
                            </div>
                        `;
                    }
                }, 300);
            }
            
            showNotification('Comment deleted successfully!');
        } else {
            alert(data.message || 'Failed to delete comment');
        }
    } catch (error) {
        console.error('Delete comment error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Edit comment functionality
function editComment(commentId) {
    const commentCard = document.querySelector(`[data-comment-id="${commentId}"]`);
    const commentTextElement = commentCard.querySelector('.comment-text');
    const currentContent = commentTextElement.textContent.trim();
    
    const editForm = document.createElement('div');
    editForm.className = 'edit-comment-form';
    editForm.innerHTML = `
        <textarea class="edit-textarea" rows="3">${escapeHtml(currentContent)}</textarea>
        <div class="edit-actions">
            <button class="btn-save" onclick="saveEditComment(${commentId})">Save</button>
            <button class="btn-cancel" onclick="cancelEditComment(${commentId})">Cancel</button>
        </div>
    `;
    
    commentTextElement.style.display = 'none';
    commentTextElement.parentElement.insertBefore(editForm, commentTextElement.nextSibling);
}

async function saveEditComment(commentId) {
    const commentCard = document.querySelector(`[data-comment-id="${commentId}"]`);
    const textarea = commentCard.querySelector('.edit-textarea');
    const newContent = textarea.value.trim();
    
    if (!newContent) {
        showNotification('Comment cannot be empty');
        return;
    }
    
    try {
        const response = await fetch('api/edit_comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                comment_id: commentId,
                content: newContent
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const commentTextElement = commentCard.querySelector('.comment-text');
            commentTextElement.innerHTML = escapeHtml(data.content).replace(/\n/g, '<br>');
            commentTextElement.style.display = 'block';
            
            const editForm = commentCard.querySelector('.edit-comment-form');
            editForm.remove();
            
            showNotification('Comment updated!');
        } else {
            showNotification(data.message || 'Failed to update comment');
        }
    } catch (error) {
        console.error('Error editing comment:', error);
        showNotification('An error occurred');
    }
}

function cancelEditComment(commentId) {
    const commentCard = document.querySelector(`[data-comment-id="${commentId}"]`);
    const commentTextElement = commentCard.querySelector('.comment-text');
    const editForm = commentCard.querySelector('.edit-comment-form');
    
    commentTextElement.style.display = 'block';
    editForm.remove();
}

// Load comments on page load
async function loadComments() {
    const postWrapper = document.querySelector('.post-wrapper');
    if (!postWrapper) return;
    
    const postId = postWrapper.dataset.postId;
    
    try {
        const response = await fetch(`api/get_comments.php?post_id=${postId}`);
        const data = await response.json();
        
        if (data.success) {
            displayComments(data.comments, data.current_user_id);
        }
    } catch (error) {
        console.error('Error loading comments:', error);
    }
}

function displayComments(comments, currentUserId) {
    const container = document.getElementById('commentsList');
    
    if (comments.length === 0) {
        container.innerHTML = `
            <div class="no-comments">
                <div class="no-comments-icon">💭</div>
                <p>No comments yet. Be the first to comment!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = comments.map(comment => `
        <div class="comment-card" data-comment-id="${comment.id}">
            <div class="comment-avatar">
                ${comment.avatar_url 
                    ? `<img src="${comment.avatar_url}" alt="${comment.username}">` 
                    : `<span class="avatar-placeholder-small">${comment.username.charAt(0).toUpperCase()}</span>`
                }
            </div>
            <div class="comment-content">
                <div class="comment-header">
                    <a href="profile.php?username=${encodeURIComponent(comment.username)}" class="comment-author">
                        u/${escapeHtml(comment.username)}
                    </a>
                    <span class="comment-dot">•</span>
                    <span class="comment-time">${comment.time_ago}</span>
                    ${currentUserId == comment.user_id ? `
                        <div style="margin-left: auto; display: flex; gap: 8px;">
                            <button class="btn-edit" onclick="editComment(${comment.id})">Edit</button>
                            <button class="btn-delete-comment" onclick="deleteComment(${comment.id})">Delete</button>
                        </div>
                    ` : ''}
                </div>
                <div class="comment-text">${escapeHtml(comment.content).replace(/\n/g, '<br>')}</div>
            </div>
        </div>
    `).join('');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Confirm Delete Post
function confirmDeletePost(postId) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        deletePost(postId);
    }
}

// Delete Post
async function deletePost(postId) {
    try {
        const response = await fetch('api/delete_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                post_id: postId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Post deleted successfully!');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 1000);
        } else {
            alert(data.message || 'Failed to delete post');
        }
    } catch (error) {
        console.error('Delete post error:', error);
        alert('An error occurred. Please try again.');
    }
}

// Share Post
function sharePost() {
    const postTitle = document.querySelector('.post-detail-title').textContent;
    const postUrl = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: postTitle,
            url: postUrl
        }).catch(err => console.log('Share cancelled'));
    } else {
        copyToClipboard(postUrl);
        showNotification('Link copied to clipboard!');
    }
}

// Helper Functions
function isUserLoggedIn() {
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
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Add animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
`;
document.head.appendChild(style);