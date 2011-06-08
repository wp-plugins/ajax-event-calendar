<?php
	// IMPORTANT: both these lines are required
	require_once( "../../../../wp-blog-header.php" );
	header( "HTTP/1.1 200 OK" );
	
	$options = get_option('aec_options');
	
	// Add or Edit events
	if ( isset($_POST['action']) ) {
		// process add/edit
		$aec->process_event( $_POST );
	} else {
		// edit event details
		if ( isset( $_POST['event'] ) ) {
			if ( isset( $_POST['event']['id'] ) ) {
				// Populate form with content from database
				$event = $aec->get_event( $_POST['event']['id'] );
			} else {
				// Initialize form for new event
				$event = $aec->init_form( $_POST['event']['start'] 
										, $_POST['event']['end'] 
										, $_POST['event']['allDay']
										, $current_user->ID
									);
			}
		}

		// Split date/time into form fields
		list( $start_date, $start_time ) = str_split( $event->start, 10 );
		list( $end_date, $end_time ) = str_split( $event->end, 10 );

		// Populate Checkboxes
		$allday_checked = ( $event->allDay ) ? 'checked="checked" ' : '';
		$accessible_checked = ( $event->access ) ? 'checked="checked" ' : '';
		$rsvp_checked = ( $event->rsvp ) ? 'checked="checked" ' : '';
?>
	<form method="post" action="" id="event_form" class="aec_form">
	<input type="hidden" name="id" id="id" value="<?php echo $event->id; ?>" />
    <input type="hidden" name="user_id" id="user_id" value="<?php echo $event->user_id; ?>" />
    <ul>
		<li>
			<label>Duration
			<span class="duration"></span>
			</label>
			<ul class="hvv">
				<li>
					<label class="required">From</label>
					<input class="auto picker" type="text" name="start_date" id="start_date" size="11" value="<?php echo $start_date; ?>" />
					<input class="auto picker" type="text" name="start_time" id="start_time" size="8" value="<?php echo trim($start_time); ?>" />
				</li>
				<li>
					<label class="required">To</label>
					<input class="auto picker" type="text" name="end_date" id="end_date" size="11" value="<?php echo $end_date; ?>" />
					<input class="auto picker" type="text" name="end_time" id="end_time" size="8" value="<?php echo trim($end_time); ?>" />
				</li>
				<li class="cb">
					<input class="auto" type="checkbox" name="allDay" id="allDay" value="1" <?php echo $allday_checked ?> />
					<label for="allDay" class="box">All Day </label>
				</li>
			</ul>
		</li>

        <li>
            <label for="title" class="required">Title</label>
            <input type="text" name="title" id="title" value="<?php echo $event->title; ?>" />
		</li>
		
		<li>
            <label for="category_id" class="required">Type</label>
            <select class="large" name="category_id" id="category_id" >
            <?php
				$categories = $aec->get_categories();
				foreach ($categories as $category) {
					$category_selected = ( $category->id == $event->category_id ) ? ' selected="selected"' : '';
					print '<option value="' . $category->id . '"'. $category_selected . '>' . $category->category . '</option>';
				}
            ?>
            </select>
        </li>
		<li>
			<label for="venue">Venue</label>
			<input class="" type="text" name="venue" id="venue" value="<?php echo $event->venue; ?>" />
		</li>
		<li>
			<label for="address">Neighborhood or Street Address</label>
			<ul class="hvv">
				<?php if ($options['form_address']) { ?>
				<li>
					<input class="" type="text" name="address" id="address" value="<?php echo $event->address; ?>" />
				</li>
				<?php } ?>
				<li class="cb">
				<?php if ($options['form_city']) { ?>
					<label for="city" class="required">City</label>
					<input class="auto" type="text" name="city" id="city" size="22" value="<?php echo $event->city; ?>" />
				<?php } ?>
				</li>
				<li>
				<?php if ($options['form_state']) { ?>
					<label for="state" class="required">State</label>
					<input class="auto" type="text" name="state" id="state" size="3" maxlength="2" value="<?php echo $event->state; ?>" />
				<?php } ?>
				</li>
				<li>
				<?php if ($options['form_zip']) { ?>
					<label for="zip" class="required">Zip</label>
					<input class="auto" type="text" name="zip" id="zip" size="5" maxlength="5" value="<?php echo $event->zip; ?>" />
				<?php } ?>
				</li>
			</ul>
        </li>
        <li>
            <?php if ($options['form_link']) { ?>
			<label for="link">Website Link</label>
            <input type="text" name="link" id="link" value="<?php echo $event->link; ?>" />
			<?php } ?>
		</li>
		<li>
			<?php if ($options['form_description']) { ?>
            <label for="description" class="required">Description</label>
            <textarea class="wide" name="description" id="description"><?php echo $event->description; ?></textarea>
			<?php } ?>
        </li>
        <li>
			<label>Contact Person</label>
			<ul class="hvv">
				<li>
					<?php if ($options['form_contact']) { ?>
					<label for="contact" class="required">Name</label>
					<input class="semi" type="text" name="contact" id="contact" value="<?php echo $event->contact; ?>" />
					<?php } ?>
				</li>
				<li>
					<?php if ($options['form_contact_info']) { ?>
					<label for="contact_info" class="required">Phone or Email Address</label>
					<input class="semi" type="text" name="contact_info" id="contact_info" value="<?php echo $event->contact_info; ?>" />
					<?php } ?>
				</li>
			</ul>
		</li>
		<li>
			<label></label>
			<?php if ($options['form_access']) { ?>
			<input type="checkbox" value="1" name="access" id="access" <?php echo $accessible_checked; ?>/>
			<label for="access" class="box">This event is accessible to people with disabilities.</label>
			<?php } ?>
		</li>
		<li>
			<label></label>
			<?php if ($options['form_rsvp']) { ?>
			<input type="checkbox" value="1" name="rsvp" id="rsvp" <?php echo $rsvp_checked; ?>/>
			<label for="rsvp" class="box">Please register with the contact person for this event.</label>
			<?php } ?>
		</li>
        <li class="buttons">
			<input type="button" name="cancel" value="Cancel" class="button-secondary" id="cancel" />
			<?php 
			if ( $event->id ) { 
				$is_admin = ( current_user_can( 'manage_options' ) == true ) ? 1 : 0;
				if ( $is_admin ) {
					$first_name = get_user_meta( $event->user_id, 'first_name', true );
					$last_name = get_user_meta( $event->user_id, 'last_name', true );
					$organization = get_user_meta( $event->user_id, 'organization', true );
					$out = '<span class="fl">Created by: <strong>';
					if ( $current_user->ID == $event->user_id ) {
						$out .= 'You';
					} else {
						if ( $event->user_id > 0 ) {
							$out .= $first_name . ' ' . $last_name . ' (' . $organization . ')';
						} else {
							$out .= 'Ajax Event Calendar';
						}
					}
					$out .= '</strong></span>' . "\n";
					echo $out;
				}
			?>
			<label><input type="button" name="delete" value="Remove Event" class="button" id="delete" /></label>
			<input type="button" name="update" value="Update Event" class="button-primary" id="update" />
			<?php } else { ?>
			<input type="button" name="add" value="Add Event" class="button-primary" id="add" />
			<?php } ?>
        </li>
    </ul>
</form>
<script type='text/javascript'>
jQuery().ready( function() {
	var dates = jQuery( '#start_date, #end_date' ).datepicker({
<?php if ($options['general_limit_events']) { ?>
		minDate: '+1d',
		maxDate: '+1y',
<?php } ?>
		showButtonPanel: true,
		onSelect: function( selectedDate ) {
			var option = ( this.id == 'start_date' ) ? 'minDate' : 'maxDate',
				instance = jQuery( this ).data( 'datepicker' ),
				date = jQuery.datepicker.parseDate( instance.settings.dateFormat || 
											jQuery.datepicker._defaults.dateFormat,
											selectedDate, instance.settings );
			dates.not( this ).datepicker( 'option', option, date );
			validateForm();
		}
	});

	var times = jQuery( '#start_time, #end_time' ).timePicker({ 
		step: 30,
		show24Hours: false,
		separator:':'
	});

	validateForm();

	jQuery( '#event_form' ).change( function() {
		validateForm();
	});
	
	jQuery( '#cancel' ).click( function( e ) {
		e.preventDefault();
		jQuery.modal.close();
	});
	
	jQuery( '#add' ).click( function( e ) {
		e.preventDefault();
		if ( validateForm() ) {

			jQuery.post( '<?php echo AEC_PLUGIN_URL; ?>inc/event.php', { 'event': jQuery( '#event_form' ).serialize(), 'action': 'add' }, function( data ){
				if ( data ) {
					var calendar = jQuery( '#aec-calendar' ).fullCalendar( 'renderEvent',
					{
						id: data.id,
						title: data.title,
						allDay: data.allDay,
						start: data.start,
						end: data.end,
						className: data.className
					}, false );
					//calendar.fullCalendar( 'unselect' );
					jQuery.jGrowl( '<strong>' + data.title + '</strong> has been added.', { header: 'Success!' } );
				}
			}, 'json' );
			jQuery.modal.close();
		}
	});
	
	jQuery( '#update' ).click( function( e ) {
		e.preventDefault();
		if ( validateForm() ) {
			jQuery.post( '<?php echo AEC_PLUGIN_URL; ?>inc/event.php', { 'event': jQuery( '#event_form' ).serialize(), 'action': 'update' }, function( data ){
				if ( data ) {
					var e = jQuery( '#aec-calendar' ).fullCalendar( 'clientEvents', data.id )[0];
					e.title = data.title;
					e.allDay = data.allDay;
					e.start = data.start;
					e.end = data.end;
					e.className = data.className;
					jQuery( '#aec-calendar' ).fullCalendar( 'updateEvent', e );
					jQuery.jGrowl( '<strong>' + e.title + '</strong> has been updated.', { header: 'Success!' } );
				}
			}, 'json' );
			jQuery.modal.close();
		}
	});

	jQuery( '#delete' ).click( function( e ) {
		e.preventDefault();
		var id = jQuery( '#id' ).val();
		var title = jQuery( '#title' ).val();

		if (confirm( 'Are you sure you wish to delete this event?' )) {
			jQuery.post( '<?php echo AEC_PLUGIN_URL; ?>inc/event.php', { 'id': id, 'action': 'delete' }, function( data ) {
				if (data) {
					jQuery( '#aec-calendar' ).fullCalendar( 'removeEvents', id );
					jQuery.jGrowl( title + ' has been deleted.', { header: 'Success!' } );
					jQuery.modal.close();
				}
			});
		}
	});

	function validateForm() {
		err = checkDuration();
		
		// to do: base required on plugin options
		var required = ['title', 'city', 'state', 'zip', 'description', 'contact', 'contact_info'];
		
		// check required fields
		jQuery.each( required, function( index, value ) {
			 if ( jQuery( '#' + this ).val() == '' ) {
				jQuery( '#' + this ).addClass( 'error' );
				err = true;
			 } else {
				jQuery( '#' + this ).removeClass( 'error' );
			 }
		});
		if ( err ) {
			jQuery( '.button-primary' ).attr( 'disabled', 'disabled' );
			return false;
		} else {
			jQuery( '.button-primary' ).removeAttr( 'disabled' );
			return true;
		}
	}

	function setDuration( duration ) {
		jQuery( '.duration' ).html( duration );
	}

	function checkDuration() {
		var allDay = jQuery( '#allDay' ).attr( 'checked' ),
			from = jQuery( '#start_date' ).val(),
			to = jQuery( '#end_date' ).val();
		
		if ( allDay ) {
			jQuery( '#start_time, #end_time' ).fadeOut( 250 );
		} else {
			jQuery( '#start_time, #end_time' ).fadeIn( 250 );
			if ( from == to ) {
				var start = jQuery.timePicker( '#start_time' ).getTime(),
					end = jQuery.timePicker( '#end_time' ).getTime();
				if ( start >= end ) {
					jQuery( '#start_time, #end_time' ).addClass( 'error' );
					setDuration( 'Invalid duration, please adjust your time inputs.' );
					return true;
				}
			}
			jQuery( '#start_time, #end_time' ).removeClass( 'error' );
			from = jQuery( '#start_date' ).val() + ' ' + jQuery( '#start_time' ).val(),
			to = jQuery( '#end_date' ).val() + ' ' + jQuery( '#end_time' ).val();
		}

		setDuration( calcDuration( from, to, allDay ) );
	}

	function calcDuration( from, to, allDay ) {
		var milliseconds = new Date( to ).getTime() - new Date( from ).getTime();
		var diff = new Object();
		diff.days = Math.floor( milliseconds/1000/60/60/24 );
		milliseconds -= diff.days*1000*60*60*24;
		diff.hours = Math.floor( milliseconds/1000/60/60 );
		milliseconds -= diff.hours*1000*60*60;
		diff.minutes = Math.floor( milliseconds/1000/60 );
		milliseconds -= diff.minutes*1000*60;
		diff.seconds = Math.floor( milliseconds/1000 );

		// format output
		var out = new Array();
		if ( allDay ) diff.days += 1;
		if ( diff.days > 0 ) out.push( diff.days + ' day' + plural( diff.days ) );
		if ( diff.hours > 0 ) out.push( diff.hours + ' hour' + plural( diff.hours ) );
		if ( diff.minutes > 0 ) out.push( diff.minutes + ' minute' + plural( diff.minutes ) );
		return out.join('<br>');
	}

	function plural( value ) {
		return ( value == 1 ) ? '' : 's';
	}
});
</script>
<?php
}
?>