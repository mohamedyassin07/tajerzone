<?php
    $shipping_method = get_primary_related_shipping_method($order);
    $shipping_method_lable = explode(' ',trim(strtolower($shipping_method)));
    $ajax_url = admin_url('admin-ajax.php');
    if($view_for == 'vendor'){
        $heading = 'h5';
        $class = 'dokan-btn dokan-btn-success margin-2';
        $tag = 'button';
        echo "<style>.margin-2{margin:2px !important;}</style>";
    }elseif ($view_for == 'client') {
        $heading = 'h2';
        $class = 'button button-primary client margin-2';
        $class = 'dokan-btn dokan-btn-success margin-2';
        $tag = 'a';
        echo "<style>.margin-2{margin:2px !important;}</style>";
    }elseif ($view_for == 'admin') {
        $heading = 'h3';
        $class = 'button button-primary margin-2';
        $tag = 'a';
        echo "<style>.margin-2{margin:2px !important;}</style>";
    }

    $parent = count($vendors_orders) > 1 ? true : wp_get_post_parent_id($order->get_id());
    $order_data = $order->get_data(); // The Order data
    $order_id =  is_admin() && isset( $_GET['post'] ) ?  $_GET['post'] : 0 ;
    if( $order_id == 0 ){
        $order_id = isset( $_GET[ 'order_id' ] ) ? $_GET[ 'order_id' ] : 0;
    }

    echo do_shortcode( '[tjr-order-receiving-confirmation order_id='. $order_id .']');
 
    $awb_no = 0;
    $confirmation = get_post_meta( $order_id, 'recieving_confirmation', true);
    $confirmation = !empty($confirmation) ?  $confirmation : 0;
    if( $confirmation === 1 ){
        // return;
    }

    if($parent ===  true){
        if($view_for !=  'client'){
            echo  "<$heading>".__("This is the parent order of these orders",'tjr') ."</$heading>";
            foreach ($vendors_orders as $child_id) {
                $link =  $view_for == 'admin'? get_edit_post_link($child_id) : '#';
                echo '<'.$tag.' href="'.$link.'" class="'.$class.'">'.__('order ', 'tjr').$child_id.'</'.$tag.'> ';
            }    
        }
    }else {
        if($view_for == 'client'){
            echo "<h2>".__('Order Shipping','tjr')."</h2>";
        }

        if(strpos(" " .strtolower($shipping_method),'dhl')){
            $awb_no     = get_post_meta($order_id,'awb_number',true);
            $awb_no     = $awb_no >   0  ?  $awb_no : '' ; // Just for testing
            $awb_status = get_post_meta($order_id,'awb_status',true);
            echo '<section class="woocommerce-customer-details" style="border: 2px dashed;padding: 2rem;margin: 2rem 0;background: #ddd;">';
            echo  "<$heading>".__("Shipping Method is : ",'tjr').$shipping_method.__(" with  AWB NO : ",'tjr').'<span id="tjr_tracking_no">'.$awb_no."</span></$heading>";
            foreach (available_processes($shipping_method_lable[0],$view_for,$awb_no) as $key => $process) {
                echo '<'.$tag.'  process ="'.$key.'" class="'.$class.' shipping_process" method="'. $shipping_method_lable[0] .'">'.$process['title'].'</'.$tag.'>';
            }
            echo '</section>';
        }
        elseif(strpos(" " .strtolower($shipping_method),'smsa')){
            $awb_no     = get_post_meta($order_id,'awb_number',true);
            $awb_no     = $awb_no >   0  ?  $awb_no : '' ; // Just for testing
            $awb_status = get_post_meta($order_id,'awb_status',true);
            echo '<section class="woocommerce-customer-details" style="border: 2px dashed;padding: 2rem;margin: 2rem 0;background: #ddd;">';
            echo  "<$heading>".__("Shipping Method is : ",'tjr').$shipping_method.__(" with  AWB NO : ",'tjr').'<span id="tjr_tracking_no">'.$awb_no."</span></$heading>";
            foreach (available_processes($shipping_method_lable[0],$view_for,$awb_no) as $key => $process) {
                echo '<'.$tag.'  process ="'.$key.'" class="'.$class.' shipping_process" method="'. $shipping_method_lable[0] .'">'.$process['title'].'</'.$tag.'>';
            }
            echo '</section>';
        }elseif (strpos(" " .strtolower($shipping_method),'aramex')){
            global $wpdb;
            $table_perfixed = $wpdb->prefix . 'comments';
            $results = $wpdb->get_results("
                SELECT *
                FROM $table_perfixed
                WHERE  `comment_post_ID` = $order_id
                AND  `comment_type` LIKE  'order_note'
            ");
            $history_list = array();
            foreach ($results as $shipment) {
                $history_list[] = $shipment->comment_content;
            }
            $last_track = "";
            if (count($history_list)) {
                foreach ($history_list as $history) {
                    $awbno = strstr($history, "- Order No", true);
                    $awbno = trim($awbno, "AWB No.");
                    if (isset($awbno)) {
                        if ((int)$awbno) {
                            $last_track = $awbno;
                            break;
                        }
                    }
                    $awbno = trim($awbno, "Aramex Shipment Return Order AWB No.");
                    if (isset($awbno)) {
                        if ((int)$awbno) {
                            $last_track = $awbno;
                            break;
                        }
                    }
                }
            }

            $awb_no     =  get_post_meta($order_id,'awb_number',true);
            $awb_no     =  $awb_no >   0  ?  $awb_no : $last_track ; // Just for testing
            $awb_status =  get_post_meta($order_id,'awb_status',true);
            echo '<section class="woocommerce-customer-details" style="border: 2px dashed;padding: 2rem;margin: 2rem 0;background: #ddd;">';
            echo  "<$heading>".__("Shipping Method is : ",'tjr').$shipping_method.__(" with  AWB NO : ",'tjr').'<span id="tjr_tracking_no">'.$awb_no."</span></$heading>";
            aramex_display_order_data_in_admin( $order );
            aramex_display_schedule_pickup_in_admin( $order );
            aramex_display_rate_calculator_in_admin($order);
            aramex_display_printlabel_in_admin($order);
            aramex_display_track_in_admin($order);
            echo '</section>';
        }
        
    }
    if( !empty( $awb_no ) ) {
        $order->update_status('shipped');
    }

?>
<div id='tjr_shipping_mangement_response'></div>
<script>
    jQuery(document).ready( function(){	
        jQuery( ".shipping_process" ).click(function(e) { 
            e.preventDefault();
            jQuery.ajax({
                url : '<?= $ajax_url ?>',
                type : 'post',
                data : {
                    action : 'tjr_shipping_process',
                    order_id : <?= $order_id;?> ,
                    view_for : '<?= $view_for; ?>',
                    process : this.getAttribute("process"),
                    method : this.getAttribute("method")
                },
                beforeSend : function(response) {
                    var msg = "</br></br>" + 'Waiting Response . . . . .' + "</br></br>";
                    jQuery('#tjr_shipping_mangement_response').html(msg);
                },
                success : function(response) {
                    
                    if( response.data.link ){
                        console.log(response.data.msg);
                        var a = document.createElement("a");
                        a.href = 'data:application/pdf;base64,'+response.data.msg;
                        a.download = response.data.awb_no; //update for filename
                        document.body.appendChild(a);
                        a.click();
                        // remove `a` following `Save As` dialog, 
                        // `window` regains `focus`
                        window.onfocus = function () {                     
                            document.body.removeChild(a)
                        } 
                    }else{
                        var msg = "</br></br>" + response.data.msg + "</br></br>";
                        jQuery('#tjr_shipping_mangement_response').html(msg);
                    }
                }
            });            
        });
    });    
</script>