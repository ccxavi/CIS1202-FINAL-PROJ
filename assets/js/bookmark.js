document.addEventListener('DOMContentLoaded', function() {
    // Add click event listeners to collection items
    document.querySelectorAll('.collection-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const container = this.closest('.bookmark-container');
            const articleId = container.dataset.articleId;
            const collectionId = this.dataset.collectionId;
            
            // Debug log
            console.log('Bookmark Data:', {
                articleId,
                collectionId
            });
            
            if (!articleId || !collectionId) {
                alert('Missing required data for bookmarking');
                return;
            }
            
            addBookmark(articleId, collectionId, container);
        });
    });

    // Prevent article link click when interacting with bookmark
    document.querySelectorAll('.bookmark-container').forEach(container => {
        container.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });
});

function addBookmark(articleId, collectionId, container) {
    const formData = new URLSearchParams();
    formData.append('article_id', articleId);
    formData.append('collection_id', collectionId);

    fetch('../controllers/addBookmark.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString()
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data); // Debug log
        
        if (data.success) {
            // Change bookmark icon color to indicate success
            const bookmarkIcon = container.querySelector('.bookmark-icon');
            bookmarkIcon.style.color = '#007bff';
            
            // Show success message
            alert('Article bookmarked successfully!');
        } else {
            throw new Error(data.message || 'Failed to bookmark article');
        }
    })
    .catch(error => {
        console.error('Error details:', error);
        alert(`Failed to bookmark article: ${error.message}`);
    });
} 