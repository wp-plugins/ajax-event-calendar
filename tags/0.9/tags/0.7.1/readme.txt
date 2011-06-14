=== Ajax Event Calendar ===
Contributors: eranmiller
Tags: multi-user, calendar, event, ajax
Requires at least: 3.1
Tested up to: 3.1.3
Stable tag: 0.7.1

A Google Calendar-like interface that allows registered users (with required access) to add, edit and delete events in a common calendar viewable by blog visitors.

== Description ==

* Administrators can edit and delete all events, others can only edit and delete events they create themselves
* Dynamic (based on event categories) and near-instant event filtering
* Dynamically generated calendar contributor list (sidebar widget)
* Current month calendar activity report (Administrator only)
* Event category management interface (Administrator only)
* Calendar event count column to Users table (Administrator only)
* Limits event entry to the next 30-minute interval and one year from the present time (optional)
* Displays the WordPress Admin menu on the front-end Calendar (optional)

== Installation ==

1. Use the automatic installer from within the WordPress administration
1. Click Activate for Ajax Event Calendar
1. Create a new page for the readonly calendar page. IMPORTANT: page slug must 'calendar'
1. (optional) Display contributor list in sidebar via WordPress Widget options
1. (optional) Create and delete existing, or add additional event category types

== Frequently Asked Questions ==


== Screenshots ==


== Other ==

* Adds two new roles "Calendar Contributor" and "Blog+Calendar Contributor"
* Adds new capability "aec_add_events" (Administrator and Calendar Contributor roles)
* Adds new capability "aec_run_reports" (Administrator only)
* These roles and capabilities are removed when the plugin is deleted
* All events associated with a deleted user are perminently deleted
* Databases are perminently deleted when the plugin is deleted
* Plugin options are perminently deleted when the plugin is deleted
* The event filter is only present when more than a single event category has been created

**Credits**

* Google Calendar styled interface (FullCalendar)
* Growl styled feedback (jGrowl)
* OSX styled modal forms (simpleModal)
* Category color selection (miniColors)

== Changelog ==
= 0.7.1 =
* Fixed widget file path

= 0.7 =
* Added options for event limits and admin menu toggle
* Modified css to address reported style collisions
* Added a PHP5 dependancy check to halt installation for users running older versions

= 0.6.1 = 
* Updated plugin link

= 0.6 =
* Refined event input form
* Roles and capabilities are removed on plugin deletion
* Added Events column to administrative users table
* All calendar events associated with a deleted user are removed

= 0.5.1 =
* Admins can edit past events
* Admins can see the user name and organization of event creator in edit mode

= 0.5 =
* Category management interface
* Refined event editing validation
* Calendar contributor widget

= 0.4 =
* Current month activity report

= 0.3.1 =
* Fixed time validation
* Fixed jGrowl css hide all notifications
* Minified css
* Fixed query to retrieve events that span longer than a single month

= 0.3 =
* Streamlined event input form html and css
* Fixed calculation for all day event durations
* Added validation for event duration input
* Added Organization name to event viewing modal, from data provided by user's wordpress profile
* Dynamically generated calendar contributor list

= 0.2.1 =
* Added Help Link

= 0.2 =
* Event display styling
* Filter appearance

= 0.1 =
* Getting the wheels to stay on the wagon

== Upgrade Notice ==

= 0.7.1
* Fixed widget file path

= 0.7 =
* Fixed CSS collision and added plugin options

= 0.6.1 =
* Updated plugin link

= 0.6 =
* First official plugin release
