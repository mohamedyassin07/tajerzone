<?php
function register_tjr_shipped_order_status() {
    register_post_status( 'wc-shipped', array(
        'label'                     => _x( 'Shipped', 'Order status', 'tjr' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped<span class="count">(%s)</span>', 'tjr' )
    ) );
}
//add_action( 'woocommerce_init', 'tjr_new_wc_order_statuses' );
add_filter( 'wc_order_statuses', 'tjr_new_wc_order_statuses' );

add_action( 'woocommerce_init', 'so_25353766_register_email' );
function so_25353766_register_email(){
    add_action( 'wp_ajax_-mark-order-shipped','_mark_order_shipped');
    add_action( 'wp_ajax_-mark-order-shipped', array( WC(), '_mark_order_shipped' ), 10, 10 );
}

add_action( 'woocommerce_admin_order_actions', 'change_available_actions_in_vendor_dashboard', 10, 2 );
function change_available_actions_in_vendor_dashboard($actions, $order){
    if ( dokan_get_option( 'order_status_change', 'dokan_selling', 'on' ) == 'on' ) {
        if ( in_array( dokan_get_prop( $order, 'status' ), array( 'pending', 'on-hold', 'processing' ) ) ) {
            if(isset($actions['complete'])){
                unset($actions['complete']);
                $actions['shipped'] =  array(
                    'url'   => admin_url('admin-ajax.php').'?action=-mark-order-shipped&order_id='.dokan_get_prop($order,'id').'&_wpnonce=16da3a8b5d',
                    'name'  => __('Shipped',''),
                    'action'=> 'shipped',
                    'icon'  => '<i class="fa fa-truck"></i>'
                );
            }
        }
    }
    return $actions ;
}
add_action( 'woocommerce_thankyou', 'orders_to_be_saved', 10, 1 );
function orders_to_be_saved($parent_order_id){
    return;

    $orders = vendors_orders($parent_order_id);
    foreach ($orders as $order) {
        // Create bill of landing
        $bill_of_billing =  make_bill_of_landing($order);

        // save log
        $my_order_log = array(
            'post_type'     => 'tjr_log',
            'post_title'    => "$order order log" ,
            'post_content'  => 'content',
            'post_status'   => 'publish',
        );
        $my_order_log_id = wp_insert_post($my_order_log);
        if($order !=  $parent_order_id){
            update_post_meta( $my_order_log_id,'parent_order_id', $parent_order_id);
        }
        update_post_meta( $my_order_log_id,'seller_id', dokan_get_seller_id_by_order($order) );
        if(isset($bill_of_billing['binn_no']) && $bill_of_billing['binn_no'] >  0){
            foreach ($bill_of_billing as $info_key => $info_value) {
                update_post_meta( $my_order_log_id,$info_key,$info_value);
            }
        }
    }

}

add_action( 'wp_ajax_-mark-order-shipped','tjr_mark_order_shipped');
function tjr_mark_order_shipped()
{
    if ( $_GET['_wpnonce'] != '16da3a8b5d' ) { // just for now
        die();
    }

    $order_id = ! empty( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;
    if ( ! $order_id ) {
        die();
    }
    if ( ! dokan_is_seller_has_order( dokan_get_current_user_id(), $order_id ) ) {
        wp_die( esc_html__( 'You do not have permission to change this order', 'dokan-lite' ) );
    }
    $order = new WC_Order($order_id);
    $order->update_status('shipped', _('vendod addprioved shipping the order') );
    $order = wc_get_order($order_id);
    $order->set_status('shipped');
    $order->save();


    //$order = dokan()->order->get( $order_id );
    //$order->update_status( 'aaaaaaaaaaa' );

    // send mail to the customer to confirm recieving
    //wp_mail()
    $to = 'sendto@example.com';
    $subject = 'The subject';
    $body = 'The email body content';
    $headers = array('Content-Type: text/html; charset=UTF-8');
    //wp_mail( $to, $subject, $body, $headers );
    //$wp_mailer =  WC()->mailer();
    //$new =  new WC_Expedited_Order_Email();
    //$new->trigger(167);
    // redirect

    wp_safe_redirect( wp_get_referer() );
    die();
}

// New order status "SHIPPED"
add_action( 'init', 'register_tjr_shipped_order_status' );


// Register in wc_order_statuses.
function tjr_new_wc_order_statuses( $order_statuses ) {
    $order_statuses['wc-shipped'] = _x( 'Shipped', 'Order status', 'woocommerce' );  
    //pre($order_statuses ,  'order statuses ' .  current_action());  
    return $order_statuses;
}
function rename_or_reorder_bulk_actions( $actions ) {
    $actions['mark_shipped'] = __( 'Mark Shipped', '' );
    return $actions;
}
add_filter( 'bulk_actions-edit-shop_order', 'rename_or_reorder_bulk_actions', 20 );




/**
 * Register "woocommerce_order_status_pending_to_quote" as an email trigger
 */
add_filter( 'woocommerce_email_actions', 'so_25353766_filter_actions' );
function so_25353766_filter_actions( $actions ){
    $actions[] = "wp_ajax_-mark-order-shipped";
    //add_debug_log($actions , 'this is the actions triggers in the emials');
    return $actions;
}

function vendors_orders($parent_order_id,$vendor_id = 0){
    $orders =  array();
    $child_orders = array(
        'post_parent' => $parent_order_id,
        'post_type' => 'shop_order',
    );
    $child_orders = get_children($child_orders);
    if(is_array($child_orders) &&  count($child_orders) >  0){
        foreach ($child_orders as $child) {
            $orders[] = $child->ID;  
        }
    }else {
        $orders[] = $parent_order_id ; 
    }
    return $orders;
}
function get_order_log_data($order){

}
//get_order_log_data()
add_action( 'wp_ajax_tjr_shipping_process','tjr_shipping_process' );
add_action( 'wp_ajax_tjr_shipping_process','tjr_shipping_process' );
function tjr_shipping_process(){
    $process    = $_POST['process'];
    $order_id   = $_POST['order_id'];
    $method     = $_POST['method'];
    $view_for   = $_POST['view_for'];
    $awb_no     = get_post_meta($order_id,'awb_number',true);
    $awb_no     = 290115964974;
    $SMSA   =  new SMSA();
    $available_processes = available_processes($method,$view_for,$awb_no);
    $user_id    = get_current_user_id() ;
    $response   = array();
    $msg = '';

    if(array_key_exists($process,$available_processes)){
        if( $method == 'smsa' ) {
        if ($process ==  'getStatus') {
            $msg = "<b>".$SMSA->getStatus($awb_no)."</b>";
        }elseif ($process == 'downloadPdf') {
            $msg = "it was downloading process";
            // $url = 'https://contribute.geeksforgeeks.org/wp-content/uploads/gfg-40.png'; 
            // $batchfile = file_get_contents($url);
            // $size = strlen($batchfile);
            // header('Content-Disposition: attachment; filename="gfg-40.png"');
            // header('Content-Type: BAT MIME TYPE or something like application/octet-stream');
            // header('Content-Lenght: '.$size);
            // //echo $batchfile;
            // die; 
            // return;
        }elseif ($process == 'getTracking') {
            $resp = $SMSA->getTracking($awb_no);
            foreach ($resp as $key => $value) {
                $msg .= "<b>$key: </b> $value</br>";
            }
        }elseif ($process == 'cancelShipment') {
            $msg = "<b>".$SMSA->cancelShipment($awb_no)."</b>";
        }elseif ($process == 'regenerateShipment') {
            // cancel the old one 
            if($SMSA->cancelShipment($awb_no)){
            // remove the old data
                $order->update_meta_data('awb_number' , $addShipMPS->addShipMPSResult);
                $order->update_meta_data('awb_status' , ($awd_status instanceof Exception) ? '' : $awd_status);

            // create new one 
                create_smsa_bills($order_id);
                if(2>1){
                    // reload the page
                    $response['reload'] = 1;
                }
            }
        }
    }else if( $method == 'aramex' ) {
        if ($process == 'getTracking') {
            $response = 'ooooooo';
        }
    }
    }else {
        $msg = __('This process not supported' , 'tjr');
    }
    $response['msg'] = $msg != '' ? $msg :  __('Something Went Wrong','tjr');
    wp_send_json_success($response);
}

add_filter( 'manage_edit-shop_order_columns','tjr_shipping_method_orders_column');
function tjr_shipping_method_orders_column($columns)
{
    $columns['shipping_method'] = __('Shipping By','tjr');
    return $columns;    
}

add_action( 'manage_shop_order_posts_custom_column' , 'tjr_shipping_method_orders_column_content' );
function tjr_shipping_method_orders_column_content( $column ) {
    global $the_order; // you can use the global WP_Order object here
    // global $post; // is also available here

    if( $column == 'shipping_method' ) {
        echo get_primary_related_shipping_method($the_order);
    }
}

function get_primary_related_shipping_method($order){
    $order_shipping_method =  $order->get_shipping_method();
    if($order_shipping_method == ''){
        $parent_id =  wp_get_post_parent_id($order->get_id());
        $parent = wc_get_order($parent_id);
        $order_shipping_method =  $parent->get_shipping_method();
    }
    return explode(',',$order_shipping_method)[0]; // get the first/Primary method if thier are more than one 
}

