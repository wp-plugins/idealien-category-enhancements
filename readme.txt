=== Idealien Category Enhancements ===
Contributors: firedolljamie, idealien
Donate link: http://www.idealienstudios.com
Tags: category, template, post, idealien
Requires at least: 2.5
Tested up to: 2.7
Stable tag: trunk

Modification to the category and post template logic to behave like pages (selectable through admin)

== Description ==

Manage category templates as easily as you manage page templates. Select which apply through the post > categories menu using file names that make sense, not category ID numbers. Now any views of the category (or sub-categories) will render the according to the category template selected. Posts in a given category can also use a category-based template.

Features: 

* You can now use one category template for multiple categories without identical copies of the category-##.php

* All category / post templates and settings are relative to the current theme applied to Wordpress

* If you select a different theme, the entries will remain in the database in case you want to revert back.

* If you delete a category, all records (regardless of theme / version) will be deleted.

* The Sub-Category Inheritance option applies for both category templates and post templates.

== Installation ==

1. Download the plugin

2. Unzip files to your wp-content/plugins directory on your local PC

3. Upload the files from local PC to your web server

4. Activate the plugin through the plugins menu in Wordpress

5. Go to the Settings > Idealien Cats menu to configure features

6. Develop template(s) which can take advantage of this functionality. Similar to a theme styles.css file, for best usability your category template files should have the following comments at the top:
`<?php /*
Category Template: Custom Template Name
Template URI: URL path where bloggers can find more information about your template(s)
Description: A short description goes here.
*/ ?> `

== Frequently Asked Questions ==

= How can I find out more about this plugin including examples of its usage? =

Visit the plugin homepage - [idealienstudios.com/code/plugins/ICE/](http://idealienstudios.com/code/plugins/ICE/ "ICE Plugin Page")