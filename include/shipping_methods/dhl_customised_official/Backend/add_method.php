<?php
    
    add_action('woocommerce_shipping_init' , function(){
        class DHL_Shipping_Method extends WC_Shipping_Method
        {
            /**
             * Constructor for your shipping class
             *
             * @access public
             * @return void
             */
            public function __construct()
            {
                $this->id = 'dhl_method';
                $this->method_title = __('DHL Shipping (TJR)' , 'tjr');
                $this->method_description = __('Shipping Method for Woocommerce' , 'tjr');
                
                
                // Availability & Countries
                if(optional($this->settings , false)->all_country)
                {
                    $this->availability = 'including';
                    $this->countries = optional($this->settings , [])->country;
                }
                
                $this->init();
                
                $this->enabled = optional($this->settings , 'yes')->enabled;
                $this->title = optional($this->settings , __('DHL' , 'tjr'))->title;
            }
            
            
            /**
             * Init your settings
             *
             * @access public
             * @return void
             */
            function init()
            {
                // Load the settings API
                $this->init_form_fields();
                $this->init_settings();
                
                // Save settings in admin if you have any defined
                add_action('woocommerce_update_options_shipping_' . $this->id , [ $this , 'process_admin_options' ]);
            }
            
            function init_form_fields()
            {
                $this->form_fields = [
                    
                    'enabled' => [
                        'title' => __('Enable' , 'tjr') ,
                        'type' => 'checkbox' ,
                        'description' => __('Enable this shipping.' , 'tjr') ,
                        'default' => 'yes' ,
                    ] ,
                    
                    
                    'title' => [
                        'title' => __('Title' , 'tjr') ,
                        'type' => 'text' ,
                        'description' => __('Title to be display on site' , 'tjr') ,
                        'default' => __('DHL Shipping' , 'tjr') ,
                    ] ,
                    'tax_enabled' => [
                        'title' => __('Enable Tax' , 'webgate') ,
                        'type' => 'checkbox' ,
                        'description' => __('Enable Tax For this shipping.' , 'webgate') ,
                        'default' => 'yes' ,
                    ] ,
                    // TJR start //
                    'tajerzone_settings_title' => [
                        'title' => __('TajerZone Settings' , 'tjr') ,
                        'type' => 'title' ,
                    ] ,
                    'price' => [
                        'title' => __('Shpping Fixed Price Fees' , 'tjr') ,
                        'type' => 'decimal' ,
                        'default' => 0 ,
                        'description' => __('If it\'s value > 0 it will be used instead of the Official DHL Calculations', 'tjr'),
                    ],
                    'defult_product_weight' => [
                        'title' => __('Defult Product Weight' , 'tjr') ,
                        'type' => 'decimal' ,
                        'default' => 1 ,
                        'description' => __('Defult Product Weight, applied if the product don\'t has a correct weight' , 'tjr') ,
                    ] ,                        
                    'extra_weight_limit' => [
                        'title' => __('Extra Weight Limit' , 'tjr') ,
                        'type' => 'decimal' ,
                        'default' => 0 ,
                        'description' => __('Extra Weight fees will not applied up to this weight limit' , 'tjr') ,
                    ] ,                        
                    'fees_per_extra_weight_unit' => [
                        'title' => __('Fees per extra weight Unit' , 'tjr') ,
                        'type' => 'decimal' ,
                        'default' => 0 ,
                        'description' => __('Extra Fees will be applied for weights larger than "Extra Weight Limit" per weight unit' , 'tjr') ,
                    ] ,
                    
                    'dhl_settings_title' => [
                        'title' => __('DHL Settings' , '') ,
                        'type' => 'title' ,
                    ] ,
                    'dhl_sandbox' => [
                        'type' => 'checkbox' ,
                        'title' => __('Enable Sandbox API' , 'tjr') ,
                        'default' => 'yes',
                    ] ,
                    'acount_key' => [
                        'title' => __('DHL Acount Number' , 'tjr') ,
                        'type' => 'text' ,
                        'default' => '460992828' ,
                    ],

                    'pass_key' => [
                        'title' => __('Pass key' , 'tjr') ,
                        'type' => 'text' ,
                        'default' => 'apW5jS3xB6jV5g' ,
                    ],
                    'pass_secret' => [
                        'title' => __('Secret key' , 'tjr') ,
                        'type' => 'text' ,
                        'default' => 'V!2uF#0hE^5jQ!9j' ,
                    ] ,
                    
                    // 'api_url' => [
                    //     'title' => __('API Url' , 'tjr') ,
                    //     'type' => 'text' ,
                    //     'default' => 'https://express.api.dhl.com/mydhlapi/' ,
                    // ] ,

                    'shipper_name' => [
                        'title' => __('Shipper Name' , 'tjr') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    'shipper_contact' => [
                        'title' => __('Shipper contact' , 'tjr') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    'shipper_phone' => [
                        'title' => __('Shipper phone' , 'tjr') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    'shipper_address' => [
                        'title' => __('Shipper Address' , 'tjr') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    'shipper_city' => [
                        'title' => __('Shipper City' , 'tjr') ,
                        'type' => 'text' ,
                        'default' => 'Riyadh' ,
                    ] ,
                    
                    'shipper_country' => [
                        'title' => __('Shipper Country' , 'tjr') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    
                    // 'all_country' => [
                    //     'type' => 'checkbox' ,
                    //     'title' => __('All Allowed Countries' , 'tjr') ,
                    // ] ,
                    
                    // 'country' => [
                    //     'type' => 'multiselect' ,
                    //     'title' => __('Ship to Specific Countries' , 'tjr') ,
                    //     'options' => require('country.php') ,
                    // ] ,
                    // 'shipType' => [
                    //     'type' => 'select' ,
                    //     'title' => __('Ship Type' , 'tjr') ,
                    //     'options' => [
                    //         'DLV' => 'DLV',
                    //         'VAL' => 'VAL',
                    //         'HAL' => 'HAL',
                    //         'BLT' => 'BLT',
                    //     ],
                    //     'default' => 'DLV' ,
                    // ],
                
                ];
                
            }
            
            /**
             * Called to calculate shipping rates for this method. Rates can be added using the add_rate() method.
             *
             * @param array $package Package array.
             */
            public function calculate_shipping($package = [])
            {
                $DHL_Shipping_Method = new DHL_Shipping_Method();
                $price = $DHL_Shipping_Method->settings['price'];
                foreach ( $package['contents'] as $key => $product ) 
                $weight =  0;
                { 
                    $product_date = $product['data'];
                    $product_weight = is_numeric($product_date->get_weight()) && $product_date->get_weight() >  0 ?  $product_date->get_weight() :  optional($this->settings , false)->defult_product_weight  ;
                    $product_weight = $product_weight * $product['quantity'];
                    $weight = $weight + $product_weight ;
                }
                
                $country_list = require('country.php');
                $destCntry = $package['destination']['country'];
                if( $price >  0 && $destCntry === 'SA'){
                    $extra_fees =  $weight > optional($this->settings , false)->extra_weight_limit ? round_up_to_correct_num($weight - optional($this->settings , false)->extra_weight_limit) * optional($this->settings , false)->fees_per_extra_weight_unit :  0;
                    $price += $extra_fees;
                    if( $this->settings['tax_enabled'] == 'yes' ) {
                        $price += $price * 15 / 100;
                    }
                    $rate = [
                        'id' => $this->id ,
                        'label' => $this->title ,
                        'cost' => $price ,
                    ];
                    $this->add_rate($rate);                        
                } else {

                    // return prr($this->settings);

                    $DHL = new DHL();
                    $destCntry = $package['destination']['country'];
                    $Date = date("Y-m-d");
                    $plannedShippingDate =  date('Y-m-d', strtotime($Date. ' + 3 days'));
                    $data = [
                        'accountNumber' => isset($this->settings['acount_key']) ? $this->settings['acount_key'] : '',
                        'originCountryCode' => !empty($this->settings['shipper_country']) ? $this->settings['shipper_country'] : 'SA',
                        'originCityName' =>  $this->settings['shipper_city'],
                        'destinationCountryCode' => $package['destination']['country'],
                        'destinationCityName' => !empty($package['destination']['city']) ? $package['destination']['city'] : $package['destination']['state'],
                        'weight' => $weight,
                        'length' => !empty( $product_date->get_length() ) ? $product_date->get_length()  : '50',
                        'width'  => !empty( $product_date->get_width() ) ? $product_date->get_width()  : '50',
                        'height' => !empty( $product_date->get_height() ) ? $product_date->get_height()  : '50',
                        'plannedShippingDate' => $plannedShippingDate,
                        'isCustomsDeclarable' => 'true',
                        'unitOfMeasurement' => 'metric',
                        'nextBusinessDay' => 'false',
                        'strictValidation' => 'false',
                        'getAllValueAddedServices' => 'false',
                        'requestEstimatedDeliveryDate' => 'true',
                        // 'estimatedDeliveryDateType' => 'QDDF',
                    ];
           
                    $body = [];
                    $ShipCharges = $DHL->getRates([] , $data );
                    if( isset( $ShipCharges->products )  ) {
                    $extra_fees =  $weight > optional($this->settings , false)->extra_weight_limit ? round_up_to_correct_num($weight - optional($this->settings , false)->extra_weight_limit) * optional($this->settings , false)->fees_per_extra_weight_unit :  0;
                    foreach ( $ShipCharges->products as $products) {  
                        foreach( $products->totalPrice as $prices )  {
                            if( $prices->price > 0 ) {
                                $price = $prices->price;
                                $price += $extra_fees;
                                if( $this->settings['tax_enabled'] == 'yes' ) {
                                    $price += $price * 15 / 100;
                                }
                                    $rate = [
                                        'id' => $prices->currencyType . '_DHL' ,
                                        'label' => $this->title . ' - ' . $products->productName . '(' . $prices->currencyType . ') ',
                                        'cost' => $price ,
                                        'taxes' => '',
                                    ];
                                    $this->add_rate($rate); 
                            }
                        }
                        
                    }
                    } elseif( isset($ShipCharges->detail) ) {
                        $message = ' DHL- ERROR [ ' . $ShipCharges->detail . ' ] [ ' . $ShipCharges->message . ' - ' . $ShipCharges->status . ' ]';
                        $messageType = "error";
                        wc_clear_notices();
                        // prr($message);
                        if (!wc_has_notice($message, $messageType)) {
                            wc_add_notice($message, $messageType);
                        }
                    }

                }
            }
        }
    });
    
    add_filter('woocommerce_shipping_methods' , function($methods){
        $methods[] = 'DHL_Shipping_Method';
        return $methods;
    });