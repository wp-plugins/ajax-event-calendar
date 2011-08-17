<?php
/**
 * Contributor List Widget Class
 */

class aec_contributor_list extends WP_Widget{

	function aec_contributor_list(){
		$widget_ops = array('description' => __('A list of calendar contributors linked to their organization websites', AEC_PLUGIN_NAME));
		parent::WP_Widget(false, __('AEC Contributors', AEC_PLUGIN_NAME), $widget_ops);
	}
	
	function widget($args, $instance){
		extract($args);
		$contributors = $this->get_users_by_role('calendar_contributor');
		echo $before_widget;
		$conts = sizeof($contributors);
		echo $before_title . sprintf(_n('(%d) Contributor','(%d) Contributors', $conts, AEC_PLUGIN_NAME), $conts) . $after_title;
		if ($contributors){
			echo '<ul>';
			foreach ($contributors as $contributor){
				$user = get_userdata($contributor);
				echo '<li><a href="' . $user->user_url . '" target="_blank">' .  $user->organization . '</a></li>';
			}
		}else{
			_e('No contributors as of yet.', AEC_PLUGIN_NAME);
		}
		echo '</ul>';
		echo $after_widget;
	}

	function get_users_by_role($roles){
		global $wpdb;
		if (! is_array($roles)){
			$roles = explode(",", $roles);
			array_walk($roles, 'trim');
		}
		$sql = '
			SELECT	ID, display_name
			FROM	' . $wpdb->users . ' INNER JOIN ' . $wpdb->usermeta . '
			ON		' . $wpdb->users . '.ID	=		' . $wpdb->usermeta . '.user_id
			WHERE	' . $wpdb->usermeta . '.meta_key =	\'' . $wpdb->prefix . 'capabilities\'
			AND		(
		';
		$i = 1;
		foreach ($roles as $role){
			$sql .= ' ' . $wpdb->usermeta . '.meta_value	LIKE	\'%"' . $role . '"%\' ';
			if ($i < count($roles)) $sql .= ' OR ';
			$i++;
		}
		$sql .= ') ';
		$sql .= ' ORDER BY display_name ';
		$userIDs = $wpdb->get_col($sql);
		return $userIDs;
	}
	
	/** @see WP_Widget::form */
	function form(){				
		_e('No options available.', AEC_PLUGIN_NAME);
	}
}

add_action('widgets_init', create_function('', 'return register_widget("aec_contributor_list");'));
?>
