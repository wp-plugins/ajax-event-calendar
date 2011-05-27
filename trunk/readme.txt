=== Ajax Event Calendar ===
Contributors: eranmiller
Tags: multi-user event calendar, event calendar, calendar, event, events, ajax
Requires at least: 3.1
Tested up to: 3.1.3
Stable tag: 0.6

A multi-user ajax event calendar which enables both editing via a Google Calendar-like interface.

== Description ==

* Displays events created by registered users with the appropriate authorization
* Administrators can edit all events, calendar contributors can only edit events they create themselves
* Efficient (immediate) content filtering, no repeat database requests
* Dynamically generated calendar contributor list (sidebar widget)
* Current month calendar activity report (Administrator only)
* Event category management interface (Administrator only)


== Installation ==

1. Use the automatic installer from within the WordPress administration
2. Click Activate for Ajax Event Calendar
3. Create a new page for the readonly calendar page. IMPORTANT: name the page as you like, but the slug must be named 'calendar'
4. (optional) Display contributor list in sidebar via WordPress Widget options
5. (optional) Create existing, or add additional event category types (when more than one exists, filter becomes available on readonly calendar page)

== Notes ==

* Adds two new roles "Calendar Contributor" and "Blog+Calendar Contributor"
* Adds new capability "aec_add_events" (Administrator and Calendar Contributor roles)
* Adds new capability "aec_run_reports" (Administrator only)
* These roles and capabilities are removed when the plugin is deleted
* Adds calendar event count column to administrative Users table
* Deletes all events associated with a deleted user!

== Credits ==

* Google Calendar styled interface (FullCalendar Plugin)
* Growl styled feedback (jGrowl Plugin)
* OSX styled modal forms (simpleModal Plugin)

== Change Log ==

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