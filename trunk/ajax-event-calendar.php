<?php
/*
Plugin Name: Ajax Event Calendar
Plugin URI: http://wordpress.org/extend/plugins/ajax-event-calendar/
Description: A Google Calendar-like interface that allows registered users (with the necessary credentials) to add, edit and delete events in a common calendar viewable by blog visitors.
Version: 0.9.5
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

/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)){
	die('Sorry, but you cannot access this page directly.');
}

define('AEC_PLUGIN_VERSION', '0.9.5');
define('AEC_DOMAIN', 'aec_');
define('AEC_PLUGIN_FILE', basename(__FILE__));
define('AEC_PLUGIN_NAME', str_replace('.php', '', AEC_PLUGIN_FILE));
define('AEC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AEC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AEC_EVENT_TABLE', AEC_DOMAIN . 'event');
define('AEC_CATEGORY_TABLE', AEC_DOMAIN . 'event_category');
define('AEC_PLUGIN_HOMEPAGE', 'http://wordpress.org/extend/plugins/' . AEC_PLUGIN_NAME . '/');

// Widgets Code
require_once AEC_PLUGIN_PATH . 'inc/contributors-widget.php';
require_once AEC_PLUGIN_PATH . 'inc/upcoming-widget.php';

if (!class_exists('ajax_event_calendar')){
	class ajax_event_calendar{

		private $required_fields = array();
		// 0: hide | 1:show | 2:require
		private $plugin_default_options = array(
				'menu' => '1',
				'limit' => '1',
				'title' => '2',
				'venue' => '1',
				'address' => '2',
				'city' => '2',
				'state' => '2',
				'zip' => '2',
				'link' => '1',
				'description' => '2',
				'contact' => '2',
				'contact_info' => '2',
				'accessible' => '1',
				'rsvp' => '1',
				'reset' => '0'
			);
			
		// PHP 5 constructor
		function __construct(){
		    add_action('init', array($this, 'localize_plugin'), 10, 1);
			add_action('admin_menu', array($this, 'set_admin_menu'));
			add_action('admin_init', array($this, 'aec_options_init'));
			add_action('delete_user', array($this, 'confirm_delete_user_events'));
			add_filter('manage_users_columns', array($this, 'add_events_column'));
			add_filter('manage_users_custom_column', array($this, 'manage_events_column'), 10, 3);
			add_filter('plugin_action_links', array($this, 'settings_link'), 10, 2);
			//placeholder for database update code
			//add_action('plugins_loaded', array($this, 'update_database');
			add_shortcode('calendar', array($this, 'show_calendar'));
     		update_option(AEC_DOMAIN . 'version', AEC_PLUGIN_VERSION);
			
			$options = get_option(AEC_DOMAIN . 'options');
			if ( !is_array($options) || !isset($options['reset']) || $options['reset']=='1') {
				// Update Settings
				update_option(AEC_DOMAIN . 'options', $this->plugin_default_options);
				// Add Sample Event
				$input['user_id'] = 0;	// system id
				$input['title'] = 'Ajax Event Calendar [v' . AEC_PLUGIN_VERSION . '] Installed!';
				$input['start_date'] = date('Y-m-d');
				$input['start_time'] = date('H:00:00');
				$input['end_date'] = date('Y-m-d');
				$input['end_time'] = date('H:00:00');
				$input['allDay'] = 1;
				$input['category_id'] = 1;
				$input['description'] = 'This is a sample event with all the fields populated.  <ul><li>Modify field options in the settings menu</li><li>Manage event categories in the calendar menu</li><li>Add user authorization in the user menu</li></ul>';
				$input['link'] = AEC_PLUGIN_HOMEPAGE;
				$input['venue'] = 'Plugins';
				$input['address'] = 'WordPress';
				$input['city'] = 'Chicago';
				$input['state'] = 'IL';
				$input['zip'] = '60605';
				$input['contact'] = 'Eran Miller';
				$input['contact_info'] = 'plugins@eranmiller.com';
				$input['access'] = 0;
				$input['rsvp'] = 0;
				$this->add_event($input, false);
			}
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
						venue VARCHAR(100) NOT NULL,
						address VARCHAR(100),
						city VARCHAR(50) NOT NULL,
						state CHAR(2) NOT NULL,
						zip MEDIUMINT(10) UNSIGNED NOT NULL,
						contact VARCHAR(50) NOT NULL,
						contact_info VARCHAR(50) NOT NULL,
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

			// new roles for plugin
			add_role('calendar_contributor', 'Calendar Contributor', array(
				'read' => 1,
				AEC_DOMAIN . 'add_events' => 1
			));
			add_role('blog_calendar_contributor', 'Blog + Calendar Contributor', array(
				'read' => 1,
				'edit_posts' => 1,
				'delete_posts' => 1,
				AEC_DOMAIN . 'add_events' => 1
			));

			// add capabilities for administrators
			$role = get_role('administrator');
			$role->add_cap(AEC_DOMAIN . 'add_events');
			$role->add_cap(AEC_DOMAIN . 'run_reports');
		}

		function set_admin_menu(){
			if (function_exists('add_options_page'))
				$page = add_menu_page('Ajax Event Calendar',  __('Calendar', AEC_PLUGIN_NAME), AEC_DOMAIN . 'add_events', AEC_PLUGIN_FILE, array($this, 'admin_page'), AEC_PLUGIN_URL . 'css/images/calendar.png', 30);

				// only load scripts and styles on plugin page
				add_action("admin_print_scripts-$page", array($this, 'load_calendar_js'));
				add_action("admin_print_styles-$page", array($this, 'load_calendar_css'));

				// contextual help override
				$help = '<h3>' . __('Ajax Event Calendar', AEC_PLUGIN_NAME) . ' <small>[v' . AEC_PLUGIN_VERSION . ']</small></h3>';
				$help .= __('Plugin help available', AEC_PLUGIN_NAME) . ' <a href="' . AEC_PLUGIN_HOMEPAGE . '" target="_blank">' . __('here', AEC_PLUGIN_NAME) . '</a>';
				$help .= '<br>' . __('Created by', AEC_PLUGIN_NAME) . ' <a href="http://eranmiller.com" target="_blank">Eran Miller</a>';
				add_contextual_help($page, $help);

				// sub menu page: categories
				$sub_category = add_submenu_page(AEC_PLUGIN_FILE, 'Categories', __('Categories', AEC_PLUGIN_NAME), AEC_DOMAIN . 'run_reports', 'event_categories', array($this, 'category_page'));
				add_contextual_help($sub_category, $help);

				// only load scripts and styles on sub page
				add_action("admin_print_scripts-$sub_category", array($this, 'load_category_js'));
				add_action("admin_print_styles-$sub_category", array($this, 'load_category_css'));

				// sub menu page: categories
				$sub_report = add_submenu_page(AEC_PLUGIN_FILE, 'Activity Report', __('Activity Report', AEC_PLUGIN_NAME), AEC_DOMAIN . 'run_reports', 'activity_report', array($this, 'run_reports'));
				add_contextual_help($sub_report, $help);
				
				// settings menu
				$sub_options = add_options_page('Calendar', __('Calendar', AEC_PLUGIN_NAME), 'manage_options', __FILE__, array($this, 'aec_options_page'));
				add_contextual_help($sub_options, $help);
		}

	    function localize_plugin() {
			load_plugin_textdomain( AEC_PLUGIN_NAME, false, AEC_PLUGIN_NAME . '/locale/' );
		}

		function admin_page(){
			if (!current_user_can(AEC_DOMAIN . 'add_events'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			include_once(AEC_PLUGIN_PATH . 'inc/admin-calendar.php');
		}

		function category_page(){
			if (!current_user_can(AEC_DOMAIN . 'run_reports'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			include_once(AEC_PLUGIN_PATH . 'inc/admin-category.php');
		}

		function run_reports(){
			if (!current_user_can(AEC_DOMAIN . 'run_reports'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
			include_once(AEC_PLUGIN_PATH . 'inc/admin-reports.php');
		}
		
		function show_calendar(){
			require_once dirname(__FILE__) . '/inc/show-calendar.php';
		}
		
		function load_calendar_js(){
			wp_enqueue_script('jquery');
			wp_enqueue_script('jq_ui', AEC_PLUGIN_URL . 'js/jquery-ui-1.8.13.custom.min.js', array('jquery'), null, false);
			wp_enqueue_script('timePicker', AEC_PLUGIN_URL . 'js/jquery.timePicker.min.js', array('jquery'), null, false);
			wp_enqueue_script('fullcalendar', AEC_PLUGIN_URL . 'js/jquery.fullcalendar.min.js', array('jquery'), null, false);
			wp_enqueue_script('growl', AEC_PLUGIN_URL . 'js/jquery.jgrowl.min.js', array('jquery'), null, false);
			wp_enqueue_script('simplemodal', AEC_PLUGIN_URL . 'js/jquery.simplemodal.1.4.1.min.js', array('jquery'), null, false);
		}

		function load_category_js(){
			wp_enqueue_script('jquery');
			wp_enqueue_script('jq_ui', AEC_PLUGIN_URL . 'js/jquery-ui-1.8.13.custom.min.js', array('jquery'), null, false);
			wp_enqueue_script('growl', AEC_PLUGIN_URL . 'js/jquery.jgrowl.min.js', array('jquery'), null, false);
			wp_enqueue_script('color_picker', AEC_PLUGIN_URL . 'js/jquery.miniColors.min.js', array('jquery'), null, false);
			wp_enqueue_script('inline_edit', AEC_PLUGIN_URL . 'js/jquery.jeditable.min.js', array('jquery'), null, false);
		}

		function load_calendar_css(){
			wp_enqueue_style('jq_ui_css', AEC_PLUGIN_URL . 'css/jquery-ui-1.8.13.custom.css');
			wp_enqueue_style('custom', AEC_PLUGIN_URL . 'css/custom.css');
			wp_enqueue_style('categories', AEC_PLUGIN_URL . 'css/cat_colors.css');
		}

		function load_category_css(){
			wp_enqueue_style('custom', AEC_PLUGIN_URL . 'css/custom.css');
		}

		function date_display_format($date){
			return date('m/d/Y g:i A', strtotime($date));
		}

		function date_database_format($date){
			return date('Y-m-d H:i:s', strtotime($date));
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
				$clean[$key] = $value;
			}
			return $clean;
		}

		function cleanse_event_input($input){
			$clean = $this->parse_input($input);
			// if event is allDay, set time values to 00:00:00
			if ($clean['allDay']){
				$clean['start_time'] = '00:00:00';
				$clean['end_time'] = '00:00:00';
			}
			return $clean;
		}

		function cleanse_category_input($input){
			$clean = $this->parse_input($input);
			$clean['bgcolor'] = str_replace('#', '', $clean['bgcolor']);  // strip # from color for storage
			$clean['fgcolor'] = str_replace('#', '', $clean['fgcolor']);
			return $clean;
		}

		function output_event($input){
			$input['allDay'] = ($input['allDay']) ? true : false;
			$output = array(
				'id'	 	=> $input['id'],
				'title'  	=> stripslashes($input['title']),
				'start'  	=> $this->date_database_format($input['start_date'] . ' ' . $input['start_time']),
				'end'	  	=> $this->date_database_format($input['end_date'] . ' ' . $input['end_time']),
				'allDay' 	=> $input['allDay'],
				'className'	=> 'cat' . $input['category_id']
			);
			echo json_encode($output);
		}

		function output_events($events){
			$output = array();
			foreach($events as $event){
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
			echo json_encode($output);
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

		function add_event($input, $output=true){
			$input = $this->cleanse_event_input($input);

			global $wpdb;
			$result = $wpdb->insert($wpdb->prefix . AEC_EVENT_TABLE
									, array('user_id' 		=> $input['user_id'],
											'title'	 		=> $input['title'],
											'start'	 		=> $this->date_database_format($input['start_date'] . ' ' . $input['start_time']),
											'end'	 		=> $this->date_database_format($input['end_date'] . ' ' . $input['end_time']),
											'allDay'	 	=> $input['allDay'],
											'category_id'	=> $input['category_id'],
											'description'	=> $input['description'],
											'link'			=> $input['link'],
											'venue'			=> $input['venue'],
											'address'		=> $input['address'],
											'city'			=> $input['city'],
											'state'			=> $input['state'],
											'zip'			=> $input['zip'],
											'contact'		=> $input['contact'],
											'contact_info'	=> $input['contact_info'],
											'access'		=> $input['access'],
											'rsvp'			=> $input['rsvp']
											)
									, array('%d', //user_id
											'%s', //title
											'%s', //start
											'%s', //end
											'%d', //allDay
											'%d', //category_id
											'%s', //description
											'%s', //link
											'%s', //venue
											'%s', //address
											'%s', //city
											'%s', //state
											'%s', //zip
											'%s', //contact
											'%s', //contact_info
											'%d', //access
											'%d' //rsvp
											)
								);
			if ($result === false){
				$this->log($wpdb->print_error());
			} else{
				if ($output) {
					$input['id'] = $wpdb->insert_id;	// ID of newly created row
					$this->output_event($input);
				}
			}
		}

		function move_event($input){
			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_EVENT_TABLE,
									array('start'	=> $input['start'],
										  'end'	 	=> $input['end'],
										  'allDay'	=> $input['allDay']
									),
									array('id' 		=> $input['id']),
									array('%s',		//start
										  '%s',		//end
										  '%d'		//allDay
									),
									array ('%d') 	//id
								);
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			echo $result;
		}

		function update_event($input){
			$input = $this->cleanse_event_input($input);

			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_EVENT_TABLE,
									array('user_id' 		=> $input['user_id'],
											'title'	 		=> $input['title'],
											'start'	 		=> $this->date_database_format($input['start_date'] . ' ' . $input['start_time']),
											'end'	 		=> $this->date_database_format($input['end_date'] . ' ' . $input['end_time']),
											'allDay'		=> $input['allDay'],
											'category_id'	=> $input['category_id'],
											'description'	=> $input['description'],
											'link'			=> $input['link'],
											'venue'			=> $input['venue'],
											'address'		=> $input['address'],
											'city'			=> $input['city'],
											'state'			=> $input['state'],
											'zip'			=> $input['zip'],
											'contact'		=> $input['contact'],
											'contact_info'	=> $input['contact_info'],
											'access'		=> $input['access'],
											'rsvp'			=> $input['rsvp']
											),
									array('id' => $input['id']),
									array('%d', //user_id
											'%s', //title
											'%s', //start
											'%s', //end
											'%d', //allDay
											'%d', //category_id
											'%s', //description
											'%s', //link
											'%s', //venue
											'%s', //address
											'%s', //city
											'%s', //state
											'%s', //zip
											'%s', //contact
											'%s', //contact_info
											'%d', //access
											'%d' //rsvp
											),
									array ('%d') //id
								);
			if ($result === false){
				$this->log($wpdb->print_error());
			} else{
				$this->output_event($input);
			}
		}

		function delete_event($id){
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE id = %d;', $id));
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			echo $result;
		}

		function process_event($_POST){
			$action = $_POST['action'];
			switch($action){
				case 'add':
				$this->add_event($_POST['event']);
				break;
				case 'move':
				$this->move_event($_POST);
				break;
				case 'update':
				$this->update_event($_POST['event']);
				break;
				case 'delete':
				$this->delete_event($_POST['id']);
				break;
			}
		}

		function init_form($start, $end, $allDay, $user_id){
			$event->id = '';
			$event->user_id = $user_id;								// WP data
			$event->title = '';
			$event->start = $this->date_display_format($start); 	// FC data
			$event->end = $this->date_display_format($end); 		// FC data
			$event->allDay = $allDay;								// FC data
			$event->category_id = 1;
			$event->description = '';
			$event->link = '';
			$event->venue = '';
			$event->address = '';
			$event->city = '';
			$event->state = '';
			$event->zip = '';
			$event->contact = '';
			$event->contact_info = '';
			$event->access = 0;
			$event->rsvp = 0;
			return $event;
		}

		function get_event($id){
			global $wpdb;
			$result = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE id = %d ORDER BY start;', $id));
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			// Format fields for user display
			$result->start = $this->date_display_format($result->start);
			$result->end = $this->date_display_format($result->end);
			$result->title = stripslashes($result->title);
			$result->description = stripslashes($result->description);
			$result->link = stripslashes($result->link);
			$result->venue = stripslashes($result->venue);
			$result->address = stripslashes($result->address);
			$result->city = stripslashes($result->city);
			$result->contact = stripslashes($result->contact);
			$result->contact_info = stripslashes($result->contact_info);
			return $result;
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

		// Display settings link on the plugins page
		function settings_link($links, $file){
			if ($file == plugin_basename(__FILE__)){
				$posk_links = '<a href="' . get_admin_url() . 'options-general.php?page=' . AEC_PLUGIN_NAME . '/' . AEC_PLUGIN_FILE . '">' . __('Settings', AEC_PLUGIN_NAME) . '</a>';
				// make the 'Settings' link appear first
				array_unshift($links, $posk_links);
			}
			return $links;
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
				$this->output_events($results);
			}
		}

		function add_category($input){
			$input = $this->cleanse_category_input($input);

			global $wpdb;
			$result = $wpdb->insert($wpdb->prefix . AEC_CATEGORY_TABLE,
									array('category'	 	=> $input['category'],
										   'bgcolor'	 	=> $input['bgcolor'],
										   'fgcolor'	 	=> $input['fgcolor']
										),
									array('%s', //category
										   '%s', //bgcolor
										   '%s' //fgcolor
										));
			if ($result === false){
				$this->log($wpdb->print_error());
			} else{
				$id = $wpdb->insert_id;	// ID of newly created row
				$output = array(
					'id'	 	=> $id,
					'category'  => $input['category'],
					'bgcolor'	=> $input['bgcolor'],
					'fgcolor'	=> $input['fgcolor']
				);
				echo json_encode($output);
			}
			$this->generate_css();
		}

		function update_category($input){
			$input = $this->cleanse_category_input($input);

			global $wpdb;
			$result = $wpdb->update($wpdb->prefix . AEC_CATEGORY_TABLE,
									array('category'	=> $input['category'],
										   'bgcolor'	=> $input['bgcolor'],
										   'fgcolor'	=> $input['fgcolor']
									),
									array('id' => $input['id']),
									array('%s', //category
										   '%s', //bgcolor
										   '%s' //fgcolor
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
				echo json_encode($output);
			}
			$this->generate_css();
		}

		function change_category($id){
			global $wpdb;
			$result = $wpdb->get_results($wpdb->prepare('UPDATE '. $wpdb->prefix . AEC_EVENT_TABLE . ' SET category_id=1 WHERE category_id= %d;', $id));
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			return $this->delete_category($id);
		}

		function delete_category($id){
			global $wpdb;
			$used = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) as count FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE category_id = %d;', $id));
			if ($used){
				echo 'false';
			} else{
				$result = $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' WHERE id = %d;', $id));
				if ($result === false){
					$this->log($wpdb->print_error());
				}
				echo $result;
			}
		}

		function get_categories(){
			global $wpdb;
			$result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' ORDER BY id;');
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			return $result;
			$this->generate_css();
		}

		function remove_table($table_name){
			global $wpdb;
			$result = $wpdb->query($wpdb->prepare('DROP TABLE %s', $table_name));
			if ($result === false){
				$this->log($wpdb->print_error());
			}
			echo $result;
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
				$out .= '{color:#' . $category->fgcolor . ';background-color:#';
				$out .= $category->bgcolor . ';border-color:#' . $category->bgcolor . '}';
				$out .= "\n";
			}

			$cssFile = AEC_PLUGIN_PATH . "css/cat_colors.css";
			$fh = fopen($cssFile, 'w+') or die('cannot open file');
			fwrite($fh, $out);
			fclose($fh);
		}

		function log($message){
			if(WP_DEBUG === true){
				if(is_array($message) || is_object($message)){
					error_log(print_r($message, true));
				} else{
					error_log($message);
				}
			}
			return;
		}

		function add_required_field($field){
			array_push($this->required_fields, $field);
		}

		function get_required_fields(){
			if (count($this->required_fields))
				return "'" . join("','", $this->required_fields) . "'";
			return;
		}

		function aec_options_validate($input){
			// validation placeholder
			return $input;
		}

		function aec_options_init(){
			$options = $options = get_option(AEC_DOMAIN . 'options');
			register_setting(AEC_DOMAIN . 'plugin_options', AEC_DOMAIN . 'options', array($this, 'aec_options_validate'));
		}

		function aec_options_page(){ 
			if (!current_user_can(AEC_DOMAIN . 'run_reports'))
				wp_die(__('You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME));
		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php _e('Ajax Event Calendar Options', AEC_PLUGIN_NAME); ?></h2>
			<?php
			$general = array(
				'menu' =>  __('Show administrative menu above the front-end calendar.', AEC_PLUGIN_NAME),
				'limit' =>  __('Enforce event creation between 30 minutes and one year from the current time.', AEC_PLUGIN_NAME),
			);
			$form = array(
				'venue' => __('Venue', AEC_PLUGIN_NAME),
				'address' => __('Neighborhood or Street Address', AEC_PLUGIN_NAME),
				'city' => __('City', AEC_PLUGIN_NAME),
				'state' => __('State', AEC_PLUGIN_NAME),
				'zip' => __('Postal Code', AEC_PLUGIN_NAME),
				'link' => __('Event Link', AEC_PLUGIN_NAME),
				'description' => __('Description', AEC_PLUGIN_NAME),
				'contact' => __('Contact Name', AEC_PLUGIN_NAME),
				'contact_info' => __('Contact Information', AEC_PLUGIN_NAME)
			);
			$optional = array(
				'accessible' => __('This event is accessible to people with disabilities.', AEC_PLUGIN_NAME),
				'rsvp' => __('Please register with the contact person for this event.', AEC_PLUGIN_NAME)
			)
			?>
			<form method="post" action="options.php">
				<?php settings_fields(AEC_DOMAIN . 'plugin_options'); ?>
				<?php $options = get_option(AEC_DOMAIN . 'options'); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('General Options', AEC_PLUGIN_NAME); ?></th>
						<td>
							<?php
							foreach ($general as $field => $value) {
								$checked = ($options[$field]) ? ' checked="checked" ' : ' ';
								echo '<input type="hidden" name="aec_options[' . $field . ']" value="0" />';
								echo '<label>';
								echo '<input' . $checked . 'id="' . $field . '" value="1" name="aec_options[' . $field . ']" type="checkbox" /> ';
								echo $value . '</label><br />';
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Check Required Form Fields', AEC_PLUGIN_NAME); ?></th>
						<td>
							<?php 
							foreach ($form as $field => $value) {
								$checked = ($options[$field] == 2) ? ' checked="checked" ' : ' ';
								echo '<input type="hidden" name="aec_options[' . $field . ']" value="1" />';
								echo '<label>';
								echo '<input' . $checked . 'id="' . $field . '" value="2" name="aec_options[' . $field . ']" type="checkbox" /> ';
								echo $value . '</label><br />';
							}
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Optional Checkboxes', AEC_PLUGIN_NAME); ?></th>
						<td>
							<?php
							foreach ($optional as $field => $value) {
								$checked = ($options[$field]) ? ' checked="checked" ' : ' ';
								echo '<input type="hidden" name="aec_options[' . $field . ']" value="0" />';
								echo '<label>';
								echo '<input' . $checked . 'id="' . $field . '" value="1" name="aec_options[' . $field . ']" type="checkbox" /> ';
								echo $value . '</label><br />';
							}
							?>
						</td>
					</tr>
					<tr><td colspan="2"><hr></td></tr>
					<tr valign="top">
						<th scope="row"><?php _e('Restore Original Settings', AEC_PLUGIN_NAME); ?></th>
						<td>
							<label>
							<input type="hidden" name="aec_options[reset]" value="0" />
							<input name="aec_options[reset]" type="checkbox" value="1" <?php if (isset($options['reset'])) { checked('1', $options['reset']); } ?> /> <?php _e('Resets plugin settings on Save', AEC_PLUGIN_NAME); ?></label>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php _e('Save Changes', AEC_PLUGIN_NAME); ?>" />
				</p>
			</form>
		</div>
<?php	}
		
		function calcDuration($from, $end, $allday){
			$timestamp = strtotime($end) - strtotime($from);
			$days = floor($timestamp/(60*60*24)); $timestamp%=60*60*24;
			$hrs = floor($timestamp/(60*60)); $timestamp%=60*60;
			$mins = floor($timestamp/60); $secs=$timestamp%60;
			
			$out = array();
			if ($allday) $days += 1;
			if ($days >= 1) { array_push($out, $days . ' ' . _n( 'day', 'days', $days, AEC_PLUGIN_NAME)); }
			if ($hrs >= 1) { array_push($out, $hrs . ' ' . _n( 'hour', 'hours', $hrs, AEC_PLUGIN_NAME)); }
			if ($mins >= 1) { array_push($out, $mins . ' ' . _n( 'minute', 'minutes', $mins, AEC_PLUGIN_NAME)); }
			return implode(', ', $out);
		}
		
		function plural($value){
			return ($value == 1) ? '' : 's';
		}
	}
}

register_activation_hook(__FILE__, array('ajax_event_calendar', 'install'));

if (class_exists('ajax_event_calendar')){
	if (version_compare(PHP_VERSION, '5', '<'))
		die(printf(__('Sorry, ' . AEC_PLUGIN_NAME . ' requires PHP 5 or higher. Your PHP version is "%s". Ask your web hosting service how to enable PHP 5 on your site.', AEC_PLUGIN_NAME), PHP_VERSION));
	$aec = new ajax_event_calendar();
}
?>