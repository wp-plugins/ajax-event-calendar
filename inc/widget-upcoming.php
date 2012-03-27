<?php
/**
 * Upcoming Event Widget Class
	As indicated in version 1.0 and as of version 1.0.1
	This widget has been removed from the Ajax Event Calendar plugin. 
	
	Instead, use the [eventlist] shortcode, explained here: http://wordpress.org/extend/plugins/ajax-event-calendar/installation
	
	The shortcode offers more customization and placement options than the old widget, explained here:
	http://code.google.com/p/wp-aec/wiki/ShortcodeOptions
 */

class aec_upcoming_events extends WP_Widget{

	function aec_upcoming_events () {
		$widget_ops = array('description' => 'This widget has been replaced by the [eventlist] shortcode, read the documentation for details!');
		parent::WP_Widget(false, __('AEC Upcoming Events', AEC_NAME), $widget_ops);
	}
		
	function widget($args, $instance) {
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<div style="padding:5px; background-color:#ff0"><p><strong>IMPORTANT:</strong></p>
		<p>As of version 1.0.1, this widget has been removed from the Ajax Event Calendar plugin. Instead, use the [eventlist] shortcode explained here:</p>
		<p>http://wordpress.org/extend/plugins/ajax-event-calendar/installation</p>
		<p>The shortcode offers more customization and placement options than the old widget, explained here:</p>
		<p>http://code.google.com/p/wp-aec/wiki/ShortcodeOptions</p>
		</div>';
		echo $after_widget;
	}
		
	/** @see WP_Widget::form */
	function form ($instance) {
		$instance = wp_parse_args((array) $instance, array('eventlimit' => 4, 'title' => __('Upcoming Events', AEC_NAME), 'category' => 0, 'whitelabel' => false, 'callink' => ''));
		$whitelabel = $instance['whitelabel'];
		$eventlimit = $instance['eventlimit'];
		$title = $instance['title'];
		$category = $instance['category'];
		$callink = $instance['callink'];
?>
	<div style="padding:5px; background-color:#ff0"><p><strong>IMPORTANT:</strong></p><p>As of version 1.0.1, this widget has been removed from the Ajax Event Calendar plugin. Instead, use the [eventlist] shortcode <a href="http://wordpress.org/extend/plugins/ajax-event-calendar/installation">explained here:</a></p><p>The shortcode offers more customization and placement options than the old widget, <a href="http://code.google.com/p/wp-aec/wiki/ShortcodeOptions">explained here</a>.</p></div>
<?php
	}
}
add_action('widgets_init', create_function('', 'return register_widget("aec_upcoming_events");'));
?>