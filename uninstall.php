<?php
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
		exit();

	function aec_delete_plugin() {
		global $wpdb;
	
		delete_option( AEC_DOMAIN . 'version' );
		delete_option( AEC_DOMAIN . 'options' );
		$role = get_role( 'administrator' );
		$role->remove_cap(  AEC_DOMAIN . 'add_events' );
		$role->remove_cap(  AEC_DOMAIN . 'run_reports' );
		remove_role( 'calendar_contributor' );
		remove_role( 'blog_calendar_contributor' );

		// Deleting events and category table
		$tables = array( 'event',  'event_category');
		
		foreach ($tables as $table) {
			$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix .  AEC_DOMAIN . $table );
		}
	}
	
	aec_delete_plugin();
?>