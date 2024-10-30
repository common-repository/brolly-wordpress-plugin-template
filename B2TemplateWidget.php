<?php
/*****
	Widgets
	
	This is an example of a simple WordPress Widget. The complete WordPress Widget API can be found here:
	http://codex.wordpress.org/Widgets_API
	
	NOTE:
	This widget is being registered within the B2Template.php Plugin file. If you want to develop a standalone widget (i.e. not a widget that is part of a larger Plugin), you'll need to add the following to the top of this plugin file in order to register it.
	
	add_action('widgets_init', create_function('', 'return register_widget(B2TemplateWidget");'));
	
	This line of code creates a PHP function that registers the widget and attaches it to the widget_init action. If you don't register the widget with WordPress, it won't show up in Available Widgets panel of the WordPress Widget Administration page and you won't be able to use it!
	
	*****/

/* Include the external TextStatistics PHP class that the widget uses to make syllable, reading level and reading grade calculations that the Plugin uses */
require_once('php/TextStatistics/TextStatistics.php');

/* This is the class definition for our widget. Notice that this class extends WP_Widget. By extending WP_Widget our widget automatically gets all the standard functionality that a widget needs. Then all we need to do is to override some functions to define what the widget will do. */

class B2TemplateWidget extends WP_Widget
{
	
	/* This is the constructor for the Widget, here we setup some variables that define the widget's name and description and add any actions or filters that the widget will use. */
	
	function B2TemplateWidget() {
		
		/* Here we define widget options to add a description to the widget in the WordPress Widget Administration page */
		$widget_ops = array('description' => __('This widget displays the word count and/or Flesch-Kincaid Readability Score for the currently viewed posts.', 'B2Template') );

		/* Here we pass some arguments to the WP_Widget constructor so that we can override some Widget properties.
			The first parameter allows us to override the id_base of the widget.
			The second parameter allows us to pass in the internationalized title of the widget.
			The third parameter lets us pass in some additional options (such as an internationalized description of the widget). */
			
		parent::WP_Widget( 'B2TemplateWidget', __('B2 Template Widget','B2Template'), $widget_ops);
		
		/* This function checks if this widget is currently added to any sidebars. If your widget requires external JavaScript or CSS, you should only include it if the widget is actually active. Otherwise, you'll be slowing down page loads by including these external files, when they aren't even being used! */
		
		 if (is_active_widget(false, false, $this->id_base) )
		 {
			add_action( 'template_redirect', array($this, 'WidgetCss') );
		}
		
	}
	
	/* This function renders the form that lets the user setup your widget's settings. You can let your users customize the title of your widget, toggle features on or off, etc... It is very easy to add some settings fields, and since WP_Widgets can be used multiple times each instance of your widget can have different settings. This function takes in one parameter, which is an array of the previously saved settings. If the user has just dragged a new instance of the widget to one of their sidebars, this will be an empty array. */
	
	function form($instance) {
	
		/* wp_parse_args allows us to set up default values for our widget settings. The first argument is the $instance array (which will be empty if this is a new instance), the second argument is an array of the default values for our settings. When a setting hasn't be seen by the user, the default will be used. */
		$instance = wp_parse_args((array) $instance, array('title'=>'Post Information','word_count'=>1,'reading_level'=>1,'syllables'=>1,'syllables_per_word'=>0,'words_per_sentence'=>0));
		
		/* Here we render the form that lets the user set the widget settings. 
		
		Notice that when we render the id and name attribute of each field, we call the functions $this->get_field_id('title') and $this->get_field_name('title') respectively. These functions turn the id: 'title' into 'widget-b2templatewidget-1-title' and the name: 'title' into 'widget-b2templatewidget[1][title]'. This tells WordPress what widget we want to save the settings for, and which instance of the widget we are working with.	
					
		The current value of each setting for this widget is stored as an array element in the $instance variable. So to display the title setting in the text field we use:
		
			value="''.attribute_escape($instance['title']).'" 
			
			The attribute_escape function simply removes any backslashes that might have been added to the data before it was added to the database. */
		
			echo '
			<p>
			<label for="'. $this->get_field_id('title').'">'.__('Widget Title','B2Template').'</label>
			<input type="text" id="'. $this->get_field_id('title').'" name="'. $this->get_field_name('title').'" value="'.attribute_escape($instance['title']).'" class="widefat" />
			</p>
			<p>
				<input type="checkbox" id="'. $this->get_field_id('word_count').'" name="'. $this->get_field_name('word_count').'" value="1" ';
				
			/* This demonstrates how to toggle a checkbox based on the users input. */
			echo (true == $instance['word_count']) ? 'checked="checked"' : '';
			
			echo ' />
				<label for="'. $this->get_field_id('word_count').'">'.__('Word Count','B2Template').'</label>
				<br/>
				<input type="checkbox" id="'. $this->get_field_id('reading_level').'" name="'. $this->get_field_name('reading_level').'" value="1" ';
			
			echo (true == $instance['reading_level']) ? 'checked="checked"' : '';
			
			echo '
				/>
				<label for="'. $this->get_field_id('reading_level').'">'.__('Reading Level','B2Template').'</label>
				<br/>
				<input type="checkbox" id="'. $this->get_field_id('syllables').'" name="'. $this->get_field_name('syllables').'" value="1" ';
			
			echo (true == $instance['syllables']) ? 'checked="checked"' : '';
			
			echo '
				/>
				<label for="'. $this->get_field_id('syllables').'">'.__('Syllables','B2Template').'</label>
				<br/>
				<input type="checkbox" id="'. $this->get_field_id('syllables_per_word').'" name="'. $this->get_field_name('syllables_per_word').'" value="1" ';
			
			echo (true == $instance['syllables_per_word']) ? 'checked="checked"' : '';
			
			echo '
				/>
				<label for="'. $this->get_field_id('syllables_per_word').'">'.__('Average Syllables /Word','B2Template').'</label>

			<br/>
				<input type="checkbox" id="'. $this->get_field_id('words_per_sentence').'" name="'. $this->get_field_name('words_per_sentence').'" value="1" ';
			
			echo (true == $instance['words_per_sentence']) ? 'checked="checked"' : '';
			
			echo '
				/>
				<label for="'. $this->get_field_id('words_per_sentence').'">'.__('Average Words / Sentence','B2Template').'</label>
			</p>
			';
			
			/* And that's all! The Save, Delete, and Close buttons are automatically added by the WP_Widget class. */
	}
	
	/* The update function is like a filter for your Widget settings. If there is any manipulation or error detection you need to perform on the settings your user is trying to save it should be done here. For example, below we make sure to strip out any HTML tags that the user may have tried to enter into the title text field, before we save it to the database. */
	
	function update($new_instance, $old_instance) {
		
		$instance['title'] = strip_tags($new_instance['title']); /* If the user tries to type any HTML tags, like <h1>, <p>, or <strong> into the title field, this line will strip it out. */
		$instance['word_count'] = $new_instance['word_count'];
		$instance['reading_level'] = $new_instance['reading_level'];
		$instance['syllables'] = $new_instance['syllables'];
		$instance['syllables_per_word'] = $new_instance['syllables_per_word'];
		$instance['words_per_sentence'] = $new_instance['words_per_sentence'];
		
		return $instance;
		
	}


	/*	This function is what actually adds your widget HTML to the page.
		
		The first parameter, $args, is an array of arguments defined by the currently active WordPress theme that defines what HTML elements the widget should be rendered inside (e.g. <div>,<li>), and what HTML element the widget title should be rendered inside (e.g. <strong>, <h3>, <h4>). It's important to echo out these elements exactly as they are in the example below, so that your widget does not break the design of the WordPress theme.
		
		The second parameter, $instance, includes the array of settings (defined above) that were saved for this instance of the widget. */
	
	function widget( $args, $instance ) {
		
		
		echo $args['before_widget'];
		
		/* If the user has set a title for the widget, then we want to display it. */
		if ( $instance['title'] )
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		
		/* Here we begin echoing out the widget HTML. */
		global $posts;
	
		if(is_array($posts))
		{
			echo '<ul>';
			
			foreach($posts as $post)
			{
				echo '<li><strong>'.$post->post_title.'</strong>';

				if(true == $instance['word_count'] || true == $instance['reading_level'] || true == $instance['syllables'] || true == $instance['syllables_per_word'] || true == $instance['words_per_sentence'])
				{
					$myTextStats = new TextStatistics();
					
					echo '<ul>';
						if(true == $instance['word_count'])
						{
							echo '<li><strong>'.__('Word Count','B2Template').'</strong> '.$myTextStats->word_count(apply_filters('the_content',$post->post_content)).'</li>';
						}
						
						if(true == $instance['reading_level'])
						{
							echo '<li><strong>'.__('Reading Level','B2Template').'</strong> '.$myTextStats->flesch_kincaid_grade_level(apply_filters('the_content',$post->post_content)).'</li>';
						}	
						if(true == $instance['syllables'])
						{
							echo '<li><strong>'.__('Syllables','B2Template').'</strong> '.$myTextStats->syllable_count(apply_filters('the_content',$post->post_content)).'</li>';
						}
						if(true == $instance['syllables_per_word'])
						{
							echo '<li><strong>'.__('Average Syllables / Word','B2Template').'</strong> '.round($myTextStats->average_syllables_per_word(apply_filters('the_content',$post->post_content)),2).'</li>';
						}	
						if(true == $instance['words_per_sentence'])
						{
							echo '<li><strong>'.__('Average Words / Sentence','B2Template').'</strong> '.round($myTextStats->average_words_per_sentence(apply_filters('the_content',$post->post_content)),2).'</li>';
						}	
				}
				
				echo '</ul>';
			}
			
			echo '</ul>';
		}

		
		echo $args['after_widget'];


	}
	
	/* This function attaches the Widget's CSS file to the page. Notice above that it is only run if the widget is currently active. */
	function WidgetCss()
	{
		wp_enqueue_style('B2TemplateWidget',WP_PLUGIN_URL.'/B2Template/css/widget.css');
	}

}
?>