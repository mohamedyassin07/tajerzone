<?php                     
add_action('dokan_order_detail_after_order_items' , 'shipping_mangement_in_vendor_dashboard' ,  10 ,  1);
function shipping_mangement_in_vendor_dashboard($order){?>
    <div class="" style="width:100%">
        <div class="dokan-panel dokan-panel-default">
            <div class="dokan-panel-heading">
                <strong><?= __('Shipping Mangement',  ''); ?>
            </div>
            <div class="dokan-panel-body" id="woocommerce-order-items">
              <?php
                $data['order'] = $order;
                $data['vendors_orders'] = vendors_orders($_GET['order_id']);
                $data['view_for'] = 'vendor';
                view('general/shipping_template' , $data);              
              ?>  
            </div>
        </div>
    </div>
<?php }

add_action('woocommerce_view_order' , 'shipping_status_in_my_account_page' ,  10 ,  1);
function shipping_status_in_my_account_page($order_id){
    //echo  "this is the custom view for the $order_id";
}

add_filter( 'dokan_get_order_status_translated', '_get_order_status_shipped_translated', 10, 2 );
function _get_order_status_shipped_translated($defult,$status)
{
    return $status;
}
add_filter( 'dokan_get_order_status_class', '_get_order_status_shipped_class', 10, 2 );
function _get_order_status_shipped_class($defult,$status)
{
    return $status;
}
add_action('woocommerce_view_order','tjr_shipping_mangement_client_dashboard' ,  10 ,  1);
function tjr_shipping_mangement_client_dashboard($order_id){
    $order = wc_get_order($order_id);
    $data['order'] = $order;
    $data['vendors_orders'] = vendors_orders($order_id);
    $data['view_for'] =  'client';
    view('general/shipping_template' , $data);              
}
function tjr_add_custom_meta_boxes(){
    add_meta_box("tjr_admin_order_mangement_meta_box", __("Shipping Mangement",'tjr'), "tjr_admin_order_mangement_meta_box", "shop_order", "normal", "high", null);
}
add_action("add_meta_boxes", "tjr_add_custom_meta_boxes");

function tjr_admin_order_mangement_meta_box($order){
    $order = wc_get_order($order->ID);
    $data['order'] = $order;
    $data['vendors_orders'] = vendors_orders($order->get_id());
    $data['view_for'] = 'admin';
    view('general/shipping_template' , $data);
}
function tjr_remove_custom_field_meta_box()
{
    remove_meta_box("postcustom", "post", "normal");
}
add_action("do_meta_boxes", "tjr_remove_custom_field_meta_box");