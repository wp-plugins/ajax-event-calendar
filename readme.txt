=== Ajax Event Calendar ===
Contributors: eranmiller
Donate link: http://eranmiller.com/plugins/donate/
Tags: authorize, calendar, category, event, event list, ajax, filter, widget, google maps, localized, rtl languages, shortcode, move, resize
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: 0.9.9.2

A fully localized community calendar that allows authorized users to manage events in custom categories.

== Description ==

Ajax Event Calendar WordPress Plugin is a fully localized (including RTL language support) community calendar that allows authorized users to add, edit, move, resize and delete events into custom categories.  Highly customized calendars can be added to pages, posts or text widgets using the "[calendar]" shortcode, similarly, an equally customizable event list can be added using the "[eventlist]" shortcode.  

This plugin does not support WordPress MU.

=  Features =

* Filter events by category
* Month and Year dropdown selectors for fast navigation
* Navigate between months/weeks with the calendar navigation buttons and the mouse wheel
* Add, delete or modify event category labels and colors
* Supports daily, weekly, biweekly, monthly, and yearly repeating events
* Display events in Day, Week, and Month views
* Specify calendar date and time formats via **Settings > General** menu
* Specify calendar time slot intervals: 15, 30 and 60 minute options (default 30)
* Specify which event form fields to hide, display and require
* Option to specify a category filter label
* Option to convert URLs entered in the description field into clickable links
* Option to open links entered in event details in a new/same browser window
* Option to allow/disallow the creation or editing of expired events
* Option to show/hide the **Add Events** link (to the Administrative Calendar) above the front-end Calendar
* Option to show/hide weekends on the calendar
* Option to allow/disallow mouse wheel calendar navigation
* Link to Google Maps, automatically generated from event address fields
* Display a generated list of calendar contributors using the sidebar widget
* View an **Activity Report** of the current month's event distribution by category
* Track the number of events created by each user in the **Users** menu
* Assign users the ability to add and modify their own events (**aec_add_events**)
* Assign users the ability to modify all events (**aec_manage_events**)
* Assign users the ability to change all calendar options (**aec_manage_calendar**)
* Available in 20 languages with support for right-to-left languages (not all translations are current)

= Shortcode Options =

* [calendar] shortcode can be added to text widget or page content, create multiple views using [optional parameters] (default):
* [calendar categories=1,2,3] (all) display events from specified category(ies)
* [calendar excluded=true] (false) exclude categories listed in the categories parameter
* [calendar filter=3] (all) highlight specified category id in filter
* [calendar view=basicDay|agendaDay|basicWeek|agendaWeek|month] (month) display specified calendar view
* [calendar month=10] (current month) display specified calendar month on load
* [calendar year=2012] (current year) display specified calendar year on load
* [calendar views=basicDay|agendaDay|basicWeek|agendaWeek|month] ("month,agendaWeek") display specified calendar view options
* [calendar nav=false] (true) toggle calendar navigation buttons
* [calendar scroll=true] (false) toggle calendar mouse wheel navigation
* [calendar height=200] (null) assigns a minimum pixel height to the calendar
* ***
* [eventlist] shortcode can be added to a text widget or page content with these [optional parameters] (default):
* [eventlist categories=1,2,3] (all) display events from specified category(ies)
* [eventlist excluded=true] (false) exclude categories listed in the categories parameter
* [eventlist start="yyyy-mm-dd"] (today) display events starting on or after the specified date
* [eventlist end="yyyy-mm-dd"] (1 year from today) display events ending on or before the specified date
* [eventlist limit=15] (4) limit events displayed to the specified quantity
* [eventlist whitelabel=true] (false) renders events without category colors
* [eventlist noresults="No Results"] ("No Upcoming Events") message displayed when no events are returned
* ***
* [eventlist] replaces Upcoming Events widget
* Category id reference is available on the **Categories** page

= Need Support? =
* [Find answers to Frequently Asked Questions](http://wordpress.org/extend/plugins/ajax-event-calendar/faq)
* [Ask for help in the WordPress forums](http://wordpress.org/tags/ajax-event-calendar?forum_id=10)
* [Submit issues and feature requests](http://code.google.com/p/wp-aec/issues/list)

= A BIG Thank You to those who have provided translations =
* Arabic (Sultan G) - Shukran
* Catalan (Isaac T) - Gracias
* Czech (Kamil) - Dekuji
* Danish (kfe1970) - Tak
* Dutch (Maikel) - Bedankt
* French (doc75, Luc) - Merci
* German (Tobias) - Danke
* Italian (Ernesto, eros.mazzurco) - Grazie
* Indonesian (Nanang) - Matur Tampiasih
* Latvian (Kaspars) - Paldies
* Lithuanian (juliuslt) - Aciu
* Norwegian (Julius) - Takk
* Polish (Szymon) - Dziekuje
* Portuguese (rgranzoti, ricardorodh) - Obrigado
* Romanian (Razvan) - Multumesc
* Russian (reddylabeu) - Spasiba
* Spanish (Fernando) - Gracias
* Swedish (Hirschan) - Tack
* Tamil (Bage) - Nandri
* Turkish (Darth crow) - Sag Olun

== Installation ==

1. [WordPress plugin installation](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)
1. To create the front-end calendar: create a page or a post with any desired title.
1. Add [calendar] shortcode to the body of the page (works with native WordPress page publishing and template options)
1. Save the page or post
1. Blog time zone option must be a city value (plugin cannot handle dates properly if set to gmt_offset)

== Frequently Asked Questions ==

= I just upgraded plugins/themes and the front-end calendar no longer appears =
Check for errors in your browser javascript console.  Other plugins or your theme may be causing a javascript conflict that prevents the calendar from functioning.  Disable any newly updated plugins/themes and isolate the cause of the error by reactivating them one at a time.

= The front-end calendar is stuck on "Loading..." in my custom theme, yet the back-end calendar works fine =
Make sure your theme templates contain `<?php wp_head(); ?>` just before the closing `</head>` tag and `<?php wp_footer(); ?>` just before the closing `</body>` tag.

= I am running WordPress Multi-Site and can only get the calendar to work on one site =
The calendar does not support multi-site installations.  However, users have reported success when the plugin is activated individually on each site.

= Calendar categories are not displaying in the calendar and only the "all" link in the event filter has a background color =
On modify the name of of any existing category and press update. Return to the front-end calendar and press Shift+F5 to reload the css file. If the category colors still do not appear, the problem is likely caused by insufficient file permissions.  Internet hosts setup security differently, and your host is denying the plugin rights to create the cat_colors.css file.  BEFORE activating your plugin, change to CHMOD777 for the plugin **css** folder.  Permissions can be modified via FTP client or your host's administrative panel; ask your host provider for assistance.

= All apostrophes in the event detail form, returns \' when I save the event.  And returns \\' on subsequent saves =
This will occur when your PHP server is configured to use magic quotes gpc.  The developers of PHP [strongly recommend against using magic quotes](http://php.net/manual/en/security.magicquotes.php), in fact the functionality has been removed from newer versions of PHP.  To correct this behavior, edit your php.ini file and disable that setting (or ask your host provider to do so).  If you are unable (or don't have access) to edit your php.ini file, [try this solution](http://wordpress.org/support/topic/plugin-ajax-event-calendar-ajax-event-calendar-dont-like-the-apostrophes?replies=11#post-2260027).

= How do I manage events? =
As with Google Calendar: to add an event, in the administrative calendar page click on a date (or range of dates) in the month view, or click on a half-hour (or range of hours) timeslot in the week view.  Only users assigned the aec_add_events capability can edit and delete events they create by clicking on events in the administrative calendar page.  Users assigned the aec_manage_events capability can edit and delete all events.

= How do I manage event categories? =
To add a category simply enter the desired category name in the input field, select a background color via the colorpicker or enter the hex value in the field provided.  Only users assigned the aec_manage_calendar capability can manage categories.

= How do I change the calendar date and times format? =
The date and time formats for the calendar are controlled by Date Format / Time Format settings located in the Settings > General page

= What happens when the plugin is deleted? =
The event and category databases, custom roles, plugin capabilities, plugin options and widget settings are **permanently removed**.

= What happens to user events when they are deleted? =
All events associated with a deleted user are **permanently deleted**.

= What happens to events associated with a deleted category? =
All events associated with a deleted category are re-assigned to the primary category.

= I am an administrator and want to grant calendar rights to a user without giving them access to all blog administrative menus =
Install a Capabilities/Roles management plugin, such as [Members](http://wordpress.org/extend/plugins/members/), and assign the capabilities listed in the plugin description, to existing or newly created roles.

= I want to include the calendar to my theme.  Is there a way to call and display the calendar without short codes? =
Yes, place this code in your template at the desired location `<?php echo do_shortcode('[calendar]') ?>`.

== Screenshots ==

1. Front-end Calendar Setup
2. Options Page
3. New User Role
4. Event field on the Users page
5. Upcoming Events
6. Manage Events
7. Manage Categories
8. Activity Report
9. Event Detail Form
10. Growl Notifications
11. Plugin uses built-in WordPress date/time localization and day of week setting
12. Front-end Calendar View with Upcoming Events and Calendar Contributors Widgets
13. Front-end Events Detail View

== Other Notes ==

Hat Tip to these fine plugins which were instrumental in the creation of this plugin:
<br>1. Google Calendar interface experience (FullCalendar)
<br>2. Growl feedback (jGrowl)
<br>3. OSX modal forms (simpleModal)
<br>4. Category color selection (miniColors)

== Changelog ==
= 0.9.9.3 =
* TODO: modify event detail dates for repeating events
* added repeating events options
* added option to toggle mousescroll on administrative calendar
* added month and year dropdown selectors for fast navigation
* added option to modify calendar time slot intervals (15, 30 and 60 minute options)
* added new [eventlist] shortcode to replace upcoming events widget, can be added to a text widget or page content
* [eventlist] shortcode can be added to a text widget or page content with [optional parameters] (default value in parenthesis)
* added eventlist optional parameter [categories="1,2,3"] (all) display events from specified category(ies)
* added eventlist optional parameter [excluded=true] (false) exclude categories listed in the categories parameter
* added eventlist optional parameter [start="yyyy-mm-dd"] (today) display events starting on or after the specified date
* added eventlist optional parameter [end="yyyy-mm-dd"] (1 year from today) display events ending on or before the specified date
* added eventlist optional parameter [limit=15] (4) limit events displayed to the specified quantity
* added eventlist optional parameter [whitelabel=true] (false) renders events without category colors
* added eventlist optional parameter [noresults="No Results"] ("No Upcoming Events") message displayed when no events are returned
* added calendar optional parameter [height=200] (75% of width) assigns a minimum pixel height
* fixed compatability conflict with easy fancybox plugin (Hat Tip: Raven)
* fixed month calendar shortcode option when set to current month
* fixed rtl localization admin menu position bug
* fixed mousescroll for week and day view
* fixed show event detail address layout
* fixed critical IE bug
* updated plugin options page layout and text
* updated filter css hover state
* moved options page position into calendar menu
* moved help text into options page sidebar
* removed menu position to avoid plugin collisions, the Calendar menu is now located below Settings
* added em icon to ajax event calendar plugin menu and pages

= 0.9.9.2 =
* added latvian localization
* updated arabic localization
* updated swedish localization
* updated spanish localization
* fixed option to toggle link target in new window
* fixed critical IE bug

= 0.9.9.1 =
* optimized mousewheel scroll
* optimized loading events notification
* fixed category reassign/delete process, now completes deletion of emptied category
* optimized performance
* added swedish localization

= 0.9.9 =
* added options to hide any non-essential input field in the event form
* added option to allow URLs in the description field to be clickable links
* added toggle option to open links in either a new or the same browser window
* fixed time zone error
* duration calculation on admin event detail fix
* added default cat_colors.css file to distribution, to address reported file authorization failures
* added filter label customization option
* added filter to admin calendar view
* added support for right-to-left language localization
* added display of uneditable events in administrative mode (nod to Treyer Lukas)
* added option to navigation between calendar months by scrolling the mouse wheel
* added optional parameter [calendar] shortcode can be added to text widget or page content, create multiple views using [optional parameters] (default):
* added optional parameter [calendar categories="1,2,3"] (all) display events from specified category(ies)
* added optional parameter [calendar excluded=true] (false) exclude categories listed in the categories parameter
* added optional parameter [calendar filter=3] (all) highlight specified category id in filter
* added optional parameter [calendar view=agendaWeek|basicWeek|month] (month) display specified calendar view
* added optional parameter [calendar month=10] (current month) display specified calendar month on load
* added optional parameter [calendar year=2012] (current year) display specified calendar year on load
* added optional parameter [calendar views=agendaWeek|basicWeek|month] ("month,agendaWeek") display specified calendar view options
* added optional parameter [calendar nav=false] (true) toggle calendar navigation buttons
* added optional parameter [calendar scroll=true] (false) toggle calendar mouse wheel navigation
* added optional parameter [calendar height=200] (null) assigns a minimum pixel height to the calendar
* replaced loading modal with growl to reduce impact of visual transition
* modified upcoming widget filter from number of weeks to maximum events displayed
* modified upcoming widget format to display only start date and time
* modified show event detail so that date/time format displays on a single line
* added upcoming events option to toggle category colors in widget
* added aec prefix to widgets for visual grouping
* added id field (to support new shortcode options) and modified layout of category management for improved readability
* added donate link
* updated help text
* added arabic localization
* added romanian localization
* updated norwegian localization
* updated italian localization
* updated french localization

= 0.9.8.6 =
* added line break detection so the description field displays as it is entered
* limit creation of expired events fix
* added norwegian localization
* added indonesian localization
* added italian localization
* updated tamil localization

= 0.9.8.51 beta =
* beta release
* fixed date/time field processing via event add/update form
* fixed duration style
* added tamil localization

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
* upcoming widget addition of user input title, undefined time zone fix, and ongoing event fix
* shortcode respectful of position within post text fix
* updated uninstall script with new capabilities and roles
* event detail form description validation fix
* added russian localization
* added danish localization
* added polish localization

= 0.9.8.1 =
* replaced php 5.3.x dependent DateTime class with a modified strtotime to accommodate d/m/Y format
* revised admin menu wording
* added german localization

= 0.9.8 =
* comprehensive refactoring of ajax elements
* localized all javascript
* fixed google map link generator and added toggle display control
* added formatting, styling and linked event details to upcoming events widget
* hooked calendar start of week into wordpress blog setting
* hooked calendar date format into wordpress blog setting
* hooked calendar time format into wordpress blog setting
* added spanish localization
* added turkish localization
* added lithuanian localization
* updated portuguese localization
* added dutch localization

= 0.9.7.1 =
* event display fix
* updated french localization

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
* added french localization

= 0.9.1 =
* added portuguese localization
* added more localization
* fixed default option initialization
* further improved event detail page ui

= 0.9 =
* improved event detail page ui
* refactored event detail page (to address instances of event detail not loading)
* added event detail form field options - plugin options page now located in "settings" menu
* added multi-language support

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
= 0.9.9.2 =
* critical IE bug fixed