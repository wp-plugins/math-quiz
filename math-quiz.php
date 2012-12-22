<?php
/*
Plugin Name: Math Quiz
Plugin URI: http://wordpress.org/extend/plugins/math-quiz/
Description: Generating random math problem for comment form.
Version: 0.4
Author: ATI
Author URI: http://atifans.net/
License: GPL2 or later
*/

//Define constants
define('SETTING_VERSION', '1.3');

//Make sure the plugin is not called outside WP
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

//Include admin functions
if ( is_admin() )
	require_once dirname( __FILE__ ) . '/admin.php';

//*******************************//
//*****Initialize the plugin*****//
//*******************************//
function start_math_engine(){
	
	//Register translation
	load_plugin_textdomain('math-quiz', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
	
	//Read plugin setting
	$quiz_setting = get_option('math-quiz-setting');
	if ( !isset( $quiz_setting['setting_version'] ) || $quiz_setting['setting_version'] != SETTING_VERSION )
		update_setting();
	
	//Prepare plugin hooks
	if( ! is_admin() ) {
		//Ajax hook
		if ( isset($_GET['math_quiz_ajax']) && $_GET['math_quiz_ajax'] == 'get_problem' ) {
			get_math_problem('ajax');
			exit();
		}
		// enqueue jQuery lib
		wp_enqueue_script('jquery');
		// echo customized style sheet
		if( $quiz_setting['quiz-css'] == 'plugin' )
			add_action( 'wp_head', 'get_style_sheet' );
		// form hook
		add_action( 'comment_form', 'get_math_problem', 1);
		// comment-process hook
		add_filter( 'preprocess_comment', 'check_math_answer', 1 );
	}
	
}
add_action('init', 'start_math_engine');

//***************************************//
//*****Background handling functions*****//
//***************************************//

//Random number generator
function number_engine($quiz_type = "summation"){
	//Math problem generator
	if($quiz_type == "summation"){
		
		$firstnum = mt_rand(1, 99);
		$secondnum = mt_rand(1, 100-$firstnum);
		$problem = __('Solve the problem: ', 'math-quiz') . $firstnum . ' + ' . $secondnum . ' = ?';
		$answer = $firstnum + $secondnum;
		
	}else if($quiz_type == "subtraction"){
	
		$firstnum = mt_rand(1, 99);
		$secondnum = mt_rand(1, $firstnum);
		$problem = __('Solve the problem: ', 'math-quiz') . $firstnum . ' - ' . $secondnum . ' = ?';
		$answer = $firstnum - $secondnum;
		
	}else if($quiz_type == "multiplication"){
		
		$firstnum = mt_rand(1, 25);
		$secondnum = mt_rand(1, 25);
		$problem = __('Solve the problem: ', 'math-quiz') . $firstnum . ' * ' . $secondnum . ' = ?';
		$answer = $firstnum * $secondnum;
	
	}else if($quiz_type == "square-root"){
	
		$number = mt_rand(1, 25);
		$square = pow($number, 2);
		$problem = __('Solve the problem: ', 'math-quiz') .'&radic;<span style="text-decoration: overline">'. $square .'</span> = ?';
		$answer = $number;
		
	}else if($quiz_type == "exponentiation"){
		
		$base = mt_rand(1, 25);
		$power = mt_rand(1, 2);
		$problem = __('Solve the problem: ', 'math-quiz') . $base . '<sup>' . $power . '</sup> = ?';
		$answer = pow($base, $power);
		
	}
	
	//Random string generator
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
    $fieldname = '';
	$uniqueid = '';
    for ($p = 0; $p < 16; $p++) {
        $fieldname .= $characters[mt_rand(0, strlen($characters)-1)];
		$uniqueid .= $characters[mt_rand(0, strlen($characters)-1)];
    }
	
	return array($problem, $answer, $fieldname, $uniqueid);
}

//Update current database data or initialize the setting
function update_setting(){
	$init_setting = array(
		'quiz-type' => 'summation',
		'quiz-css' => 'theme',
		'quiz-css-content' => '',
		'quiz-position' => 'submit',
		'quiz-ajax' => 'after',
		'setting_version' => SETTING_VERSION
	);
	
	$quiz_setting = get_option('math-quiz-setting');
	
	//If there's a existing setting, merge it.
	if( isset($quiz_setting['setting_version']) && version_compare( $quiz_setting['setting_version'], SETTING_VERSION, '<' )){
		$quiz_setting = array_merge( $init_setting, $quiz_setting );
		$quiz_setting['setting_version'] = SETTING_VERSION;
		update_option( 'math-quiz-setting', $quiz_setting );
	}else{
		add_option( 'math-quiz-setting', $init_setting );
	}
	
}

//Fixed quiz form
function get_quiz_form(){
	return '<p id="mathquiz"><label for="mathquiz">%problem%</label><input name="%fieldname%" type="text" placeholder="" /> <a id="refresh-mathquiz" href="javascript:void(0)">%reloadbutton%</a><input type="hidden" name="uniqueid" value="%uniqueid%"/></p>';
}
//***********************************//
//*****Action handling functions*****//
//***********************************//

//Generate math problem for unknown users
$problem_fired = 0;
function get_math_problem( $mode ){
	// only if this function was called exactly once
	if($$problem_fired++ > 0)
		return false;
		
	if(!current_user_can('publish_posts')){
		if( $mode == 'ajax' ){
			//Start session
			$siteurl = parse_url( site_url() );
			session_set_cookie_params(0, $siteurl['path']);
			session_name('nyan-q');
			session_start();
			
			//Get quiz setting
			$quiz_setting = get_option('math-quiz-setting');
			
			//Get things from the number engine
			list($problem, $answer, $fieldname, $uniqueid) = number_engine( $quiz_setting['quiz-type'] );
			
			//Store them into session data
			$_SESSION[$uniqueid]['answer'] = $answer;
			$_SESSION[$uniqueid]['fieldname'] = $fieldname;
		
			//Filter specific string
			$fireworks = str_replace( '%problem%', $problem, get_quiz_form() );
			$fireworks = str_replace( '%uniqueid%', $uniqueid, $fireworks );
			$fireworks = str_replace( '%fieldname%', $fieldname, $fireworks );
			$fireworks = str_replace( '%reloadbutton%', __('Refresh Quiz', 'math-quiz'), $fireworks );
			
			echo $fireworks;
		}else{
			// echo ajax script in footer
			add_action( 'wp_footer', 'get_ajax_script' );
		}
	}
	
	return true;
}

//Echo ajax code
$ajax_fired = 0;
function get_ajax_script(){
	// only if this function was called exactly once
	if( $ajax_fired++ > 0 )
		return false;
	
	//Get quiz setting
	$quiz_setting = get_option('math-quiz-setting');
	
	$ajax_code = '<script type="text/javascript">
	(function($){
	var MathQuizCall = function(){
			$.ajax({
				type : "GET",
				url : "'. site_url() .'/index.php",
				data : { math_quiz_ajax : "get_problem" },
				success : function(response){
					$("#' . $quiz_setting['quiz-position'] . '").' . $quiz_setting['quiz-ajax'] . '(response);	
				}
			});
		};
	var MathQuizRefresh = function(){
			$.ajax({
				type : "GET",
				url : "'. site_url() .'/index.php",
				data : { math_quiz_ajax : "get_problem" },
				success : function(response){
					$("#mathquiz").replaceWith(response);	
				}
			});
		};
		
	jQuery(document).ready(function() {
		MathQuizCall();
		$("body").on("click", "#refresh-mathquiz", MathQuizRefresh);
	});
	})(jQuery);
	</script>';
	
	echo $ajax_code;
	return true;
}

//Echo style sheet
$style_fired = 0;
function get_style_sheet(){
	// only if this function was called exactly once
	if( $style_fired++ > 0 )
		return false;
	
	//Get quiz setting
	$quiz_setting = get_option('math-quiz-setting');
	
	$style = '<style type="text/css">' . $quiz_setting['quiz-css-content'] . '</style>';
	
	echo $style;
	return true;
}

//Check answers
function check_math_answer( $commentdata ){
	//Split post data
	extract( $commentdata );
	
	//Check user identity and comment type
	if( !current_user_can( 'publish_posts' ) &&
		$comment_type != 'pingback' &&
		$comment_type != 'trackback' ) {
		
		//Start session
		$siteurl = parse_url( site_url() );
		session_set_cookie_params(0, $siteurl['path']);
		session_name('nyan-q');
		session_start();
		
		//Use the uniqueid to get generated problem
		$uniqueid = $_POST['uniqueid'];
		
		//Die if the problem can't be read from the session data
		if ( empty( $_SESSION[$uniqueid] ) || empty($_POST[ $_SESSION[$uniqueid]['fieldname'] ]) ){
			wp_die( __( 'You failed to answer the question. Please try again.', 'mathquiz' ) );
		}
		
		//Check answer
		if( $_POST[ $_SESSION[$uniqueid]['fieldname'] ] != $_SESSION[$uniqueid]['answer'] )
			wp_die( __( 'The answer is incorrect.  Please try again.', 'mathquiz' ) );
		
		//Problem solved, so destroy the uniqueid	
		unset($_SESSION[$uniqueid]);
	}
	
	return $commentdata;
}
?>