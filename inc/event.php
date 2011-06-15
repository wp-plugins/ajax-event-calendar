<?php
	// IMPORTANT: both these lines are required
	require_once("../../../../wp-blog-header.php");
	header("HTTP/1.1 200 OK");
	
	$options = get_option('aec_options');
	
	// Add or Edit events
	if (isset($_POST['action'])) {
		// process add/edit
		$aec->process_event($_POST);
	} else {
		foreach ($options as $option => $value) {
			if ($value == 2) {
				$aec->add_required_field($option);
			}
		}

		// edit event details
		if (isset($_POST['event'])) {
			if (isset($_POST['event']['id'])) {
				// Populate form with content from database
				$event = $aec->get_event($_POST['event']['id']);
			} else {
				// Initialize form for new event
				$event = $aec->init_form($_POST['event']['start'],
										$_POST['event']['end'],
										$_POST['event']['allDay'],
										$current_user->ID
									);
			}
		}

		// Split date/time into form fields
		list($start_date, $start_time) = str_split($event->start, 10);
		list($end_date, $end_time) = str_split($event->end, 10);

		// Populate Checkboxes
		$allday_checked = ($event->allDay) ? 'checked="checked" ' : '';
		$accessible_checked = ($event->access) ? 'checked="checked" ' : '';
		$rsvp_checked = ($event->rsvp) ? 'checked="checked" ' : '';
?>
	<form method="post" action="" id="event_form" class="aec_form">
	<input type="hidden" name="id" id="id" value="<?php echo $event->id; ?>" />
    <input type="hidden" name="user_id" id="user_id" value="<?php echo $event->user_id; ?>" />
    <ul>
		<li>
			<label><?php _e('Duration', AEC_PLUGIN_NAME); ?>
			<span class="duration"></span>
			</label>
			<ul class="hvv">
				<li>
					<label for="start_date"><?php _e('From', AEC_PLUGIN_NAME); ?></label>
					<input class="auto picker" type="text" name="start_date" id="start_date" size="11" readonly="readonly" value="<?php echo $start_date; ?>" />
					<input class="auto cb" type="text" name="start_time" id="start_time" size="8" value="<?php echo trim($start_time); ?>" />
				</li>
				<li>
					<label for="end_date"><?php _e('To', AEC_PLUGIN_NAME); ?></label>
					<input class="auto picker" type="text" name="end_date" id="end_date" size="11" readonly="readonly" value="<?php echo $end_date; ?>" />
					<input class="auto cb" type="text" name="end_time" id="end_time" size="8" value="<?php echo trim($end_time); ?>" />
				</li>
				<li>
					<label>&nbsp;</label>
					<input type="hidden" name="allDay" value="0" />
					<input class="auto" type="checkbox" name="allDay" id="allDay" value="1" <?php echo $allday_checked ?> />
					<label for="allDay" class="box"><?php _e('All Day', AEC_PLUGIN_NAME); ?></label>
				</li>
			</ul>
		</li>
        <li>
            <label for="title"><?php _e('Title', AEC_PLUGIN_NAME); ?></label>
            <input type="text" name="title" id="title" value="<?php echo $event->title; ?>" />
		</li>
		<li>
            <label for="category_id"><?php _e('Type', AEC_PLUGIN_NAME); ?></label>
            <select class="large" name="category_id" id="category_id" >
            <?php
				$categories = $aec->get_categories();
				foreach ($categories as $category) {
					$category_selected = ($category->id == $event->category_id) ? ' selected="selected"' : '';
					print '<option value="' . $category->id . '"'. $category_selected . '>' . $category->category . '</option>';
				}
            ?>
            </select>
        </li>
		<li>
			<label for="venue"><?php _e('Venue', AEC_PLUGIN_NAME); ?></label>
			<input class="" type="text" name="venue" id="venue" value="<?php echo $event->venue; ?>" />
		</li>
		<li>
			<label><?php _e('Address', AEC_PLUGIN_NAME); ?></label>
			<ul class="hvv">
				<li>
					<label for="address"><?php _e('Neighborhood or Street Address', AEC_PLUGIN_NAME); ?></label>
					<input class="" type="text" name="address" id="address" value="<?php echo $event->address; ?>" />
				</li>
				<li class="cb">
					<label for="city"><?php _e('City', AEC_PLUGIN_NAME); ?></label>
					<input class="auto" type="text" name="city" id="city" size="22" value="<?php echo $event->city; ?>" />
				</li>
				<li>
					<label for="state"><?php _e('State', AEC_PLUGIN_NAME); ?></label>
					<input class="auto" type="text" name="state" id="state" size="3" maxlength="2" value="<?php echo $event->state; ?>" />
				</li>
				<li>
					<label for="zip"><?php _e('Zip', AEC_PLUGIN_NAME); ?></label>
					<input class="auto" type="text" name="zip" id="zip" size="5" maxlength="5" value="<?php echo $event->zip; ?>" />
				</li>
			</ul>
        </li>
        <li>
			<label for="link"><?php _e('Website Link', AEC_PLUGIN_NAME); ?></label>
            <input type="text" name="link" id="link" class="wide" value="<?php echo $event->link; ?>" />
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
					<input class="semi" type="text" name="contact" id="contact" value="<?php echo $event->contact; ?>" />
				</li>
				<li>
					<label for="contact_info"><?php _e('Phone or Email Address', AEC_PLUGIN_NAME); ?></label>
					<input class="semi" type="text" name="contact_info" id="contact_info" value="<?php echo $event->contact_info; ?>" />
				</li>
			</ul>
		</li>
		<?php if ($options['accessible']) { ?>
		<li>
			<label></label>
			<input type="hidden" name="access" value="0" />
			<input type="checkbox" value="1" name="access" id="access" <?php echo $accessible_checked; ?>/>
			<label for="access" class="box"><?php _e('This event is accessible to people with disabilities.', AEC_PLUGIN_NAME); ?></label>
		</li>
		<?php 
			} 
			if ($options['rsvp']) { 
		?>
		<li>
			<label></label>
			<input type="hidden" name="rsvp" value="0" />
			<input type="checkbox" value="1" name="rsvp" id="rsvp" <?php echo $rsvp_checked; ?>/>
			<label for="rsvp" class="box"><?php _e('Please register with the contact person for this event.', AEC_PLUGIN_NAME); ?></label>
		</li>
		<?php } ?>
        <li class="buttons">
			<input type="button" name="cancel" value="<?php _e('Cancel', AEC_PLUGIN_NAME); ?>" class="button-secondary" id="cancel" />
			<?php if ($event->id) { ?>
			<span class="fl"><input type="button" name="delete" value="<?php _e('Delete', AEC_PLUGIN_NAME); ?>" class="button" id="delete" /></span>
			<input type="button" name="update" value="<?php _e('Update', AEC_PLUGIN_NAME); ?>" class="button-primary" id="update" />
			<?php } else { ?>
			<input type="button" name="add" value="<?php _e('Add', AEC_PLUGIN_NAME); ?>" class="button-primary" id="add" />
			<?php } ?>
        </li>
		<?php if ($event->id) { ?>
		<li>
		<?php
			$is_admin = (current_user_can('manage_options') == true) ? 1 : 0;
			if ($is_admin) {
				$first_name = get_user_meta($event->user_id, 'first_name', true);
				$last_name = get_user_meta($event->user_id, 'last_name', true);
				$organization = (isset($organization)) ? '(' . get_user_meta($event->user_id, 'organization', true) . ')' : '';
				$out = '<span>Created by: ';
				$author = 'Ajax Event Calendar';
				if ($event->user_id > 0) {
					$author = $first_name . ' ' . $last_name . $organization;
				}
				$out .= '<strong>' . $author . '</strong>';
				echo $out . '</span>';
			}
		?>
		</li>
		<?php } ?>
    </ul>
</form>
<script type='text/javascript'>
jQuery().ready(function() {
	var dates = jQuery('#start_date, #end_date').datepicker({
<?php if ($options['limit']) { ?>
		minDate: '+1d',
		maxDate: '+1y',
<?php } ?>
		showOn: "button",
		buttonImage: "<?php echo AEC_PLUGIN_URL; ?>css/images/calendar.png",
		buttonImageOnly: true,
		showButtonPanel: true,
		onSelect: function(selectedDate) {
			var option = (this.id == 'start_date') ? 'minDate' : 'maxDate',
				instance = jQuery(this).data('datepicker'),
				date = jQuery.datepicker.parseDate(instance.settings.dateFormat || 
											jQuery.datepicker._defaults.dateFormat,
											selectedDate, instance.settings);
			dates.not(this).datepicker('option', option, date);
			validateForm();
		}
	});

	var times = jQuery('#start_time,#end_time').timePicker({ 
		step: 30,
		show24Hours: false,
		separator:':'
	});

	validateForm();

	jQuery('#event_form').change(function() {
		validateForm();
	});
	
	jQuery('#cancel').click(function(e) {
		e.preventDefault();
		jQuery('.time-picker').remove();
		jQuery.modal.close();
	});
	
	jQuery('#add').click(function(e) {
		e.preventDefault();
		if (validateForm()) {
			jQuery.post('<?php echo AEC_PLUGIN_URL; ?>inc/event.php', {'event':jQuery('#event_form').serialize(),'action':'add'}, function(data){
				if (data) {
					var calendar = jQuery('#aec-calendar').fullCalendar('renderEvent',
					{
						id: data.id,
						title: data.title,
						allDay: data.allDay,
						start: data.start,
						end: data.end,
						className: data.className
					}, false);
					//calendar.fullCalendar('unselect');
					jQuery.jGrowl('<strong>' + data.title + '</strong> <?php _e('has been added.', AEC_PLUGIN_NAME); ?>', { header: '<?php _e('Success!', AEC_PLUGIN_NAME); ?>' });
				}
			}, 'json');
			jQuery('.time-picker').remove();
			jQuery.modal.close();
		}
	});
	
	jQuery('#update').click(function(e) {
		e.preventDefault();
		if (validateForm()) {
			jQuery.post('<?php echo AEC_PLUGIN_URL; ?>inc/event.php', {'event':jQuery('#event_form').serialize(),'action':'update'}, function(data){
				if (data) {
					var e = jQuery('#aec-calendar').fullCalendar('clientEvents',data.id)[0];
					e.title = data.title;
					e.allDay = data.allDay;
					e.start = data.start;
					e.end = data.end;
					e.className = data.className;
					jQuery('#aec-calendar').fullCalendar('updateEvent', e);
					jQuery.jGrowl('<strong>' + e.title + '</strong> <?php _e('has been updated.', AEC_PLUGIN_NAME); ?>', { header: '<?php _e('Success!', AEC_PLUGIN_NAME); ?>' });
				}
			}, 'json');
			jQuery('.time-picker').remove();
			jQuery.modal.close();
		}
	});

	jQuery('#delete').click(function(e) {
		e.preventDefault();
		var id = jQuery('#id').val();
		var title = jQuery('#title').val();

		if (confirm('<?php _e('Are you sure you wish to delete this event?', AEC_PLUGIN_NAME); ?>')) {
			jQuery.post('<?php echo AEC_PLUGIN_URL; ?>inc/event.php', { 'id': id, 'action': 'delete' }, function(data) {
				if (data) {
					jQuery('#aec-calendar').fullCalendar('removeEvents', id);
					jQuery.jGrowl('<strong>' + title + '</strong> <?php _e('has been deleted.', AEC_PLUGIN_NAME); ?>', { header: '<?php _e('Success!', AEC_PLUGIN_NAME); ?>' });
					jQuery('.time-picker').remove();
					jQuery.modal.close();
				}
			});
		}
	});

	function validateForm() {
		err = checkDuration();
		
		// to do: base required on plugin options
		var required = [<?php echo $aec->get_required_fields(); ?>];
		// check required fields
		if (required.length > 0) {
			jQuery.each(required, function(index, value) {
				 jQuery('#'+value).parent().find('label').css({color:'red'});
				 if (jQuery('#' + this).val() == '') {
					jQuery('#' + this).addClass('error');
					err = true;
				 } else {
					jQuery('#' + this).removeClass('error');
				 }
			});
		}
		
		if (err) {
			jQuery('.button-primary').attr('disabled', 'disabled');
			return false;
		} else {
			jQuery('.button-primary').removeAttr('disabled');
			return true;
		}
	}

	function checkDuration() {
		var allDay = jQuery('#allDay').attr('checked'),
			from = jQuery('#start_date').val(),
			to = jQuery('#end_date').val();
		
		if (allDay) {
			jQuery('#start_time, #end_time').fadeOut(250);
		} else {
			jQuery('#start_time, #end_time').fadeIn(250);
			if (from == to) {
				var start = jQuery.timePicker('#start_time').getTime(),
					end = jQuery.timePicker('#end_time').getTime();
				if (start >= end) {
					jQuery('#start_time, #end_time').addClass('error');
					jQuery('.duration').html('<?php _e('Invalid duration, please adjust your time inputs.', AEC_PLUGIN_NAME); ?>');
					return true;
				}
			}
			jQuery('#start_time, #end_time').removeClass('error');
			from = jQuery('#start_date').val() + ' ' + jQuery('#start_time').val(),
			to = jQuery('#end_date').val() + ' ' + jQuery('#end_time').val();
		}
		jQuery('.duration').html(calcDuration(from, to, allDay));
	}

	function calcDuration(from, to, allDay) {
		var milliseconds = new Date(to).getTime() - new Date(from).getTime();
		var diff = new Object();
		diff.days = Math.floor(milliseconds/1000/60/60/24);
		milliseconds -= diff.days*1000*60*60*24;
		diff.hours = Math.floor(milliseconds/1000/60/60);
		milliseconds -= diff.hours*1000*60*60;
		diff.minutes = Math.floor(milliseconds/1000/60);
		milliseconds -= diff.minutes*1000*60;
		diff.seconds = Math.floor(milliseconds/1000);

		// format output
		var out = new Array();
		if (allDay) diff.days += 1;
		if (diff.days > 0) out.push(diff.days + ' day' + plural(diff.days));
		if (diff.hours > 0) out.push(diff.hours + ' hour' + plural(diff.hours));
		if (diff.minutes > 0) out.push(diff.minutes + ' minute' + plural(diff.minutes));
		return out.join('<br>');
	}

	function plural(value) {
		return (value == 1) ? '' : 's';
	}
});
</script>
<?php
}
?>