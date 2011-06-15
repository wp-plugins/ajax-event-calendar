<?php
	// IMPORTANT: both these lines are required
	require_once("../../../../wp-blog-header.php");
	header("HTTP/1.1 200 OK");
	
	if (isset($_POST['id'])) {
		function calcDuration($from, $end, $allday){
			$timestamp = strtotime($end) - strtotime($from);
			$days = floor($timestamp/(60*60*24)); $timestamp%=60*60*24;
			$hrs = floor($timestamp/(60*60)); $timestamp%=60*60;
			$mins = floor($timestamp/60); $secs=$timestamp%60;
			
			$out = array();
			if ($allday) $days += 1;
			if ($days >= 1) { array_push($out, $days.' day'.plural($days)); }
			if ($hrs >= 1) { array_push($out, $hrs.' hour'.plural($hrs)); }
			if ($mins >= 1) { array_push($out, $mins.' minute'.plural($mins)); }
			return implode(', ', $out);
		}

		function plural($value){
			return ($value == 1) ? '' : 's';
		}
		$event = $aec->get_event($_POST['id']);

		$out = '<ul>';

			// Split date/time fields
			list($start_date, $start_time) = str_split($event->start, 10);
			list($end_date, $end_time) = str_split($event->end, 10);
			
			$start_time = trim($start_time);
			$end_time = trim($end_time);
			
			$out .= '<li><h3>';
			if ($event->allDay) {
				$out .= $start_date;
				if ($start_date != $end_date) $out .= ' - ' . $end_date;
				$out .= ' <span class="duration">' . __('All Day', AEC_PLUGIN_NAME) . '</span>';
			} else {
				$out .= $event->start;
				$out .= ($start_date != $end_date) ? ' - ' . $event->end : ' - ' . $end_time;
				$out .= ' <span class="duration">' . calcDuration($event->start,$event->end,$event->allDay) . '</span>';
			}
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