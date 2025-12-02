// communities.js - Communities page functionality

// Join Community
async function joinCommunity(communityId, button) {
  // Check if user is logged in
  if (!isUserLoggedIn()) {
    alert('Please login to join communities');
    window.location.href = 'auth.php';
    return;
  }

  // Disable button during request
  button.disabled = true;
  const originalHTML = button.innerHTML;
  button.innerHTML = '<span class="spinner"></span> Joining...';

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
      // Change button to "Joined" state
      button.className = 'btn-joined';
      button.innerHTML = '<span class="btn-icon">✓</span><span>Joined</span>';
      button.onclick = function() {
        window.location.href = `community.php?id=${communityId}`;
      };
      
      // Show success notification
      showNotification('Successfully joined the community!', 'success');
    } else {
      alert(data.message || 'Failed to join community');
      button.disabled = false;
      button.innerHTML = originalHTML;
    }
  } catch (error) {
    console.error('Join community error:', error);
    alert('An error occurred. Please try again.');
    button.disabled = false;
    button.innerHTML = originalHTML;
  }
}

// Helper function to check if user is logged in
function isUserLoggedIn() {
  return document.body.dataset.loggedIn === 'true';
}

// Show notification
function showNotification(message, type) {
  const notification = document.createElement('div');
  notification.className = 'notification';
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
  notification.textContent = message;
  
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
`;
document.head.appendChild(style);

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