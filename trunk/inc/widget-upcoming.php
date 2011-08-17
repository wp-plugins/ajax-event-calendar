<?php
/**
 * Upcoming Event Widget Class
 */

class aec_upcoming_events extends WP_Widget{

	function aec_upcoming_events(){
		$widget_ops = array('description' => __('Displays upcoming events with optional filters.', AEC_PLUGIN_NAME));
		parent::WP_Widget(false, __('AEC Upcoming Events', AEC_PLUGIN_NAME), $widget_ops);
	}
	
	function query_events_by_category($category_id, $eventlimit){
		global $wpdb;
		$start = date('Y-m-d');
		$andcategory = ($category_id) ? ' AND category_id = ' . $category_id : '';

		$results = $wpdb->get_results($wpdb->prepare('SELECT
													id,
													title,
													start,
													end,
													allDay,
													category_id
													FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
													WHERE (start >= %s
													OR end >= %s)' .
													$andcategory . '
													ORDER BY start 
													LIMIT %d;',
													$start,
													$start,
													$eventlimit));
		if ($results !== false) return $results;
	}
	
	function query_categories(){
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' ORDER BY id;');
		if ($results !== false) return $results;
	}
	
	function widget($args, $instance){
		extract($args, EXTR_SKIP);
		$whitelabel	= ($instance['whitelabel']) ? apply_filters('widget_whitelabel', $instance['whitelabel']) : false;
		$eventlimit	= ($instance['eventlimit']) ? apply_filters('widget_eventlimit', $instance['eventlimit']) : 4;
		$category 	= ($instance['category']) ? apply_filters('widget_category', $instance['category']) : 0;
		$title 		= ($instance['title']) ? apply_filters('widget_title', $instance['title']) : __('Upcoming Events', AEC_PLUGIN_NAME);
		$callink	= ($instance['callink']) ? apply_filters('widget_callink', $instance['callink']) : 0;
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		$out 		= '<ul class="upcoming_events">';
		$events 	= $this->query_events_by_category($category, $eventlimit);
		if ($events){
			foreach ($events as $event){
				// split database formatted datetime value into display formatted date and time values
				$event->start_date	= ajax_event_calendar::date_convert($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
				$event->start_time 	= ajax_event_calendar::date_convert($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);
				$event->end_date 	= ajax_event_calendar::date_convert($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
				$event->end_time 	= ajax_event_calendar::date_convert($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);
				
				// link to event			
				$class = ($whitelabel) ? '' : ' cat' . $event->category_id;
				$out .= '<li class="fc-event round5' . $class . '" onClick="jQuery.eventDialog({\'id\':' . $event->id . '});">';
				$out .= '<strong>' . ajax_event_calendar::render_i18n_data($event->title) . '</strong><br>';
				$out .= $event->start_date;
				// multiple day event, not spanning all day
				if (!$event->allDay)
					$out .= ' ' . $event->start_time;
				$out .= '</li>';
			}
		} else {
			$out .= '<li>';
			$out .= __('No upcoming events', AEC_PLUGIN_NAME);
			$out .= '</li>';
		}
		if ($callink) $out .= "<h3 class='widget-title'><a href='{$callink}'>" . __('Link to Calendar', AEC_PLUGIN_NAME) . '</a></h3>';
		$out .= '</ul>';
		echo $out;
		echo $after_widget;
	}
		
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['whitelabel'] = (isset($new_instance['whitelabel']) ? 1 : 0);
		$instance['callink'] = $new_instance['callink'];
		$instance['eventlimit'] = $new_instance['eventlimit'];
		$instance['title'] = $new_instance['title'];
		$instance['category'] = $new_instance['category'];
		return $instance;
	}
	
	/** @see WP_Widget::form */
	function form($instance){
		$instance = wp_parse_args((array) $instance, array('eventlimit' => 4, 'title' => __('Upcoming Events', AEC_PLUGIN_NAME), 'category' => 0, 'whitelabel' => false, 'callink' => ''));
		$whitelabel = $instance['whitelabel'];
		$eventlimit = $instance['eventlimit'];
		$title = $instance['title'];
		$category = $instance['category'];
		$callink = $instance['callink'];
?>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', AEC_PLUGIN_NAME); ?></label>
		<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo $title; ?>">
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Event category displayed', AEC_PLUGIN_NAME); ?></label>
		<select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" class="widefat" style="width:100%;">
			<?php
				echo '<option value="0">' . __('All', AEC_PLUGIN_NAME) . '</option>';
				$categories = $this->query_categories();
				foreach ($categories as $cat) {
					$category_selected = ($cat->id == $category) ? ' selected="selected"' : '';
					echo '<option value="' . $cat->id . '"' . $category_selected . '>' . ajax_event_calendar::render_i18n_data($cat->category) . '</option>';
				}
			?>
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('eventlimit'); ?>"><?php _e('Maximum events displayed', AEC_PLUGIN_NAME); ?></label>
		<select id="<?php echo $this->get_field_id('eventlimit'); ?>" name="<?php echo $this->get_field_name('eventlimit'); ?>" class="widefat" style="width:100%;">
			<?php
				$limitrange = range(2, 12);
				foreach ($limitrange as $event) {
					$selected_eventlimit = ($event == $eventlimit) ? ' selected="selected"' : '';
					echo '<option value="' . $event . '"' . $selected_eventlimit . '>' . $event . '</option>';
				}
			?>
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('callink'); ?>"><?php _e('Link to calendar', AEC_PLUGIN_NAME); ?></label>
		<input id="<?php echo $this->get_field_id('callink'); ?>" name="<?php echo $this->get_field_name('callink'); ?>" class="widefat" value="<?php echo $callink; ?>" />
	</p>
	<p>
		<input class="checkbox" type="checkbox" <?php checked($whitelabel, true ); ?> id="<?php echo $this->get_field_id('whitelabel'); ?>" name="<?php echo $this->get_field_name('whitelabel'); ?>" />
		<label for="<?php echo $this->get_field_id('whitelabel'); ?>"><?php _e('Hide category colors', AEC_PLUGIN_NAME); ?></label>
	</p>
	<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("aec_upcoming_events");'));
?>