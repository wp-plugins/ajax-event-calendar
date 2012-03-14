<?php
	$city 				= __('City', AEC_NAME);
	$state 				= __('State', AEC_NAME);
	$postal				= __('Postal Code', AEC_NAME);
	$timeslots			= array(5, 10, 15, 30, 60);
	$timeslot_opts		= array();
	foreach($timeslots as $timeslot){
		array_push($timeslot_opts, $timeslot . ' ' . __('Minutes', AEC_NAME));
	}
	$format_opts 		= array("{{$city}}, {{$state}} {{$postal}}", "{{$postal}} {{$city}}");
	$field_opts2		= array( __('Hide', AEC_NAME), __('Display', AEC_NAME));
	$field_opts3		= array( __('Hide', AEC_NAME), __('Display', AEC_NAME), __('Require', AEC_NAME));

	echo "<div class='wrap'>\n";
	echo "<a href='http://". AEC_HOMEPAGE . "' target='_blank'><span class='em-icon icon32'></span></a>\n";
	echo $this->add_wrap(__('Ajax Event Calendar Options', AEC_NAME), "<h2>", "</h2>");

	if ((isset($_GET['updated']) && $_GET['updated'] == 'true') ||
	   (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true')) {
		echo $this->add_wrap(__('Settings Updated', AEC_NAME), '<div id="message" style="width:94%" class="message updated"><p><strong>', '.</strong></p></div>');
	}

	echo "<div class='postbox-container' style='width:65%'>\n";
	echo "<form method='post' action='options.php' class='aec-form'>\n";
	settings_fields('aec_plugin_options');
	$aec_options = get_option('aec_options');

	$form = $this->add_hidden_field('title', 2);		// preserves event title as a required field
	$form .= $this->add_hidden_field('filter_label');	// preserves category filter label value
	
	$form .= $this->add_checkbox_field('show_weekends', __('Display calendar weekends.', AEC_NAME));
	$form .= $this->add_checkbox_field('limit', __('Prevent users from adding or editing expired events.', AEC_NAME));
	$form .= $this->add_checkbox_field('menu', __('Display the Add Events link on the front-end calendar.', AEC_NAME));
	$form .= $this->add_checkbox_field('show_map_link', __('Display the View Map link on event details.', AEC_NAME));
	$form .= $this->add_checkbox_field('make_links', __('Convert description field URLs into  clickable links.', AEC_NAME));
	$form .= $this->add_checkbox_field('popup_links', __('Open Event Detail links in a new browser window.', AEC_NAME));
	$form .= $this->add_checkbox_field('scroll', __('Navigate administrative calendar with mousewheel.', AEC_NAME));
	$form .= $this->add_wrap(__("Date Format, Time Format, and Week Start", AEC_NAME), "<span class='fr helptip round5'>", " ");
	$form .= $this->add_wrap(__("settings are located here", AEC_NAME), "<a href='" . ADMIN_URL(). "options-general.php'>", "</a>.</span>");
	$form .= $this->add_select_field('addy_format', __('Address format', AEC_NAME), $format_opts);
	$form .= $this->add_select_field('step_interval', __('Event timepicker interval', AEC_NAME), $timeslot_opts, $timeslots);
	
	$form .= $this->add_wrap(__('Hide, display or require form fields.  Hidden fields do not appear in the event form.', AEC_NAME), '<p>', '</p>');
	$form .= $this->add_select_field('venue', __('Venue', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('address', __('Neighborhood or Street Address', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('city', __('City', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('state', __('State/Province', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('zip', __('Postal Code', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('country', __('Country', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('link', __('Event Link', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('description', __('Description', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('contact', __('Contact Name', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('contact_info', __('Contact Information', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('accessible', __('This event is accessible to people with disabilities.', AEC_NAME), $field_opts2);
	$form .= $this->add_select_field('rsvp', __('Please register with the contact person for this event.', AEC_NAME), $field_opts2);
	
	$out  = $this->add_panel(__('Modify calendar and form options then click Save Changes below.', AEC_NAME), $form);
	$out .= $this->add_checkbox_field('reset', __('Reset all settings on Save.', AEC_NAME));
	$out .= $this->add_wrap("<input name='Submit' type='submit' class='button-primary auto' value='" . esc_attr(__('Save Changes', AEC_NAME)) . "' />", "<p class='submit'>", "</p>");
	$out .= "</form>\n";
	$out .= "</div>\n";
	
	echo $out;
	echo $this->add_sidebar();
	echo "</div>\n";
?>