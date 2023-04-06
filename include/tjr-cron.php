<?php  
// expire events on date field.
if (!wp_next_scheduled('tjr_expire_shipment')){
    wp_schedule_event( time(), 'daily', 'tjr_expire_shipment' ); // this can be hourly, twicedaily, or daily
  }
add_action('tjr_expire_shipment', 'tjr_expire_shipment_function');
  
function tjr_expire_shipment_function() {
    $today = date('Y-m-d');
    $query = new WC_Order_Query( array(
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'ids',
    ));
    $orders = $query->get_orders();
    foreach($orders as $order_id){
        $expiredate = get_post_meta( $order_id, 'ship_end_date', true ); // get the date from the db
        if ($expiredate) {
            if( $expiredate <= $today ){
                $order = new WC_Order($order_id);
                if( $order && $order->get_status() != 'delivered' ){
                    $order->update_status('delivered',__('Automatic Approved Recieving','tjr') );
                    update_post_meta( $order_id, 'recieving_confirmation', 1 );
                }
            }
        }
    }
}

add_action('woocommerce_order_status_changed', 'tjr_order_status_changed_time', 10, 3);
function tjr_order_status_changed_time($order_id, $old_status, $new_status){
    $days = carbon_get_theme_option( 'ship_end_date' );
    $days = !empty( $days ) ? $days : '10' ;
    $Date = date('Y-m-d');
    $time = date('Y-m-d', strtotime($Date. ' + '. $days .' days'));

    if( $old_status == 'on-hold' && $new_status == 'shipped'){
        update_post_meta( $order_id, 'ship_end_date', $time );
    }
    if( $old_status == 'processing' && $new_status == 'shipped'){
        update_post_meta( $order_id, 'ship_end_date', $time );
    }
}