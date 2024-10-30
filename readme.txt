=== Plugin Name ===
Contributors: dan.imbrogno
Donate link: http://bloggingsquared.com
Tags: plugin, template, learn, php, developer, plugin template, WordPress API, example, tutorial
Requires at least: 2.8
Tested up to: 3.1.1
Stable tag: trunk

This is a template plugin to help you start writing your own WordPress plugins. It demonstrated AJAX, saving variables, updating a database, internationalization and proper code encapsulation.

== Description ==

To use this plugin template, add it to your WordPress plugins directory and activate it from the Plugins page. Then take a quick moment to see how the plugin works. There are three components to the plugin that you should try out before you start looking at the code.

1. First, go to the visitor facing side of your website. You'll notice a "Fact Check" form at the bottom of each post. This bit is intended to demonstrate AJAX form submission from the visitor facing pages.
1. Next, go to the admin side of the website. Under the Settings tab, click B2 Template Plugin. This bit shows how to save plugin settings the normal way, how to save data using AJAX from the admin pages, and also demonstrates how internationalization works.
1. Finally, go to the Widgets page under the Appearance tab. Here you'll see a Widget called B2 Template Widget. Drag this into one of your sidebars and experiment with the different settings. Then go to the visitor facing pages of your website and have a look at how this widget works.

Once you've examined these parts of the Plugin, open up the file B2Template.php and begin reading through the well commented code. If you have any questions please visit our blog at [http://bloggingsquared.com](http://bloggingsquared.com "Blogging Squared") or contact the developer directly by emailing [dan.imbrogno@brolly.ca](mailto:dan.imbrogno@brolly.ca).

I hope this helps you on your way to becoming a WordPress plugin guru!

Blogging Squared provides high quality custom WordPress themes and plugins for individuals and businesses at competitive rates. Contact us at [info@bloggingsquared.com](mailto:info@bloggingsquared.com) to request a quote.

== Installation ==

1. Upload the folder `B2Template` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.0 =
* Initial version of the plugin

= 2.0 =
* Completely refactored plugin to have a more logical class structure.
* Complete separation of html, php, js and css.
* Added more detailed comments to facilitate learning
* Added template for WordPress 2.8 and up style widgets

= 2.1 =
* Fixed bug where wordpress activation and deactivation hooks didn't work on some machines. Now using preffered register_activation_hook and register_deactivation_hook methods
* Fixed bug where widget options did not update.
* Fixed bug where RouteActions would throw a warning

= 2.2 =
* Moved update plugin script out of the activation function, since this behaviour is improper and no longer supported. Activating function is only for initial installation, Update function is run on each page load
* Fixed broken $.ajax jQuery calls resulting from upgrade to jQuery 1.4. The type:'post' had to be declared first in the object properties
* Cleaned up handling of ajax / non-ajax actions
* Tested compatibility with WordPress 3.1.1

== Upgrade Notice ==

= 2.0 =
If you'd like to learn how to develop plugins on WordPress 2.8 and higher this version is better suited.