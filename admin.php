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
						'summation' => __('summation', 'math-quiz'),
						'subtraction' => __('subtraction', 'math-quiz'),
						'multiplication' => __('multiplication', 'math-quiz'),
						'square-root' => __('square-root', 'math-quiz'),
						'exponentiation' => __('exponentiation', 'math-quiz')
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
				<td><textarea name="quiz-form" id="quiz-form" cols="60" rows="10"><?php echo $quiz_setting['quiz-form']; ?></textarea>
				<p class="description"><?php _e('&#37;problem&#37;, &#37;uniqueid&#37;, &#37;fieldname&#37; are the preserved strings that should never be removed.', 'math-quiz'); ?></p></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="quiz-position"><?php _e('Quiz Position', 'math-quiz'); ?></label></th>
				<td>
				<select name="quiz-ajax" id="quiz-ajax">
					<?php
						$quizAjax = array(
						'before' => __('before', 'math-quiz'),
						'after' => __('after', 'math-quiz')
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
				<p class="description"><?php _e('Please enter the "HTML element id" where you want to insert the quiz form before or after.', 'math-quiz'); ?></p>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'math-quiz'); ?>"  /></p>
	</form>
</div>
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
		
		//Check quiz-form
		if( substr_count($_POST['quiz-form'], '%problem%') == 1 && 
			substr_count($_POST['quiz-form'], '%uniqueid%') == 1	&&
			substr_count($_POST['quiz-form'], '%fieldname%') == 1 ){
			$quiz_setting['quiz-form'] = $_POST['quiz-form'];
		}else{
			if(strlen($setting_error) > 0) $setting_error .= ', ';
			$setting_error .= __('Quiz Form', 'math-quiz');
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
			$setting_error .= __('Quiz Insert Order', 'math-quiz');
		}
		
		update_option( 'math-quiz-setting', stripslashes_deep($quiz_setting) );
	}
	
	return $setting_error;
}
?>