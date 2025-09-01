/**
 * Admin Point History Page JavaScript
 * Handles user search, selection, and timeline display
 */

(function($) {
    'use strict';

    // Global variables
    let allUsers = [];
    let filteredUsers = [];

    // Initialize when document is ready
    $(document).ready(function() {
        initializeAdminPage();
    });

    /**
     * Initialize the admin page
     */
    function initializeAdminPage() {
        // Get all users for search functionality
        getAllUsers();
        
        // Bind event listeners
        bindEventListeners();
        
        // Initialize search functionality
        initializeSearch();
    }

    /**
     * Get all users for search functionality
     */
    function getAllUsers() {
        // Extract users from the dropdown options
        $('#user-select option').each(function() {
            const $option = $(this);
            const value = $option.val();
            if (value) {
                const text = $option.text();
                const match = text.match(/^(.+?) \((\d+) points\)$/);
                if (match) {
                    allUsers.push({
                        userid: value,
                        handle: match[1],
                        points: parseInt(match[2])
                    });
                }
            }
        });
        filteredUsers = [...allUsers];
    }

    /**
     * Bind event listeners
     */
    function bindEventListeners() {
        // User selection change
        $('#user-select').on('change', function() {
            const selectedUserId = $(this).val();
            if (selectedUserId) {
                loadUserPointHistory(selectedUserId);
            } else {
                showPlaceholder();
            }
        });

        // User search input
        $('#user-search').on('input', function() {
            const searchQuery = $(this).val().toLowerCase();
            filterUsers(searchQuery);
        });

        // Prevent form submission on enter
        $('#user-search').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });
    }

    /**
     * Initialize search functionality
     */
    function initializeSearch() {
        // Create a searchable dropdown
        $('#user-search').on('focus', function() {
            $(this).parent().addClass('focused');
        });

        $('#user-search').on('blur', function() {
            setTimeout(() => {
                $(this).parent().removeClass('focused');
            }, 200);
        });
    }

    /**
     * Filter users based on search query
     */
    function filterUsers(searchQuery) {
        if (!searchQuery) {
            filteredUsers = [...allUsers];
        } else {
            filteredUsers = allUsers.filter(user => 
                user.handle.toLowerCase().includes(searchQuery)
            );
        }
        
        updateUserDropdown();
    }

    /**
     * Update user dropdown with filtered results
     */
    function updateUserDropdown() {
        const $dropdown = $('#user-select');
        const currentValue = $dropdown.val();
        
        // Clear existing options (except the first placeholder)
        $dropdown.find('option:not(:first)').remove();
        
        // Add filtered users
        filteredUsers.forEach(user => {
            const $option = $('<option>')
                .val(user.userid)
                .text(`${user.handle} (${user.points} points)`);
            $dropdown.append($option);
        });
        
        // Restore selection if still valid
        if (currentValue && filteredUsers.some(u => u.userid === currentValue)) {
            $dropdown.val(currentValue);
        }
    }

    /**
     * Load user point history
     */
    function loadUserPointHistory(userId) {
        const $container = $('#qa-admin-timeline-container');
        
        // Show loading state
        $container.html(`
            <div class="qa-admin-loading">
                <div class="qa-loading-spinner"></div>
                <p>Loading point history...</p>
            </div>
        `);

        // Make AJAX request
        $.ajax({
            url: window.location.href,
            method: 'POST',
            data: {
                ajax_get_user_history: userId
            },
            dataType: 'json',
            success: function(response) {
                if (response.user && response.history) {
                    displayUserTimeline(response.user, response.history);
                } else {
                    showError('Failed to load user data');
                }
            },
            error: function() {
                showError('Failed to load point history. Please try again.');
            }
        });
    }

    /**
     * Display user timeline
     */
    function displayUserTimeline(user, history) {
        const $container = $('#qa-admin-timeline-container');
        
        if (!history || history.length === 0) {
            $container.html(`
                <div class="qa-admin-no-history">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <h3>No Point History Found</h3>
                    <p>This user has no point history recorded yet.</p>
                </div>
            `);
            return;
        }

        // Create timeline HTML
        let timelineHTML = `
            <div class="qa-admin-user-info">
                <div class="qa-admin-user-avatar">
                    ${getUserAvatarHTML(user)}
                </div>
                <div class="qa-admin-user-details">
                    <h2>${escapeHtml(user.handle)}</h2>
                    <p class="qa-admin-user-points">Total Points: ${user.points}</p>
                    <p class="qa-admin-user-level">Level: ${getUserLevelText(user.level)}</p>
                </div>
            </div>
            
            <ul class="timeline" id="qa-admin-timeline">`;

        // Add timeline items
        history.forEach((activity, index) => {
            const pointsClass = activity.points >= 0 ? 'positive' : 'negative';
            const pointsSign = activity.points >= 0 ? '+' : '';
            const status = getActivityStatus(activity.activity_type);
            
            timelineHTML += `
                <li class="timeline-item" data-status="${status}" data-index="${index}">
                    <div class="timeline-avatar">
                        ${getUserAvatarHTML(user)}
                    </div>
                    
                    <div class="timeline-header">
                        <span class="timeline-title">${escapeHtml(user.handle)}</span>
                        <span class="timeline-action">earned ${pointsSign}${activity.points} points</span>
                    </div>
                    
                    <div class="timeline-content">
                        ${escapeHtml(activity.description)}
                        <span class="timeline-date">${formatDate(activity.created)}</span>
                    </div>
                </li>`;
        });

        timelineHTML += '</ul>';
        
        $container.html(timelineHTML);
        
        // Add animation to timeline items
        animateTimelineItems();
    }

    	/**
	 * Get user avatar HTML
	 */
	function getUserAvatarHTML(user) {
		// Use the avatar HTML from the PHP response if available
		if (user.avatar && user.avatar.trim() !== '') {
			return user.avatar;
		}
		
		// Fallback to initials if no avatar
		const initial = user.handle.charAt(0).toUpperCase();
		return `<div class="avatar-initial">${initial}</div>`;
	}

    /**
     * Get activity status for timeline styling
     */
    function getActivityStatus(activityType) {
        const statuses = {
            'question_posted': 'opened',
            'answer_posted': 'commented',
            'comment_posted': 'commented',
            'vote_given': 'assigned',
            'best_answer': 'closed',
            'bonus_points': 'opened',
            'registration': 'opened'
        };
        
        return statuses[activityType] || 'opened';
    }

    /**
     * Get user level text
     */
    function getUserLevelText(level) {
        const levels = {
            0: 'Basic User',
            10: 'Editor',
            20: 'Moderator',
            100: 'Administrator',
            120: 'Super Administrator'
        };
        
        return levels[level] || 'Basic User';
    }

    /**
     * Format date
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 1) {
            return 'Yesterday';
        } else if (diffDays < 7) {
            return `${diffDays} days ago`;
        } else {
            return date.toLocaleDateString();
        }
    }

    /**
     * Animate timeline items
     */
    function animateTimelineItems() {
        $('.timeline-item').each(function(index) {
            const $item = $(this);
            $item.css({
                'opacity': '0',
                'transform': 'translateY(20px)',
                'animation-delay': (index * 0.1) + 's'
            });
            
            setTimeout(() => {
                $item.css({
                    'opacity': '1',
                    'transform': 'translateY(0)',
                    'animation': 'slideIn 0.4s ease forwards'
                });
            }, index * 100);
        });
    }

    /**
     * Show placeholder
     */
    function showPlaceholder() {
        const $container = $('#qa-admin-timeline-container');
        $container.html(`
            <div class="qa-admin-placeholder">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <h3>Select a user to view their point history</h3>
                <p>Choose a user from the dropdown above to see their complete timeline</p>
            </div>
        `);
    }

    /**
     * Show error message
     */
    function showError(message) {
        const $container = $('#qa-admin-timeline-container');
        $container.html(`
            <div class="qa-admin-error">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <h3>Error</h3>
                <p>${escapeHtml(message)}</p>
            </div>
        `);
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

})(jQuery);
