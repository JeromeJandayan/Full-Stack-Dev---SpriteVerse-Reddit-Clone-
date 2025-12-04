// search.js - Search page functionality

// Filter search results
function filterSearch(type) {
  const urlParams = new URLSearchParams(window.location.search);
  const query = urlParams.get('q');
  
  if (query) {
    window.location.href = `search.php?q=${encodeURIComponent(query)}&type=${type}`;
  }
}

// Highlight search terms in results (optional enhancement)
document.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const query = urlParams.get('q');
  
  if (query && query.length > 2) {
    highlightSearchTerms(query);
  }
});

function highlightSearchTerms(query) {
  const elements = document.querySelectorAll('.post-title, .post-content, .community-name, .community-description, .user-name');
  const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
  
  elements.forEach(element => {
    const originalText = element.textContent;
    const highlightedText = originalText.replace(regex, '<mark>$1</mark>');
    
    if (originalText !== highlightedText) {
      element.innerHTML = highlightedText;
    }
  });
}

function escapeRegex(string) {
  return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Add highlight styling
const style = document.createElement('style');
style.textContent = `
  mark {
    background: linear-gradient(135deg, var(--neon-blue), var(--neon-purple));
    color: white;
    padding: 2px 4px;
    border-radius: 4px;
    font-weight: 600;
  }
`;
document.head.appendChild(style);