=== Ajax Event Calendar ===
Contributors: eranmiller
Donate link: http://eranmiller.com/plugins/donate/
Tags: multi-user, calendar, category, event, ajax, filter, upcoming, widget, google, localized, rtl-support
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: 0.9.9.2

A fully localized community calendar that allows authorized users to manage events in custom categories.

== Description ==

This plugin allows authorized users to add, edit (via drag n' drop and resize) and delete events in a community calendar viewable by all blog visitors.  The calendar can be added to a page or a post using the "[calendar]" shortcode with several filtering parameters.  All shortcode parameters can be applied together, for example [calendar categories="1,2,3" filter=2 month=8 views=false scroll=true].

**[calendar categories="1,2,3"]** display events from one or more (comma separated) category id(s)* - default:(all)
<br>**[calendar filter=3]** display events filtered by selected id* - default:(all)
<br>*Category id reference is available on the **Categories** page
<br>**[calendar excluded=true]** (true|false) toggles inclusion/exclusion of category(ies) - default: false
<br>**[calendar month=10]** display events from the specified month - default: current month
<br>**[calendar year=2012]** display events from the specified year - default: current year
<br>**[calendar views=false]** (true|false) toggles display of calendar week/month buttons - default: true
<br>**[calendar view=week]** (week|month) displays specified calendar view - default: month
<br>**[calendar nav=false]** (true|false) hides calendar prev and next navigation buttons - default: true
<br>**[calendar scroll=true]** (true|false) toggles mouse wheel in calendar navigation - default: false

Experiencing problems? [Read the FAQ](http://wordpress.org/extend/plugins/ajax-event-calendar/faq).
<br>Can't find the solution? [Try the forum](http://wordpress.org/tags/ajax-event-calendar?forum_id=10) and post your questions there.
<br>If you use this plugin please [rate and confirm plugin compatibility](http://wordpress.org/extend/plugins/ajax-event-calendar/).
<br>If you enjoy this plugin please consider [making a donation](http://eranmiller.com/plugins/donate/).

=  Features =

* Users assigned the Calendar Contributor role (have the aec_add_events capability) can edit and delete events they create
* User roles that have the aec_manage_events capability can edit and delete events created by others
* Instantly filter events by category
* Shortcode options enable multiple instances of calendar data filtered by various display options
* Customizable upcoming events list (sidebar widget), displays event details when clicked 
* Dynamically generated calendar contributor list (sidebar widget)
* Auto-generated Google Maps link, based on event address fields
* Format Date and Time via integrated blog settings
* Navigate between months/weeks with the calendar navigation buttons or the mouse wheel
* Multi-language support, including right-to-left languages (19 translations and counting!)

= User roles that have the aec_manage_calendar capability can... =

* Add, delete or modify event category labels and colors
* Assign the Calendar Contributor role to users and allow them to add events
* View an **Activity Report** of the current month's event distribution by category
* Keep track of the number of **Events** created by each user in the **Users** menu
* Modify calendar date and time formats via blog settings
* Specify which event form fields to hide, display and require
* Toggle URLs entered in the description field into clickable links
* Toggle Event Detail links to open in new/same browser window
* Prevent users from adding or modifying expired events
* Password protect the front-end calendar
* Toggle the **Add Events** link on the front-end Calendar (links to the administrative calendar via login)
* Toggle the display of weekends on the calendar
* Display a contributors list using the "Calendar Contributors" sidebar widget
* Display a filtered list of upcoming events using the "Upcoming Events" sidebar widget

= A BIG Thank You to those who have provided translations =
* Arabic (Sultan G) - Shukran
* Catalan (Isaac T) - Gracias
* Danish (kfe1970) - Tak
* Dutch (Maikel) - Bedankt
* French (doc75word, luc) - Merci
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
* Note: not all translations are up-to-date.
* Don't see your language or want to help complete a translation? Send PO files to: plugins at eranmiller dot com

== Installation ==

1. [WordPress plugin installation](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)
1. To create the front-end calendar: create a page or a post with any desired title.
1. Add [calendar] shortcode to the body of the page (works with native WordPress page publishing and template options)
1. Save the page or post
1. Blog time zone option must be a city value (plugin cannot handle dates properly if set to gmt_offset)

== Frequently Asked Questions ==

= I just upgraded plugins/themes and the front-end calendar no longer appears =
Check for errors in your browser javascript console.  Other plugins or your theme may be causing a javascript conflict that prevents the calendar from functioning.  Disable any newly updated plugins/themes and isolate the cause of the error by reactivating them one at a time.  Contact the author of the plugin or theme causing the error.

= I've installed the plugin but the Calendar option does not appear on the Administrative menu (under Comments) =
Likely caused when [two menu items attempt to inhabit the same menu position](http://core.trac.wordpress.org/ticket/15595).  To resolve this conflict, edit the ajax-event-calendar.php file:
<br>Search for the text "add_menu_page('Ajax Event Calendar'".
<br>Increment the number at the end of the line by one (initially set to 30).
<br>Save the file and refresh the browser window.
<br>Repeat until the Calendar option appears in the admin menu.

= Calendar categories are not displaying in the calendar and only the "all" link in the event filter has a background color =
On the categories page and **press** update on any of the existing categories. Return to the front-end calendar and refresh the page to reload the css file. If the category colors still do not appear, the problem is likely caused by insufficient permission.  Internet hosts setup security differently, and your host is  denying the plugin permission to create the cat_colors.css file (which contains the category color styles).  BEFORE activating your plugin, change to CHMOD777 for the plugin **css** folder.  Permissions can be modified via FTP client or your host's administrative panel; ask your host provider for assistance.

= All apostrophes in the event detail form, returns \' when I save the event.  And returns \\' on subsequent saves =
This will occur when your PHP server is configured to use magic quotes gpc.  The developers of PHP [strongly recommend against using magic quotes](http://php.net/manual/en/security.magicquotes.php), in fact the functionality has been removed from newer versions of PHP.  To correct this behavior, edit your php.ini file and disable that setting (or ask your host provider to do so).  If you are unable (or don't have access) to edit your php.ini file, you can try [this solution](http://wordpress.org/support/topic/plugin-ajax-event-calendar-ajax-event-calendar-dont-like-the-apostrophes?replies=11#post-2260027).

= How do I manage events? = 
As with Google Calendar: to add an event, in the administrative calendar page click on a date (or range of dates) in the month view, or click on a half-hour (or range of hours) timeslot in the week view.  Only users assigned the aec_add_events capability can edit and delete events they create by clicking on events in the administrative calendar page.  Users assigned the aec_manage_events capability can edit and delete all events.

= How do I manage event categories? = 
To add a category simply enter the desired category name in the input field, select a background color via the colorpicker or enter the hex value in the field provided.  Only users assigned the aec_manage_calendar capability can manage categories.

= How does the calendar filter work? =
The filter appears on the front-end Calendar when more than one event category has been created.

= What happens when the plugin is deleted? =
The event and category databases, custom roles, plugin capabilities, plugin options and widget settings are **permanently removed**.

= What happens to user events when they are deleted? =
All events associated with a deleted user are **permanently deleted**.

= What happens to events associated with a deleted category? =
All events associated with a deleted category are re-assigned to the primary category type.

= What roles/capabilities does this plugin include? =
The plugin comes with a custom role called Calendar Contributor (which allows assigned users to add, edit and delete their own calendar events). 
<br>**aec_add_events**: allows a user to add, edit and delete their own calendar events
<br>**aec_manage_events**: allows a user to add, edit and delete all calendar events
<br>**aec_manage_calendar**: allows a user to modify calendar settings, control which event fields to display and require, modify categories, and view the activity report

= I am an administrator and want to grant calendar rights to a user without giving them access to all administrative menus =
For more options install the [Members](http://wordpress.org/extend/plugins/members/) plugin and assign the capabilities listed above as desired to existing, or newly created roles.

= Plugins known to be incompatibile with AEC =
WP Minify plugin: to ensure proper display of the calendar detail view, disable the HTML minification option.

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
= 0.9.9.2 =
* added latvian localization
* updated arabic localization
* updated swedish localization
* updated spanish localization
* fixed option to toggle link target in new window
* critical IE bug fixed 

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
* added optional shortcode parameter to only display events from one or more categories [calendar categories="1,2,3"] default: displays all categories 
* added optional shortcode parameter to exclude display of categories in the categories option[calendar excluded=true] default: false
* added optional shortcode parameter to set default filter category_id option [calendar filter=3] default: All
* added optional shortcode parameter to toggle the calendar view between week and month [calendar view=week] default: month
* added optional shortcode parameter to display a specific calendar month on load [calendar month=10] default: current month
* added optional shortcode parameter to display a specific calendar year on load [calendar year=2012] default: current year
* added optional shortcode parameter to hide calendar week and month buttons [calendar views=false] default: true
* added optional shortcode parameter to hide calendar month navigation prev and next buttons [calendar nav=false] default: true
* added optional shortcode parameter to enable calendar month navigation via the mouse wheel [calendar scroll=true] default: false
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

= 0.9.9.1 =
* shortcode options, code optimizations, fixes to javascript conflicts

= 0.9.8.6 =
* fixed creation of past events, localization, style modifications

= 0.9.8.51 beta =
* major fix to add/update date fields, validation and duration style

= 0.9.8.5 =
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
* added upcoming events widget, wider theme support

= 0.9.1 =
* more localization, fixed default option initialization, ui improvements

= 0.9 =
* added form field options, foundation for localization, ui improvements