<?php
/**
 * Mini-calendar Widget Class
 */

class aec_minical extends WP_Widget{

	function aec_minical(){
		$widget_ops = array('description' => __('Displays a miniature calendar of events.', AEC_PLUGIN_NAME));
		parent::WP_Widget(false, __('AEC Mini Calendar', AEC_PLUGIN_NAME), $widget_ops);
	}
			
	function widget($args, $instance){
		extract($args, EXTR_SKIP);
		$title 		= ($instance['title']) ? apply_filters('widget_title', $instance['title']) : __('Mini Calendar', AEC_PLUGIN_NAME);
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<div class="aec-minical"></div>';	
		echo $after_widget;
	}
		
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		return $instance;
	}
	
	/** @see WP_Widget::form */
	function form($instance){
		$instance = wp_parse_args((array) $instance, array( 'title' => __('AEC Mini Calendar', AEC_PLUGIN_NAME)));
		$title = $instance['title'];
?>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', AEC_PLUGIN_NAME); ?></label>
		<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo $title; ?>">
	</p>
	<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("aec_minical");'));
?>