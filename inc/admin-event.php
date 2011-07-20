<?php
	$options = get_option('aec_options');

	if (isset($_POST['event'])) {

		// edit existing event
		if (isset($_POST['event']['id'])) {

			// populate form with content from database
			$event 					= $this->query_event($_POST['event']['id']);
			$event->title 			= $this->render_i18n_data($event->title);
			$event->description 	= $this->render_i18n_data($event->description);
			$event->link 			= $this->render_i18n_data($event->link);
			$event->venue 			= $this->render_i18n_data($event->venue);
			$event->address 		= $this->render_i18n_data($event->address);
			$event->city	 		= $this->render_i18n_data($event->city);
			$event->state		 	= $this->render_i18n_data($event->state);
			$event->zip	 			= $this->render_i18n_data($event->zip);
			$event->contact			= $this->render_i18n_data($event->contact);
			$event->contact_info	= $this->render_i18n_data($event->contact_info);

		// add new event
		} else {
			global $current_user;
			
			// initialize form for new event
			get_currentuserinfo();	// wp data
			$event->id 				= '';
			$event->user_id 		= $current_user->ID;
			$event->title 			= '';
			$event->start 			= $_POST['event']['start'];
			$event->end 			= $_POST['event']['end'];
			$event->allDay 			= $_POST['event']['allDay'];			
			// recurring event placeholder
			// $event->repeat_interval = 0;
			// $event->repeat_end		= '';
			$event->category_id 	= 1;
			$event->description 	= '';
			$event->link 			= '';
			$event->venue		 	= '';
			$event->address		 	= '';
			$event->city 			= '';
			$event->state			= '';
			$event->zip 			= '';
			$event->contact		 	= '';
			$event->contact_info 	= '';
			$event->access			= 0;
			$event->rsvp			= 0;
		}
		// split database formatted datetime value into display formatted date and time values
		$datepicker_format	= ($this->parse_date_format(AEC_WP_DATE_FORMAT)) ? 'd-m-Y' : 'm/d/Y';	// php date format
		$timepicker_format  = ($this->parse_time_format(AEC_WP_TIME_FORMAT)) ? 'H:i' : 'h:i A';		// php time format
		$event->start_date	= $this->date_convert($event->start, AEC_DB_DATETIME_FORMAT, $datepicker_format);
		$event->start_time 	= $this->date_convert($event->start, AEC_DB_DATETIME_FORMAT, $timepicker_format);
		$event->end_date 	= $this->date_convert($event->end, AEC_DB_DATETIME_FORMAT, $datepicker_format);
		$event->end_time 	= $this->date_convert($event->end, AEC_DB_DATETIME_FORMAT, $timepicker_format);
	}

	// populate checkboxes
	$allday_checked					= ($event->allDay) ? 'checked="checked" ' : '';
	$accessible_checked 			= ($event->access) ? 'checked="checked" ' : '';
	$rsvp_checked 					= ($event->rsvp) ? 'checked="checked" ' : '';
?>
	<form method="post" action="" id="event_form" class="aec_form">
	<input type="hidden" name="id" id="id" value="<?php echo $event->id; ?>">
    <input type="hidden" name="user_id" id="user_id" value="<?php echo $event->user_id; ?>">
	<input type="hidden" name="allDay" value="0">
	<input type="hidden" name="access" value="0">
	<input type="hidden" name="rsvp" value="0">
    <ul>
		<li>
			<label><?php _e('Duration', AEC_PLUGIN_NAME); ?><br>
					<input class="auto" type="checkbox" name="allDay" id="allDay" value="1" <?php echo $allday_checked ?>><label class="box"><?php _e('All Day', AEC_PLUGIN_NAME); ?></label></label>
			<ul class="hvv">
				<li>
					<label for="start_date"><?php _e('From', AEC_PLUGIN_NAME); ?></label>
					<input class="auto picker" type="text" name="start_date" id="start_date" size="11" readonly="readonly" value="<?php echo $event->start_date; ?>">
				</li>
				<li>
					<label>&nbsp;</label>
					<input class="auto picker cb" type="text" name="start_time" id="start_time" size="8" readonly="readonly" value="<?php echo strtoupper($event->start_time); ?>">
				</li>
				<li>
					<label for="end_date"><?php _e('To', AEC_PLUGIN_NAME); ?></label>
					<input class="auto picker" type="text" name="end_date" id="end_date" size="11" readonly="readonly" value="<?php echo $event->end_date; ?>">
				</li>
				<li>
					<label>&nbsp;</label>
					<input class="auto picker cb" type="text" name="end_time" id="end_time" size="8" readonly="readonly" value="<?php echo strtoupper($event->end_time); ?>">
				</li>
			</ul>
			<label>&nbsp;</label><span class="duration-message"></span>
		</li>
		<!-- recurring event placeholder
		<li><label><?php //_e('Repeat', AEC_PLUGIN_NAME); ?></label>
			<ul class="hvv">
				<li>
					<label for="repeat_interval"><?php _e('Interval', AEC_PLUGIN_NAME); ?></label>
					<select class="auto" name="repeat_interval" id="repeat_interval">
					<?php				
						/*
						$repeat_options = array('0' => __('None', AEC_PLUGIN_NAME), 
												'1' => __('Daily', AEC_PLUGIN_NAME),
												'7' => __('Weekly', AEC_PLUGIN_NAME));
						foreach ($repeat_options as $option => $value) {
							$selected = ($event->repeat_interval == $option) ? ' selected="selected"' : '';
							print '<option value="' . $option . '">' . $value . '</option>';
						}
						*/
					?>
					</select>
				</li>
				<li>
					<label for="repeat_end"><?php //_e('Until', AEC_PLUGIN_NAME); ?></label>
					<input class="auto picker cb" type="text" name="repeat_end" id="repeat_end" size="11" readonly="readonly" value="<?php //cho $event->repeat_end; ?>">
				</li>
			</ul>
		</li>
		//-->
        <li>
            <label for="title"><?php _e('Title', AEC_PLUGIN_NAME); ?></label>
            <input type="text" name="title" id="title" value="<?php echo $event->title; ?>">
		</li>
		<li>
            <label for="category_id"><?php _e('Type', AEC_PLUGIN_NAME); ?></label>
			<select class="large" name="category_id" id="category_id" >
		<?php
			$categories = $this->query_categories();
			foreach ($categories as $category) {
				$category_selected = ($category->id == $event->category_id) ? ' selected="selected"' : '';
				echo '<option value="' . $category->id . '"'. $category_selected . '>' . $this->render_i18n_data($category->category) . '</option>';
			}
            ?>
			</select>
        </li>
		<li>
			<label for="venue"><?php _e('Venue', AEC_PLUGIN_NAME); ?></label>
			<input class="" type="text" name="venue" id="venue" value="<?php echo $event->venue; ?>">
		</li>
		<li>
			<label><?php _e('Address', AEC_PLUGIN_NAME); ?></label>
			<ul class="hvv">
				<li>
					<label for="address"><?php _e('Neighborhood or Street Address', AEC_PLUGIN_NAME); ?></label>
					<input class="" type="text" name="address" id="address" value="<?php echo $event->address; ?>">
				</li>
				<li class="cb">
					<label for="city"><?php _e('City', AEC_PLUGIN_NAME); ?></label>
					<input class="auto" type="text" name="city" id="city" size="22" value="<?php echo $event->city; ?>">
				</li>
				<li>
					<label for="state"><?php _e('State', AEC_PLUGIN_NAME); ?></label>
					<input class="auto" type="text" name="state" id="state" size="3" maxlength="2" value="<?php echo $event->state; ?>">
				</li>
				<li>
					<label for="zip"><?php _e('Postal Code', AEC_PLUGIN_NAME); ?></label>
					<input class="auto" type="text" name="zip" id="zip" size="10" maxlength="10" value="<?php echo $event->zip; ?>">
				</li>
			</ul>
        </li>
        <li>
			<label for="link"><?php _e('Website Link', AEC_PLUGIN_NAME); ?></label>
            <input type="text" name="link" id="link" class="wide" value="<?php echo $event->link; ?>">
		</li>
		<li>
            <label for="description"><?php _e('Description', AEC_PLUGIN_NAME); ?></label>
            <textarea class="wide" name="description" id="description"><?php echo $event->description; ?></textarea>
        </li>
        <li>
			<label><?php _e('Contact Person', AEC_PLUGIN_NAME); ?></label>
			<ul class="hvv">
				<li>
					<label for="contact"><?php _e('Name', AEC_PLUGIN_NAME); ?></label>
					<input class="semi" type="text" name="contact" id="contact" value="<?php echo $event->contact; ?>">
				</li>
				<li>
					<label for="contact_info"><?php _e('Phone or Email Address', AEC_PLUGIN_NAME); ?></label>
					<input class="semi" type="text" name="contact_info" id="contact_info" value="<?php echo $event->contact_info; ?>">
				</li>
			</ul>
		</li>
		<?php if ($options['accessible']) { ?>
		<li>
			<label></label>
			<input type="checkbox" value="1" name="access" id="access" <?php echo $accessible_checked; ?>/>
			<label for="access" class="box"><?php _e('This event is accessible to people with disabilities.', AEC_PLUGIN_NAME); ?></label>
		</li>
		<?php
			}
			if ($options['rsvp']) {
		?>
		<li>
			<label></label>
			<input type="checkbox" value="1" name="rsvp" id="rsvp" <?php echo $rsvp_checked; ?>/>
			<label for="rsvp" class="box"><?php _e('Please register with the contact person for this event.', AEC_PLUGIN_NAME); ?></label>
		</li>
		<?php } ?>
        <li class="buttons">
			<input type="button" name="cancel_event" value="<?php _e('Cancel', AEC_PLUGIN_NAME); ?>" class="button-secondary" id="cancel_event">
			<?php if ($event->id) { ?>
			<span class="fl"><input type="button" name="delete_event" value="<?php _e('Delete', AEC_PLUGIN_NAME); ?>" class="button" id="delete_event"></span>
			<input type="button" name="update_event" value="<?php _e('Update', AEC_PLUGIN_NAME); ?>" class="button-primary" id="update_event">
			<?php } else { ?>
			<input type="button" name="add_event" value="<?php _e('Add', AEC_PLUGIN_NAME); ?>" class="button-primary" id="add_event">
			<?php } ?>
        </li>
		<?php
		if ($event->id) {
			$is_admin = (current_user_can(AEC_DOMAIN . 'manage_calendar') == true) ? 1 : 0;
			if ($is_admin) {
				$first_name 	= get_user_meta($event->user_id, 'first_name', true);
				$last_name		= get_user_meta($event->user_id, 'last_name', true);
				$organization 	= (isset($organization)) ? ' (' . get_user_meta($event->user_id, 'organization', true) . ')' : '';
				$out 			= '<li><span>' . __('Created by', AEC_PLUGIN_NAME) . ': ';
				$author 		= ($event->user_id > 0) ? $first_name . ' ' . $last_name . $organization : __('Ajax Event Calendar', AEC_PLUGIN_NAME);

				$out 			.= '<strong>' . $author . '</strong></span></li>';
				echo $out;
			}
		}
		?>
    </ul>
</form>