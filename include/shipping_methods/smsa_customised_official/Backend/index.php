<?php
    
    require 'add_method.php';
    require 'log_smsa.php';
    
    add_action('add_meta_boxes' , function(){
        
        $post_id = optional($_GET , false)->post;
        if($post_id && get_post_meta($post_id , '_shipping_method_awb' , true) == 'webgate_method')
        {
            add_meta_box('webgate_order_meta_box' , __('AWB Method') , function() use ($post_id){
                
                require 'view.php';
                awb_admin_message_clear();
                
            } , 'shop_order' , 'normal' , 'high');
        }
        
    });
   
    
   