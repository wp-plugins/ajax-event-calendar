<?php
/**
 * Contributor List Widget Class
 */

class contributor_list extends WP_Widget {

	function contributor_list() {
		$widget_ops = array('description' => 'A list of calendar contributors linked to their organization websites' );
		parent::WP_Widget( false, 'Calendar Contributors', $widget_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );
		$contributors = $this->get_users_by_role( 'calendar_contributor' );
		echo $before_widget;
		echo $before_title . ' (' . sizeof( $contributors ) . ') Contributors' . $after_title; 
		if ( $contributors ) {
			echo '<ul>';
			foreach ( $contributors as $contributor ) {
				$user = get_userdata( $contributor );
				echo '<li><a href="' . $user->user_url . '" target="_blank">' .  $user->organization . '</a></li>';
			}
		} else {
			echo 'No contributors as of yet!';
		}
		echo '</ul>';
		echo $after_widget;		
	}

	function get_users_by_role( $roles ) {
		global $wpdb;
		if ( ! is_array( $roles ) ) {
			$roles = explode( ",", $roles );
			array_walk( $roles, 'trim' );
		}
		$sql = '
			SELECT	ID, display_name
			FROM	' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON		' . $wpdb->users . '.ID	=		' . $wpdb->usermeta . '.user_id
			WHERE	' . $wpdb->usermeta . '.meta_key =	\'' . $wpdb->prefix . 'capabilities\'
			AND		(
		';
		$i = 1;
		foreach ( $roles as $role ) {
			$sql .= ' ' . $wpdb->usermeta . '.meta_value	LIKE	\'%"' . $role . '"%\' ';
			if ( $i < count( $roles ) ) $sql .= ' OR ';
			$i++;
		}
		$sql .= ' ) ';
		$sql .= ' ORDER BY display_name ';
		$userIDs = $wpdb->get_col( $sql );
		return $userIDs;
	}
	
	/** @see WP_Widget::form */
	function form() {				
		echo "No options to worry about....it's magic!";
	}
}

?>
