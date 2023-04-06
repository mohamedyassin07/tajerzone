<?php 
function tjr_add_custom_emails_email( $email_classes ) {

    // include_once(inc.'emails/Shipped_order_email_class'.'.php');
        // include our custom email class
        require( inc .'emails/Shipped_order_email_class.php' );

        // add the email class to the list of email classes that WooCommerce loads
        $email_classes['Confirmed_Order_Email'] = new WC_Confirmed_Order_Email();
    
        return $email_classes;
	//$email_classes['customer_completeding_order'] = new ClientOrderShipped();
	//$email_classes['customer_completeding_order'] = new WC_Confirmed_Order_Email();
     
}
add_filter( 'woocommerce_email_classes', 'tjr_add_custom_emails_email');

add_filter( 'woocommerce_email_actions', 'tjr_register_custom_order_status_action');


function tjr_register_custom_order_status_action( $actions ) {

    $actions[] = 'woocommerce_order_status_shipped';

    return $actions;
}

function get_custom_email_html( $order, $heading = false, $mailer ) {
	$template = '../../tajerzone/views/emails/client_order_shipped.php';
	return wc_get_template_html( $template, array(
		'order'         => $order,
		'email_heading' => $heading,
		'sent_to_admin' => false,
		'plain_text'    => false,
		'email'         => $mailer
	) );
}
function order_receiving_confirmation_link($order_id){
    $page_id = option_val('dokan_pages');
    if( !empty( $page_id ) ) {
        $my_orders_page = $page_id['dashboard'];
    }
    $page_link =  get_page_link($my_orders_page) .'/orders/?order_id='.$order_id;
    return $page_link;
}
function send_mail_onshipping_order( $new_status, $old_status, $order ) {
    if($new_status ==  'wc-shipped'){
        $mailer = WC()->mailer();
        $recipient = "someone@somewhere.com";
        $subject = __("Hi! Here is a custom notification from us!", 'theme_name');
        $content = get_custom_email_html( $order, $subject, $mailer );
        $headers = "Content-Type: text/html\r\n";
        $mailer->send( $recipient, $subject, $content, $headers );    
    }
}
// add_action( 'transition_post_status', 'send_mail_onshipping_order', 10, 3 );
