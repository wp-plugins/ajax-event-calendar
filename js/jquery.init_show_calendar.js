/**
 * Handle: init_show_calendar
 * Version: 0.9.9.1
 * Deps: jQuery
 * Enqueue: true
 */

jQuery(document).ready(function($) {
	$.jGrowl.defaults.closerTemplate = '<div>' + custom.hide_all_notifications + '</div>';
	$.jGrowl.defaults.position = (custom.is_rtl == '1') ? 'bottom-left' : 'bottom-right';
	var isFilter 	= ($('#aec-filter li a').length > 0);
	var isCalendar	= ($('#aec-calendar').length > 0);
	if (isCalendar) {
		var calendar = $('#aec-calendar').fullCalendar({
			isRTL: custom.is_rtl,
			monthNames: [custom.january, custom.february, custom.march, custom.april, custom.may, custom.june, custom.july,
						 custom.august, custom.september, custom.october, custom.november, custom.december], 
			monthNamesShort: [custom.jan, custom.feb, custom.mar, custom.apr, custom.may, custom.jun, custom.jul, custom.aug,
							custom.sep, custom.oct, custom.nov, custom.dec],
			dayNames: [custom.sunday, custom.monday, custom.tuesday, custom.wednesday, custom.thursday, custom.friday, custom.saturday],
			dayNamesShort: [custom.sun, custom.mon, custom.tue, custom.wed, custom.thu, custom.fri, custom.sat],
			buttonIcons: false,
			buttonText:{
				today: custom.today,
				month: custom.month,
				week: custom.week,
				day: custom.day,
				prev: '&nbsp;&#9668;&nbsp;',  // left triangle
				next: '&nbsp;&#9658;&nbsp;',  // right triangle
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
			weekends: (custom.show_weekends == '1') ? true : false,
			eventRender: function(e, element) {
				// check if filter is active
				if (isFilter) {
					var filter = $('#aec-filter li.active').children();
					// if filter is not "all", hide all category types other than the selected
					if (!filter.hasClass('all') && !filter.hasClass(e.className[0])) {
						element.hide();
					}
				}
			},
			events:{
				url: custom.ajaxurl,
				data:{ action: 'get_events',
					   'readonly': true,
					   'categories': shortcode.categories,
					   'excluded': (shortcode.excluded) ? 1 : 0,
				},
				type: 'POST'
			},
			header:{
				left: shortcode.nav,
				center: 'title',
				right: shortcode.views
			},
			defaultView: shortcode.view,
			month: shortcode.month,
			year: shortcode.year,
			editable: custom.editable,
			selectable: custom.editable,
			selectHelper: custom.editable,
			loading: function(b){
				if (b) $.jGrowl(custom.loading, {sticky:true});
				else $('#jGrowl').jGrowl('close');
			},
			eventClick: function(e){
				eventDialog(e);
			}
		});

		// mousewheel navigation
		if (shortcode.scroll) {
			$('#aec-calendar').mousewheel(function(e, delta) {
				calendar.fullCalendar('incrementDate',0 ,delta, 0);
				return false;
			});
		}
	};

	if (isFilter) {
		filter($('#aec-filter .' + shortcode.filter));
		$('#aec-filter li a').click(function() {
			filter(this);
		});
	};

	function filter(active) {
		$('#aec-filter li').next().fadeTo(0, 0.5).removeClass('active');
		$(active).parent().fadeTo(250, 1).addClass('active');
		calendar.fullCalendar('rerenderEvents');
	}
		
	// public method for sidebar widget access
	$.eventDialog = function(e) {
		eventDialog(e);
	}
	
	function eventDialog(e){
		// adjusts modal top for WordPress admin bar
		var wpadminbar = $('#wpadminbar');
		var wpadminbar_height = (wpadminbar.length > 0) ? wpadminbar.height() : '0';

		// check for modal html structure, if not present add it to the DOM
		if ($('#aec-modal').length == 0) {
			var modal = '<div id="aec-modal"><div class="aec-title"></div><div class="aec-content"></div></div>';
			$('body').prepend(modal);
		}
		$('#aec-modal').modal({
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
					$('#aec-modal', modal.container).show();
					var title = $('div.aec-title', modal.container),
						content = $('div.aec-content', modal.container),
						closebtn = $('div.close', modal.container);
					title.html(custom.loading_event_form).show();
					d.container.slideDown(150, function () {
						$.post(custom.ajaxurl, { action:'get_event', 'id': e.id }, function(data) {
							title.html(data.title);
							content.html(data.content);
							var h = content.height() + title.height() + 20;
							d.container.animate({ height: h }, 150, function () {
								closebtn.show();
								content.show();
								$('.duration').html(calcDuration(data.start, data.end, data.allDay));
							});
						}, 'json');
					});
				});
			},
			onClose: function (d){
				var modal = this;
				d.container.animate({ top:'-' + (d.container.height() + 20) }, 250, function(){
					$('.time-picker').remove();
					modal.close();
				});
			}
		});
	}

	function calcDuration(from, to, allDay){
		var mills = new Date(to).getTime() - new Date(from).getTime();
		var diff = new Object();
		diff.weeks = Math.floor(mills/604800000);
		mills -= diff.weeks*604800000;
		diff.days = Math.floor(mills/86400000);
		mills -= diff.days*86400000;
		diff.hours = Math.floor(mills/3600000);
		mills -= diff.hours*3600000;
		diff.minutes = Math.floor(mills/60000);
		mills -= diff.minutes*60000;

		// format output
		var out = new Array();
		if (allDay == true) diff.days += 1;
		_jn(out, diff.weeks, custom.week, custom.weeks);
		_jn(out, diff.days, custom.day, custom.days);
		if (allDay == false) {
			_jn(out, diff.hours, custom.hour, custom.hours);
			_jn(out, diff.minutes, custom.minute, custom.minutes);
		}
		if (custom.is_rtl) out.reverse();
		return out.join(', ');
	}
	
	function _jn(arr, quantity, singular, plural){
		if (quantity > 0) {
			var out = new Array();
			out.push(quantity);
			out.push((quantity != 1) ? plural : singular);
			if (custom.is_rtl) out.reverse();
			element = out.join(' ');
			arr.push(element);
		}
		return
	}

});