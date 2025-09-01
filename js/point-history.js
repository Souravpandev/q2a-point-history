/**
 * Q2A Point History Plugin JavaScript
 * Handles export functionality and interactive features
 */

(function() {
    'use strict';

    // Export point history data
    window.exportPointHistory = function(format) {
        const userhandle = getCurrentUserHandle();
        if (!userhandle) {
            alert('Unable to determine user handle for export.');
            return;
        }

        // Show loading state
        const exportButtons = document.querySelectorAll('.qa-point-export-csv, .qa-point-export-json');
        exportButtons.forEach(btn => {
            btn.disabled = true;
            btn.textContent = 'Exporting...';
        });

        // Fetch point history data
        fetchPointHistoryData(userhandle, format)
            .then(data => {
                if (format === 'csv') {
                    exportAsCSV(data);
                } else if (format === 'json') {
                    exportAsJSON(data);
                }
            })
            .catch(error => {
                console.error('Export failed:', error);
                alert('Export failed. Please try again.');
            })
            .finally(() => {
                // Reset button states
                exportButtons.forEach(btn => {
                    btn.disabled = false;
                    if (btn.classList.contains('qa-point-export-csv')) {
                        btn.textContent = 'Export as CSV';
                    } else {
                        btn.textContent = 'Export as JSON';
                    }
                });
            });
    };

    // Get current user handle from URL
    function getCurrentUserHandle() {
        const pathParts = window.location.pathname.split('/');
        const pointHistoryIndex = pathParts.indexOf('point-history');
        if (pointHistoryIndex !== -1 && pathParts[pointHistoryIndex + 1]) {
            return pathParts[pointHistoryIndex + 1];
        }
        return null;
    }

    // Fetch point history data from the server
    async function fetchPointHistoryData(userhandle, format) {
        const response = await fetch(`/qa-plugin/q2a-point-history/export.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `userhandle=${encodeURIComponent(userhandle)}&format=${format}&action=export`
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    // Export data as CSV
    function exportAsCSV(data) {
        if (!data.activities || data.activities.length === 0) {
            alert('No data to export.');
            return;
        }

        // Create CSV content
        const headers = ['Date', 'Activity', 'Points', 'Post ID', 'Description'];
        const csvContent = [
            headers.join(','),
            ...data.activities.map(activity => [
                formatDate(activity.created),
                activity.activity_type,
                activity.points,
                activity.postid || '',
                `"${activity.description.replace(/"/g, '""')}"`
            ].join(','))
        ].join('\n');

        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `point-history-${data.userhandle}-${formatDate(new Date())}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Export data as JSON
    function exportAsJSON(data) {
        if (!data.activities || data.activities.length === 0) {
            alert('No data to export.');
            return;
        }

        // Create JSON content
        const jsonContent = JSON.stringify(data, null, 2);
        
        // Create and download file
        const blob = new Blob([jsonContent], { type: 'application/json;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `point-history-${data.userhandle}-${formatDate(new Date())}.json`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Format date for export
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    }

    // Initialize plugin when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializePointHistoryPlugin();
    });

    // Initialize plugin functionality
    function initializePointHistoryPlugin() {
        // Add smooth scrolling to pagination links
        const paginationLinks = document.querySelectorAll('.qa-pagination-page, .qa-pagination-prev, .qa-pagination-next');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // Smooth scroll to top of page
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });

        // Add hover effects to activity items
        const activityItems = document.querySelectorAll('.qa-point-history-activity-item');
        activityItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(4px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });

        // Add click tracking for analytics (if enabled)
        if (typeof qa_ga !== 'undefined') {
            trackPluginUsage();
        }
    }

    // Track plugin usage for analytics
    function trackPluginUsage() {
        const trackEvent = function(category, action, label) {
            if (typeof qa_ga !== 'undefined' && qa_ga.trackEvent) {
                qa_ga.trackEvent(category, action, label);
            }
        };

        // Track export usage
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('qa-point-export-csv')) {
                trackEvent('Point History', 'Export', 'CSV');
            } else if (e.target.classList.contains('qa-point-export-json')) {
                trackEvent('Point History', 'Export', 'JSON');
            }
        });

        // Track pagination usage
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('qa-pagination-page') || 
                e.target.classList.contains('qa-pagination-prev') || 
                e.target.classList.contains('qa-pagination-next')) {
                trackEvent('Point History', 'Navigation', 'Pagination');
            }
        });
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + E for export menu
        if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
            e.preventDefault();
            const exportSection = document.querySelector('.qa-point-history-export');
            if (exportSection) {
                exportSection.scrollIntoView({ behavior: 'smooth' });
                exportSection.style.outline = '2px solid #3498db';
                setTimeout(() => {
                    exportSection.style.outline = 'none';
                }, 2000);
            }
        }
    });

    // Add tooltips for better UX
    function addTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', showTooltip);
            element.addEventListener('mouseleave', hideTooltip);
        });
    }

    function showTooltip(e) {
        const tooltip = document.createElement('div');
        tooltip.className = 'qa-tooltip';
        tooltip.textContent = e.target.getAttribute('data-tooltip');
        tooltip.style.cssText = `
            position: absolute;
            background: #2c3e50;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            z-index: 1000;
            pointer-events: none;
            white-space: nowrap;
        `;
        
        document.body.appendChild(tooltip);
        
        const rect = e.target.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
        
        e.target._tooltip = tooltip;
    }

    function hideTooltip(e) {
        if (e.target._tooltip) {
            e.target._tooltip.remove();
            e.target._tooltip = null;
        }
    }

    // Initialize tooltips when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addTooltips);
    } else {
        addTooltips();
    }

})();
