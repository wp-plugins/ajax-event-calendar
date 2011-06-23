<?php
	if (!defined('WP_UNINSTALL_PLUGIN'))
		exit();

	function aec_delete_plugin() {
		global $wpdb;
	
		// Delete Plugin Settings
		delete_option('aec_version');
		delete_option('aec_options');
		
		// Delete Widget Settings
		delete_option('widget_aec_contributor_list');
		delete_option('widget_aec_upcoming_events');
		
		// Delete Custom Roles and Capabilities
		$role = get_role('administrator');
		$role->remove_cap('aec_add_events');
		$role->remove_cap('aec_run_reports');
		remove_role('calendar_contributor');
		remove_role('blog_calendar_contributor');

		// Delete Event and Category Tables
		$tables = array('aec_event',  'aec_event_category');
		
		foreach ($tables as $table) {
			$wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . $table);
		}
	}
	
	aec_delete_plugin();
?>