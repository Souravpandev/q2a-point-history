<?php
/*
	Admin Module for Q2A Point History Plugin
	Developed by Qlassy Team
	Lead Developer: Sourav Pan

	File: qa-plugin/q2a-point-history/qa-point-history-admin.php
	Version: 1.0.0
	Description: Admin configuration module for the Q2A Point History plugin

	This program is free software. You can redistribute and modify it
	under the terms of the GNU General Public License.
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_point_history_admin
{
	/**
	 * Initialize database tables
	 */
	function init_queries($tableslc)
	{
		// Resolve fully-prefixed table names as Q2A provides prefixed names in $tableslc
		$point_history_table = qa_db_add_table_prefix('point_history');
		$settings_table = qa_db_add_table_prefix('point_history_settings');

		// If both tables already exist, return null (no initialization needed)
		if (in_array($point_history_table, $tableslc) && in_array($settings_table, $tableslc)) {
			return null;
		}

		$result = array();

		// Create point_history table if it doesn't exist
		if (!in_array($point_history_table, $tableslc)) {
			$result[] = 'CREATE TABLE IF NOT EXISTS ^point_history (
				id INT(11) NOT NULL AUTO_INCREMENT,
				userid INT(11) NOT NULL,
				activity_type VARCHAR(50) NOT NULL,
				points INT(11) NOT NULL DEFAULT 0,
				postid INT(11) DEFAULT NULL,
				description TEXT,
				created DATETIME NOT NULL,
				PRIMARY KEY (id),
				KEY userid (userid),
				KEY activity_type (activity_type),
				KEY created (created),
				KEY postid (postid)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
		}

		// Create point_history_settings table if it doesn't exist
		if (!in_array($settings_table, $tableslc)) {
			$result[] = 'CREATE TABLE IF NOT EXISTS ^point_history_settings (
				id INT(11) NOT NULL AUTO_INCREMENT,
				setting_key VARCHAR(100) NOT NULL,
				setting_value TEXT,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY setting_key (setting_key)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
		}

		// Return the queries if we have any
		return $result;
	}

	/**
	 * Allow this module to be used in admin template
	 */
	function allow_template($template)
	{
		return ($template == 'admin');
	}

	/**
	 * Set default options for the plugin
	 */
	function option_default($option)
	{
		switch ($option) {
			case 'point_history_enabled':
				return 1;
			case 'point_history_track_questions':
				return 1;
			case 'point_history_track_answers':
				return 1;
			case 'point_history_track_votes':
				return 1;
			case 'point_history_track_best_answers':
				return 1;
			case 'point_history_track_comments':
				return 1;
			case 'point_history_track_bonus':
				return 1;
			case 'point_history_max_logs_per_user':
				return 1000;
			case 'point_history_cleanup_days':
				return 365;
			case 'point_history_widget_enabled':
				return 1;
			case 'point_history_widget_limit':
				return 10;

			default:
				return null;
		}
	}

	/**
	 * Admin form for plugin configuration
	 */
	function admin_form(&$qa_content)
	{
		// Check if we need to fix the database structure
		$tableslc = qa_db_list_tables();
		$init_queries = $this->init_queries($tableslc);
		if (!empty($init_queries)) {
			foreach ($init_queries as $query) {
				qa_db_query_sub($query);
			}
		}
		
		// Process form submission
		$saved = false;
		if (qa_clicked('point_history_save')) {
			// Run database initialization to fix any table structure issues
			$tableslc = qa_db_list_tables();
			$init_queries = $this->init_queries($tableslc);
			if (!empty($init_queries)) {
				foreach ($init_queries as $query) {
					qa_db_query_sub($query);
				}
			}
			
			qa_opt('point_history_enabled', (bool)qa_post_text('point_history_enabled'));
			qa_opt('point_history_track_questions', (bool)qa_post_text('point_history_track_questions'));
			qa_opt('point_history_track_answers', (bool)qa_post_text('point_history_track_answers'));
			qa_opt('point_history_track_votes', (bool)qa_post_text('point_history_track_votes'));
			qa_opt('point_history_track_best_answers', (bool)qa_post_text('point_history_track_best_answers'));
			qa_opt('point_history_track_comments', (bool)qa_post_text('point_history_track_comments'));
			qa_opt('point_history_track_bonus', (bool)qa_post_text('point_history_track_bonus'));
			qa_opt('point_history_max_logs_per_user', (int)qa_post_text('point_history_max_logs_per_user'));
			qa_opt('point_history_cleanup_days', (int)qa_post_text('point_history_cleanup_days'));
			qa_opt('point_history_widget_enabled', (bool)qa_post_text('point_history_widget_enabled'));
			qa_opt('point_history_widget_limit', (int)qa_post_text('point_history_widget_limit'));

			$saved = true;
		}

		// Process cleanup action
		if (qa_clicked('point_history_cleanup')) {
			$this->cleanup_old_logs();
			$saved = true;
		}

		// Process reset action
		if (qa_clicked('point_history_reset')) {
			$this->reset_point_history();
			$saved = true;
		}

		// Process reinstall action
		if (qa_clicked('point_history_reinstall')) {
			$this->reinstall_plugin();
			$saved = true;
		}

		// Get current statistics
		$stats = $this->get_plugin_statistics();
		
		// Check database status
		$db_status = $this->check_database_status();

		// Prepare form
		$qa_content['title'] = 'Q2A Point History Plugin Settings';
		$qa_content['error'] = '';

		$qa_content['form'] = array(
			'tags' => 'method="post" action="' . qa_self_html() . '"',
			'style' => 'tall',
			'fields' => array(
				array(
					'label' => 'Plugin Status',
					'type' => 'checkbox',
					'tags' => 'name="point_history_enabled"',
					'value' => qa_opt('point_history_enabled'),
					'note' => 'Enable or disable the point history tracking plugin'
				),

				array(
					'type' => 'blank'
				),

				array(
					'label' => 'Tracking Options',
					'type' => 'static',
					'value' => '<strong>Select which activities to track:</strong>'
				),

				array(
					'label' => 'Track Questions',
					'type' => 'checkbox',
					'tags' => 'name="point_history_track_questions"',
					'value' => qa_opt('point_history_track_questions'),
					'note' => 'Track when users post questions'
				),

				array(
					'label' => 'Track Answers',
					'type' => 'checkbox',
					'tags' => 'name="point_history_track_answers"',
					'value' => qa_opt('point_history_track_answers'),
					'note' => 'Track when users post answers'
				),

				array(
					'label' => 'Track Comments',
					'type' => 'checkbox',
					'tags' => 'name="point_history_track_comments"',
					'value' => qa_opt('point_history_track_comments'),
					'note' => 'Track when users post comments'
				),

				array(
					'label' => 'Track Votes Given',
					'type' => 'checkbox',
					'tags' => 'name="point_history_track_votes"',
					'value' => qa_opt('point_history_track_votes'),
					'note' => 'Track when users vote on content'
				),

				array(
					'label' => 'Track Best Answers',
					'type' => 'checkbox',
					'tags' => 'name="point_history_track_best_answers"',
					'value' => qa_opt('point_history_track_best_answers'),
					'note' => 'Track when answers are selected as best'
				),

				array(
					'label' => 'Track Bonus Points',
					'type' => 'checkbox',
					'tags' => 'name="point_history_track_bonus"',
					'value' => qa_opt('point_history_track_bonus'),
					'note' => 'Track admin-assigned bonus points'
				),

				array(
					'type' => 'blank'
				),

				array(
					'label' => 'Widget Settings',
					'type' => 'static',
					'value' => '<strong>Widget display options:</strong>'
				),

				array(
					'label' => 'Enable Widget',
					'type' => 'checkbox',
					'tags' => 'name="point_history_widget_enabled"',
					'value' => qa_opt('point_history_widget_enabled'),
					'note' => 'Show point history widget on user profile pages'
				),

				array(
					'label' => 'Widget Display Limit',
					'type' => 'number',
					'tags' => 'name="point_history_widget_limit"',
					'value' => qa_opt('point_history_widget_limit'),
					'note' => 'Maximum number of activities to show in widget'
				),

				array(
					'type' => 'blank'
				),

				array(
					'label' => 'Admin Tools',
					'type' => 'static',
					'value' => '<strong>Administrative tools and access:</strong>'
				),

				array(
					'label' => 'Point History Viewer',
					'type' => 'static',
					'value' => '<a href="' . qa_path_html('admin-point-history') . '" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 600;">📊 View All Users Point History</a><br><small style="color: #656d76;">Click to open the admin point history viewer in a new tab</small>'
				),

				array(
					'type' => 'blank'
				),

				array(
					'label' => 'Performance Settings',
					'type' => 'static',
					'value' => '<strong>Performance and storage options:</strong>'
				),

				array(
					'label' => 'Max Logs Per User',
					'type' => 'number',
					'tags' => 'name="point_history_max_logs_per_user"',
					'value' => qa_opt('point_history_max_logs_per_user'),
					'note' => 'Maximum number of point history logs to keep per user (0 = unlimited)'
				),

				array(
					'label' => 'Cleanup Days',
					'type' => 'number',
					'tags' => 'name="point_history_cleanup_days"',
					'value' => qa_opt('point_history_cleanup_days'),
					'note' => 'Automatically remove logs older than this many days (0 = never)'
				),



				array(
					'type' => 'blank'
				),

				array(
					'label' => 'Database Status',
					'type' => 'static',
					'value' => '<strong>Database status:</strong><br>' .
						'point_history table: ' . $db_status['point_history'] . '<br>' .
						'point_history_settings table: ' . $db_status['point_history_settings'] . '<br>' .
						'Status: ' . $db_status['overall_status']
				),
				array(
					'label' => 'Plugin Statistics',
					'type' => 'static',
					'value' => '<strong>Current plugin statistics:</strong><br>' .
						'Total tracked activities: ' . number_format($stats['total_activities']) . '<br>' .
						'Total users with history: ' . number_format($stats['total_users']) . '<br>' .
						'Database size: ' . $stats['db_size'] . '<br>' .
						'Oldest log: ' . $stats['oldest_log']
				)
			),

			'buttons' => array(
				array(
					'tags' => 'name="point_history_save"',
					'label' => 'Save Settings',
					'value' => 'Save Changes'
				),
				array(
					'tags' => 'name="point_history_cleanup"',
					'label' => 'Cleanup Old Logs',
					'value' => 'Cleanup Old Logs',
					'note' => 'Remove logs older than the specified cleanup days'
				),
				array(
					'tags' => 'name="point_history_reset"',
					'label' => 'Reset All Data',
					'value' => 'Reset All Data',
					'note' => 'WARNING: This will permanently delete all point history data!'
				),
				array(
					'tags' => 'name="point_history_reinstall"',
					'label' => 'Reinstall Plugin',
					'value' => 'Reinstall Plugin',
					'note' => 'Recreate database tables and fix any structural issues'
				)
			)
		);

		if ($saved) {
			$qa_content['form']['ok'] = 'Settings saved successfully! Database has been updated.';
		}

		return $qa_content['form'];
	}

	/**
	 * Check database table status
	 */
	private function check_database_status()
	{
		$status = array(
			'point_history' => '❌ Not Found',
			'point_history_settings' => '❌ Not Found',
			'overall_status' => '❌ Tables Missing'
		);

		try {
			// Check point_history table
			$point_history_exists = qa_db_read_one_value(qa_db_query_sub(
				'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "qa_point_history"'
			));
			
			if ($point_history_exists) {
				$status['point_history'] = '✅ Exists';
			}

			// Check point_history_settings table
			$settings_exists = qa_db_read_one_value(qa_db_query_sub(
				'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "qa_point_history_settings"'
			));
			
			if ($settings_exists) {
				$status['point_history_settings'] = '✅ Exists';
			}

			// Set overall status
			if ($status['point_history'] === '✅ Exists' && $status['point_history_settings'] === '✅ Exists') {
				$status['overall_status'] = '✅ All Tables Present';
			} elseif ($status['point_history'] === '✅ Exists' || $status['point_history_settings'] === '✅ Exists') {
				$status['overall_status'] = '⚠️ Partial Tables Present';
			} else {
				$status['overall_status'] = '❌ Tables Missing';
			}

		} catch (Exception $e) {
			$status['overall_status'] = '❌ Error Checking Tables';
		}

		return $status;
	}

	/**
	 * Get plugin statistics
	 */
	private function get_plugin_statistics()
	{
		$stats = array(
			'total_activities' => 0,
			'total_users' => 0,
			'db_size' => '0 KB',
			'oldest_log' => 'N/A'
		);

		// Check if tables exist
		$table_exists = qa_db_read_one_value(qa_db_query_sub(
			'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "qa_point_history"'
		));

		if ($table_exists) {
			// Get total activities
			$stats['total_activities'] = qa_db_read_one_value(qa_db_query_sub(
				'SELECT COUNT(*) FROM ^point_history'
			));

			// Get total users
			$stats['total_users'] = qa_db_read_one_value(qa_db_query_sub(
				'SELECT COUNT(DISTINCT userid) FROM ^point_history'
			));

			// Get oldest log
			$oldest = qa_db_read_one_value(qa_db_query_sub(
				'SELECT MIN(created) FROM ^point_history'
			));
			if ($oldest) {
				$stats['oldest_log'] = date('Y-m-d H:i:s', strtotime($oldest));
			}

			// Get database size
			$db_size = qa_db_read_one_value(qa_db_query_sub(
				'SELECT ROUND(((data_length + index_length) / 1024), 2) AS "DB Size in KB" 
				 FROM information_schema.tables 
				 WHERE table_schema = DATABASE() AND table_name = "qa_point_history"'
			));
			if ($db_size) {
				$stats['db_size'] = $db_size . ' KB';
			}
		}

		return $stats;
	}

	/**
	 * Cleanup old logs
	 */
	private function cleanup_old_logs()
	{
		$cleanup_days = (int)qa_opt('point_history_cleanup_days');
		if ($cleanup_days <= 0) {
			return;
		}

		$cutoff_date = date('Y-m-d H:i:s', strtotime('-' . $cleanup_days . ' days'));
		
		qa_db_query_sub(
			'DELETE FROM ^point_history WHERE created < $',
			$cutoff_date
		);
	}

	/**
	 * Reset all point history data
	 */
	private function reset_point_history()
	{
		// Truncate the point_history table
		qa_db_query_sub('TRUNCATE TABLE ^point_history');
		
		// Truncate the point_history_settings table
		qa_db_query_sub('TRUNCATE TABLE ^point_history_settings');
	}

	/**
	 * Reinstall the plugin by recreating tables
	 */
	private function reinstall_plugin()
	{
		try {
			// Drop existing tables if they exist
			qa_db_query_sub('DROP TABLE IF EXISTS ^point_history');
			qa_db_query_sub('DROP TABLE IF EXISTS ^point_history_settings');
			
			// Recreate tables with proper structure
			qa_db_query_sub('CREATE TABLE ^point_history (
				id INT(11) NOT NULL AUTO_INCREMENT,
				userid INT(11) NOT NULL,
				activity_type VARCHAR(50) NOT NULL,
				points INT(11) NOT NULL DEFAULT 0,
				postid INT(11) DEFAULT NULL,
				description TEXT,
				created DATETIME NOT NULL,
				PRIMARY KEY (id),
				KEY userid (userid),
				KEY activity_type (activity_type),
				KEY created (created),
				KEY postid (postid)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
			
			qa_db_query_sub('CREATE TABLE ^point_history_settings (
				id INT(11) NOT NULL AUTO_INCREMENT,
				setting_key VARCHAR(100) NOT NULL,
				setting_value TEXT,
				updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id),
				UNIQUE KEY setting_key (setting_key)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
			
		} catch (Exception $e) {
			// Log error
			error_log('Q2A Point History Plugin Reinstall Error: ' . $e->getMessage());
		}
	}

}
