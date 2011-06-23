<?php
// View event details
$options = get_option('aec_options');
?>
<link rel="stylesheet" type="text/css" href="<?php echo AEC_PLUGIN_URL; ?>css/jquery-ui-1.8.13.custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo AEC_PLUGIN_URL; ?>css/custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo AEC_PLUGIN_URL; ?>css/cat_colors.css" />
	<div id="aec-container">
		<div id="aec-loading"><?php _e('Loading...', AEC_PLUGIN_NAME); ?></div>
		<div id="aec-modal">
			<div class="aec-title"></div>
			<div class="aec-content"></div>
		</div>
		<div id="aec-header">
			<?php
			if ($options['menu']) {
				$out = '<div id="aec-menu">';
				$out .= wp_loginout( admin_url() . 'admin.php?page=ajax-event-calendar.php', false);
				$out .= wp_register(' | ', '', false);
				$out .= '</div>';
				echo $out;
			}
			?>
			<ul id="aec-filter">
			<?php
				$categories = $this->get_categories();
				if (sizeof($categories) > 1) {
					$out = '<li>' . htmlentities(__('Show Types', AEC_PLUGIN_NAME)) . '</li>' . "\n";
					$out .= '<li class="active"><a class="all">' . htmlentities(__('All', AEC_PLUGIN_NAME)) . '</a></li>' . "\n";
					foreach ($categories as $category) {
						 $out .= '<li><a class="cat' . $category->id . '">' . $category->category . '</a></li>' . "\n";
					}
					echo $out;
				}
			?>
			</ul>
		</div>
		<div id="datepicker"></div>
		<div id="aec-calendar"></div>
		<?php echo '<a href="http://eranmiller.com/" id="aec-credit">' . AEC_PLUGIN_NAME . ' v' . AEC_PLUGIN_VERSION . ' ' . htmlentities(__('Created By', AEC_PLUGIN_NAME)) . ' Eran Miller</a>'; ?>
	</div>

<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/jquery.fullcalendar.min.js"></script>
<script type="text/javascript" src="<?php echo AEC_PLUGIN_URL; ?>js/jquery.simplemodal.1.4.1.min.js"></script>
<script type="text/javascript">
	jQuery(document).ready(function() {	
		var isFilter = (jQuery('#aec-filter li a').length > 0);
		var calendar = jQuery('#aec-calendar').fullCalendar({
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
				agenda: 'h:mmt{ - h:mmt}'
				, '': 'h(:mm)t'
			},
			firstHour: 8,
			weekMode: 'liquid',
			editable: false,
			eventRender: function(e, element) {
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
			},
			events: {
				url: '<?php echo AEC_PLUGIN_URL; ?>inc/events.php',
				data: { 'edit' : 0 },
				type: 'POST'
				//, error: function(obj, type) { }
			},
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek'
			},
			selectable: false,
			selectHelper: false,
			loading: function(b) {
				if (b) jQuery('#aec-loading').modal({ overlayId: 'aec-modal-overlay', close: false });
				else jQuery.modal.close();
			},
			eventClick: function(e) {
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
				overlayId: 'aec-modal-overlay',
				containerId: 'aec-modal-container',
				closeHTML: '<div class="close"><a href="#" class="simplemodal-close" title="<?php htmlentities(_e('Close Event Details', AEC_PLUGIN_NAME)); ?>">x</a></div>',
				minHeight: 35,
				opacity: 65,
				position: ['0',],
				overlayClose: true,
				onOpen: function (d) {
					var modal = this;
					modal.container = d.container[0];
					d.overlay.fadeIn(250, function () {
						jQuery('#aec-modal', modal.container).show();
						var title = jQuery('div.aec-title', modal.container),
							content = jQuery('div.aec-content', modal.container),
							closebtn = jQuery('div.close', modal.container);
						title.html('<?php htmlentities(_e('Loading Event Details...', AEC_PLUGIN_NAME)); ?>').show();
						d.container.slideDown(250, function () {
							jQuery.post('<?php echo AEC_PLUGIN_URL; ?>inc/show-event.php', { 'id': e.id, 'do': 'edit' }, function(data) {
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
				},
				onClose: function (d) {
					var modal = this;
					d.container.animate({ top:'-' + (d.container.height() + 20) }, 350, function () {
						modal.close();
					});
				}
			});
		}
	});
</script>