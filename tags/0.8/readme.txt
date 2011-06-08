=== Ajax Event Calendar ===
Contributors: eranmiller
Tags: multi-user, calendar, event, ajax
Requires at least: 3.1
Tested up to: 3.1.3
Stable tag: 0.8

A Google Calendar-like experience to add, edit and delete events in a common calendar.

== Description ==

A Google Calendar-like interface that allows registered users (with required access) to add, edit and delete events in a common calendar viewable by all blog visitors.

* Administrators can edit and delete all events, others can only edit and delete events they create themselves
* Dynamic (based on event categories) and near-instant event filtering
* Dynamically generated calendar contributor list (sidebar widget)

= Administrator only =

* Current month calendar activity report
* Event category management interface
* Calendar event count column to Users table

= Options =

* Edit, delete or add event category types
* Event creation is restricted to this time period: from the following 30-minute interval to one year into the future
* Displays the WordPress Login/Register links (Admin menu) on the front-end Calendar
* Front-end calendar supports password protection
* Front-end calendar adjusts width, with and without sidebar
* Display a contributors list in sidebar by employing "Calendar Contributors" in Widgets

== Installation ==

1. The easiest way to install this plugin is via the automatic installer within WordPress administration
1. To create the front-end (non-administrative) view of the calendar: create a new page with any name, the **page slug must be 'calendar'**

== Frequently Asked Questions ==

= What does this plugin remove on deletion? =
The event and category databases, custom calendar roles and capabilities, and plugin options are permanently removed.

= How does the calendar filter work? =
The filter is displays only when more than a one kind of event category has been created.

= What happens to user events when they are deleted? =
All events associated with a deleted user are permanently deleted.

= What happens when a category is deleted? =
All events associated with the deleted category are re-assigned to the primary category type (Event).

= How are roles and capabilities affected by this plugin? =
Two new roles "Calendar Contributor" and "Blog+Calendar Contributor" are added, both containing a new capability "aec_add_events".  A new capability "aec_run_reports" is added to Administrator accounts only.

== Screenshots ==

* Coming Soon

== Other ==

= Credits =

* Google Calendar styled interface (FullCalendar)
* Growl styled feedback (jGrowl)
* OSX styled modal forms (simpleModal)
* Category color selection (miniColors)

== Changelog ==
= 0.8 =
* Fixed css conflicts with themes
* Added sidebar toggle option
* Added password protection support

= 0.7.6 =
* Fixed toggle admin menu option

= 0.7.5 =
* Fixed css, filters and modals

= 0.7.4 =
* Fixed activity report missing file

= 0.7.3 =
* Fixed update issues

= 0.7.2 =
* Fixed truncated plugin description

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
= 0.8 =
* CSS conflicts, sidebar toggle option, password protection support

= 0.7.6 =
* Fixed toggle admin menu option

= 0.7.5 =
* Fixed css, filters and modals

= 0.7.4 =
* Fixed activity report missing file

= 0.7.3 =
* Fixed update issues

= 0.7.2 =
* Fixed truncated plugin description

= 0.7.1
* Fixed widget file path

= 0.7 =
* Fixed CSS collision and added plugin options

= 0.6.1 =
* Updated plugin link

= 0.6 =
* First official plugin release