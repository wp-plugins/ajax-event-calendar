/**
 * Handle: init_admin_calendar
 * Version: 0.9.8.5
 * Deps: $jq
 * Enqueue: true
 */

$jq = jQuery.noConflict();
$jq().ready(function(){
	$jq.jGrowl.defaults.closerTemplate = '<div>' + custom.hide_all_notifications + '</div>';
	$jq.jGrowl.defaults.position = 'bottom-right';
	
	var d 			= new Date(),
		now 		= d.getTime(),
		twoHours 	= (120 * 60 * 1000),
		today 		= new Date(d.getFullYear(), d.getMonth(), d.getDate()),
		nextYear 	= new Date(d.getFullYear()+1, d.getMonth(), d.getDate()),
		calendar 	= $jq('#aec-calendar').fullCalendar({
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
			events:{
				url: ajaxurl,
				data:{ action: 'get_events',
					   'edit': custom.editable },
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
			eventClick: function(e, js, view){
				eventtime = (e.end == null) ? e.start : e.end;
				if (custom.limit && (eventtime < now && custom.admin == false)){
					$jq.jGrowl(custom.error_past_edit, { header: custom.whoops });
					return;
				}
				eventDialog(e, custom.edit_event);
			},
			select: function(start, end, allDay, js, view){
				if (custom.limit){
					if (start < today || (start < now && view.name == 'agendaWeek')){
						$jq.jGrowl(custom.error_past_create, { header: custom.whoops });
						return false;
					// create an event today, starting up to 30 minutes into the future, and ending two hours later
					} else if (start < now){
						start 	= roundUp(now);
						end 	= roundUp(now + twoHours);
						allDay 	= false;
					} else if (start > nextYear){
						$jq.jGrowl(custom.error_future_create, { header: custom.whoops });
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
					$jq.jGrowl(custom.error_past_resize, { header: custom.whoops });
					revertFunc();
					return false;
				}
				moveEvent(e);
			},
			eventDrop: function(e, dayDelta, minuteDelta, allDay, revertFunc, js, ui, view){
				if (custom.limit == true && e.start < now){
					$jq.jGrowl(custom.error_past_move, { header: custom.whoops });
					revertFunc();
					return;
				}
				// if (!confirm("Did you mean to move this event?")){revertFunc();}
				moveEvent(e);
			}
		});

		function roundUp(date){
			var inc = 30 * 60 * 1000; // 30 minutes
			return new Date(inc * Math.ceil(date / inc));
		}
		
		function toUnixDate(date){
			return $jq.fullCalendar.formatDate(date, 'yyyy-MM-dd HH:mm:ss'); // unix datetime
		}

		// update dragged/resized event
		function moveEvent(e){
			var start	= toUnixDate(e.start),
				// if an event with a null end date/time is moved, dynamically create an end by adding two hours to the new start
				end		= (e.end == null) ? new Date(Date.parse(e.start) + twoHours) : e.end,
				end	 	= toUnixDate(end),
				allDay 	= (e.allDay) ? 1:0;
			$jq.post(ajaxurl,{ action: 'move_event', 'id': e.id, 'start': start, 'end': end, 'allDay': allDay }, function(data){
				if (data){
					$jq.jGrowl('<strong>' + e.title + '</strong> ' + custom.has_been_modified,{ header: custom.success });
				}
			});
		}

		function eventDialog(e, actionTitle){
			// check for modal html structure, if not present add it to the DOM
			if ($jq('aec-modal').length == 0) {
				var modal = '<div id="aec-modal"><div class="aec-title"></div><div class="aec-content"></div></div>';
				$jq('body').prepend(modal);
			}
			
			// adjusts modal top for WordPress admin bar
			var wpadminbar = $jq('#wpadminbar');
			var wpadminbar_height = (wpadminbar.length > 0) ? wpadminbar.height() : '0';
			
			$jq('#aec-modal').modal({
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
						$jq('#aec-modal', modal.container).show();
						var title 		= $jq('div.aec-title', modal.container),
							content 	= $jq('div.aec-content', modal.container),
							closebtn 	= $jq('div.close', modal.container);
						title.html(custom.loading_event_form).show();
						d.container.slideDown(150, function (){
							content.load(ajaxurl,{ action: 'admin_event', 'event': e }, function (){
								title.html(actionTitle);
								var h = content.height() + title.height() + 20;
								d.container.animate({ height: h }, 250, function (){
									closebtn.show();
									content.show();

									// execute modal window event handlers					
									if ($jq('#start_time').length > 0) {
										
										var times = $jq('#start_time, #end_time').timePicker({ 
											step: 30,
											show24Hours: custom.is24HrTime,
											separator: ':'
										}).hide();
										
										// toggle limit
										if (custom.limit == true) $jq.datepicker.setDefaults({'minDate':'0', 'maxDate':'+1y'});
										
										// toggle weekends
										if (custom.show_weekends == false) $jq.datepicker.setDefaults({'beforeShowDay':$jq.datepicker.noWeekends});
										
										// localize datepicker
										$jq.datepicker.setDefaults($jq.datepicker.regional[custom.locale]);
										
										var dates = $jq('#start_date, #end_date').datepicker({
											dateFormat: custom.datepicker_format,
											firstDay: custom.start_of_week,
											showButtonPanel: true,
											onSelect: function(selectedDate) {
												var option 		= (this.id == 'start_date') ? 'minDate' : 'maxDate',
													instance 	= $jq(this).data('datepicker'),
													date 		= $jq.datepicker.parseDate(instance.settings.dateFormat || 
																				$jq.datepicker._defaults.dateFormat,
																				selectedDate, instance.settings);
												dates.not(this).datepicker('option', option, date);
												checkDuration();
											}
										});

										/* recurring event placeholder
										var repeat_end = $jq('#repeat_end').datepicker({
											dateFormat: custom.datepicker_format,
											firstDay: custom.start_of_week
										}).hide();
										*/
										
										validateForm();
										checkDuration();
										
										/* recurring event placeholder
										$jq('#repeat_end').val($jq('#end_date').val());
										$jq('#start_date, #end_date, #start_time, #end_time, #allDay, #repeat_interval, #repeat_end').change(function(){
										*/
										
										$jq('#start_date, #end_date, #start_time, #end_time, #allDay').change(function(){
											checkDuration();
											
										});
										
										$jq('.required').parent().find('input, textarea').keyup(function(){
											validateForm();
										});
										
										$jq('#cancel_event').click(function(e){
											e.preventDefault();
											$jq('.time-picker').remove();
											$jq.modal.close();
										});

										$jq('#add_event').click(function(e){
											e.preventDefault();
											if (!validateForm()) return;
											$jq.post(ajaxurl, { action: 'add_event', 'event': $jq('#event_form').serialize() }, function(data){
												if (data) {
													var calendar = $jq('#aec-calendar').fullCalendar('renderEvent',
													{
														id: 		data.id,
														title: 		data.title,
														allDay: 	data.allDay,
														start: 		data.start,
														end:		data.end,
														className:	data.className
													}, false);
													// calendar.fullCalendar('unselect');
													$jq.jGrowl('<strong>' + data.title + '</strong> ' + custom.has_been_created, { header: custom.success });
												}
											}, 'json');
											$jq('.time-picker').remove();
											$jq.modal.close();
										});
										
										$jq('#update_event').click(function(e) {
											e.preventDefault();
											if (!validateForm()) return;
											$jq.post(ajaxurl, { action: 'update_event', 'event': $jq('#event_form').serialize() }, function(data){
												if (data) {
													var e 		= $jq('#aec-calendar').fullCalendar('clientEvents', data.id)[0];
													e.title 	= data.title;
													e.allDay	= data.allDay;
													e.start 	= data.start;
													e.end 		= data.end;
													e.className = data.className;
													$jq('#aec-calendar').fullCalendar('updateEvent', e);
													$jq.jGrowl('<strong>' + e.title + '</strong> ' + custom.has_been_modified, { header: custom.success });
												}
											}, 'json');
											$jq('.time-picker').remove();
											$jq.modal.close();
										});

										$jq('#delete_event').click(function(e) {
											e.preventDefault();
											var id 		= $jq('#id').val();
											var title 	= $jq('#title').val();
											if (confirm(custom.delete_event)) {
												$jq.post(ajaxurl, { action: 'delete_event', 'id': id }, function(data) {
													if (data) {
														$jq('#aec-calendar').fullCalendar('removeEvents', id);
														$jq.jGrowl('<strong>' + title + '</strong> ' + custom.has_been_deleted, { header: custom.success });
														$jq('.time-picker').remove();
														$jq.modal.close();
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
			var	allDay 	= $jq('#allDay').attr('checked'),
				from 	= $jq('#start_date').val(),
				to 		= $jq('#end_date').val();
			
			/*
			// recurring event placeholder
			repeat 	= $jq('#repeat_interval').val();
			if (repeat > 0) {
				$jq('#repeat_end').fadeIn(250);
			} else {
				$jq('#repeat_end').fadeOut(250);
			}
			*/
			
			if (allDay) {
				$jq('#start_time, #end_time').fadeOut(250);
			} else {
				$jq('#start_time, #end_time').fadeIn(250);
				if (from == to) {
					var start	= $jq.timePicker('#start_time').getTime(),
						end 	= $jq.timePicker('#end_time').getTime();
					if (start >= end) {
						$jq('#start_time, #end_time').addClass('aec-error');
						$jq('.duration-message').html(custom.error_invalid_duration);
						validateForm(true);
						return;
					} else {
						$jq('#start_time, #end_time').removeClass('aec-error');
					}
				}
				from 	= $jq('#start_date').val() + ' ' + $jq('#start_time').val(),
				to 		= $jq('#end_date').val() + ' ' + $jq('#end_time').val(),
				allDay  = (allDay) ? 1:0;
				validateForm(false);
			}
			$jq('.duration-message').html(calcDuration(from, to, allDay));
		}

		function validateForm(err){
			var err = false;
			 
			// convert required fields string into array
			var required = custom.required_fields.split(",");
			
			// no required fields
			if (!required.length) return;
			
			// process required fields
			$jq.each(required, function(index, value) {
				$jq('#' + value).parent().find('label').addClass('required');
				if ($jq('#' + this).val() == '') {
					$jq('#' + this).addClass('aec-error');
					err = true;
				} else {
					$jq('#' + this).removeClass('aec-error');
				}
			});
			
			if (err) {
				$jq('.button-primary').attr('disabled', 'disabled');
				return false;
			}
			$jq('.button-primary').removeAttr('disabled');
			return true;
		}

		// Convert dates for duration processing
		function convertDate(datetime){
			var dt 		= datetime.split(' ');
				date 	= dt[0];
				time 	= dt[1];
				
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
				// 24Hr Time format
				hours		= time.substr(0,2);
				if (!custom.is24HrTime) hours = 12 + parseInt(hours, 10);
				minutes		= time.substr(3,2);
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
			diff.seconds = Math.floor(milliseconds/1000);

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
});