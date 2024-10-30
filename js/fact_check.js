/* 

Please see admin.js to learn how these functions work.

*/
jQuery(document).ready(function($) {
	
	// When a FactCheck form is submitted, perform the AJAX request
	$('.B2Template_FactCheck').submit(function()
	{
		
		// We need to remember which post was being fact checked so we can show the feedback in the correct posts div. There is a chance that we may be viewing multiple posts when this is submitted
		var current_post = $(this).find('[name="B2Template_PostId"]').val();
		
		$.ajax({
			type:'POST',
			url: $(this).attr('action'),
			async: true,
			dataType: 'json',
			data:$(this).serialize(),
				
			success: function(html)
			{
				update_html(html, current_post); // pass the id of the post to update
			},
			error: function(request, status, error)
			{
				alert(B2Template.AjaxError);
			}
		});
			
		event.preventDefault(); // This is very important. If we don't call this function, our form will proceed with the submit, and the page will refresh and our AJAX won't run. This function call prevents the form submit, and allows our AJAX to work it's magic. Make sure you remember to pass the variable 'event' into the event handler!
	});
	
	function update_html(data, current_post)
	{
		if(data.error) {
			show_feedback(data.error, current_post, false);
		}
		else
		{
			show_feedback(data.success, current_post, true);
			$('#B2Template_Fact_'+current_post).val('');
			$('#B2Template_Comment_'+current_post).val('');
			$('#B2Template_Source_'+current_post).val('');
			$('#B2Template_FactCheckList_'+current_post).animate({opacity:0},500, false, function() {
					$(this).html(data.html);
					$(this).animate({opacity:1},500);
				});
		}
	}
	
	function show_feedback(message, current_post, is_success)
	{
	
		var css_class = "updated";
		
		if(is_success == false)
		{
			css_class = "error";
		}
		
		// Add the error or success message to B2Template_Feedback so the user knows what happened. Fade out the feedback div, change the html content and then fade it back in.
		$('#B2Template_Feedback_'+current_post).animate({opacity:0},500, false, function() {
				$(this).html('<div class="'+css_class+'"><p>'+message+'</p></div>');
				$(this).animate({opacity:1},500);
			});
	}
	
	// Here we use jQuery to create a button that will add highlighted text to our "Fact" textbox. We create this button with jQuery, because it's only useful if the user has JavaScript enabled. If JavaScript is disabled, then this button is useless.
	$('[name="B2Template_Fact"]').after('<input type="button" class="B2Template_InsertHighlightedText" value="Insert highlighted text" />');
	
	// Here we bind the button to the function that will get the highlighted text and insert it in the "Fact" textbox
	$('.B2Template_InsertHighlightedText').click(function()
	{
		$(this).parent().find('[name="B2Template_Fact"]').val(getSelectedText());

	});
	
	/* This function returns whatever text the user has highlighted on the page */
	function getSelectedText() {
	    if (window.getSelection) {
	        return window.getSelection();
	    }
	    else if (document.selection) {
	        return document.selection.createRange().text;
	    }
	    return '';
	}
	
});