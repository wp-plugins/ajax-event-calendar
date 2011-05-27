<?php
/*
Plugin Name: Ajax Event Calendar
Plugin URI: http://eranmiller.com/plugins/aec/
Description: A multi-user ajax event calendar for editing and viewing via a "Google Calendar"-like interface.
Version: 0.2.3
Author: Eran Miller
Author URI: http://eranmiller.com
License: GPL2
*/

/*  Copyright 2011  Eran Miller <email : eranmiller+aec@gmail.com>

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
if ( basename( $_SERVER['PHP_SELF'] ) == basename( __FILE__ ) ) {
	die( 'Sorry, but you cannot access this page directly.' );
}

define( 'AEC_DOMAIN', 'aec_' );
define( 'AEC_PLUGIN_NAME', str_replace( '.php', '', basename( __FILE__ ) ) );
define( 'AEC_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'AEC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AEC_PLUGIN_VERSION', '0.6' );
define( 'AEC_EVENT_TABLE', AEC_DOMAIN . 'event' );
define( 'AEC_CATEGORY_TABLE', AEC_DOMAIN . 'event_category' );
define( 'AEC_PLUGIN_HOMEPAGE', 'http://eranmiller.com/plugins/aec/' );

// contributor widget code
require_once AEC_PLUGIN_PATH . 'inc/aec_contributor_list.php';

if ( !class_exists( 'ajax_event_calendar' ) ) {
	class ajax_event_calendar{

		// PHP 4 constructor
		function ajax_event_calendar() {
			$this->__construct();
		}

		// PHP 5 constructor
		function __construct() {
			update_option( AEC_DOMAIN . 'version', AEC_PLUGIN_VERSION );
			add_action( 'admin_menu', array( $this, 'set_admin_menu' ) );
			add_action( 'delete_user', array( $this, 'confirm_delete_user_events' ) );
			add_action( 'widgets_init', create_function( '', 'return register_widget( "contributor_list" );' ) );
			add_filter( 'page_template', array( $this, 'page_templates' ) );
			add_filter( 'manage_users_columns', array( $this, 'add_events_column' ) );
			add_filter( 'manage_users_custom_column', array( $this, 'manage_events_column'), 10, 3 );
			add_filter( 'delete_user', array( $this, 'delete_user_confirmation' ) );
		}

		// Calendar page override
		function page_templates( $page_template )
		{
			if ( is_page( 'calendar' ) )
				$page_template = dirname( __FILE__ ) . '/inc/page-calendar.php';

			return $page_template;
		}

		function install() {
			/*
			if ( version_compare( get_bloginfo('version'), '0.3', '<' ) ) {
				deactivate_plugins( plugin_basename( __FILE__ ) );
				wp_die( 'This plugin requires WordPress version 4.0 or higher. ');
			}
			*/
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			global $wpdb;

			if ( $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpdb->prefix . AEC_EVENT_TABLE . '"' ) != $wpdb->prefix . AEC_EVENT_TABLE ) {
				$sql = 'CREATE TABLE ' . $wpdb->prefix . AEC_EVENT_TABLE . ' (
							id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT
							, user_id INT(10) UNSIGNED NOT NULL
							, title VARCHAR(100) NOT NULL
							, start DATETIME NOT NULL
							, end DATETIME NOT NULL
							, allDay TINYINT(1) UNSIGNED DEFAULT 0
							, category_id TINYINT(4) UNSIGNED NOT NULL
							, description VARCHAR(1000)
							, link VARCHAR(100)
							, venue VARCHAR(100) NOT NULL
							, address VARCHAR(100)
							, city VARCHAR(50) NOT NULL
							, state CHAR(2) NOT NULL
							, zip MEDIUMINT(5) UNSIGNED NOT NULL
							, contact VARCHAR(50) NOT NULL
							, contact_info VARCHAR(50) NOT NULL
							, access TINYINT(1) UNSIGNED DEFAULT 0
							, rsvp TINYINT(1) UNSIGNED DEFAULT 0
						);
						## SAMPLE ROWS
						INSERT INTO ' . $wpdb->prefix . AEC_EVENT_TABLE . ' (id, user_id, title, start, end, allDay, category_id, description, link, venue, address, city, state, zip, contact, contact_info, access, rsvp)
						VALUES (NULL, 0, "Ajax Event Calendar (v' . AEC_PLUGIN_VERSION . ') Installed!", "' . date('Y-m-d') . '", "' . date('Y-m-d') . '", 1, 1, "' . AEC_PLUGIN_NAME . ' was installed.", "' . AEC_PLUGIN_HOMEPAGE . '", "Event Venue", "Event Street Address or Neighborhood", "Event City", "IL", 60601, "Contact Name", "Contact Email or Phone", 0, 0);';
				dbDelta( $sql );
			}

			if ( $wpdb->get_var( 'SHOW TABLES LIKE "' . $wpdb->prefix . AEC_CATEGORY_TABLE . '"' ) != $wpdb->prefix . AEC_CATEGORY_TABLE ) {
				$sql = 'CREATE TABLE ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' (
							id TINYINT(4) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT
							, category VARCHAR(25) NOT NULL
							, bgcolor CHAR(6) NOT NULL
							, fgcolor CHAR(6) NOT NULL
						);
						## SAMPLE ROWS
						INSERT INTO ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' ( id, category, bgcolor, fgcolor )
						VALUES 	( NULL, "Event", "517ed6", "FFFFFF"),
								( NULL, "Deadline", "e3686c", "FFFFFF"),
								( NULL, "Volunteer", "8fc9b0", "FFFFFF");';
				dbDelta( $sql );
			}

			// new roles for plugin
			add_role('calendar_contributor', 'Calendar Contributor', array(
				'read' => true
				, AEC_DOMAIN . 'add_events' => true
			));
			add_role('blog_calendar_contributor', 'Blog + Calendar Contributor', array(
				'read' => true
				, 'edit_posts' => true
				, 'delete_posts' => true
				, AEC_DOMAIN . 'add_events' => true
			));

			// add capabilities for administrators
			$role = get_role( 'administrator' );
			$role->add_cap( AEC_DOMAIN . 'add_events' );
			$role->add_cap( AEC_DOMAIN . 'run_reports' );
		}

		function set_admin_menu(){
			if ( function_exists( 'add_options_page' ) )
				$page = add_menu_page( 'Ajax Event Calendar', 'Calendar', AEC_DOMAIN . 'add_events', basename( __FILE__ ), array( $this, 'admin_page' ), AEC_PLUGIN_URL . 'css/images/calendar.png', 30 );
				// only load scripts and styles on plugin page
				add_action( "admin_print_scripts-$page", array( $this, 'load_calendar_js' ) );
				add_action( "admin_print_styles-$page", array( $this, 'load_calendar_css' ) );

				// contextual help override
				$help_text = '<h4>Ajax Event Calendar help is available <a href="' . AEC_PLUGIN_HOMEPAGE . '">here</a></h4>';
				add_contextual_help( $page, $help_text );

				// sub menu pages
				$sub = add_submenu_page( basename( __FILE__ ), 'Categories', 'Categories', AEC_DOMAIN . 'run_reports', 'event_categories', array( $this, 'category_page' ));
				// only load scripts and styles on sub page
				add_action( "admin_print_scripts-$sub", array( $this, 'load_category_js' ) );
				add_action( "admin_print_styles-$sub", array( $this, 'load_category_css' ) );

				add_submenu_page( basename( __FILE__ ), 'Activity Report', 'Activity Report', AEC_DOMAIN . 'run_reports', 'activity_report', array( $this, 'run_reports' ));
		}

		function admin_page() {
			if ( !current_user_can( AEC_DOMAIN . 'add_events' ) )
				wp_die( __( 'You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME ) );
			include_once( AEC_PLUGIN_PATH . 'inc/admin-calendar.php' );
		}

		function category_page() {
			if ( !current_user_can( AEC_DOMAIN . 'run_reports' ) )
				wp_die( __( 'You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME ) );
			include_once( AEC_PLUGIN_PATH . 'inc/admin-category.php' );
		}

		function run_reports() {
			if ( !current_user_can( AEC_DOMAIN . 'run_reports' ) )
				wp_die( __( 'You do not have sufficient permissions to access this page.', AEC_PLUGIN_NAME ) );
			echo "<h3>Current Month Activity</h3>";
			$rows = $this->report_monthly_activity();
			if ( count( $rows ) ) {
				foreach ( $rows as $row ) {
					echo '<strong>' . $row->cnt . '</strong>: ' . $row->category . $this->plural($row->cnt) . '<br>';
				}
			} else {
				echo 'No events this month';
			}
		}

		function load_calendar_js() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jq_ui', AEC_PLUGIN_URL . 'js/jquery-ui-1.8.11.custom.min.js', array( 'jquery' ), null, false );
			wp_enqueue_script( 'timePicker', AEC_PLUGIN_URL . 'js/jquery.timePicker.min.js', array( 'jquery' ), null, false );
			wp_enqueue_script( 'fullcalendar', AEC_PLUGIN_URL . 'js/fullcalendar.min.js', array( 'jquery' ), null, false );
			wp_enqueue_script( 'growl', AEC_PLUGIN_URL . 'js/jquery.jgrowl.min.js', array( 'jquery' ), null, false );
			wp_enqueue_script( 'simplemodal', AEC_PLUGIN_URL . 'js/jquery.simplemodal.1.4.1.min.js', array( 'jquery' ), null, false );
		}

		function load_category_js() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jq_ui', AEC_PLUGIN_URL . 'js/jquery-ui-1.8.11.custom.min.js', array( 'jquery' ), null, false );
			wp_enqueue_script( 'growl', AEC_PLUGIN_URL . 'js/jquery.jgrowl.min.js', array( 'jquery' ), null, false );
			wp_enqueue_script( 'color_picker', AEC_PLUGIN_URL . 'js/jquery.miniColors.min.js', array( 'jquery' ), null, false );
			wp_enqueue_script( 'inline_edit', AEC_PLUGIN_URL . 'js/jquery.jeditable.min.js', array( 'jquery' ), null, false );
		}

		function load_calendar_css() {
			wp_enqueue_style( 'jq_ui_css', AEC_PLUGIN_URL . 'css/jquery-ui-1.8.11.custom.css' );
			wp_enqueue_style( 'custom', AEC_PLUGIN_URL . 'css/custom.css' );
			wp_enqueue_style( 'categories', AEC_PLUGIN_URL . 'css/cat_colors.css' );
		}

		function load_category_css() {
			wp_enqueue_style( 'custom', AEC_PLUGIN_URL . 'css/custom.css' );
		}

		function date_display_format( $date ) {
			return date( 'm/d/Y g:i A', strtotime( $date ) );
		}

		function date_database_format( $date ) {
			return date( 'Y-m-d H:i:s', strtotime( $date ) );
		}

		function parse_input( $input ) {
			$clean = array();
			if ( !is_array( $input ) ) {
				// convert serialized form into an array
				parse_str( $input, $output_array );
				$input = $output_array;
			}
			foreach ( $input as $key => $value ) {
				// trim whitespace from input
				$clean[$key] = $value;
			}
			return $clean;
		}

		function cleanse_event_input( $input ) {
			$clean = $this->parse_input( $input );
			// if null values, set form checkboxes to false
			$cboxs = array( 'allDay', 'access', 'rsvp' );
			foreach ( $cboxs as $cbox ) {
				if ( !array_key_exists( $cbox, $clean ) ) {
					$clean[$cbox] = 0;
				}
			}

			// if event is allDay, set time values to 00:00:00
			if ( $clean['allDay'] ) {
				$clean['start_time'] = '00:00:00';
				$clean['end_time'] = '00:00:00';
			}
			return $clean;
		}

		function cleanse_category_input( $input ) {
			$clean = $this->parse_input( $input );
			$clean['bgcolor'] = str_replace( '#', '', $clean['bgcolor'] );  // strip # from color for storage
			$clean['fgcolor'] = str_replace( '#', '', $clean['fgcolor'] );
			return $clean;
		}

		function output_event( $input ) {
			$input['allDay'] = ( $input['allDay'] ) ? true : false;
			$output = array(
				'id'	 		=> $input['id']
				, 'title'  		=> stripslashes( $input['title'] )
				, 'start'  		=> $this->date_database_format( $input['start_date'] . ' ' . $input['start_time'] )
				, 'end'	  		=> $this->date_database_format( $input['end_date'] . ' ' . $input['end_time'] )
				, 'allDay' 		=> $input['allDay']
				, 'className'	=> 'cat' . $input['category_id']
			);
			echo json_encode( $output );
		}

		function output_events( $events ) {
			$output = array();
			foreach( $events as $event ) {
				$allDay   = ( $event->allday ) ? true : false;
				array_push( $output, array(
					'id'			=> $event->id
					, 'title'		=> stripslashes( $event->title )
					, 'start'		=> $event->start
					, 'end'			=> $event->end
					, 'allDay'		=> $allDay
					, 'className'	=> 'cat' . $event->category_id
				) );
			}
			echo json_encode( $output );
		}

		function report_monthly_activity() {
			global $wpdb;
			$result = $wpdb->get_results( 'SELECT COUNT(a.category_id) AS cnt, b.category FROM ' .
										$wpdb->prefix . AEC_EVENT_TABLE . ' AS a ' .
										'INNER JOIN ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' AS b ' .
										'ON a.category_id = b.id ' .
										'WHERE MONTH(start) = MONTH(NOW()) ' .
										'GROUP BY category_id ' .
										'ORDER BY cnt DESC;'
										);

			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			}
			return $result;
		}

		function add_event( $input ) {
			$input = $this->cleanse_event_input( $input );

			global $wpdb;
			$result = $wpdb->insert( $wpdb->prefix . AEC_EVENT_TABLE
									, array(  'user_id' 		=> $input['user_id']
											, 'title'	 		=> $input['title']
											, 'start'	 		=> $this->date_database_format( $input['start_date'] . ' ' . $input['start_time'] )
											, 'end'	 			=> $this->date_database_format( $input['end_date'] . ' ' . $input['end_time'] )
											, 'allDay'	 		=> $input['allDay']
											, 'category_id'		=> $input['category_id']
											, 'description'		=> $input['description']
											, 'link'			=> $input['link']
											, 'venue'			=> $input['venue']
											, 'address'			=> $input['address']
											, 'city'			=> $input['city']
											, 'state'			=> $input['state']
											, 'zip'				=> $input['zip']
											, 'contact'			=> $input['contact']
											, 'contact_info'	=> $input['contact_info']
											, 'access'			=> $input['access']
											, 'rsvp'			=> $input['rsvp']
											)
									, array(  '%d' //user_id
											, '%s' //title
											, '%s' //start
											, '%s' //end
											, '%d' //allDay
											, '%d' //category_id
											, '%s' //description
											, '%s' //link
											, '%s' //venue
											, '%s' //address
											, '%s' //city
											, '%s' //state
											, '%s' //zip
											, '%s' //contact
											, '%s' //contact_info
											, '%d' //access
											, '%d' //rsvp
											)
								);
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			} else {
				$input['id'] = $wpdb->insert_id;	// ID of newly created row
				$this->output_event( $input );
			}
		}

		function move_event( $input ) {
			global $wpdb;
			$result = $wpdb->update( $wpdb->prefix . AEC_EVENT_TABLE
									, array(  'start'	=> $input['start']
											, 'end'	 	=> $input['end']
											, 'allDay'	=> $input['allDay']
									)
									, array(  'id' 		=> $input['id'] )
									, array(  '%s'		//start
											, '%s'		//end
											, '%d'		//allDay
											)
									, array ( '%d' ) 	//id
								);
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			}
			echo $result;
		}

		function update_event( $input ) {
			$input = $this->cleanse_event_input( $input );

			global $wpdb;
			$result = $wpdb->update( $wpdb->prefix . AEC_EVENT_TABLE
									, array(  'user_id' 		=> $input['user_id']
											, 'title'	 		=> $input['title']
											, 'start'	 		=> $this->date_database_format( $input['start_date'] . ' ' . $input['start_time'] )
											, 'end'	 			=> $this->date_database_format( $input['end_date'] . ' ' . $input['end_time'] )
											, 'allDay'	 		=> $input['allDay']
											, 'category_id'	 	=> $input['category_id']
											, 'description'		=> $input['description']
											, 'link'			=> $input['link']
											, 'venue'			=> $input['venue']
											, 'address'			=> $input['address']
											, 'city'			=> $input['city']
											, 'state'			=> $input['state']
											, 'zip'				=> $input['zip']
											, 'contact'			=> $input['contact']
											, 'contact_info'	=> $input['contact_info']
											, 'access'			=> $input['access']
											, 'rsvp'			=> $input['rsvp']
											)
									, array( 'id' => $input['id'] )
									, array(  '%d' //user_id
											, '%s' //title
											, '%s' //start
											, '%s' //end
											, '%d' //allDay
											, '%d' //category_id
											, '%s' //description
											, '%s' //link
											, '%s' //venue
											, '%s' //address
											, '%s' //city
											, '%s' //state
											, '%s' //zip
											, '%s' //contact
											, '%s' //contact_info
											, '%d' //access
											, '%d' //rsvp
											)
									, array ( '%d' ) //id
								);
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			} else {
				$this->output_event( $input );
			}
		}

		function delete_event( $id ) {
			global $wpdb;
			$result = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE id = %d;', $id ) );
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			}
			echo $result;
		}

		function process_event( $_POST ) {
			$action = $_POST['action'];
			switch( $action ) {
				case 'add':
				$this->add_event( $_POST['event'] );
				break;
				case 'move':
				$this->move_event( $_POST );
				break;
				case 'update':
				$this->update_event( $_POST['event'] );
				break;
				case 'delete':
				$this->delete_event( $_POST['id'] );
				break;
			}
		}

		function init_form( $start, $end, $allDay, $user_id ) {
			$event->id = '';
			$event->user_id = $user_id;								// WP data
			$event->title = '';
			$event->start = $this->date_display_format( $start ); 	// FC data
			$event->end = $this->date_display_format( $end ); 		// FC data
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

		function get_event( $id ) {
			global $wpdb;
			$result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE id = %d ORDER BY start;', $id ) );
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			}
			// Format fields for user display
			$result->start = $this->date_display_format( $result->start );
			$result->end = $this->date_display_format( $result->end );
			$result->title = stripslashes( $result->title );
			$result->description = stripslashes( $result->description );
			$result->link = stripslashes( $result->link );
			$result->venue = stripslashes( $result->venue );
			$result->address = stripslashes( $result->address );
			$result->city = stripslashes( $result->city );
			$result->contact = stripslashes( $result->contact );
			$result->contact_info = stripslashes( $result->contact_info );
			return $result;
		}

		function add_events_column( $columns ) {
				$columns['calendar_events'] = 'Events';
			return $columns;
		}

		function manage_events_column( $empty='', $column_name, $user_id ) {
			if ( $column_name == 'calendar_events' ) {
				$event_count = $this->get_event_count( $user_id );
				return $event_count;
			}
		}
		function delete_user_confirmation( ) {
			// TODO: add filter to delete confirmation page displaing the number of events to be deleted.
		}
		
		function confirm_delete_user_events( $user_id ) {
			$event_count = $this->get_event_count( $user_id );
			$this->delete_user_events( $user_id );
		}

		function get_event_count( $user_id ) {
			global $wpdb;
			$result = $wpdb->get_var( 'SELECT count(id)
											FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
											WHERE user_id = ' . $user_id . ';'
										);
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
				return false;
			} 
			return $result;
		}
		
		function delete_user_events( $user_id ) {
			global $wpdb;
			$result = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE user_id = %d;', $user_id ) );
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
				return false;
			}
			return $result;
		}

		function get_events( $user_id, $start, $end ) {
			global $wpdb;
			$anduser = ( $user_id ) ? ' AND user_id = ' . $user_id : '';
			$start = date( 'Y-m-d', $start );
			$end = date( 'Y-m-d', $end );
			$results = $wpdb->get_results( 'SELECT
											id
											, title
											, start
											, end
											, allday
											, category_id
											FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . '
											WHERE ( ( start >= "' . $start . '"
											AND start < "' . $end . '" )
											OR ( end >= "' . $start . '"
											AND end < "' . $end . '" )
											OR ( start < "' . $start . '"
											AND end > "' . $end . '" ) )' .
											$anduser .
											' ORDER BY start;'
										);
			if ( $results === false ) {
				$this->log( $wpdb->print_error() );
			} else {
				$this->output_events( $results );
			}
		}
		
		function add_category( $input ) {
			$input = $this->cleanse_category_input( $input );

			global $wpdb;
			$result = $wpdb->insert( $wpdb->prefix . AEC_CATEGORY_TABLE
									, array(  'category'	 	=> $input['category']
											, 'bgcolor'	 		=> $input['bgcolor']
											, 'fgcolor'	 		=> $input['fgcolor']
											)
									, array(  '%s' //category
											, '%s' //bgcolor
											, '%s' //fgcolor
											)
								);
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			} else {
				$id = $wpdb->insert_id;	// ID of newly created row
				$output = array(
					'id'	 		=> $id
					, 'category'  	=> $input['category']
					, 'bgcolor'		=> $input['bgcolor']
					, 'fgcolor'		=> $input['fgcolor']
				);
				echo json_encode( $output );
			}
			$this->generate_css();
		}

		function update_category( $input ) {
			$input = $this->cleanse_category_input( $input );

			global $wpdb;
			$result = $wpdb->update( $wpdb->prefix . AEC_CATEGORY_TABLE
									, array(  'category'	 	=> $input['category']
											, 'bgcolor'	 		=> $input['bgcolor']
											, 'fgcolor'	 		=> $input['fgcolor']
											)
									, array( 'id' => $input['id'] )
									, array(  '%s' //category
											, '%s' //bgcolor
											, '%s' //fgcolor
											)
									, array ( '%d' ) //id
								);
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			} else {
				$output = array(
					'category'  	=> $input['category']
					, 'bgcolor'		=> $input['bgcolor']
					, 'fgcolor'		=> $input['fgcolor']
				);
				echo json_encode( $output );
			}
			$this->generate_css();
		}

		function change_category( $id ) {
			global $wpdb;
			$result = $wpdb->get_results( $wpdb->prepare( 'UPDATE '. $wpdb->prefix . AEC_EVENT_TABLE . ' SET category_id=1 WHERE category_id= %d;', $id ) );
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			}
			return $this->delete_category( $id );
		}

		function delete_category( $id ) {
			global $wpdb;
			$used = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) as count FROM ' . $wpdb->prefix . AEC_EVENT_TABLE . ' WHERE category_id = %d;', $id ) );
			if ( $used ) {
				echo 'false';
			} else {
				$result = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' WHERE id = %d;', $id ) );
				if ( $result === false ) {
					$this->log( $wpdb->print_error() );
				}
				echo $result;
			}
		}

		function get_categories() {
			global $wpdb;
			$result = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . AEC_CATEGORY_TABLE . ' ORDER BY id;' );
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			}
			return $result;
			$this->generate_css();
		}

		function remove_table( $table_name ) {
			global $wpdb;
			$result = $wpdb->query( $wpdb->prepare( 'DROP TABLE %s', $table_name ) );
			if ( $result === false ) {
				$this->log( $wpdb->print_error() );
			}
			echo $result;
		}
		
		function generate_css() {
			$categories = $this->get_categories();
			$out = '';
			foreach ( $categories as $category ) {
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
			$fh = fopen( $cssFile, 'w+' ) or die( 'cannot open file' );
			fwrite( $fh, $out );
			fclose( $fh );
		}

		function log( $message ) {
			if( WP_DEBUG === true ) {
				if( is_array( $message ) || is_object( $message ) ){
					error_log( print_r( $message, true ) );
				} else {
					error_log( $message );
				}
			}
			return;
		}

		function plural( $value ) {
			return ( $value == 1 ) ? '' : 's';
		}

		//placeholder
		function deactivate() {}
		
	}
}

register_activation_hook( __FILE__, array( 'ajax_event_calendar', 'install' ) );
register_deactivation_hook( __FILE__, array( 'ajax_event_calendar', 'deactivate' ) );

if ( class_exists( 'ajax_event_calendar' ) ) {
	$aec = new ajax_event_calendar();
}
?>