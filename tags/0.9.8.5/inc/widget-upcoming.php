<?php
/**
 * Contributor List Widget Class
 */

class aec_upcoming_events extends WP_Widget{

	function aec_upcoming_events(){
		$widget_ops = array('description' => __('Displays events upcoming in the following weeks.', AEC_PLUGIN_NAME));
		parent::WP_Widget(false, __('Upcoming Events', AEC_PLUGIN_NAME), $widget_ops);
	}
	
	function query_events_by_duration_and_category($duration, $category_id){
		global $wpdb;
		$week = 604800;
		
		// check if blog timezone is not set
		// if ($tz = get_option('timezone_string')) {
		//	date_default_timezone_set($tz);		// localize date using blog timezone
		// }

		$start = date('Y-m-d');
		$end = date('Y-m-d', strtotime($start) + ($duration * $week));
		$andcategory = ($category_id=="all") ? '' : ' AND category_id = ' . $category_id;		
		$results = $wpdb->get_results('SELECT
										id,
										title,
										start,
										end,
										allday as allDay,
										category_id
										FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
										WHERE ((start >= "' . $start . '"
										AND start < "' . $end . '")
										OR (end >= "' . $start . '"
										AND end < "' . $end . '"))' .
										$andcategory .
										' ORDER BY start;'
									);
		if ($results !== false) return $results;
	}
	
	function query_categories(){
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' ORDER BY id;');
		if ($results !== false) return $results;
	}
	
	function widget($args, $instance){
		extract($args, EXTR_SKIP);
		$weeks 		= ($instance['weeks']) ? apply_filters('widget_weeks', $instance['weeks']) : 3;
		$category 	= ($instance['category']) ? apply_filters('widget_category', $instance['category']) : 'all';
		$title 		= ($instance['title']) ? apply_filters('widget_title', $instance['title']) : __('Three Week Agenda', AEC_PLUGIN_NAME);
		
		echo $before_widget;
		echo $before_title . $title . $after_title;
		$out 		= '<ul class="upcoming_events">';
		$events 	= $this->query_events_by_duration_and_category($weeks, $category);
		if ($events){
			foreach ($events as $event){
				// split database formatted datetime value into display formatted date and time values
				$event->start_date	= ajax_event_calendar::date_convert($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
				$event->start_time 	= ajax_event_calendar::date_convert($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);
				$event->end_date 	= ajax_event_calendar::date_convert($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
				$event->end_time 	= ajax_event_calendar::date_convert($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);
				
				// link to event			
				$out .= '<li class="fc-event round5 cat' . $event->category_id . '" onClick="$jq.eventDialog({\'id\':' . $event->id . '});">';
				
				if ($event->start_date != $event->end_date) {
					// multiple day event, spanning all day
					if ($event->allDay) {
							$out .= $event->start_date;
							$out .= ' - ' . $event->end_date;
						
						// multiple day event, not spanning all day
						} else {
							$out .= $event->start_date . ' ' . $event->start_time;
							$out .= '<br>' . $event->end_date . ' ' . $event->end_time;
						}
					
				} else {
						
						// one day event, spanning all day
						if ($event->allDay) {
							$out .= $event->start_date;
						
						// one day event, spanning hours
						} else {
							$out .= $event->start_date;
							$out .= '<br>' . $event->start_time . ' - ' . $event->end_time;
						}
				}
				$out .= '<br><strong>' . ajax_event_calendar::render_i18n_data($event->title) . '</strong></li>';
			}
		}else{
			$out .= '<li>';
			$out .= sprintf(_n('No events listed in the next week', 'No events listed in the next %d weeks', $weeks, AEC_PLUGIN_NAME), $weeks);
			$out .= '</li>';
		}
		$out .= '</ul>';
		echo $out;
		echo $after_widget;
	}
		
	function update($new_instance, $old_instance){
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['weeks'] = $new_instance['weeks'];
		$instance['category'] = $new_instance['category'];
		return $instance;
	}
	
	/** @see WP_Widget::form */
	function form($instance){
		$instance = wp_parse_args((array) $instance, array( 'title' => __('Three Week Agenda', AEC_PLUGIN_NAME), 'weeks' => '3', 'category' => 'all'));
		$title = $instance['title'];
		$weeks = $instance['weeks'];
		$category = $instance['category'];
?>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', AEC_PLUGIN_NAME); ?></label>
		<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo $title; ?>">
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Display this Category', AEC_PLUGIN_NAME); ?></label>
		<select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" class="widefat" style="width:100%;">
			<?php
				echo '<option value="all">' . __('All', AEC_PLUGIN_NAME) . '</option>';
				$categories = $this->query_categories();
				foreach ($categories as $cat) {
					$category_selected = ($cat->id == $category) ? ' selected="selected"' : '';
					echo '<option value="' . $cat->id . '"' . $category_selected . '>' . $cat->category . '</option>';
				}
			?>
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id('weeks'); ?>"><?php _e('Number of Weeks from Today', AEC_PLUGIN_NAME); ?></label>
		<select id="<?php echo $this->get_field_id('weeks'); ?>" name="<?php echo $this->get_field_name('weeks'); ?>" class="widefat" style="width:100%;">
			<?php
				$limit = range(1, 12);
				foreach ($limit as $week) {
					$week_selected = ($week == $weeks) ? ' selected="selected"' : '';
					echo '<option value="' . $week . '"' . $week_selected . '>' . $week . '</option>';
				}
			?>
		</select>
	</p>
	<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("aec_upcoming_events");'));
?>