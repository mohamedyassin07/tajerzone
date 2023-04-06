<?php
/****************************************** Set Debug Enviroment ******************************************/
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
/**=============================================================================================== */
/****************************************** Debug Helpers ******************************************/
/**
 * get_line_info
 *
 * @return void
 */
function get_line_info(){
	$excuting_line = debug_backtrace()[1]['line'];

	$excuting_file = debug_backtrace()[1]['file'];
	$excuting_file = explode("\\" ,$excuting_file);
	
	$count = count($excuting_file);

	$excuting_folder 	= @$excuting_file[($count-2)];		
	$excuting_file		= $excuting_file[($count-1)];
	// $excuting_file		= explode('.',$excuting_file)[0];
	return "$excuting_folder/$excuting_file/@line => $excuting_line";
}
/**
 * echo_line
 *
 * @param  mixed $echo
 * @return void
 */
function echo_line($echo = true){
	$line = get_line_info();

	if($echo){
		echo "<h2>$line</h2>";
	}else {
		return $line ;
	}
}

/**
 * prr
 *
 * @param  mixed $element
 * @param  mixed $title
 * @param  mixed $echo
 * @return void
 */
function prr($element,$title = '',$echo = true){
	$title = is_string($title) && strlen($title) ? $title.' ' : '';
    $blog_info = get_line_info();
	$title = "<h3> {$title}[ {$blog_info} ] </h3>";

	if($echo){
		echo "<div class=\"container\"> $title<pre style=\"background: #71d3ff;padding: 2rem; color: #000000;
    \">";
		print_r($element);
		echo "</pre></div>";
	}else {
		return "$title<pre>".print_r($element)."</pre>";
	}
}
/**
 * text
 *
 * @param  mixed $text
 * @param  mixed $return
 * @return void
 */
function text( $text= NULL , $return =  false){

	if ($text == NULL) {
		$text = "<h6 >A long text to be shown in places when you can't see results so perhaps you will need to make a standard check . You can search for me in the page with 6s ssssss.</h6>";
	}else {
		$text =  "<h3 >$text</h3>";
	}
	if($return ==  false){
		echo $text;
	}else {
		return $text;
	}
}