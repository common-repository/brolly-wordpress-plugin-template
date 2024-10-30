<?php
/* First we check if WP_UNINSTALL_PLUGIN is set to true. WordPress sets this to true when the user chooses to delete the plugin. Doing this check prevents a malicious user from calling the file directly and uninstalling your plugin without the consent of the Administrator. */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

/* Keep the uninstall instructions inside a function. Make sure you prefix the function name with your unique plugin name to avoid naming conflicts. */
function B2Template_Uninstall()
{	
	global $wpdb;
	
	/* With these lines we remove the database tables */
	$sql = 'DROP TABLE IF EXISTS '. $wpdb->prefix.'B2Template_FactChecks;';
	$wpdb->query( $sql );
	
	$sql = 'DROP TABLE IF EXISTS '. $wpdb->prefix.'B2Template_Words;';
	$wpdb->query( $sql );
	
	/* With these lines we delete the options */
	delete_option('B2Template_site_title_append');
	delete_option('B2Template_site_title_prepend');
	delete_option('B2Template_db_version');
	delete_option('B2Template_plugin_language');
}

/* Call the function to uninstall */
B2Template_Uninstall();

?>