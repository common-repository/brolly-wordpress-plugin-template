<?php
/*
Plugin Name: B2Template
Plugin URI: http://brolly.ca/blog/wordpress/brolly-template-plugin/
Description: This is a template plugin to help you start writing your own wordpress plugins. It focuses on demonstrating ajax interaction from the front and back end, saving variables, creating and upgrading a database, internationalization and structuring your plugin for reusability and to avoid naming conflicts
Version: 2.2
Author: Dan Imbrogno, Blogging Squared
Author URI: http://www.bloggingsquared.com

Copyright 2011  DAN_IMBROGNO  (email : dan.imbrogno@brolly.ca)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*****

	This template assumes a functional knowledge of PHP basics, and some knowledge of Object Oriented programming. If you are not comfortable with PHP, or have never worked with classes before, I would recommend practicing this first before continuing with the template.
	
	To create your own plugin follow these instructions (Where MyPlugin is the name of the plugin you wish to create)

	1) Update the fields in the comment at the top of this file with your own information
	2) Rename folder from B2Template to MyPlugin
	3) Rename file from B2Template.php to MyPlugin.php, and B2TemplateWidget.php to MyPluginWidget.php
	4) Perform a Find and Replace on the B2Template folder to replace all instances of B2Template with MyPlugin
	5) Activate the plugin from the Wordpress Administration panel
	6) Modify MyPlugin.php file to suit your needs
	
	TABLE OF CONTENTS
	==============
	CHAPTER 1.0.................................. Creating the class
		1.1............................................ Defining class variables
		1.2............................................ Defining class constants
		1.3............................................ The plugin constructor
	CHAPTER 2.0.................................. Actions and Filters
		2.1............................................ Actions
		2.2............................................ Filters
	CHAPTER 3.0................................. Plugin Activation and Deactivation
	CHAPTER 4.0................................. Internationalization
		4.1............................................ Using the Gettext function
		4.2............................................ Loading the Plugin textdomain
		4.3............................................ Creating language files
	CHAPTER 5.0.................................. Inserting Administration page links
		5.1............................................ Registering Settings
		5.2............................................ Loading the page
		5.3............................................ Loading JavaScript and CSS into a configuration page
		5.4............................................ Settings link on Plugins page
	CHAPTER 6.0............................... Using Filters and Actions
		6.1............................................ Filter to adding content to a WordPress Post
		6.2............................................ Action to add JavaScript and CSS to viewer facing pages
		6.3............................................ Filter to wrap text around the Wordpress site title
		6.4............................................ Filter the content to apply the Find and Replace rules
	CHAPTER 7.0.................................. Executing Actions with and without Ajax
		7.1............................................ Routing Actions
		7.2............................................ Dynamic Ajax Elements
	CHAPTER 9.0.................................. Widgets
	CHAPTER 10.0................................ Database Interaction
	
		
	*****/

/*
	1.0 Creating the class
	This instantiates our plugin class. It's a good idea to encapsulate your plugin inside a class to avoid naming conflicts. The end user may have many other plugins installed, and if your plugin has the same variable or function names as another, this will cause problems. By putting all your plugin functions inside a class, you avoid this problem entirely, and the only things you need to make sure are unique is the name of your plugin. A good plugin name would be "AcmeBugsBunnyQuotes" a bad plugin name would be "Quotes".
*/

$B2Template = new B2Template(); 

class B2Template
{

	/*
		1.1 Defining class variables
		These are class variables. They are values that are used by more than one of the functions below. By defining them here, any function below can access them.
	*/
	
	var $errors = false;
	var $updated_post = false;
	var $success = false;
	
	/*
		1.2 Defining class constants
		These are class constants. The only real difference between class constants and class variables are that they can't be changed. You may have seen define() statements in PHP before, a class constant is simply a "define" statement that only works inside a class. If you aren't sure why you'd need to use a constant, or have never heard of the term "magic number" in regards to coding before, check out this article: http://is.gd/iVyZU
	*/
	
	const ERROR_FIND_EMPTY = 0;
	const ERROR_DUPLICATE_RULE = 1;
	const ERROR_MISSING_FIELDS = 2;
	const ERROR_MYSQL = 3;
	
	const DB_VERSION = 4; // This number represents the current version of the plugins table structure. Increment this every time you modify the scheme of the database tables that you create for your plugin (see chapter 3.0).
		
	/*
		1.3 The plugin constructor
		This is our plugin constructor. This is what runs first when our plugin gets loaded, so this is a great place to do any intialization that your plugin needs to do before it performs any of its other functions.
		
	*/
		
	function B2Template()
	{
		/*
		2.0 Actions and Filters
		Inside the plugin constructor is a good place to initalize any class variables you've created. It is also a good place to add the hooks and filters that we'd like to register with WordPress.

		2.1 Actions
		Actions can be thought of as the steps that WordPress takes to generate a page. In the process of generating a WordPress page and delivering it to the visitor, WordPress performs tons of actions. Reading a value from the database, initializing a plugin, registering sidebars, printing the code that goes in your html documents <head> tag etc... WordPress lets us insert our own functions into the mix to alter how WordPress behaves when performing these actions. The add_action function let's us attach our own PHP function to be run whenever wordpress performs a particular action. For example, if we want to run a piece of code every time the user saves a post, then we want to attach our function to the save_post action. This would be done with the following line:
			
			add_action('save_post', array($this,'RunThisWhenPostIsSaved'));
			
			The first parameter is obvious, this is the name of the action you want to attach to. A complete list can be found here: 
			
			http://codex.wordpress.org/Plugin_API/Action_Reference.
			
			The second parameter might be a bit more confusing. You might be accustomed to simply entering the name of the function that should be called in the second parameter. This is correct, but since all of our plugin functions are inside a class, we need to tell WordPress to look inside this ($this) class to find our function 'RunThisWhenPostIsSaved'. So instead of a string 'RunThisWhenPostIsSaved', we pass in an array where the first item is a reference to the class which contains the function(e.g. $this, B2Template, MyPlugin), and the second item is the name of the function 'RunThisWhenPostIsSaved')
		*/
		
		/* During the admin_menu action we can add our own items into WordPress' Administration menu. */
		add_action('admin_menu', array($this,'InsertAdminMenuLink'));
		
		/* The init action is one of the first that runs in WordPress. In this case I'm running attaching to it, so I can see if my plugin is trying to execute any actions. More on this later! */
		add_action('init', array($this,'RouteActions'),2);
		
		/* You can attach as many functions as you want to a single action and Wordpress will run them in sequence.  If you want them to run in a different order then you add them, you can define the third parameter which indicated the priority. So my RouteActions function above will run after the SetLanguage action here, because the priority of SetLanguage is 3 (third priority) and RouteActions is 2 (second priority) the SetLanguage function sets up our plugins internationalization. More on this later! */
		add_action('init', array($this, 'SetLanguage'),1);
		
		/* The next two functions run when our plugin is activated or deactivated. In these functions we run the code necessary to install, and possibly uninstall our plugin when the user activates or deactivates it from the Plugins page. */
		register_activation_hook(__FILE__,array($this,'Activate'));
		
		// Deactivation function
		register_deactivation_hook(__FILE__,array($this,'Deactivate'));
		
		// This function runs on each page load to check if our plugin has been updated and, if so, what SQL functions we need to run to upgrade our plugin's tables to the most recent version
		add_action('init', array($this, 'Update'),1);
				
		/* Often times, our plugins need to attach new javascript files or css files to our page. WordPress provides a very clean mechanism for doing so that helps prevent us from needlessly including the same file multiple times (which would slow down your page load time). Scroll down to the StylesAndScripts function to see how to properly attach a JavaScript or CSS file. */
		add_action('wp_print_styles',array($this,'Styles'));
		add_action('wp_print_scripts',array($this,'Scripts'));
		
		/* Recently WordPress overhauled the Widgets API, making it easier to create multi-instance widgets. Creating a widget, and creating a Plugin are very similar processes. Sometimes a plugin will come with one or more widgets. I've separated the widget file from the main plugin file. Scroll down to the InitializeWidget function to see how to include and initialize a WordPress Widget. If you want to learn how to build a widget, open B2TemplateWidget.php */
		add_action('widgets_init', array($this,'InitializeWidget'));
		
		/* Actions can also take a fourth parameter which indicates how many arguments are going to be passed into the function. This is more commonly used with Filters so I'll discuss this more below. */
		
		/* 
		2.2 Filters
		Filters work similarly to actions, the main difference being that filters are used to modify some piece of data as it moves from one place to another. Filters usually accept an argument, perform some sort of modification of it, and then return the modified argument. They have priority levels as well, so we can alter the order in which they are applied. A complete list of filters can be found here: 
		
		http://codex.wordpress.org/Plugin_API/Filter_Reference
		
		 */
		 
		/* This filter lets us modify WordPress locale setting (i.e. en-US, fr-FR etc...), to override whatever WP_LANG is defined as in the wp-config.php file. I'm using this to demonstrate how internationalization works.  */
		add_filter( 'locale', array($this,'LanguageToggle'),1);
		
		
		/* This filter modifies the WordPress settings that are typically accessed using bloginfo(); You can see this filter has 4 parameters. The third we discussed as being the priority level, you can see I've set it to be very low priority to make sure it runs after other plugins have worked their magic. The four is the number of accepted arguments. Different filters accept a different number of arguments so you'll have to reference the codex to see if more arguments are available for the filter you're using. You can scroll down to the FilterBlogInfo action to see what arguments this filter passes in. I'm using this filter to prepend and append something to the WordPress site title, so you can change your blog from being called "My Boring Blog" to "Isn't My Boring Blog Just Awesome?". Useful, I know.*/
		add_filter('bloginfo', array($this,'FilterBlogInfo'),10,2);
		
		/* This filter takes in the content of a post, let's us work some magic on it and then spits returns it so it can be displayed to the user. This function is what I'm using to perform a find and replace on the post content. */
		add_filter('the_content', array($this,'FilterTheContent'),1);
		
		/* This hooks into the same content filter and adds some html code to the end of the post content that displays the form for submitting a Fact Check. */
		add_filter('the_content', array($this,'FactCheckForm'));
		
		/* As you can see, filters also occasionally contain variables. This filter lets us add a little link underneath our Plugins name on the Plugins page that links the user to the Plugins settings page. */
		add_filter("plugin_action_links_B2Template/B2Template.php", array($this, 'InsertSettingsLink')); 
		
		/* WordPress provides us with a good mechanism for keeping track of when things go wrong in our plugin. This might be some unknown error, or perhaps an invalid piece of user input. Remember that it is very important to handle bad user input and internal plugin problems, so that your users understand what went wrong and how to fix it. You can learn more abou the WP_Error object here: 
		
		http://codex.wordpress.org/Function_Reference/WP_Error
		
		*/
		
		$this->errors = new WP_Error();
		
	}
	
	/*****
		3.0 Plugin Activation and Deactivation
		Generally when you create a plugin, you'll need to do something to set it up once the user activates it. By attaching a function to the activate_ and deactivate_ WordPress actions, you are able to do just that. In my activate function, I am going to create to tables in our WordPress database. When you create a table, you should also create an option that will let you know in the future what version of the table was created. This will let you perform upgrades to the table, if you decide to change the structure later on to fix bugs or add new functionality.
		*****/	
		
	function Activate() 
	{
		/* When you use the global keyword to define a variable, what you are saying is that you want to be able to access a variable that exists outside of our class. The $wpdb variable in WordPress allows us to easily interact with the database that WordPress is installed on. To learn more about how to use the $wpdb Object visit here: http://codex.wordpress.org/Function_Reference/wpdb_Class */
		global $wpdb;
		
		/* These are the names of the tables I am creating. Notice that I've prepended the table names with $wpdb->prefix, this is the prefix that the user chose when first installing WordPress. You should be sure to add this to the beginning of any tables you create to prevent mixups if the user has multiple copies of WordPress installed in one database. */
		$words_table = $wpdb->prefix.'B2Template_Words';
		$facts_table = $wpdb->prefix.'B2Template_FactChecks';
		

		/* Here we double check to see if the table we are going to create already exists. If it does then we'll check to see if it needs upgrading with the function below. If the table doesn't exist then execute the query to create it for the first time. */
		if($wpdb->get_var('SHOW TABLES LIKE \'' . $words_table . '\';') != $words_table)
		{
			/* This is the SQL that creates the first table our plugin will use */
			$create_words_table_sql = 'CREATE TABLE ' . $words_table. ' (
						`id` mediumint(9) NOT NULL AUTO_INCREMENT,
						`find` VARCHAR(256) NOT NULL,
						`replace` VARCHAR(256) NOT NULL,
						UNIQUE KEY id (id)
				);';
				
			/* Passing the sql statement into the $wpdb->query() function executes the SQL */
			$wpdb->query($create_words_table_sql);
			
		}
		
		/* Lets do the same thing for our second table. */
		if($wpdb->get_var('SHOW TABLES LIKE \'' . $facts_table . '\';') != $facts_table)
		{
			$create_fact_checks_table_sql = 'CREATE TABLE ' . $facts_table. ' (
						`id` mediumint(9) NOT NULL AUTO_INCREMENT,
						`post_id` mediumint(9) NOT NULL,
						`fact` VARCHAR(256) NOT NULL,
						`comment` VARCHAR(256) NOT NULL,
						`source` VARCHAR(256) NOT NULL,
						UNIQUE KEY id (id)
				);';
			$wpdb->query($create_fact_checks_table_sql);
			
		}
		
		/* Now we store an option in Wordpress that tells us what version of the plugin's table structure is installed. This way, in the future we can easily run the SQL code needed to upgrade older versions of the plugin to the most recent. The add_option function let's us quickly insert a value into the WordPress options table.*/
		add_option( 'B2Template_db_version', self::DB_VERSION );
	}
	
	function Update()
	{
		
		/* In this line we check what version of the tables are current installed. The get_option function lets us quickly retreive a value that has stored in the WordPress options table. */
		$installed_ver = get_option( 'B2Template_db_version' );
		
		// If the installed db version and the current db version are the same, then theres no need to upgrade.
		if($installed_ver == self::DB_VERSION) return false;
		
		global $wpdb;
		
		$words_table = $wpdb->prefix.'B2Template_Words';
		$facts_table = $wpdb->prefix.'B2Template_FactChecks';
		
		// If we need to upgrade from version 1 to version 2, perform these queries
		if( $installed_ver < 2)
		{
			 /* From version 2 to version 3 I renamed a field called fieldOne to find, and added a new column called replace */
			$sql_rename_fieldOne= 'ALTER TABLE ' . $words_table.' CHANGE COLUMN `fieldOne` `find` VARCHAR(256) NOT NULL;';
			$wpdb->query($sql_rename_fieldOne);
			
			$sql_add_replace =  'ALTER TABLE ' . $words_table.' ADD `replace` VARCHAR(256) NOT NULL AFTER `find`;';
			$wpdb->query($sql_add_replace);
			
		}
		
		// If we need to upgrade from version 2 to version 4, perform these queries
		if( $installed_ver < 4)
		{
			 /* From version 3.0 to version 4.0 I created a second table called B2_FactChecks */
			$create_fact_checks_table_sql = 'CREATE TABLE ' . $facts_table. ' (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					post_id mediumint(9) NOT NULL,
					fact VARCHAR(256) NOT NULL,
					comment VARCHAR(256) NOT NULL,
					source VARCHAR(256) NOT NULL,
					UNIQUE KEY id (id)
			);';
			$wpdb->query($create_fact_checks_table_sql);
		}
		
		/* Now that these queries have been run, we know we have the most recent version of the database installed, so let's update our version number. The update_option function lets us quickly update an option that exists in the WordPress options table. If the option doesn't already exist, it will be created. */
		update_option( 'B2Template_db_version', self::DB_VERSION );

		
	}

	/* This function will run when the user deactivates the plugin from the WordPress Plugin screen. You might want to put some code in here to remove the tables and options that your plugin created, but, if you do that here, and the user reenables your plugin, they will lose all the data and settings they had previously set. Your alternative, is to create an uninstall.php file and place your cleanup code in there. This code is run when the user deletes your plugin. Open uninstall.php to see how this file works.  */	
	function Deactivate()
	{
		// Nothing to see here for now!
	}
	
	/*****
		4.0 Internationalization
		WordPress is used in many different languages, not just English, so if you code your plugin in English, you're leaving out potentially thousands of users who might find it useful. Now don't worry if you don't speak a hundred languages, you don't necessarily need to include every language in your plugin, just code it in a way that enables other individuals in the WordPress community to translate it for you. This is done by utilizing the Internationalization functions built in to WordPress.
		
		Remember, it's a lot easier to simply internationalize a Plugin while you're writing it, than it is to go through it at the end, so you might as well get used to doing it now. Even if you aren't planning on releasing the Plugin to the public, it's just a good idea and might save you a ton of time later.
	
		4.1 Using the Gettext Function
		Whenever you are outputting a piece of text to the user's screen, you need to wrap your text in a gettext function call. 
			
			This means turning: echo "I can only speak English"; 
			Into: echo __('I can speak any language in the world!');
			
			The __(); function takes the text you pass it, and if necessary, replaces it with the string's translation from an external language file. More on this in 4.3.
			
			Now we have one more small step to take to make sure that the translation files created for your plugin only affect the text in your plugin and not other plugins or the WordPress core. We need to add whats called a "textdomain" to our __(); function call, which is simply a unique identifier that tells us that this string, is part of this plugin and so the translation should come from this plugins language file, and not from anywhere else.
			
			So this:	echo __('I can speak any language in the world!');
			Becomes: echo __('I can speak any language in the world!','B2Template');
			
			Notice the second parameter, it's simply a unique string identifier. It doesn't necessarily have to be the name of your plugin, but why complicate things?
			
		4.2 Loading the Plugin textdomain
		Now that all our text is wrapped in gettext function calls, we need to load up our language file and tell Wordpress what textdomain identifies the language translations for this plugin. We do that with the function below. 		
		
		*****/
		
	function SetLanguage() {
		load_plugin_textdomain( 'B2Template', null, 'B2Template/lang' );
	}
	
	/* 
		load_plugin_textdomain simply tells WordPress the textdomain we set for our translation strings, and tells it where to look for our language files. The second parameter is deprecated since WordPress 2.6, so I'm not going to bother to use it here. The third parameter tells WordPress where our language files are located relative to the plugins directory. If you navigate to this directory in the plugin you can see the Spanish language file created for this plugin. The final step in the process is learning how to create these files.
		
		4.3 Creating language files
		If you look at the language files that exist in the lang folder of this Plugin, you'll see there are two files, one with the extension .mo and the other with the extension .po, but you'll notice that these files have the same name. The naming convention here is very important. Each file begins with our text domain (B2Template), is followed by a hypen (-) and then contains the localization code for the Spanish language. Take note that this localization code is for an ambiguous spanish, but we could narrow it down to Costa Rican spanish by renaming them to "B2Template-es_CR" or Spain spanish by renaming them to "B2Template-es_ES". A complete list of all possible language codes is available here: http://is.gd/iVJC2 and all possible country codes is available here: http://is.gd/iVJvL .
		
		So what are .mo and .po files and how do they work? The reason we have two files, is that the .po file is for editing (doing the actual translation work), and the .mo is an optimized file that WordPress is able to read quickly and easily, but would be too confusing for us to work with by hand. Now how do you create these files? Enter PoEdit.
		
		Poedit is cross-platform gettext catalogs (.po files) editor and can be downloaded from the following location
		http://www.poedit.net/
		
		Once you've downloaded PoEdit, open it up and go to File > New Catalog. Enter the details on the Project Info tab, noting that you likely what UTF-8 as the charset and the source code charset. You can leave the Plural Forms box blank.
		
		Click to the Paths tab, here we need to tell PoEdit what php files we need to search through to find our strings that require translation. We should make these paths relative, so that when someone else open's up the .po file on their computer, it will still point to the right place. So for base path enter "../", because we are going to save this file in B2Template/lang and we want it to search inside B2Template. Then under Paths, add an entry and simply type ".", because we want PoEdit to search all files and folders for strings to translate.
		
		Click to the Keywords tab, here we need to tell PoEdit what function signifies the Gettext function. WordPress denotes the Gettext function as __(), so we need to add "__" as an entry in this list. There is another Gettext function that looks like this _e(), which is basically a short form for echo __();, so if you use this function you need to add "_e" into the list as well.
		
		Click OK, now PoEdit will prompt you to save the file. Save it in B2Template/lang/, remembering our naming scheme. Let's make a French Canadian language file by saving it as B2Template-fr_CA.po
		
		Once you've saved it, if you've set the catalogue settings correctly, PoEdit will return all the strings you wrapped in __() or _e() function calles. Click OK to add them. Now all you need to do is click on each string, translate it in the box provided, and once you're done click save. The .mo file will auomatically be created.
		
		Now when your user sets WP_LANG to fr_CA in their wp-config.php file, your French Canadian translation will show up instead of the English ones...Voila!
		
		I'm using this function simply to demonstrate that the Spanish language switching option on the plugin page works. Normally you wouldn't need this, since the WordPress language setting is defined in wp-config.php under define('WP_LANG',''); This function just makes it easier for you to see the effect, without having to edit your wp-config.php file. */
	function LanguageToggle($lang) {

		  if ( strcmp(get_option('B2Template_plugin_language'),'es') == 0 ) {
		    return 'es';
		  } else {
		    return $lang;
		  }

	}


	/*****
		5.0 Inserting Administration page links
		If your plugin requires some configuration on the user's part, Wordpress makes it easy to add your own pages to the WordPress Administration menu. 
		*****/
	function InsertAdminMenuLink() {
		/* Using the add_menu_page and add_submenu_page functions we can add our own pages to the Administration menu. More information about this function can be found here: http://codex.wordpress.org/Adding_Administration_Menus  */
		
		$page = add_submenu_page( 
			'options-general.php' , // This is the parent page that we want to add our configuration page to
			__( 'B2 Template Plugin' , 'B2Template' ), // This will be the title of our configuration page
			__( 'B2 Template Plugin' , 'B2Template' ), // This will be the name of the link in the menu
			'edit_plugins' , // This is the minimum role that the user must have to be able to see and click on this link. You use this parameter to limit access to only certain WordPress users, i.e. authors cannot access the page but Administrators can. A complete list of user roles and capabilities can be found here: http://codex.wordpress.org/Roles_and_Capabilities
			'B2Template' , // This is a unique string used to identify your configuration page. The URI of this admin page will be options-general.php?page=B2Template. If you have multiple configuration pages, remember that they should all have unique identifiers
			array( $this , 'ConfigPageHtml' ) // This is the function that will be run to genereate the page when the user clicks on the link. 
		);
		
		// Add_submenu_page returns a unique identifier for our page. We can use that identifier in the hooks below so that these functions only run when the user visits our Administration page, and not other pages. This is helpful because we don't want to slow down the users loading time by including stylesheets and javascript across the entire WordPress admin, when it's only being used on our page.
		add_action( 'admin_print_scripts-'.$page , array( $this, 'AdminScripts' ) );
		add_action( 'admin_print_styles-'.$page , array( $this, 'AdminStyles' ) );
		
		// WordPress requires us to "register" our plugins settings before we can let the user update them. This is a security feature to prevent unauthorized addition of settings. 
		add_action( 'admin_init' , array( $this,'RegisterAdminSettings' ) );
		
	}
	
	/*****
		5.1 Registering Settings
		This is how we register settings so that WordPress recognizes them and saves them to the database when the user updates them.
		*****/
	function RegisterAdminSettings()
	{
		/* The first parameter is the name of the Settings group that is going to be saved. As you can see I have two separate groups of settings that can be saved independently. The second parameter is the name of the setting you want to save. Notice that I've added the plugin name to the beginning of the setting to avoid a naming conflict in the database.*/
		
		register_setting('B2TemplateSettings', 'B2Template_site_title_prepend');
		register_setting('B2TemplateSettings', 'B2Template_site_title_append');
		
		register_setting('B2TemplateLanguage', 'B2Template_plugin_language');
		
	}
	
	/*****
		5.2 Loading the page
		This is the callback function that we defined above to run when the user clicks on the link to get to our plugin's configuration page. You could simple us an echo statement in this function to output all of your html here, but in order to keep things a little cleaner and more organized, I use the following method to retrieve the html from an external file.
		*****/
		
	function ConfigPageHtml() {
		$content = '';
		ob_start(); // This function begins output buffering, this means that any echo or output that is generated after this function will be captured by php instead of being sent directly to the user.
			require_once('html/config.php'); // This function includes my configuration page html. Open the file html/config.php to see how to format a configuration page to save options.
			$content = ob_get_contents(); // This function takes all the html retreived from html/config.php and stores it in the variable $content
		ob_end_clean(); // This function ends the output buffering
		
		echo $content; // Now I simply echo the content out to the user
	
	}
	
	/*****
		5.3 Loading JavaScript and CSS into a configuration page
		WordPress provides us with some functions that allow us to add JS and CSS files to the page header in a way that helps reduce the chances that files will be included multiple times. You can imagine if you have three plugins that use jQuery, your page would take a performance hit if three instances of the library were being included on every page.
		*****/	
	function AdminScripts()
	{
		/* wp_enqueue_script allows us to include JavaScript files.  */
		wp_enqueue_script(
			'B2Template_Admin', // This is a unique identifier for this .js file. This is how WordPress can tell if a .js file has already been loaded.
			WP_PLUGIN_URL.'/B2Template/js/admin.js', // This is the path to the .js file
			array('jquery') // This is an array of JavaScript files that this .js file depends on. If these files are not loaded, then WordPress will make sure to load them before your .js file. My file depends on jquery, so by entering the identifier "jquery" here, it will automatically be included. There's no need for me to include the jquery files in my plugin, because WordPress already includes the library. For a complete list of scripts available in WordPress can be found here: http://codex.wordpress.org/Function_Reference/wp_enqueue_script
		);
		
		/* wp_localize_script allows us to pass some variables that need to be generated with PHP into our JavaScript. For example, any text we use in our Javascript file needs to be run through our gettext function so that it can be internationalized (see chapter 4.0). Also for security purposes, when we use AJAX we need to provide our .js file with a security code, called an "nonce", to ensure that the AJAX request is coming from a valid source. We pass this variable to our JS file using the wp_localize_script function as well. */
		wp_localize_script(
			'B2Template_Admin', // This is the unique identifier of the Javascript file that that we are localizing. This should match the unique identifier above in wp_enqueue_script
			'B2Template', // This is the name of the object that is going to store all the variables that we set in the next parameter. So in our Javascript file, the first setting below can be accessed using B2Template.DeleteFindAndReplaceWord
			array( // This is an array of variables that we want to send to our Javascript file. The first two are nonce security keys that we'll use in our AJAX functions to make sure the request are legit.
				// This is an error message that we use in our JavaScript file, notice that it's wrapped in the  __() function, so that if we translate our plugin into another langauge, this error message will also be translated.
				'AjaxError'=>__('An error occurred. Please try your action again. If the problem persists please contact the plugin developer.','B2Template')
			)
		);
	}
	
	function AdminStyles()
	{
		/* wp_enqueue_style lets us add CSS files to our plugin the same way we do with JavaScript files */
		wp_enqueue_style(
			'B2Template_Admin', // Unique identifier
			WP_PLUGIN_URL.'/B2Template/css/admin.css' // Path to the CSS file
			// Another parameter could be added here to list dependant CSS files (e.g. a CSS Framework)
		);
	}
	
	/*****
		5.4 Settings link on Plugins page
		This function is a filter on the links that appear below the B2Template row of the WordPress Plugin Page. This filter lets us add or own links to this list. I'm using it to provide users with a shortcut to the Plugin's settings page, but it could be used for anything (e.g. to insert a donate link, or link to Plugin suppert forums). The function takes an array of links, adds our link to the array and then returns the modified array.
		*****/
	function InsertSettingsLink($links)
	{
		$settings_link = '<a href="options-general.php?page=B2Template">'.__('Settings','B2Template').'</a>'; 
		 array_unshift( $links, $settings_link ); 
		 return $links; 
	}
	
	/*****
		6.0 Using Filters and Actions
		Your plugin might need to add forms, or other content to the viewer facing side of Wordpress. There are many different ways to do this, but the most convenient may be simply to simply use the the_content filter to replace or append your html to the content of a WordPress post or page. You can also look into using Short Codes to embed content into WordPress. This will be demonstrated in a future version of this template. More information on Short Codes can be found here: http://codex.wordpress.org/Shortcode_API
		*****/
	
	/*****
	6.1 Filter to adding content to a WordPress Post
	This function filters the_content of a WordPress Post. We receive the $content parameter, add our own HTML form to the end of it and return it to the user 
		*****/
	function FactCheckForm($content)
	{
	
		ob_start();
			require('html/fact_check.php'); // Scroll up to ConfigPageHtml() to see how to use output buffering (ob_) to store the contents of a file in a variable
			$content .= ob_get_contents();
		ob_end_clean();
		return $content;
	
	}
	
	/*****
	6.2 Action to add JavaScript and CSS to viewer facing pages
	This function adds JavaScript and CSS to the viewer facing side of WordPress. Scroll up to AdminStyles and AdminScripts() to see how these functions work 
		*****/
	function Styles()
	{		
		wp_enqueue_style('B2Template_SubmitForm',WP_PLUGIN_URL.'/B2Template/css/fact_check.css');
	}
	
	function Scripts()
	{
		wp_enqueue_script('B2Template_SubmitForm',WP_PLUGIN_URL.'/B2Template/js/fact_check.js',array('jquery'));
		wp_localize_script('B2Template_SubmitForm','B2Template',
			array(
				'AjaxError'=>__('An error occurred. Please try your action again. If the problem persists please contact the plugin developer.','B2Template')
			));
	}
	
	
	/*****
		6.3 Filter to wrap text around the Wordpress site title
		This function filters the bloginfo() function, allowing you to edit the values before they are returned to the user. This function adds the text that the user specified in the Plugin Settings page, before and after the the WordPress site title
		*****/
	function FilterBlogInfo($option_value, $option_name)
	{
		// This function only deals with $bloginfo('name'), so if it's not the name option, leave the function
		if(strcmp($option_name, 'name') != 0) return $option_value;
		
		// Wrap the option_value in B2Template_site_title_prepend and B2Template_site_title_append
		$new_option_value = get_option( 'B2Template_site_title_prepend' ).' ';
		$new_option_value .= $option_value;
		$new_option_value .= ' '.get_option( 'B2Template_site_title_append' );
		
		// Return the new value
		return $new_option_value;
	}

	/*****
	6.4 Filter the content to apply the Find and Replace rules 
		This function filters the the_content() function, allowing you to edit the post content before it is returned to the user. This function executes a string replace on the content to apply the find and replace rules that the user specified in the Plugin Settings page.
		*****/
	function FilterTheContent($content) {
	
		// Get the list of find and replace rules from the database
		$rules = $this->GetFindAndReplaceWords();
		if(is_array($rules)) // If there are rules then go through each one and perform the find and replace on the content
		{
			foreach($rules as $rule)
			{
				
				$content = str_replace(stripslashes($rule['find']), stripslashes($rule['replace']), $content);
			}
		}
		
		// Return the modified content
		return $content;
	}
	
	/*****
		7.0 Executing Actions with and without Ajax
		
		WordPress makes it very easy to enhance your plugins with Ajax. If you haven't used Ajax before, please read up about it before continuing. A good resource for learning Ajax concepts can be found here: http://www.w3schools.com/ajax/default.asp
		
		When I develop plugins, I try to make the plugin work on its own first, and then incorporate JavaScript to "Ajax-ify" it. This way, the plugin will still work if the user has JavaScript disabled. This isn't always essential, so evaluate who you think will be using your plugin, to decide if this is worth your time.
		
		The WordPress Codex provides two separate methods of performing Ajax, one for viewer facing pages, and one for administrator facing pages. The method I demonstrate here simplifies this a bit, because the same method works both on viewer facing pages and administrator facing pages.
	*****/
	
	/*****
		7.1 Routing Actions
		Often a plugin will need to trigger an action when the user does something, such as saving data to the database when the user submits a form. One plugin may have many actions, so you need a way to tell your plugin what action to take based on the action the user has performed. This is accomplished by setting a POST variable that specifies the action that should take place.  e.g. AddFindAndReplaceWord, DeleteFindAndReplaceWord. The following function, RouteActions(), is run when WordPress initializes, and execute the necessary functions required to accomplish the desired action.
		
		In this plugin, AddFindAndReplaceWord and DeleteFindAndReplaceWord can be executed via a form post, or an ajax post. If it was submitted with a form, we simply need to perform the action, and return a message to let the user know if it worked or not. If it was submitted via ajax, then we also need to regenerate the Rule List HTML, and pass it to our JavaScript function so that the user sees the updated rule list. 
		
		The method I use below routes the form actions and then, if the post was submitted via ajax, routes another action to generate the html that needs to be updated. To trigger the action via a form, I post the form to options-general?page=B2Template&B2Template_Action=ACTION_NAME_HERE.  To trigger the action via ajax, I add the variable B2Template_Ajax=ACTION_NAME_HERE to the post. See html/config.php and js/admin.js respectively to see an example of this.
		
		Note that I prepend B2Template_ to B2Template_Action and B2Template_Ajax, to avoid a naming conflict and inadvertantely triggering actions in other plugins.
		*****/
	
	function RouteActions()
	{
		
		if(!isset($_REQUEST['B2Template_Action'])) return false;
		$action = $_REQUEST['B2Template_Action'];
		
		if($action) // This code verifies the nonce value that was sent with the action. This is  avery important check that ensures that a malicious user cannot use your plugin to compromise the WordPress installation. Without this check, your plugin introduces a serious security vulnerability to whomever uses the plugin.
		{
			check_admin_referer($action);
		}
		
		// Pass the action name to our function that executes the actions
		$result = $this->DoAction($action);
		
		// If it was an Ajax call, then pass our action to the function that will generate the updated html
		if($this->IsAjax())
		{
			$this->AjaxResponse($action);
		}
		
	}
	
	function DoAction($action)
	{
		
		$result = false;
		
		switch($action) // Check which action was requested, and send the required POST variables to the function to make it happen
		{
			case 'AddFindAndReplaceWord':
				$result = $this->AddFindAndReplaceWord($_POST['B2Template_Find'], $_POST['B2Template_Replace']);
			break;
			case 'DeleteFindAndReplaceWord':
				$result = $this->DeleteFindAndReplaceWord($_POST['B2Template_Id']);
			break;
			case 'AddFactCheck':
				$result = $this->AddFactCheck($_POST['B2Template_PostId'], $_POST['B2Template_Fact'], $_POST['B2Template_Comment'], $_POST['B2Template_Source']);
			break;
		}
		
		return $result;
		
	}
	
	function AjaxResponse($action)
	{
		// This object will contain all the data we need to pass to our JavaScript file to update the page. This PHP variable will be converted into JavaScript Object Notation (JSON) so that our JavaScript file can use this variable.
		$data = array();
		
		/* If errors were triggered get them and add them to the error element of data array*/
		if($this->errors->get_error_message())
		{
			$data['error'] = $this->errors->get_error_message();
		}
		elseif($this->success) /* If no errors were found, and a success message was set, add it to the success element of the data array */
		{
			$data['success'] = $this->success;
		}
		
		/* Check which action was requested, and send the required POST variable to the function in order to get the updated HTML elements that we need to update */
		switch($action)
		{
			case 'AddFindAndReplaceWord':
				$data['html'] = $this->RuleListHtml();
			break;
			case 'DeleteFindAndReplaceWord':
				$data['html'] = $this->RuleListHtml();
			break;
			case 'AddFactCheck':
				$data['html'] = $this->FactCheckListHtml($_POST['B2Template_PostId']);
			break;
		}
		
		/* Die here to stop WordPress from returning a page, instead we want it to just return the $data variable in JSON format, so our jQuery function can use it. See js/admin.js or js/fact_check.js to see how we use this variable to update the page. */
		die(json_encode($data));
		
	}
	
	function IsAjax()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == "XMLHttpRequest")
		{
			return true;
		}
		return false;
	}

	/*****	
	
		7.2 Dynamic Ajax Elements
		With Ajax there is generally an element on the page that gets updated once the user performs an action. In the case of this template plugin, these are the Rule List in the Plugin Settings page and the Fact Check List at the bottom of each post on the viewer facing pages. I save these elements in their own subfolder called ajax_elements under the html heading, and then create functions that perform all the logic neccessary to render the element, and return the html. I use this function when the page is initially generated (as you can see for example in html/config.php). Then when the Ajax action is performed, I rerun this function to get an updated version of these elements, and then use Javascript to update the page.
		*****/
	
	
	/* This function generates the html that disables the Rule List. */
	function RuleListHtml()
	{
		$content = '';
		
		$rules = $this->GetFindAndReplaceWords(); // Call the database function to get all the Find and Replace Words from the database
		
		if($rules) // If there are rules, then we need to generate the list of rules and return it
		{
			ob_start(); // Scroll up to ConfigPageHtml() to see how to use output buffering (ob_) to store the contents of a file in a variable
				require_once('html/ajax_elements/rule_list.php');
				$content = ob_get_contents();
			ob_end_clean();
		}
		
		return $content;
	}
	
	/* This function is identical to the one above, but is instead used to generate the fact check list visible below each post.  */
	function FactCheckListHtml($post_id)
	{
		
		$facts = $this->GetFactChecksByPost($post_id); // Call the database function to get all the Fact Checks for the specified post from the database.
		
		ob_start();
			require('html/ajax_elements/fact_list.php');
			$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	
	}
	

	
	/*****
		9.0 Initializing Widgets
		For WordPress 2.8 and above, Widgets are created by creating a simple class structure. To add a widget to your plugin, simply include the file and register the widget. To see an example of how to create a widget open B2TemplateWidget.php
		
		More information on developing widgets can be found here: http://codex.wordpress.org/Widgets_API#Developing_Widgets_on_2.8.2B
		
		Since this Widget is attached to a plugin, we are initalizing the Widget within the Plugin. This needs to be done during the widgets_init action. If you want to develop a standalone Widget (i.e. not a widget that is part of a larger Plugin), see the note at the top of the B2TemplateWidget.php file.
		*****/
	function InitializeWidget()
	{
		require_once('B2TemplateWidget.php'); // Include the widget file
		register_widget('B2TemplateWidget'); // Register the widget 'B2TemplateWidget' is the class name of the widget.
	}


	/*****
		10.0 Database Interaction
		By now, you've already been introduced to the global $wpdb; object. This object provides many useful functions for adding data to, and retreiving data from the database. The following functions deal with saving, retreiving or deleting database records. It is good form to have a separate dedicated functions that performs one single task of adding, deleting or retreiving values from the database instead of mixing sql statements throughout your code. If you are building a very complex plugin, you may want to strucutre your plugin in MVC (Model - View - Controller) format. Here is a tutorial to get your started on this: http://is.gd/create.php
		*****/

	/* This function takes a find and replace value and inserts it in the B2Template_Words table. */
	function AddFindAndReplaceWord($find, $replace) {
		
		global $wpdb;
		
		$result = false; // Assume our result failed by default
		
		if(empty($find)) // If the $find parameter is empty, we can't save the rule, so report an error and return false
		{
			// This line registers a new error with our WP_Error object.
			
			$this->errors->add(
				self::ERROR_FIND_EMPTY, // This is the error code 0, see the class constant definition at the top of the plugin
				__('The find value was not set. Enter the text you would like to have replaced then try again.','B2Template') // This is the error message
			);
			return false;
		}
		
		/* Next we need to check that the "find" value that was provided doesn't already exist in the database, since it would be pointless to have two rules to replace the same word.
			
			This function uses $wpdb->prepare(), which is a very important function that sanitizes the database input to prevent mysql injection hacks. What's important to remember here, is that you should never directly plug form values directly into your mysql query. Let's say we have this query:
			
			$sql = 'DELETE * FROM important_data WHERE id = '.$_POST['delete_id'];
			
			That is a correct sql statement, but consider what would happen if a malicious user altered your form so that $_POST['delete_id'] was equal to: "id"... When then the query that you would execute would be:
			
			'DELETE * FROM important_data WHERE id = id'
			
			Goodbye important_data!
			
			Fortunately $wpdb->prepare ensures that the users content is stripped of anything that could alter your mysql query. The first parameter is the sql query you want to execute, where you use placeholders to signify the user data you want to add into your query. In the query below the placeholder is the %s. There are several placeholders you can use:
			
			%s is a placeholder for a string "Hello World!"
			%d is a placeholder for an integer 53
			%f is a placeholder for a float 52.99
			
			After the first placeholder you simply pass in values you want substituted in for the placeholders, in the same order that the placeholders appear in the SQL statement.
			
			Here's another example:
			
			$sql = $wpdb->prepare('UPDATE my_table SET name=%s, birthday=%s, height=%f WHERE id=%d;', $name, $birthday, $height, $id);
			
			
		*/
		$sql = $wpdb->prepare('SELECT COUNT(`id`) FROM '.$wpdb->prefix.'B2Template_Words WHERE STRCMP(BINARY `find`,%s) = 0 LIMIT 1;',$find);
		
		$exists = $wpdb->get_var($sql); // $wpdb->get_var returns a single value from the database. In this case 1 if the find term exists and 0 if it does not.
		
		if($exists != 0) // If the find value already exists, then throw an error and return false
		{
			$this->errors->add(self::ERROR_DUPLICATE_RULE, sprintf(__('A replace rule for that term already exists. Delete the existing rule for "%s" and try again, or create a different rule.','B2Template'),stripslashes($find)));
			return false;
		}
		
		// Otherwise we are good to go! Let's build the sql to add it into the database.
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'B2Template_Words(`find`, `replace`) VALUES(%s,%s);', $find, $replace);
		
		$result =	$wpdb->query( $sql );
		
		if($result) // If $result is true the database add worked!
		{
			$this->success = __('Find and Replace rule has been added.','B2Template');
		}
		else // If its false we probably had a mysql error.
		{
			$this->errors->add(self::ERROR_MYSQL, __('There was a problem updating the database. Try your action again, and if it continues to fail contact the plugin developer', 'B2Template'));
		}
		
		return $result; // Return the result
	
	}

	/* This function deletes a find and replace rule from the database */
	function DeleteFindAndReplaceWord($id) {
		global $wpdb;
		
		if (!empty($id)) { // If we are given an id to delete
		
			$sql = $wpdb->prepare('DELETE FROM ' . $wpdb->prefix.'B2Template_Words WHERE id = %s;', $id);
			$result = $wpdb->query( $sql ); // Delete the find and replace rule
			
			if($result) // If $result is true the database delete worked!
			{
				$this->success = __('Find and Replace rule has been removed.','B2Template');
			}
			else // If its false we probably had a mysql error.
			{
				$this->errors->add(self::ERROR_MYSQL, __('There was a problem updating the database. Try your action again, and if it continues to fail contact the plugin developer', 'B2Template'));
			}
		}
		
		return $result;  // Return the result
	
	}

	/* This function gets all the find and replace words in the database */
	function GetFindAndReplaceWords() {
	
		global $wpdb;
		$sql = 'SELECT `id`,`find`,`replace` FROM '.$wpdb->prefix.'B2Template_Words;';
		$result = $wpdb->get_results( $sql , ARRAY_A ); // Get all the find and replace rules from the database
		return $result;
		
	}
	
	/* This function adds a fact check to the database */
	function AddFactCheck($post_id, $fact, $comment, $source) {
	
		global $wpdb;
		
		$this->updated_post = $post_id; // Remember which post we updated, since we might be displaying more then one post at a time and we want to make sure we add the error or success message next to the correct one.
		
		if(empty($post_id)  || empty($fact) || empty($comment) || empty($source)) // If we weren't given all the required fields throw an error and return false
		{
			$this->errors->add(self::ERROR_MISSING_FIELDS,__('All fields are required, please fill in the missing fields and submit your fact check again.','B2Template')); 
			return false;
		}
		
		// Otherwise insert the Fact Check into the database
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'B2Template_FactChecks(`post_id`, `fact`,`comment`,`source`) VALUES(%d,%s,%s,%s);', $post_id, $fact, $comment, $source);
		$result =	$wpdb->query( $sql );
		
		if($result) // If $result is true then our database add worked!
		{
			$this->success = __('Your fact check has been reported.','B2Template');
		}
		else // If its false we probably had a mysql error.
		{
			$this->errors->add(self::ERROR_MYSQL, __('There was a problem updating the database. Try your action again, and if it continues to fail contact the plugin developer', 'B2Template'));
		}
		
		return $result;  // Return the result
	
	}
	
	/* This function gets all the fact checks for the given post */
	function GetFactChecksByPost($post_id) {
		
		global $wpdb;
		
		$sql = $wpdb->prepare('SELECT `post_id`,`fact`,`comment`,`source` FROM '.$wpdb->prefix.'B2Template_FactChecks WHERE `post_id` = %d;', $post_id);
		$result = $wpdb->get_results( $sql, ARRAY_A); // Get all the fact checks for the given post
		return $result; // Return the result
	}

}
	
?>