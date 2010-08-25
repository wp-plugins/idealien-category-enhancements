=== Idealien Category Enhancements ===
Contributors: idealien
Donate link: http://www.idealienstudios.com
Tags: category, template, post, idealien, theme, cms, categories, customize, presentation, wp-as-a-cms
Requires at least: 2.5
Tested up to: 2.8
Stable tag: trunk

Modification to the category and post template logic to behave like pages (selectable through admin)

== Description ==

This plugin makes category templates be selectable by drop-down list from the manage > categories screen of the admin console. It makes the selection / use of category and post templates as easy as page templates. It also includes configuration options that allow you to configure whether sub-categories inherit templates from their parent if none are specified.

Features: 

* You can now use one category template for multiple categories without identical copies of the category-##.php

* All category / post templates and settings are relative to the current theme applied to Wordpress

* If you select a different theme, the entries will remain in the database in case you want to revert back.

* If you delete a category, all records (regardless of theme / version) will be deleted.

* The Sub-Category Inheritance option applies for both category templates and post templates.

* Now supports parent / child theme configurations.

== Installation ==

1. Download the plugin

2. Unzip files to your wp-content/plugins directory on your local PC

3. Upload the files from local PC to your web server

4. Activate the plugin through the plugins menu in Wordpress

5. Go to the Settings > Category Enhancements menu to configure features

6. Develop template(s) which can take advantage of this functionality. Your category template files SHOULD have the following comments at the top:
`<?php /*
Category Template: Custom Template Name
Template URI: URL path where WP users can find more information about your template(s)
Description: A short description goes here.
*/ ?>`

== Frequently Asked Questions ==

= Does this plugin work with WordPress 3.0? =

Not currently. With the custom post types functionality in 3.0, the purpose that I had in creating ICE has been solved through core code. I suggest you look at http://wordpress.org/extend/plugins/simple-custom-post-type-archives/ if you are wanting similar functionality through 3.0.

= How can I support this awesome plugin? =

Make feature suggestions at http://idealienstudios.com/projects/ICE/ or donate via paypal to jamie.oastler@gmail.com

= Why did my custom templates suddenly stop working? =

Did you upgrade your theme? Selections from the manage > categories panel are stored based on theme directory and version.

= This plugin is great for modifying the front-end of categories / posts, but what about the admin console? =

Check out examples of using ICE with [More Fields](http://wordpress.org/extend/plugins/more-fields/ "More Fields") on the [Idealien Templates Project Page](http://idealienstudios.com/projects/templates/ "Idealien Templates Project Page"). More Fields lets you create customized write panels with custom fields for easy to edit admin templates.

= How can I find out more about this plugin including examples of its usage? =

Visit the plugin homepage - [idealienstudios.com/projects/ICE/](http://idealienstudios.com/projects/ICE/ "ICE Project Page")

== Screenshots ==

1. The options screen for Idealien Category Enhancements.


