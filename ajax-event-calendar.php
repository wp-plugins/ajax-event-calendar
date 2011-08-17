<?php
/*
Plugin Name: Ajax Event Calendar
Plugin URI: http://wordpress.org/extend/plugins/ajax-event-calendar/
Description: A fully localized community calendar that allows authorized users to manage events in custom categories.
Version: 0.9.9.2
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

// plugin requires PHP5
if (version_compare(PHP_VERSION, '5', '<'))
	die(printf(__('Sorry, ' . AEC_PLUGIN_NAME . ' requires PHP 5 or higher. Your PHP version is "%s". Ask your web hosting service how to enable PHP 5 on your site.', AEC_PLUGIN_NAME), PHP_VERSION));

// disallow direct access to the plugin file
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)){
	die('Sorry, but you cannot access this page directly.');
}

define('AEC_PLUGIN_VERSION', '0.9.9.2');
define('AEC_PLUGIN_FILE', basename(__FILE__));
define('AEC_PLUGIN_NAME', str_replace('.php', '', AEC_PLUGIN_FILE));
define('AEC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AEC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AEC_EVENT_TABLE', 'aec_event');
define('AEC_CATEGORY_TABLE', 'aec_event_category');
define('AEC_PLUGIN_HOMEPAGE', 'http://wordpress.org/extend/plugins/' . AEC_PLUGIN_NAME . '/');
define('AEC_WP_DATE_FORMAT', get_option('date_format'));
define('AEC_WP_TIME_FORMAT', get_option('time_format'));
define('AEC_DB_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('AEC_LOCALE', substr(get_locale(), 0, 2));	// for javascript localization scripts

// if uncommented, overrides the location of the WP error log to the AEC plugin root
// @ini_set('error_log', AEC_PLUGIN_PATH . 'aec_debug.log');

if (!class_exists('ajax_event_calendar')){
	class ajax_event_calendar{

		private $required_fields  = array();
		private $shortcode_params = array();
		private $plugin_defaults  = array(
									'filter_label'		=> '',
									'limit' 			=> '0',
									'show_weekends'		=> '1',
									'show_map_link'		=> '1',
									'menu' 				=> '1',
									'make_links'		=> '0',
									'popup_links'		=> '1',
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

		function __construct(){
			add_action('plugins_loaded', array($this, 'version_patches'));
		    add_action('init', array($this, 'localize_plugin'), 10, 1);
			add_action('admin_menu', array($this, 'render_admin_menu'));
			add_action('admin_init', array($this, 'admin_options_initialize'));
			add_action('wp_print_scripts', array($this, 'frontend_calendar_scripts'));
			add_action('wp_print_styles', array($this, 'calendar_styles'));
			add_action('delete_user', array($this, 'delete_events_by_user'));

			// ajax hooks
			add_action('wp_ajax_nopriv_get_events', array($this, 'render_events'));
			add_action('wp_ajax_get_events', array($this, 'render_events'));
			add_action('wp_ajax_nopriv_get_event', array($this, 'render_frontend_modal'));
			add_action('wp_ajax_get_event', array($this, 'render_frontend_modal'));
			add_action('wp_ajax_admin_event', array($this, 'render_admin_modal'));
			add_action('wp_ajax_add_event', array($this, 'add_event'));
			add_action('wp_ajax_update_event', array($this, 'update_event'));
			add_action('wp_ajax_copy_event', array($this, 'copy_event'));
			add_action('wp_ajax_delete_event', array($this, 'delete_event'));
			add_action('wp_ajax_move_event', array($this, 'move_event'));
			add_action('wp_ajax_add_category', array($this, 'add_category'));
			add_action('wp_ajax_update_category', array($this, 'update_category'));
			add_action('wp_ajax_delete_category', array($this, 'confirm_delete_category'));
			add_action('wp_ajax_reassign_category', array($this, 'reassign_category'));

			// wordpress overrides
			add_filter('manage_users_columns', array($this, 'add_events_column'));
			add_filter('manage_users_custom_column', array($this, 'manage_events_column'), 10, 3);
			add_filter('plugin_action_links', array($this, 'settings_link'), 10, 2);
			add_filter('option_page_capability_aec_plugin_options', array($this, 'set_option_page_capability'));

			add_shortcode('calendar', array($this, 'render_frontend_calendar'));

			// register scripts
			wp_register_script('fullcalendar', AEC_PLUGIN_URL . 'js/jquery.fullcalendar.min.js', array('jquery'), '1.5.1', true);
			wp_register_script('simplemodal', AEC_PLUGIN_URL . 'js/jquery.simplemodal.1.4.1.min.js', array('jquery'), '1.4.1', true);
			wp_register_script('jquery-ui-datepicker', AEC_PLUGIN_URL . 'js/jquery.ui.datepicker.min.js', array('jquery-ui-core'), '1.8.5', true);
			wp_register_script('datepicker-locale', AEC_PLUGIN_URL . 'js/i18n/jquery.ui.datepicker-' . substr(get_locale(), 0, 2) . '.js', array('jquery-ui-datepicker'), '1.8.5', true);
			wp_register_script('timePicker', AEC_PLUGIN_URL . 'js/jquery.timePicker.min.js', array('jquery'), '5195', true);
			wp_register_script('growl', AEC_PLUGIN_URL . 'js/jquery.jgrowl.min.js', array('jquery'), '1.2.5', true);
			wp_register_script('miniColors', AEC_PLUGIN_URL . 'js/jquery.miniColors.min.js', array('jquery'), '0.1', true);
			wp_register_script('jeditable', AEC_PLUGIN_URL . 'js/jquery.jeditable.min.js', array('jquery'), '1.7.1', true);
			wp_register_script('mousewheel', AEC_PLUGIN_URL . 'js/jquery.mousewheel.min.js', array('jquery'), '3.0.4', true);
			wp_register_script('init_admin_calendar', AEC_PLUGIN_URL . 'js/jquery.init_admin_calendar.js', array('jquery', 'fullcalendar', 'simplemodal', 'growl', 'mousewheel', 'timePicker', 'jquery-ui-datepicker'), AEC_PLUGIN_VERSION, true);
			wp_register_script('init_show_calendar', AEC_PLUGIN_URL . 'js/jquery.init_show_calendar.js', array('jquery', 'mousewheel', 'simplemodal', 'fullcalendar'), AEC_PLUGIN_VERSION, true);
			wp_register_script('init_show_category', AEC_PLUGIN_URL . '/js/jquery.init_admin_category.js', array('jquery', 'jeditable', 'miniColors', 'growl'), AEC_PLUGIN_VERSION, true);

			// register styles
			wp_register_style('custom', AEC_PLUGIN_URL . 'css/custom.css', null, AEC_PLUGIN_VERSION);
			wp_register_style('custom_rtl', AEC_PLUGIN_URL . 'css/custom_rtl.css', null, AEC_PLUGIN_VERSION);
			wp_register_style('categories', AEC_PLUGIN_URL . 'css/cat_colors.css', null, AEC_PLUGIN_VERSION);
			wp_register_style('jq_ui_css', AEC_PLUGIN_URL . 'css/jquery-ui-1.8.13.custom.css', null, '1.8.13');
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
						state CHAR(3),
						zip VARCHAR(10),
						contact VARCHAR(50),
						contact_info VARCHAR(50),
						access TINYINT(1) UNSIGNED DEFAULT 0,
						rsvp TINYINT(1) UNSIGNED DEFAULT 0)
						CHARSET=utf8;';
				dbDelta($sql);
			}

			if ($wpdb->get_var('SHOW TABLES LIKE "' . $wpdb->prefix . AEC_CATEGORY_TABLE . '"') != $wpdb->prefix . AEC_CATEGORY_TABLE){
				$sql = 'CREATE TABLE ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' (
							id TINYINT(4) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
							category VARCHAR(25) NOT NULL,
							bgcolor CHAR(6) NOT NULL,
							fgcolor CHAR(6) NOT NULL
						) CHARSET=utf8;
						## DEFAULT CATEGORIES
						INSERT INTO ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' (id, category, bgcolor, fgcolor)
						VALUES 	(NULL, "Event", "517ed6", "FFFFFF"),
								(NULL, "Deadline", "e3686c", "FFFFFF"),
								(NULL, "Volunteer", "8fc9b0", "FFFFFF");';
				dbDelta($sql);
			}

			// add new role
			add_role('calendar_contributor', 'Calendar Contributor', array(
				'read' 				=> 1,
				'aec_add_events' 	=> 1
			));

			// add calendar capabilities to administrator
			$role = get_role('administrator');
			$role->add_cap('aec_add_events');
			$role->add_cap('aec_manage_events');
			$role->add_cap('aec_manage_calendar');
		}

		// settings initialization and patches
		function version_patches(){
			$plugin_updated = false;
			$options 		= get_option('aec_options');

			// initial and manual option initialization
			if (!is_array($options) || !isset($options['reset']) || $options['reset'] == '1') {
				update_option('aec_options', $this->plugin_defaults);
			}

			// set version for new plugin installations
			$installed = get_option('aec_version');
			if ($installed === false) {
				update_option('aec_version', AEC_PLUGIN_VERSION);
				$plugin_updated = true;
			}

			// patches
			// < 0.9.6
			if (version_compare(get_option('aec_version'), '0.9.6', '<')) {

				// if not present, add title as required option field
				$options 	= get_option('aec_options');
				if (!isset($options['title'])) {
					$options['title'] = 2;
					update_option('aec_options', $options);
				}

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

			// < 0.9.8.1
			if (version_compare(get_option('aec_version'), '0.9.8.1', '<')) {
				// add new options
				$options 		= $this->insert_option('show_weekends', 1);
				$options 		= $this->insert_option('show_map_link', 1);
				$plugin_updated = true;
			}

			// < 0.9.8.5
			if (version_compare(get_option('aec_version'), '0.9.8.5', '<')) {

				// update tables to UTF8 and modify category table
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				global $wpdb;
				$sqla = 'ALTER TABLE ' . $wpdb->prefix . AEC_EVENT_TABLE . ' CONVERT TO CHARACTER SET utf8;';
				$wpdb->query($sqla);
				$sqlb = 'ALTER TABLE ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' CONVERT TO CHARACTER SET utf8;';
				$wpdb->query($sqlb);
				$sqlc = 'ALTER TABLE ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' '
						. 'modify category VARCHAR(100) NOT NULL;';
				$wpdb->query($sqlc);

				//remove sidebar option
				$this->decommission_options(array('sidebar'));

				// remove retired administrator capability
				$role = get_role('administrator');
				$role->remove_cap('aec_run_reports');

				// remove retired role
				remove_role('blog_calendar_contributor');

				// remove outdated widget option
				delete_option('widget_upcoming_events');
				delete_option('widget_contributor_list');
				$plugin_updated = true;
			}

			// < 0.9.9.1
			if (version_compare(get_option('aec_version'), '0.9.9.1', '<')) {
				// update table to support Aussi state length
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				global $wpdb;
				$sql = 'ALTER TABLE ' . $wpdb->prefix . AEC_EVENT_TABLE
					 . ' MODIFY state CHAR(3);';
				$wpdb->query($sql);

				// add new options
				$options 		= $this->insert_option('filter_label', '');
				$options		= $this->insert_option('make_links', '0');
				$options		= $this->insert_option('popup_links', '1');
				$options		= $this->insert_option('addy_format', '0');
				$plugin_updated = true;
			}

			if ($plugin_updated) {
				// on patch completion update plugin version
				update_option('aec_version', AEC_PLUGIN_VERSION);

				// re-creates cat_colors.css file on update
				$this->generate_css();

				// add sample event once plugin has gone through all update routines
				$_POST['event']['user_id'] = 0;	// system id
				$_POST['event']['title'] = 'Ajax Event Calendar [v' . AEC_PLUGIN_VERSION . '] Installed!';
				$_POST['event']['start_date'] = date('Y-m-d');
				$_POST['event']['start_time'] = '00:00:00';
				$_POST['event']['end_date'] = date('Y-m-d');
				$_POST['event']['end_time'] = '00:00:00';
				$_POST['event']['allDay'] = 1;
				$_POST['event']['category_id'] = 1;
				$_POST['event']['description'] = "Now that the calendar is installed, here are some optional next steps:
- Create one (or more) calendar views with the [calendar] shortcode (for options see plugin homepage)
- Add, delete or modify event category labels and colors
- Specify which event form fields to hide, display and require
- Assign the Calendar Contributor role to users and allow them to add events
- Display AEC widgets (Upcoming Events and Calendar Contributors) in the sidebar

Experiencing problems? Read the FAQ (http://wordpress.org/extend/plugins/ajax-event-calendar/faq).
Can't find the solution? Try the forum (http://wordpress.org/tags/ajax-event-calendar?forum_id=10) and post your questions there.";
				$_POST['event']['link'] = AEC_PLUGIN_HOMEPAGE;
				$_POST['event']['venue'] = 'Fake Address';
				$_POST['event']['address'] = '201 East Randolph Street';
				$_POST['event']['city'] = 'Chicago';
				$_POST['event']['state'] = 'IL';
				$_POST['event']['zip'] = '60601-6530';
				$_POST['event']['contact'] = 'Eran Miller';
				$_POST['event']['contact_info'] = 'plugins at eranmiller dot com';
				$_POST['event']['access'] = 1;
				$_POST['event']['rsvp'] = 0;

				// removes previously created release events and creates a new one
				$_POST['user_id'] = $_POST['event']['user_id'];
				$this->delete_events_by_user();
				$this->add_event();
			}
		}

	    function localize_plugin($page){
			load_plugin_textdomain( AEC_PLUGIN_NAME, false, AEC_PLUGIN_NAME . '/locale/' );
			$timezone = get_option('timezone_string');
			if ($timezone) {
				date_default_timezone_set($timezone);
			} else {
				// TODO: look into converting gmt_offset into timezone_string
				date_default_timezone_set('UTC');
			}
		}

		// localized javascript variables
		function localized_variables(){
			$options = get_option('aec_options');

			// initialize required form fields
			foreach ($options as $option => $value) {
				if ($value == 2) $this->add_required_field($option);
			}

			$isEuroDate	= $this->parse_date_format(AEC_WP_DATE_FORMAT);
			$is24HrTime	= $this->parse_time_format(AEC_WP_TIME_FORMAT);

			return array(
				'is_rtl'					=> is_rtl(),
				'locale'					=> AEC_LOCALE,
				'start_of_week' 			=> get_option('start_of_week'),
				'datepicker_format' 		=> ($isEuroDate) ? 'dd-mm-yy' : 'mm/dd/yy',		// jquery datepicker format
				'is24HrTime'				=> $is24HrTime,
				'show_weekends'				=> $options['show_weekends'],
				'agenda_time_format' 		=> ($is24HrTime) ? 'H:mm{ - H:mm}' : 'h:mmt{ - h:mmt}',
				'other_time_format' 		=> ($is24HrTime) ? 'H:mm' : 'h:mmt',
				'axis_time_format' 			=> ($is24HrTime) ? 'HH:mm' : 'h:mmt',
				'limit' 					=> $options['limit'],
				'today'						=> __('Today', AEC_PLUGIN_NAME),
				'all_day'					=> __('All Day', AEC_PLUGIN_NAME),
				'months'					=> __('Months', AEC_PLUGIN_NAME),
				'month'						=> __('Month', AEC_PLUGIN_NAME),
				'weeks'						=> __('Weeks', AEC_PLUGIN_NAME),
				'week'						=> __('Week', AEC_PLUGIN_NAME),
				'days'						=> __('Days', AEC_PLUGIN_NAME),
				'day'						=> __('Day', AEC_PLUGIN_NAME),
				'hours'						=> __('Hours', AEC_PLUGIN_NAME),
				'hour'						=> __('Hour', AEC_PLUGIN_NAME),
				'minutes'					=> __('Minutes', AEC_PLUGIN_NAME),
				'minute'					=> __('Minute', AEC_PLUGIN_NAME),
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
				'loading'					=> __('Loading Events...', AEC_PLUGIN_NAME),
				'success'					=> __('Success!', AEC_PLUGIN_NAME),
				'whoops'					=> __('Whoops!', AEC_PLUGIN_NAME)
			);
		}

		function render_admin_menu(){
			if (function_exists('add_options_page')) {

				// define help text
				$help = '<h3>' . __('Ajax Event Calendar', AEC_PLUGIN_NAME);
				$help .= ' <small>[v' . AEC_PLUGIN_VERSION . ']</small></h3>';
				$help .= __('Have questions about this plugin?', AEC_PLUGIN_NAME);
				$help .= " <a href='" . AEC_PLUGIN_HOMEPAGE . "faq/' target='_blank'>";
				$help .= __('Check out the FAQ', AEC_PLUGIN_NAME);
				$help .= "</a>";
				$help .= '<br>' . __('Submit your questions and comments about this plugin', AEC_PLUGIN_NAME);
				$help .= " <a href='http://wordpress.org/tags/ajax-event-calendar?forum_id=10' target='_blank'>";
				$help .= __('in the forum', AEC_PLUGIN_NAME);
				$help .= "</a>";
				$help .= '<br>' . __('You are welcome to request help from others in your native language, but I am only able to assist in English.', AEC_PLUGIN_NAME);
				$help .= '<br><br>' . __('If you use this plugin', AEC_PLUGIN_NAME);
				$help .= " <a href='" . AEC_PLUGIN_HOMEPAGE . "' target='_blank'>";
				$help .= __('please rate and vote on compatibility', AEC_PLUGIN_NAME);
				$help .= "</a>.";
				$help .= '<br>' . __('Consider making a donation to support continued development of this plugin.', AEC_PLUGIN_NAME);
				$help .= '<span class="round5 fr">' . ' <a href="http://eranmiller.com/plugins/donate/" target="_blank">';
				$help .= __('DONATE', AEC_PLUGIN_NAME);
				$help .= "</a></span>";
				$help .= '<br><br>' . __('Thank you,', AEC_PLUGIN_NAME);
				$help .= ' <a href="http://eranmiller.com" target="_blank">Eran Miller</a>';

				// main menu page: calendar
				$page = add_menu_page('Ajax Event Calendar',  __('Calendar', AEC_PLUGIN_NAME), 'aec_add_events', AEC_PLUGIN_FILE, array($this, 'render_admin_calendar'), AEC_PLUGIN_URL . 'css/images/calendar.png', 30);

				// calendar admin specific scripts and styles
				add_action("admin_print_scripts-$page", array($this, 'admin_calendar_scripts'));
				add_action("admin_print_styles-$page", array($this, 'calendar_styles'));
				add_contextual_help($page, $help);

				if (current_user_can('aec_manage_calendar')) {
					// sub menu page: category management
					$sub_category = add_submenu_page(AEC_PLUGIN_FILE, 'Categories', __('Categories', AEC_PLUGIN_NAME), 'aec_manage_calendar', 'event_categories', array($this, 'render_admin_category'));
					add_contextual_help($sub_category, $help);

					// category admin specific scripts and styles
					add_action("admin_print_scripts-$sub_category", array($this, 'admin_category_scripts'));
					add_action("admin_print_styles-$sub_category", array($this, 'admin_category_styles'));

					// sub menu page: activity report
					$sub_report = add_submenu_page(AEC_PLUGIN_FILE, 'Activity Report', __('Activity Report', AEC_PLUGIN_NAME), 'aec_manage_calendar', 'activity_report', array($this, 'render_activity_report'));
					add_contextual_help($sub_report, $help);

					// sub settings page: calendar options
					$sub_options = add_options_page('Calendar', __('Ajax Event Calendar', AEC_PLUGIN_NAME), 'aec_manage_calendar', __FILE__, array($this, 'render_calendar_options'));
					add_contextual_help($sub_options, $help);
				}
			}
		}

		function render_frontend_calendar($atts){

			// shortcode defaults
			extract(shortcode_atts(array(
				'categories'	=> false,
				'excluded'		=> false,
				'filter'		=> 'all',
				'month'			=> date('m')-1,
				'year'			=> date('Y'),
				'view' 			=> 'month',
				'views' 		=> 'month,agendaWeek',
				'nav'			=> 'prev,next today',
				'scroll'		=> false
			), $atts));

			// shortcode input validation
			if ($excluded != false) $excluded = true;
			if ($filter != 'all') $filter = 'cat' . intval($filter);
			if ($month != date('m')-1) $month = intval($month)-1;
			if ($year != date('Y')) $year = intval($year);
			if ($view != 'month') $view = 'agendaWeek';
			if ($views != "month,agendaWeek") $views = '';
			if ($nav != 'prev,next today') $nav = '';
			if ($scroll != false) $scroll = true;

			// pass shortcode parameters to javascript
			$out  = "<script type='text/javascript'>\n";
			$out .= "var shortcode = {\n";
			$out .= "categories: '{$categories}',\n";
			$out .= "excluded: '{$excluded}',\n";
			$out .= "filter: '{$filter}',\n";
			$out .= "view: '{$view}',\n";
			$out .= "month: '{$month}',\n";
			$out .= "year: '{$year}',\n";
			$out .= "views: '{$views}',\n";
			$out .= "nav: '{$nav}',\n";
			$out .= "scroll: '{$scroll}'\n";
			$out .= "};\n";
			$out .= "</script>\n";

			$out .= '<div id="aec-container">';
			$out .='<div id="aec-header">';
			$options = get_option('aec_options');
			if ($options['menu']) {
				$out .= '<div id="aec-menu">';
				$out .= '<a href="' . admin_url() . 'admin.php?page=ajax-event-calendar.php">' . __('Add Events', AEC_PLUGIN_NAME) . '</a>';
				$out .= '</div>';
			}

			$out .= $this->render_category_filter($options, $categories, $excluded);
			$out .= '</div>';
			$out .= '<div id="aec-calendar"></div>' . "\n";
			$out .= '<a href="http://eranmiller.com/" id="aec-credit">' . AEC_PLUGIN_NAME . ' v' . AEC_PLUGIN_VERSION . ' ' . __('Created By', AEC_PLUGIN_NAME) . ' Eran Miller</a>';
			$out .= '</div>' . "\n";
			return $out;
		}

		function render_admin_calendar(){
			if (!current_user_can('aec_add_events'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));

			$options = get_option('aec_options');
			$out = '<div class="wrap">' . "\n";
			$out .= $this->render_category_filter($options);
			$out .= '<h2>' . __('Calendar', AEC_PLUGIN_NAME) . '</h2>' . "\n";
			$out .= '<div id="aec-calendar"></div>' . "\n";
			$out .= '</div>' . "\n";
			echo $out;
		}

		function render_category_filter($options, $categories=false, $excluded=false){
			$out = '<ul id="aec-filter">';
			$categories = $this->query_categories($categories, $excluded);
			if (sizeof($categories) > 1) {
				$out .= "<li>{$options['filter_label']}</li>\n";
				$out .= '<li class="active"><a class="round5 all">' . __('All', AEC_PLUGIN_NAME) . '</a></li>' . "\n";
				foreach ($categories as $category) {
					 $out .= '<li><a class="round5 cat' . $category->id . '">' . $this->render_i18n_data($category->category) . '</a></li>' . "\n";
				}
			}
			$out .= '</ul>';
			return $out;
		}

		function render_frontend_modal(){
			require_once AEC_PLUGIN_PATH . 'inc/show-event.php';
		}

		function render_admin_modal(){
			if (!current_user_can('aec_add_events'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			require_once AEC_PLUGIN_PATH . 'inc/admin-event.php';
			exit();
		}

		function render_admin_category(){
			if (!current_user_can('aec_manage_calendar'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));

			$categories = $this->query_categories();
			$out  = "<div class='wrap'>\n";
			$out .= '<h2>' . __('Categories', AEC_PLUGIN_NAME) . "</h2>\n";
			$out .= '<h5>' . __('Add a new, or edit/delete an existing calendar category.  To change the category tile color, click the color swatch or edit the field containing the hex value, then click Update.  The foreground color (black or white) is automatically assigned for optimal readbility based on the selected background color.', AEC_PLUGIN_NAME) . "</h5>\n";
			$out .= "<form id='aec-category-form'>\n";
			$out .= "<input type='hidden' id='fgcolor' name='fgcolor' class='fg ltr' value='#FFFFFF' />";
			$out .= "<p><input type='text' id='category' name='category' value='' /> ";
			$out .= "<input class='bg colors ltr' type='text' id='bgcolor' name='bgcolor' value='#005294' size='7' maxlength='7' autocomplete='off'> ";
			$out .= "<button class='add button-primary'>" . __('Add', AEC_PLUGIN_NAME) . "</button></p>\n";
			$out .= "</form>\n";
			$out .= "<form id='aec-category-list'>\n";
			foreach ($categories as $category) {
				$delete = ($category->id > 1) ?
					"<button class='button-secondary delete'>" . __('Delete', AEC_PLUGIN_NAME) . "</button>\n" :
					" <em>" . __('This category is required and can only be edited.', AEC_PLUGIN_NAME) . "</em>\n";
				$out .= "<p id='id_{$category->id}'>\n";
				$out .= "<input type='hidden' name='fgcolor' value='#{$category->fgcolor}' class='fg ltr' />\n";
				$out .= "<input type='text' name='id' value='{$category->id}' size='2' readonly='readonly' />\n";
				$out .= "<input type='text' name='category' value='" . $this->render_i18n_data($category->category) . "' class='edit' />\n";
				$out .= "<input type='text' name='bgcolor' size='7' maxlength='7' autocomplete='off' value='#{$category->bgcolor}' class='bg colors ltr' />\n";
				$out .= "<button id='category_update' class='update button-secondary'>" . __('Update', AEC_PLUGIN_NAME) . "</button>\n";
				$out .= $delete;
				$out .= "</p>\n";
			}
			$out .= "</form>\n";
			$out .= "</div>\n";
			echo $out;
		}

		function render_activity_report(){
			if (!current_user_can('aec_manage_calendar'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));

			$out  = "<div class='wrap'>\n";
			$out .= "<h2>" . __('Activity Report', AEC_PLUGIN_NAME) . "</h2>\n";
			$out .= "<h5>" . __('Number of events scheduled for the current month, by type:', AEC_PLUGIN_NAME) . "</h5>\n";
			$rows = $this->query_monthly_activity();
			if ( count( $rows ) ) {
				foreach ( $rows as $row ) {
					$out .= "<p><strong>{$row->cnt}</strong> <em>" . $this->render_i18n_data($row->category) . "</em> ";
					$out .= _n('Event', 'Events', $row->cnt, AEC_PLUGIN_NAME);
					$out .= "</p>\n";
				}
			} else {
				$out .= "<p><em>" . __('No events this month.', AEC_PLUGIN_NAME) . "</em></p>\n";
			}
			$out .= "</div>\n";
			echo $out;
		}

		function render_calendar_options(){
			if (!current_user_can('aec_manage_calendar'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			require_once AEC_PLUGIN_PATH . 'inc/admin-options.php';
		}

		function render_event($input, $user_id, $render=false){
			// users that are not logged-in see all events
			if ($user_id == -1) {
				$editable = false;
				$disabled = '';
			} else {
				// users with aec_manage_events capability can edit all events
				// users with aec_add_events capability can edit events only they create
				if ($input->user_id == $user_id || $user_id == false) {
					$editable = true;
					$disabled = '';
				} else {
					$editable = false;
					$disabled = ' fc-event-disabled';
				}
			}
			$output = array(
				'id'	 	=> $input->id,
				'title'  	=> $input->title,
				'start'		=> $input->start,
				'end'		=> $input->end,
				'allDay' 	=> ($input->allDay) ? true : false,
				'className'	=> "cat{$input->category_id}{$disabled}",
				'editable'	=> $editable
			);
			//$output = $this->cleanse_output($output);
			if ($render) $this->render_json($output);
			return $output;
		}

		// renders events as json, employing shortcode options
		function render_events(){
			$categories	 	= (isset($_POST['categories'])) ? $this->cleanse_shortcode_input($_POST['categories']) : false;
			$excluded		= ($categories && isset($_POST['excluded'])) ? $_POST['excluded'] : false;
			$start			= date('Y-m-d', $_POST['start']);
			$end			= date('Y-m-d', $_POST['end']);
			$readonly		= (isset($_POST['readonly'])) ? true : false;
			$events			= $this->query_events($start, $end, $categories, $excluded);
			if ($events) {
				$output 	= array();
				foreach($events as $event){
					array_push($output, $this->render_event($event, $this->return_user_id($readonly), false));
				}
				$this->render_json($output);
			}
		}
		// outputs added/updated category as json
		function render_category($input){
			$output = array(
				'id'	 	=> $input->id,
				'category'  => $input->category,
				'bgcolor'	=> $input->bgcolor,
				'fgcolor'	=> $input->fgcolor
			);
			$this->render_json($output);
		}

		function render_json($output){
			header("Content-Type: application/json");
			// $this->log('raw data');
			// $this->log($output);
			echo json_encode($this->cleanse_output($output));
			exit;
		}

		/* TODO: ical
		function render_ical_export($output){
			$blog_name = get_bloginfo('name');
			$blog_url = get_bloginfo('home');
			$timezone = get_option('timezone_string');

			header('Content-type: text/calendar');
			header('Content-Disposition: attachment; filename="event-calendar.ics"');
			$content = <<<CONTENT
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//$blog_name//NONSGML v1.0//EN
X-WR-CALNAME:calendar of {$blog_name}
X-WR-TIMEZONE:{$timezone}
X-ORIGINAL-URL:{$blog_url}
X-WR-CALDESC:Events from {$blog_name}
CALSCALE:GREGORIAN
METHOD:PUBLISH
{$output}END:VCALENDAR
CONTENT;
			echo $content;
			exit;
		}
*/

		function render_i18n_data($data){
			return htmlentities(stripslashes($data), ENT_COMPAT, 'UTF-8');
		}

		// database queries
		function frontend_calendar_variables(){
			return array_merge($this->localized_variables(),
				array(
					'ajaxurl'  		=> admin_url('admin-ajax.php'),		// required for non-admin ajax pages
					'editable'		=> false
				)
			);
		}

		function frontend_calendar_scripts(){
			if (!is_admin()) {
				wp_enqueue_script('jquery');
				wp_enqueue_script('fullcalendar');
				wp_enqueue_script('simplemodal');
				wp_enqueue_script('mousewheel');
				wp_enqueue_script('growl');
				wp_enqueue_script('init_show_calendar');
				wp_localize_script('init_show_calendar', 'custom', $this->frontend_calendar_variables());
			}
		}

		function calendar_styles(){
			wp_enqueue_style('jq_ui_css');
			wp_enqueue_style('categories');
			wp_enqueue_style('custom');
			if (is_rtl()) {
				wp_enqueue_style('custom_rtl');
			}
		}

		function admin_calendar_variables(){
			$is_admin = (current_user_can('aec_manage_events')) ? 1 : 0;
			return array_merge($this->localized_variables(),
				array(
					'admin' 					=> $is_admin,
					'required_fields'			=> join(",", $this->get_required_fields()),
					'editable'					=> true,
					'error_no_rights'			=> __('You cannot edit events created by other users.', AEC_PLUGIN_NAME),
					'error_past_create'			=> __('You cannot create events in the past.', AEC_PLUGIN_NAME),
					'error_future_create'		=> __('You cannot create events more than a year in advance.', AEC_PLUGIN_NAME),
					'error_past_resize'			=> __('You cannot resize expired events.', AEC_PLUGIN_NAME),
					'error_past_move'			=> __('You cannot move events into the past.', AEC_PLUGIN_NAME),
					'error_past_edit'			=> __('You cannot edit expired events.', AEC_PLUGIN_NAME),
					'error_invalid_duration'	=> __('Invalid duration, please adjust your time inputs.', AEC_PLUGIN_NAME)
				)
			);
		}

		function admin_calendar_scripts(){
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_script('jquery-ui-droppable');
			wp_enqueue_script('jquery-ui-selectable');
			wp_enqueue_script('jquery-ui-resizable');
			wp_enqueue_script('jquery-ui-datepicker');
			if (AEC_LOCALE != 'en') wp_enqueue_script('datepicker-locale');	// if not in English, load localization
			wp_enqueue_script('timePicker');
			wp_enqueue_script('growl');
			wp_enqueue_script('fullcalendar');
			wp_enqueue_script('simplemodal');
			wp_enqueue_script('mousewheel');
			wp_enqueue_script('init_admin_calendar');
			wp_localize_script('init_admin_calendar', 'custom', $this->admin_calendar_variables());
		}

		function admin_category_variables(){
			return array_merge($this->localized_variables(),
				array(
					'error_blank_category'		=> __('Category type cannot be a blank value.', AEC_PLUGIN_NAME),
					'confirm_category_delete'	=> __('Are you sure you want to delete this category type?', AEC_PLUGIN_NAME),
					'confirm_category_reassign'	=> __('Several events are associated with this category. Click OK to reassign these events to the default category.', AEC_PLUGIN_NAME),
					'events_reassigned'			=> __('Events have been reassigned to the default category.', AEC_PLUGIN_NAME)
				)
			);
		}

		function admin_category_scripts(){
			wp_enqueue_script('jquery');
			wp_enqueue_script('growl');
			wp_enqueue_script('miniColors');
			wp_enqueue_script('jeditable');
			wp_enqueue_script('init_show_category');
			wp_localize_script('init_show_category', 'custom', $this->admin_category_variables());
		}

		function admin_category_styles(){
			wp_enqueue_style('categories');
			wp_enqueue_style('custom');
			if (is_rtl()) {
				wp_enqueue_style('custom_rtl');
			}
		}

		function admin_options_initialize(){
			register_setting('aec_plugin_options', 'aec_options', array($this, 'admin_options_validate'));
		}

		function admin_options_validate($input){
			// validation placeholder
			return $input;
		}

		function confirm_delete_category(){
			if (!isset($_POST['id'])) return;
			if ($this->query_events_by_category($_POST['id'])){
				$this->render_json('false');
			}
			$this->delete_category($_POST['id']);
		}

		function query_monthly_activity(){
			global $wpdb;
			$result = $wpdb->get_results('SELECT COUNT(a.category_id) AS cnt, b.category FROM ' .
										$wpdb->prefix . AEC_EVENT_TABLE . ' AS a ' .
										'INNER JOIN ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' AS b ' .
										'ON a.category_id = b.id ' .
										'WHERE MONTH(start) = MONTH(NOW()) ' .
										'GROUP BY category_id ' .
										'ORDER BY cnt DESC;'
									);
			return $this->return_result($result);
		}

		function query_event($id){
			global $wpdb;
			$result = $wpdb->get_row($wpdb->prepare('SELECT *
									FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
									WHERE id = %d ORDER BY start;', $id));
			return $this->return_result($result);
		}

		// output for fullcalendar array
		function query_events($start, $end, $category_id, $excluded){
			global $wpdb;
			$excluded = ($excluded) ? 'NOT IN' : 'IN';
			$andcategory = ($category_id) ? " AND category_id {$excluded}({$category_id})" : '';
			$result = $wpdb->get_results('SELECT
										id,
										user_id,
										title,
										start,
										end,
										allDay,
										category_id
										FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
										WHERE ((start >= "' . $start . '"
										AND start < "' . $end . '")
										OR (end >= "' . $start . '"
										AND end < "' . $end . '")
										OR (start < "' . $start . '"
										AND end > "' . $end . '"))' .
										$andcategory .
										' ORDER BY start;'
									);
			return $this->return_result($result);
		}

		function query_events_by_user($user_id){
			global $wpdb;
			$result = $wpdb->get_var($wpdb->prepare('SELECT count(id)
									 FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
									 WHERE user_id = %d;', $user_id));
			return $this->return_result($result);
		}

		function query_events_by_category(){
			if (!isset($_POST['id'])) return;
			global $wpdb;
			$result = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) as count
									FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
									WHERE category_id = %d;', $_POST['id']));
			return $this->return_result($result);
		}

		function query_categories($category_id=false, $excluded=false){
			global $wpdb;
			$excluded = ($excluded) ? 'NOT IN' : 'IN';
			$wherecategory = ($category_id) ? " WHERE id {$excluded}({$category_id})" : '';
			$result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . $wherecategory . ' ORDER BY id;');
			return $this->return_result($result);
		}

		function insert_event($input){
			global $wpdb;
			$result = $wpdb->insert($wpdb->prefix . AEC_EVENT_TABLE,
									array('user_id' 		=> $input->user_id,
										  'title'	 		=> $input->title,
										  'start'			=> $input->start,
										  'end'				=> $input->end,
										  'allDay'			=> $input->allDay,
										  'category_id'		=> $input->category_id,
										  'description'		=> $input->description,
										  'link'			=> $input->link,
										  'venue'			=> $input->venue,
										  'address'			=> $input->address,
										  'city'			=> $input->city,
										  'state'			=> $input->state,
										  'zip'				=> $input->zip,
										  'contact'			=> $input->contact,
										  'contact_info'	=> $input->contact_info,
										  'access'			=> $input->access,
										  'rsvp'			=> $input->rsvp
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
			if ($this->return_result($result)){
				if ($input->user_id){					// only render events not generated by the system (user id: 0)
					$input->id = $wpdb->insert_id;		// id of newly created row
					$this->render_event($input, $this->return_user_id(), true);
				}
			}
		}

		function add_event(){
			if (!isset($_POST['event'])) return;
			$input = $this->cleanse_event_input($_POST['event']);
			$this->insert_event($input);
		}

		/* TODO: copy event
		function copy_event(){
			$clone = $_POST['clone'];
			$input = $this->convert_object_to_array($this->query_event($clone['id']));
			$input['start'] = $clone['start'];
			$input['end'] = $clone['end'];
			$this->insert_event($input);
			return;
		}
		*/

		function add_category(){
			if (!isset($_POST['category_data'])) return;
			$input = $this->cleanse_category_input($_POST['category_data']);
			global $wpdb;
			$result = $wpdb->insert($wpdb->prefix . AEC_CATEGORY_TABLE,
									array('category'	=> $input->category,
										  'bgcolor'		=> $input->bgcolor,
										  'fgcolor' 	=> $input->fgcolor
										),
									array('%s',
										  '%s',
										  '%s'
										));
			if ($this->return_result($result)) {
				$input->id = $wpdb->insert_id;	// id of newly created row
				$this->generate_css();
				$this->render_category($input);
			}
		}

		function move_event(){
			if (!isset($_POST)) return;
			$input = $this->convert_array_to_object($this->parse_input($_POST));
			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_EVENT_TABLE,
									array('start'		=> $input->start,
										  'end'	 		=> $input->end,
										  'allDay'		=> $input->allDay
										),
									array('id' 		=> $input->id),
									array('%s',		// start
										  '%s',		// end
										  '%d'		// allDay
										),
									array ('%d')	// id
								);
			//$this->render_event($result, $this->return_user_id(), true);
			$this->return_result($result);
		}

		function update_event(){
			if (!isset($_POST['event'])) return;
			$input = $this->cleanse_event_input($_POST['event']);
			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_EVENT_TABLE,
									array('user_id' 		=> $input->user_id,
										  'title'	 		=> $input->title,
										  'start'			=> $input->start,
										  'end'				=> $input->end,
										  'allDay'			=> $input->allDay,
										  'category_id'		=> $input->category_id,
										  'description'		=> $input->description,
										  'link'			=> $input->link,
										  'venue'			=> $input->venue,
										  'address'			=> $input->address,
										  'city'			=> $input->city,
										  'state'			=> $input->state,
										  'zip'				=> $input->zip,
										  'contact'			=> $input->contact,
										  'contact_info'	=> $input->contact_info,
										  'access'			=> $input->access,
										  'rsvp'			=> $input->rsvp
										),
									array('id' 				=> $input->id),
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

			if ($this->return_result($result)) {
				$this->render_event($input, $this->return_user_id(), true);
			}
		}

		function update_category(){
			if (!isset($_POST['category_data'])) return;
			$input = $this->cleanse_category_input($_POST['category_data']);
			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_CATEGORY_TABLE,
									array('category'	=> $input->category,
										  'bgcolor'		=> $input->bgcolor,
										  'fgcolor'		=> $input->fgcolor
									),
									array('id' => $input->id),
									array('%s',
										  '%s',
										  '%s'
									),
									array ('%d') //id
								);
			if ($this->return_result($result)){
				$this->generate_css();
				$this->render_category($input);
			}
		}

		function reassign_category(){
			if (!isset($_POST['id'])) return;
			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_EVENT_TABLE,
						array('category_id' => 1),
						array('category_id' => $_POST['id']),
						array('%d'),
						array('%d'));
			if ($this->return_result($result))
				$this->delete_category($_POST['id']);
		}

		function delete_event(){
			if (!isset($_POST['id'])) return;
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE id = %d;', $_POST['id']));
			$this->render_json($this->return_result($result));
		}

		function delete_events_by_user(){
			if (!isset($_POST['user_id'])) return;
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE user_id = %d;', $_POST['user_id']));
			return $this->return_result($result);
		}

		// TODO: waiting for improved delete_user hook
		/*
		function reassign_events_to_user(){
			if (!isset($_POST['user_id']) || !isset($_POST['reassigned'])) return;
			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_EVENT_TABLE,
						array('user_id' => $_POST['reassigned']),
						array('user_id' => $_POST['user_id']),
						array('%d'),
						array('%d'));
			return $this->return_result($result);
		}
		*/

		function delete_category($id){
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' WHERE id = %d;', $id));
			if ($this->return_result($result)) {
				$this->generate_css();
				$this->render_json($result);
			}
		}

		// parse form input
		function parse_input($input){
			if (!is_array($input)){
				// convert serialized form into an array
				parse_str($input, $array);
				$input = $array;
			}
			// trim whitespace from input
			array_walk($input, create_function('&$val', '$val = trim($val);'));
			return $input;
		}

		function parse_date_format($format){
			// d | j	 1 | 01, day of the month
			// m | n	 3 | 03, month of the year
			// if date format begins with d or j assign Euro format, otherwise US format
			return (strpos($format, 'd') === 0 || strpos($format, 'j') === 0) ? true : false;
		}

		function parse_time_format($format){
			// H | G	 24-hour, with | without leading zeros
			// g | H	 24-hour, with | without leading zeros
			return (strpos($format, 'G') !== false || strpos($format, 'H') !== false) ? true : false;
		}

		// restricts jquery datepicker format (based on WP date format) to ensure accurate localization
		function get_wp_date_format(){
			return ($this->parse_date_format(AEC_WP_DATE_FORMAT)) ? 'd-m-Y' : 'm/d/Y';
		}

		// restricts jquery timepicker format (based on WP time format) to ensure accurate localization
		function get_wp_time_format(){
			return ($this->parse_time_format(AEC_WP_TIME_FORMAT)) ? 'H:i' : 'h:i A';
		}

		// split datetime fields
		function split_datetime($datetime){
			$out = array();
			array_push($out, $this->date_convert($datetime, AEC_DB_DATETIME_FORMAT, $this->get_wp_date_format()));
			array_push($out, $this->date_convert($datetime, AEC_DB_DATETIME_FORMAT, $this->get_wp_time_format()));
			return $out;
		}

		// merge date and time fields, and convert to database format
		function merge_date_time($date, $time){
			$datetime 	= "{$date} {$time}";
			$format 	= "{$this->get_wp_date_format()} {$this->get_wp_time_format()}";
			return $this->date_convert($datetime, $format, AEC_DB_DATETIME_FORMAT);
		}

		// removes slashes from strings and arrays
		function cleanse_output($output){
			if (is_array($output)) {
				array_walk_recursive($output, create_function('&$val', '$val = stripslashes($val);'));
			} else {
				$output = stripslashes($output);
			}
			// $this->log('cleansed');
			// $this->log($output);
			return $output;
		}
		function cleanse_event_input($input){
			$clean = $this->convert_array_to_object($this->parse_input($input));

			if ($clean->allDay) {
				$clean->start_time	= '00:00:00';
				$clean->end_time	= '00:00:00';
			}
			$clean->start		= $this->merge_date_time($clean->start_date, $clean->start_time);
			$clean->end			= $this->merge_date_time($clean->end_date, $clean->end_time);
			return $clean;
		}

		function cleanse_category_input($input){
			$clean = $this->convert_array_to_object($this->parse_input($input));
			$clean->bgcolor = str_replace('#', '', $clean->bgcolor);	// strip '#' for storage
			$clean->fgcolor = str_replace('#', '', $clean->fgcolor);
			return $clean;
		}

		// convert category string input into array, force integer values, return as string output
		function cleanse_shortcode_input($input){
			$input = explode(',', $input);
			array_walk($input, create_function('&$val', '$val = intval($val);'));
			return join(',', $input);
		}

		// set required fields on admin event detail form
		function add_required_field($field){
			array_push($this->required_fields, $field);
		}

		// get required fields on admin event detail form
		function get_required_fields(){
			if (count($this->required_fields))
				return $this->required_fields;
			return;
		}

		function convert_object_to_array($object){
			$array = array();
			foreach($object as $key => $value){
				$array[$key] = $value;
			}
			return $array;
		}

		function convert_array_to_object($array = array()) {
			if (!empty($array)) {
				$data = false;
				foreach ($array as $key => $val) {
					$data->{$key} = $val;
				}
				return $data;
			}
			return false;
		}
		function date_convert($date, $from, $to=false){
			// if date format is d/m/Y, modify token to 'd-m-Y' so strtotime parses date correctly
			if (strpos($from, 'd') == 0) $date = str_replace("/", "-", $date);
			if ($to){ return date_i18n($to, strtotime($date)); }
			return strtotime($date);
		}

		function return_result($result){
			if ($result === false){
				$this->log($wpdb->print_error());
				return false;
			}
			return $result;
		}

		function return_user_id($readonly = false){
			if ($readonly) return "-1";
			global $current_user;
			get_currentuserinfo();
			return (current_user_can('aec_manage_events')) ? false : $current_user->ID;
		}

		// dynamically creates cat_colors.css file
		function generate_css(){
			$categories = $this->query_categories();
			$out = '';
			foreach ($categories as $category){
				$out .= ".cat{$category->id}";
				$out .= ",.cat{$category->id} .fc-event-skin";
				$out .= ",.fc-agenda .cat{$category->id}";
				$out .= ",a.cat{$category->id}";
				$out .= ",a.cat{$category->id}:hover";
				$out .= ",a.cat{$category->id}:active";
				$out .= ",a.cat{$category->id}:visited";
				$out .= "{color:#{$category->fgcolor} !important;background-color:#";
				$out .= "{$category->bgcolor} !important;border-color:#{$category->bgcolor} !important}\n";
			}

			$cssFile = AEC_PLUGIN_PATH . "css/cat_colors.css";
			$fh = fopen($cssFile, 'w+') or die('cannot open file');
			fwrite($fh, $out);
			fclose($fh);
		}

/* TODO: ical
		function generate_ical_export(){
			// reuse existing queries
			//if ($export	= $this->query_ical_events()) {
				$this->log($export);
				foreach ($export as $event) {
					$start_time = date('Ymd\THis', $event->start);
					$end_time = date('Ymd\THis', $event->end);
					$summary = $event->title;
					$link = $event->link;
					$space = '      ';
					$content = ($event->description)? str_replace(',', '\,', str_replace('\\', '\\\\', str_replace("\n", "\n" . $space, strip_tags($event->description)))) : '';
					if ($link) $content = $content . "\n" . $space . "\n" . $space . $link;
						$export .= <<<EVENT
BEGIN:VEVENT
DTSTART:$start_time
DTEND:$end_time
SUMMARY:$summary
DESCRIPTION:$content
END:VEVENT
EVENT;
				}
			}
			$this->render_ical_export($export);
		}
*/

		// adds column field label to WordPress users page
		function add_events_column($columns){
			$columns['calendar_events'] = __('Events', AEC_PLUGIN_NAME);
			return $columns;
		}

		// adds column field value to WordPress users page
		function manage_events_column($empty='', $column_name, $user_id){
			if ($column_name == 'calendar_events')
				return $this->query_events_by_user($user_id);
		}

		// displays the "settings" link beside the plugin on the WordPress plugins page
		function settings_link($links, $file){
			if ($file == plugin_basename(__FILE__)){
				$settings = '<a href="' . get_admin_url() . 'options-general.php?page=' . AEC_PLUGIN_NAME . '/' . AEC_PLUGIN_FILE . '">' . __('Settings', AEC_PLUGIN_NAME) . '</a>';
				array_unshift($links, $settings);	// make the 'Settings' link appear first
			}
			return $links;
		}

		// changes the permissions for using the calendar settings page
		function set_option_page_capability($capability){
			return 'aec_manage_calendar';
		}

		// if not present, add options
		function insert_option($key, $value){
			$options = get_option('aec_options');
			if (!array_key_exists($key, $options)){
				$options[$key] = $value;
			}
			update_option('aec_options', $options);
		}

		function decommission_options($keys){
			$options = get_option('aec_options');
			foreach ($keys as $key) {
				if (array_key_exists($key, $options)){
					unset($options[$key]);
				}
			}
			update_option('aec_options', $options);
		}

		function log($message){
			if (is_array($message) || is_object($message)){
				error_log(print_r($message, true));
			} else {
				error_log($message);
			}
			return;
		}
	}
}

register_activation_hook(__FILE__, array('ajax_event_calendar', 'install'));

if (class_exists('ajax_event_calendar')){
	// widgets code
	require_once AEC_PLUGIN_PATH . 'inc/widget-contributors.php';
	require_once AEC_PLUGIN_PATH . 'inc/widget-upcoming.php';
	$aec = new ajax_event_calendar();

	// TODO: ical
	//if (isset($_GET['ical'])) $aec->generate_ical_export();
}
?>