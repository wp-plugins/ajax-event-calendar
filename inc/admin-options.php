<?php
	$city 				= __('City', AEC_NAME);
	$state 				= __('State', AEC_NAME);
	$postal				= __('Postal Code', AEC_NAME);
	$timeslot_opts		= array(5, 10, 15, 30, 60);
	$format_opts 		= array("{{$city}}, {{$state}} {{$postal}}", "{{$postal}} {{$city}}");
	$field_opts2		= array( __('Hide', AEC_NAME), __('Display', AEC_NAME));
	$field_opts3		= array( __('Hide', AEC_NAME), __('Display', AEC_NAME), __('Require', AEC_NAME));

	echo "<div class='wrap'>\n";
	echo "<a href='http://eranmiller.com' target='_blank'><div id='em-icon' style='background:url(". AEC_URL ."css/images/em-icon-32.png) no-repeat' class='icon32'></div></a>\n";
	echo $this->add_wrap(__('Ajax Event Calendar Options', AEC_NAME), "<h2>", "</h2>");

	if((isset($_GET['updated']) && $_GET['updated'] == 'true') ||
	   (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true')){
		echo $this->add_wrap(__('Settings updated'), '<div id="message" style="width:94%" class="message updated"><p><strong>', '.</strong></p></div>');
	}

	echo "<div class='postbox-container' style='width:70%'>\n";
	echo "<form method='post' action='options.php' class='aec_form'>\n";
	settings_fields('aec_plugin_options');
	$aec_options = get_option('aec_options');

	$form = $this->add_hidden_field('title', 2);	// preserves event title as a required field.
	$form .= $this->add_wrap(__("Date Format, Time Format, and Week Starts On settings are located", AEC_NAME), "<span class='fr helptip round5'>", " ");
	$form .= $this->add_wrap(__("here", AEC_NAME), "<a href='" . ADMIN_URL(). "options-general.php'>", "</a>.</span>");
	$form .= $this->add_checkbox_field('show_weekends', __('Display calendar weekends.', AEC_NAME));
	$form .= $this->add_checkbox_field('show_map_link', __('Display <code>View Map</code> link on event details (uses populated address fields).', AEC_NAME));
	$form .= $this->add_checkbox_field('menu', __('Display <code>Add Events</code> link on the front-end calendar.', AEC_NAME));
	$form .= $this->add_checkbox_field('make_links', __('Make URLs entered in the description field into clickable links.', AEC_NAME));
	$form .= $this->add_checkbox_field('popup_links', __('Make links on the <code>Event Detail</code> page open in a new window.', AEC_NAME));
	$form .= $this->add_checkbox_field('limit', __('Prevent users from adding or editing expired events.', AEC_NAME));
	$form .= $this->add_checkbox_field('scroll', __('Activate mouse wheel navigation for the administrative calendar view.', AEC_NAME));
	$form .= $this->add_text_field('filter_label', __('Category filter label', AEC_NAME));
	$form .= $this->add_select_field('addy_format', __('Address format', AEC_NAME), $format_opts);
	$form .= $this->add_select_field('step_interval', __('Timepicker interval', AEC_NAME), $timeslot_opts);
	
	$form .= $this->add_wrap(__('Hide, display or require form fields.  Hidden fields do not appear in the event form.', AEC_NAME), '<p>', '</p>');
	$form .= $this->add_select_field('venue', __('Venue', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('address', __('Neighborhood or Street Address', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('city', __('City', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('state', __('State', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('zip', __('Postal Code', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('country', __('Country', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('link', __('Event Link', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('description', __('Description', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('contact', __('Contact Name', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('contact_info', __('Contact Information', AEC_NAME), $field_opts3);
	$form .= $this->add_select_field('accessible', __('This event is accessible to people with disabilities.', AEC_NAME), $field_opts2);
	$form .= $this->add_select_field('rsvp', __('Please register with the contact person for this event.', AEC_NAME), $field_opts2);
	
	$out  = $this->add_panel(__('Modify calendar and form options then click <code>Save Changes</code> below.', AEC_NAME), $form);
	$out .= $this->add_checkbox_field('reset', __('Reset all settings on Save.', AEC_NAME));
	$out .= $this->add_wrap("<input name='Submit' type='submit' class='button-primary auto' value='" . esc_attr__('Save Changes', AEC_NAME) . "' />", "<p class='submit'>", "</p>");
	$out .= "</form>\n";
	$out .= "</div>\n";
	
	echo $out;
	echo $this->add_sidebar();
	echo "</div>\n";
?>