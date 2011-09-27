=== Ajax Event Calendar ===
Contributors: eranmiller
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NCDKRE46K2NBA
Tags: user authorize, event calendar, customize categories, event list, ajax, category filter, google map, rtl languages, shortcode, move events, resize events, copy events, recurring events, repeating events, translated
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: 0.9.9.2

A fully localized community calendar that allows authorized users to manage events in custom categories.

== Description ==

Ajax Event Calendar WordPress Plugin is a fully localized (including RTL language support) community calendar that allows authorized users to add, edit, move, copy, resize and delete events into custom categories.  Highly customized calendars can be added to pages, posts or text widgets using the "[calendar]" shortcode.  Similarly, an equally customizable event list can be added using the "[eventlist]" shortcode.  The **Installation** tab contains details on plugin installation and shortcode options.

This plugin does not support WordPress MU.

=  Features =

* Filter events by category
* Display events in Day, Week, and Month views
* Add, delete or modify event category labels and colors
* Copy events
* Supports daily, weekly, biweekly, monthly, and yearly repeating events
* Month and Year dropdown selectors for fast navigation
* Navigate between months/weeks with the calendar navigation buttons and the mouse wheel
* Mini-calendar setting
* Specify calendar date and time formats and start of week via **Settings > General** menu
* Specify calendar time slot intervals: 5, 10, 15, 30 and 60 minute options (default 30)
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
* Unobstrusive design loads code and data only when necessary
* Available in 21 languages with support for right-to-left languages (NOTE: not all translations are current)

= Need Support? =
* [Read about installation and options](http://wordpress.org/extend/plugins/ajax-event-calendar/installation)
* [Find answers to Frequently Asked Questions](http://wordpress.org/extend/plugins/ajax-event-calendar/faq)
* [Ask for help in the WordPress forums](http://wordpress.org/tags/ajax-event-calendar?forum_id=10)
* [Submit issues and feature requests](http://code.google.com/p/wp-aec/issues/list)

= A BIG Thank You to those who have provided translations =
* Arabic (Sultan G) - Shukran
* Catalan (Isaac Turon) - Gracias
* Czech (Kamil) - Dekuji
* Danish (kfe1970) - Tak
* Dutch (Maikel) - Bedankt
* French (doc75, Luc) - Merci
* German (Tobias) - Danke
* Hungarian (Gabor Major) - Koszonom
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

1. Follow the typical [WordPress plugin installation steps](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).
1. If you are unfamiliar with shortcode usage, learn about shortcodes.
1. To create a new calendar, add the [calendar] shortcode to the body of a page, a post or a text widget.
1. To create a new event list, add the [eventlist] shortcode to the body of a page, a post or a text widget. As of version 1.0 this replaces the Upcoming Events widget, the widget will be removed in future plugin versions.
1. Shortcode display can be customized using these [Shortcode Options](http://code.google.com/p/wp-aec/wiki/ShortcodeOptions).

**IMPORTANT !**

* When adding shortcodes to pages or posts be sure to use the WordPress HTML editor view - not the Visual editor view.
* Your blog time zone option must be a city value - the plugin will not handle dates correctly when set to a numeric gmt_offset.
* Be sure to backup critical event data prior to upgrading the plugin. Choose a plugin from the WordPress Plugins Repository that can backup and restore custom (non-WordPress) tables.

== Frequently Asked Questions ==

= I have a question about the plugin, where can I get help? =
First, scan this FAQ for answers.
Next, search the [forum](http://wordpress.org/tags/ajax-event-calendar) - use the name of the plugin in quotes and your keyword(s) to effectively narrow the search, for example: `"ajax event calendar" css`.  Finally, if you can't find the answer in the forum, search the [issue tracker](http://code.google.com/p/wp-aec/issues/list) for known issues and requests - if you still don't find an answer, submit your bug report or feature request there.

= The front-end calendar does not work in my custom theme =
Make sure your theme templates contain `<?php wp_head(); ?>` just before the closing `</head>` tag and `<?php wp_footer(); ?>` just before the closing `</body>` tag.

= The administrative calendar displays, but the front-end calendar does not display =
Did you add the [calendar] shortcode to a page, post or text widget?  If not, read the plugin [installation instructions](http://wordpress.org/extend/plugins/ajax-event-calendar/installation/).  If you did, see the next FAQ for suggestions.

= I added the [calendar] shortcode to a page, but I can't see the calendar.  I only a link for "Add Events" and the filter. =
Troubleshoot by viewing your site using the Firefox browser with the [Firebug plugin](http://getfirebug.com/) activated.  When you load the page in question, any errors in the javascript will display in the console. The error text should provide information as to line and the file causing the issue.  Next, disable all plugins other than the calendar, the javascript errors should cease to appear.  Activate the plugins one at a time until the calandar stops working (and you'll have found the conflict).  Themes with javascript can also be the cause of such errors.

= I want to modify some of the styles in the calendar =
View your site using Firefox browser with [Firebug plugin](http://getfirebug.com/) activated.  Read about using Firebug and learn how to "inspect" specific page elements for styling.  Once you have settled on the look you want, apply it to your theme's css.  Confirm your changes with Firebug, it's an invaluable tool you can use many times over.

= Calendar categories are not displaying in the calendar and only the "all" link in the event filter has a background color OR The category colors no longer appear in the filter, the calendar or the event list. =
Try:
<br>1. add a new category (temporarily)
<br>2. wait for the save confirmation notification to appear
<br>3. delete the temporary category
<br>4. navigate to the frontend calendar
<br>5. press shift+F5
<br>If the category colors do not appear, the problem is likely caused by insufficient file permissions.  Internet hosts setup security differently, and your host is denying the plugin rights to create the cat_colors.css file.  Deactivate the plugin. Change to CHMOD777 for the plugin's **css** folder.  Permissions can be modified via FTP client or your host's administrative panel - ask your host provider for assistance.  Reactivate the plugin.
<br>If the category colors still do not appear check your php sever logs for errors.

= All apostrophes in the event detail form, returns \' when I save the event.  And returns \\' on subsequent saves =
This will occur when your PHP server is configured to use magic quotes gpc.  The developers of PHP [strongly recommend against using magic quotes](http://php.net/manual/en/security.magicquotes.php), in fact, the functionality has been removed from the latest versions of PHP.
<br>To correct this behavior: edit your php.ini file, or ask your host provider to do so, [as described here](http://php.net/manual/en/security.magicquotes.disabling.php).

= How do I manage events? =
As with Google Calendar: to add an event, in the administrative calendar page click on a date (or range of dates) in the month view, or click on a half-hour (or range of hours) timeslot in the week view.  Only users assigned the **aec_add_events** capability can edit and delete events they create by clicking on events in the administrative calendar page.  Users assigned the **aec_manage_events** capability can edit and delete all events.

= How do I manage event categories? =
To add a category simply enter the desired category name in the input field, select a background color via the colorpicker or enter the hex value in the field provided.  Only users assigned the aec_manage_calendar capability can manage categories.

= I want to grant calendar rights to a user without giving them access to all blog administrative menus =
Install a [Capabilities/Roles management plugin](http://sct.temple.edu/blogs/it/2011/02/16/members-vs-capability-manager-plug-in/), and assign the capabilities listed in the plugin description, to existing or newly created roles.

= Is there a way to configure the WordPress to allow users that come to the site to can register and create their own events (so not every event would need to be created by the administrator)? =
Absolutely!  If you allow users to self-register and you'll need to change WordPress settings so that the default role assigned to new users is Calendar Contributor.

= What happens when the plugin is deleted? =
The event and category databases, custom roles, plugin capabilities, plugin options and widget settings are **permanently removed**.

= What happens to user events when they are deleted? =
All events associated with a deleted user are **permanently deleted**.

= What happens to events associated with a deleted category? =
All events associated with a deleted category are re-assigned to the primary category.

= I want to include the calendar to my theme.  Is there a way to call and display the calendar without short codes? =
Yes, place this code in your template at the desired location `<?php echo do_shortcode('[calendar]') ?>`.

= Does the AEC support the WordPress Network (Multisite) feature? When I tried to implement it on a multisite I was unable to add events or categories on any of the sub sites except the primary. =
The plugin does not specifically support multisite setups.  However, one user claims the plugin works if "activated on a site-by-site basis."

= How can I add "print calendar" functionality on the calendar page? =
To add print functionality, open a post or page, select the HTML tab in Wordpress Post editor, and enter this code snippet above or below the calendar shortcode: `<a href="javascript:window.print();">Print Page</a>`.  If you want to hide certain page elements, such as sidebars you'll need to add print specific rules to your theme stylesheet.  For helpful resources to start you off, search for "Print Style Sheets" in your favorite search engine.

= How can I add images to the event detail? =
Insert the image in the description field of the desired event, don't forget to include a height parameter or your event detail box may not size correctly.

= How can I hide the Duration box and text? =
Add `display:none` to `#aec-modal .duration` to your theme css file

= How can I change current day background and font color? =
Add the `.fc-state-highlight` class to your theme stylesheet and apply the desired color rules

= How can I put the calendar on the home page? =
Create a front-end calendar as described in the plugin installation page.
Then follow the instructions described [in this link](http://codex.wordpress.org/Creating_a_Static_Front_Page).

= Is there a way to link directly to a post/page/anything from the calendar? =
No, this plugin was created to function independently of posts and uses a different set of database tables.

= Can I import/link Google calendars to the Ajax Event Calendar? =
No, this calendar emulates the look and feel of Google Calendar but also extends the field options beyond what is offered by Google.

== How can I configure WordPress to display in my language? ==
http://codex.wordpress.org/WordPress_in_Your_Language

= When I try to activate the plugin I get an error about my PHP version, how can I fix this? =
Search your ISP host's FAQ for "How can I enable PHP5?"

== Screenshots ==

1. Front-end Calendar shortcode setup
2. Options - event form fields selection and calendar settings
3. General Settings - date/time format, timezone and week start selection
4. Users - the Event field tracks the number of events inserted by each user
5. Upcoming Events widget options (replaced by `[eventlist]` shortcode as of version 1.0)
6. Administrative Calendar View - Manage Events
7. Categories - edit category filter label, and manage event categories
8. Activity Report - tracks the number of events by category
9. Event Detail - event detail form modal window
10. Notifications - growl-styled unobtrusive status updates
11. Front-end Events Detail View

== Other Notes ==

Hat Tip to these fine plugins which were instrumental in the creation of this plugin:
<br>1. Google Calendar interface experience (FullCalendar)
<br>2. Growl feedback (jGrowl)
<br>3. OSX modal forms (simpleModal)
<br>4. Category color selection (miniColors)

= Plugin Conflicts =
Mime Type Link Images(http://wordpress.org/support/topic/calendar-disappeared?replies=5)
<br>Custom Field Template Plugin(http://wordpress.org/support/topic/ajax-event-calendar-conflict-with-custom-field-template?replies=9)


== Changelog ==

= 1.0 =
* added support repeating events
* added copy event functionality
* added option to toggle mousescroll in administrative calendar
* added month and year dropdown selectors for fast navigation
* added option to modify calendar time slot intervals
* added [eventlist] shortcode to replace upcoming events widget
* added eventlist shortcode parameter to display events from specified category(ies)
* added eventlist shortcode parameter to exclude categories listed in the categories parameter
* added eventlist shortcode parameter to display events starting on or after the specified date
* added eventlist shortcode parameter to display events ending on or before the specified date
* added eventlist shortcode parameter to limit events displayed to the specified quantity
* added eventlist shortcode parameter to render events without category colors
* added eventlist shortcode parameter to display a customized message when no events are returned
* added calendar shortcode parameter to render the calendar with a minimum pixel height
* added calendar shortcode parameter to render a minicalendar
* added repeating event icon indicator
* fixed compatability conflict with easy fancybox plugin (Hat Tip: Raven)
* fixed month calendar shortcode option when set to current month
* fixed rtl localization admin menu position bug
* fixed mousescroll for week and day view
* fixed show event detail address layout
* fixed critical IE bug
* optimized loading of javascript and css files
* updated plugin options page layout and text
* updated filter css hover state
* moved options page position into calendar menu
* moved help text into options page sidebar
* removed menu position to avoid plugin collisions
* added calendar branding
* added hungarian
* added czech
* updated german
* updated swedish
* updated italian
* updated catalan

= 0.9.9.2 =
* added latvian
* updated arabic
* updated swedish
* updated spanish
* fixed option to toggle link target in new window
* fixed critical IE bug

= 0.9.9.1 =
* optimized mousewheel scroll
* optimized loading events notification
* fixed category reassign/delete process, now completes deletion of emptied category
* optimized performance
* added swedish

= 0.9.9 =
* added options to hide any non-essential input field in the event form
* added option to allow URLs in the description field to be clickable links
* added toggle option to open links in either a new or the same browser window
* fixed time zone error
* duration calculation on admin event detail fix
* added default cat_colors.css file to distribution, to address reported file authorization failures
* added filter label customization option
* added filter to admin calendar view
* added support for right-to-left language
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
* added arabic
* added romanian
* updated norwegian
* updated italian
* updated french

= 0.9.8.6 =
* added line break detection so the description field displays as it is entered
* limit creation of expired events fix
* added norwegian
* added indonesian
* added italian
* updated tamil

= 0.9.8.51 beta =
* beta release
* fixed date/time field processing via event add/update form
* fixed duration style
* added tamil

= 0.9.8.5 =
* calendar weekday (tue) short name fix
* plugin options page save settings for manage_calendar capability fix
* automatically adjusts modal top when WordPress admin bar is visible (contributed by Carl W.)
* event duration display fix
* datepicker localization, noweekends fix
* excised orphaned options
* improved instructional text on the calendar settings page
* added hex input field and more instructional text to category management
* fixed front-end calendar for themes that display multiple pages simultaneously
* revised javascript enqueuing and rendering, fixes theme/plugin conflicts
* upcoming widget addition of user input title, undefined time zone fix, and ongoing event fix
* shortcode respectful of position within post text fix
* updated uninstall script with new capabilities and roles
* event detail form description validation fix
* added russian
* added danish
* added polish

= 0.9.8.1 =
* replaced php 5.3.x dependent DateTime class with a modified strtotime to accommodate d/m/Y format
* revised admin menu wording
* added german

= 0.9.8 =
* comprehensive refactoring of ajax elements
* localized all javascript
* fixed google map link generator and added toggle display control
* added formatting, styling and linked event details to upcoming events widget
* hooked calendar start of week into wordpress blog setting
* hooked calendar date format into wordpress blog setting
* hooked calendar time format into wordpress blog setting
* added spanish
* added turkish
* added lithuanian
* updated portuguese
* added dutch

= 0.9.7.1 =
* event display fix
* updated french

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
* added french

= 0.9.1 =
* added portuguese
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
= 1.0 =
* support for recurring events, eventlist shortcode, minicalendar option, bug fixes