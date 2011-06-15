<?php
	// IMPORTANT: both these lines are required
	require_once( "../../../../wp-blog-header.php" );
    header( "HTTP/1.1 200 OK" ); 
	// in edit mode:
		// users that are not logged-in see all events
		// administrator can edit all events
		// calendar contributors can only see/edit events they create via admin interface
	// in readonly:
		// mode everyone can see all events
	$user = false;
	if ( $_POST['edit'] ) {
		$user = ( is_user_logged_in() ) ? ( ( current_user_can( 'manage_options' ) ) ? false : $current_user->ID ) : false;
	}
	$aec->get_events( $user, $_POST['start'] , $_POST['end'] );
?>