<?php
/*
	Admin Page Module for Q2A Point History Plugin
	Developed by Qlassy Team
	Lead Developer: Sourav Pan
	
	File: qa-plugin/q2a-point-history/qa-point-history-admin-page.php
	Version: 1.0.0
	Description: Admin page module for viewing any user's point history

	This program is free software. You can redistribute and modify it
	under the terms of the GNU General Public License.
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

// Define user level constants if not already defined
if (!defined('QA_USER_LEVEL_ADMIN')) {
	define('QA_USER_LEVEL_ADMIN', 100);
}

class qa_point_history_admin_page
{
	private $directory;
	private $urltoroot;

	public function load_module($directory, $urltoroot)
	{
		$this->directory = $directory;
		$this->urltoroot = $urltoroot;
	}

	/**
	 * Suggest the request for this page
	 */
	public function suggest_requests()
	{
		return array(
			array(
				'title' => 'Admin Point History',
				'request' => 'admin-point-history',
				'nav' => 'A', // Admin navigation
			),
		);
	}

	/**
	 * Check if this request matches our page
	 */
	public function match_request($request)
	{
		return $request == 'admin-point-history';
	}

	/**
	 * Process the page request
	 */
	public function process_request($request)
	{
		// Check if user is admin
		if (!qa_is_logged_in() || qa_get_logged_in_level() < QA_USER_LEVEL_ADMIN) {
			$qa_content = qa_content_prepare();
			$qa_content['error'] = 'Access denied. Admin privileges required.';
			return $qa_content;
		}

		// Handle AJAX user search
		$search_query = qa_post_text('ajax_search_users');
		if ($search_query !== null) {
			$this->handle_ajax_search_users($search_query);
			return;
		}

		// Handle AJAX get user point history
		$selected_userid = qa_post_text('ajax_get_user_history');
		if ($selected_userid !== null) {
			$this->handle_ajax_get_user_history($selected_userid);
			return;
		}

		// Display the main page
		return $this->display_admin_page();
	}

	/**
	 * Display the admin point history page
	 */
	private function display_admin_page()
	{
		$qa_content = qa_content_prepare();
		$qa_content['title'] = 'Admin Point History';
		$qa_content['template'] = 'admin-point-history';
		
		// Add CSS and JS
		$qa_content['css_src'][] = $this->urltoroot . 'css/point-history.min.css?v=' . QA_VERSION;
		$qa_content['script_src'][] = $this->urltoroot . 'js/point-history-admin.min.js?v=' . QA_VERSION;

		// Get all users for dropdown
		$users = $this->get_all_users();

		$qa_content['custom'] = '
		<div class="qa-admin-point-history-page">
			<div class="qa-admin-controls">
				<div class="qa-user-selector">
					<label for="user-select">Select User:</label>
					<div class="qa-search-container">
						<input type="text" id="user-search" placeholder="Search users..." class="qa-user-search">
						<select id="user-select" class="qa-user-dropdown">
							<option value="">Choose a user...</option>';

		foreach ($users as $user) {
			$qa_content['custom'] .= '<option value="' . $user['userid'] . '">' . qa_html($user['handle']) . ' (' . qa_html($user['points']) . ' points)</option>';
		}

		$qa_content['custom'] .= '
						</select>
					</div>
				</div>
			</div>

			<div class="qa-admin-content">
				<div id="qa-admin-timeline-container" class="qa-admin-timeline-container">
					<div class="qa-admin-placeholder">
						<svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
						</svg>
						<h3>Select a user to view their point history</h3>
						<p>Choose a user from the dropdown above to see their complete timeline</p>
					</div>
				</div>
			</div>
		</div>';

		return $qa_content;
	}

	/**
	 * Handle AJAX user search
	 */
	private function handle_ajax_search_users($search_query)
	{
		// Check admin privileges again for AJAX requests
		if (!qa_is_logged_in() || qa_get_logged_in_level() < QA_USER_LEVEL_ADMIN) {
			http_response_code(403);
			echo json_encode(array('error' => 'Access denied'));
			exit;
		}
		
		$users = $this->search_users($search_query);
		
		$response = array();
		foreach ($users as $user) {
			$response[] = array(
				'userid' => $user['userid'],
				'handle' => $user['handle'],
				'points' => $user['points'],
				'avatar' => $this->get_user_avatar_html_small($user)
			);
		}
		
		echo json_encode($response);
		exit;
	}

	/**
	 * Handle AJAX get user point history
	 */
	private function handle_ajax_get_user_history($userid)
	{
		// Check admin privileges again for AJAX requests
		if (!qa_is_logged_in() || qa_get_logged_in_level() < QA_USER_LEVEL_ADMIN) {
			http_response_code(403);
			echo json_encode(array('error' => 'Access denied'));
			exit;
		}
		
		$point_history = $this->get_user_point_history($userid);
		$user_info = $this->get_user_info($userid);
		
		// Add avatar HTML to user info
		$user_info['avatar'] = $this->get_user_avatar_html($user_info);
		
		$response = array(
			'user' => $user_info,
			'history' => $point_history
		);
		
		echo json_encode($response);
		exit;
	}

	/**
	 * Get all users for dropdown
	 */
	private function get_all_users()
	{
		$query = qa_db_query_sub(
			'SELECT u.userid, u.handle, COALESCE(up.points, 0) as points FROM ^users u LEFT JOIN ^userpoints up ON u.userid = up.userid WHERE u.level>=0 ORDER BY u.handle ASC LIMIT 1000'
		);
		
		$users = array();
		while (($row = qa_db_read_one_assoc($query, true)) !== null) {
			$users[] = $row;
		}
		
		return $users;
	}

	/**
	 * Search users by handle
	 */
	private function search_users($search_query)
	{
		$query = qa_db_query_sub(
			'SELECT u.userid, u.handle, COALESCE(up.points, 0) as points FROM ^users u LEFT JOIN ^userpoints up ON u.userid = up.userid WHERE u.handle LIKE # AND u.level>=0 ORDER BY u.handle ASC LIMIT 50',
			'%' . $search_query . '%'
		);
		
		$users = array();
		while (($row = qa_db_read_one_assoc($query, true)) !== null) {
			$users[] = $row;
		}
		
		return $users;
	}

	/**
	 * Get user point history
	 */
	private function get_user_point_history($userid)
	{
		// Check if point_history table exists
		$table_exists = qa_db_read_one_value(qa_db_query_sub(
			'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "qa_point_history"'
		));

		if (!$table_exists) {
			return array();
		}

		// Get point history from database
		$query = qa_db_query_sub(
			'SELECT activity_type, points, postid, description, created 
			 FROM ^point_history 
			 WHERE userid = # 
			 ORDER BY created DESC',
			$userid
		);

		$results = array();
		while (($row = qa_db_read_one_assoc($query, true)) !== null) {
			$results[] = $row;
		}

		return $results;
	}

	/**
	 * Get user info
	 */
	private function get_user_info($userid)
	{
		$query = qa_db_query_sub(
			'SELECT u.userid, u.handle, COALESCE(up.points, 0) as points, u.level, u.flags, u.email, u.avatarblobid, u.avatarwidth, u.avatarheight FROM ^users u LEFT JOIN ^userpoints up ON u.userid = up.userid WHERE u.userid=#',
			$userid
		);
		
		return qa_db_read_one_assoc($query);
	}

	/**
	 * Get user avatar HTML for main user info (64px)
	 */
	private function get_user_avatar_html($user)
	{
		return $this->get_user_avatar_html_by_size($user, 64);
	}
	
	/**
	 * Get user avatar HTML for dropdown/search (24px)
	 */
	private function get_user_avatar_html_small($user)
	{
		return $this->get_user_avatar_html_by_size($user, 24);
	}
	
	/**
	 * Get user avatar HTML with specified size
	 */
	private function get_user_avatar_html_by_size($user, $size)
	{
		try {
			$user_query = qa_db_query_sub(
				'SELECT flags, email, avatarblobid, avatarwidth, avatarheight FROM ^users WHERE userid=#',
				$user['userid']
			);
			$user_data = qa_db_read_one_assoc($user_query);
			
			if ($user_data && isset($user_data['flags']) && isset($user_data['email'])) {
				$avatar_html = qa_get_user_avatar_html(
					(int)$user_data['flags'],
					$user_data['email'],
					$user['handle'],
					$user_data['avatarblobid'],
					(int)$user_data['avatarwidth'],
					(int)$user_data['avatarheight'],
					$size, // size parameter
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
		$initial = strtoupper(substr($user['handle'], 0, 1));
		$initial_size = $size == 64 ? 24 : 18; // Font size based on avatar size
		return '<div class="avatar-initial" style="width: ' . $size . 'px; height: ' . $size . 'px; font-size: ' . $initial_size . 'px;">' . $initial . '</div>';
	}
}
