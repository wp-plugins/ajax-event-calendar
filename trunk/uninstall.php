<?php
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
		exit();

	function aec_delete_plugin() {
		global $wpdb;
	
		delete_option( 'aec_plugin_version' );

		$role = get_role( 'administrator' );
		$role->remove_cap( 'aec_add_events' );
		$role->remove_cap( 'aec_run_reports' );
		remove_role( 'calendar_contributor' );
		remove_role( 'blog_calendar_contributor' );

		// Deleting events and category table
		$tables = array('aec_event', 'aec_event_category');
		
		foreach ($tables as $table) {
			$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . $table );
		}
	}
	
	aec_delete_plugin();
?>