/**
 * Handle: init_admin_calendar
 * Version: 0.9.9
 * Deps: jQuery
 * Enqueue: true
 */

jQuery(document).ready(function($) {
	var isFilter = ($('#aec-filter li a').length > 0);
	$.jGrowl.defaults.closerTemplate = '<div>' + custom.hide_all_notifications + '</div>';
	$.jGrowl.defaults.position = 'bottom-right';
	
	var d 			= new Date(),
		now 		= d.getTime(),
		twoHours 	= (120 * 60 * 1000),
		today 		= new Date(d.getFullYear(), d.getMonth(), d.getDate()),
		nextYear 	= new Date(d.getFullYear()+1, d.getMonth(), d.getDate()),
		calendar 	= $('#aec-calendar').fullCalendar({
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
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek'
			},
			editable: custom.editable,
			selectable: custom.editable,
			selectHelper: custom.editable,
			dragOpacity: 0.4,
			loading: function(b){
				if (b) $('#aec-loading').modal({ overlayId: 'aec-modal-overlay', close: false });
				else $.modal.close();
			},
			eventClick: function(e, js, view){
				eventtime = (e.end == null) ? e.start : e.end;
				if (custom.limit == true && custom.admin == false && eventtime < now){
					$.jGrowl(custom.error_past_edit, { header: custom.whoops });
					return;
				}
				eventDialog(e, custom.edit_event);
			},
			select: function(start, end, allDay, js, view){
				if (custom.limit == true){					
					if (start < today || (start < now && view.name == 'agendaWeek')){
						$.jGrowl(custom.error_past_create, { header: custom.whoops });
						return false;
					// create an event today, starting up to 30 minutes into the future, and ending two hours later
					} else if (start < now){
						start 	= roundUp(now);
						end 	= roundUp(now + twoHours);
						allDay 	= false;
					} else if (start > nextYear){
						$.jGrowl(custom.error_future_create, { header: custom.whoops });
						return false;
					}
				}
				start 	= toUnixDate(start);
				end 	= toUnixDate(end);
				allDay 	= (allDay) ? 1:0;
				e 		= { 'start': start, 'end': end, 'allDay': allDay };  // object for fullcalendar/php processing
				eventDialog(e, custom.add_event);
			},
			eventResize: function(e, dayDelta, minuteDelta, revertFunc, js, ui, view){
				eventtime = (e.end == null) ? e.start : e.end;
				if (custom.limit == true && eventtime < now){
					$.jGrowl(custom.error_past_resize, { header: custom.whoops });
					revertFunc();
					return false;
				}
				moveEvent(e);
			},
			eventDragStart: function(e, js, ui, view){
				if (js.ctrlKey) {
					$(this).clone(true).insertBefore($(this));
					
				}
			},
			eventDrop: function(e, dayDelta, minuteDelta, allDay, revertFunc, js, ui, view){
				if (custom.limit == true && e.start < now){
					$.jGrowl(custom.error_past_move, { header: custom.whoops });
					revertFunc();
					return;
				}
				if (js.ctrlKey) {
					$.fn.copyEvent(e, js);
					//console.log(calendar.fullCalendar("clientEvents"));
				} else {
					moveEvent(e);
				}
			}
		});
		
		// mousewheel navigation
		$('#aec-calendar').mousewheel(function(e, delta) {
			var dir = (delta > 0) ? 'prev' : 'next';
			calendar.fullCalendar(dir);
			return false;
		});
		
		function roundUp(date){
			var inc = 30 * 60 * 1000; // 30 minutes
			return new Date(inc * Math.ceil(date / inc));
		}
		
		function toUnixDate(date){
			return $.fullCalendar.formatDate(date, 'yyyy-MM-dd HH:mm:ss'); // unix datetime
		}

		$.fn.copyEvent = function(e) {
			var selectedIndex = parseInt(e._id.replace(/_fc/,'')-1); // I've appended id's to the event DOM objects to reference them
			var clone = {'id': e.id, 'start': toUnixDate(e.start), 'end': toUnixDate(e.end)};
			$.post(ajaxurl,{ action: 'copy_event', 'clone': clone }, function(data){
				if (data){
					$.jGrowl('<strong>' + e.title + '</strong> ' + custom.copy_has_been_created,{ header: custom.success });
					var newEvent = $.extend({}, calendar.fullCalendar("clientEvents")[ selectedIndex ] );
					newEvent.source = null;
					newEvent._id = "_fc" + parseInt(calendar.fullCalendar("clientEvents").length+1);
					newEvent.id = data.id;
					newEvent.start = new Date(e.start);
					newEvent.allDay = e.allDay;
					newEvent.title = e.title + " copy";
					newEvent.className = e.className;
					calendar.fullCalendar("renderEvent", newEvent);
				}
			});
		}
		
		// update dragged/resized event
		function moveEvent(e){
			var start	= toUnixDate(e.start),
				// if an event with a null end date/time is moved, dynamically create an end by adding two hours to the new start
				end		= (e.end == null) ? new Date(Date.parse(e.start) + twoHours) : e.end,
				end	 	= toUnixDate(end),
				allDay 	= (e.allDay) ? 1:0;
			$.post(ajaxurl,{ action: 'move_event', 'id': e.id, 'start': start, 'end': end, 'allDay': allDay }, function(data){
				if (data){
					$.jGrowl('<strong>' + e.title + '</strong> ' + custom.has_been_modified,{ header: custom.success });
				}
			});
		}

		function eventDialog(e, actionTitle){
			// check for modal html structure, if not present add it to the DOM
			if ($('aec-modal').length == 0) {
				var modal = '<div id="aec-modal"><div class="aec-title"></div><div class="aec-content"></div></div>';
				$('body').prepend(modal);
			}
			
			// adjusts modal top for WordPress admin bar
			var wpadminbar = $('#wpadminbar');
			var wpadminbar_height = (wpadminbar.length > 0) ? wpadminbar.height() : '0';
			
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
								var h = content.height() + title.height() + 20;
								d.container.animate({ height: h }, 250, function (){
									closebtn.show();
									content.show();

									// execute modal window event handlers					
									if ($('#start_time').length > 0) {
										
										var times = $('#start_time, #end_time').timePicker({ 
											step: 30,
											show24Hours: custom.is24HrTime,
											separator: ':'
										}).fadeTo(0,0.2).attr("disabled","disabled");
										
										// toggle limit
										if (custom.limit == true) $.datepicker.setDefaults({'minDate':'0', 'maxDate':'+1y'});
										
										// toggle weekends
										if (custom.show_weekends == false) $.datepicker.setDefaults({'beforeShowDay':$.datepicker.noWeekends});
										
										// localize datepicker
										$.datepicker.setDefaults($.datepicker.regional[custom.locale]);
										
										var dates = $('#start_date, #end_date').datepicker({
											dateFormat: custom.datepicker_format,
											firstDay: custom.start_of_week,
											showButtonPanel: true,
											onSelect: function(selectedDate) {
												var option 		= (this.id == 'start_date') ? 'minDate' : 'maxDate',
													instance 	= $(this).data('datepicker'),
													date 		= $.datepicker.parseDate(instance.settings.dateFormat || 
																				$.datepicker._defaults.dateFormat,
																				selectedDate, instance.settings);
												dates.not(this).datepicker('option', option, date);
												checkDuration();
											}
										});

										/* recurring event placeholder
										var repeat_end = $('#repeat_end').datepicker({
											dateFormat: custom.datepicker_format,
											firstDay: custom.start_of_week
										}).hide();
										*/
										
										validateForm();
										checkDuration();
										
										/* recurring event placeholder
										$('#repeat_end').val($('#end_date').val());
										$('#start_date, #end_date, #start_time, #end_time, #allDay, #repeat_interval, #repeat_end').change(function(){
										*/
										
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
											$.post(ajaxurl, { action: 'add_event', 'event': $('#event_form').serialize() }, function(data){
												if (data) {
													var calendar = $('#aec-calendar').fullCalendar('renderEvent',
													{
														id: 		data.id,
														title: 		data.title,
														allDay: 	data.allDay,
														start: 		data.start,
														end:		data.end,
														className:	data.className
													}, false);
													// calendar.fullCalendar('unselect');
													$.jGrowl('<strong>' + data.title + '</strong> ' + custom.has_been_created, { header: custom.success });
												}
											}, 'json');
											$('.time-picker').remove();
											$.modal.close();
										});
										
										$('#update_event').click(function(e) {
											e.preventDefault();
											if (!validateForm()) return;
											$.post(ajaxurl, { action: 'update_event', 'event': $('#event_form').serialize() }, function(data){
												if (data) {
													var e 		= $('#aec-calendar').fullCalendar('clientEvents', data.id)[0];
													e.title 	= data.title;
													e.allDay	= data.allDay;
													e.start 	= data.start;
													e.end 		= data.end;
													e.className = data.className;
													$('#aec-calendar').fullCalendar('updateEvent', e);
													$.jGrowl('<strong>' + e.title + '</strong> ' + custom.has_been_modified, { header: custom.success });
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
						modal.close();
					});
				}
			});
		}

		// modal window javascript
		function checkDuration(){
			var	allDay 	= $('#allDay').attr('checked'),
				from 	= $('#start_date').val(),
				to 		= $('#end_date').val();
			
			/*
			// recurring event placeholder
			repeat 	= $('#repeat_interval').val();
			if (repeat > 0) {
				$('#repeat_end').fadeIn(250);
			} else {
				$('#repeat_end').fadeOut(250);
			}
			*/
			
			if (allDay) {
				$('#start_time, #end_time').fadeTo(150,0.2).attr("disabled","disabled");
				
			} else {
				$('#start_time, #end_time').fadeTo(150,1).removeAttr("disabled");
				if (from == to) {
					var start	= $.timePicker('#start_time').getTime(),
						end 	= $.timePicker('#end_time').getTime();
					if (start >= end) {
						$('#start_time, #end_time').addClass('aec-error');
						$('.duration-message').html(custom.error_invalid_duration);
						validateForm(true);
						return;
					}
					$('#start_time, #end_time').removeClass('aec-error');
				}
				from 	= $('#start_date').val() + ' ' + $('#start_time').val(),
				to 		= $('#end_date').val() + ' ' + $('#end_time').val(),
				allDay  = (allDay) ? 1:0;
				$('#start_time, #end_time').removeClass('aec-error');
				validateForm(false);
			}
			$('.duration-message').html(calcDuration(from, to, allDay));
		}

		function validateForm(err){
			var err = false;
			 
			// convert required fields string into array
			var required = custom.required_fields.split(",");
			
			// no required fields
			if (!required.length) return;
			
			// process required fields
			$.each(required, function(index, value) {
				$('#' + value).parent().find('label').addClass('required');
				if ($('#' + this).val() == '') {
					$('#' + this).addClass('aec-error');
					err = true;
				} else {
					$('#' + this).removeClass('aec-error');
				}
			});
			
			if (err) {
				$('.button-primary').attr('disabled', 'disabled');
				return false;
			}
			$('.button-primary').removeAttr('disabled');
			return true;
		}

		// Convert dates for duration processing
		function convertDate(datetime){
			var dt 		= datetime.split(' ');
				date 	= dt[0];
				time 	= dt[1];
			if (!custom.is24HrTime)
				ampm	= dt[2];

			// US Date Format
			if (date.indexOf('/') >= 0) {
				var dateparts	= date.split('/');
				var month 		= dateparts[0];
				var day 		= dateparts[1];
			}
			
			// European Date Format
			if (date.indexOf('-') >= 0) {
				var dateparts	= date.split('-');
				var day 		= dateparts[0];
				var month 		= dateparts[1];
			}

			var year 			= dateparts[2];
			
			if (undefined !== time) {
				hours		= time.substr(0,2);
				minutes		= time.substr(3,2);
				if (!custom.is24HrTime) {
					if (hours == 12) hours = 0;
					if (ampm == 'PM') hours = 12 + parseInt(hours, 10);
				}
				if (hours == 24) hours = 0;
				return month + '/' + day + '/' + year + ' ' + hours + ':' + minutes + ':' + '00';
			}
			return month + '/' + day + '/' + year;
		}

		function calcDuration(from, to, allDay){
			from = convertDate(from);
			to = convertDate(to);

			var milliseconds = new Date(to).getTime() - new Date(from).getTime();
			
			var diff = new Object();
			diff.weeks = Math.floor(milliseconds/1000/60/60/24/7);
			milliseconds -= diff.weeks*1000*60*60*24*7;
			diff.days = Math.floor(milliseconds/1000/60/60/24);
			milliseconds -= diff.days*1000*60*60*24;
			diff.hours = Math.floor(milliseconds/1000/60/60);
			milliseconds -= diff.hours*1000*60*60;
			diff.minutes = Math.floor(milliseconds/1000/60);
			milliseconds -= diff.minutes*1000*60;

			// format output
			var out = new Array();
			if (allDay) diff.days += 1;
			if (diff.weeks > 0) out.push(diff.weeks + ' ' + _n(diff.weeks, custom.week, custom.weeks));
			if (diff.days > 0) out.push(diff.days + ' ' + _n(diff.days, custom.day, custom.days));
			if (diff.hours > 0) out.push(diff.hours + ' ' + _n(diff.hours, custom.hour, custom.hours));
			if (diff.minutes > 0) out.push(diff.minutes + ' ' + _n(diff.minutes, custom.minute, custom.minutes));
			return out.join(', ');
		}
		
		function _n(quantity, singular, plural){
			return (quantity != 1) ? plural : singular;
		}
		
		if (isFilter) {
			filter($('#aec-filter .all')); // filter: activate all
			$('#aec-filter li a').click(function() {
				filter(this);
			});
		};

		function filter(active) {
			$('#aec-filter li').next().fadeTo(0, 0.5).removeClass('active');
			$(active).parent().fadeTo(250, 1).addClass('active');
			calendar.fullCalendar('rerenderEvents');
		}
});