<?php

///////////////////////////////
///////////////////////////////
//Admin panel is not ready yet.
///////////////////////////////
///////////////////////////////

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
		write_setting();
?>
<div class="wrap">
	<h2><?php _e('Math Quiz', 'math-quiz'); ?></h2>
	<form name="math-quiz-form" method="post" action="">
	<?php
	if ( function_exists( 'wp_nonce_field' ) )
		wp_nonce_field( 'math-quiz-control-panel' );
	?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="quiz-type"><?php _e('Quiz Type', 'math-quiz'); ?></label></th>
				<td>
				<select name="quiz-type" id="quiz-type">
					<option value="subtraction"><?php _e('subtraction', 'math-quiz'); ?></option>
					<option value="square-root"><?php _e('square root', 'math-quiz'); ?></option>
				<p class="description"><?php _e('Choose your favorite quiz type!', 'math-quiz'); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="quiz-form"><?php _e('Quiz Form', 'math-quiz'); ?></label></th>
				<td><textarea name="quiz-form" id="quiz-form" cols="60" rows="10"></textarea>
				<p class="description"><?php _e('You can customize your own quiz form here.', 'math-quiz'); ?></p></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="quiz-position"><?php _e('Quiz Position', 'math-quiz'); ?></label></th>
				<td><input name="quiz-position" type="text" id="quiz-position" value="" class="regular-text" />
				<p class="description"><?php _e('Please enter the "HTML element id" where you want to insert the quiz form.', 'math-quiz'); ?></p></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes', 'math-quiz'); ?>"  /></p>
	</form>
</div>
<?php
} //math_setting_page ends here

function write_setting(){
	if ( check_admin_referer( 'math-quiz-control-panel' ) ) {
	
	}
}
?>