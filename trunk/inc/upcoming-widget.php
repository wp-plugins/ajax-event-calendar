<?php
/**
 * Contributor List Widget Class
 */

class aec_upcoming_events extends WP_Widget{

	function aec_upcoming_events(){
		$widget_ops = array('description' => __('Displays events upcoming in the following weeks.', AEC_PLUGIN_NAME));
		parent::WP_Widget(false, __('Upcoming Events', AEC_PLUGIN_NAME), $widget_ops);
	}
	
	function widget($args, $instance){
		extract($args, EXTR_SKIP);
		$weeks = ($instance['weeks']) ? apply_filters('widget_weeks', $instance['weeks']) : 3;
		$category = ($instance['category']) ? apply_filters('widget_category', $instance['category']) : 'all';
		$events = $this->get_events($weeks,$category);
		echo $before_widget;
		$es = sizeof($events);
		echo $before_title . _n(sprintf('(%d) Upcoming Event', $es), sprintf('(%d) Upcoming Events', $es), $es, AEC_PLUGIN_NAME) . $after_title;
		$out = '<ul>';
		if ($events){
			foreach ($events as $event){
				list($start_date, $start_time) = str_split($event->start, 10);
				list($end_date, $end_time) = str_split($event->end, 10);
				$start_time = trim($start_time);
				$end_time = trim($end_time);

				$out .= '<li class="cat' . $event->category_id . '" onclick="' . $event->id . '" >';
				if ($start_date != $end_date) {
					if ($event->allday) {
						$out .= $start_date;
						$out .= ' - ' . $end_date;
					} else {
						$out .= $event->start;
						$out .= '<br>' . $event->end;
					}
				} else {
					if ($event->allday) {
						$out .= $start_date . ' (' . __('All Day', AEC_PLUGIN_NAME) . ')';
					} else {
						$out .= $start_date . ' (' . $start_time . ' - ' . $end_time . ')';
					}
				}
				$out .= '<br>' . $event->title . '</li>';
			}
		}else{
			$out .= '<li>';
			$out .= _n('No events listed in the next week', sprintf('No events listed in the next %d weeks', $weeks), $weeks, AEC_PLUGIN_NAME);
			$out .= '</li>';
		}
		$out .= '</ul>';
		echo $out;
		echo $after_widget;
	}

	function get_events($duration, $category_id){
		global $wpdb;
		$week = 604800;
		$start = date('Y-m-d');
		$end = date('Y-m-d', strtotime($start) + ($duration * $week));
		$andcategory = ($category_id=="all") ? '' : ' AND category_id = ' . $category_id;
		$results = $wpdb->get_results('SELECT
										id,
										title,
										start,
										end,
										allday,
										category_id
										FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
										WHERE ((start >= "' . $start . '"
										AND start < "' . $end . '")
										OR (end >= "' . $start . '"
										AND end < "' . $end . '")
										OR (start < "' . $start . '"
										AND end > "' . $end . '"))' .
										$andcategory .
										' ORDER BY start;'
									);
		if ($results !== false) return $results;
	}
	
	function get_categories(){
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' ORDER BY id;');
		if ($results !== false) return $results;
	}
		
	function update($new_instance, $old_instance){
		$instance = $old_instance;

		$instance['weeks'] = $new_instance['weeks'];
		$instance['category'] = $new_instance['category'];
				
		return $instance;
	}
	
	/** @see WP_Widget::form */
	function form($instance){
		$instance = wp_parse_args( (array) $instance, array( 'weeks' => '2', 'category' => 'all') );
		$weeks = $instance['weeks'];
		$category = $instance['category'];
?>
	<p>
		<label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Display These Categories', AEC_PLUGIN_NAME); ?></label>
		<select id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>" class="widefat" style="width:100%;">
			<?php
				echo '<option value="all">' . __('All', AEC_PLUGIN_NAME) . '</option>';
				$categories = $this->get_categories();
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
				foreach (range(1, 12) as $week) {
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