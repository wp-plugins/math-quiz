<?php

///////////////////////////////
///////////////////////////////
//Admin panel is not ready yet.
///////////////////////////////
///////////////////////////////

add_action( 'admin_menu', 'math_quiz_menu' );

function math_quiz_menu(){
	add_options_page('Math Quiz', 'Math Quiz', 'manage_options', 'math-quiz-menu', 'math_setting_page');
}

//Return setting content
function math_setting_page(){
	$content = '';
	return $content;
}

function add_settings_page() {
		if( current_user_can('manage_options') ) {
			$page = add_options_page( __( 'Comment Quiz', 'commentquiz' ), __( 'Quiz', 'commentquiz' ), 'manage_options', 'quiz', array( &$this, 'settings_page' ) );
			add_filter( 'plugin_action_links', array( &$this, 'filter_plugin_actions' ), 10, 2 );
			add_action( 'load-' . $page, array( &$this, 'save_options' ) );
			return $page;
		}
		return false;
	}
?>