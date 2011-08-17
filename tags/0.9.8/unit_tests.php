<?php	
	$_POST['event']['user_id'] = 0;	// system id
	$_POST['event']['title'] = 'Ajax Event Calendar [v' . AEC_PLUGIN_VERSION . '] Installed!';
	$_POST['event']['start_date'] = date(AEC_WP_DATE_FORMAT);
	$_POST['event']['start_time'] = date(AEC_WP_TIME_FORMAT);
	$_POST['event']['end_date'] = date(AEC_WP_DATE_FORMAT);
	$_POST['event']['end_time'] = date(AEC_WP_TIME_FORMAT);
	$_POST['event']['allDay'] = 1;
	$_POST['event']['category_id'] = 1;
	$_POST['event']['description'] = 'Now that the calendar is installed...here are a few next steps you can take:<ul><li>Add the front-end calendar page (illustrated in the FAQ section via the event link)</li><li>Change the event categories</li><li>Add the calendar widgets to your sidebar</li><li>Modify the calendar options (via the WP Settings: General and Ajax Event Calendar menus)</li><li>Authorize calendar contributors (via the WP Users menu)</li></ul><br>Can\'t find what you\'re looking for in the FAQ? <a href="http://wordpress.org/tags/ajax-event-calendar?forum_id=10" target="_blank">Check out the forum</a> and post your questions there.<br><br>If you use and enjoy this plugin, please remember to vote via the event link.<br>Thanks!<br>Eran';
	$_POST['event']['link'] = AEC_PLUGIN_HOMEPAGE;
	$_POST['event']['venue'] = 'Cloud Gate';
	$_POST['event']['address'] = '201 East Randolph Street';
	$_POST['event']['city'] = 'Chicago';
	$_POST['event']['state'] = 'IL';
	$_POST['event']['zip'] = '60601-6530';
	$_POST['event']['contact'] = 'Eran Miller';
	$_POST['event']['contact_info'] = 'plugins@eranmiller.com';
	$_POST['event']['access'] = 1;
	$_POST['event']['rsvp'] = 0;
	ajax_event_calendar::add_event(false);
?>