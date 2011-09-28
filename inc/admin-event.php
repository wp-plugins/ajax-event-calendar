<?php
	$aec_options = get_option('aec_options');
	if (isset($_POST['event'])) {
		// edit existing event
		if (isset($_POST['event']['id'])) {
			// populate form with content from database
			$event 					= $this->db_query_event($_POST['event']['id']);
			$event->title 			= $this->render_i18n_data($event->title);
			$event->description 	= $this->render_i18n_data($event->description);
			$event->link 			= $this->render_i18n_data($event->link);
			$event->venue 			= $this->render_i18n_data($event->venue);
			$event->address 		= $this->render_i18n_data($event->address);
			$event->city	 		= $this->render_i18n_data($event->city);
			$event->state		 	= $this->render_i18n_data($event->state);
			$event->zip	 			= $this->render_i18n_data($event->zip);
			$event->country			= $this->render_i18n_data($event->country);
			$event->contact			= $this->render_i18n_data($event->contact);
			$event->contact_info	= $this->render_i18n_data($event->contact_info);

		// add new event
		}else{
			global $current_user;
			// initialize form for new event
			get_currentuserinfo();	// wp data
			$event->id 				= '';
			$event->user_id 		= $current_user->ID;
			$event->title 			= '';
			$event->start 			= $_POST['event']['start'];
			$event->end 			= $_POST['event']['end'];
			$event->allDay 			= $_POST['event']['allDay'];
			$event->repeat_freq		= 0;
			$event->repeat_int		= 0;
			$event->repeat_end		= $_POST['event']['end'];
			$event->category_id 	= 1;
			$event->description 	= '';
			$event->link 			= '';
			$event->venue		 	= '';
			$event->address		 	= '';
			$event->city 			= '';
			$event->state			= '';
			$event->zip 			= '';
			$event->country			= '';
			$event->contact		 	= '';
			$event->contact_info 	= '';
			$event->access			= 0;
			$event->rsvp			= 0;
		}

		// split database formatted datetime value into display formatted date and time values
		list($event->start_date, $event->start_time) = $this->split_datetime($event->start);
		list($event->end_date, $event->end_time) = $this->split_datetime($event->end);
		$event->repeat_end = $this->convert_date($event->repeat_end, AEC_DB_DATETIME_FORMAT, $this->get_wp_date_format());
	}

	// populate checkboxes
	$allday_checked		= ($event->allDay) ? 'checked="checked" ' : '';
	$accessible_checked = ($event->access) ? 'checked="checked" ' : '';
	$rsvp_checked 		= ($event->rsvp) ? 'checked="checked" ' : '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Event Form</title>
</head>
<body>
	
	<form method="post" action="<?php echo __FILE__; ?>" id="event-form" class="aec-form">
	<input type="hidden" name="id" id="id" value="<?php echo $event->id; ?>">
    <input type="hidden" name="user_id" id="user_id" value="<?php echo $event->user_id; ?>">
	<input type="hidden" name="allDay" value="0">
	<input type="hidden" name="venue" value="">
	<input type="hidden" name="address" value="">
	<input type="hidden" name="city" value="">
	<input type="hidden" name="state" value="">
	<input type="hidden" name="zip" value="">
	<input type="hidden" name="country" value="">
	<input type="hidden" name="link" value="">
	<input type="hidden" name="description" value="">
	<input type="hidden" name="contact" value="">
	<input type="hidden" name="contact_info" value="">
	<input type="hidden" name="repeat_freq" value="0">
	<input type="hidden" name="repeat_int" value="0">
	<input type="hidden" name="repeat_end" value="">
	<input type="hidden" name="access" value="0">
	<input type="hidden" name="rsvp" value="0">
	<ul>
		<li>
			<label for="allDay">
				<?php _e('Duration', AEC_NAME); ?>
				<span>
					<input class="auto" type="checkbox" name="allDay" id="allDay" value="1" <?php echo $allday_checked ?>>
					<label class="box"><?php _e('All Day', AEC_NAME); ?></label>
				</span>
			</label>
			<ul class="hvv">
				<li>
					<label for="start_date"><?php _e('From', AEC_NAME); ?></label>
					<input class="auto picker" type="text" name="start_date" id="start_date" size="11" readonly="readonly" value="<?php echo $event->start_date; ?>">
				</li>
				<li>
					<label>&nbsp;</label>
					<input class="auto picker cb" type="text" name="start_time" id="start_time" size="8" readonly="readonly" value="<?php echo strtoupper($event->start_time); ?>">
				</li>
				<li>
					<label for="end_date"><?php _e('To', AEC_NAME); ?></label>
					<input class="auto picker" type="text" name="end_date" id="end_date" size="11" readonly="readonly" value="<?php echo $event->end_date; ?>">
				</li>
				<li>
					<label>&nbsp;</label>
					<input class="auto picker cb" type="text" name="end_time" id="end_time" size="8" readonly="readonly" value="<?php echo strtoupper($event->end_time); ?>">
				</li>
			</ul>
		</li>
		<li>
			<label>&nbsp;</label>
			<span class="duration"></span>
		</li>
		<li><label><?php _e('Repeats', AEC_NAME); ?></label>
			<ul class="hhh">
				<li>
					<select class="select auto" name="repeat_freq" id="repeat_freq">
					<?php
						$every = __('Every', AEC_NAME);
						$repeat_options = array(__('Never', AEC_NAME), $every);
						$frequency = 2;
						while ($frequency < 31) {
							array_push($repeat_options, "{$every} $frequency");
							$frequency++;
						}
						foreach ($repeat_options as $option => $value) {
							$selected = ($event->repeat_freq == $option) ? ' selected="selected"' : '';
							echo "<option value='{$option}'{$selected}>{$value}</option>\n";
						}
					?>
					</select>
				</li>
				<li>
					<select class="select auto" name="repeat_int" id="repeat_int">
					<?php
						$repeat_options = array(__('Day(s)', AEC_NAME),
												__('Week(s)', AEC_NAME),
												__('Month(s)', AEC_NAME),
												__('Year(s)', AEC_NAME));
						foreach ($repeat_options as $option => $value) {
							$selected = ($event->repeat_int == $option) ? ' selected="selected"' : '';
							echo "<option value='{$option}'{$selected}>{$value}</option>\n";
						}
					?>
					</select>
				</li>
				<li><label for="repeat_end" id="repeat_label" class="auto"><?php _e('Until this date', AEC_NAME); ?></label>
					<input class="auto picker" type="text" name="repeat_end" id="repeat_end" size="11" readonly="readonly" value="<?php echo $event->repeat_end; ?>">
				</li>
			</ul>
		</li>
        <li>
            <label for="title"><?php _e('Title', AEC_NAME); ?></label>
            <input type="text" name="title" id="title" class="wide" value="<?php echo $event->title; ?>">
		</li>
		<li>
            <label for="category_id"><?php _e('Category', AEC_NAME); ?></label>
			<select class="wide" name="category_id" id="category_id" >
		<?php
			$categories = $this->db_query_categories();
			foreach ($categories as $category) {
				$category_selected = ($category->id == $event->category_id) ? ' selected="selected"' : '';
				echo '<option value="' . $category->id . '"'. $category_selected . '>' . $this->render_i18n_data($category->category) . '</option>';
			}
            ?>
			</select>
        </li>
		<?php if ($aec_options['venue'] > 0) { ?>
		<li>
			<label for="venue"><?php _e('Venue', AEC_NAME); ?></label>
			<input class="wide" type="text" name="venue" id="venue" value="<?php echo $event->venue; ?>">
		</li>
		<?php
			}
			if ($aec_options['address'] > 0 || $aec_options['city'] > 0 || $aec_options['state'] > 0 || $aec_options['zip'] > 0) { ?>
		<li>
			<label><?php _e('Address', AEC_NAME); ?></label>
			<ul class="hvv">
				<?php if ($aec_options['address'] > 0) { ?>
				<li>
					<label for="address"><?php _e('Neighborhood or Street Address', AEC_NAME); ?></label>
					<input class="wide" type="text" name="address" id="address" value="<?php echo $event->address; ?>">
				</li>
				<?php
					}
					if ($aec_options['city'] > 0) { ?>
				<li>
					<label for="city"><?php _e('City', AEC_NAME); ?></label>
					<input class="auto" type="text" name="city" id="city" size="20" value="<?php echo $event->city; ?>">
				</li>
				<?php
					}
					if ($aec_options['state'] > 0) { ?>
				<li>
					<label for="state"><?php _e('State/Province', AEC_NAME); ?></label>
					<input class="auto" type="text" name="state" id="state" size="10" maxlength="50" value="<?php echo $event->state; ?>">
				</li>
				<?php
					}
					if ($aec_options['zip'] > 0) { ?>
				<li>
					<label for="zip"><?php _e('Postal Code', AEC_NAME); ?></label>
					<input class="auto" type="text" name="zip" id="zip" size="10" maxlength="10" value="<?php echo $event->zip; ?>">
				</li>
				<?php
					}
					if ($aec_options['country'] > 0) { ?>
				<li>
					<label for="country"><?php _e('Country', AEC_NAME); ?></label>
					<input class="auto" type="text" name="country" id="country" size="10" maxlength="50" value="<?php echo $event->country; ?>">
				</li>
				<?php } ?>
			</ul>
        </li>
		<?php
			}
			if ($aec_options['link'] > 0) { ?>
        <li>
			<label for="link"><?php _e('Website Link', AEC_NAME); ?></label>
            <input type="text" name="link" id="link" class="wide" value="<?php echo $event->link; ?>">
		</li>
		<?php
			}
			if ($aec_options['description'] > 0) { ?>
		<li>
            <label for="description"><?php _e('Description', AEC_NAME); ?></label>
            <textarea class="wide" name="description" id="description"><?php echo $event->description; ?></textarea>
        </li>
        <?php
			}
			if ($aec_options['contact'] > 0 || $aec_options['contact_info']) { ?>
		<li>
			<label><?php _e('Contact Person', AEC_NAME); ?></label>
			<ul class="hvv">
				<?php if ($aec_options['contact'] > 0) { ?>
				<li>
					<label for="contact"><?php _e('Name', AEC_NAME); ?></label>
					<input class="semi" type="text" name="contact" id="contact" value="<?php echo $event->contact; ?>">
				</li>
				<?php
					}
					if ($aec_options['contact_info'] > 0) { ?>
				<li>
					<label for="contact_info"><?php _e('Phone or Email Address', AEC_NAME); ?></label>
					<input class="semi" type="text" name="contact_info" id="contact_info" value="<?php echo $event->contact_info; ?>">
				</li>
				<?php } ?>
			</ul>
		</li>
		<?php
			}
			if ($aec_options['accessible']) { ?>
		<li>
			<label></label>
			<input type="checkbox" value="1" name="access" id="access" <?php echo $accessible_checked; ?>/>
			<label for="access" class="box"><?php _e('This event is accessible to people with disabilities.', AEC_NAME); ?></label>
		</li>
		<?php
			}
			if ($aec_options['rsvp']) { ?>
		<li>
			<label></label>
			<input type="checkbox" value="1" name="rsvp" id="rsvp" <?php echo $rsvp_checked; ?>/>
			<label for="rsvp" class="box"><?php _e('Please register with the contact person for this event.', AEC_NAME); ?></label>
		</li>
		<?php } ?>
        <li class="buttons">
			<input type="button" name="cancel_event" value="<?php _e('Cancel', AEC_NAME); ?>" class="button-secondary" id="cancel_event">
			<?php if ($event->id) { ?>
			<span class="fl"><input type="button" name="delete_event" value="<?php _e('Delete', AEC_NAME); ?>" class="button" id="delete_event"></span>
			<input type="button" name="copy_event" value="<?php _e('Copy', AEC_NAME); ?>" class="button" id="copy_event">
			<input type="button" name="update_event" value="<?php _e('Update', AEC_NAME); ?>" class="button-primary" id="update_event">
			<?php } else { ?>
			<input type="button" name="add_event" value="<?php _e('Add', AEC_NAME); ?>" class="button-primary" id="add_event">
			<?php } ?>
        </li>
		<?php
		if ($event->id) {
			$is_admin = (current_user_can('aec_manage_calendar') == true) ? 1 : 0;
			if ($is_admin) {
				$first_name 	= get_user_meta($event->user_id, 'first_name', true);
				$last_name		= get_user_meta($event->user_id, 'last_name', true);
				$organization 	= (isset($organization)) ? ' (' . get_user_meta($event->user_id, 'organization', true) . ')' : '';
				$out 			= '<li><span>' . __('Created by', AEC_NAME) . ': ';
				$author 		= ($event->user_id > 0) ? "{$first_name} {$last_name} {$organization}" : __('Ajax Event Calendar', AEC_NAME);

				$out 			.= '<strong>' . $author . '</strong></span></li>';
				echo $out;
			}
		}
		?>
    </ul>
</form>
</body>
</html>