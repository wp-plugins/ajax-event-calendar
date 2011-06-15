<?php
/*
Template Name: calendar
*/
// View event details
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

	$options = get_option('aec_options');
	if (isset($_POST['id'])) {
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
					$out .= '<li><small>Presented by ';
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
get_header();
?>
<link rel="stylesheet" type="text/css" href="<?php echo AEC_PLUGIN_URL; ?>css/jquery-ui-1.8.11.custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo AEC_PLUGIN_URL; ?>css/custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo AEC_PLUGIN_URL; ?>css/cat_colors.css" />
	<div id="container">
		<div id="content" role="main">
<?php
	if (post_password_required()) {
		the_content();
	} else {
?>
			<div id="aec-loading"><?php _e('Loading...', AEC_PLUGIN_NAME); ?></div>
			<div id="aec-modal">
				<div class="title"></div>
				<div class="content"></div>
			</div>
			<div id="aec-header" class="ui-widget">
				<?php 
					if ($options['menu']) {
						wp_register('', ''); ?> | <?php wp_loginout(); 
					}
				?>
				<ul id="aec-filter">
				<?php
					$categories = $aec->get_categories();
					if (sizeof($categories) > 1) {
						$out = '<li><h3>' . __('Show Types', AEC_PLUGIN_NAME) . '</h3></li>' . "\n";
						$out .= '<li class="active"><a class="all">' . __('All', AEC_PLUGIN_NAME) . '</a></li>' . "\n";
						foreach ($categories as $category) {
							 $out .= '<li><a class="cat' . $category->id . '">' . $category->category . '</a></li>' . "\n";
						}
						echo $out;
					}
				?>
				</ul>
			</div>
			<div id="aec-calendar"></div>
			<a href="http://eranmiller.com/" id="aec-credit"><?php echo AEC_PLUGIN_NAME . ' v' . AEC_PLUGIN_VERSION; ?> created by Eran Miller</a>
			<?php } ?>
		</div>
	</div>

<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/jquery-ui-1.8.11.custom.min.js"></script>
<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/fullcalendar.min.js"></script>
<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/jquery.simplemodal.1.4.1.min.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function() {		
		<?php if (!$options['sidebar']) { ?>
			jQuery('#content').css({ 'margin':'0' });
		<?php }	?>
		
		var isFilter = (jQuery('#aec-filter li a').length > 0);
		var calendar = jQuery('#aec-calendar').fullCalendar({
			theme: true
			, timeFormat: {
				agenda: 'h:mmt{ - h:mmt}'
				, '': 'h(:mm)t'
			}
			, firstHour: 8
			, weekMode: 'liquid'
			, editable: false
			, eventRender: function(e, element) {
				// check if filter is active
				if (isFilter) {
					var filter = jQuery('#aec-filter li.active').children().attr('class');
					// skip filter if selected option is "all"		
					if (filter != 'all') {
						// hide all category types other than the selected
						if (e.className[0] != filter) {
							element.hide();
						}
					}
				}
			}
			, events: {
				url: '<?php echo AEC_PLUGIN_URL; ?>inc/events.php'
				, data: { 'edit' : 0 }
				, type: 'POST'
				//, error: function(obj, type) { }
			}
			, header: {
				left: 'prev,next today'
				, center: 'title'
				, right: 'month,agendaWeek'
			}
			, selectable: false
			, selectHelper: false
			, loading: function(b) {
				if (b) jQuery('#aec-loading').modal({ overlayId: 'aec-modal-overlay', close: false });
				else jQuery.modal.close();
			}
			,eventClick: function(e) {
				eventDialog(e);
			}
		});
		
		if (isFilter) {
			filter(jQuery('#aec-filter .all')); // filter: activate all
			
			jQuery('#aec-filter li a').click(function() {
				filter(this);
			});
		};

		function filter(active) {
			jQuery('#aec-filter li').next().fadeTo(0, 0.3).removeClass('active');
			jQuery(active).parent().fadeTo(250, 1).addClass('active');
			calendar.fullCalendar('rerenderEvents');
		}
		
		function eventDialog(e) {		
			jQuery('#aec-modal').modal({
				overlayId: 'aec-modal-overlay'
				, containerId: 'aec-modal-container'
				, closeHTML: '<div class="close"><a href="#" class="simplemodal-close" title="<?php _e('click here (or press ESC) close event details', AEC_PLUGIN_NAME); ?>">x</a></div>'
				, minHeight: 35
				, opacity: 65
				, position: ['0',]
				, overlayClose: true
				, onOpen: function (d) {
					var modal = this;
					modal.container = d.container[0];
					d.overlay.fadeIn(250, function () {
						jQuery('#aec-modal', modal.container).show();
						var title = jQuery('div.title', modal.container),
							content = jQuery('div.content', modal.container),
							closebtn = jQuery('div.close', modal.container);
						title.html('Loading event details...').show();
						d.container.slideDown(250, function () {
							jQuery.post('.', { 'id': e.id, 'do': 'edit' }, function(data) {
								title.html(data.title);
								content.html(data.content);
								var h = content.height() + title.height() + 20;
								d.container.animate({ height: h }, 250, function () {
									closebtn.show();
									content.show();
								});
							}, 'json');
						});
					});
				}
				, onClose: function (d) {
					var modal = this;
					d.container.animate({ top:'-' + (d.container.height() + 20) }, 350, function () {
						modal.close();
					});
				}
			});
		}
	});
</script>
<?php if ($options['sidebar']) { get_sidebar(); } ?>
<?php get_footer(); ?>