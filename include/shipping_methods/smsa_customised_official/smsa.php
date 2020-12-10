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
        require __DIR__ . '/Helper/session.php';
        require __DIR__ . '/Helper/optional.php';
        require __DIR__ . '/Helper/SMSA.php';
        require __DIR__ . '/Helper/add_route.php';
        
        // Backend
        require(__DIR__ . '/Backend/index.php');
        
        // Fronted
        require(__DIR__ . '/Fronted/index.php');
        
    }