=== Ajax Event Calendar ===
Contributors: eranmiller
Tags: multi-user, calendar, event, ajax, filter
Requires at least: 3.1
Tested up to: 3.2
Stable tag: 0.9.7.1

A fully localized Google/OSX hybrid interface for multiple users to manage events in a community calendar.

== Description ==

A fully localized Google/OSX hybrid interface which enables users (registered with the necessary access) to add, edit and delete events in a community calendar viewable by all blog visitors.

If you enjoy the use of this plugin please [rate it and confirm compatibility](http://wordpress.org/extend/plugins/ajax-event-calendar/)

=  Features =

* Dynamic category-based near-instant event filtering
* Dynamically generated calendar contributor list (sidebar widget)
* Upcoming events list, that displays details of an event when clicked (sidebar widget)
* Users assigned the Calendar Contributor role can edit and delete events they create
* Auto-generated Google Maps link, based on event address fields
* Date and time formatting, via integrated blog settings
* Multi-language Support

= Administators can... =

* Edit and delete any event
* Create, edit and delete event categories
* View an activity report of the current month's event creation
* View the total number of events created ("event counts") by user, in the blog Users menu
* Control calendar date and time format via blog settings
* Control which event fields to display and require
* Limit event creation to a pre-defined window of time: between the next 30-minute interval and one year
* Password protect the front-end calendar
* Show/Hide WordPress Login/Register links (Admin menu) on the front-end Calendar
* Show/Hide calendar weekends
* Display a contributors list in the sidebar using the "Calendar Contributors" Widget
* Display a list of upcoming events in the sidebar using the "Upcoming Events" Widget

= Languages =
* Dutch
* French
* German
* Lithuanian
* Portuguese
* Spanish
* Turkish

== Installation ==

1. The easiest method of installation is via the integrated WordPress plugin installer
1. To display the front-end calendar follow the instructions in the screenshots section

== Frequently Asked Questions ==

= I would like to add a language to the supported list =
Send translations to plugins@eranmiller.com and I will package them in future releases of the plugin

= What capabilities does this plugin include and how can I assign them to a role? =
The plugin comes with a custom role called Calendar Contributor (which allows assigned users to add, edit and delete their own calendar events). For more options install the [Capability Manager](http://wordpress.org/extend/plugins/capsman/) plugin and the following plugin capabilities to roles as desired:

aec_add_events: allows a user to add, edit and delete their own calendar events

aec_manage_events: allows a user to add, edit and delete all calendar events

aec_manage_calendar: allows a user to modify calendar settings, control which event fields to display and require, modify categories, and view the activity report

= I upgraded the plugin to 0.9.6 and the front-end calendar no longer appears =
To accommodate a wider range of themes, the front-end calendar installation, as of this version, depends on a shortcode - see the [Installation tab](http://wordpress.org/extend/plugins/ajax-event-calendar/installation/) for details.

= I upgraded the plugin and the calendar no longer appears =
Go to the Calendar Options page in the Settings menu and if none of the checkboxes are selected, select **Reset Settings** and click **Save Settings**.  If the calendar still does not appear, please post the issue in the [Support Forum](http://wordpress.org/tags/ajax-event-calendar?forum_id=10 "forum") and include your URL for troubleshooting purposes.

= How do I manage (add, edit, delete) events? = 
As with Google Calendars, to add an event, simply click on a date in the administrative calendar view.  Only users assigned the aec_add_events capability can edit and delete events they create.  Users assigned the aec_manage_events capability can edit and delete all events.

= How do I manage (add, edit, delete) categories? = 
To add a category, simply enter the desired category name in the input field, select a background color and click Add.  Only users assigned the aec_manage_calendar capability can manage categories.

= How does the calendar filter work? =
The filter appears only when more than one event category has been created.

= The calendar won't let me create events prior to the current date =
By default, event creation is restricted to "Enforce event creation between 30 minutes and one year from the current time".  To remove this restriction, uncheck the "Enforce event creation..." checkbox in the Settings menu, under the Calendar sub-menu.

= What does this plugin remove when deleted? =
The event and category databases, calendar contributor role, plugin capabilities, plugin options and widget settings are permanently removed.

= What happens to user events when they are deleted? =
All events associated with a deleted user are permanently deleted.

= What happens to events associated with a deleted category? =
All events associated with a deleted category are re-assigned to the primary category type.

== Screenshots ==

1. Frontend Calendar Setup
2. Plugin Options, in the Settings Menu
3. New User Role
4. New Event field in Users table
5. Two New Widgets: Upcoming Events and Calendar Contributors
6. Backend Calendar Administration
7. Category Administration
8. Activity Report
9. Event Detail Input Form
10. Growl Notifications
11. Plugin uses built-in WordPress Date/Time localization and day of week setting
12. Frontend Calendar View with Upcoming Events and Calendar Contributors Widgets
13. Frontend Events Detail View

== Other Notes ==

**Known Compatibility Issues**
1. WP Minify plugin - to ensure proper display of the calendar detail view - disable the HTML minification option.
1. SEO Image Galleries - the SEO javascript implementation causes this plugin's javascript to cease functioning.

**Hat Tip to the authors of these fine plugins**
* Google Calendar interface experience (FullCalendar)
* Growl feedback (jGrowl)
* OSX modal forms (simpleModal)
* Category color selection (miniColors)

== Changelog ==
= 0.9.8 =
* comprehensive refactoring of ajax elements (updated to jquery 1.6.1 for more secure ajax transactions)
* localized all javascript
* fixed google map link generator and added toggle display control
* added formatting, styling and linked event details to upcoming events widget 
* hooked calendar start of week into wordpress blog setting
* hooked calendar date format into wordpress blog setting
* hooked calendar time format into wordpress blog setting
* added spanish localization (fernando) - gracias!
* added turkish localization (darth crow) - sag olun!
* added lithuanian localization (juliuslt) - aciu!
* updated portuguese localization (ricardorodh) - obrigado!
* added dutch localization (Maikel) - bedankt!

= 0.9.7.1 =
* event display fix
* updated french localization (doc75word)

= 0.9.7 =
* fixed localization bugs
* revised installation and faq instructions

= 0.9.6 =
* fixed po files to include plural translation strings
* fixed date localization bug on calendar

= 0.9.5 =
* added upcoming events widget
* added redirect to event administration page from front-end calendar page login link
* changed front-end calendar implementation from custom template to shortcode, to accomodate wider range of themes
* auto-generated google maps link, based on event address fields
* added french localization (doc75word) - merci!

= 0.9.1 =
* added portuguese localization (rgranzoti) - obrigado!
* added more localization
* fixed default option initialization
* further improved event detail page ui

= 0.9 =
* improved event detail page ui
* refactored event detail page (to address instances of event detail not loading)
* added event detail form field options - plugin options page now located under "settings" menu
* added multi-language support (open call for translations)

= 0.8 =
* fixed css conflicts with themes
* added sidebar toggle option
* added password protection support

= 0.7.6 =
* fixed toggle admin menu option

= 0.7.5 =
* fixed css, filters and modals

= 0.7.4 =
* fixed activity report missing file

= 0.7.3 =
* fixed update issues

= 0.7.2 =
* fixed truncated plugin description

= 0.7.1 =
* fixed widget file path

= 0.7 =
* added options for event limits and admin menu toggle
* modified css to address reported style collisions
* added a php5 dependency check to halt installation for users running older versions

= 0.6.1 = 
* updated plugin link

= 0.6 =
* refined event input form
* roles and capabilities are removed on plugin deletion
* added events column to administrative users table
* all calendar events associated with a deleted user are removed

= 0.5.1 =
* admins can edit past events
* admins can see the user name and organization of event creator in edit mode

= 0.5 =
* category management interface
* refined event editing validation
* calendar contributor widget

= 0.4 =
* current month activity report

= 0.3.1 =
* fixed time validation
* fixed jgrowl css hide all notifications
* minified css
* fixed query to retrieve events that span longer than a single month

= 0.3 =
* streamlined event input form html and css
* fixed calculation for all day event durations
* added validation for event duration input
* added organization name to event viewing modal, from data provided by user's wordpress profile
* dynamically generated calendar contributor list

= 0.2.1 =
* added help link

= 0.2 =
* event display styling
* filter appearance

= 0.1 =
* getting the wheels to stay on the wagon

== Upgrade Notice ==
= 0.9.8 =
* comprehensive refactoring of ajax actions and localization improvements

= 0.9.7.1 =
* event display fix

= 0.9.7 =
* more localization fixes, widget date fix

= 0.9.6 =
* improved and fixed localization

= 0.9.5 =
* added upcoming events widget, wider theme support, french

= 0.9.1 =
* more localization, fixed default option initialization, ui improvements, portuguese

= 0.9 =
* added form field options, foundation for localization, ui improvements

= 0.8 =
* css conflicts, sidebar toggle option, password protection support

= 0.7.6 =
* fixed toggle admin menu option

= 0.7.5 =
* fixed css, filters and modals

= 0.7.4 =
* fixed activity report missing file

= 0.7.3 =
* fixed update issues

= 0.7.2 =
* fixed truncated plugin description

= 0.7.1 =
* fixed widget file path

= 0.7 =
* fixed css collision and added plugin options

= 0.6.1 =
* updated plugin link

= 0.6 =
* first official plugin release