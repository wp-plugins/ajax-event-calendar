=== Ajax Event Calendar ===
Contributors: eranmiller
Tags: multi-user, categories, calendar, event, ajax, filter, upcoming, widget
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: 0.9.8.5

A fully localized Google/OSX hybrid interface for multiple users to manage events in a community calendar.

== Description ==

A fully localized Google/OSX hybrid interface which enables users (registered with the necessary access) to add, edit and delete events in a community calendar viewable by all blog visitors.  The calendar can be added to a page or a post using the "[calendar]" shortcode.

If you enjoy this plugin please [rate it and confirm compatibility](http://wordpress.org/extend/plugins/ajax-event-calendar/)

=  Features =

* Dynamic category-based near-instant event filtering
* Dynamically generated calendar contributor list (sidebar widget)
* Upcoming events list, that displays details of an event when clicked (sidebar widget)
* Users assigned the Calendar Contributor role can edit and delete events they create
* Auto-generated Google Maps link, based on event address fields
* Date and time formatting, via integrated blog settings
* Multi-language Support (available in 10 languages other than English)

= Administator Options =

* Edit and delete any event
* Create, edit and delete event categories
* View an activity report of the current month's event creation
* View the total number of events created ("event counts") by user, in the blog Users menu
* Modify calendar date and time formats via blog settings
* Modify which event fields to display and require
* Prevent users from adding events that have transpired
* Password protect the front-end calendar
* Toggle Add Events link on the front-end Calendar (links to the administrative calendar via login)
* Toggle the display of weekends on the calendars
* Display a contributors list using the "Calendar Contributors" sidebar widget
* Display a list of upcoming events using the "Upcoming Events" sidebar widget

= 10 Languages and counting... =
* Danish
* Dutch
* French
* German
* Lithuanian
* Polish
* Portuguese
* Russian
* Spanish
* Turkish
* Note: not all translations have been completed.
* Don't see your language, found a typo in a translation or want to help complete a translation? Post it on this forum! Send PO translation files to plugins@eranmiller.com

== Installation ==

1. The easiest method of installation is via the integrated WordPress plugin installer
1. To create the front-end calendar: create a page or a post with any desired title.
1. Add [calendar] shortcode to the body of the page
1. Select any desired publish options
1. Select any desired template options
1. Save the page or post

== Frequently Asked Questions ==

= I've installed the plugin but the Calendar option does not appear in the Administrative menu (under Comments) =
The issue is likely caused when more than [two menu items attempt to inhabit the same menu position](http://core.trac.wordpress.org/ticket/15595).  To resolve this problem:
Edit the ajax-event-calendar.php file. -- Search for the text "add_menu_page('Ajax Event Calendar'". -- Increment the number at the end of the line by one (initially set to 30). -- Save the file.  -- Refresh the browser window. -- Repeat until the Calendar option appears in the admin menu.

= I upgraded the plugin to version 0.9.6 and the front-end calendar dissappeared =
As of version 0.9.6, to accommodate the widest range of display options, the front-end calendar display was altered and is now triggered by the inclusion of the "[calendar]" shortcode in either a page or a post.  See the [installation tab](http://wordpress.org/extend/plugins/ajax-event-calendar/installation/) for details.

= I upgraded the plugin and the calendar no longer appears =
Go to the Calendar Options page in the Settings menu and if you don't have access to the page or none of the checkboxes are selected, select **Reset Settings** and click **Save Settings**.  If the calendar still does not appear, please post the issue in the [Support Forum](http://wordpress.org/tags/ajax-event-calendar?forum_id=10 "forum") be sure to include your URL in the description.

= How do I manage (add, edit, delete) events? = 
As with Google Calendar: to add an event, in the administrative calendar page click on a date (or range of dates) in the month view, or click on a half-hour (or range of hours) timeslot in the week view.  Only users assigned the aec_add_events capability can edit and delete events they create.  Users assigned the aec_manage_events capability can edit and delete all events.

= How do I manage (add, edit, delete) categories? = 
To add a category, simply enter the desired category name in the input field, select a background color via the colorpicker or enter the hex value in the field provided, then click Add.  Only users assigned the aec_manage_calendar capability can manage categories.

= How does the calendar filter work? =
The filter appears on the front-end Calendar when more than one event category has been created.

= What happens when the plugin is deleted? =
The event and category databases, custom roles, plugin capabilities, plugin options and widget settings are **permanently removed**.

= What happens to user events when they are deleted? =
All events associated with a deleted user are **permanently deleted**.

= What happens to events associated with a deleted category? =
All events associated with a deleted category are re-assigned to the primary category type.

= What capabilities does this plugin include and how can I assign them to a role? =
The plugin comes with a custom role called Calendar Contributor (which allows assigned users to add, edit and delete their own calendar events). For more options install the [Capability Manager](http://wordpress.org/extend/plugins/capsman/) plugin and the following plugin capabilities to roles as desired:
**aec_add_events**: allows a user to add, edit and delete their own calendar events
**aec_manage_events**: allows a user to add, edit and delete all calendar events
**aec_manage_calendar**: allows a user to modify calendar settings, control which event fields to display and require, modify categories, and view the activity report

== Screenshots ==

1. Front-end Calendar Setup
2. Plugin Options, in the Settings Menu
3. New User Role
4. New Event field in Users table
5. Upcoming Events Widget Options
6. Back-end Calendar Administration
7. Category Administration
8. Activity Report
9. Event Detail Input Form
10. Growl Notifications
11. Plugin uses built-in WordPress Date/Time localization and day of week setting
12. Front-end Calendar View with Upcoming Events and Calendar Contributors Widgets
13. Front-end Events Detail View

== Other Notes ==

**Known Compatibility Issues**
1. WP Minify plugin - to ensure proper display of the calendar detail view - disable the HTML minification option.
1. SEO Image Galleries - the SEO javascript implementation causes this plugin's javascript to cease functioning.

**Hat Tip to these fine plugins which were instrumental in the creation of this plugin:**
* Google Calendar interface experience (FullCalendar)
* Growl feedback (jGrowl)
* OSX modal forms (simpleModal)
* Category color selection (miniColors)

== Changelog ==
= 0.9.8.5 =
* calendar weekday (tue) short name localization fix
* plugin options page save settings for manage_calendar capability fix
* automatically adjusts modal top when WordPress admin bar is visible (contributed by Carl W.)
* event duration display fix
* minicalendar localization, noweekends fix
* excised orphaned options
* improved instructional text on the calendar settings page
* added hex input field and more instructional text to category management
* fixed front-end calendar for themes that display multiple pages simultaneously
* revised javascript enqueuing and rendering, fixes theme/plugin conflicts
* upcoming widget addition of user input title, undefined timezone fix, and ongoing event fix
* shortcode respectful of position within post text fix
* updated uninstall script with new capabilities and roles
* event detail form description validation fix
* added russian localization (reddylabeu) - spasiba!
* added danish localization (kfe1970) - tak!
* added polish localization (szymon) - dziekuje!

= 0.9.8.1 =
* replaced php 5.3.x dependent DateTime class with a modified strtotime to accommodate d/m/Y format
* revised admin menu wording

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
* changed front-end calendar implementation from custom template to shortcode, to accommodate wider range of themes
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
= 0.9.8.5
* fixes to theme/javascript conflicts, localization and much more!

= 0.9.8.2 =
* minor fixes to upgrade issues

= 0.9.8.1 =
* php 5.3.x dependency fix

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