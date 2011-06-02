<?php
/*
Template Name: calendar
*/
// View event details
	if ( isset( $_POST['id'] ) ) {
		$event = $aec->get_event( $_POST['id'] );

		$out = '<ul>';
		// Split date/time fields
		list( $start_date, $start_time ) = str_split( $event->start, 10 );
		list( $end_date, $end_time ) = str_split( $event->end, 10 );
		$start_time = trim( $start_time );
		$end_time = trim( $end_time );

		$out .= '<li><h3>' . $event->start;
		if ( $event->allDay )
			$out .= ' (all day)';
		if ( $start_date != $end_date ) {
			$out .= ' - ' . $event->end;
		} else {
			if ( $start_time != $end_time )
				$out .= ' - ' . $end_time;
		}
		$out .= '</h3></li>';

		$out .= '<li>' . stripslashes($event->description) . '</li>';

		if ( !empty( $event->link ) )
			$out .= '<li><a href="' . $event->link . '" target="_blank">Event Link</a></li>';
		
		$out .= '<li><h3>Venue</h3>';
			if ( !empty( $event->venue ) )
				$out .= $event->venue . '<br>';
			if ( !empty( $event->address ) )
				$out .= $event->address . '<br>';
			$out .= $event->city . ', ' . strtoupper( $event->state ) . ' ' . $event->zip;
		$out .= '</li>';

		if ( !empty( $event->contact ) )
			$out .= '<li><h3>Contact Information</h3>';
			$out .= $event->contact;
			if ( !empty( $event->contact_info ) )
				$out .= ' (' . $event->contact_info . ')';
		if ( !empty( $event->contact ) )
			$out .= '</li>';

		if ( $event->access )
			$out .= '<li>This event is accessible to people with disabilities.</li>';
		
		if ( $event->rsvp )
			$out .= '<li>Please register with the contact person for this event.</li>';
			

		$org = get_userdata( $event->user_id );
		if ( !empty( $org->organization ) ) {
				$out .= '<li>Presented by ';
			if ( !empty( $org->user_url ) ) {
				$out .= '<a href="' . $org->user_url . '" target="_blank">' . $org->organization . '</a>';
			} else {
				$out .= $org->organization;
			}
			$out .= '</li>';
		}
		$out .= '</ul>';

		$categories = $aec->get_categories();
		foreach ( $categories as $category ) {
			if ( $category->id == $event->category_id ) $cat = $category->category;
		}
		
		$output = array(
			'title' 	=> $event->title . ' (' . $cat . ')'
			, 'content' => $out
		);
		echo json_encode( $output );
		exit;
	}
get_header();
?>
<div class="wrap">
<link rel="stylesheet" type="text/css" href="<?php echo AEC_PLUGIN_URL; ?>css/jquery-ui-1.8.11.custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo AEC_PLUGIN_URL; ?>css/custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo AEC_PLUGIN_URL; ?>css/cat_colors.css" />
	<div id="aec-loading">Loading...</div>
	<div id="aec-modal">
		<div class="title"></div>
		<div class="content"></div>
	</div>
	<div id="aec-header">
		<?php wp_register( '', '' ); ?> | <?php wp_loginout(); ?>
		<ul id="aec-filter">
		<?php
			$categories = $aec->get_categories();
			if ( sizeof( $categories ) > 1 ) {
				$out = '<li><h3>Show Types</h3></li>' . "\n";
				$out .= '<li class="active"><a class="all">All</a></li>' . "\n";
				foreach ($categories as $category) {
					 $out .= '<li><a class="cat' . $category->id . '">' . $category->category . '</a></li>' . "\n";
				}
				echo $out;
			}
		?>
		</ul>
	</div>
	<div id="aec-calendar"></div>
 </div>

<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/jquery-ui-1.8.11.custom.min.js"></script>
<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/fullcalendar.min.js"></script>
<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/jquery.simplemodal.1.4.1.min.js"></script>
<script type="text/javascript">
	jQuery( document ).ready(function() {	

		var isFilter = ( jQuery( '#aec-filter li a' ).length > 0 );
		var calendar = jQuery( '#aec-calendar' ).fullCalendar({
			theme: true
			, timeFormat: {
				agenda: 'h:mmt{ - h:mmt}'
				, '': 'h(:mm)t'
			}
			, firstHour: 8
			, weekMode: 'liquid'
			, editable: false
			, eventRender: function( e, element ) {
				// check if filter is active
				if ( isFilter ) {
					var filter = jQuery( '#aec-filter li.active' ).children().attr( 'class' );
					// skip filter if selected option is "all"		
					if ( filter != 'all' ) {
						// hide all category types other than the selected
						if ( e.className[0] != filter ) {
							element.hide();
						}
					}
				}
			}
			, events: {
				url: '<?php echo AEC_PLUGIN_URL; ?>inc/events.php'
				, data: { 'edit' : 0 }
				, type: 'POST'
				//, error: function( obj, type ) { }
			}
			, header: {
				left: 'prev,next today'
				, center: 'title'
				, right: 'month,agendaWeek'
			}
			, selectable: false
			, selectHelper: false
			, loading: function( b ) {
				if ( b ) jQuery( '#aec-loading' ).modal({ overlayId: 'aec-modal-overlay', close: false });
				else jQuery.modal.close();
			}
			,eventClick: function( e ) {
				eventDialog( e );
			}
		});
		
		if ( isFilter ) {
			filter( jQuery( '#aec-filter .all' ) ); // filter: activate all
			
			jQuery( '#aec-filter li a' ).click( function() {
				filter( this );
			});
		};

		function filter( active ) {
			jQuery( '#aec-filter li' ).next().fadeTo( 0, 0.3 ).removeClass( 'active' );
			jQuery( active ).parent().fadeTo( 250, 1 ).addClass( 'active' );
			calendar.fullCalendar( 'rerenderEvents' );
		}
		
		function eventDialog( e ) {		
			jQuery( '#aec-modal' ).modal({
				overlayId: 'aec-modal-overlay'
				, containerId: 'aec-modal-container'
				, closeHTML: '<div class="close"><a href="#" class="simplemodal-close" title="click here (or press ESC) close event details">x</a></div>'
				, minHeight: 35
				, opacity: 65
				, position: ['0',]
				, overlayClose: true
				, onOpen: function ( d ) {
					var modal = this;
					modal.container = d.container[0];
					d.overlay.fadeIn( 250, function () {
						jQuery( '#aec-modal', modal.container ).show();
						var title = jQuery( 'div.title', modal.container ),
							content = jQuery( 'div.content', modal.container ),
							closebtn = jQuery( 'div.close', modal.container );
						title.html( 'Loading event details...' ).show();
						d.container.slideDown( 250, function () {
							jQuery.post( '.', { 'id': e.id, 'do': 'edit' }, function( data ) {
								title.html( data.title );
								content.html( data.content );
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
<?php get_footer(); ?>