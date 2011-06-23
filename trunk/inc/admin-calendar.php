<?php 
	$is_admin = (current_user_can('manage_options') == true) ? 1 : 0;
	$options = get_option('aec_options');
?>
<div class="wrap">
	<h2><?php _e('Calendar', AEC_PLUGIN_NAME); ?></h2>
	<div id="aec-loading"><?php _e('Loading...', AEC_PLUGIN_NAME); ?></div>
	<div id="aec-modal">
		<div class="aec-title"></div>
		<div class="aec-content"></div>
	</div>
	<div id="aec-calendar"</div>
</div>
<script type='text/javascript'>
jQuery().ready( function() {
	jQuery.jGrowl.defaults.closerTemplate = '<div><?php htmlentities(_e('hide all notifications', AEC_PLUGIN_NAME)); ?></div>';
	jQuery.jGrowl.defaults.position = 'bottom-right';

	var d = new Date(),
		now = d.getTime(),
		today = new Date( d.getFullYear(), d.getMonth(), d.getDate() ),
		nextYear = new Date( d.getFullYear() + 1, d.getMonth(), d.getDate() ),
		admin = <?php echo $is_admin; ?>;
		limit = <?php echo $options['limit']; ?>;

	var calendar = jQuery( '#aec-calendar' ).fullCalendar( {
		
		monthNames: ['<?php htmlentities(_e('January', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('February', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('March', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('April', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('May', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('June', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('July', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('August', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('September', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('October', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('November', AEC_PLUGIN_NAME)); ?>',
					 '<?php htmlentities(_e('December', AEC_PLUGIN_NAME)); ?>'],
		monthNamesShort: ['<?php htmlentities(_e('Jan', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Feb', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Mar', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Apr', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('May', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Jun', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Jul', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Aug', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Sep', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Oct', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Nov', AEC_PLUGIN_NAME)); ?>',
						  '<?php htmlentities(_e('Dec', AEC_PLUGIN_NAME)); ?>'],
		dayNames: ['<?php htmlentities(_e('Sunday', AEC_PLUGIN_NAME)); ?>',
				   '<?php htmlentities(_e('Monday', AEC_PLUGIN_NAME)); ?>',
				   '<?php htmlentities(_e('Tuesday', AEC_PLUGIN_NAME)); ?>',
				   '<?php htmlentities(_e('Wednesday', AEC_PLUGIN_NAME)); ?>',
				   '<?php htmlentities(_e('Thursday', AEC_PLUGIN_NAME)); ?>',
				   '<?php htmlentities(_e('Friday', AEC_PLUGIN_NAME)); ?>',
				   '<?php htmlentities(_e('Saturday', AEC_PLUGIN_NAME)); ?>'],
		dayNamesShort: ['<?php htmlentities(_e('Sun', AEC_PLUGIN_NAME)); ?>',
						'<?php htmlentities(_e('Mon', AEC_PLUGIN_NAME)); ?>',
						'<?php htmlentities(_e('Tue', AEC_PLUGIN_NAME)); ?>',
						'<?php htmlentities(_e('Wed', AEC_PLUGIN_NAME)); ?>',
						'<?php htmlentities(_e('Thu', AEC_PLUGIN_NAME)); ?>',
						'<?php htmlentities(_e('Fri', AEC_PLUGIN_NAME)); ?>',
						'<?php htmlentities(_e('Sat', AEC_PLUGIN_NAME)); ?>'],
		buttonText: {
			today: '<?php htmlentities(_e('Today', AEC_PLUGIN_NAME)); ?>',
			month: '<?php htmlentities(_e('Month', AEC_PLUGIN_NAME)); ?>',
			week: '<?php htmlentities(_e('Week', AEC_PLUGIN_NAME)); ?>',
			day: '<?php htmlentities(_e('Day', AEC_PLUGIN_NAME)); ?>'
		},
		allDayText: '<?php htmlentities(_e('All Day', AEC_PLUGIN_NAME)); ?>',
		/*
			titleFormat: {
				month: 'MMMM yyyy',
				week: "d [ yyyy]{ '&#8212;'[ MMM] d MMM yyyy}",
				day: 'dddd, d MMM, yyyy'
			},
			columnFormat: {
				month: 'ddd',
				week: 'ddd d/M',
				day: 'dddd d/M'
			},
			axisFormat: 'H:mm',
			*/
		timeFormat: {
			agenda: 'h:mmt{ - h:mmt}',
			'': 'h(:mm)t'
		},
		
		firstHour: 8,
		weekMode: 'liquid',
		editable: true,
		events: {
			url: '<?php echo AEC_PLUGIN_URL; ?>inc/events.php',
			data: { 'edit' : 1 },
			type: 'POST'
			//, error: function( obj, type ) {
			//}
		},
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek'
		},
		selectable: true,
		selectHelper: true,
		loading: function( b ) {
			if ( b ) jQuery( '#aec-loading' ).modal( { overlayId: 'aec-modal-overlay', close: false } );
			else jQuery.modal.close();
		},
		select: function( start, end, allDay, js, view ) {
			if ( limit ) {
				if ( start < today || ( start < now && view.name == 'agendaWeek' )) {
					jQuery.jGrowl( '<?php htmlentities(_e('You cannot create events in the past.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Whoops!', AEC_PLUGIN_NAME)); ?>' } );
					return false;
				} else if ( start < now ) {
					twoHours = 120 * 60 * 1000;
					end = (end == start) ? now + twoHours : Date.parse(end)  + twoHours;
					start = roundUp(now);				
					end = roundUp(end);
					allDay = false;
				} else if ( start > nextYear ) {
					jQuery.jGrowl( '<?php htmlentities(_e('You cannot create events more than a year in advance.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Whoops!', AEC_PLUGIN_NAME)); ?>' } );
					return false;
				}
			}

			// Turn variables into event object
			e = { 'start': start, 'end': end, 'allDay': allDay };
			e = dbFormat( e );
			eventDialog( e, 'Add Event' );
		},
		eventResize: function( e, dayDelta, minuteDelta, revertFunc, js, ui, view ) {
			eventtime = ( e.end == null ) ? e.start : e.end;
			if ( limit && eventtime < now ) {
				jQuery.jGrowl( '<?php htmlentities(_e('You cannot resize expired events.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Whoops!', AEC_PLUGIN_NAME)); ?>' } );
				revertFunc();
				return false;
			}
			moveEvent( e );
		},
		// IMPORTANT: parameters must be listed as shown for revertFunc and view to function
		eventDrop: function( e, dayDelta, minuteDelta, allDay, revertFunc, js, ui, view ) {
			if ( limit && e.start < now ) {
				jQuery.jGrowl( '<?php htmlentities(_e('You cannot move events into the past.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Whoops!',  AEC_PLUGIN_NAME)); ?>' } );
				revertFunc();
				return;
			}
			//if ( !confirm( "Did you mean to move this event?" ) ) {
				//revertFunc();
			//}
			moveEvent( e );
		},
		eventClick: function( e, js, view ) {
			eventtime = ( e.end == null ) ? e.start : e.end;			
			if ( limit && (eventtime < now && admin == false )) {
				jQuery.jGrowl( '<?php htmlentities(_e('You cannot edit expired events.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Whoops!',  AEC_PLUGIN_NAME)); ?>' } );
				return;
			}
			eventDialog( e, '<?php htmlentities(_e('Edit Event', AEC_PLUGIN_NAME)); ?>' );
		}
	});
	
	function roundUp( timeA ) {
		var inc = 30 * 60 * 1000; //30 minutes
		return new Date( inc * Math.ceil( timeA / inc ) );
	}
	
	function addMinutes( timeA, minutes ) {
		var timeB = new Date( timeA.getTime() );
		timeB.setMinutes( timeA.getMinutes() + minutes );
		return timeB;	
	}
	
	// Format date/time values for js and php processing
	function dbFormat( i ) {
		var a = ( i.allDay ) ? 1 : 0;
		if ( i.end == null ) { i.end = addMinutes( i.start, 120 ) } // adds two hours
		var format = 'u';	// ISO date format
		var o = {
			'start': jQuery.fullCalendar.formatDate( i.start, format ),
			'end': jQuery.fullCalendar.formatDate( i.end, format ),
			'allDay': a
			}
		return o;
	};

	// Update dragged/resized event
	function moveEvent( e ) {			
		db = dbFormat( e );
		jQuery.post( '<?php echo AEC_PLUGIN_URL; ?>inc/admin-event.php', { 'id': e.id, 'start': db.start, 'end': db.end, 'allDay': db.allDay, 'action': 'move' }, function( data ){
			if ( data ) {
				jQuery.jGrowl( '<strong>' + e.title + '</strong> <?php htmlentities(_e('has been modified.', AEC_PLUGIN_NAME)); ?>', { header: '<?php htmlentities(_e('Success!', AEC_PLUGIN_NAME)); ?>' } );
			}
		});
	}
	
	function eventDialog( e, actionTitle ) {		
		jQuery( '#aec-modal' ).modal({
			overlayId: 'aec-modal-overlay',
			containerId: 'aec-modal-container',
			closeHTML: '<div class="close"><a href="#" class="simplemodal-close" title="<?php htmlentities(_e('Close Event Form', AEC_PLUGIN_NAME)); ?>">x</a></div>',
			minHeight: 35,
			opacity: 65,
			position: ['0',],
			overlayClose: true,
			onOpen: function ( d ) {
				var modal = this;
				modal.container = d.container[0];
				d.overlay.fadeIn( 150, function () {
					jQuery( '#aec-modal', modal.container ).show();
					var title = jQuery( 'div.aec-title', modal.container ),
						content = jQuery( 'div.aec-content', modal.container ),
						closebtn = jQuery( 'div.close', modal.container );
					title.html( '<?php htmlentities(_e('Loading Event Form...', AEC_PLUGIN_NAME)); ?>' ).show();
					d.container.slideDown( 150, function () {
						content.load( '<?php echo AEC_PLUGIN_URL; ?>inc/admin-event.php', { 'event': e }, function () {
							title.html( actionTitle );
							var h = content.height() + title.height() + 20;
							d.container.animate( { height: h }, 250, function () {
								closebtn.show();
								content.show();
							});
						}, 'json' );
					});
				});
			},
			onClose: function ( d ) {
				var modal = this;
				d.container.animate( { top:'-' + ( d.container.height() + 20 ) }, 350, function () {
					modal.close();
				});
			}
		});
	}
});
</script>