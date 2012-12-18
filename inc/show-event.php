<?php
	if (!isset($_POST)) {
		return;
	}

	$aec_options 		= get_option('aec_options');
	$event				= new stdClass();
	$event 				= $this->db_query_event($_POST['id']);

	if ($event->repeat_freq) {
		$event->start 	= $_POST['start'];
		$event->end 	= (!empty($_POST['end'])) ? $_POST['end'] : $_POST['start'];
	}

	$event->start_date	= ajax_event_calendar::convert_date($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
	$event->start_time 	= ajax_event_calendar::convert_date($event->start, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);
	$event->end_date 	= ajax_event_calendar::convert_date($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);
	$event->end_time 	= ajax_event_calendar::convert_date($event->end, AEC_DB_DATETIME_FORMAT, AEC_WP_TIME_FORMAT);
	$divider			= '&ndash;';
	$event->repeat_end	= ajax_event_calendar::convert_date($event->repeat_end, AEC_DB_DATETIME_FORMAT, AEC_WP_DATE_FORMAT);

	if ($event->allDay) {
		$event->start		= $event->start_date;
		$event->end 		= $event->end_date;
		$event->start_time	= '';
		$event->end_time	= '';
	}

	if ($event->start_date == $event->end_date) {
		$event->end_date 	= '';
		if ($event->allDay) {
			$divider 		= '';
		}
	}

	$out = "<div class='times'>{$event->start_date} {$event->start_time} {$divider} {$event->end_date} {$event->end_time}</div>\n";

	$categories = $this->db_query_categories();
	foreach($categories as $category) {
		if ($event->category_id == $category->id) {
			$cat = $category->category;
			break;
		}
	}
	$out .= "<div class='category'>$cat</div>\n";
	$out .= "<div class='round5 duration'></div>\n";

	if (!empty($event->description)) {
		// maintain lines breaks entered in textarea
		$description = nl2br($event->description);

		// convert urls in text into clickable links
		if ($aec_options['make_links']) {
			$description = make_clickable($description);
		}
		$out .= "<div class='description'>{$description}</div>\n";
	}

	if (!empty($event->venue) || 
		!empty($event->address) ||
		!empty($event->city) ||
		!empty($event->state) ||
		!empty($event->zip)) {

			$city 		= "{$event->city}";
			$state		= "{$event->state}";
			$zip		= "{$event->zip}";
			$comma		= ", ";

			if (empty($state)) {
				$comma 	= '';
			}

			$csz 		= ($aec_options['addy_format']) ? "{$zip} {$city}" : "{$city}{$comma} {$state} {$zip}";

		// google map link
		if ($aec_options['show_map_link']) {
			$out .= "<a href='http://maps.google.com/?q=" . urlencode($event->address . " " . $csz . " " . $event->country) . "' class='round5 maplink cat{$event->category_id}'>" . __('View Map', AEC_NAME) . "</a>\n";
		}

		$out .= "<div class='round5 location'>\n";

		if (!empty($event->venue)) {
			$out .= "<span>{$event->venue}</span>\n";
		}

		if (!empty($event->address)) {
			$out .= "<span>{$event->address}</span>\n";
		}

		$out .= trim($csz);

		if (!empty($event->country)) {
			$out .= "&nbsp;&nbsp;{$event->country}\n";
		}

		if ($event->access) {
			$out .= "<span>" . __('This event is accessible to people with disabilities.', AEC_NAME) . "</span>\n";
		}
		$out .= "</div>\n";
	}

	$adjust = 2;
	$out .= "<div class='contact'>\n";
	if ($event->rsvp) {
		$out .= "<label>" . __('Please register with the contact person for this event.' , AEC_NAME) . "</label>\n";
		$adjust -= 1;
	}

	if (!empty($event->contact)) {
		$out .= "<span>{$event->contact}</span>\n";
		$adjust -= 1;
	}
	if (!empty($event->contact_info)) {
		$contact_info = make_clickable($event->contact_info);
		$out .= "{$contact_info}\n";
		$adjust -= 1;
	}
	$out .= "</div>\n";
	
	$org = get_userdata($event->user_id);
	if (!empty($org->organization)) {
		$organization = stripslashes($org->organization);
		$out .= '<div class="presented">' . __('Presented by', AEC_NAME) . ' ';
		if (!empty($org->user_url)) {
			$out .= "<a href='{$org->user_url}' target='_blank'>{$organization}</a>";
		}else{
			$out .= $organization;
		}
		$out .= "</div>\n";
		$adjust -= 1;
	}
	
	if (!empty($event->link)) {
		if ($adjust < 1) {
			$adjust = 1;
		}
		$out .= str_repeat("<br>", $adjust);
		$link  = "<a href='{$event->link}' class='link round5 cat{$event->category_id}'>";
		$link .= __('Event Link', AEC_NAME);
		$link .= "</a>\n";
		$out .= "{$link}";
	}
	
	// make links open in a new window
	if ($aec_options['popup_links']) {
		$out = popuplinks($out);
	}

	$output = array(
		'title'		=> $event->title,
		'content'	=> $out,
		// used by javascript duration calculation
		'start'		=> date('m/d/Y H:i:00', strtotime($event->start)),
		'end'		=> date('m/d/Y H:i:00', strtotime($event->end)),
		'allDay'	=> $event->allDay,
		'repeat_f'	=> $event->repeat_freq,
		'repeat_i'	=> $event->repeat_int,
		'repeat_e'	=> $event->repeat_end
	);
	$this->render_json($output);
?>