<?php 
function tjr_add_custom_emails_email( $email_classes ) {

    include_once(inc.'emails/Shipped_order_email_class'.'.php');
	//$email_classes['customer_completeding_order'] = new ClientOrderShipped();
	//$email_classes['customer_completeding_order'] = new WC_Confirmed_Order_Email();
     
	return $email_classes;
}
add_filter( 'woocommerce_email_classes', 'tjr_add_custom_emails_email');

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
    $page_id = option_val('tjr_settings_order_receiving_confirmation_page');
    $page_link =  get_page_link($page_id) .'?order_id='.$order_id;
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
add_action( 'transition_post_status', 'send_mail_onshipping_order', 10, 3 );
