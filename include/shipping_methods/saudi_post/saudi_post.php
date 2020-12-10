<?php 
if ( ! defined( 'WPINC' ) ) {
    die; 
}
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function saudi_post_shipping_method() {
        if ( ! class_exists( 'saudi_post_shipping_method' ) ) {
            class saudi_post_shipping_method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = '_saudi_post';
                    $this->method_title       = __( 'Saudi Post', '' );  
                    $this->method_description = __( 'Custom Shipping Method for Saudi Post', '' ); 
 
                    // Availability & Countries
                    $this->availability = 'including';
                    $this->countries = array(
                        'US', // Unites States of America
                        'CA', // Canada
                        'DE', // Germany
                        'GB', // United Kingdom
                        'IT',   // Italy
                        'ES', // Spain
                        'HR',  // Croatia
                        'EG',
                        'SA'
                        );
 
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Saudi Post', '' );
                }
 
                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 
 
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }
 
                /**
                 * Define settings field for this shipping
                 * @return void 
                 */
                function init_form_fields() { 
 
                    $this->form_fields = array(
 
                     'enabled' => array(
                          'title' => __( 'Enable', '' ),
                          'type' => 'checkbox',
                          'description' => __( 'Enable this shipping.', '' ),
                          'default' => 'yes'
                          ),
 
                     'title' => array(
                        'title' => __( 'Title', '' ),
                          'type' => 'text',
                          'description' => __( 'Title to be display on site', '' ),
                          'default' => __( 'Saudi Post', '' )
                          ),
 
                     'weight' => array(
                        'title' => __( 'Weight (kg)', '' ),
                          'type' => 'number',
                          'description' => __( 'Maximum allowed weight', '' ),
                          'default' => 100
                          ),
 
                     );
 
                }
 
                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package = array() ) {

                    $weight = 0;
                    $cost = 0;
                    $country = $package["destination"]["country"];
 
                    foreach ( $package['contents'] as $item_id => $values ) 
                    { 
                        $_product = $values['data']; 
                        $weight = $weight + $_product->get_weight() * $values['quantity']; 
                    }
 
                    $weight = wc_get_weight( $weight, 'kg' );
 
                    if( $weight <= 10 ) {
 
                        $cost = 0;
 
                    } elseif( $weight <= 30 ) {
 
                        $cost = 5;
 
                    } elseif( $weight <= 50 ) {
 
                        $cost = 10;
 
                    } else {
 
                        $cost = 20;
 
                    }
 
                    $countryZones = array(
                        'HR' => 0,
                        'US' => 3,
                        'GB' => 2,
                        'CA' => 3,
                        'ES' => 2,
                        'DE' => 1,
                        'IT' => 1
                        );
 
                    $zonePrices = array(
                        0 => 10,
                        1 => 30,
                        2 => 50,
                        3 => 70
                        );
 
                    $priceFromZone = 0 ;
 
                    $cost += $priceFromZone;
 
                    $rate = array(
                        'id' => $this->id,
                        'label' => $this->title,
                        'cost' => 0
                    );
                    $this->add_rate( $rate );
                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'saudi_post_shipping_method' );
 
    function add_saudi_post_shipping_method( $methods ) {
        $methods[] = 'saudi_post_shipping_method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'add_saudi_post_shipping_method' );
 
    function _saudi_post_validate_order( $posted )   {
 
        $packages = WC()->shipping->get_packages();
 
        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
         
        if( is_array( $chosen_methods ) && in_array( '_saudi_post', $chosen_methods ) ) {
             
            foreach ( $packages as $i => $package ) {
 
                if ( $chosen_methods[ $i ] != "_saudi_post" ) {
                             
                    continue;
                             
                }
 
                $saudi_post_shipping_method = new saudi_post_shipping_method();
                $weightLimit = (int) $saudi_post_shipping_method->settings['weight'];
                $weight = 0;
 
                foreach ( $package['contents'] as $item_id => $values ) 
                { 
                    $_product = $values['data']; 
                    $weight = $weight + $_product->get_weight() * $values['quantity']; 
                }
 
                $weight = wc_get_weight( $weight, 'kg' );
                
                if( $weight > $weightLimit ) {
 
                        $message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', '' ), $weight, $weightLimit, $saudi_post_shipping_method->title );
                             
                        $messageType = "error";
 
                        if( ! wc_has_notice( $message, $messageType ) ) {
                         
                            wc_add_notice( $message, $messageType );
                      
                        }
                }
            }       
        } 
    }

    add_action( 'woocommerce_review_order_before_cart_contents', '_saudi_post_validate_order' , 10 );
    add_action( 'woocommerce_after_checkout_validation', '_saudi_post_validate_order' , 10 );
}