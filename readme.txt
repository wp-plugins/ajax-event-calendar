=== Ajax Event Calendar ===
Contributors: eranmiller
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NCDKRE46K2NBA
Tags: calendar, event calendar, event list, move events, resize events, copy events, recurring events, repeating events
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: 1.0.2

A drag-n-drop calendar that enables filtered views of community added events in custom categories.

== Description ==

A community calendar that allows authorized users to add, edit, move, copy, resize, delete and filter events into customizable categories.  Calendars and event lists can be added to your blog by adding [highly customizable](http://code.google.com/p/wp-aec/wiki/ShortcodeOptions) shortcodes in the body of a page, a post or a text widget.

[youtube http://www.youtube.com/watch?v=bEzomAUe4DE&rel=0]

= Key Features =
* Select multiple days to quickly create multi-day events
* Drag events or stretch across multiple days to quickly change event date/duration
* Display events in Day, Week, and Month views
* Copy/Duplicate/Clone events
* Supports daily, weekly, monthly and yearly repeating events
* Month and Year dropdown selectors for fast navigation
* Optional navigate calendar months/weeks via mouse wheel
* Optional mini-calendar view (for sidebar)
* Customize category filter label (default: "Show Type")
* Add, modify and delete event category names and colors
* Instantly filter events by category
* Customize date/time format and start of week setting
* Customize calendar minute intervals
* Customize which event fields to hide, display or require
* Optional convert URLs entered in the event description into clickable links
* Optional disallow creation of past events
* Optional show **Add Events** link (to the Administrative Calendar) on the front-end Calendar
* Optional to show/hide weekends on the calendar
* Link to Google Maps automatically generated from address fields
* Optional sidebar widget that displays a list of calendar contributors
* View **Activity Report** of current month's event distribution by category
* Track the number of events created by each user in the **Users** menu
* Assign users the ability to add and modify their own events (**aec_add_events**)
* Assign users the ability to modify all events (**aec_manage_events**)
* Assign users the ability to change all calendar options (**aec_manage_calendar**)
* Supports right-to-left languages

= Available in 23 languages =
* Arabic ( Sultan G )
* Catalan ( Isaac Turon )
* Chinese [China and Taiwan] ( GC Tech )
* Czech ( Kamil )
* Danish ( kfe1970 )
* Dutch ( Maikel )
* English ( Eran Miller, http://www.eranmiller.com )
* French ( doc75, Luc )
* German ( Ralph Stenzel, http://www.klein-aber-fein.de )
* Hungarian ( Gabor Major )
* Italian ( Ernesto, eros.mazzurco )
* Indonesian ( Nanang )
* Latvian ( Kaspars )
* Lithuanian ( Vincent G, http://www.host1free.com )
* Norwegian ( Julius )
* Polish ( Szymon )
* Portuguese ( rgranzoti, ricardorodh )
* Romanian ( Razvan )
* Russian ( reddylabeu )
* Spanish ( Fernando )
* Swedish ( Hirschan )
* Tamil ( Bage )
* Turkish ( Darth crow )

== Installation ==

1. Follow the typical [WordPress plugin installation steps](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)
2. If you are unfamiliar with shortcode usage, [learn about shortcodes](http://codex.wordpress.org/Shortcode_API)
3. To create a new calendar, add the [calendar] shortcode to the body of a page, a post or a text widget
4. To create a new event list, add the [eventlist] shortcode to the body of a page, a post or a text widget
5. To customize display, review the [shortcode options](http://code.google.com/p/wp-aec/wiki/ShortcodeOptions)
6. Having problems? [Read this first](http://code.google.com/p/wp-aec/wiki/FrequentlyAskedQuestions)
7. Ask other users for help [on the forums](http://wordpress.org/tags/ajax-event-calendar?forum_id=10)
8. Found a bug? [Submit valid bugs and feature requests here](http://code.google.com/p/wp-aec/issues/list?can=1)


** Important **

* Your blog time zone option must be a city name value, the plugin may not handle dates correctly when set to a numeric gmt_offset.
* Does NOT support WordPress MU implementations.
* When adding shortcodes to pages or posts be sure to use the HTML editor mode - not the Visual editor.
* You are encourage to backup event data (located in aec_event and aec_event_category tables) before running plugin updates. Select a plugin that can backup and restore custom (non-WordPress) tables.

== Frequently Asked Questions ==

[The FAQ is located here](http://code.google.com/p/wp-aec/wiki/FrequentlyAskedQuestions).


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

These fine plugins were instrumental in the creation of this plugin:

1. Google-like calendar interface (FullCalendar)
2. Growl feedback (jGrowl)
3. OSX modal forms (simpleModal)
4. Category color selection (miniColors)

== Changelog ==

= 1.0.2 =
* [#264](http://code.google.com/p/wp-aec/issues/detail?id=264): fixed critical IE button locking issue
* added inline widget message to alert users not aware of notifications on plugin homepage
* added chinese

= 1.0.1 =
* fixed drag-n-drop functionality (updated fullcalendar.js)
* [#246](http://code.google.com/p/wp-aec/issues/detail?can=2&id=246): fixed mousewheel scrolling (updated mousescroll.js)
* replaced dynamically generated cat-colors.css file with inline css to eliminate permission failures
* updated simplemodal.js
* fixed modal overlay caused by theme header images css


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
* added calendar icons (Hat Tip: Luc)
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
= 1.0.2 =
* IE bug fix and widget to eventlist migration reminder