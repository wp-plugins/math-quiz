<?php

//Make sure the plugin is not called outside WP
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

add_action( 'admin_menu', 'math_quiz_menu' );

function math_quiz_menu(){
	add_options_page('Math Quiz', 'Math Quiz', 'manage_options', 'math-quiz-menu', 'math_setting_page');
}

//Return setting content
function math_setting_page(){

	if ( !empty($_POST['submit'] ) )
		$save_result = save_setting();

	//Get quiz setting
	$quiz_setting = get_option('math-quiz-setting');
	
	//Enqueue jQuery
	wp_enqueue_script('jquery');
?>
<div class="wrap">
	<h2><?php _e('Math Quiz', 'math-quiz'); ?></h2>
	<?php 
		if( !empty($_POST['submit'] ) ) { 
	?>
		<div id="setting-error-settings_updated" class="updated settings-error"> 
		<p><strong>
		<?php 
			if( strlen($save_result) > 0 ) {
				_e('The following settings is invalid, please try again. <br />', 'math-quiz');
				echo $save_result;
			}else{
				_e('Setting saved.', 'math-quiz');
			}
		?>
		</strong></p></div>
	<?php 
		} //Notice box ended here
	?>
	<form name="math-quiz-form" method="post" action="">
	<?php wp_nonce_field( 'math-quiz-control-panel' ); ?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="quiz-type"><?php _e('Quiz Type', 'math-quiz'); ?></label></th>
				<td>
				<select name="quiz-type" id="quiz-type">
					<?php
						//Use this kind of loop for better gettext compatibility
						$quizType = array(
						'summation' => __('Summation', 'math-quiz'),
						'subtraction' => __('Subtraction', 'math-quiz'),
						'multiplication' => __('Multiplication', 'math-quiz'),
						'square-root' => __('Square root', 'math-quiz'),
						'exponentiation' => __('Exponentiation', 'math-quiz')
						);
						while( $key = current($quizType) ){
							echo '<option value="'. key($quizType) .'"';
							if( key($quizType) == $quiz_setting['quiz-type'] ) echo ' selected="selected"';
							echo '>'. $key .'</option>';
							next($quizType);
						}
					?>
				</select>
				<p class="description"><?php _e('Choose your favorite quiz type!', 'math-quiz'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="quiz-form"><?php _e('Quiz Form', 'math-quiz'); ?></label></th>
				<td>
				<textarea id="quiz-form" class="code disabled" disabled="disabled" cols="80" rows="5"><?php echo get_quiz_form(); ?></textarea>
				<p class="description"><?php _e('This is how your quiz form will look like, customize it using CSS!', 'math-quiz'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="quiz-css"><?php _e('Quiz Customization', 'math-quiz'); ?></label></th>
				<td>
				<select name="quiz-css" id="quiz-css">
					<?php
						$quizCSS = array(
						'theme' => __('Use theme CSS', 'math-quiz'),
						'plugin' => __('Customize it here', 'math-quiz')
						);
						while( $key = current($quizCSS) ){
							echo '<option value="'. key($quizCSS) .'"';
							if( key($quizCSS) == $quiz_setting['quiz-css'] ) echo ' selected="selected"';
							echo '>'. $key .'</option>';
							next($quizCSS);
						}
					?>
				</select><br><br>
				<textarea name="quiz-css-content" id="plugin" class="quiz-css-content code" cols="40" rows="10" <?php if($quiz_setting['quiz-css'] != 'plugin') echo 'style="display:none"'; ?>><?php echo $quiz_setting['quiz-css-content']; ?></textarea>
				<p class="description"><?php _e('It\'s recommended to use theme CSS.', 'math-quiz'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="quiz-position"><?php _e('Quiz Position', 'math-quiz'); ?></label></th>
				<td>
				<select name="quiz-ajax" id="quiz-ajax">
					<?php
						$quizAjax = array(
						'before' => __('Insert before', 'math-quiz'),
						'html' => __('Insert into', 'math-quiz'),
						'after' => __('Insert after', 'math-quiz')
						);
						while( $key = current($quizAjax) ){
							echo '<option value="'. key($quizAjax) .'"';
							if( key($quizAjax) == $quiz_setting['quiz-ajax'] ) echo ' selected="selected"';
							echo '>'. $key .'</option>';
							next($quizAjax);
						}
					?>
				</select>
				<?php _e('the HTML element id: ', 'math-quiz'); ?>
				<input name="quiz-position" type="text" id="quiz-position" value="<?php echo $quiz_setting['quiz-position']; ?>" class="regular-text" placeholder="HTML element id here"/>
				<p class="description"><?php _e('Please enter the "HTML element id" where you want to insert the quiz form, and choose a insert method.', 'math-quiz'); ?></p>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'math-quiz'); ?>"  /></p>
	</form>
</div>
<script type="text/javascript">
$(function() {
    $('#quiz-css').change(function(){
        $('.quiz-css-content').hide();
        $('#' + $(this).val()).show();
    });
});
</script>
<?php
} //math_setting_page ends here

function save_setting(){
	if ( check_admin_referer( 'math-quiz-control-panel' ) ) {
		//Get quiz setting
		$quiz_setting = get_option('math-quiz-setting');
		
		$setting_error = '';
		
		//Check quiz-type
		if( $_POST['quiz-type'] == 'summation' ||
			$_POST['quiz-type'] == 'subtraction' ||
			$_POST['quiz-type'] == 'multiplication' ||
			$_POST['quiz-type'] == 'square-root' ||
			$_POST['quiz-type'] == 'exponentiation' 
			){
			$quiz_setting['quiz-type'] = $_POST['quiz-type'];
		}else{
			$setting_error .= __('Quiz Type', 'math-quiz');
		}
		
		//Check quiz-css
		if( $_POST['quiz-css'] == 'theme' ){
			$quiz_setting['quiz-css'] = 'theme';
		}else if( $_POST['quiz-css'] == 'plugin' ){
			$quiz_setting['quiz-css'] = 'plugin';
			$quiz_setting['quiz-css-content'] = $_POST['quiz-css-content'];
		}else{
			if(strlen($setting_error) > 0) $setting_error .= ', ';
			$setting_error .= __('Quiz Customization', 'math-quiz');
		}
		
		//Check quiz-position
		if( preg_match("/^[:A-Z_a-z][:A-Z_a-z-.0-9]*/", $_POST['quiz-position']) ){
			$quiz_setting['quiz-position'] = $_POST['quiz-position'];
		}else{
			if(strlen($setting_error) > 0) $setting_error .= ', ';
			$setting_error .= __('Quiz Position', 'math-quiz');
		}
		
		//Check quiz-ajax
		if( $_POST['quiz-ajax'] == 'before' ||
			$_POST['quiz-ajax'] == 'after'
			){
			$quiz_setting['quiz-ajax'] = $_POST['quiz-ajax'];
		}else{
			if(strlen($setting_error) > 0) $setting_error .= ', ';
			$setting_error .= __('Quiz Insert Method', 'math-quiz');
		}
		
		update_option( 'math-quiz-setting', stripslashes_deep($quiz_setting) );
	}
	
	return $setting_error;
}
?>