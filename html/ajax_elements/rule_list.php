<?php
/* This is an AJAX element that displays all the Find and Replace rules returned from the database, and provides a form that lets the user delete each rule from the list. */
?>

<ul>

<?php
	foreach($rules as $rule)
	{
?>
	<li>
		<form method="post" action="<?php echo wp_nonce_url('options-general.php?page=B2Template&amp;B2Template_Action=DeleteFindAndReplaceWord','DeleteFindAndReplaceWord'); ?>#B2Template_AddFindAndReplaceWord">
			<?php printf(__('Find the word %s"%s"%s and replace with %s"%s"%s','B2Template'),'<strong>',stripslashes($rule['find']),'</strong>','<strong>',stripslashes($rule['replace']),'</strong>'); ?>
			<input type="hidden" name="B2Template_Id" value="<?php echo $rule['id']; ?>">
			<input class="button-secondary" type="submit" name="Submit" value="<?php _e("Delete Rule", 'B2Template') ?>" />
		</form>
	</li>
<?php
	}
?>
</ul>