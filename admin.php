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
						$quizType = array('subtraction', 'square-root');
						for($i = 0; $i < count($quizType); $i++){
							echo '<option value="'. $quizType[$i] .'"';
							if( $quizType[$i] == $quiz_setting['quiz-type'] ) echo ' selected="selected"';
							echo '>'. __($quizType[$i], 'math-quiz') .'</option>';
						}
					?>
				</select>
				<p class="description"><?php _e('Choose your favorite quiz type!', 'math-quiz'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="quiz-form"><?php _e('Quiz Form', 'math-quiz'); ?></label></th>
				<td><textarea name="quiz-form" id="quiz-form" cols="60" rows="10"><?php echo $quiz_setting['quiz-form']; ?></textarea>
				<p class="description"><?php _e('「%problem%, %uniqueid%, %fieldname%」are the preserved strings that should never be removed.', 'math-quiz'); ?></p></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="quiz-position"><?php _e('Quiz Position', 'math-quiz'); ?></label></th>
				<td><input name="quiz-position" type="text" id="quiz-position" value="<?php echo $quiz_setting['quiz-position']; ?>" class="regular-text" />
				<p class="description"><?php _e('Please enter the "HTML element id" where you want to insert the quiz form.', 'math-quiz'); ?></p></td>
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
		if( $_POST['quiz-type'] == 'subtraction' ){
			$quiz_setting['quiz-type'] = 'subtraction';
		}else if($_POST['quiz-type'] == 'square-root'){
			$quiz_setting['quiz-type'] = 'square-root';
		}else{
			$setting_error .= 'Quiz Type';
		}
		
		//Check quiz-form
		if( substr_count($_POST['quiz-form'], '%problem%') == 1 && 
			substr_count($_POST['quiz-form'], '%uniqueid%') == 1	&&
			substr_count($_POST['quiz-form'], '%fieldname%') == 1 ){
			$quiz_setting['quiz-form'] = $_POST['quiz-form'];
		}else{
			if(strlen($setting_error) > 0) $setting_error .= ', ';
			$setting_error .= 'Quiz Form';
		}
		
		//Check quiz-position
		if( preg_match("/^[:A-Z_a-z][:A-Z_a-z-.0-9]*/", $_POST['quiz-position']) ){
			$quiz_setting['quiz-position'] = $_POST['quiz-position'];
		}else{
			if(strlen($setting_error) > 0) $setting_error .= ', ';
			$setting_error .= 'Quiz Position';
		}
		
		update_option( 'math-quiz-setting', stripslashes_deep($quiz_setting) );
	}
	
	return $setting_error;
}
?>