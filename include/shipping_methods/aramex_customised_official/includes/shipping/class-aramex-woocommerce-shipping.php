<?php
/*
Plugin Name:  Aramex Shipping WooCommerce
Plugin URI:   https://aramex.com
Description:  Aramex Shipping WooCommerce plugin
Version:      1.0.0
Author:       aramex.com
Author URI:   https://www.aramex.com/solutions-services/developers-solutions-center
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  aramex
Domain Path:  /languages
*/

if (!class_exists('Aramex_Shipping_Method')) {

    /**
     * Controller for Aramex shipping
     */
    class Aramex_Shipping_Method extends WC_Shipping_Method
    {
        /**
         * Aramex_Shipping_Method constructor
         *
         * @return void
         */
        public function __construct()
        {
            $this->id = 'aramex';
            $this->method_title = __('Aramex (TJR)', 'aramex');
            $this->method_description = __('Shipping Method for Aramex', 'aramex');
            $this->init();
            $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
            $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('Aramex Shipping', 'aramex');
            include_once __DIR__ . '../../core/class-aramex-helper.php';
        }

        /**
         * Init your settings
         *
         * @return void
         */
        public function init()
        {
            // Load the settings API
            $this->init_form_fields();
            $this->init_settings();
            // Save settings in admin if you have any defined
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        /**
         * Define settings field for this shipping
         * @return void
         */
        public function init_form_fields()
        {
            $this->form_fields = include('data-aramex-settings.php');
        }

        /**
         * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
         *
         * @param array $package Package
         * @return void
         */
        public function calculate_shipping($package = array())
        {
            $defult_product_weight = isset(optional($this->settings , false)->defult_product_weight) && optional($this->settings , false)->defult_product_weight >  0 ?  optional($this->settings , false)->defult_product_weight : 0;

            $weight =  0;
            foreach ( $package['contents'] as $key => $product ) 
            { 
                $product_date = $product['data'];
                $product_weight = is_numeric($product_date->get_weight()) && $product_date->get_weight() >  0 ?  $product_date->get_weight() :  optional($this->settings , false)->defult_product_weight  ;
                $product_weight = $product_weight * $product['quantity'];
                $weight = $weight + $product_weight ;
            }
            $extra_weight_limit = !empty( $this->settings['extra_weight_limit']) ? $this->settings['extra_weight_limit'] : 0;
            $fees_per_extra_weight_unit = !empty( $this->settings['fees_per_extra_weight_unit']) ? $this->settings['fees_per_extra_weight_unit'] : 0;
            $extra_fees =  $weight > $extra_weight_limit ? round_up_to_correct_num($weight - $extra_weight_limit) * $fees_per_extra_weight_unit :  0;

            $settings = new Aramex_Shipping_Method();
            if (isset($settings->settings['price']) && $settings->settings['price'] >  0){
                $price = $settings->settings['price'];
                if( $this->settings['tax_enabled'] == 'yes' ) {
                    $price += $price * 15 / 100;
                }
                $rate = array(
                    'id' => "aramex",
                    'label' => 'Aramex',
                    'cost' => $price,
                );
                $this->add_rate($rate);
                return ;
            }
            
            $allowed_domestic_methods = $settings->form_fields['allowed_domestic_methods']['options'];
            $allowed_international_methods = $settings->form_fields['allowed_international_methods']['options'];
            $rate_calculator_checkout_page = $settings->settings['rate_calculator_checkout_page'];
            $rate_calculator_checkout_page_only_for_international = $settings->settings['rate_calculator_checkout_page_only_for_international'];
            if ($rate_calculator_checkout_page != 1) {
                return false;
            }
            $referer_parse = parse_url($_SERVER['REQUEST_URI']);
            if (strpos($referer_parse['path'], '/product/')!==false) {
                return false;
            }
            $rate_calculator_checkout_page = $this->settings['rate_calculator_checkout_page'];
            if ($rate_calculator_checkout_page == "0") {
                WC()->session->set('aramex_block', true);
                return false;
            } else {
                WC()->session->set('aramex_block', false);
            }
            $pkgWeight = 0;
            $pkgQty = 0;
            
            foreach ($package['contents'] as $item_id => $values) {
                $product = $values['data'];
                if( $product->is_type( 'simple' ) ){
                    // a simple product
                    $product_defult_weight = isset( $this->settings['product_defult_weight'] ) ?  $this->settings['product_defult_weight'] : 0;
                    $array_weight = $product->get_data();
                    $weight = $array_weight['weight'] >  0 ?  $array_weight['weight'] : $product_defult_weight;
                    
                  } elseif( $product->is_type( 'variation' ) ){
                      // a variable product
                    $array_weight = $product->get_data();
                    $product_defult_weight = isset( $this->settings['product_defult_weight'] ) ?  $this->settings['product_defult_weight'] : 0;
                    if(empty($array_weight['weight'])){
                        $parent_weight = $product->get_parent_data();
                        $weight = $parent_weight['weight'] >  0 ? $parent_weight['weight'] : $product_defult_weight;

                    }else{
                        $weight = $array_weight['weight'];
                    }
                }
                $pkgWeight = $pkgWeight + $weight * $values['quantity'];
                $pkgQty = $pkgQty + $values['quantity'];
            }
            $product_group = 'EXP';
            $allowed_methods = array();
            $allowed_methods = $this->settings['allowed_international_methods'];
            if (strtolower($this->settings['country']) == strtolower($package['destination']['country'])) {
                $product_group = 'DOM';
                $allowed_methods = $this->settings['allowed_domestic_methods'];
            }

            if(!empty($rate_calculator_checkout_page_only_for_international) && $product_group == 'DOM'){
                WC()->session->set('aramex_block', false);
            	return;
            }

            $info = $this->getInfo(wp_create_nonce('aramex-shipment-check' . wp_get_current_user()->user_email));
            if(!is_array($allowed_methods)){
            	return;
            }
            $aramex_shipping_method_name = $this->settings['title'];
            $title_without = $this->settings['hide_shipping_product1'];

            foreach ($allowed_methods as $key => $allowed_method) {
                $OriginAddress = array(
                    'StateOrProvinceCode' => $this->settings['state'],
                    'City' => $this->settings['city'],
                    'PostCode' => str_replace(" ","",$this->settings['postalcode']),
                    'CountryCode' => $this->settings['country'],
                );
                
                $DestinationAddress = array(
                    'StateOrProvinceCode' => '',
                    'City' => $package['destination']['city'],
                    'PostCode' => str_replace(" ","",$package['destination']['postcode']),
                    'CountryCode' => $package['destination']['country'],
                );
                $ShipmentDetails = array(
                    'PaymentType' => 'P',
                   // 'PaymentOptions' => 'ACCT',
                    'ProductGroup' => $product_group,
                    'ProductType' => $allowed_method,
                    'ActualWeight' => array('Value' => $pkgWeight, 'Unit' => get_option('woocommerce_weight_unit')),
                    'ChargeableWeight' => array('Value' => $pkgWeight, 'Unit' => get_option('woocommerce_weight_unit')),
                    'NumberOfPieces' => $pkgQty,
                );

                //SOAP object
                $soapClient = new SoapClient($info['baseUrl'] . 'aramex-rates-calculator-wsdl.wsdl', array("trace" => true, 'cache_wsdl' => WSDL_CACHE_NONE));
                // $soapClient = new SoapClient( __DIR__ . '/aramex-rates-calculator-wsdl.wsdl', array("trace" => true, 'cache_wsdl' => WSDL_CACHE_NONE));
                $baseCurrencyCode = get_woocommerce_currency();
                // prr($info['baseUrl'] ,  'client info');
                // $info['clientInfo']['UserName'] =  'fahad@tajerzone.com';
                // $info['clientInfo']['Password'] =  'Finance11$';
                
                $params = array(
                    'ClientInfo' => $info['clientInfo'],
                    'OriginAddress' => $OriginAddress,
                    'DestinationAddress' => $DestinationAddress,
                    'ShipmentDetails' => $ShipmentDetails,
                    'PreferredCurrencyCode' => $baseCurrencyCode
                );
                if ($allowed_method == "CDA") {
                    $params['ShipmentDetails']['Services'] = "";
                } else {
                    $params['ShipmentDetails']['Services'] = "";
                }
                try {
                    $price = "";
                    $results = $soapClient->CalculateRate($params);
                    $response = array();
                    if ($results->HasErrors) {
                        WC()->session->set('aramex_error', true);
                        // prr($results->Notifications->Notification);
                        // remote_pre($results->Notifications->Notification);
                        if (isset($results->Notifications->Notification) && is_object($results->Notifications->Notification) && @count($results->Notifications->Notification)  > 1){
                            $error = "";
                            foreach ($results->Notifications->Notification as $notify_error) {
                                $error .= 'Aramex: ' . $notify_error->Code . ' - ' . $notify_error->Message . "  *******  ";
                            }
                            $response['error'] = $error;
                        } else {
                            if ($results->Notifications->Notification->Code == 'ERR20') {
                                continue;
                            }
                            if ($results->Notifications->Notification->Code == 'ERR61') {
                                continue;
                            }
                            $response['error'] = 'Aramex: ' . $results->Notifications->Notification->Code . ' - ' . $results->Notifications->Notification->Message;
                        }
                        $response['type'] = 'error';
                        $aramex_visit_checkout = WC()->session->get('aramex_visit_checkout');
                        $aramex_set_first_success = WC()->session->get('aramex_set_first_success');
                        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

                        if (!$aramex_visit_checkout && !$aramex_set_first_success) {
                            $aramex_visit_checkout = 1;
                        } else {
                            $aramex_visit_checkout = $aramex_visit_checkout + 1;
                        }

                        if ($aramex_visit_checkout === 1) {
                            $response['type'] = 'error_aramex';
                            WC()->session->set('aramex_visit_checkout', $aramex_visit_checkout);
                        }
                    } else {
                        $aramex_visit_checkout = WC()->session->get('aramex_visit_checkout');
                        if (!$aramex_visit_checkout) {
                            WC()->session->set('aramex_set_first_success', true);
                        }
                        WC()->session->set('aramex_error', false);
                        $response['type'] = 'success';
                        $price = $results->TotalAmount->Value; // this is the total price
                    }
                } catch (Exception $e) {
                    $response['type'] = 'error';
                    $response['error'] = $e->getMessage();
                }

                if ($response['type'] == 'error_aramex') {
                    $message = null;
                    $messageType = "error";
                    if (!wc_has_notice($message, $messageType)) {
                        wc_add_notice($message, $messageType);
                    }
                }
                if ($response['type'] == 'error') {
                    $message = $response['error'];
                    $messageType = "error";
                    if (!wc_has_notice($message, $messageType)) {
                        wc_add_notice($message, $messageType);
                    }
                }

                if ($product_group == 'DOM') {
                    foreach ($allowed_domestic_methods as $key_dom => $domestic_method) {
                        if ($key_dom == $allowed_method) {
                            $title = $domestic_method;
                            break;
                        }
                    }
                } else {
                    foreach ($allowed_international_methods as $key_int => $international_method) {
                        if ($key_int == $allowed_method) {
                            $title = $international_method;
                            break;
                        }
                    }
                }
                if ($response['type'] == 'error') {
                    $rate = array(
                        'id' => $allowed_method . "_aramex",
                        'label' => "Aramex",
                        'cost' => "",
                    );
                    $this->add_rate($rate);
                    break;
                } 
                $title = $title ?  $aramex_shipping_method_name ."->". $title : "";
                $price = empty($this->settings['aramex_round']) ? ceil( $price ) : $price;
                if( $this->settings['tax_enabled'] == 'yes' ) {
                    $price += $price * 15 / 100;
                }
                $rate = array(
                    'id' => $allowed_method . "_aramex",
                    'label' => $title . "($allowed_method)",
                    'cost' => $price + $extra_fees,
                );
                // prr($rate);
                $this->add_rate($rate);
            }
        }

        /**
         *  Get total info about Admin
         *
         * @param string $nonce Nonce
         * @return array Total info
         */
        private function getInfo($nonce)
        {
            $baseUrl = $this->getWsdlPath($nonce);
            $clientInfo = $this->getClientInfo($nonce);

            return (array('baseUrl' => $baseUrl, 'clientInfo' => $clientInfo));
        }

        /**
         * Get info about Admin
         *
         * @param string $nonce Nonce
         * @return array
         */
        private function getClientInfo($nonce)
        {
            $settings = $this->getSettings($nonce);
            return array(
                'AccountCountryCode' => $settings['account_country_code'],
                'AccountEntity' => $settings['account_entity'],
                'AccountNumber' => $settings['account_number'],
                'AccountPin' => $settings['account_pin'],
                'UserName' => $settings['user_name'],
                'Password' => $settings['password'],
                'Version' => 'v1.0',
                'Source' => 31,
                'address' => $settings['address'],
                'city' => $settings['city'],
                'state' => $settings['state'],
                'postalcode' => $settings['postalcode'],
                'country' => $settings['country'],
                'name' => $settings['name'],
                'company' => $settings['company'],
                'phone' => $settings['phone'],
                'email' => $settings['email_origin'],
                'report_id' => $settings['report_id'],
            );
        }

        /**
         * Get path of WSDl file
         *
         * @return string
         */
        private function getPath()
        {
            return __DIR__ . '/../../wsdl/';
        }

        /**
         * Get Admin settings
         *
         * @param string $nonce Nonce
         * @return mixed|void
         */
        private function getSettings($nonce)
        {
            if (wp_verify_nonce($nonce, 'aramex-shipment-check' . wp_get_current_user()->user_email) == false) {
                echo(__('Invalid form data.'));
                die();
            }

            $includedStuff = get_included_files();
            $string = 'wp-config.php';
            $found = false;
            foreach ($includedStuff as $key => $url) {
                if (strpos($url, $string) !== false) {
                    $found = true;
                    break;
                }
            }
            if ($found == false) {
                require_once('../../../../../wp-config.php');
            }
            return get_option('woocommerce_aramex_settings');
        }

        /**
         * Get path of WSDL file
         *
         * @param string $nonce Nonce
         * @return string Path
         */
        private function getWsdlPath($nonce)
        {
            $settings = $this->getSettings($nonce);
            if ($settings['sandbox_flag'] == 1) {
                $path = $this->getPath() . 'test/';
            } else {
                $path = $this->getPath();
            }
            return $path;
        }
    }
}
