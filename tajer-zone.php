<?php
/*
 * Plugin Name: Tajerzone Custom Plugin
 * Description: Custom Solution for Tajerzone.com
 * Version: 1.0.0
 * Author: Mohamed Yassin
 * Author URI: https://developeryassin.wordpress.com/
 * Text Domain: tjr
 * Domain Path: languages/
*/
// Constants
define('inc',plugin_dir_path( __FILE__ ).'/include/' );
define('views',plugin_dir_path( __FILE__ ).'/views/');
define('TJR_URL',plugin_dir_url( __FILE__ ) );

$include =  array(
    'base',
    'config',
    'general',
    //'tgm',
    'shipping',
    'shipping_methods/smsa_customised_official/smsa',
    'shipping_methods/aramex_customised_official/aramex',
    //'shipping_methods/saudi_post/saudi_post',
    //'widget',
    'emails',
    'ajax',
    'processes',
    'shortcode',
    'vendor',
    'order',
    'meta_box',
    'bill_of_lading',
    'enqueue',
    'logs'
);
$i = 0;
foreach ($include as $file) {
    if(!include_once inc.$file.'.php'){
        $i ++; 
        echo "$i ::  $file Not Found </br>";  
    }
};
if(production ==  false){
    include_once(inc.'debug.php');
}