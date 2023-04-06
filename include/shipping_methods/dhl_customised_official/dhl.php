<?php
    
    /**
     * Plugin Name: WebGate DHLShipping
     * Description: DHLShipping Shipping Method for WooCommerce
     * Version: 1.0.0
     * Text Domain: WebGate DHLShipping
     */
    
    if(!defined('WPINC'))
    {
        die;
    }

    define('DHL_MOCK' , false);
    
    /*
     * Check if WooCommerce is active
     */
    if(in_array('woocommerce/woocommerce.php' , apply_filters('active_plugins' , get_option('active_plugins'))))
    {
        require_once 'Helper/DHL.php';
        require_once 'Backend/add_method.php';


        // Fronted
        require_once 'Fronted/index.php' ;
   
    }