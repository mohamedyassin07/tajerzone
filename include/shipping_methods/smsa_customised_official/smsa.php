<?php
    
    /**
     * Plugin Name: WebGate SMSAShipping
     * Description: SMSAShipping Shipping Method for WooCommerce
     * Version: 1.0.0
     * Text Domain: WebGate SMSAShipping
     */
    
    if(!defined('WPINC'))
    {
        die;
    }
    
    /*
     * Check if WooCommerce is active
     */
    if(in_array('woocommerce/woocommerce.php' , apply_filters('active_plugins' , get_option('active_plugins'))))
    {
        // require_once 'vendor/autoload.php';
        require_once 'Helper/session.php';
        require_once 'Helper/optional.php';
        require_once 'Helper/SMSA.php';
        require_once 'Helper/add_route.php';
        
        // Backend
        require_once 'Backend/index.php' ;
        
        // Fronted
        // require_once 'Fronted/index.php' ;

            // Add a custom metabox only for shop_order post type (order edit pages)
    add_action('woocommerce_order_details_after_customer_details' , function(){
        $order_id = optional($_GET,get_query_var('view-order'))->{'view-order'};
        
        if(get_post_meta($order_id , '_shipping_method_awb' , true) == '_smsa')
        {
            require __DIR__ . '/Fronted/view.php';
            awb_admin_message_clear();
        }
    });
        
    }