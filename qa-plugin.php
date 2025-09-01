<?php
/*
	Plugin Name: Q2A Point History
	Plugin URI: https://github.com/Souravpandev/q2a-point-history
	Plugin Description: A comprehensive point history tracking plugin for Question2Answer that allows users to view their point earning history in a beautiful timeline widget. Features include real-time point tracking, admin point history viewer, responsive timeline design, avatar integration, export functionality, and performance optimizations with minified assets.
	Plugin Version: 1.0.0
	Plugin Date: 2025-01-28
	Plugin Author: Sourav Pan
	Plugin Author URI: https://github.com/Souravpandev
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.8
	Plugin Update Check URI:

	This program is free software: you can redistribute it and/or
	modify it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License along
	with this program. If not, see <http://www.gnu.org/licenses/>.

	Developer: Sourav Pan
	Website: https://wpoptimizelab.com/
	GitHub: https://github.com/Souravpandev
	Repository: https://github.com/Souravpandev/q2a-point-history

	FEATURES:
	- Real-time point activity tracking and logging
	- Beautiful timeline widget with avatar integration
	- Admin point history viewer for all users
	- Responsive design with modern UI/UX
	- Performance optimizations with minified CSS/JS
	- Database query optimization with JOIN queries
	- Conditional asset loading for better performance
	- SVG icon integration for enhanced visual appeal
	- Professional styling and user experience
	- Export functionality for data analysis
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

qa_register_plugin_layer('qa-point-history-layer.php', 'Q2A Point History Layer');
qa_register_plugin_module('module', 'qa-point-history-admin.php', 'qa_point_history_admin', 'Q2A Point History Settings');
qa_register_plugin_module('event', 'qa-point-history-event.php', 'qa_point_history_event', 'Q2A Point History Event Handler');
qa_register_plugin_module('widget', 'qa-point-history-widget.php', 'qa_point_history_widget', 'Q2A Point History Widget');

qa_register_plugin_module('page', 'qa-point-history-admin-page.php', 'qa_point_history_admin_page', 'Q2A Point History Admin Page');
