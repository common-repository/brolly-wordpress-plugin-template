/* 

jQuery is a great javascript library that makes writing JavaScript quick and easy. I highly recommend using it. It's good practice to put all of your jQuery code inside this function.

jQuery(document).ready(function($) {

});

Both Scriptaculous and jQuery use the $ shortform function, so if you just start using $() in your JavaScript code, you can't be 100% sure where you're accessing jQuery or Scriptaculous. To avoid this problem, we use the long form of the function here: jQuery().

Then we attach a function to the document.ready event. This means that all of our javascript code will execute once all the HTML is loaded. This is helpful because we know that all of our html has been loaded and is available to be manipulated by jQuery. By passing ($) into the function, we can now use the $ short form function within this code block, and know that we are referencing jQuery.

If you are not familiar with jQuery I recommend reading up on it first before continuing. You can learn more about jQuery here: http://jquery.com/

*/
jQuery(document).ready(function($) {
	
	
	/* 
	In order to keep your JavaScript and HTML separate, it is best to "bind" behaviour to your HTML elements, instead of putting JavaScript function calls on the onclick="" or onsubmit="" parameters of elements.
	
	This function binds a function to the #B2Template_RuleList form's submit action. When this form is submitted, I want to run an ajax function to delete a record from the database. Note that I am doing this using 
	
	$().live('submit', function(event));
	
	Instead of 
	
	$().submit(function(event));
	
	The difference is that with live(), new forms added via ajax to #B2Template_RuleList will automatically get this behaviour. If I simply used bind(), then the forms and buttons that existed when the page first loaded would get the behaviour, but any elements added with ajax wouldn't.
	
	*/
	$('#B2Template_RuleList form').live('submit',function(event) {
		
		/* Now we execute an AJAX request */
		$.ajax({
			type:'POST', // We are going to use POST to submit the data
			url:$(this).attr('action'), // Post it to the current href
			data:$(this).serialize(), // serialize the form data 
			dataType: 'json', // The data that we are going to get back from the server will be in JSON format
			success: function(html) // When we get a response from the server, pass the response to this function
			{
				update_html(html);
			},
			error: function(request, status, error) // If an error occurs send a message to the user
			{
				alert(B2Template.AjaxError);
			}
		});
		
		event.preventDefault(); // This is very important. If we don't call this function, our form will proceed with the submit, and the page will refresh and our AJAX won't run. This function call prevents the form submit, and allows our AJAX to work it's magic. Make sure you remember to pass the variable 'event' into the event handler!
	
	});
	
	/* Bind an AJAX function to the Add a Find and Replace Rule form. Since this form doesn't get updated or have new instances added by AJAX, it doesn't need to be bound using $.live(). */
	$('#B2Template_AddFindAndReplaceWord').submit(function(event)
	{
		$.ajax(
			{
				type:"POST",
				url:$(this).attr('action'),
				data:$(this).serialize(),
				dataType:'json',
				success:function(data) { 
					update_html(data); 
				},
				error:function(request, status, error)
				{
					alert(B2Template.AjaxError);
				}
			}
		);
		event.preventDefault();
	});
	
	function update_html(data)
	{
		// Analyze the data returned by the server. 
		if(data.error) { // If an error was set, then display it using the show_feedback function
			show_feedback(data.error, false);
		}
		else
		{	// Otherwise a success message should be set, display that using the show_feedback function
			show_feedback(data.success, true);
			// Clear the form fields so the user can enter a new find and replace rule
			$('#B2Template_Find').val('');
			$('#B2Template_Replace').val('');
			// Fade out the RuleList, update the html with the new version returned from the server, and then fade it back in.
			$('#B2Template_RuleList').animate({opacity:0},500, false, function() {
					$(this).html(data.html);
					$(this).animate({opacity:1},500);
				});
		}
	}
	
	function show_feedback(message, is_success)
	{
	
		var css_class = "updated"; // This class creates a familiar WordPress feedback div denoting success
		
		if(is_success == false)
		{
			css_class = "error"; // this class creates a familiar WordPress feedback div denoting that something went wrong
		}
		
		// Add the error or success message to B2Template_Feedback so the user knows what happened. Fade out the feedback div, change the html content and then fade it back in.
		$('#B2Template_Feedback').animate({opacity:0},500, false, function() {
				$(this).html('<div class="'+css_class+'"><p>'+message+'</p></div>');
				$(this).animate({opacity:1},500);
			});
	}
	
});