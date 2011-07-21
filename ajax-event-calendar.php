<?php
/*
Plugin Name: Ajax Event Calendar
Plugin URI: http://wordpress.org/extend/plugins/ajax-event-calendar/
Description: A fully localized Google Calendar/OSX hybrid interface which enables users (registered with the necessary access) to add, edit and delete events in a community calendar viewable by all blog visitors.
Version: 0.9.8.5
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

define('AEC_PLUGIN_VERSION', '0.9.8.5');
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
define('AEC_WP_DATETIME_FORMAT', AEC_WP_DATE_FORMAT . ' ' . AEC_WP_TIME_FORMAT);
define('AEC_DB_DATETIME_FORMAT', 'Y-m-d H:i:s');
define('AEC_LOCALE', substr(get_locale(), 0, 2));	//  for javascript localization scripts

// logs WordPress errors to /wp-content/debug.log
define('AEC_DEBUG', true);	// do not modify this setting the plugin is functioning

if (!class_exists('ajax_event_calendar')){
	class ajax_event_calendar{

		private $required_fields = array();
		
		function __construct(){
			add_action('plugins_loaded', array($this, 'version_patches'));
		    add_action('init', array($this, 'localize_plugin'), 10, 1);
			add_action('admin_menu', array($this, 'render_admin_menu'));
			add_action('admin_init', array($this, 'admin_options_initialize'));
			// add_action('admin_notices', array($this, 'generate_notice'));  // admin notification placeholder
			add_action('wp_print_scripts', array($this, 'frontend_calendar_scripts'));
			add_action('wp_print_styles', array($this, 'frontend_calendar_styles'));
			add_action('delete_user', array($this, 'confirm_delete_user_events'));

			add_shortcode('calendar', array($this, 'render_frontend_calendar'));
			
			// wordpress overrides
			add_filter('manage_users_columns', array($this, 'add_events_column'));
			add_filter('manage_users_custom_column', array($this, 'manage_events_column'), 10, 3);
			add_filter('plugin_action_links', array($this, 'settings_link'), 10, 2);
			add_filter('option_page_capability_' . AEC_DOMAIN . 'plugin_options', array($this, 'set_option_page_capability'));

			// ajax hooks
			add_action('wp_ajax_nopriv_get_events', array($this, 'render_events'));
			add_action('wp_ajax_get_events', array($this, 'render_events'));
			add_action('wp_ajax_nopriv_get_event', array($this, 'render_frontend_modal'));
			add_action('wp_ajax_get_event', array($this, 'render_frontend_modal'));
			add_action('wp_ajax_admin_event', array($this, 'render_admin_modal'));
			add_action('wp_ajax_add_event', array($this, 'add_event'));
			add_action('wp_ajax_update_event', array($this, 'update_event'));
			add_action('wp_ajax_delete_event', array($this, 'delete_event'));
			add_action('wp_ajax_move_event', array($this, 'move_event'));
			add_action('wp_ajax_add_category', array($this, 'add_category'));
			add_action('wp_ajax_update_category', array($this, 'update_category'));
			add_action('wp_ajax_delete_category', array($this, 'delete_category'));
			add_action('wp_ajax_reassign_category', array($this, 'reassign_category'));

			// register scripts
			wp_register_script('fullcalendar', AEC_PLUGIN_URL . 'js/jquery.fullcalendar.min.js', array('jquery'), '1.5.1', true);
			wp_register_script('simplemodal', AEC_PLUGIN_URL . 'js/jquery.simplemodal.1.4.1.min.js', array('jquery'), '1.4.1', true);
			wp_register_script('jquery-ui-datepicker', AEC_PLUGIN_URL . 'js/jquery.ui.datepicker.min.js', array('jquery-ui-core'), '1.8.5', true);
			wp_register_script('datepicker-locale', AEC_PLUGIN_URL . 'js/i18n/jquery.ui.datepicker-' . substr(get_locale(), 0, 2) . '.js', array('jquery-ui-datepicker'), '1.8.5', true);
			wp_register_script('timePicker', AEC_PLUGIN_URL . 'js/jquery.timePicker.min.js', array('jquery'), '5195', true);
			wp_register_script('growl', AEC_PLUGIN_URL . 'js/jquery.jgrowl.min.js', array('jquery'), '1.2.5', true);
			wp_register_script('miniColors', AEC_PLUGIN_URL . 'js/jquery.miniColors.min.js', array('jquery'), '0.1', true);
			wp_register_script('jeditable', AEC_PLUGIN_URL . 'js/jquery.jeditable.min.js', array('jquery'), '1.7.1', true);
			wp_register_script('init_admin_calendar', AEC_PLUGIN_URL . 'js/jquery.init_admin_calendar.js', array('jquery', 'fullcalendar', 'simplemodal', 'growl', 'timePicker', 'jquery-ui-datepicker'), '0.9.8.5', true);
			wp_register_script('init_show_calendar', AEC_PLUGIN_URL . 'js/jquery.init_show_calendar.js', array('jquery', 'simplemodal', 'fullcalendar'), '0.9.8.5', true);
			wp_register_script('init_show_category', AEC_PLUGIN_URL . '/js/jquery.init_admin_category.js', array('jquery', 'jeditable', 'miniColors', 'growl'), '0.9.8.5', true);

			// register styles
			wp_register_style('custom', AEC_PLUGIN_URL . 'css/custom.css', null, '0.9.8.5');
			wp_register_style('categories', AEC_PLUGIN_URL . 'css/cat_colors.css', null, '0.9.8.5');
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
						state CHAR(2),
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

			// initial and manual option initialization
			if (!is_array($options) || !isset($options['reset']) || $options['reset'] == '1') {
				$plugin_default_options = array(
					'show_weekends'		=> '1',
					'show_map_link'		=> '1',
					'menu' 				=> '1',
					'limit' 			=> '0',
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

			// set version for new plugin installations
			$installed = get_option(AEC_DOMAIN . 'version');
			if ($installed === false) {
				update_option(AEC_DOMAIN . 'version', AEC_PLUGIN_VERSION);
				$plugin_updated = true;
			}

			// patches
			// < 0.9.6
			if (version_compare(get_option(AEC_DOMAIN . 'version'), '0.9.6', '<')) {

				// if not present, add title as required option field
				$options 	= get_option(AEC_DOMAIN . 'options');
				if (!isset($options['title'])) {
					$options['title'] = 2;
					update_option(AEC_DOMAIN . 'options', $options);
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
			if (version_compare(get_option(AEC_DOMAIN . 'version'), '0.9.8.1', '<')) {

				// if not present, add options
				$options 	= get_option(AEC_DOMAIN . 'options');
				if (!isset($options['show_weekends']) || !isset($options['show_map_link'])) {
					$options['show_weekends'] = 1;
					$options['show_map_link'] = 1;
					update_option(AEC_DOMAIN . 'options', $options);
				}
				$plugin_updated = true;
			}

			// < 0.9.8.5
			if (version_compare(get_option(AEC_DOMAIN . 'version'), '0.9.8.5', '<')) {

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
				$role 		= get_role('administrator');
				$role->remove_cap(AEC_DOMAIN . 'run_reports');

				// remove retired role
				remove_role('blog_calendar_contributor');

				// remove outdated widget option
				delete_option('widget_upcoming_events');
				delete_option('widget_contributor_list');

				$plugin_updated = true;
			}

			if ($plugin_updated) {
				// on patch completion update plugin version
				update_option(AEC_DOMAIN . 'version', AEC_PLUGIN_VERSION);

				$this->generate_css();

				// add sample event once plugin has gone through all update routines
				$_POST['event']['user_id'] = 0;	// system id
				$_POST['event']['title'] = 'Ajax Event Calendar [v' . AEC_PLUGIN_VERSION . '] Installed!';
				$_POST['event']['start_date'] = date(AEC_WP_DATE_FORMAT);
				$_POST['event']['start_time'] = '00:00:00';
				$_POST['event']['end_date'] = date(AEC_WP_DATE_FORMAT);
				$_POST['event']['end_time'] = '00:00:00';
				$_POST['event']['allDay'] = 1;
				$_POST['event']['category_id'] = 1;
				$_POST['event']['description'] = "Now that the calendar is installed...these are a few next steps:<ul><li>Add the front-end calendar page (illustrated in the Screenshots section via the Event Link)</li><li>Change the event categories</li><li>Add the calendar widgets to your sidebar</li><li>Modify the calendar options (via the WP Settings: General and Ajax Event Calendar menus)</li><li>Authorize calendar contributors (via the WP Users menu)</li></ul><br>Can't find what you're looking for in the FAQ? <a href='http://wordpress.org/tags/ajax-event-calendar?forum_id=10' target='_blank'>Check out the forum</a> and post your questions there.<br><br>If you use and enjoy this plugin, please remember to vote via the Event Link.<br>Thanks!<br>Eran";
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
				$this->add_event();
			}
		}

		function generate_notice(){
			// Shows as an error message. You could add a link to the right page if you wanted.
			$this->render_message("You need to upgrade your database as soon as possible...", true);
			// admin only
			if (current_user_can('manage_options')) {
				// $this->render_message("Hello admins!");
			}
		}

	    function localize_plugin($page){
			load_plugin_textdomain( AEC_PLUGIN_NAME, false, AEC_PLUGIN_NAME . '/locale/' );
		}
		
		// localized javascript variables
		function localized_variables(){
			$options = get_option(AEC_DOMAIN . 'options');

			// initialize required form fields
			foreach ($options as $option => $value) {
				if ($value == 2) $this->add_required_field($option);
			}

			$isEuroDate	= $this->parse_date_format(AEC_WP_DATE_FORMAT);
			$is24HrTime	= $this->parse_time_format(AEC_WP_TIME_FORMAT);
			
			return array(
				'locale'					=> AEC_LOCALE,
				'start_of_week' 			=> get_option('start_of_week'),
				'datepicker_format' 		=> ($isEuroDate) ? 'dd-mm-yy' : 'mm/dd/yy',	// jquery datepicker format
				'is24HrTime'				=> $is24HrTime,
				'show_weekends'				=> $options['show_weekends'],
				'agenda_time_format' 		=> ($is24HrTime) ? 'H:mm{ - H:mm}' : 'h:mmt{ - h:mmt}',
				'other_time_format' 		=> ($is24HrTime) ? 'H:mm' : 'h:mmt',
				'axis_time_format' 			=> ($is24HrTime) ? 'HH:mm' : 'h:mmt',
				'limit' 					=> $options['limit'],
				'calculating'				=> __('Calculating...', AEC_PLUGIN_NAME),
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
				'success'					=> __('Success!', AEC_PLUGIN_NAME),
				'whoops'					=> __('Whoops!', AEC_PLUGIN_NAME)
			);
		}
		
		function render_message($message, $errormsg=false){
			if ($errormsg) {
				echo '<div id="message" class="error">';
			} else {
				echo '<div id="message" class="updated fade">';
			}
			echo "<p><strong>$message</strong></p></div>";
		}

		function render_admin_menu(){
			if (function_exists('add_options_page')) {
			
				// define help text
				$help = '<h3>' . __('Ajax Event Calendar', AEC_PLUGIN_NAME) . ' <small>[v' . AEC_PLUGIN_VERSION . ']</small></h3>';
				$help .= __('Plugin help available', AEC_PLUGIN_NAME) . ' <a href="' . AEC_PLUGIN_HOMEPAGE . '" target="_blank">' . __('here', AEC_PLUGIN_NAME) . '</a>';
				$help .= '<br>' . __('Created by', AEC_PLUGIN_NAME) . ' <a href="http://eranmiller.com" target="_blank">Eran Miller</a>';

				// main menu page: calendar
				$page = add_menu_page('Ajax Event Calendar',  __('Calendar', AEC_PLUGIN_NAME), AEC_DOMAIN . 'add_events', AEC_PLUGIN_FILE, array($this, 'render_admin_calendar'), AEC_PLUGIN_URL . 'css/images/calendar.png', 30);

				// calendar admin specific scripts and styles
				add_action("admin_print_scripts-$page", array($this, 'admin_calendar_scripts'));
				add_action("admin_print_styles-$page", array($this, 'admin_calendar_styles'));
				add_contextual_help($page, $help);

				if (current_user_can(AEC_DOMAIN . 'manage_calendar')) {
					// sub menu page: category management
					$sub_category = add_submenu_page(AEC_PLUGIN_FILE, 'Categories', __('Categories', AEC_PLUGIN_NAME), AEC_DOMAIN . 'manage_calendar', 'event_categories', array($this, 'render_admin_category'));
					add_contextual_help($sub_category, $help);

					// category admin specific scripts and styles
					add_action("admin_print_scripts-$sub_category", array($this, 'admin_category_scripts'));
					add_action("admin_print_styles-$sub_category", array($this, 'admin_category_styles'));

					// sub menu page: activity report
					$sub_report = add_submenu_page(AEC_PLUGIN_FILE, 'Activity Report', __('Activity Report', AEC_PLUGIN_NAME), AEC_DOMAIN . 'manage_calendar', 'activity_report', array($this, 'render_activity_report'));
					add_contextual_help($sub_report, $help);

					// sub settings page: calendar options
					$sub_options = add_options_page('Calendar', __('Ajax Event Calendar', AEC_PLUGIN_NAME), AEC_DOMAIN . 'manage_calendar', __FILE__, array($this, 'render_calendar_options'));
					add_contextual_help($sub_options, $help);
					
				}
			}
		}

		function render_frontend_calendar(){
			$out  = '<div id="aec-container">';
			$out .= '<div id="aec-loading">' . __('Loading...', AEC_PLUGIN_NAME) . '</div>';
			$out .='<div id="aec-header">';
			$options = get_option('aec_options');
			if ($options['menu']) {
				$out .= '<div id="aec-menu">';
				$out .= '<a href="' . admin_url() . 'admin.php?page=ajax-event-calendar.php">' . __('Add Events', AEC_PLUGIN_NAME) . '</a>';
				$out .= '</div>';
			}
			$out .= '<ul id="aec-filter">';
			$categories = $this->query_categories();

			if (sizeof($categories) > 1) {
				$out .= '<li>' . __('Show Types', AEC_PLUGIN_NAME) . '</li>' . "\n";
				$out .= '<li class="active"><a class="round5 all">' . __('All', AEC_PLUGIN_NAME) . '</a></li>' . "\n";
				foreach ($categories as $category) {
					 $out .= '<li><a class="round5 cat' . $category->id . '">' . $this->render_i18n_data($category->category) . '</a></li>' . "\n";
				}
			}
			$out .= '</ul>';
			$out .= '</div>';
			$out .= '<div id="aec-calendar"></div>';
			$out .= '<a href="http://eranmiller.com/" id="aec-credit">' . AEC_PLUGIN_NAME . ' v' . AEC_PLUGIN_VERSION . ' ' . __('Created By', AEC_PLUGIN_NAME) . ' Eran Miller</a>';
			$out .= '</div>';
			return $out;
		}

		function render_admin_calendar(){
			if (!current_user_can(AEC_DOMAIN . 'add_events'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));

			$out = '<div class="wrap">' . "\n";
			$out .= '<h2>' . __('Calendar', AEC_PLUGIN_NAME) . '</h2>' . "\n";
			$out .= '<div id="aec-loading">' . __('Loading...', AEC_PLUGIN_NAME) . '</div>' . "\n";
			$out .= '<div id="aec-calendar"</div>' . "\n";
			$out .= '</div>' . "\n";
			echo $out;
		}

		function render_frontend_modal(){
			require_once AEC_PLUGIN_PATH . 'inc/show-event.php';
		}
		
		function render_admin_modal(){
			if (!current_user_can(AEC_DOMAIN . 'add_events'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			require_once AEC_PLUGIN_PATH . 'inc/admin-event.php';
			exit();
		}

		function render_admin_category(){
			if (!current_user_can(AEC_DOMAIN . 'manage_calendar'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));

			$categories = $this->query_categories();
			$out  = '<div class="wrap">' . "\n";
			$out .= '<h2>' . __('Categories', AEC_PLUGIN_NAME) . '</h2>' . "\n";
			$out .= '<h5>' . __('Add a new, or edit/delete an existing calendar category.  To change the category tile color, click the color swatch or edit the field containing the hex value, then click Update.  The foreground color (black or white) is automatically assigned for optimal readbility based on the selected background color.', AEC_PLUGIN_NAME) . '</h5>' . "\n";
			$out .= '<form id="aec-category-form">' . "\n";
			$out .= '<p><input type="hidden" id="fgcolor" name="fgcolor" class="fg" value="#FFFFFF" />';
			$out .= '<input class="bg colors" type="text" id="bgcolor" name="bgcolor" value="#005294" size="7" maxlength="7" autocomplete="off"> ';
			$out .= '<input type="text" id="category" name="category" value="" /> ';
			$out .= '<button class="add button-primary">' . __('Add', AEC_PLUGIN_NAME) . '</button></p>';
			$out .= '</form>' . "\n";
			$out .= '<form id="aec-category-list">' . "\n";
			foreach ($categories as $category) {
				$delete = ($category->id > 1) ? 
					'<button class="button-secondary delete">' . __('Delete', AEC_PLUGIN_NAME) . '</button>' . "\n" : 
					' <em>' . __('This category is required and can only be edited.', AEC_PLUGIN_NAME) . '</em>';
				$out .= '<p id="id_' . $category->id . '">' . "\n";
				$out .= '<input type="hidden" name="id" value="' . $category->id . '" />' . "\n";
				$out .= '<input type="hidden" name="fgcolor" value="#' . $category->fgcolor . '" class="fg" />' . "\n";
				$out .= '<input type="text" name="bgcolor" size="7" maxlength="7" autocomplete="off" value="#' . $category->bgcolor . '" class="bg colors" />' . "\n";
				$out .= '<input type="text" name="category" value="' . $this->render_i18n_data($category->category) . '" class="edit" />' . "\n";
				$out .= '<button id="category_update" class="update button-secondary">' . __('Update', AEC_PLUGIN_NAME) . '</button>' . "\n";	
				$out .= $delete;
				$out .= '</p>' . "\n";
			}
			$out .= '</form>' . "\n";
			$out .= '</div>' . "\n";
			echo $out;
		}

		function render_activity_report(){
			if (!current_user_can(AEC_DOMAIN . 'manage_calendar'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			
			$out  = '<div class="wrap">' . "\n";
			$out .= '<h2>' . __('Activity Report', AEC_PLUGIN_NAME) . '</h2>' . "\n";
			$out .= '<h5>' . __('Number of events scheduled for the current month, by type:', AEC_PLUGIN_NAME) . '</h5>' . "\n";
			$rows = $this->query_monthly_activity();
			if ( count( $rows ) ) {
				foreach ( $rows as $row ) {
					$out .= '<p><strong>' . $row->cnt . '</strong> <em>' . $this->render_i18n_data($row->category) . '</em> ';
					$out .= _n('Event', 'Events', $row->cnt, AEC_PLUGIN_NAME);
					$out .= '</p>' . "\n";
				}
			} else {
				$out .= '<p><em>' . __('No events this month.', AEC_PLUGIN_NAME) . '</em></p>' . "\n";
			}
			$out .= '</div>' . "\n";
			print $out;
		}
		
		function twentyeleven_option_page_capability( $capability ) {
			return 'edit_theme_options';
		}

		function render_calendar_options(){
			if (!current_user_can(AEC_DOMAIN . 'manage_calendar'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			require_once AEC_PLUGIN_PATH . 'inc/admin-options.php';
		}

		function render_event($input, $render=false){
			$input['allDay'] = ($input['allDay']) ? true : false;
			$output = array(
				'id'	 	=> $input['id'],
				'title'  	=> $input['title'],
				'start'		=> $input['start'],
				'end'		=> $input['end'],
				'allDay' 	=> $input['allDay'],
				'className'	=> 'cat' . $input['category_id']
			);
			//$output = $this->cleanse_output($output);
			if ($render) return $output;
			$this->render_json($output);
		}

		// renders events as json, authentication dependent, input: database object
		function render_events(){
			// users that are not logged-in see all events
			$user = false;
			global $current_user;
			get_currentuserinfo();
			if ($_POST['edit']) {
				// users with aec_manage_events capability can edit all events
				// users with aec_add_events capability can see/edit events only they create
				$user = (is_user_logged_in()) ? ((current_user_can(AEC_DOMAIN . 'manage_events')) ? false : $current_user->ID) : false;
			}

			$events = $this->query_events($user, $_POST['start'], $_POST['end']);
			if ($events) {
				$output = array();
				foreach($events as $event){
					array_push($output, $this->render_event($this->convert_object_to_array($event), true));
				}
								
				$this->render_json($output);
			}
		}

		// outputs added/updated category as json
		function render_category($input){
			$output = array(
				'id'	 	=> $input['id'],
				'category'  => $input['category'],
				'bgcolor'	=> $input['bgcolor'],
				'fgcolor'	=> $input['fgcolor']
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

		function render_i18n_data($data){
			return htmlentities(stripslashes($data), ENT_COMPAT, 'UTF-8');
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

		
		function frontend_calendar_variables(){
			$options = get_option(AEC_DOMAIN . 'options');
			return array_merge($this->localized_variables(),
				array(
					'ajaxurl'  	=> admin_url('admin-ajax.php'),		// required for non-admin ajax pages
					'editable'	=> false
				)
			);
		}

		function frontend_calendar_scripts(){
			if (!is_admin()) {
				wp_enqueue_script('jquery');
				wp_enqueue_script('fullcalendar');
				wp_enqueue_script('simplemodal');
				wp_enqueue_script('init_show_calendar');
				wp_localize_script('init_show_calendar', 'custom', $this->frontend_calendar_variables());
			}
		}

		function frontend_calendar_styles(){
			if (!is_admin()) {
				wp_enqueue_style('categories');
				wp_enqueue_style('custom');
			}
		}

		function admin_calendar_variables(){
			$is_admin = (current_user_can(AEC_DOMAIN . 'manage_calendar')) ? 1 : 0;
			return array_merge($this->localized_variables(),
				array(
					'admin' 					=> $is_admin,
					// if moving editing to front-end add ajaxurl 
					//'ajaxurl'  				=> admin_url('admin-ajax.php'),		// required for non-admin ajax pages
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

		function admin_calendar_scripts(){
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_script('jquery-ui-droppable');
			wp_enqueue_script('jquery-ui-selectable');
			wp_enqueue_script('jquery-ui-resizable');
			wp_enqueue_script('jquery-ui-datepicker');
			if (AEC_LOCALE != 'en') wp_enqueue_script('datepicker-locale');	// if not english load localization
			wp_enqueue_script('timePicker');
			wp_enqueue_script('growl');
			wp_enqueue_script('fullcalendar');
			wp_enqueue_script('simplemodal');
			wp_enqueue_script('init_admin_calendar');
			wp_localize_script('init_admin_calendar', 'custom', $this->admin_calendar_variables());
		}

		function admin_calendar_styles(){
			wp_enqueue_style('jq_ui_css');
			wp_enqueue_style('categories');
			wp_enqueue_style('custom');
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
			wp_enqueue_style('custom');
			wp_enqueue_style('categories');
		}

		function admin_options_initialize(){
			register_setting(AEC_DOMAIN . 'plugin_options', AEC_DOMAIN . 'options', array($this, 'admin_options_validate'));
		}

		function admin_options_validate($input){
			// validation placeholder
			return $input;
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
			$event = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE id = %d ORDER BY start;', $id));
			if ($this->return_result($event)) {
				return $event;
			}
		}
		
		// output 6 primary fields for fullcalendar input
		function query_events($user_id, $start, $end){
			global $wpdb;
			$anduser = ($user_id) ? ' AND user_id = ' . $user_id : '';
			$start = date('Y-m-d', $start);
			$end = date('Y-m-d', $end);
			$result = $wpdb->get_results('SELECT
										id,
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
										$anduser .
										' ORDER BY start;'
									);
			return $this->return_result($result);
		}

		function query_event_count_by_user($user_id){
			global $wpdb;
			$result = $wpdb->get_var('SELECT count(id)
									 FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
									 WHERE user_id = ' . $user_id . ';'
									);
			return $this->return_result($result);
		}

		function query_categories(){
			global $wpdb;
			$result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' ORDER BY id;');
			return $this->return_result($result);
		}

		function add_event(){
			if (!isset($_POST['event'])) return;
			$input = $this->cleanse_event_input($_POST['event']);			

			global $wpdb;
			$result = $wpdb->insert($wpdb->prefix . AEC_EVENT_TABLE,
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
				if ($input['user_id']) {					// only render events not generated by the system (user id: 0)
					$input['id'] = $wpdb->insert_id;		// id of newly created row
					$this->render_event($input);
				}
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
			if ($this->return_result($result)) {
				$input['id'] = $wpdb->insert_id;	// id of newly created row
				$this->generate_css();
				$this->render_category($input);
			}
		}

		function move_event(){
			if (!isset($_POST)) return;
			$input = $_POST;
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
			$this->render_json($this->return_result($result));
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

			if ($this->return_result($result)) {
				$this->render_event($input);
			}
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
			$this->generate_css();
			if ($this->return_result($result)){
				$this->render_category($input);
			}
		}

		function delete_event(){
			if (!isset($_POST['id'])) return;
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE id = %d;', $_POST['id']));
			$this->render_json($this->return_result($result));
		}

		function delete_events_by_user($user_id){
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE user_id = %d;', $user_id));
			return $this->return_result($result);
		}

		/*  
		// placeholder for when delete_user hook includes reassign id
		function reassign_events_by_user($old_id, $new_id){
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('UPDATE '. $wpdb->prefix . AEC_EVENT_TABLE . ' SET user_id = %d WHERE user_id = %d;', $new_id, $old_id));
			return $this->return_result($result);
		}
		*/
		
		function delete_category(){
			if (!isset($_POST['id'])) return;
			global $wpdb;
			$used = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) as count FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE category_id = %d;', $_POST['id']));
			if ($used){
				$this->render_json('false');
			} else{
				$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' WHERE id = %d;', $_POST['id']));
				if ($this->return_result($result)) {
					$this->generate_css();
					$this->render_json($result);
				}
			}
		}

		function reassign_category(){
			if (!isset($_POST['id'])) return;
			global $wpdb;
			$result = $wpdb->get_results($wpdb->prepare('UPDATE '. $wpdb->prefix . AEC_EVENT_TABLE . ' SET category_id=1 WHERE category_id= %d;', $_POST['id']));
			if ($this->return_result($result))
				return $this->delete_category($id);
		}

		// parse form input
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

		function cleanse_event_input($input){
			$clean = $this->parse_input($input);
			// merge date fields and convert to database format
			$start 	= $clean['start_date'] . ' ' . $clean['start_time'];
			$end 	= $clean['end_date'] . ' ' . $clean['end_time'];
			$clean['start'] = $this->date_convert($start, AEC_WP_DATETIME_FORMAT, AEC_DB_DATETIME_FORMAT);
			$clean['end']	= $this->date_convert($end, AEC_WP_DATETIME_FORMAT, AEC_DB_DATETIME_FORMAT);
			return $clean;
		}

		function cleanse_category_input($input){
			$clean = $this->parse_input($input);
			$clean['bgcolor'] = str_replace('#', '', $clean['bgcolor']);	// strip '#' for storage
			$clean['fgcolor'] = str_replace('#', '', $clean['fgcolor']);
			return $clean;
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

		function confirm_delete_user_events($user_id){
			// TODO: display number of events to be deleted on delete confirmation page
			// $event_count = $this->query_event_count_by_user($user_id);
			$this->delete_events_by_user($user_id);
		}

		function convert_object_to_array($object){
			$array = array();
			foreach($object as $key => $value){
				$array[$key] = $value;
			}
			return $array;
		}

		function date_convert($date, $from, $to=false){
			// ajax_event_calendar::log($date .' '. $from .' '. $to);
			// if date format is d/m/Y, modify token to 'd-m-Y' so strtotime parses date correctly
			if (strpos($from, 'd') == 0) $date = str_replace("/", "-", $date);
			if ($to) return date_i18n($to, strtotime($date));
			return strtotime($date);

			// PHP 5.3 placeholder
			// $convert = DateTime::createFromFormat($from, $date);
			// return $convert->format($to);
		}

		function return_duration($event){
			$diff	= strtotime($event->end) - strtotime($event->start);
			$wsec	= 7*60*60*24;
			$dsec	= 60*60*24;
			$hsec	= 60*60;
			$msec	= 60;
			$week 	= floor($diff / $wsec);
			$day 	= floor(($diff - $week * $wsec) / $dsec);
			$hour 	= floor(($diff - $week * $wsec - $day * $dsec) / $hsec);
			$minute = floor(($diff - $week * $wsec - $day * $dsec - $hour * $hsec) / $msec);
			$day = ($event->allDay) ? $day+1 : $day;		// add one to day value of "allday" events

			$out = array();
			if ($week) { array_push($out, sprintf(_n('%d Week', '%d Weeks', $week, AEC_PLUGIN_NAME), $week)); }
			if ($day) { array_push($out, sprintf(_n('%d Day', '%d Days', $day, AEC_PLUGIN_NAME), $day)); }
			if ($hour) { array_push($out, sprintf(_n('%d Hour', '%d Hours', $hour, AEC_PLUGIN_NAME), $hour)); }
			if ($minute) { array_push($out, sprintf(_n('%d Minute', '%d Minutes', $minute, AEC_PLUGIN_NAME), $minute)); }

			return implode(', ', $out);
		}
		
		function return_result($result){
			if ($result === false){
				$this->log($wpdb->print_error());
				return false;
			}
			return $result;
		}

		// dynamically creates cat_colors.css file
		function generate_css(){
			$categories = $this->query_categories();
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

		// adds column field label to WordPress users page
		function add_events_column($columns){
			$columns['calendar_events'] = __('Events', AEC_PLUGIN_NAME);
			return $columns;
		}

		// adds column field value to WordPress users page
		function manage_events_column($empty='', $column_name, $user_id){
			if ($column_name == 'calendar_events')
				return $this->query_event_count_by_user($user_id);
		}

		// displays the "settings" link beside the plugin on the WordPress plugins page
		function settings_link($links, $file){
			if ($file == plugin_basename(__FILE__)){
				$settings = '<a href="' . get_admin_url() . 'options-general.php?page=' . AEC_PLUGIN_NAME . '/' . AEC_PLUGIN_FILE . '">' . __('Settings', AEC_PLUGIN_NAME) . '</a>';
				array_unshift($links, $settings);	// make the 'Settings' link appear first
			}
			return $links;
		}

		function set_option_page_capability($capability){
			return AEC_DOMAIN . 'manage_calendar';
		}

		function decommission_options($keys){
			$options = get_option(AEC_DOMAIN . 'options');
			foreach ($keys as $key) {
				if (array_key_exists($key, $options)) {
					unset($options[$key]);
				}
			}
			update_option(AEC_DOMAIN . 'options', $options);
		}

		function log($message){
			if (AEC_DEBUG){
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

// overrides the location of the WP error log
// @ini_set('error_log', AEC_PLUGIN_PATH . AEC_DOMAIN . 'debug.log');

if (class_exists('ajax_event_calendar')){
	if (version_compare(PHP_VERSION, '5', '<'))
		die(printf(__('Sorry, ' . AEC_PLUGIN_NAME . ' requires PHP 5 or higher. Your PHP version is "%s". Ask your web hosting service how to enable PHP 5 on your site.', AEC_PLUGIN_NAME), PHP_VERSION));

		// widgets code
		require_once AEC_PLUGIN_PATH . 'inc/widget-contributors.php';
		require_once AEC_PLUGIN_PATH . 'inc/widget-upcoming.php';
		$aec = new ajax_event_calendar();
}
?>