<?php
/*
	Event Module for Q2A Point History Plugin
	Developed by Qlassy Team
	Lead Developer: Sourav Pan
	
	File: qa-plugin/q2a-point-history/qa-point-history-event.php
	Version: 1.0.0
	Description: Event tracking module for the Q2A Point History plugin

	This program is free software. You can redistribute and modify it
	under the terms of the GNU General Public License.
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_point_history_event
{
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
			default:
				return null;
		}
	}

	/**
	 * Process events to track point-earning activities
	 */
	function process_event($event, $userid, $handle, $cookieid, $params)
	{
		// Only process if plugin is enabled and user is logged in
		if (!qa_opt('point_history_enabled') || !$userid) {
			return;
		}

		// Track different types of events
		switch ($event) {
			case 'q_post':
				if (qa_opt('point_history_track_questions')) {
					$this->log_point_activity($userid, 'question_posted', qa_opt('points_post_q'), $params['postid'], 'Posted a question');
				}
				break;

			case 'a_post':
				if (qa_opt('point_history_track_answers')) {
					$this->log_point_activity($userid, 'answer_posted', qa_opt('points_post_a'), $params['postid'], 'Posted an answer');
				}
				break;

			case 'c_post':
				if (qa_opt('point_history_track_comments')) {
					$this->log_point_activity($userid, 'comment_posted', 0, $params['postid'], 'Posted a comment');
				}
				break;

			case 'q_vote_up':
				if (qa_opt('point_history_track_votes')) {
					$this->log_point_activity($userid, 'question_voted_up', qa_opt('points_vote_up_q'), $params['postid'], 'Voted up a question');
				}
				break;

			case 'q_vote_down':
				if (qa_opt('point_history_track_votes')) {
					$this->log_point_activity($userid, 'question_voted_down', qa_opt('points_vote_down_q'), $params['postid'], 'Voted down a question');
				}
				break;

			case 'a_vote_up':
				if (qa_opt('point_history_track_votes')) {
					$this->log_point_activity($userid, 'answer_voted_up', qa_opt('points_vote_up_a'), $params['postid'], 'Voted up an answer');
				}
				break;

			case 'a_vote_down':
				if (qa_opt('point_history_track_votes')) {
					$this->log_point_activity($userid, 'answer_voted_down', qa_opt('points_vote_down_a'), $params['postid'], 'Voted down an answer');
				}
				break;

			case 'c_vote_up':
				if (qa_opt('point_history_track_votes')) {
					$this->log_point_activity($userid, 'comment_voted_up', qa_opt('points_vote_up_c'), $params['postid'], 'Voted up a comment');
				}
				break;

			case 'c_vote_down':
				if (qa_opt('point_history_track_votes')) {
					$this->log_point_activity($userid, 'comment_voted_down', qa_opt('points_vote_down_c'), $params['postid'], 'Voted down a comment');
				}
				break;

			case 'a_select':
				if (qa_opt('point_history_track_best_answers')) {
					// Get the answer author's userid
					$answer_userid = qa_db_read_one_value(qa_db_query_sub(
						'SELECT userid FROM ^posts WHERE postid = #',
						$params['postid']
					));
					if ($answer_userid) {
						$this->log_point_activity($answer_userid, 'answer_selected', qa_opt('points_a_selected'), $params['postid'], 'Answer selected as best');
					}
				}
				break;

			case 'a_unselect':
				if (qa_opt('point_history_track_best_answers')) {
					// Get the answer author's userid
					$answer_userid = qa_db_read_one_value(qa_db_query_sub(
						'SELECT userid FROM ^posts WHERE postid = #',
						$params['postid']
					));
					if ($answer_userid) {
						$this->log_point_activity($answer_userid, 'answer_unselected', -qa_opt('points_a_selected'), $params['postid'], 'Answer unselected as best');
					}
				}
				break;

			case 'u_register':
				$this->log_point_activity($userid, 'user_registered', qa_opt('points_base'), 0, 'User registration bonus');
				break;
		}

		// Track points received from votes on user's content
		$this->track_vote_points($event, $userid, $params);
	}

	/**
	 * Track points received from votes on user's content
	 */
	private function track_vote_points($event, $userid, $params)
	{
		if (!qa_opt('point_history_track_votes') || !isset($params['postid'])) {
			return;
		}

		// Get the post details
		$post = qa_db_read_one_assoc(qa_db_query_sub(
			'SELECT userid, type, upvotes, downvotes FROM ^posts WHERE postid = #',
			$params['postid']
		));

		if (!$post || $post['userid'] == $userid) {
			return; // Skip if post not found or user voting on their own content
		}

		$post_userid = $post['userid'];
		$post_type = $post['type'];
		$points = 0;

		// Calculate points based on vote type and post type
		switch ($event) {
			case 'q_vote_up':
				if ($post_type == 'Q') {
					$points = qa_opt('points_per_q_voted_up');
				}
				break;
			case 'q_vote_down':
				if ($post_type == 'Q') {
					$points = -qa_opt('points_per_q_voted_down');
				}
				break;
			case 'a_vote_up':
				if ($post_type == 'A') {
					$points = qa_opt('points_per_a_voted_up');
				}
				break;
			case 'a_vote_down':
				if ($post_type == 'A') {
					$points = -qa_opt('points_per_a_voted_down');
				}
				break;
			case 'c_vote_up':
				if ($post_type == 'C') {
					$points = qa_opt('points_per_c_voted_up');
				}
				break;
			case 'c_vote_down':
				if ($post_type == 'C') {
					$points = -qa_opt('points_per_c_voted_down');
				}
				break;
		}

		if ($points != 0) {
			$activity = $post_type == 'Q' ? 'question_voted_on' : ($post_type == 'A' ? 'answer_voted_on' : 'comment_voted_on');
			$description = $post_type == 'Q' ? 'Question received a vote' : ($post_type == 'A' ? 'Answer received a vote' : 'Comment received a vote');
			
			$this->log_point_activity($post_userid, $activity, $points, $params['postid'], $description);
		}
	}

	/**
	 * Log point activity to the database
	 */
	private function log_point_activity($userid, $activity_type, $points, $postid, $description)
	{
		// Check if the point_history table exists
		if (!$this->is_table_ready()) {
			return; // Silently fail if table is not ready
		}

		try {
			// Check if we should limit the number of logs per user
			$max_logs = (int)qa_opt('point_history_max_logs_per_user');
			if ($max_logs > 0) {
				$current_logs = qa_db_read_one_value(qa_db_query_sub(
					'SELECT COUNT(*) FROM ^point_history WHERE userid = #',
					$userid
				));
				
				if ($current_logs >= $max_logs) {
					// Remove oldest logs to make room
					qa_db_query_sub(
						'DELETE FROM ^point_history WHERE userid = # ORDER BY created ASC LIMIT #',
						$userid, $current_logs - $max_logs + 1
					);
				}
			}

			// Insert the point activity log
			qa_db_query_sub(
				'INSERT INTO ^point_history (userid, activity_type, points, postid, description, created) VALUES (#, $, #, #, $, NOW())',
				$userid, $activity_type, $points, $postid, $description
			);
		} catch (Exception $e) {
			// Log error silently to avoid breaking the main Q2A functionality
			error_log('Q2A Point History Plugin Error: ' . $e->getMessage());
		}
	}

	/**
	 * Check if the point_history table is ready for use
	 */
	private function is_table_ready()
	{
		try {
			$table_exists = qa_db_read_one_value(qa_db_query_sub(
				'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "qa_point_history"'
			));
			return $table_exists > 0;
		} catch (Exception $e) {
			return false;
		}
	}

}
