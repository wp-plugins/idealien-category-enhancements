=== Idealien Category Enhancements ===
Contributors: firedolljamie, idealien
Donate link: http://www.idealienstudios.com
Tags: category, template, post
Requires at least: 2.5
Tested up to: 2.6.2
Stable tag: 1trunk

Modification to the category and post template logic to behave like pages (selectable through admin)

== Description ==

The idea for this plugin was to expose into the manage > categories portion of the admin console a way for the user to select a template for the category based on a drop-down list. Not that dissimilar from the process a user can select a template for a page based on the drop-down list. As a result, any queries to the category url would render the page according to the category template selected. In addition, the post in a given category will look for an equivelently named template in order to render queries to itself.

Features: 
1. You can now use one category template for multiple categories without identical copies of the category-##.php
1. All category / post templates and settings are relative to the current theme applied to Wordpress
1. If you select a different theme, the entries will remain in the database in case you want to revert back.
1. If you delete a category, all records (regardless of theme / version) will be deleted.
1. The Sub-Category Inheritance option applies for both category templates and post templates.

== Installation ==

1. Download the plugin
1. Unzip files to your wp-content/plugins directory on your local PC / repository
1. Upload the files from local PC to your web server
1. Activate the plugin through the plugins menu in Wordpress
1. Go to the Settings > Category Enhancements menu to configure features
1. Develop template(s) which can take advantage of this functionality. Similar to a theme styles.css file, for best usability your category template files should have the following comments at the top:
`<?php /*
Category Template: Custom Template Name
Template URI: URL path where bloggers can find more information about your template(s)
Description: A short description goes here.
*/ ?> `


== Frequently Asked Questions ==

= How can I find out more about this plugin including examples of its usage? =

Visit the plugin homepage - [idealienstudios.com/code/ICE/](http://idealienstudios.com/code/ICE/ "ICE Plugin Page")