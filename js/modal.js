// modal.js - Modal functionality

// ========== Create Post Modal ==========

// Open Create Post Modal
function openCreatePostModal() {
  const modal = document.getElementById('createPostModal');
  modal.classList.add('show');
  document.body.style.overflow = 'hidden'; // Prevent background scroll
}

// Close Create Post Modal
function closeCreatePostModal() {
  const modal = document.getElementById('createPostModal');
  modal.classList.remove('show');
  document.body.style.overflow = ''; // Restore scroll
  
  // Reset form
  document.getElementById('createPostForm').reset();
  removeImage();
}

// ========== Create Community Modal ==========

// Open Create Community Modal
function openCreateCommunityModal() {
  const modal = document.getElementById('createCommunityModal');
  modal.classList.add('show');
  document.body.style.overflow = 'hidden';
}

// Close Create Community Modal
function closeCommunityModal() {
  const modal = document.getElementById('createCommunityModal');
  modal.classList.remove('show');
  document.body.style.overflow = '';
  
  // Reset form
  document.getElementById('createCommunityForm').reset();
  removeCommunityIcon();
}

// ========== Modal Event Listeners ==========

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
  const postModal = document.getElementById('createPostModal');
  const communityModal = document.getElementById('createCommunityModal');
  
  postModal?.addEventListener('click', function(e) {
    if (e.target === postModal) {
      closeCreatePostModal();
    }
  });
  
  communityModal?.addEventListener('click', function(e) {
    if (e.target === communityModal) {
      closeCommunityModal();
    }
  });
  
  // Close modal on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (postModal?.classList.contains('show')) {
        closeCreatePostModal();
      }
      if (communityModal?.classList.contains('show')) {
        closeCommunityModal();
      }
    }
  });
});

// ========== Image Preview Functions ==========

// Preview uploaded image for post
function previewImage(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('imagePreview');
  const previewImg = document.getElementById('previewImg');
  const fileText = document.querySelector('.file-text');
  
  if (file) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      preview.style.display = 'block';
      fileText.textContent = file.name;
    };
    
    reader.readAsDataURL(file);
  }
}

// Remove image preview for post
function removeImage() {
  const preview = document.getElementById('imagePreview');
  const previewImg = document.getElementById('previewImg');
  const fileInput = document.getElementById('post_image');
  const fileText = document.querySelector('.file-text');
  
  preview.style.display = 'none';
  previewImg.src = '';
  fileInput.value = '';
  fileText.textContent = 'Choose an image';
}

// Preview community icon
function previewCommunityIcon(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('communityIconPreview');
  const previewImg = document.getElementById('previewCommunityIcon');
  const fileText = document.querySelector('.file-text-community');
  
  if (file) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      preview.style.display = 'block';
      fileText.textContent = file.name;
    };
    
    reader.readAsDataURL(file);
  }
}

// Remove community icon preview
function removeCommunityIcon() {
  const preview = document.getElementById('communityIconPreview');
  const previewImg = document.getElementById('previewCommunityIcon');
  const fileInput = document.getElementById('community_icon');
  const fileText = document.querySelector('.file-text-community');
  
  preview.style.display = 'none';
  previewImg.src = '';
  fileInput.value = '';
  fileText.textContent = 'Choose an icon';
}

// ========== Form Submission Handlers ==========

// Handle Create Post Form Submission
async function handleCreatePost(event) {
  event.preventDefault();
  
  const form = event.target;
  const submitBtn = document.getElementById('createPostSubmitBtn');
  const btnText = submitBtn.querySelector('.btn-text');
  const btnLoading = submitBtn.querySelector('.btn-loading');
  
  // Get form data
  const formData = new FormData(form);
  
  // Validate
  const communityId = formData.get('community_id');
  const title = formData.get('title').trim();
  
  if (!communityId) {
    showModalAlert('Please select a community', 'error', 'createPostModal');
    return;
  }
  
  if (!title) {
    showModalAlert('Please enter a post title', 'error', 'createPostModal');
    return;
  }
  
  // Show loading state
  submitBtn.disabled = true;
  btnText.style.display = 'none';
  btnLoading.style.display = 'flex';
  
  try {
    const response = await fetch('api/create_post.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      showModalAlert('Post created successfully! Refreshing...', 'success', 'createPostModal');
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      showModalAlert(data.message || 'Failed to create post', 'error', 'createPostModal');
      submitBtn.disabled = false;
      btnText.style.display = 'block';
      btnLoading.style.display = 'none';
    }
  } catch (error) {
    console.error('Create post error:', error);
    showModalAlert('An error occurred. Please try again.', 'error', 'createPostModal');
    submitBtn.disabled = false;
    btnText.style.display = 'block';
    btnLoading.style.display = 'none';
  }
}

// Handle Create Community Form Submission
async function handleCreateCommunity(event) {
  event.preventDefault();
  
  const form = event.target;
  const submitBtn = document.getElementById('createCommunitySubmitBtn');
  const btnText = submitBtn.querySelector('.btn-text');
  const btnLoading = submitBtn.querySelector('.btn-loading');
  
  // Get form data
  const formData = new FormData(form);
  
  // Validate
  const name = formData.get('name').trim();
  const description = formData.get('description').trim();
  
  if (!name) {
    showModalAlert('Please enter a community name', 'error', 'createCommunityModal');
    return;
  }
  
  if (!description) {
    showModalAlert('Please enter a description', 'error', 'createCommunityModal');
    return;
  }
  
  // Show loading state
  submitBtn.disabled = true;
  btnText.style.display = 'none';
  btnLoading.style.display = 'flex';
  
  try {
    const response = await fetch('api/create_community.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      showModalAlert('Community created successfully! Redirecting...', 'success', 'createCommunityModal');
      setTimeout(() => {
        window.location.href = 'communities.php';
      }, 1000);
    } else {
      showModalAlert(data.message || 'Failed to create community', 'error', 'createCommunityModal');
      submitBtn.disabled = false;
      btnText.style.display = 'block';
      btnLoading.style.display = 'none';
    }
  } catch (error) {
    console.error('Create community error:', error);
    showModalAlert('An error occurred. Please try again.', 'error', 'createCommunityModal');
    submitBtn.disabled = false;
    btnText.style.display = 'block';
    btnLoading.style.display = 'none';
  }
}

// ========== Alert Functions ==========

// Show alert in modal
function showModalAlert(message, type, modalId) {
  const modal = document.getElementById(modalId);
  const modalBody = modal.querySelector('.modal-body');
  
  // Remove existing alerts
  const existingAlerts = modalBody.querySelectorAll('.alert');
  existingAlerts.forEach(alert => alert.remove());
  
  // Create new alert
  const alert = document.createElement('div');
  alert.className = `alert alert-${type}`;
  alert.style.marginBottom = '16px';
  alert.innerHTML = `
    <span class="alert-icon">${type === 'error' ? '⚠️' : '✅'}</span>
    <span class="alert-text">${message}</span>
  `;
  
  // Insert at the top of modal body
  modalBody.insertBefore(alert, modalBody.firstChild);
  
  // Auto-remove after 5 seconds
  setTimeout(() => {
    alert.style.animation = 'slideUp 0.3s ease';
    setTimeout(() => alert.remove(), 300);
  }, 5000);
}