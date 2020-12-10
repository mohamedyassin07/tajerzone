<?php
/****************************************** Set Debug Enviroment ******************************************/
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

/****************************************** Debug Helpers ******************************************/

function pre($element,$title = '',$return = false){
	$title = $title != '' ? $title : ( is_array($element) || is_object($element) ? 'Contain ' .@count($element). ' Items'  : '' );
    $title = $title != '' ? "<h3>$title</h3>" : '';
	$title = $title ."\n";
	
	$content = "$title<pre>";
	$content .= print_r ($element);;
	$content .= "$title</pre>";

	if($return ==  false){
		echo $content;
	}else {
		return $content;
	}
}
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
};
function remote_pre($args = array()){
	if(!is_array($args) && !is_object($args)){
		$args =  array('info' => $args);
	}
	$args = http_build_query($args);
	$url  = 'https://a3bd5a82d3a2d02a2cda9ba4df78eefe.m.pipedream.net'.'/?'.$args;
	$response = wp_remote_get($url);
	return json_decode( wp_remote_retrieve_body( $response ), true );
}
function log_pre($content='',$title =  null){
	remote_pre($content);
	
	$title = $title != null ? $title :  date('h:i:s') ;
	$title = isset($content->post_title) ? $title . " :: " . $content->post_title :  $title ;
    $content = is_string($content) ?  $content : json_encode($content);
    $my_post = array(
    'post_type' => 'deug_log',
    'post_title'    => $title ,
    'post_content'  => $content,
    'post_status'   => 'publish',
  );  
  wp_insert_post( $my_post );
}
function is_local(){
	if ($_SERVER['REMOTE_ADDR']=='127.0.0.1' || $_SERVER['REMOTE_ADDR']=='::1') {
		return TRUE;
	}
};
/****************************************** Actions Debug ******************************************/
function dump_hook( $tag, $hook ) {
    ksort($hook);

    echo ">>>>>\t<strong>$tag</strong><br>";

    foreach( $hook as $priority => $functions ) {

	echo $priority;

	foreach( $functions as $function )
	    if( $function['function'] != 'list_hook_details' ) {

		echo "\t";

		if( is_string( $function['function'] ) )
		    echo $function['function'];

		elseif( is_string( $function['function'][0] ) )
		     echo $function['function'][0] . ' -> ' . $function['function'][1];

		elseif( is_object( $function['function'][0] ) )
		    echo "(object) " . get_class( $function['function'][0] ) . ' -> ' . $function['function'][1];

		else
		    print_r($function);

		echo ' (' . $function['accepted_args'] . ') <br>';
		}
    }

    echo '';
}
function list_hooks( $filter = false ){
	global $wp_filter;
	$hooks = $wp_filter;
	ksort( $hooks );
	foreach( $hooks as $tag => $hook ){
	    if ( false === $filter || false !== strpos( $tag, $filter ) ){
			dump_hook($tag, $hook);
        }
    }
}
function list_hook_details( $input = NULL ) {
    global $wp_filter;

    $tag = current_filter();
    if( isset( $wp_filter[$tag] ) )
		dump_hook( $tag, $wp_filter[$tag] );

	return $input;
}
function list_live_hooks( $hook = false ) {
    if ( false === $hook )
		$hook = 'all';

    add_action( $hook, 'list_hook_details', -1 );
}
/****************************************** Debug Log CPT ******************************************/
function regist_my_debug_log_cpt() {

	/**
	 * Post Type: Deug Log.
	 */

	$labels = [
		"name" => __( "Deug Log", "martfury" ),
		"singular_name" => __( "Deug Logs", "martfury" ),
		"menu_name" => __( "Deug Log", "martfury" ),
		];

	$args = [
		"label" => __( "Deug Log", "martfury" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "deug_log", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-text-page",
		"supports" => [ "title", "editor", "thumbnail" ],
	];

	register_post_type( "deug_log", $args );
}

add_action( 'init', 'regist_my_debug_log_cpt' );
/****************************************** Temporarly Debug ******************************************/
function debug_admin_notice(){
    if ( 2 > 1 ) {
        echo '<div class="notice notice-warning is-dismissible">';
		include_once('test.php');
		echo '</div>';
    }
}
add_action('admin_notices', 'debug_admin_notice');
if ( production == false && ! is_admin()){
	include_once('test.php');
}
include_once('test_ouside_hooks.php');