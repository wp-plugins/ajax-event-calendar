/**
 * Handle: init_admin_calendar
 * Version: 1.0
 * Deps: jQuery
 * Enqueue: true
 */

jQuery(document).ready(function($){
	/* beta 1.0 patch 5 */
	var aec_init = function(){
		$.jGrowl.defaults.closerTemplate = '<div>' + custom.hide_all_notifications + '</div>';
		$.jGrowl.defaults.position = (custom.is_rtl=='1') ? 'bottom-left' : 'bottom-right';

		isFilter = ($('#aec-filter li a').length > 0);
		calendarInit();
		eventHandlers();
	},
	filter = function(active){
		$('#aec-filter li').next().fadeTo(0, 0.5).removeClass('active');
		$(active).parent().fadeTo(250, 1).addClass('active');
		calendar.fullCalendar('rerenderEvents');
	},
	eventHandlers = function(){
		if(isFilter){
			$('#aec-filter li a').click(function(){
				filter(this);
			});
		}

		// quick navigation
		if ($('#aec-quickselect').length == 0) {
			$('.fc-button-prev', '.fc-header').after('<div id="aec-quickselect"></div>');
		}
		var quickSelect = $('#aec-quickselect').datepicker({
			changeMonth: true,
			changeYear: true,
			onChangeMonthYear: function(year, month, inst){
				if(inst.selectedMonth != inst.currentMonth || inst.selectedYear != inst.currentYear){
					calendar.fullCalendar('gotoDate', year, month-1);
				}
			}
		});
		$('.fc-button-prev, .fc-button-next, .fc-button-today', '.fc-header').click(function(e){
			var date = calendar.fullCalendar('getDate');
			quickSelect.datepicker("setDate", date);
		});

		// mousewheel navigation
		if (custom.scroll){
			$('#aec-calendar').mousewheel(function(e, delta){
				var scroll = (delta > 0) ? -1 : 1;
				var view = calendar.fullCalendar('getView');
				if(view.name == 'month'){
					calendar.fullCalendar('incrementDate', 0, scroll, 0);
				}else if(view.name == 'agendaWeek' || view.name == 'basicWeek'){
					calendar.fullCalendar('incrementDate', 0, 0, scroll * 7);
				}else if(view.name == 'agendaDay' || view.name == 'basicDay'){
					calendar.fullCalendar('incrementDate', 0, 0, scroll);
				}
				var date = calendar.fullCalendar('getDate');
				$.datepicker.setDefaults($.datepicker.regional[custom.locale]);
				quickSelect.datepicker("setDate", date);
				return false;
			});
		}
	},
	renderEvent = function(data){
		$.each(data, function(index, d){
			$('#aec-calendar').fullCalendar('renderEvent', {
				id: 		d.id,
				title: 		d.title,
				allDay: 	d.allDay,
				start: 		d.start,
				end:		d.end,
				className:	d.className
			}, false);
		});
		return data[0].title;
	},
	updateEvent = function(e, data){
		calendar.fullCalendar("removeEvents", data[0].id);
		return renderEvent(data);
	},
	// dragged/resized events
	moveEvent = function(e, theaction){
		$.post(ajaxurl,{ action: 'move_event', 'event': e }, function(data){
			if (data){
				data.start = toUnixDate(e.start);
				data.end = toUnixDate(e.end);
				updateEvent(e, data);
				$.jGrowl('<strong>' + e.title + '</strong> ' + custom.has_been_modified,{ header: custom.success });
			}
		});
	},
	checkAllDay = function(){
		var	allDay 	= ($('#allDay').attr('checked')) ? 1:0;
		if(allDay){
			$.timePicker("#start_time").setTime(new Date(0, 0, 0, 0, 0, 0));
			$.timePicker("#end_time").setTime(new Date(0, 0, 0, 0, 0, 0));
		}else{
			$.timePicker("#start_time").setTime(new Date(0, 0, 0, 8, 0, 0));
			$.timePicker("#end_time").setTime(new Date(0, 0, 0, 10, 0, 0));
		}
	},
	checkRepeat = function(){
		var repeat 	= $('#repeat_freq').val();
		if(repeat > 0){
			$('#repeat_int, #repeat_label, #repeat_end').fadeIn(250);
		}else{
			$('#repeat_int, #repeat_label, #repeat_end').fadeOut(250);
		}
	},
	checkDuration = function(){
		var	allDay 	= ($('#allDay').attr('checked')) ? 1:0,
			from 	= $('#start_date').val(),
			to 		= $('#end_date').val(),
			start	= $('#start_time').val(),
			end		= $('#end_time').val(),
			until	= $('#repeat_end').val();
		if(allDay){
			$('#start_time, #end_time').fadeOut(250).attr("disabled","disabled");
		}else{
			$('#start_time, #end_time').fadeIn(250).removeAttr("disabled");
			from = Date.parse(from);
			to = Date.parse(to);
			if(from > to){
				$('#start_date, #end_date').addClass('aec-error');
				$('.duration').html(custom.error_invalid_duration);
					validateForm(true);
					return;
			}else if(to > until){
				$('#repeat_end').addClass('aec-error');
				validateForm(true);
				return;
			}else if(from == to){
				var start	= $.timePicker('#start_time').getTime(),
					end 	= $.timePicker('#end_time').getTime();
				if(start >= end){
					$('#start_time, #end_time').addClass('aec-error');
					$('.duration').html(custom.error_invalid_duration);
					validateForm(true);
					return;
				}
				$('#start_date, #end_date, #start_time, #end_time').removeClass('aec-error');
			}
			from 	= $('#start_date').val() + ' ' + $('#start_time').val(),
			to 		= $('#end_date').val() + ' ' + $('#end_time').val(),
			$('#start_time, #end_time').removeClass('aec-error');
			validateForm(false);
		}
		$('.duration').html(calcDuration(from, to, allDay));
	},
	validateForm = function(err){
		var err = false;

		// convert required fields string into array
		var required = custom.required_fields.split(",");

		// no required fields
		if(!required.length)
			return;

		// process required fields
		$.each(required, function(index, value){
			$('#' + value).parent().find('label').addClass('required');
			if($('#' + this).val() == ''){
				$('#' + this).addClass('aec-error');
				err = true;
			}else{
				$('#' + this).removeClass('aec-error');
			}
		});

		if(err){
			$('.button-primary').attr('disabled', 'disabled');
			return false;
		}
		$('.button-primary').removeAttr('disabled');
		return true;
	},
	// convert dates for duration processing
	convertDate = function(datetime){
		var dt 		= datetime.split(' ');
			date 	= dt[0];
			time 	= dt[1];
		if(!custom.is24HrTime)
			ampm	= dt[2];

		// US date format
		if(date.indexOf('/') >= 0){
			var dateparts	= date.split('/');
			var month 		= dateparts[0];
			var day 		= dateparts[1];
		}

		// European date format
		if(date.indexOf('-') >= 0){
			var dateparts	= date.split('-');
			var day 		= dateparts[0];
			var month 		= dateparts[1];
		}

		var year 			= dateparts[2];
		if(undefined !== time){
			hours		= time.substr(0,2);
			minutes		= time.substr(3,2);
			if(!custom.is24HrTime){
				if(hours == 12)
					hours = 0;
				if(ampm == 'PM')
					hours = 12 + parseInt(hours, 10);
			}
			if(hours == 24)
				hours = 0;
			return month + '/' + day + '/' + year + ' ' + hours + ':' + minutes + ':' + '00';
		}
		return month + '/' + day + '/' + year;
	},
	calcDuration = function(from, to, allDay){
		from = convertDate(from);
		to = convertDate(to);

		var mills = new Date(to).getTime() - new Date(from).getTime();
		var diff = new Object();
		diff.weeks = Math.floor(mills/604800000);
		mills -= diff.weeks*604800000;
		diff.days = Math.floor(mills/86400000);
		mills -= diff.days*86400000;
		diff.hours = Math.floor(mills/3600000);
		mills -= diff.hours*3600000;
		diff.minutes = Math.floor(mills/60000);

		// format output
		var out = new Array();
		if(allDay)
			diff.days += 1;
		_jn(out, diff.weeks, custom.week, custom.weeks);
		_jn(out, diff.days, custom.day, custom.days);
		if(!allDay){
			_jn(out, diff.hours, custom.hour, custom.hours);
			_jn(out, diff.minutes, custom.minute, custom.minutes);
		}
		if(custom.is_rtl)
			out.reverse();
		return out.join(', ');
	},// javascript _n method
	_jn = function(arr, quantity, singular, plural){
		if(quantity > 0){
			var out = new Array();
			out.push(quantity);
			out.push((quantity != 1) ? plural : singular);
			if(custom.is_rtl)
				out.reverse();
			element = out.join(' ');
			arr.push(element);
		}
		return;
	},
	roundUp = function(date){
		var inc = 1800000; // 30 minutes
		return new Date(inc * Math.ceil(date / inc));
	},
	toUnixDate = function(date){
		return $.fullCalendar.formatDate(date, 'yyyy-MM-dd HH:mm:ss'); // unix datetime
	},
	calendarInit = function(){
		var d 			= new Date(),
			now 		= d.getTime(),
			twoHours 	= (7200000),	// milliseconds
			today 		= new Date(d.getFullYear(), d.getMonth(), d.getDate());
		calendar 	= $('#aec-calendar').fullCalendar({
			isRTL: (custom.is_rtl=='1') ? true : false,
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
				prev: '&nbsp;&#9668;&nbsp;',  // left triangle
				next: '&nbsp;&#9658;&nbsp;'  // right triangle
			},
			aspectRatio: 2,
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
			weekends: (custom.show_weekends=='1') ? true : false,
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
				url: ajaxurl,
				data:{ action: 'get_events' },
				type: 'POST'
			},
			header:{
				left: 'prev,next,today',
				center: 'title',
				right: 'month,agendaWeek'
			},
			editable: custom.editable,
			selectable: custom.editable,
			selectHelper: custom.editable,
			dragOpacity: 0.3,
			lazyFetching: false,
			loading: function(b){
				if (b) $.jGrowl(custom.loading, {sticky:true});
				else $('#jGrowl').jGrowl('close');
			},
			//eventMouseover: function(e, js, view){
			//},
			//viewDisplay: function(view){
			//},
			eventClick: function(e, js, view){
				if ($(this).hasClass('fc-event-disabled')){
					$.jGrowl(custom.error_no_rights, {header:custom.whoops});
					return;
				}
				eventtime = (e.end == null) ? e.start : e.end;
				if (custom.limit == true && custom.admin == false && eventtime < now){
					$.jGrowl(custom.error_past_edit, {header:custom.whoops});
					return;
				}
				e.viewStart = toUnixDate(view.visStart);
				e.viewEnd 	= toUnixDate(view.visEnd);
				eventDialog(e, custom.edit_event);
			},
			select: function(start, end, allDay, js, view){
				if(custom.limit == true){
					if(start < today || (start < now && view.name != 'month')){
						$.jGrowl(custom.error_past_create, { header: custom.whoops });
						return false;
					// create event that starts as much as 30 minutes from now, and ends two hours later
					} else if (start < now){
						start 	= roundUp(now);
						end 	= roundUp(now + twoHours);
						allDay 	= false;
					}
				}
				var e 		= new Object;
				e.start 	= toUnixDate(start);
				e.end 		= toUnixDate(end);
				e.allDay 	= (allDay) ? 1:0;
				e.viewStart = toUnixDate(view.visStart);
				e.viewEnd	= toUnixDate(view.visEnd);
				eventDialog(e, custom.add_event);
			},
			eventResize: function(e, dayDelta, minuteDelta, revertFunc, js, ui, view){
				eventtime 		= (e.end == null) ? e.start : e.end;
				if(custom.limit == true && eventtime < now){
					$.jGrowl(custom.error_past_resize, { header: custom.whoops });
					revertFunc();
					return false;
				}
				e.end			= (e.end == null) ? e.start : e.end;
				e.resize 		= 1;
				e.dayDelta 		= dayDelta;
				e.minuteDelta	= minuteDelta;
				e.view_start 	= toUnixDate(view.visStart);
				e.view_end 		= toUnixDate(view.visEnd);
				moveEvent(e);
			},
			eventDrop: function(e, dayDelta, minuteDelta, allDay, revertFunc, js, ui, view){
				if(custom.limit == true && e.start < now){
					$.jGrowl(custom.error_past_move, { header: custom.whoops });
					revertFunc();
					return;
				}
				e.allDay 		= (allDay) ? 1:0;
				e.resize 		= '';
				e.dayDelta 		= dayDelta;
				e.minuteDelta 	= minuteDelta;
				e.view_start 	= toUnixDate(view.visStart);
				e.view_end 		= toUnixDate(view.visEnd);
				moveEvent(e);
			}
		});
		// initialize filter: activate all
		if(isFilter){
			filter($('#aec-filter .all'));
		}
	},
	eventDialog = function(e, actionTitle){
		var ev = e;
		// check for modal, if not present add it to the DOM
		if ($('#aec-modal').length == 0) {
			var modal = '<div id="aec-modal"><div class="aec-title"></div><div class="aec-content"></div></div>';
			$('body').prepend(modal);
		}

		// adjusts modal top for WordPress admin bar
		var wpadminbar = $('#wpadminbar');
		var wpadminbar_height = (wpadminbar.length > 0) ? wpadminbar.height() : '0';

		// modal window controls
		$('#aec-modal').modal({
			overlayId: 'aec-modal-overlay',
			containerId: 'aec-modal-container',
			closeHTML: '<div class="close"><a href="#" class="simplemodal-close" title="' + custom.close_event_form + '">x</a></div>',
			minHeight: 35,
			opacity: 65,
			position: [wpadminbar_height,],
			overlayClose: true,
			onOpen: function (d){
				var modal = this;
				modal.container = d.container[0];
				d.overlay.fadeIn(150, function (){
					$('#aec-modal', modal.container).show();
					var title 		= $('div.aec-title', modal.container),
						content 	= $('div.aec-content', modal.container),
						closebtn 	= $('div.close', modal.container);
					title.html(custom.loading_event_form).show();
					d.container.slideDown(150, function (){
						content.load(ajaxurl,{ action: 'admin_event', 'event': e }, function (){
							title.html(actionTitle);
							var h = content.height() + title.height() + 30;
							d.container.animate({ height: h }, 250, function (){
								closebtn.show();
								content.show();

								// execute modal window event handlers
								if ($('#start_time').length > 0) {
									var times = $('#start_time, #end_time').timePicker({
										step: parseInt(custom.step_interval, 10),
										show24Hours: custom.is24HrTime,
										separator: ':'
									});

									// toggle limit
									if (custom.limit == true) $.datepicker.setDefaults({'minDate':'0'});

									// toggle weekends
									if (custom.show_weekends == false) $.datepicker.setDefaults({'beforeShowDay':$.datepicker.noWeekends});

									// localize datepicker
									$.datepicker.setDefaults($.datepicker.regional[custom.locale]);

									var dates = $('#start_date, #end_date').datepicker({
										dateFormat: custom.datepicker_format,
										firstDay: custom.start_of_week,
										showButtonPanel: true,
										showOtherMonths: true,
										selectOtherMonths: true,
										onSelect: function(selectedDate) {
											var option 		= (this.id == 'start_date') ? 'minDate' : 'maxDate',
												instance 	= $(this).data('datepicker'),
												repeat		= repeat_end.datepicker('getDate'),
												end_date	= $('#end_date').datepicker('getDate'),
												date 		= $.datepicker.parseDate(instance.settings.dateFormat ||
															  $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
											dates.not(this).datepicker('option', option, date);
											if(repeat < end_date){
												repeat_end.datepicker('setDate', end_date);
												$('#repeat_freq').val(0);
												checkRepeat();
											}
											checkDuration();
										}
									});

									var repeat_end = $('#repeat_end').datepicker({
										changeMonth: true,
										changeYear: true,
										dateFormat: custom.datepicker_format,
										firstDay: custom.start_of_week,
										showButtonPanel: false,
										showOtherMonths: true,
										selectOtherMonths: true,
										beforeShow: function(selectedDate) {
											var min = $('#end_date').datepicker('getDate');
											repeat_end.datepicker('option', {minDate: min});
										}
									});

									validateForm();
									checkRepeat();
									checkDuration();

									$('#allDay').change(function(){
										checkAllDay();
									});

									$('#repeat_freq').change(function(){
										checkRepeat();
									});

									$('#start_date, #end_date, #start_time, #end_time, #allDay').change(function(){
										checkDuration();
									});

									$('.required').parent().find('input, textarea').keyup(function(){
										validateForm();
									});

									$('#cancel_event').click(function(e){
										e.preventDefault();
										$('.time-picker').remove();
										$.modal.close();
									});

									$('#add_event').click(function(e){
										e.preventDefault();
										if (!validateForm()) return;

										var viewparams = '&view_start=' + encodeURIComponent(ev.viewStart) +
														 '&view_end=' + encodeURIComponent(ev.viewEnd);
										var formfields = $('#event_form').serialize() + viewparams;
										$.post(ajaxurl, { action: 'add_event', 'event': formfields }, function(data){
											if (data) {
												title = renderEvent(data);
												$.jGrowl('<strong>' + title + '</strong> ' + custom.has_been_created, { header: custom.success });
											}
										}, 'json');
										$('.time-picker').remove();
										$.modal.close();
									});
									
									$('#copy_event').click(function(e){
										e.preventDefault();
										if (!validateForm()) return;

										var viewparams = '&view_start=' + encodeURIComponent(ev.viewStart) +
														 '&view_end=' + encodeURIComponent(ev.viewEnd);
										var formfields = $('#event_form').serialize() + viewparams;
										$.post(ajaxurl, { action: 'copy_event', 'event': formfields }, function(data){
											if (data) {
												title = renderEvent(data);
												$.jGrowl('<strong>' + title + '</strong> ' + custom.has_been_created, { header: custom.success });
											}
										}, 'json');
										$('.time-picker').remove();
										$.modal.close();
									});

									$('#update_event').click(function(e) {
										e.preventDefault();
										if (!validateForm()) return;
										var viewparams = '&view_start=' + encodeURIComponent(ev.viewStart) +
														 '&view_end=' + encodeURIComponent(ev.viewEnd);
										var formfields = $('#event_form').serialize() + viewparams;

										$.post(ajaxurl, { action: 'update_event', 'event': formfields }, function(data){
											if (data) {
												var e 		= $('#aec-calendar').fullCalendar('clientEvents', data.id)[0];
												title		= updateEvent(e, data);
												$.jGrowl('<strong>' + title + '</strong> ' + custom.has_been_modified, { header: custom.success });
											}
										}, 'json');
										$('.time-picker').remove();
										$.modal.close();
									});

									$('#delete_event').click(function(e) {
										e.preventDefault();
										var id 		= $('#id').val();
										var title 	= $('#title').val();
										if (confirm(custom.delete_event)) {
											$.post(ajaxurl, { action: 'delete_event', 'id': id }, function(data) {
												if (data) {
													$('#aec-calendar').fullCalendar('removeEvents', id);
													$.jGrowl('<strong>' + title + '</strong> ' + custom.has_been_deleted, { header: custom.success });
													$('.time-picker').remove();
													$.modal.close();
												}
											});
										}
									});
								}
							});
						}, 'json');
					});
				});
			},
			onClose: function (d){
				var modal = this;
				d.container.animate({ top:'-' + (d.container.height() + 20) }, 350, function (){
					$('.time-picker').remove();
					modal.close();
				});
			}
		});
	};

	aec_init();
});