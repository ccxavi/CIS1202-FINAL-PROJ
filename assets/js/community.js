document.addEventListener('DOMContentLoaded', function() {
    // Get the current sort parameter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sort') || 'recent';
    
    // Store open comment sections in session storage
    const openCommentSections = JSON.parse(sessionStorage.getItem('openCommentSections') || '[]');
    
    // Open comment sections that were previously open
    openCommentSections.forEach(postId => {
        const commentSection = document.getElementById(`comments-${postId}`);
        const toggle = document.querySelector(`.comment-toggle[data-post-id="${postId}"]`);
        const currentPost = document.getElementById(`post-${postId}`);
        
        if (commentSection && toggle && currentPost) {
            commentSection.style.display = 'block';
            toggle.classList.add('active');
            currentPost.classList.add('has-comments');
            currentPost.style.marginBottom = '0';
            
            // Adjust position of next posts
            let nextElement = commentSection.nextElementSibling;
            if (nextElement) {
                nextElement.style.marginTop = '20px';
            }
        }
    });
    
    // Toggle comment section
    const commentToggles = document.querySelectorAll('.comment-toggle');
    commentToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const commentSection = document.getElementById(`comments-${postId}`);
            const currentPost = document.getElementById(`post-${postId}`);
            
            // Toggle comment section
            if (commentSection.style.display === 'none') {
                // Show comments
                commentSection.style.display = 'block';
                
                // Add active styles
                this.classList.add('active');
                currentPost.classList.add('has-comments');
                
                // Adjust post margins
                currentPost.style.marginBottom = '0';
                
                // Adjust the position of the next posts
                let nextElement = commentSection.nextElementSibling;
                if (nextElement) {
                    nextElement.style.marginTop = '20px';
                }
                
                // Save open state in session storage
                if (!openCommentSections.includes(postId)) {
                    openCommentSections.push(postId);
                    sessionStorage.setItem('openCommentSections', JSON.stringify(openCommentSections));
                }
            } else {
                // Hide comments
                commentSection.style.display = 'none';
                
                // Remove active styles
                this.classList.remove('active');
                currentPost.classList.remove('has-comments');
                
                // Reset margins
                currentPost.style.marginBottom = '1.5rem';
                
                // Reset next post margin
                let nextElement = commentSection.nextElementSibling;
                if (nextElement) {
                    nextElement.style.marginTop = '1.5rem';
                }
                
                // Remove from session storage
                const index = openCommentSections.indexOf(postId);
                if (index > -1) {
                    openCommentSections.splice(index, 1);
                    sessionStorage.setItem('openCommentSections', JSON.stringify(openCommentSections));
                }
            }
        });
    });
    
    // Toggle reply form and replies
    const replyToggles = document.querySelectorAll('.reply-toggle');
    replyToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            const repliesContainer = document.getElementById(`replies-${commentId}`);
            
            // Toggle reply form
            if (replyForm.style.display === 'none') {
                replyForm.style.display = 'block';
                this.classList.add('active');
                
                // Show replies if any
                if (repliesContainer) {
                    repliesContainer.style.display = 'block';
                }
            } else {
                replyForm.style.display = 'none';
                this.classList.remove('active');
                
                // Hide replies
                if (repliesContainer) {
                    repliesContainer.style.display = 'none';
                }
            }
        });
    });
    
    // Fix for post likes
    document.querySelectorAll('.post-actions form button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const postId = form.querySelector('input[name="like_post_id"]').value;
            const countElement = this.childNodes[this.childNodes.length - 1]; // Get the text node
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('ajax_action', 'like_post');
            formData.append('post_id', postId);
            
            fetch('../controllers/CommunityController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the like count
                    countElement.textContent = data.likes;
                    
                    // Toggle active class
                    if (data.is_liked) {
                        this.classList.add('liked');
                    } else {
                        this.classList.remove('liked');
                    }
                } else {
                    console.error('Error liking post:', data.error);
                    if (data.error === 'User not authenticated') {
                        window.location.href = '../views/loginRegister.php';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
    // Handle comment likes via AJAX
    document.querySelectorAll('.comment-like-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const commentId = this.getAttribute('data-comment-id');
            const countElement = this.querySelector('.like-count');
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('ajax_action', 'like_comment');
            formData.append('comment_id', commentId);
            
            fetch('../controllers/CommunityController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the like count
                    countElement.textContent = data.likes;
                    
                    // Toggle active class
                    if (data.is_liked) {
                        this.classList.add('liked');
                    } else {
                        this.classList.remove('liked');
                    }
                } else {
                    console.error('Error liking comment:', data.error);
                    if (data.error === 'User not authenticated') {
                        window.location.href = '../views/loginRegister.php';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
    
    // Handle comment submission via AJAX
    document.querySelectorAll('.comment-form, .reply-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const postId = this.querySelector('input[name="comment_post_id"]').value;
            const content = this.querySelector('textarea[name="comment_content"]').value;
            const parentId = this.querySelector('input[name="comment_parent_id"]')?.value || null;
            const textarea = this.querySelector('textarea');
            
            if (!content.trim()) {
                return; // Don't submit empty comments
            }
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('ajax_action', 'add_comment');
            formData.append('post_id', postId);
            formData.append('content', content);
            if (parentId) {
                formData.append('parent_id', parentId);
            }
            
            fetch('../controllers/CommunityController.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the textarea
                    textarea.value = '';
                    
                    // Store the current scroll position
                    const scrollPosition = window.scrollY;
                    
                    // Reload to show the new comment but preserve sort parameter
                    const currentUrl = new URL(window.location.href);
                    if (currentUrl.searchParams.has('sort')) {
                        location.reload();
                    } else {
                        window.location.href = `${window.location.pathname}?sort=${currentSort}`;
                    }
                    
                    // After reload, restore scroll position
                    window.addEventListener('load', function() {
                        window.scrollTo(0, scrollPosition);
                    });
                } else {
                    console.error('Error adding comment:', data.error);
                    if (data.error === 'User not authenticated') {
                        window.location.href = '../views/loginRegister.php';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    });
}); 