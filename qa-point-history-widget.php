<?php
/*
	Widget Module for Q2A Point History Plugin
	Developed by Qlassy Team
	Lead Developer: Sourav Pan
	
	File: qa-plugin/q2a-point-history/qa-point-history-widget.php
	Version: 1.0.0
	Description: Widget display module for the Q2A Point History plugin

	This program is free software. You can redistribute and modify it
	under the terms of the GNU General Public License.
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_point_history_widget
{
	/**
	 * Check if widget can be displayed on the specified template
	 */
	function allow_template($template)
	{
		// Allow on all templates for maximum visibility
		return true;
	}

	/**
	 * Check if widget can be displayed in the specified region
	 */
	function allow_region($region)
	{
		// Allow in all regions for maximum visibility
		return true;
	}

	/**
	 * Output the widget HTML
	 */
	function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
	{
		try {
			// Show for all logged-in users on all pages
			if (!qa_is_logged_in()) {
				return;
			}

		// Get current user ID
		$current_userid = qa_get_logged_in_userid();
		if (!$current_userid) {
			return;
		}

		// Check if widget is enabled in admin settings
		if (!qa_opt('point_history_widget_enabled')) {
			return;
		}

		// Get point history data for current user
		$widget_limit = (int)qa_opt('point_history_widget_limit');
		$point_history = $this->get_point_history($current_userid, $widget_limit);
		
		// Get current user handle for the link
		$current_handle = qa_get_logged_in_handle();
		
		// Output the widget
		$themeobject->output('<div class="qa-point-history-widget">');
		$themeobject->output('<h3 class="qa-point-history-title">My Point History</h3>');
		
		if (empty($point_history)) {
			$themeobject->output('<div class="qa-point-history-empty">No point history yet. Start participating to earn points!</div>');
		} else {
			$themeobject->output('<ul class="timeline" id="qa-point-timeline">');
			
			foreach ($point_history as $index => $activity) {
				$points_class = $activity['points'] >= 0 ? 'positive' : 'negative';
				$points_sign = $activity['points'] >= 0 ? '+' : '';
				$status = $this->get_activity_status($activity['activity_type']);
				$is_hidden = ($index >= 2) ? ' style="display: none;"' : '';
				
				// Get user avatar
				$user_avatar = $this->get_user_avatar($current_userid);
				
				$themeobject->output('<li class="timeline-item" data-status="' . $status . '"' . $is_hidden . ' data-index="' . $index . '">');
				
				// Avatar section
				$themeobject->output('<div class="timeline-avatar">');
				$themeobject->output($user_avatar);
				$themeobject->output('</div>');
				
				// Header section - now includes all content in one line
				$themeobject->output('<div class="timeline-header">');
				$themeobject->output('<span class="timeline-title">' . qa_html(qa_get_logged_in_handle()) . '</span>');
				$themeobject->output('<span class="timeline-action">earned ' . $points_sign . qa_html($activity['points']) . ' points for ' . qa_html($activity['description']) . ' - ' . qa_html($this->format_date($activity['created'])) . '</span>');
				$themeobject->output('</div>');
				
				$themeobject->output('</li>');
			}
			
			$themeobject->output('</ul>');
		}
		
		// Add show more/less functionality if there are more items
		if (count($point_history) > 2) {
			$themeobject->output('<div class="qa-timeline-show-more">');
			$themeobject->output('<button class="qa-timeline-show-more-btn" onclick="qaTimelineToggle()" data-expanded="false">');
			$themeobject->output('<svg class="qa-timeline-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">');
			$themeobject->output('<path d="M7 10L12 15L17 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>');
			$themeobject->output('</svg>');
			$themeobject->output('</button>');
			$themeobject->output('</div>');
			
			// Add JavaScript for show more/less functionality
			$themeobject->output('<script>');
			$themeobject->output('function qaTimelineToggle() {');
			$themeobject->output('    const button = document.querySelector(".qa-timeline-show-more-btn");');
			$themeobject->output('    const isExpanded = button.getAttribute("data-expanded") === "true";');
			$themeobject->output('    const timeline = document.querySelector(".timeline");');
			$themeobject->output('    ');
			$themeobject->output('    if (!isExpanded) {');
			$themeobject->output('        // Expand: show all items');
			$themeobject->output('        const hiddenItems = document.querySelectorAll(".timeline-item[style*=\'display: none\']");');
			$themeobject->output('        hiddenItems.forEach((item, index) => {');
			$themeobject->output('            item.style.display = "block";');
			$themeobject->output('            item.style.animation = "slideIn 0.4s ease forwards";');
			$themeobject->output('        });');
			$themeobject->output('        ');
			$themeobject->output('        // Remove fade effect and rotate icon');
			$themeobject->output('        if (timeline) {');
			$themeobject->output('            timeline.style.maskImage = "none";');
			$themeobject->output('            timeline.style.webkitMaskImage = "none";');
			$themeobject->output('        }');
			$themeobject->output('        button.setAttribute("data-expanded", "true");');
			$themeobject->output('        button.querySelector(".qa-timeline-arrow").style.transform = "rotate(180deg)";');
			$themeobject->output('    } else {');
			$themeobject->output('        // Collapse: hide items beyond first 2');
			$themeobject->output('        const allItems = document.querySelectorAll(".timeline-item");');
			$themeobject->output('        allItems.forEach((item, index) => {');
			$themeobject->output('            if (index >= 2) {');
			$themeobject->output('                item.style.display = "none";');
			$themeobject->output('            }');
			$themeobject->output('        });');
			$themeobject->output('        ');
			$themeobject->output('        // Restore fade effect and rotate icon back');
			$themeobject->output('        if (timeline) {');
			$themeobject->output('            timeline.style.maskImage = "linear-gradient(to bottom, black, black 4%, transparent)";');
			$themeobject->output('            timeline.style.webkitMaskImage = "linear-gradient(to bottom, black, black 4%, transparent)";');
			$themeobject->output('        }');
			$themeobject->output('        button.setAttribute("data-expanded", "false");');
			$themeobject->output('        button.querySelector(".qa-timeline-arrow").style.transform = "rotate(0deg)";');
			$themeobject->output('    }');
			$themeobject->output('}');
			$themeobject->output('</script>');
		}
		
		$themeobject->output('</div>');
		
		} catch (Exception $e) {
			// If there's any error, don't output anything to prevent breaking other widgets
			// You can log the error if needed: error_log('Point History Widget Error: ' . $e->getMessage());
			return;
		}
	}

	/**
	 * Get point history for a user
	 */
	private function get_point_history($userid, $limit = 10)
	{
		try {
			// Check if point_history table exists
			$table_exists = qa_db_read_one_value(qa_db_query_sub(
				'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "qa_point_history"'
			));

			if (!$table_exists) {
				return array();
			}

			// Get point history from database
			$results = qa_db_read_all_assoc(qa_db_query_sub(
				'SELECT activity_type, points, postid, description, created 
				 FROM ^point_history 
				 WHERE userid = # 
				 ORDER BY created DESC 
				 LIMIT #',
				$userid, $limit
			));

			return $results;
			
		} catch (Exception $e) {
			// If there's any database error, return empty array
			return array();
		}
	}

	/**
	 * Get user avatar HTML using Q2A built-in functions
	 */
	private function get_user_avatar($userid)
	{
		try {
			// Get user data from Q2A database
			$user_query = qa_db_query_sub(
				'SELECT flags, email, avatarblobid, avatarwidth, avatarheight, handle FROM ^users WHERE userid=#',
				$userid
			);
			$user_data = qa_db_read_one_assoc($user_query);
			
			if ($user_data && isset($user_data['flags']) && isset($user_data['email'])) {
				// Use Q2A's built-in avatar function with all required parameters
				$avatar_html = qa_get_user_avatar_html(
					(int)$user_data['flags'],
					$user_data['email'],
					$user_data['handle'],
					$user_data['avatarblobid'],
					(int)$user_data['avatarwidth'],
					(int)$user_data['avatarheight'],
					24, // size - match the CSS timeline-avatar size
					false // padding
				);
				
				if ($avatar_html) {
					// Remove the link wrapper and return just the image
					$avatar_html = preg_replace('/<a[^>]*>(.*?)<\/a>/', '$1', $avatar_html);
					return $avatar_html;
				}
			}
		} catch (Exception $e) {
			// If there's any error, fall back to initials
		}
		
		// Fallback to initials if no avatar or error
		$handle = qa_get_logged_in_handle();
		$initial = strtoupper(substr($handle, 0, 1));
		
		return '<div class="avatar-initial">' . $initial . '</div>';
	}

	/**
	 * Get activity status for timeline styling
	 */
	private function get_activity_status($activity_type)
	{
		$statuses = array(
			'question_posted' => 'opened',
			'answer_posted' => 'commented', 
			'comment_posted' => 'commented',
			'vote_given' => 'assigned',
			'best_answer' => 'closed',
			'bonus_points' => 'opened',
			'registration' => 'opened'
		);
		
		return isset($statuses[$activity_type]) ? $statuses[$activity_type] : 'opened';
	}

	/**
	 * Format date using Q2A's built-in date formatting
	 */
	private function format_date($timestamp)
	{
		// Ensure timestamp is an integer
		if (is_string($timestamp)) {
			$timestamp = strtotime($timestamp);
		}
		
		if (!$timestamp || !is_numeric($timestamp)) {
			return 'Unknown date';
		}
		
		// Use Q2A's built-in date formatting function
		$date_info = qa_when_to_html((int)$timestamp, 7); // Show full date after 7 days
		
		if (isset($date_info['data'])) {
			return $date_info['data'];
		}
		
		// Fallback to simple date format
		return date('M d, Y', (int)$timestamp);
	}
}
