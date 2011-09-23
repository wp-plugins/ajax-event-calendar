/**
 * Handle: init_show_calendar
 * Version: 1.0
 * Deps: jQuery
 * Enqueue: true
 */

jQuery(document).ready(function($){

	var aec_init = function(){
		$.jGrowl.defaults.closerTemplate = '<div>' + custom.hide_all_notifications + '</div>';
		$.jGrowl.defaults.position = (custom.is_rtl == '1') ? 'bottom-left' : 'bottom-right';
		isFilter 	= ($('#aec-filter li a').length > 0);
		if($('#aec-calendar').length > 0){
			calendarInit();
			eventHandlers();
		}
	},
	filter = function(active){
		$('#aec-filter li').next().fadeTo(0, 0.5).removeClass('active');
		$(active).parent().fadeTo(250, 1).addClass('active');
		calendar.fullCalendar('rerenderEvents');		
	},
	eventHandlers = function(){
		if(isFilter){
			// category filter
			filter($('#aec-filter .' + shortcode.filter));
			$('#aec-filter li a').click(function(){
				filter(this);
			});
		};
		if(shortcode.nav){
			// quick navigation
			if ($('#aec-quickselect').length == 0) {
				$('.fc-button-prev', '.fc-header').after('<div id="aec-quickselect"></div>');			
			}
			var quickSelect = $('#aec-quickselect').datepicker({
				changeMonth: true,
				changeYear: true,
				onChangeMonthYear: function(year, month, inst){
					calendar.fullCalendar('gotoDate', year, month-1);
				}
			});
			$('.fc-button-prev, .fc-button-next, .fc-button-today', '.fc-header').click(function(){
				var date = calendar.fullCalendar('getDate');
				quickSelect.datepicker("setDate", date);
			});
		}
		if(shortcode.scroll){
			// mousewheel navigation
			$('#aec-calendar').mousewheel(function(e, delta){
				var scroll = (delta > 0) ? -1 : 1;
				// TODO: add month selector
				var view = calendar.fullCalendar('getView');
				if(view.name == 'month'){
					calendar.fullCalendar('incrementDate', 0, scroll, 0);
				}else if(view.name == 'agendaWeek' || view.name == 'basicWeek'){
					calendar.fullCalendar('incrementDate', 0, 0, scroll * 7);
				}else if (view.name == 'agendaDay' || view.name == 'basicDay'){
					calendar.fullCalendar('incrementDate', 0, 0, scroll);
				}
				var date = calendar.fullCalendar('getDate');
				quickSelect.datepicker("setDate", date);
				return false;
			});
		}
	},
	calendarInit = function(){
		calendar = $('#aec-calendar').fullCalendar({
			isRTL: custom.is_rtl,
			monthNames: [custom.january, custom.february, custom.march, custom.april, custom.may, custom.june, custom.july,
						 custom.august, custom.september, custom.october, custom.november, custom.december],
			monthNamesShort: [custom.jan, custom.feb, custom.mar, custom.apr, custom.may_short, custom.jun, custom.jul, custom.aug,
							custom.sep, custom.oct, custom.nov, custom.dec],
			dayNames: [custom.sunday, custom.monday, custom.tuesday, custom.wednesday, custom.thursday, custom.friday, custom.saturday],
			dayNamesShort: [custom.sun, custom.mon, custom.tue, custom.wed, custom.thu, custom.fri, custom.sat],
			buttonIcons: false,
			buttonText:{
				today: custom.today,
				month: custom.month,
				week: custom.week,
				day: custom.day,
				prev: '&nbsp;&#9668;&nbsp;', // left triangle
				next: '&nbsp;&#9658;&nbsp;'  // right triangle
			},
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
			events:{
				url: custom.ajaxurl,
				data:{ action: 'get_events',
					   'readonly': true,
					   'categories': shortcode.categories,
					   'excluded': (shortcode.excluded) ? 1 : 0
				},
				type: 'POST'
			},
			header:{
				left: shortcode.nav,
				center: (shortcode.mini == 1) ? 'title' : '',
				right: shortcode.views
			},
			height: shortcode.height,
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
			//eventMouseover: function(e, js, view){
			//},
			eventClick: function(e){
				eventDialog(e);
			},
			eventRender: function(e, element){
				// check if filter is active
				if(isFilter){
					var filter = $('#aec-filter li.active').children();
					// if filter is not "all", hide all category types other than the selected
					if(!filter.hasClass('all') && !filter.hasClass(e.className[0]))
						element.hide();
				}
				if(shortcode.mini){
					element.html('&nbsp;').attr('title', e.title);
				}
			}
		});
	},
	eventDialog = function(e){
		// adjusts modal top for WordPress admin bar
		var wpadminbar = $('#wpadminbar');
		var wpadminbar_height = (wpadminbar.length > 0) ? wpadminbar.height() : '0';

		// check for modal html structure, if not present add it to the DOM
		if($('#aec-modal').length == 0){
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
			onOpen: function(d){
				var modal = this;
				modal.container = d.container[0];
				d.overlay.fadeIn(150, function() {
					$('#aec-modal', modal.container).show();
					var title = $('div.aec-title', modal.container),
						content = $('div.aec-content', modal.container),
						closebtn = $('div.close', modal.container);
					title.html(custom.loading_event_form).show();
					d.container.slideDown(150, function(){
						$.post(custom.ajaxurl, { action:'get_event', 'id': e.id, 'start': toUnixDate(e.start), 'end': toUnixDate(e.end)}, function(data){
							title.html(data.title);
							content.html(data.content);
							var h = content.height() + title.height() + 40;
							d.container.animate({ height: h }, 150, function(){
								closebtn.show();
								content.show();
								$('.duration').html(calcDuration(data.start, data.end, data.allDay, data.repeat_f, data.repeat_i, data.repeat_e));
							});
						}, 'json');
					});
				});
			},
			onClose: function(d){
				var modal = this;
				d.container.animate({ top:'-' + (d.container.height() + 20) }, 250, function(){
					$('.time-picker').remove();
					modal.close();
				});
			}
		});
	},
	calcDuration = function(from, to, allDay, frequency, interval, until){
		var mills = new Date(to).getTime() - new Date(from).getTime();
		var diff = new Object();
		diff.weeks = Math.floor(mills/604800000);
		mills -= diff.weeks*604800000;
		diff.days = Math.floor(mills/86400000);
		mills -= diff.days*86400000;
		diff.hours = Math.floor(mills/3600000);
		mills -= diff.hours*3600000;
		diff.minutes = Math.floor(mills/60000);

		var date	= human_date(diff, allDay);
		var datestr	= date.join(', ');
		var reps = new Array();
		
		if(frequency > 0){
			reps.push(' ');
			reps.push('(');
			reps.push(custom.repeats_every);
			reps.push(' ');
			if (interval == 0) _jn(reps, frequency, custom.day, custom.days, 1);
			if (interval == 1) _jn(reps, frequency, custom.week, custom.weeks, 1);
			if (interval == 2) _jn(reps, frequency, custom.month, custom.months, 1);
			if (interval == 3) _jn(reps, frequency, custom.years, custom.years, 1);
			reps.push(' ');
			reps.push(custom.until);
			reps.push(' ');
			reps.push(until);
			reps.push(')');
		}	
		var out = datestr;
		out += reps.join('');
		return out;
	},
	human_date = function(diff, allDay){
		var out = new Array();
		if(allDay == 1){
			diff.days += 1;
		}
		_jn(out, diff.weeks, custom.week, custom.weeks);
		_jn(out, diff.days, custom.day, custom.days);
		if(allDay == 0){
			_jn(out, diff.hours, custom.hour, custom.hours);
			_jn(out, diff.minutes, custom.minute, custom.minutes);
		}
		return out;
	},
	_jn = function(arr, quantity, singular, plural, min){
		if(undefined == min){
			min = 0;
		}
		if(quantity > 0){
			var out = new Array();
			if(quantity > min){
				out.push(quantity);
			}
			out.push((quantity != 1) ? plural : singular);
			element = out.join(' ');
			arr.push(element);
		}
		return;
	},
	toUnixDate = function(date){
		return $.fullCalendar.formatDate(date, 'yyyy-MM-dd HH:mm:ss'); // unix datetime
	};
	
	aec_init();
	
	// public method for sidebar widget access
	$.aecDialog = function(e){
		e.start = $.fullCalendar.parseDate(e.start);
		e.end 	= $.fullCalendar.parseDate(e.end);
		eventDialog(e);
	};
});