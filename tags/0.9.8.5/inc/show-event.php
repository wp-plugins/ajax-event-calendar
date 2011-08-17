<?php
	if (!isset($_POST['id'])) return;
	$options 	= get_option('aec_options');
	$event 		= $this->query_event($_POST['id']);
	$event->start_date	= ajax_event_calendar::date_convert($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
	$event->start_time 	= ajax_event_calendar::date_convert($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);
	$event->end_date 	= ajax_event_calendar::date_convert($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
	$event->end_time 	= ajax_event_calendar::date_convert($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);

	$out = '<ul>';
	$out .= '<li><h3>';

	if ($event->start_date != $event->end_date) {
		// multiple day event, spanning all day
		if ($event->allDay) {
			$out .= $event->start_date;
			$out .= ' - ' . $event->end_date;
			$event->start = $event->start_date;
			$event->end = $event->end_date;
			
		// multiple day event, not spanning all day
		} else {
			$out .= $event->start_date . ' ' . $event->start_time;
			$out .= '<br>' . $event->end_date . ' ' . $event->end_time;
		}
		
	} else {
			
		// one day event, spanning all day
		if ($event->allDay) {
			$out .= $event->start_date;
			$event->start = $event->start_date;
			$event->end = $event->end_date;
		
		// one day event, spanning hours
		} else {
			$out .= $event->start_date;
			$out .= '<br>' . $event->start_time . ' - ' . $event->end_time;
		}
	}
	$out .= '</h3>';
	$out .= '<span class="duration round5">' . __('Duration', AEC_PLUGIN_NAME) . ': ' . $this->return_duration($event) . '</span>';
	$out .= '</li>';
	$out .= '<li>' . $event->description . '</li>';

	if (!empty($event->venue) || !empty($event->address) || !empty($event->city) || !empty($event->state) || !empty($event->zip) ) {
		$out .= '<li><h3>' . __('Location', AEC_PLUGIN_NAME) . '</h3>';
		if (!empty($event->venue)) $out .= $event->venue . '<br>';
		$city 	= (!empty($event->city)) ? $event->city . ' ' : '';
		$state 	= (!empty($event->state)) ? $event->state . ' ' : '';
		$zip	= (!empty($event->zip)) ? $event->zip : '';
		$csz 	= $city . $state . $zip;
		if (!empty($event->address)) {
			$out .= $event->address . '<br>' . $csz;
		} else {
			$out .= $csz;
		}
		$out .= '</li>';

		// google map link
		 if ($options['show_map_link'] && (!empty($event->address) || !empty($csz))) {
			$out .= '<li>';
			$out .= '<a href="http://maps.google.com/?q=' . urlencode($event->address . ' ' . $csz) . '" class="round5 cat' . $event->category_id . '" target="_blank">' . __('View Map', AEC_PLUGIN_NAME) . '</a>';
			$out .= '</li>';
		}
	}

	if (!empty($event->contact) || !empty($event->contact_info)) {
		$out .= '<li><h3>' . __('Contact Information', AEC_PLUGIN_NAME) . '</h3>';
		if (!empty($event->contact)) $out .= $event->contact . '<br>';
		if (!empty($event->contact_info)) $out .= $event->contact_info;
		$out .= '</li>';
	}

	if ($event->access || $event->rsvp) {
		$out .= '<hr>';
		if ($event->access) $out .= '<li>' . __('This event is accessible to people with disabilities.', AEC_PLUGIN_NAME) . '</li>';
		if ($event->rsvp) $out .= '<li>' . __('Please register with the contact person for this event.' , AEC_PLUGIN_NAME) . '</li>';
	}

	$org = get_userdata($event->user_id);
	if (!empty($org->organization)) {
			$out .= '<li class="presented">' . __('Presented by', AEC_PLUGIN_NAME) . ' ';
		if (!empty($org->user_url)) {
			$out .= '<a href="' . $org->user_url . '" target="_blank">' . stripslashes($org->organization) . '</a>';
		} else {
			$out .= stripslashes($org->organization);
		}
		$out .= '</li>';
	}

	if (!empty($event->link)) $out .= '<li><a href="' . $event->link . '" class="link cat' . $event->category_id . '" target="_blank">' . __('Event Link', AEC_PLUGIN_NAME) . '</a></li>';

	$out .= '</ul>';

	$categories = $this->query_categories();
	foreach ($categories as $category) {
		if ($event->category_id == $category->id) {
			$cat = $category->category;
			break;
		}
	}

	$output = array(
		'title'		=> $event->title . ' (' . $cat . ')',
		'content'	=> $out
	);
	
	$this->render_json($output);
?>