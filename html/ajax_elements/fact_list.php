<?php
/* This is an AJAX element that displays all the Fact Check statements returned from the database. */
if(is_array($facts))
{
	?>
	<ol>
	<?php
		foreach($facts as $fact)
		{
			printf('%sThe statement "%s%s%s" is being disputed. A visitor claims %s"%s"%s. Source [%s]%s','<li>','<em>',stripslashes($fact['fact']),'</em>','<strong>',stripslashes($fact['comment']),'</strong>',stripslashes($fact['source']),'</li>');
		}
	?>
	</ol>
	<?php
}
?>