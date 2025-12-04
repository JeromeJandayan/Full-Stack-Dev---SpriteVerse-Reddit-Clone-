// community.js - Single community page functionality

// Join Community
async function joinCommunity(communityId, button) {
  // Check if user is logged in
  if (!isUserLoggedIn()) {
    alert('Please login to join communities');
    window.location.href = 'auth.php';
    return;
  }

  // Disable button during request
  if (button) {
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<span class="spinner">Joining...</span>';
  }

  try {
    const response = await fetch('api/join_community.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        community_id: communityId
      })
    });

    const data = await response.json();

    if (data.success) {
      showNotification('Successfully joined the community!');
      
      // Reload page to update membership status
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      alert(data.message || 'Failed to join community');
      if (button) {
        button.disabled = false;
        button.innerHTML = originalHTML;
      }
    }
  } catch (error) {
    console.error('Join community error:', error);
    alert('An error occurred. Please try again.');
    if (button) {
      button.disabled = false;
      button.innerHTML = originalHTML;
    }
  }
}

// Leave Community
async function leaveCommunity(communityId) {
  if (!confirm('Are you sure you want to leave this community?')) {
    return;
  }

  try {
    const response = await fetch('api/leave_community.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        community_id: communityId
      })
    });

    const data = await response.json();

    if (data.success) {
      showNotification('You have left the community');
      
      // Reload page to update membership status
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      alert(data.message || 'Failed to leave community');
    }
  } catch (error) {
    console.error('Leave community error:', error);
    alert('An error occurred. Please try again.');
  }
}

// Open Create Post Modal (with pre-selected community)
function openCreatePostModal(communityId) {
  const modal = document.getElementById('createPostModal');
  if (modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
  }
}

// Helper Functions
function isUserLoggedIn() {
  return document.body.dataset.loggedIn === 'true';
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

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateX(100%);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }
  
  @keyframes slideOut {
    to {
      opacity: 0;
      transform: translateX(100%);
    }
  }
  
  .spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
    display: inline-block;
    margin-right: 8px;
  }
  
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
`;
document.head.appendChild(style);