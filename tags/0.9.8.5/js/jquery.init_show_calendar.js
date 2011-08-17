/**
 * Handle: init_show_calendar
 * Version: 0.9.8.5
 * Deps: $jq
 * Enqueue: true
 */

$jq = jQuery.noConflict();
$jq().ready(function(){
	var isFilter = ($jq('#aec-filter li a').length > 0);
	var isCalendar = ($jq('#aec-calendar').length > 0);
	if (isCalendar) {
		var calendar = $jq('#aec-calendar').fullCalendar({
			monthNames: [custom.january, custom.february, custom.march, custom.april, custom.may, custom.june, custom.july,
						 custom.august, custom.september, custom.october, custom.november, custom.december], 
			monthNamesShort: [custom.jan, custom.feb, custom.mar, custom.apr, custom.may, custom.jun, custom.jul, custom.aug,
							custom.sep, custom.oct, custom.nov, custom.dec],
			dayNames: [custom.sunday, custom.monday, custom.tuesday, custom.wednesday, custom.thursday, custom.friday, custom.saturday],
			dayNamesShort: [custom.sun, custom.mon, custom.tue, custom.wed, custom.thu, custom.fri, custom.sat],
			buttonText:{
				today: custom.today,
				month: custom.month,
				week: custom.week,
				day: custom.day
			},
			// aspectRatio: 2,
			allDayText: custom.all_day,
			timeFormat:{
				agenda: custom.agenda_time_format,
				'': custom.other_time_format
			},
			columnFormat:{
				week: 'ddd d',
				month: 'ddd'
			},
			axisFormat: custom.axis_time_format,
			firstDay: custom.start_of_week,
			firstHour: 8,
			weekMode: 'liquid',
			weekends: (custom.show_weekends=='1')?true:false,
			eventRender: function(e, element) {
				// check if filter is active
				if (isFilter) {
					var filter = $jq('#aec-filter li.active').children();
					// if filter is not "all", hide all category types other than the selected
					if (!filter.hasClass('all') && !filter.hasClass(e.className[0])) {
						element.hide();
					}
				}
			},
			events:{
				url: custom.ajaxurl,
				data:{ action: 'get_events',
					   'edit' : custom.editable },
				type: 'POST'
			},
			header:{
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek'
			},
			editable: custom.editable,
			selectable: custom.editable,
			selectHelper: custom.editable,
			loading: function(b){
				if (b) $jq('#aec-loading').modal({ overlayId: 'aec-modal-overlay', close: false });
				else $jq.modal.close();
			},
			eventClick: function(e){
				eventDialog(e);
			}
		});
	};
	
	if (isFilter) {
		filter($jq('#aec-filter .all')); // filter: activate all
		$jq('#aec-filter li a').click(function() {
			filter(this);
		});
	};

	function filter(active) {
		$jq('#aec-filter li').next().fadeTo(0, 0.5).removeClass('active');
		$jq(active).parent().fadeTo(250, 1).addClass('active');
		calendar.fullCalendar('rerenderEvents');
	}

	// public method for sidebar widget access
	$jq.eventDialog = function(e) {
		eventDialog(e);
	}
	
	function eventDialog(e){
		// adjusts modal top for WordPress admin bar
		var wpadminbar = $jq('#wpadminbar');
		var wpadminbar_height = (wpadminbar.length > 0) ? wpadminbar.height() : '0';

		// check for modal html structure, if not present add it to the DOM
		if ($jq('aec-modal').length == 0) {
			var modal = '<div id="aec-modal"><div class="aec-title"></div><div class="aec-content"></div></div>';
			$jq('body').prepend(modal);
		}
		$jq('#aec-modal').modal({
			overlayId: 'aec-modal-overlay',
			containerId: 'aec-modal-container',
			closeHTML: '<div class="close"><a href="#" class="simplemodal-close" title="' + custom.close_event_form + '">x</a></div>',
			minHeight: 35,
			opacity: 65,
			position: [wpadminbar_height,],
			overlayClose: true,
			onOpen: function (d) {
				var modal = this;
				modal.container = d.container[0];
				d.overlay.fadeIn(150, function () {
					$jq('#aec-modal', modal.container).show();
					var title = $jq('div.aec-title', modal.container),
						content = $jq('div.aec-content', modal.container),
						closebtn = $jq('div.close', modal.container);
					title.html(custom.loading_event_form).show();
					d.container.slideDown(150, function () {
						$jq.post(custom.ajaxurl, { action:'get_event', 'id': e.id }, function(data) {
							title.html(data.title);
							content.html(data.content);
							var h = content.height() + title.height() + 20;
							d.container.animate({ height: h }, 150, function () {
								closebtn.show();
								content.show();
							});
						}, 'json');
					});
				});
			},
			onClose: function (d){
				var modal = this;
				d.container.animate({ top:'-' + (d.container.height() + 20) }, 250, function (){
					modal.close();
				});
			}
		});
	}
});