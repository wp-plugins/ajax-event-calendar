<?php 
	$is_admin = ( current_user_can( 'manage_options' ) == true ) ? 1 : 0;
?>
<div class='wrap'>
	<div id='loading'>Loading...</div>
	<div id='modal'>
		<div class='title'></div>
		<div class='content'></div>
	</div>
	<div id='calendar'></div>
	<p class="alignright"><a href="<?php echo AEC_PLUGIN_HOMEPAGE; ?>" target="_blank">Ajax Event Calendar <strong>v<?php echo AEC_PLUGIN_VERSION; ?></strong></a>. Created by <a href="http://eranmiller.com" target="_blank" title="my website">Eran Miller</a></p>
</div>
<script type='text/javascript'>
jQuery().ready( function() {
	jQuery.jGrowl.defaults.closerTemplate = '<div>hide all notifications</div>';
	jQuery.jGrowl.defaults.position = 'bottom-right';

	var d = new Date(),
		now = d.getTime(),
		today = new Date( d.getFullYear(), d.getMonth(), d.getDate() ),
		nextYear = new Date( d.getFullYear() + 1, d.getMonth(), d.getDate() ),
		admin = <?php echo $is_admin; ?>;

	var calendar = jQuery( '#calendar' ).fullCalendar( {
		theme: true
		, timeFormat: {
			agenda: 'h:mmt{ - h:mmt}'
			, '': 'h(:mm)t'
		}
		, firstHour: 8
		, weekMode: 'liquid'
		, editable: true
		, events: {
			url: '<?php echo AEC_PLUGIN_URL; ?>inc/events.php'
			, data: { 'edit' : 1 }
			, type: 'POST'
			//, error: function( obj, type ) {
			//}
		}
		, header: {
			left: 'prev,next today'
			, center: 'title'
			, right: 'month,agendaWeek'
		}
		, selectable: true
		, selectHelper: true
		, loading: function( b ) {
			if ( b ) jQuery( '#loading' ).modal( { overlayId: 'modal-overlay', close: false } );
			else jQuery.modal.close();
		}
		, select: function( start, end, allDay, js, view ) {
			if ( start < now ) {
				jQuery.jGrowl( 'You cannot create events in the past.', { header: 'Whoops!' } );
				return false;
			} else if ( start > nextYear ) {
				jQuery.jGrowl( 'You cannot create events more than a year in advance.', { header: 'Whoops!' } );
				return false;
			}
			// Turn variables into event object
			e = { 'start': start, 'end': end, 'allDay': allDay };
			e = dbFormat( e );
			eventDialog( e, 'Add Event' );
		}
		, eventResize: function( e, dayDelta, minuteDelta, revertFunc, js, ui, view ) {
			eventtime = ( e.end == null ) ? e.start : e.end;
			if ( eventtime < now ) {
				jQuery.jGrowl( 'You cannot resize expired events.', { header: 'Whoops!' } );
				revertFunc();
				return false;
			}
			moveEvent( e );
		}
		// IMPORTANT: parameters must be listed as shown for revertFunc and view to function
		, eventDrop: function( e, dayDelta, minuteDelta, allDay, revertFunc, js, ui, view ) {
			if ( e.start < now ) {
				jQuery.jGrowl( 'You cannot move events into the past.', { header: 'Whoops!' } );
				revertFunc();
				return;
			}
			//if ( !confirm( "Did you mean to move this event?" ) ) {
				//revertFunc();
			//}
			moveEvent( e );
		}
		, eventClick: function( e, js, view ) {
			eventtime = ( e.end == null ) ? e.start : e.end;			
			if ( eventtime < now && admin == false ) {
				jQuery.jGrowl( 'You cannot edit expired events.', { header: 'Whoops!' } );
				return;
			}
			eventDialog( e, 'Edit Event' );
		}
	});
	
	// Format date/time values for js and php processing
	function dbFormat( i ) {
		var a = ( i.allDay ) ? 1 : 0;
		// creates a two hour default event duration
		if ( i.end == null ) {
			i.end = new Date( i.start.getTime() );
			i.end.setMinutes( i.end.getMinutes() + 120 );
		}
		var format = 'u';	// ISO date format
		var o = {
				'start': jQuery.fullCalendar.formatDate( i.start, format )
				, 'end': jQuery.fullCalendar.formatDate( i.end, format )
				, 'allDay': a
			}
		return o;
	};

	// Update dragged/resized event
	function moveEvent( e ) {			
		db = dbFormat( e );
		jQuery.post( '<?php echo AEC_PLUGIN_URL; ?>inc/event.php', { 'id': e.id, 'start': db.start, 'end': db.end, 'allDay': db.allDay, 'action': 'move' }, function( data ){
			if ( data ) {
				jQuery.jGrowl( '<strong>' + e.title + '</strong> has been modified.', { header: 'Success!' } );
			}
		});
	}
	
	function eventDialog( e, actionTitle ) {		
		jQuery( '#modal' ).modal({
			overlayId: 'modal-overlay'
			, containerId: 'modal-container'
			, closeHTML: '<div class="close"><a href="#" class="simplemodal-close">x</a></div>'
			, minHeight: 35
			, opacity: 65
			, position: ['0',]
			, overlayClose: true
			, onOpen: function ( d ) {
				var modal = this;
				modal.container = d.container[0];
				d.overlay.fadeIn( 150, function () {
					jQuery( '#modal', modal.container ).show();
					var title = jQuery( 'div.title', modal.container ),
						content = jQuery( 'div.content', modal.container ),
						closebtn = jQuery( 'div.close', modal.container );
					title.html( 'Loading event form...' ).show();
					d.container.slideDown( 150, function () {
						content.load( '<?php echo AEC_PLUGIN_URL; ?>inc/event.php', { 'event': e }, function () {
							title.html( actionTitle );
							var h = content.height() + title.height() + 20;
							d.container.animate( { height: h }, 250, function () {
								closebtn.show();
								content.show();
							});
						}, 'json' );
					});
				});
			}
			, onClose: function ( d ) {
				var modal = this;
				d.container.animate( { top:'-' + ( d.container.height() + 20 ) }, 350, function () {
					modal.close();
				});
			}
		});
	}
});
</script>