	<?php 
		global $post; // this is the global $post variable, we can get the current post's ID with this. 
		
		/* Notice that all html element id's in this form are appended with $post->ID. Because there may be multiple instances of this form on one page, we need to make sure that all ID's are unique. Using $post->ID is a good way to do this. Names may be duplicate. */
	 ?>
	 
	 	<!-- Here we have the title and instructions for the form. Notice that it has been wrapped in a gettext function for internationalization -->
	<h2><?php _e('Fact Check My Post', "B2Template") ?></h2>
	
	<!-- 	Notice here we use the function wp_nonce_url to generate the security key that tells WordPress that the form posting was legit, i.e. it came from the WordPress Administration page and not from some malicious source. This is not needed when we are saving options (link in the form below and above) because the settings_field function adds the nonce to the form for us.  -->
	<form id="B2Template_FactCheck_<?php echo $post->ID ?>" class="B2Template_FactCheck" method="post" action="<?php echo wp_nonce_url('?B2Template_Action=AddFactCheck','AddFactCheck'); ?>#B2Template_FactCheck_<?php echo $post->ID ?>"><!-- Quick tip: Adding a named anchor that references the id of the form that is being submitted is useful here, because once the user submits the form, the page will jump down to the form they just submitted so they can see that their changes have taken effect, and read the Feedback message -->

	
	<h3><?php _e('Think something I\'ve said is factually wrong? Report it here.','B2Template') ?></h3>
	 
	 <!-- This div holds the feedback message that we will print to the user after they've submitted their form. Remember to always provide clear feedback to your users so they know if their action was successful, and if not, how to fix it. By using class="error" and class="updated" we show the user error and success messages that are similar to what they see in other WordPress pages  -->
	<div id="B2Template_Feedback_<?php echo $post->ID ?>" class="B2Template_Feedback">
		<?php
			if($this->updated_post == $post->ID)
			{
				if($error = $this->errors->get_error_message())
				{
					echo '<div class="error"><p>'.$error.'</p></div>';
				}
	
				if($success = $this->success)
				{
					echo '<div class="updated"><p>'.$success.'</p></div>';
				}
			}
		?>
	</div>
	
	<!-- This is the form for submitting the Find and Replace rules -->
	<p>
		<label for="B2Template_Fact_<?php echo $post->ID ?>"><?php _e('Fact', 'B2Template') ?></label>
		<input type="text" id="B2Template_Fact_<?php echo $post->ID ?>"  name="B2Template_Fact" value="" />
	</p>
	<p>
		<label for="B2Template_Comment_<?php echo $post->ID ?>"><?php _e('Comment', 'B2Template') ?></label>
		<input type="text" id="B2Template_Comment_<?php echo $post->ID ?>" name="B2Template_Comment" value="" />
	</p>
	<p>
		<label for="B2Template_Source_<?php echo $post->ID ?>"><?php _e('Source', 'B2Template') ?></label>
		<input type="text" id="B2Template_Source_<?php echo $post->ID ?>" name="B2Template_Source" value="" />
	</p>
	<p>
	
		<!-- This hidden field holds the $post->ID, so that our PHP script knows which post the Fact Check applies to -->
		<input type="hidden" id="B2Template_PostId_<?php echo $post->ID ?>"  name="B2Template_PostId" value="<?php echo $post->ID ?>" />
		
		<!-- This is our submit button -->
		<input class="button" type="submit" name="Submit" value="<?php _e("Submit Fact Check", 'B2Template') ?>" />
	</p>
	
</form>

<!-- This div displays all the Fact Checks that have already been added to database. Since I want this section of the page to update on each Ajax call, I've stuck it in it's own file under html/ajax_elements, and I include it by calling the function FactCheckListHtml(). It's important to wrap this page inside an enclosing div, (e.g. B2Template_FactCheckList_), so that when we run our Ajax function, and regenerate FactCheckListHtml, we can easily use jquery to replace the html inside this div with the updated html. More on this in js/fact_check.js -->
<div id="B2Template_FactCheckList_<?php echo $post->ID ?>">
	<?php
		echo $this->FactCheckListHtml($post->ID);
	?>
</div>