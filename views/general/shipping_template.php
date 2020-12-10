<?php
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
    $order_id =  is_admin() && $_GET['post'] >  0 ?  $_GET['post'] : 0 ;
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
        $shipping_method = get_primary_related_shipping_method($order);
        if(strpos(" " .strtolower($shipping_method),'smsa')){
            $awb_no     = get_post_meta($order_id,'awb_number',true);
            $awb_no     = $awb_no >   0  ?  $awb_no : 290115734845 ; // Just for testing
            $awb_status = get_post_meta($order_id,'awb_status',true);
            echo  "<$heading>".__("Shipping Method is : ",'tjr').$shipping_method.__(" with  AWB NO : ",'tjr').'<span id="tjr_tracking_no">'.$awb_no."</span></$heading>";
        }elseif (strpos(" " .strtolower($shipping_method),'aramex')){
            $awb_no     =  get_post_meta($order_id,'awb_number',true);
            $awb_no     =  $awb_no >   0  ?  $awb_no : 290115734845 ; // Just for testing
            $awb_status =  get_post_meta($order_id,'awb_status',true);
            echo  "<$heading>".__("Shipping Method is : ",'tjr').$shipping_method.__(" with  AWB NO : ",'tjr').'<span id="tjr_tracking_no">'.$awb_no."</span></$heading>";
        }
        foreach (available_processes('aramex',$view_for,$awb_no) as $key => $process) {
            echo '<'.$tag.'  process ="'.$key.'" class="'.$class.' shipping_process" method="aramex">'.$process['title'].'</'.$tag.'>';
        }
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
                    var data =  response.data;
                    if(data.hasOwnProperty('download')){
                        // 
                    }else if(data.hasOwnProperty('link')){
                        window.location = data.link;
                    }else{
                        var msg = "</br></br>" + data.msg + "</br></br>";
                        jQuery('#tjr_shipping_mangement_response').html(msg);
                    }
                }
            });            
        });
    });    
</script>