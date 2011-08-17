<?php
	if (!isset($_POST['id'])) return;
	$options 	= get_option('aec_options');
	$event 		= $this->query_event($_POST['id']);
	$event->start_date	= ajax_event_calendar::date_convert($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
	$event->start_time 	= ajax_event_calendar::date_convert($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);
	$event->end_date 	= ajax_event_calendar::date_convert($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
	$event->end_time 	= ajax_event_calendar::date_convert($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);

	
	$out = '<h3>';

	if ($event->start_date != $event->end_date) {
		// multiple day event, spanning all day
		if ($event->allDay) {
			$out .= "{$event->start_date} - {$event->end_date}";
		// multiple day event, not spanning all day
		} else {
			$out .= "{$event->start_date} {$event->start_time} - {$event->end_date} {$event->end_time}";
		}
	} else {
		// one day event, spanning all day
		if ($event->allDay) {
			$out .= $event->start_date;
		// one day event, spanning hours
		} else {
			$out .= "{$event->start_date} {$event->start_time} - {$event->end_time}";
		}
	}
	$out .= "<span class='duration round5'></span></h3>\n";
	
	// maintain lines breaks entered in textarea
	$description = nl2br($event->description);
	
	// convert urls in text into clickable links
	if ($options['make_links'])
		$description = make_clickable($description);
	
	$out .= "<p>{$description}</p>\n";

	if (!empty($event->venue) || !empty($event->address) || !empty($event->city) || !empty($event->state) || !empty($event->zip) ) {
		
		$city 	= (!empty($event->city)) ? $event->city : '';
		$state 	= (!empty($event->state)) ? $event->state : '';
		$zip	= (!empty($event->zip)) ? $event->zip : '';
		$csz 	= ($options['addy_format']) ? "{$zip} {$city}" : "{$city} {$state}, {$zip}";

		$out .= '<h3>' . __('Location', AEC_PLUGIN_NAME) . "</h3>\n";

		// google map link
		 if ($options['show_map_link'] && (!empty($event->address) || !empty($csz))) {
			$out .= "<a href='http://maps.google.com/?q=" . urlencode($event->address . " " . $csz) . "' class='round5 maplink cat{$event->category_id}'>" . __('View Map', AEC_PLUGIN_NAME) . "</a>\n";
		}
		if (!empty($event->venue)) $out .= "<p>{$event->venue}\n";
		$out .= (!empty($event->address)) ? "<br>{$event->address}<br>{$csz}</p>" : "<br>{$csz}</p>";	
	}

	if (!empty($event->contact) || !empty($event->contact_info)) {
		$out .= "<h3>" . __('Contact Information', AEC_PLUGIN_NAME) . "</h3>\n";
		$contact = (!empty($event->contact)) ? $event->contact : '';
		$cntinfo = (!empty($event->contact_info)) ? "(" . make_clickable($event->contact_info) . ")" : '';
		$out .= "<p>{$contact} {$cntinfo}</p>\n";
	}

	if ($event->access || $event->rsvp) {
		if ($event->access) $out .= '<p>' . __('This event is accessible to people with disabilities.', AEC_PLUGIN_NAME) . '</p>';
		if ($event->rsvp) $out .= '<p>' . __('Please register with the contact person for this event.' , AEC_PLUGIN_NAME) . '</p>';
	}

	$org = get_userdata($event->user_id);
	if (!empty($org->organization)) {
		$organization = stripslashes($org->organization);
			$out .= '<p class="presented">' . __('Presented by', AEC_PLUGIN_NAME) . ' ';
		if (!empty($org->user_url)) {
			$out .= "<a href='{$org->user_url}' target='_blank'>{$organization}</a>";
		} else {
			$out .= $organization;
		}
		$out .= "</p>\n";
	}

	if (!empty($event->link)) {
		$link  = "<a href='{$event->link}' class='link round5 cat{$event->category_id}'>";
		$link .= __('Event Link', AEC_PLUGIN_NAME);
		$link .= "</a>\n";
		$out .= $link;
	}

	$categories = $this->query_categories();
	foreach ($categories as $category) {
		if ($event->category_id == $category->id) {
			$cat = $category->category;
			break;
		}
	}
			
	if ($event->allDay) {
		$event->start = $event->start_date;
		$event->end = $event->end_date;
	}
	
	// make links open in a new window
	if ($options['popup_links']) 
		$out = popuplinks($out);
	
	$output = array(
		'title'		=> "{$event->title} ({$cat})",
		'content'	=> $out,
		'start'		=> date('m/d/Y H:i:00', strtotime($event->start)),	// used by javascript duration calculation
		'end'		=> date('m/d/Y H:i:00', strtotime($event->end)),	// used by javascript duration calculation
		'allDay'	=> $event->allDay									// used by javascript duration calculation
	);
	
	$this->render_json($output);
?>