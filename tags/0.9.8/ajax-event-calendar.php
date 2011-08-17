<?php
/*
Plugin Name: Ajax Event Calendar
Plugin URI: http://wordpress.org/extend/plugins/ajax-event-calendar/
Description: A Google Calendar-like interface that allows registered users (with the necessary credentials) to add, edit and delete events in a common calendar viewable by blog visitors.
Version: 0.9.8
Author: Eran Miller
Author URI: http://eranmiller.com
License: GPL2
*/

/*  Copyright 2011  Eran Miller <email: plugins@eranmiller.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// disallow direct access to the plugin file
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)){
	die('Sorry, but you cannot access this page directly.');
}

define('AEC_PLUGIN_VERSION', '0.9.8');
define('AEC_DOMAIN', 'aec_');
define('AEC_PLUGIN_FILE', basename(__FILE__));
define('AEC_PLUGIN_NAME', str_replace('.php', '', AEC_PLUGIN_FILE));
define('AEC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AEC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AEC_EVENT_TABLE', AEC_DOMAIN . 'event');
define('AEC_CATEGORY_TABLE', AEC_DOMAIN . 'event_category');
define('AEC_PLUGIN_HOMEPAGE', 'http://wordpress.org/extend/plugins/' . AEC_PLUGIN_NAME . '/');
define('AEC_WP_DATE_FORMAT', get_option('date_format'));
define('AEC_WP_TIME_FORMAT', get_option('time_format'));
define('AEC_WP_DATE_TIME_FORMAT', AEC_WP_DATE_FORMAT . ' ' . AEC_WP_TIME_FORMAT);
define('AEC_DB_DATE_TIME_FORMAT', 'Y-m-d H:i:s');

if (!class_exists('ajax_event_calendar')){
	class ajax_event_calendar{

		private $required_fields = array();

		function __construct(){
			add_action('plugins_loaded', array($this, 'version_patches'));
		    add_action('init', array($this, 'localize_plugin'), 10, 1);
			// future use placeholder: add_action('admin_notices', array($this, 'show_notices'));
			add_action('admin_menu', array($this, 'set_admin_menu'));
			add_action('admin_init', array($this, 'aec_options_init'));
			add_action('delete_user', array($this, 'confirm_delete_user_events'));
			add_filter('manage_users_columns', array($this, 'add_events_column'));
			add_filter('manage_users_custom_column', array($this, 'manage_events_column'), 10, 3);
			add_filter('plugin_action_links', array($this, 'settings_link'), 10, 2);

			add_filter('the_posts', array($this, 'load_js_and_css'));
			add_shortcode('calendar', array($this, 'show_calendar'));

			// ajax hooks
			add_action('wp_ajax_nopriv_get_events', array($this, 'show_events'));
			add_action('wp_ajax_get_events', array($this, 'show_events'));
			add_action('wp_ajax_nopriv_get_event', array($this, 'show_event'));
			add_action('wp_ajax_get_event', array($this, 'show_event'));
			add_action('wp_ajax_admin_event', array($this, 'admin_event'));
			add_action('wp_ajax_return_duration', array($this, 'return_duration'));
			add_action('wp_ajax_add_event', array($this, 'add_event'));
			add_action('wp_ajax_update_event', array($this, 'update_event'));
			add_action('wp_ajax_delete_event', array($this, 'delete_event'));
			add_action('wp_ajax_move_event', array($this, 'move_event'));
			add_action('wp_ajax_add_category', array($this, 'add_category'));
			add_action('wp_ajax_update_category', array($this, 'update_category'));
			add_action('wp_ajax_delete_category', array($this, 'delete_category'));
			add_action('wp_ajax_change_category', array($this, 'change_category'));
		}

		function install(){
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			global $wpdb;
			if ($wpdb->get_var('SHOW TABLES LIKE "' . $wpdb->prefix . AEC_EVENT_TABLE . '"') != $wpdb->prefix . AEC_EVENT_TABLE) {
				$sql = 'CREATE TABLE ' . $wpdb->prefix . AEC_EVENT_TABLE . ' (
						id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
						user_id INT(10) UNSIGNED NOT NULL,
						title VARCHAR(100) NOT NULL,
						start DATETIME NOT NULL,
						end DATETIME NOT NULL,
						allDay TINYINT(1) UNSIGNED DEFAULT 0,
						category_id TINYINT(4) UNSIGNED NOT NULL,
						description VARCHAR(1000),
						link VARCHAR(100),
						venue VARCHAR(100),
						address VARCHAR(100),
						city VARCHAR(50),
						state CHAR(2),
						zip VARCHAR(10),
						contact VARCHAR(50),
						contact_info VARCHAR(50),
						access TINYINT(1) UNSIGNED DEFAULT 0,
						rsvp TINYINT(1) UNSIGNED DEFAULT 0);';
				dbDelta($sql);
			}

			if ($wpdb->get_var('SHOW TABLES LIKE "' . $wpdb->prefix . AEC_CATEGORY_TABLE . '"') != $wpdb->prefix . AEC_CATEGORY_TABLE){
				$sql = 'CREATE TABLE ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' (
							id TINYINT(4) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
							category VARCHAR(25) NOT NULL,
							bgcolor CHAR(6) NOT NULL,
							fgcolor CHAR(6) NOT NULL
						);
						## DEFAULT CATEGORIES
						INSERT INTO ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' (id, category, bgcolor, fgcolor)
						VALUES 	(NULL, "Event", "517ed6", "FFFFFF"),
								(NULL, "Deadline", "e3686c", "FFFFFF"),
								(NULL, "Volunteer", "8fc9b0", "FFFFFF");';
				dbDelta($sql);
			}

			// add new role
			add_role('calendar_contributor', 'Calendar Contributor', array(
				'read' 						=> 1,
				AEC_DOMAIN . 'add_events' 	=> 1
			));

			// add calendar capabilities to administrator
			$role = get_role('administrator');
			$role->add_cap(AEC_DOMAIN . 'add_events');
			$role->add_cap(AEC_DOMAIN . 'manage_events');
			$role->add_cap(AEC_DOMAIN . 'manage_calendar');
		}

		// settings initialization and patches
		function version_patches(){
			$plugin_updated = false;
			$options 		= get_option(AEC_DOMAIN . 'options');

			// manual update
			if (!is_array($options) || !isset($options['reset']) || $options['reset'] == '1') {
				$plugin_default_options = array(
					'show_weekends'		=> '1',
					'show_map_link'		=> '1',
					'menu' 				=> '1',
					'limit' 			=> '1',
					'title' 			=> '2',
					'venue' 			=> '1',
					'address'			=> '2',
					'city' 				=> '2',
					'state' 			=> '2',
					'zip'				=> '2',
					'link' 				=> '1',
					'description' 		=> '2',
					'contact' 			=> '2',
					'contact_info' 		=> '2',
					'accessible' 		=> '1',
					'rsvp' 				=> '1',
					'reset' 			=> '0'
				);
				update_option(AEC_DOMAIN . 'options', $plugin_default_options);
			}

			// incremental patches
			// < 0.9.6
			if (version_compare(get_option(AEC_DOMAIN . 'version'), '0.9.6', '<')) {
				// if not present, add title as required option field
				if (!isset($options['title'])) {
					$options['title'] = 2;
					update_option(AEC_DOMAIN . 'options', $options);
				}

				// remove outdated widget option
				delete_option('widget_upcoming_events');

				// update database fields
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				global $wpdb;
				$sql = 'ALTER TABLE ' . $wpdb->prefix . AEC_EVENT_TABLE . ' '
					. 'modify venue VARCHAR(100),'
					. 'modify city VARCHAR(50),'
					. 'modify state CHAR(2),'
					. 'modify zip VARCHAR(10),'
					. 'modify contact VARCHAR(50),'
					. 'modify contact_info VARCHAR(50);';
				$wpdb->query($sql);
				$plugin_updated = true;
			}

			// < 0.9.8
			if (version_compare(get_option(AEC_DOMAIN . 'version'), '0.9.8', '<')) {

				// remove capability - replaced by aec-manage-calendar
				$role = get_role('administrator');
				$role->remove_cap('aec_run_reports');
				
				// if not present, add options
				if (!isset($options['show_weekends']) || !isset($options['show_map_link'])) {
					$options['show_weekends'] = 1;
					$options['show_map_link'] = 1;
					update_option(AEC_DOMAIN . 'options', $options);
				}
				$plugin_updated = true;
			}

			if ($plugin_updated) {
				// on patch completion update plugin version
				update_option(AEC_DOMAIN . 'version', AEC_PLUGIN_VERSION);

				$this->generate_css();

				// add sample event once plugin has gone through all update routines
				include_once(AEC_PLUGIN_PATH . 'unit_tests.php');
			}
		}

		function show_notices() {
			// Shows as an error message. You could add a link to the right page if you wanted.
			$this->render_message("You need to upgrade your database as soon as possible...", true);
			// admin only
			if (current_user_can('manage_options')) {
				// $this->render_message("Hello admins!");
			}
		}

		function render_message($message, $errormsg = false){
			if ($errormsg) {
				echo '<div id="message" class="error">';
			} else {
				echo '<div id="message" class="updated fade">';
			}
			echo "<p><strong>$message</strong></p></div>";
		}

	    function localize_plugin($page){
			load_plugin_textdomain( AEC_PLUGIN_NAME, false, AEC_PLUGIN_NAME . '/locale/' );

			// load jquery version 1.6.1
			if (!is_admin()) {
				wp_deregister_script('jquery');
				wp_register_script('jquery', AEC_PLUGIN_URL . 'js/jquery-1.6.1.min.js', false, '1.6.1', true);
			}
		}

		function set_admin_menu(){
			if (function_exists('add_options_page'))

				// define help text
				$help = '<h3>' . __('Ajax Event Calendar', AEC_PLUGIN_NAME) . ' <small>[v' . AEC_PLUGIN_VERSION . ']</small></h3>';
				$help .= __('Plugin help available', AEC_PLUGIN_NAME) . ' <a href="' . AEC_PLUGIN_HOMEPAGE . '" target="_blank">' . __('here', AEC_PLUGIN_NAME) . '</a>';
				$help .= '<br>' . __('Created by', AEC_PLUGIN_NAME) . ' <a href="http://eranmiller.com" target="_blank">Eran Miller</a>';

				$page = add_menu_page('Ajax Event Calendar',  __('Calendar', AEC_PLUGIN_NAME), AEC_DOMAIN . 'add_events', AEC_PLUGIN_FILE, array($this, 'admin_calendar'), AEC_PLUGIN_URL . 'css/images/calendar.png', 30);

				// only load scripts and styles on plugin page
				add_action("admin_print_scripts-$page", array($this, 'admin_calendar_js'));
				add_action("admin_print_styles-$page", array($this, 'admin_calendar_css'));
				add_contextual_help($page, $help);

				// sub menu page: categories
				$sub_category = add_submenu_page(AEC_PLUGIN_FILE, 'Categories', __('Categories', AEC_PLUGIN_NAME), AEC_DOMAIN . 'manage_calendar', 'event_categories', array($this, 'admin_category'));
				add_contextual_help($sub_category, $help);

				// only load scripts and styles on sub page
				add_action("admin_print_scripts-$sub_category", array($this, 'admin_category_js'));
				add_action("admin_print_styles-$sub_category", array($this, 'admin_category_css'));

				// sub menu page: categories
				$sub_report = add_submenu_page(AEC_PLUGIN_FILE, 'Activity Report', __('Activity Report', AEC_PLUGIN_NAME), AEC_DOMAIN . 'manage_calendar', 'activity_report', array($this, 'run_reports'));
				add_contextual_help($sub_report, $help);

				// settings menu
				$sub_options = add_options_page('Calendar', __('Ajax Event Calendar', AEC_PLUGIN_NAME), AEC_DOMAIN . 'manage_calendar', __FILE__, array($this, 'aec_options_page'));
				add_contextual_help($sub_options, $help);
		}

		function return_duration(){
			// convert event array to object
			$event->start 	= $_POST['event']['start'];
			$event->end		= $_POST['event']['end'];
			$event->allDay 	= $_POST['event']['allDay'];

			header("Content-Type: application/json");
			echo json_encode($this->process_duration($event));
			exit;
		}

		function process_duration($event){
			$format	= ($event->allDay) ? AEC_WP_DATE_FORMAT : AEC_WP_DATE_TIME_FORMAT;
			$start	= DateTime::createFromFormat($format, $event->start);
			$end 	= DateTime::createFromFormat($format, $event->end);
			if ($start && $end) {
				$int = $start->diff($end);
				$int->d = ($event->allDay) ? $int->d+1 : $int->d;		// add one to day value of "allday" events

				$out = array();
				if ($int->y) { array_push($out, sprintf(_n('%d Year', '%d Years', $int->y, AEC_PLUGIN_NAME), $int->y)); }
				if ($int->m) { array_push($out, sprintf(_n('%d Month', '%d Months', $int->m, AEC_PLUGIN_NAME), $int->m)); }
				if ($int->d) { array_push($out, sprintf(_n('%d Day', '%d Days', $int->d, AEC_PLUGIN_NAME), $int->d)); }
				if ($int->h) { array_push($out, sprintf(_n('%d Hour', '%d Hours', $int->h, AEC_PLUGIN_NAME), $int->h)); }
				if ($int->i) { array_push($out, sprintf(_n('%d Minute', '%d Minutes', $int->i, AEC_PLUGIN_NAME), $int->i)); }

				return implode('<br>', $out);
			}
		}

		function date_convert($date, $from, $to){
			//ajax_event_calendar::log($date .' '. $from .' '. $to);
			$convert = DateTime::createFromFormat($from, $date);
			return $convert->format($to);
		}

		// convert php to jquery datepicker format
		function convert_date_format($format){
			$php = array(
				'j',    // day, no leading zero
				'd',    // day, 2 digits
				'l',    // day, full name  (lowercase 'L')
				'n',    // month, no leading zero
				'm',    // month, 2 digits
				'F',    // month, full name
				'Y'     // year, 4 digits
				//'y'		// year, 2 digits
			);
			$jqdp = array(
				'd',	// day, no leading zero
				'dd',	// day, 2 digits
				'DD',	// day, full name
				'm',	// month, no leading zero
				'mm',	// month, 2 digits
				'MM',	// month, full name
				'yy'	// year, 4 digits
				//'y'	// year, 2 digits
			);
			foreach($php as &$p){
				$p = '/'.$p.'/';
			}
			return preg_replace($php, $jqdp, $format);
		}

		function is24hrs($format){
			// g | G	 12- | 24-hour, without leading zeros
			// h | H	 12- | 24-hour, with leading zeros
			return (strpos($format, 'G') !== false || strpos($format, 'H') !== false) ? true : false;
		}

		function common_vars(){
			$options = get_option(AEC_DOMAIN . 'options');

			// initialize required form fields
			foreach ($options as $option => $value) {
				if ($value == 2) $this->add_required_field($option);
			}

			$is24hrs = $this->is24hrs(AEC_WP_TIME_FORMAT);

			return array(
				'locale'					=> substr(get_locale(), 0, 2),	// first two characters of locale
				'start_of_week' 			=> get_option('start_of_week'),
				'is24hrs'					=> $is24hrs,
				'show_weekends'				=> $options['show_weekends'],
				'agenda_time_format' 		=> ($is24hrs) ? 'H:mm{ - H:mm}' : 'h:mmt{ - h:mmt}',
				'other_time_format' 		=> ($is24hrs) ? 'H:mm' : 'h:mmt',
				'axis_time_format' 			=> ($is24hrs) ? 'HH:mm' : 'h:mmt',
				'datepicker_format' 		=> $this->convert_date_format(AEC_WP_DATE_FORMAT),
				'limit' 					=> $options['limit'],
				'calculating'				=> __('Calculating...', AEC_PLUGIN_NAME),
				'minutes'					=> __('Minutes', AEC_PLUGIN_NAME),
				'hours'						=> __('Hours', AEC_PLUGIN_NAME),
				'days'						=> __('Days', AEC_PLUGIN_NAME),
				'january' 					=> __('January', AEC_PLUGIN_NAME),
				'february'					=> __('February', AEC_PLUGIN_NAME),
				'march' 					=> __('March', AEC_PLUGIN_NAME),
				'april' 					=> __('April', AEC_PLUGIN_NAME),
				'may' 						=> __('May', AEC_PLUGIN_NAME),
				'june' 						=> __('June', AEC_PLUGIN_NAME),
				'july' 						=> __('July', AEC_PLUGIN_NAME),
				'august' 					=> __('August', AEC_PLUGIN_NAME),
				'september'					=> __('September', AEC_PLUGIN_NAME),
				'october' 					=> __('October', AEC_PLUGIN_NAME),
				'november' 					=> __('November', AEC_PLUGIN_NAME),
				'december'					=> __('December', AEC_PLUGIN_NAME),
				'jan' 						=> __('Jan', AEC_PLUGIN_NAME),
				'feb' 						=> __('Feb', AEC_PLUGIN_NAME),
				'mar' 						=> __('Mar', AEC_PLUGIN_NAME),
				'apr' 						=> __('Apr', AEC_PLUGIN_NAME),
				'may' 						=> __('May', AEC_PLUGIN_NAME),
				'jun' 						=> __('Jun', AEC_PLUGIN_NAME),
				'jul' 						=> __('Jul', AEC_PLUGIN_NAME),
				'aug' 						=> __('Aug', AEC_PLUGIN_NAME),
				'sep' 						=> __('Sep', AEC_PLUGIN_NAME),
				'oct' 						=> __('Oct', AEC_PLUGIN_NAME),
				'nov' 						=> __('Nov', AEC_PLUGIN_NAME),
				'dec'						=> __('Dec', AEC_PLUGIN_NAME),
				'sunday'					=> __('Sunday', AEC_PLUGIN_NAME),
				'monday'					=> __('Monday', AEC_PLUGIN_NAME),
				'tuesday'					=> __('Tuesday', AEC_PLUGIN_NAME),
				'wednesday'					=> __('Wednesday', AEC_PLUGIN_NAME),
				'thursday'					=> __('Thursday', AEC_PLUGIN_NAME),
				'friday'					=> __('Friday', AEC_PLUGIN_NAME),
				'saturday'					=> __('Saturday', AEC_PLUGIN_NAME),
				'sun'						=> __('Sun', AEC_PLUGIN_NAME),
				'mon'						=> __('Mon', AEC_PLUGIN_NAME),
				'tue'						=> __('Tue', AEC_PLUGIN_NAME),
				'wed'						=> __('Wed', AEC_PLUGIN_NAME),
				'thu'						=> __('Thu', AEC_PLUGIN_NAME),
				'fri'						=> __('Fri', AEC_PLUGIN_NAME),
				'sat'						=> __('Sat', AEC_PLUGIN_NAME),
				'today'						=> __('Today', AEC_PLUGIN_NAME),
				'month'						=> __('Month', AEC_PLUGIN_NAME),
				'week'						=> __('Week', AEC_PLUGIN_NAME),
				'day'						=> __('Day', AEC_PLUGIN_NAME),
				'all_day'					=> __('All Day', AEC_PLUGIN_NAME),
				'close_event_form'			=> __('Close Event Form', AEC_PLUGIN_NAME),
				'loading_event_form'		=> __('Loading Event Form...', AEC_PLUGIN_NAME),
				'update_btn'				=> __('Update', AEC_PLUGIN_NAME),
				'delete_btn'				=> __('Delete', AEC_PLUGIN_NAME),
				'category_type'				=> __('Category type', AEC_PLUGIN_NAME),
				'hide_all_notifications'	=> __('hide all notifications', AEC_PLUGIN_NAME),
				'has_been_created'			=> __('has been created.', AEC_PLUGIN_NAME),
				'has_been_modified'			=> __('has been modified.', AEC_PLUGIN_NAME),
				'has_been_deleted'			=> __('has been deleted.', AEC_PLUGIN_NAME),
				'add_event'					=> __('Add Event', AEC_PLUGIN_NAME),
				'edit_event'				=> __('Edit Event', AEC_PLUGIN_NAME),
				'delete_event'				=> __('Delete this event?', AEC_PLUGIN_NAME),
				'success'					=> __('Success!', AEC_PLUGIN_NAME),
				'whoops'					=> __('Whoops!', AEC_PLUGIN_NAME)
			);
		}

		// back-end calendar
		function admin_calendar_vars(){
			$is_admin = (current_user_can('manage_options') == true) ? 1 : 0;
			return array_merge($this->common_vars(),
				array(
					'admin' 					=> $is_admin,
					'required_fields'			=> join(",", $this->get_required_fields()),
					'editable'					=> true,
					'error_past_create'			=> __('You cannot create events in the past.', AEC_PLUGIN_NAME),
					'error_future_create'		=> __('You cannot create events more than a year in advance.', AEC_PLUGIN_NAME),
					'error_past_resize'			=> __('You cannot resize expired events.', AEC_PLUGIN_NAME),
					'error_past_move'			=> __('You cannot move events into the past.', AEC_PLUGIN_NAME),
					'error_past_edit'			=> __('You cannot edit expired events.', AEC_PLUGIN_NAME),
					'error_invalid_duration'	=> __('Invalid duration, please adjust your time inputs.', AEC_PLUGIN_NAME)
				)
			);
		}

		function admin_calendar_js(){
			wp_enqueue_script('jquery');
			wp_enqueue_script('jq_ui', AEC_PLUGIN_URL . 'js/jquery-ui-1.8.13.custom.min.js', array('jquery'), null, true);
			wp_enqueue_script('timePicker', AEC_PLUGIN_URL . 'js/jquery.timePicker.min.js', array('jquery'), null, true);
			wp_enqueue_script('fullcalendar', AEC_PLUGIN_URL . 'js/jquery.fullcalendar.min.js', array('jquery'), null, true);
			wp_enqueue_script('growl', AEC_PLUGIN_URL . 'js/jquery.jgrowl.min.js', array('jquery'), null, true);
			wp_enqueue_script('simplemodal', AEC_PLUGIN_URL . 'js/jquery.simplemodal.1.4.1.min.js', array('jquery'), null, true);
			wp_enqueue_script('init_admin_calendar', AEC_PLUGIN_URL . 'js/jquery.init_admin_calendar.js', array('jquery'), null, true);
			wp_localize_script('init_admin_calendar', 'custom', $this->admin_calendar_vars());
		}

		function admin_calendar_css(){
			wp_enqueue_style('jq_ui_css', AEC_PLUGIN_URL . 'css/jquery-ui-1.8.13.custom.css');
			wp_enqueue_style('custom', AEC_PLUGIN_URL . 'css/custom.css');
			wp_enqueue_style('categories', AEC_PLUGIN_URL . 'css/cat_colors.css');
		}

		function admin_calendar(){
			if (!current_user_can(AEC_DOMAIN . 'add_events'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			require_once AEC_PLUGIN_PATH . 'inc/admin-calendar.php';
		}

		// front-end calendar
		function show_calendar_vars(){
			$options = get_option(AEC_DOMAIN . 'options');
			return array_merge($this->common_vars(),
				array(
					'ajaxurl'  	=> admin_url('admin-ajax.php'),		// required for non-admin ajax pages
					'editable'	=> false
				)
			);
		}

		// front-end scripts and styles
		function load_js_and_css($pages){
			
			if (empty($pages)) return $pages;
			$shortcode_found = false;
			foreach ($pages as $page) {
				if (stripos($page->post_content, '[calendar]') !== false) {
					$shortcode_found = true;
					break;
				}
			}

			wp_enqueue_script('jquery');
			wp_enqueue_script('simplemodal', AEC_PLUGIN_URL . 'js/jquery.simplemodal.1.4.1.min.js', array('jquery'), null, true);
			wp_enqueue_style('custom', AEC_PLUGIN_URL . 'css/custom.css');
			wp_enqueue_style('categories', AEC_PLUGIN_URL . 'css/cat_colors.css');

			// [calendar] shortcode present
			if ($shortcode_found) {
				wp_enqueue_script('fullcalendar', AEC_PLUGIN_URL . 'js/jquery.fullcalendar.min.js', array('jquery'), null, true);
				wp_enqueue_script('init_show_calendar', AEC_PLUGIN_URL . 'js/jquery.init_show_calendar.js', array('jquery'), null, true);
				wp_localize_script('init_show_calendar', 'custom', $this->show_calendar_vars());
			} else {
				wp_enqueue_script('init_dialog_only', AEC_PLUGIN_URL . 'js/jquery.init_dialog_only.js', array('jquery'), null, true);
				wp_localize_script('init_dialog_only', 'custom', $this->show_calendar_vars());
			}
			return $pages;
		}

		function show_calendar(){
			require_once AEC_PLUGIN_PATH . 'inc/show-calendar.php';
		}

		// admin category page
		function admin_category_vars(){
			return array_merge($this->common_vars(),
				array(
					'error_blank_category'		=> __('Category type cannot be a blank value.', AEC_PLUGIN_NAME),
					'confirm_category_delete'	=> __('Are you sure you want to delete this category type?', AEC_PLUGIN_NAME),
					'confirm_category_reassign'	=> __('Several events are associated with this category. Click OK to reassign these events to the default category.', AEC_PLUGIN_NAME),
					'events_reassigned'			=> __('Events have been reassigned to the default category.', AEC_PLUGIN_NAME)
				)
			);
		}

		function admin_category_js(){
			wp_enqueue_script('jquery');
			wp_enqueue_script('jq_ui', AEC_PLUGIN_URL . 'js/jquery-ui-1.8.13.custom.min.js', array('jquery'), null, true);
			wp_enqueue_script('growl', AEC_PLUGIN_URL . 'js/jquery.jgrowl.min.js', array('jquery'), null, true);
			wp_enqueue_script('color_picker', AEC_PLUGIN_URL . 'js/jquery.miniColors.min.js', array('jquery'), null, true);
			wp_enqueue_script('inline_edit', AEC_PLUGIN_URL . 'js/jquery.jeditable.min.js', array('jquery'), null, true);
			wp_enqueue_script('init_show_category', plugins_url('/js/jquery.init_admin_category.js', __FILE__), array('jquery'), null, true);
			wp_localize_script('init_show_category', 'custom', $this->admin_category_vars());
		}

		function admin_category_css(){
			wp_enqueue_style('custom', AEC_PLUGIN_URL . 'css/custom.css');
		}

		function admin_category(){
			if (!current_user_can(AEC_DOMAIN . 'manage_calendar'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			include_once(AEC_PLUGIN_PATH . 'inc/admin-category.php');
		}

		// admin reports page
		function run_reports(){
			if (!current_user_can(AEC_DOMAIN . 'manage_calendar'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			include_once(AEC_PLUGIN_PATH . 'inc/admin-reports.php');
		}

		function report_monthly_activity(){
			global $wpdb;
			$result = $wpdb->get_results('SELECT COUNT(a.category_id) AS cnt, b.category FROM ' .
										$wpdb->prefix . AEC_EVENT_TABLE . ' AS a ' .
										'INNER JOIN ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' AS b ' .
										'ON a.category_id = b.id ' .
										'WHERE MONTH(start) = MONTH(NOW()) ' .
										'GROUP BY category_id ' .
										'ORDER BY cnt DESC;'
									);
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			return $result;
		}

		// event detail modal
		function admin_event(){
			if (!current_user_can(AEC_DOMAIN . 'add_events'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			require_once AEC_PLUGIN_PATH . 'inc/admin-event.php';
			exit;
		}

		// show event detail modal
		function show_event(){
			require_once AEC_PLUGIN_PATH . 'inc/show-event.php';
		}

		function add_event($display=true){
			if (!isset($_POST['event'])) return;

			$input = $this->cleanse_event_input($_POST['event']);

			global $wpdb;
			$result = $wpdb->insert($wpdb->prefix . AEC_EVENT_TABLE,
									array('user_id' 		=> $input['user_id'],
										  'title'	 		=> $input['title'],
										  'start'			=> $input['start'],
										  'end'				=> $input['end'],
										  'allDay'	 		=> $input['allDay'],
										  'category_id'		=> $input['category_id'],
										  'description'		=> $input['description'],
										  'link'			=> $input['link'],
										  'venue'			=> $input['venue'],
										  'address'			=> $input['address'],
										  'city'			=> $input['city'],
										  'state'			=> $input['state'],
										  'zip'				=> $input['zip'],
										  'contact'			=> $input['contact'],
										  'contact_info'	=> $input['contact_info'],
										  'access'			=> $input['access'],
										  'rsvp'			=> $input['rsvp']
										),
									array('%d',				// user_id
										  '%s',				// title
										  '%s',				// start
										  '%s',				// end
										  '%d',				// allDay
										  '%d',				// category_id
										  '%s',				// description
										  '%s',				// link
										  '%s',				// venue
										  '%s',				// address
										  '%s',				// city
										  '%s',				// state
										  '%s',				// zip
										  '%s',				// contact
										  '%s',				// contact_info
										  '%d',				// access
										  '%d' 				// rsvp
										)
								);
			if ($result === false){
				$this->log($wpdb->print_error());
			} else {
				if ($display) {
					$input['id'] = $wpdb->insert_id;		// id of newly created row
					$input['allDay'] = ($input['allDay']) ? true : false;
					$output = array(
						'id'	 	=> $input['id'],
						'title'  	=> stripslashes($input['title']),
						'start'		=> $input['start'],
						'end'		=> $input['end'],
						'allDay' 	=> $input['allDay'],
						'className'	=> 'cat' . $input['category_id']
					);
					header("Content-Type: application/json");
					echo json_encode($output);
					exit;
				}
			}
			return;
		}

		function move_event(){
			if (!isset($_POST)) return;

			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_EVENT_TABLE,
									array('start'	=> $input['start'],
										  'end'	 	=> $input['end'],
										  'allDay'	=> $input['allDay']
										),
									array('id' 		=> $input['id']),
									array('%s',		// start
										  '%s',		// end
										  '%d'		// allDay
										),
									array ('%d')	// id
								);
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			header("Content-Type: application/json");
			echo json_encode($result);
			exit;
		}

		function update_event(){
			if (!isset($_POST['event'])) return;

			$input = $this->cleanse_event_input($_POST['event']);
			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_EVENT_TABLE,
									array('user_id' 		=> $input['user_id'],
										  'title'	 		=> $input['title'],
										  'start'			=> $input['start'],
										  'end'				=> $input['end'],
										  'allDay'			=> $input['allDay'],
										  'category_id'		=> $input['category_id'],
										  'description'		=> $input['description'],
										  'link'			=> $input['link'],
										  'venue'			=> $input['venue'],
										  'address'			=> $input['address'],
										  'city'			=> $input['city'],
										  'state'			=> $input['state'],
										  'zip'				=> $input['zip'],
										  'contact'			=> $input['contact'],
										  'contact_info'	=> $input['contact_info'],
										  'access'			=> $input['access'],
										  'rsvp'			=> $input['rsvp']
										),
									array('id' 				=> $input['id']),
									array('%d',				// user_id
										  '%s',				// title
										  '%s',				// start
										  '%s',				// end
										  '%d',				// allDay
										  '%d',				// category_id
										  '%s',				// description
										  '%s',				// link
										  '%s',				// venue
										  '%s',				// address
										  '%s',				// city
										  '%s',				// state
										  '%s',				// zip
										  '%s',				// contact
										  '%s',				// contact_info
										  '%d',				// access
										  '%d' 				// rsvp
										),
									array ('%d') 			// id
								);
			if ($result === false){
				$this->log($wpdb->print_error());
			} else{
				$input['allDay'] = ($input['allDay']) ? true : false;
				$output = array(
					'id'	 	=> $input['id'],
					'title'  	=> stripslashes($input['title']),
					'start'		=> $input['start'],
					'end'		=> $input['end'],
					'allDay' 	=> $input['allDay'],
					'className'	=> 'cat' . $input['category_id']
				);
				header("Content-Type: application/json");
				echo json_encode($output);
				exit;
			}
		}

		function delete_event(){
			if (!isset($_POST['id'])) return;

			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE id = %d;', $_POST['id']));
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			header("Content-Type: application/json");
			echo json_encode($result);
			exit;
		}

		function get_event($id){
			global $wpdb;
			$result = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE id = %d ORDER BY start;', $id));
			if ($result === false){
				$this->log($wpdb->print_error());
			}

			// format fields for user display
			$result->start 			= $this->date_convert($result->start, AEC_DB_DATE_TIME_FORMAT, AEC_WP_DATE_TIME_FORMAT);
			$result->end 			= $this->date_convert($result->end, AEC_DB_DATE_TIME_FORMAT, AEC_WP_DATE_TIME_FORMAT);
			$result->title		 	= stripslashes($result->title);
			$result->description 	= stripslashes($result->description);
			$result->link 			= stripslashes($result->link);
			$result->venue 			= stripslashes($result->venue);
			$result->address 		= stripslashes($result->address);
			$result->city 			= stripslashes($result->city);
			$result->contact 		= stripslashes($result->contact);
			$result->contact_info	= stripslashes($result->contact_info);
			return $result;
		}

		function show_events(){
			// users that are not logged-in see all events
			$user = false;
			global $current_user;
			get_currentuserinfo();
			if ($_POST['edit']) {
				// users with aec_manage_events capability can edit all events
				// users with aec_add_events capability can see/edit events only they create
				$user = (is_user_logged_in()) ? ((current_user_can(AEC_DOMAIN . 'manage_events')) ? false : $current_user->ID) : false;
			}
			$this->get_events($user, $_POST['start'], $_POST['end']);
		}

		function get_events($user_id, $start, $end){
			global $wpdb;
			$anduser = ($user_id) ? ' AND user_id = ' . $user_id : '';
			$start = date('Y-m-d', $start);
			$end = date('Y-m-d', $end);
			$results = $wpdb->get_results('SELECT
											id,
											title,
											start,
											end,
											allday,
											category_id
											FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
											WHERE ((start >= "' . $start . '"
											AND start < "' . $end . '")
											OR (end >= "' . $start . '"
											AND end < "' . $end . '")
											OR (start < "' . $start . '"
											AND end > "' . $end . '"))' .
											$anduser .
											' ORDER BY start;'
										);
			if ($results === false){
				$this->log($wpdb->print_error());
			} else{
				$output = array();
				foreach($results as $event){
					$allDay = ($event->allday) ? true : false;
					array_push($output, array(
						'id'		=> $event->id,
						'title'		=> stripslashes($event->title),
						'start'		=> $event->start,
						'end'		=> $event->end,
						'allDay'	=> $allDay,
						'className'	=> 'cat' . $event->category_id
					));
				}
				header("Content-Type: application/json");
				echo json_encode($output);
				exit;
			}
		}

		function add_category(){
			if (!isset($_POST['category_data'])) return;
			$input = $this->cleanse_category_input($_POST['category_data']);

			global $wpdb;
			$result = $wpdb->insert($wpdb->prefix . AEC_CATEGORY_TABLE,
									array('category'	=> $input['category'],
										  'bgcolor'		=> $input['bgcolor'],
										  'fgcolor' 	=> $input['fgcolor']
										),
									array('%s',
										  '%s',
										  '%s'
										));
			if ($result === false){
				$this->log($wpdb->print_error());
			} else{
				$id		= $wpdb->insert_id;	// ID of newly created row
				$output = array(
					'id'	 	=> $id,
					'category'  => $input['category'],
					'bgcolor'	=> $input['bgcolor'],
					'fgcolor'	=> $input['fgcolor']
				);
				header("Content-Type: application/json");
				echo json_encode($output);
			}
			$this->generate_css();
			exit;
		}

		function update_category(){
			if (!isset($_POST['category_data'])) return;
			$input = $this->cleanse_category_input($_POST['category_data']);

			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_CATEGORY_TABLE,
									array('category'	=> $input['category'],
										  'bgcolor'		=> $input['bgcolor'],
										  'fgcolor'		=> $input['fgcolor']
									),
									array('id' => $input['id']),
									array('%s',
										  '%s',
										  '%s'
									),
									array ('%d') //id
								);
			if ($result === false){
				$this->log($wpdb->print_error());
			} else{
				$output = array(
					'category'  => $input['category'],
					'bgcolor'	=> $input['bgcolor'],
					'fgcolor'	=> $input['fgcolor']
				);
				header("Content-Type: application/json");
				echo json_encode($output);
			}
			$this->generate_css();
			exit;
		}

		function delete_category(){
			if (!isset($_POST['id'])) return;

			global $wpdb;
			$used = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) as count FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE category_id = %d;', $_POST['id']));
			if ($used){
				header("Content-Type: application/json");
				echo 'false';
				exit;
			} else{
				$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' WHERE id = %d;', $_POST['id']));
				if ($result === false){
					$this->log($wpdb->print_error());
				}
				$this->generate_css();
				header("Content-Type: application/json");
				echo $result;
				exit;
			}
		}

		function change_category(){
			if (!isset($_POST['id'])) return;

			global $wpdb;
			$result = $wpdb->get_results($wpdb->prepare('UPDATE '. $wpdb->prefix . AEC_EVENT_TABLE . ' SET category_id=1 WHERE category_id= %d;', $_POST['id']));
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			return $this->delete_category($id);
		}

		function get_categories(){
			global $wpdb;
			$result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' ORDER BY id;');
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			return $result;
		}

		function parse_input($input){
			$clean = array();
			if (!is_array($input)){

				// convert serialized form into an array
				parse_str($input, $output_array);
				$input = $output_array;
			}
			foreach ($input as $key => $value){
				// trim whitespace from input
				$clean[$key] = trim($value);
			}
			return $clean;
		}

		function cleanse_event_input($input){
			$clean = $this->parse_input($input);

			// convert dates to database format
			$start 	= $clean['start_date'] . ' ' . $clean['start_time'];
			$end 	= $clean['end_date'] . ' ' . $clean['end_time'];

			$clean['start'] = $this->date_convert($start, AEC_WP_DATE_TIME_FORMAT, AEC_DB_DATE_TIME_FORMAT);
			$clean['end']	= $this->date_convert($end, AEC_WP_DATE_TIME_FORMAT, AEC_DB_DATE_TIME_FORMAT);

			return $clean;
		}

		function cleanse_category_input($input){
			$clean = $this->parse_input($input);
			$clean['bgcolor'] = str_replace('#', '', $clean['bgcolor']);	// strip '#' from color value, for storage
			$clean['fgcolor'] = str_replace('#', '', $clean['fgcolor']);
			return $clean;
		}

		function remove_table($table_name){
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DROP TABLE %s', $table_name));
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			echo $result;
		}

		function confirm_delete_user_events($user_id){

			// TODO: add filter to delete confirmation page displaing the number of events to be deleted.
			$event_count = $this->get_event_count($user_id);
			$this->delete_user_events($user_id);
		}

		function get_event_count($user_id){
			global $wpdb;
			$result = $wpdb->get_var('SELECT count(id)
									 FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
									 WHERE user_id = ' . $user_id . ';'
									);
			if ($result === false){
				$this->log($wpdb->print_error());
				return false;
			}
			return $result;
		}

		function delete_user_events($user_id){
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE user_id = %d;', $user_id));
			if ($result === false){
				$this->log($wpdb->print_error());
				return false;
			}
			return $result;
		}

		function generate_css(){
			$categories = $this->get_categories();
			$out = '';
			foreach ($categories as $category){
				$out .= '.cat' . $category->id;
				$out .= ',.cat' . $category->id . ' .fc-event-skin';
				$out .= ',.fc-agenda .cat' . $category->id;
				$out .= ',a.cat' . $category->id;
				$out .= ',a.cat' . $category->id . ':hover';
				$out .= ',a.cat' . $category->id . ':active';
				$out .= ',a.cat' . $category->id . ':visited';
				$out .= '{color:#' . $category->fgcolor . ' !important;background-color:#';
				$out .= $category->bgcolor . ' !important;border-color:#' . $category->bgcolor . ' !important}';
				$out .= "\n";
			}

			$cssFile = AEC_PLUGIN_PATH . "css/cat_colors.css";
			$fh = fopen($cssFile, 'w+') or die('cannot open file');
			fwrite($fh, $out);
			fclose($fh);
		}

		function add_required_field($field){
			array_push($this->required_fields, $field);
		}

		function get_required_fields(){
			if (count($this->required_fields))
				return $this->required_fields;
			return;
		}

		// validation placeholder
		function aec_options_validate($input){
			return $input;
		}

		// initialize plugin options
		function aec_options_init($page){
			$options = get_option(AEC_DOMAIN . 'options');
			register_setting(AEC_DOMAIN . 'plugin_options', AEC_DOMAIN . 'options', array($this, 'aec_options_validate'));
		}

		function aec_options_page(){
			if (!current_user_can(AEC_DOMAIN . 'manage_calendar'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			require_once AEC_PLUGIN_PATH . 'inc/admin-options.php';
		}

		function add_events_column($columns){
			$columns['calendar_events'] = __('Events', AEC_PLUGIN_NAME);
			return $columns;
		}

		function manage_events_column($empty='', $column_name, $user_id){
			if ($column_name == 'calendar_events'){
				$event_count = $this->get_event_count($user_id);
				return $event_count;
			}
		}

		// display settings link on the plugins page
		function settings_link($links, $file){
			if ($file == plugin_basename(__FILE__)){
				$posk_links = '<a href="' . get_admin_url() . 'options-general.php?page=' . AEC_PLUGIN_NAME . '/' . AEC_PLUGIN_FILE . '">' . __('Settings', AEC_PLUGIN_NAME) . '</a>';

				// make the 'Settings' link appear first
				array_unshift($links, $posk_links);
			}
			return $links;
		}

		function log($message){
			if(WP_DEBUG === true){
				if (is_array($message) || is_object($message)){
					error_log(print_r($message, true));
				} else {
					error_log($message);
				}
			}
			return;
		}
	}
}

register_activation_hook(__FILE__, array('ajax_event_calendar', 'install'));

if (class_exists('ajax_event_calendar')){
	if (version_compare(PHP_VERSION, '5', '<'))
		die(printf(__('Sorry, ' . AEC_PLUGIN_NAME . ' requires PHP 5 or higher. Your PHP version is "%s". Ask your web hosting service how to enable PHP 5 on your site.', AEC_PLUGIN_NAME), PHP_VERSION));

		// widgets code
		require_once AEC_PLUGIN_PATH . 'inc/widget-contributors.php';
		require_once AEC_PLUGIN_PATH . 'inc/widget-upcoming.php';
		$aec = new ajax_event_calendar();
}
?>