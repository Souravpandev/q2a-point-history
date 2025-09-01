<?php
/*
	Layer Module for Q2A Point History Plugin
	Developed by Qlassy Team
	Lead Developer: Sourav Pan
	
	File: qa-plugin/q2a-point-history/qa-point-history-layer.php
	Version: 1.0.0
	Description: Asset loading layer module for the Q2A Point History plugin

	This program is free software. You can redistribute and modify it
	under the terms of the GNU General Public License.
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

class qa_html_theme_layer extends qa_html_theme_base
{
	/**
	 * Load CSS in the head section only when needed
	 */
	function head_css()
	{
		qa_html_theme_base::head_css();
		
		// Only load CSS if widget is enabled and user is logged in
		if ($this->should_load_assets()) {
			$this->output('<link rel="stylesheet" type="text/css" href="' . qa_html(qa_path_to_root() . 'qa-plugin/q2a-point-history/css/point-history.min.css?v=' . QA_VERSION) . '">');
		}
	}

	/**
	 * Load JavaScript in the head section only when needed
	 */
	function head_script()
	{
		qa_html_theme_base::head_script();
		
		// Only load JS if widget is enabled and user is logged in, or on point-history pages
		if ($this->should_load_assets() || $this->is_point_history_page()) {
			$this->output('<script type="text/javascript" src="' . qa_html(qa_path_to_root() . 'qa-plugin/q2a-point-history/js/point-history.min.js?v=' . QA_VERSION) . '"></script>');
		}
	}

	/**
	 * Check if we should load CSS/JS assets
	 */
	private function should_load_assets()
	{
		// Load assets only if:
		// 1. Widget is enabled in admin settings
		// 2. User is logged in (widget only shows for logged-in users)
		return qa_opt('point_history_widget_enabled') && qa_is_logged_in();
	}

	/**
	 * Check if this is a point history page (needs JS for export functionality)
	 */
	private function is_point_history_page()
	{
		$request = qa_request();
		return strpos($request, 'point-history/') === 0;
	}
}
