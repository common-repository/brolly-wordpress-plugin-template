<?php 
/***** 
	This file generates the Plugin Settings page. When creating a Settings page, try to use similar markup and classes as the rest of the WordPress Administration pages. By doing this, your Plugin page feels similar to the WordPress core Administration pages.

*****/
?>

<div class="wrap"> <!-- WordPress Admin pages begin with this div -->

	<form method="post" action="options.php"> <!-- In order to save the options we post to this page -->
	
		<?php
			
			settings_fields( 'B2TemplateSettings' ); // This function automatically adds all the hidden fields we need to save all the options in each option group. This form is for saving the B2TemplateSettings option group. Make sure you put this somewhere between the <form> and </form> tags.
			
		?>
		
		<!-- Here we have the title and instructions for the form. Notice that it has been wrapped in a gettext function for internationalization -->
		<h2><?php _e('Wrap Site Title (Saving Plugin Settings Demo)', 'B2Template' ); ?></h2>
		
		<p><?php _e('This demonstrates how to register plugin settings and submit them. When set, the values below will be prepended and appended to the WordPress site title.','B2Template'); ?></p>
		
		<!-- This table holds the labels and input boxes for saving the plugin settings. The important thing here is for the "name" attribute of each input to match the name we chose in the RegisterAdminSettings function. Also notice that we fill in the value for each field  that has been saved by calling get_option('option_name'); -->
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="B2Template_site_title_prepend"><?php _e('Prepend Site Title With', 'B2Template'); ?></label>
					</th>
					<td>
						<input type="text" id="B2Template_site_title_prepend" name="B2Template_site_title_prepend" value="<?php echo get_option('B2Template_site_title_prepend'); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="B2Template_site_title_append"><?php _e('Append Site Title With', 'B2Template'); ?></label>
					</th>
					<td>
						<input type="text" id="B2Template_site_title_append" name="B2Template_site_title_append" value="<?php echo get_option('B2Template_site_title_append'); ?>" />
					</td>
				</tr>
			</tbody>
		</table>
		
		<!-- This is our submit button -->
		<p class="submit">
			<input type="submit" class="button-primary" name="Submit" value="<?php _e("Save Changes", 'B2Template'); ?>" />
		</p>
		
	</form>
	
	
	<!-- The title and description for the Ajax Demonstration form. Notice that even though this form uses Ajax to submit, it will still submit properly if JavaScript is disabled. Also notice that there is no JavaScript visible in this form either. I find it best to avoid cluttering your HTML with Javascript by using jQuery to bind actions and events to your html controls. Look at js/admin.js to see how to do this and how to submit forms using Ajax. -->
	<h2><?php _e('Content Find and Replace Rules (Ajax Demo)', "B2Template") ?></h2>
	
	<p><?php _e("This demonstrates how to save values to a database table. This form uses Ajax to submit, but degrades gracefully when the user disables javascript. When added, the rules below will find and replace text values in WordPress posts.",'B2Template'); ?></p>
	
<!-- 	Notice here we use the function wp_nonce_url to generate the security key that tells WordPress that the form posting was legit, i.e. it came from the WordPress Administration page and not from some malicious source. This is not needed when we are saving options (link in the form below and above) because the settings_field function adds the nonce to the form for us.  -->
	
	<form id="B2Template_AddFindAndReplaceWord" method="post" action="<?php echo wp_nonce_url('options-general.php?page=B2Template&amp;B2Template_Action=AddFindAndReplaceWord','AddFindAndReplaceWord'); ?>#B2Template_AddFindAndReplaceWord"> <!-- Quick tip: Adding a named anchor that references the id of the form that is being submitted is useful here, because once the user submits the form, the page will jump down to the form they just submitted so they can see that their changes have taken effect, and read the Feedback message -->
	
		<h3><?php _e('Add a Rule','B2Template') ?></h3>
		
		<!-- This div holds the feedback message that we will print to the user after they've submitted their form. Remember to always provide clear feedback to your users so they know if their action was successful, and if not, how to fix it. By using class="error" and class="updated" we show the user error and success messages that are similar to what they see in other WordPress pages  -->
		<div id="B2Template_Feedback">
			<?php
				if($error = $this->errors->get_error_message())
				{
					echo '<div class="error"><p>'.$error.'</p></div>';
				}
				if($success = $this->success)
				{
					echo '<div class="updated"><p>'.$success.'</p></div>';
				}
			?>
		</div>
		
		<!-- This is the form for submitting the Find and Replace rules -->
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="B2Template_Find"><?php _e('Find', 'B2Template') ?></label>
					</th>
					<td>
						<input type="text" id="B2Template_Find" name="B2Template_Find" value="" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="B2Template_Replace"><?php _e('Replace', 'B2Template') ?></label>
					</th>
					<td>
						<input type="text" id="B2Template_Replace" name="B2Template_Replace" value="" />
					</td>
				</tr>
			</tbody>
		</table>	
			
		<!-- This is our submit button -->
		<p class="submit">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e("Add Find and Replace Rule", 'B2Template') ?>" />
		</p>
	</form>
	
	<h3><?php _e('Rules','B2Template'); ?></h3>
	
	<!-- This div displays all the rules that have already been added to database. Since I want this section of the page to update on each Ajax call, I've stuck it in it's own file under html/ajax_elements, and I include it by calling the function RuleListHtml(). It's important to wrap this page inside an enclosing div, (e.g. B2Template_RuleList), so that when we run our Ajax function, and regenerate RuleListHtml, we can easily use jquery to replace the html inside this div with the updated html. More on this in js/admin.js -->
	
	<div id="B2Template_RuleList">
		<?php echo $this->RuleListHtml(); ?>
	</div>	
	
	<!-- This form is the same as the first one, except it saves the settings for the B2TemplateLanguage setting group. -->
	<form method="post" action="options.php">
		<?php
			
			settings_fields( 'B2TemplateLanguage' );
			
		?>
		
		<h2><?php _e('Plugin Language (Internationalization Demo)', 'B2Template' ); ?></h2>
		<p><?php _e('This demonstrates the use of language files for internationalizing a plugin.','B2Template'); ?></p>
		
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="B2Template_plugin_language"><?php _e('Ver plugin en Espa&ntilde;ol', 'B2Template'); ?></label>
					</th>
					<td>
						<!-- This demonstrates how to use a checkbox in your WordPress Plugin settings. If the value of B2Template_plugin_language is equal to "es", then the checkbox has been selected and we output checked="checked" so it displays as such. If the user deselects this checkbox and submits the form, the value of get_option('B2Template_plugin_language') will be NULL. -->
						<input type="checkbox" id="B2Template_plugin_language" name="B2Template_plugin_language" value="es" <?php echo (strcmp(get_option('B2Template_plugin_language'),'es') == 0) ? 'checked="checked"' : ''; ?> />
					</td>
				</tr>
			</tbody>
		</table>
		
		<p class="submit">
			<input type="submit" class="button-primary" name="Submit" value="<?php _e("Save Changes", 'B2Template'); ?>" />
		</p>
		
	</form>


</div>
