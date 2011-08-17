=== Ajax Event Calendar ===
Contributors: eranmiller
Tags: multi-user, calendar, event, ajax, filter
Requires at least: 3.1
Tested up to: 3.1.3
Stable tag: 0.9.7

Provides a hybrid Google/OSX interface for multiple users to manage events in a community calendar.

== Description ==

Provides a hybrid Google/OSX interface which enables registered users (with required access) to add, edit and delete events in a community calendar, viewable by all blog visitors.

* **ATTENTION!** The template override has been replaced with a new shortcode to accommodate a wider range of themes, see installation instructions for details
* Users can only edit and delete events they create
* Dynamic category-based near-instant event filtering
* Dynamically generated calendar contributor list (sidebar widget)
* Display upcoming events in a list (sidebar widget)
* Auto-generated Google Maps link, based on event address fields
* Multi-language support - please email translations to plugins@eranmiller.com so I can package them in future releases of the plugin

= Administrative =

* Administrators can edit and delete all events
* Current calendar month activity report
* Event category management
* Added column "event counts" in the Users table, for tracking event creation by user
* Control which event detail fields to display and require

= Options =

* Add, edit or delete event category types
* Settings Toggle: Limit event creation to a pre-defined window of time: between the next 30-minute interval and one year
* Settings Toggle: Display of WordPress Login/Register links (Admin menu) on the front-end Calendar, links directly to event administration
* Front-end calendar can be password protected
* Display a contributors list in sidebar by using "Calendar Contributors" Widget
* Display a upcoming events in sidebar by using "Upcoming Events" Widget

== Installation ==

1. The easiest way to install this plugin is through the integrated WordPress plugin installer
1. To create the front-end (non-administrative) calendar view: create a new (or use an existing) page and type **[calendar]** in the page content, save and you're done!

== Frequently Asked Questions ==

= How do I manage (add, edit, delete) events? = 
As with Google Calendars, to add an event, simply click on a date in the administrative calendar view.  Users can only edit and delete events they create.  Administrators can edit and delete all events.

= The calendar won't let me create events prior to the current date =
By default, event creation is restricted to "Enforce event creation between 30 minutes and one year from the current time".  To remove this restriction, uncheck the "Enforce event creation..." checkbox in the Settings menu, under the Calendar sub-menu.

= I upgraded the plugin and the calendar no longer appears =
Go to the Calendar Options page in the Settings menu and if none of the checkboxes are selected, select **Reset Settings** and click **Save Settings**.  If the calendar still does not appear, please post the issue in the [Support Forum](http://wordpress.org/tags/ajax-event-calendar?forum_id=10 "forum") and include your URL for troubleshooting purposes.

= I upgraded the plugin to 0.9.6 and the front-end calendar no longer appears =
To accommodate a wider range of themes, the front-end calendar installation, as of this version, depends on a shortcode - see the [Installation tab](http://wordpress.org/extend/plugins/ajax-event-calendar/installation/) for details.

= What does this plugin remove when deleted? =
The event and category databases, custom calendar roles and capabilities, plugin options and widget settings are permanently removed.

= How does the calendar filter work? =
The filter appears only when more than one event category has been created.

= What happens to user events when they are deleted? =
All events associated with a deleted user are permanently deleted.

= What happens to events associated with a deleted category? =
All events associated with a deleted category are re-assigned to the primary category type.

= What are roles and capabilities does this plugin create? =
Two new roles "Calendar Contributor" and "Blog+Calendar Contributor" are added, both contain a new capability "aec_add_events".  A new capability "aec_run_reports" is added to Administrator accounts only.

== Screenshots ==

1. Plugin Setup
2. Plugin Options
3. Two new User Roles available
4. New Event field in the Users table
5. Two new Widgets available
6. Calendar Administration
9. Event Detail Input Form
7. Category Administration
8. Activity Report
10. Growl Notifications
11. Calendar and Widgets Localized in French

== Other Notes ==

**Compatibility**:

1. If you use the WP Minify plugin, disable the HTML minification option or the calendar detail view will not load properly.

Hat Tip to the authors of these fine jQuery plugins (used in the creation of this one):

* Google Calendar interface experience (FullCalendar)
* Growl feedback (jGrowl)
* OSX modal forms (simpleModal)
* Category color selection (miniColors)

== Changelog ==
= 0.9.7 =
* Fixed localization bugs
* Updated French localization (doc75word)
* Revised Installation and FAQ Instructions
* Added Screenshots
* Localized Upcoming Events Widget date

= 0.9.6 =
* Fixed po files to include plural translation strings
* Fixed date localization bug on calendar

= 0.9.5 =
* Added upcoming events widget
* Added redirect to event administration page from front-end calendar page login link
* Changed front-end calendar implementation from custom template to shortcode, to accomodate wider range of themes
* Auto-generated Google Maps link, based on event address fields
* A giant **Merci** to doc75word for submitting the French localization!

= 0.9.1 =
* A giant **Obrigado** to rgranzoti for submitting the initial translation in Portuguese!
* Added more localization
* Fixed default option initialization
* Further improved event detail page UI

= 0.9 =
* Improved event detail page UI
* Refactored event detail page (to address instances of event detail not loading)
* Added event detail form field options - plugin options page now located under "Settings" menu
* Added multi-language support (open call for translations)

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
* Added a PHP5 dependency check to halt installation for users running older versions

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
* Added Organization name to event viewing modal, from data provided by user's WordPress profile
* Dynamically generated calendar contributor list

= 0.2.1 =
* Added Help Link

= 0.2 =
* Event display styling
* Filter appearance

= 0.1 =
* Getting the wheels to stay on the wagon

== Upgrade Notice ==
= 0.9.7 =
* More localization fixes, widget date fix

= 0.9.6 =
* Improved and fixed localization

= 0.9.5 =
* Added upcoming events widget, wider theme support, French

= 0.9.1 =
* More localization, fixed default option initialization, UI improvements, Portuguese

= 0.9 =
* Added form field options, foundation for localization, UI improvements

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

= 0.7.1 =
* Fixed widget file path

= 0.7 =
* Fixed CSS collision and added plugin options

= 0.6.1 =
* Updated plugin link

= 0.6 =
* First official plugin release