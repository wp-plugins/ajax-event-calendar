<?php
	// IMPORTANT: both these lines are required
	require_once("../../../../wp-blog-header.php");
	header("HTTP/1.1 200 OK");
	
	if (isset($_POST['id'])) {
		$event = $aec->get_event($_POST['id']);

		$out = '<ul>';

			// Split date/time fields
			list($start_date, $start_time) = str_split($event->start, 10);
			list($end_date, $end_time) = str_split($event->end, 10);
			$start_time = trim($start_time);
			$end_time = trim($end_time);
			$duration = $aec->calcDuration($event->start,$event->end,$event->allDay);
			
			$out .= '<li><h3>';
			if ($start_date != $end_date) {
				if ($event->allDay) {
					$out .= $start_date;
					$out .= ' - ' . $end_date;
				} else {
					$out .= $event->start;
					$out .= '<br>' . $event->end;
				}
			} else {
				if ($event->allDay) {
					$out .= $start_date;
					$duration = __('All Day', AEC_PLUGIN_NAME);
				} else {
					$out .= $start_date;
					$out .= '<br>' . $start_time . ' - ' . $end_time;
				}
			}
			$out .= ' <span class="duration">' . $duration . '</span>';

			$out .= '</h3></li>';
			$out .= '<li>' . stripslashes($event->description) . '</li>';
			
			if (!empty($event->venue) || !empty($event->address) ||
				!empty($event->city) || !empty($event->state) || 
				!empty($event->zip) ) {
				$out .= '<li><h3>' . __('Location', AEC_PLUGIN_NAME) . '</h3>';
				$v = array();
				$csz = array();
				if (!empty($event->venue)) $v[] = $event->venue;
				if (!empty($event->address)) $v[] = $event->address;
				if (!empty($event->city)) $csz[] = $event->city;
				if (!empty($event->state)) $csz[] = strtoupper($event->state);
				if (!empty($event->zip)) $csz[] = $event->zip;
				$v[] = implode($csz, ', ');
				$out .= implode($v, '<br>');
				$out .= '</li>';
			}

			if (!empty($event->contact) || !empty($event->contact_info)) {
				$out .= '<li><h3>' . __('Contact Information', AEC_PLUGIN_NAME) . '</h3>';
				if (!empty($event->contact)) $c[] = $event->contact;
				if (!empty($event->contact_info)) $c[] = $event->contact_info;
				$out .= implode($c, '<br>');
				$out .= '</li>';
			}

			if ($event->access || $event->rsvp) {
				$out .= '<hr>';
				if ($event->access) $out .= '<li>' . __('This event is accessible to people with disabilities.', AEC_PLUGIN_NAME) . '</li>';
				if ($event->rsvp) $out .= '<li>' . __('Please register with the contact person for this event.' , AEC_PLUGIN_NAME) . '</li>';				
			}
			
			$org = get_userdata($event->user_id);
			if (!empty($org->organization)) {
					$out .= '<li><small>' . __('Presented by', AEC_PLUGIN_NAME) . ' ';
				if (!empty($org->user_url)) {
					$out .= '<a href="' . $org->user_url . '" target="_blank">' . $org->organization . '</a>';
				} else {
					$out .= $org->organization;
				}
				$out .= '</small></li>';
			}
			
			if (!empty($event->link)) $out .= '<li><a href="' . $event->link . '" class="link cat' . $event->category_id . '" target="_blank">' . __('Event Link', AEC_PLUGIN_NAME) . '</a></li>';

		$out .= '</ul>';

		$categories = $aec->get_categories();
		foreach ($categories as $category) {
			if ($category->id == $event->category_id) $cat = $category->category;
		}
		
		$output = array(
			'title'		=> $event->title . ' (' . $cat . ')',
			'content'	=> $out
		);
		echo json_encode($output);
		exit;
	}
?>