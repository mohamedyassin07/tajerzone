<?php
add_shortcode( 'tjr_processes_page_content', 'tjr_processes_page_content' );
function tjr_processes_page_content( $atts ) {
    return view('frontend/processes_page_content');
}
add_shortcode( 'tjr_order_receiving_confirmation_shortcode', 'tjr_order_receiving_confirmation_shortcode' );
function tjr_order_receiving_confirmation_shortcode( $atts ) {
    // Secure the process by
        // 1- the user need to login
        // 2- a nonce-like string in the url
        // 3- anonce like and use the awb number

    $data['order_id'] =  isset($_REQUEST['order_id']) && is_numeric($_REQUEST['order_id']) ?  $_REQUEST['order_id'] :  false;
    return view('frontend/tjr_order_receiving_confirmation_shortcode', $data);
}