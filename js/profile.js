// profile.js - Profile page functionality

// Open Edit Profile Modal
function openEditProfileModal() {
  const modal = document.getElementById('editProfileModal');
  if (modal) {
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
  }
}

// Close Edit Profile Modal
function closeEditProfileModal() {
  const modal = document.getElementById('editProfileModal');
  if (modal) {
    modal.classList.remove('show');
    document.body.style.overflow = '';
    
    // Reset form
    const form = document.getElementById('editProfileForm');
    if (form) {
      form.reset();
    }
  }
}

// Preview Avatar
function previewAvatar(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('currentAvatarPreview');
  const fileText = document.querySelector('.file-text-avatar');
  
  if (file) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      // Replace the preview with new image
      preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">`;
      fileText.textContent = file.name;
    };
    
    reader.readAsDataURL(file);
  }
}

// Handle Edit Profile Form Submission
async function handleEditProfile(event) {
  event.preventDefault();
  
  const form = event.target;
  const submitBtn = document.getElementById('editProfileSubmitBtn');
  const btnText = submitBtn.querySelector('.btn-text');
  const btnLoading = submitBtn.querySelector('.btn-loading');
  
  // Get form data
  const formData = new FormData(form);
  
  // Show loading state
  submitBtn.disabled = true;
  btnText.style.display = 'none';
  btnLoading.style.display = 'flex';
  
  try {
    const response = await fetch('api/update_profile.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if (data.success) {
      showModalAlert('Profile updated successfully! Refreshing...', 'success', 'editProfileModal');
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      showModalAlert(data.message || 'Failed to update profile', 'error', 'editProfileModal');
      submitBtn.disabled = false;
      btnText.style.display = 'block';
      btnLoading.style.display = 'none';
    }
  } catch (error) {
    console.error('Update profile error:', error);
    showModalAlert('An error occurred. Please try again.', 'error', 'editProfileModal');
    submitBtn.disabled = false;
    btnText.style.display = 'block';
    btnLoading.style.display = 'none';
  }
}

// Confirm Delete Account
function confirmDeleteAccount() {
  const confirmed = confirm(
    'Are you sure you want to delete your account?\n\n' +
    'This action is IRREVERSIBLE and will:\n' +
    '• Delete all your posts\n' +
    '• Delete all your comments\n' +
    '• Remove you from all communities\n' +
    '• Permanently delete your profile\n\n' +
    'Type "DELETE" in the next prompt to confirm.'
  );
  
  if (confirmed) {
    const verification = prompt('Type DELETE (in uppercase) to confirm account deletion:');
    
    if (verification === 'DELETE') {
      deleteAccount();
    } else {
      alert('Account deletion cancelled. The text did not match.');
    }
  }
}

// Delete Account
async function deleteAccount() {
  try {
    const response = await fetch('api/delete_account.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert('Your account has been deleted successfully.');
      window.location.href = 'index.php';
    } else {
      alert(data.message || 'Failed to delete account');
    }
  } catch (error) {
    console.error('Delete account error:', error);
    alert('An error occurred. Please try again.');
  }
}

// Show alert in modal
function showModalAlert(message, type, modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  
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

// Close modal on click outside
document.addEventListener('DOMContentLoaded', function() {
  const editModal = document.getElementById('editProfileModal');
  
  editModal?.addEventListener('click', function(e) {
    if (e.target === editModal) {
      closeEditProfileModal();
    }
  });
  
  // Close on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && editModal?.classList.contains('show')) {
      closeEditProfileModal();
    }
  });
});