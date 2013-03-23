<?php
/*
Plugin Name: Math Quiz
Plugin URI: http://wordpress.org/extend/plugins/math-quiz/
Description: Generating random math problem for comment form.
Version: 0.9
Author: ATI
Author URI: http://atifans.net/
License: GPL2 or later
*/

//Define constants
define('SETTING_VERSION', '2.7');

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
	if ( empty( $quiz_setting['setting_version'] ) || $quiz_setting['setting_version'] != SETTING_VERSION )
		update_setting();
	
	//Prepare plugin hooks
	if( ! is_admin() ) {
		//Ajax hook
		if ( isset($_GET['math_quiz_ajax']) && $_GET['math_quiz_ajax'] == 'get_problem' ) {
			header("Content-Type: text/html; charset=UTF-8");
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
function number_engine(){
	//Random string generator
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz";
	$uniqueid = '';
    for ($p = 0; $p < 32; $p++) {
		$uniqueid .= $characters[mt_rand(0, strlen($characters)-1)];
    }
	
	//Select to use + or - 
	$selector = mt_rand(0, 1);
	$num1 = mt_rand(10, 50);
	$num2 = mt_rand(1, $num1-1);
	if( $selector == 0 ){
		$problem = $num1 . ' + ' . $num2 . ' = ?';
		$answer = $num1 + $num2;
	}else{
		$problem = $num1 . ' - ' . $num2 . ' = ?';
		$answer = $num1 - $num2;
	}
	
	$problem = pictureGenerator( $problem );
	
	return array($problem, $answer, $uniqueid);
}

//Update current database data or initialize the setting
function update_setting(){
	$init_setting = array(
		'quiz-css' => 'theme',
		'quiz-css-content' => '',
		'quiz-position-selector' => 'default',
		'quiz-position' => 'submit',
		'quiz-ajax' => 'after',
		'setting_version' => SETTING_VERSION
	);
	
	$quiz_setting = get_option('math-quiz-setting');
	
	//If there's a existing setting, merge it and remove old one.
	if( !empty($quiz_setting['setting_version']) && version_compare( $quiz_setting['setting_version'], SETTING_VERSION, '<' )){
		$intersect = array_intersect($init_setting, $quiz_setting);
		$quiz_setting = array_merge( $init_setting, $intersect );
		$quiz_setting['setting_version'] = SETTING_VERSION;
		update_option( 'math-quiz-setting', $quiz_setting );
	}else{
		add_option( 'math-quiz-setting', $init_setting );
	}
	
}

//Fixed quiz form
function get_quiz_form(){
	return '<p id="mathquiz"><label for="mathquiz">%problemlabel%<img src="data:image/png;base64,%problem%"></label> <input name="math-quiz" type="text" /> <a id="refresh-mathquiz" href="javascript:void(0)">%reloadbutton%</a><input type="hidden" name="uniqueid" value="%uniqueid%" /><input type="hidden" name="nyan-q" value="%sessionid%" /></p>';
}

//Fire the session
function prepareSession(){
	$siteurl = parse_url( site_url() );
	session_set_cookie_params(0, $siteurl['path']);
	session_name('nyan-q');
	session_start();
}

//Base64 picture generator
function pictureGenerator( $text ){
	 // constant values
    $backgroundSizeX = 2000;
    $backgroundSizeY = 350;
    $sizeX = 120;
    $sizeY = 30;
    $fontFile = dirname( __FILE__ ) . '/lib/SourceCodePro-Bold.ttf';
    $textLength = strlen($text);
 
    // generate random security values
    $backgroundOffsetX = mt_rand(0, $backgroundSizeX - $sizeX - 1);
    $backgroundOffsetY = mt_rand(0, $backgroundSizeY - $sizeY - 1);
    $angle = mt_rand(-5, 5);
    $fontColorR = 50;
    $fontColorG = 50;
    $fontColorB = 50;
    $fontSize = mt_rand(14, 16);
    $textX = mt_rand(0, (int)($sizeX - 0.68 * $textLength * $fontSize)); // these coefficients are empiric
    $textY = mt_rand((int)($fontSize * 1.2), (int)($sizeY - 0.5 * $fontSize)); // don't try to learn how they were taken out

    // create image with background
    $src_im = imagecreatefrompng( dirname( __FILE__ ) . "/lib/background.png");
	$dst_im = imagecreatetruecolor($sizeX, $sizeY);
    $resizeResult = imagecopyresampled($dst_im, $src_im, 0, 0, $backgroundOffsetX, $backgroundOffsetY, $sizeX, $sizeY, $sizeX, $sizeY);

    $color = imagecolorallocate($dst_im, $fontColorR, $fontColorG, $fontColorB);

    imagettftext($dst_im, $fontSize, -$angle, $textX, $textY, $color, $fontFile, $text);

	ob_start();
		imagepng($dst_im);
		$imagedata = ob_get_contents(); // read from buffer
		imagedestroy($src_im); // free memory
		imagedestroy($dst_im);
	ob_end_clean(); // delete buffer
	
	return base64_encode($imagedata);
}

//***********************************//
//*****Action handling functions*****//
//***********************************//

//Generate math problem for unknown users
function get_math_problem( $mode ){
	// only if this function was called exactly once
    static $problem_fired = 0;
	if($problem_fired++ > 0)
		return false;
		
	if(!current_user_can('publish_posts')){
		if( $mode == 'ajax' ){
			//Support cross domain AJAX call
			header('Access-Control-Allow-Origin: ' . home_url() );
			
			//Start session
			prepareSession();
			
			//Get things from the number engine
			list($problem, $answer, $uniqueid) = number_engine();
			
			//Store them into session data
			$_SESSION[$uniqueid]['answer'] = $answer;
		
			//Filter specific string
			$stringToBeReplace = array(
				'%problem%',
				'%uniqueid%',
				'%sessionid%',
				'%problemlabel%',
				'%reloadbutton%'
			);
			$stringToReplace = array(
				$problem, 
				$uniqueid, 
				session_id(),
				__('Solve the problem: ', 'math-quiz'), 
				__('Refresh Quiz', 'math-quiz')
			);
			$fireworks = str_replace( $stringToBeReplace, $stringToReplace, get_quiz_form() );
			
			echo $fireworks;
			
		}else{
			// echo ajax script in footer
			add_action( 'wp_footer', 'get_ajax_script' );
		}
	}
	
	return true;
}

//Echo ajax code
function get_ajax_script(){
	// only if this function was called exactly once
    static $ajax_fired = 0;
	if( $ajax_fired++ > 0 )
		return false;
	
	//Get quiz setting
	$quiz_setting = get_option('math-quiz-setting');
	
	//Check setting
	if( $quiz_setting['quiz-position-selector'] == 'custom'){
		$selector = '$("#' . $quiz_setting['quiz-position'] . '").' . $quiz_setting['quiz-ajax'] . '(response);';	
	}else{
		$selector = '$("#submit").parent().before(response);';	
	}
	
	$ajax_code = 
'<script type="text/javascript">
	(function($){
	var MathQuizCall = function(){
			$.ajax({
				type : "GET",
				url : "'. site_url() .'/index.php",
				data : { math_quiz_ajax : "get_problem" },
				success : function(response){'. $selector .'}
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
function get_style_sheet(){
	// only if this function was called exactly once
    static $style_fired = 0;
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
		
		//Resume session
		session_id($_POST['nyan-q']);
		//Start session
		prepareSession();
		
		//Use the uniqueid to get generated problem
		$uniqueid = $_POST['uniqueid'];
		
		//Die if the problem can't be read from the session data
		if ( empty( $_SESSION[$uniqueid] ) || empty( $_POST['math-quiz'] ) ) {
			wp_die( __( 'You failed to answer the question. Please go back and try another problem.', 'mathquiz' ) );
		}
		
		//Check answer
		if( $_POST['math-quiz'] != $_SESSION[$uniqueid]['answer'] ) {
			unset( $_SESSION[$uniqueid] );
			wp_die( __( 'The answer is incorrect.  Please go back and try another problem.', 'mathquiz' ) );
		}
		
		//Problem solved, so destroy the uniqueid	
		unset( $_SESSION[$uniqueid] );
	}
	
	return $commentdata;
}
?>