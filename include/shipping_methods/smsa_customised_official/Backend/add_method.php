<?php
    
    add_action('woocommerce_shipping_init' , function(){
        class WebGate_Shipping_Method extends WC_Shipping_Method
        {
            /**
             * Constructor for your shipping class
             *
             * @access public
             * @return void
             */
            public function __construct()
            {
                $this->id = 'webgate_method';
                $this->method_title = __('SMSA (TJR)' , 'webgate');
                $this->method_description = __('Shipping Method for SMSAShipping' , 'webgate');
                
                
                // Availability & Countries
                if(optional($this->settings , false)->all_country)
                {
                    $this->availability = 'including';
                    $this->countries = optional($this->settings , [])->country;
                }
                
                $this->init();
                
                $this->enabled = optional($this->settings , 'yes')->enabled;
                $this->title = optional($this->settings , __('SMSA' , 'webgate'))->title;
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
                        'title' => __('Enable' , 'webgate') ,
                        'type' => 'checkbox' ,
                        'description' => __('Enable this shipping.' , 'webgate') ,
                        'default' => 'yes' ,
                    ] ,
                    
                    'title' => [
                        'title' => __('Title' , 'webgate') ,
                        'type' => 'text' ,
                        'description' => __('Title to be display on site' , 'webgate') ,
                        'default' => __('WebGate Shipping' , 'webgate') ,
                    ] ,
                    // TJR start //
                    'tajerzone_settings_title' => [
                        'title' => __('TajerZone Settings' , 'tjr') ,
                        'type' => 'title' ,
                    ] ,
                    'price' => [
                        'title' => __('Shpping Fixed Price Fees' , 'tjr') ,
                        'type' => 'number' ,
                        'default' => 0 ,
                        //'description' => __('If it\'s value > 0 it will be used instead of the Official SMSA Calculations', 'tjr'),
                    ],
                    'defult_product_weight' => [
                        'title' => __('Defult Product Weight' , 'tjr') ,
                        'type' => 'number' ,
                        'default' => 1 ,
                        'description' => __('Defult Product Weight, applied if the product don\'t has a correct weight' , 'tjr') ,
                    ] ,                        
                    'extra_weight_limit' => [
                        'title' => __('Extra Weight Limit' , 'tjr') ,
                        'type' => 'number' ,
                        'default' => 0 ,
                        'description' => __('Extra Weight fees will not applied up to this weight limit' , 'tjr') ,
                    ] ,                        
                    'fees_per_extra_weight_unit' => [
                        'title' => __('Fees per extra weight Unit' , 'tjr') ,
                        'type' => 'number' ,
                        'default' => 0 ,
                        'description' => __('Extra Fees will be applied for weights larger than "Extra Weight Limit" per weight unit' , 'tjr') ,
                    ] ,
                    
                    'smsa_settings_title' => [
                        'title' => __('Smsa Settings' , '') ,
                        'type' => 'title' ,
                    ] ,

                    'passkey' => [
                        'title' => __('pass key' , 'webgate') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    'api_url' => [
                        'title' => __('API Url' , 'webgate') ,
                        'type' => 'text' ,
                        'default' => 'https://track.smsaexpress.com/secom/smsawebservice.asmx?WSDL' ,
                    ] ,
                    
                    'shipper_name' => [
                        'title' => __('Shipper Name' , 'webgate') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    'shipper_contact' => [
                        'title' => __('Shipper contact' , 'webgate') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    'shipper_phone' => [
                        'title' => __('Shipper phone' , 'webgate') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    'shipper_address' => [
                        'title' => __('Shipper Address' , 'webgate') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    'shipper_city' => [
                        'title' => __('Shipper City' , 'webgate') ,
                        'type' => 'text' ,
                        'default' => 'Riyadh' ,
                    ] ,
                    
                    'shipper_country' => [
                        'title' => __('Shipper Country' , 'webgate') ,
                        'type' => 'text' ,
                        'default' => '' ,
                    ] ,
                    
                    
                    'all_country' => [
                        'type' => 'checkbox' ,
                        'title' => __('All Allowed Countries' , 'webgate') ,
                    ] ,
                    
                    'country' => [
                        'type' => 'multiselect' ,
                        'title' => __('Ship to Specific Countries' , 'webgate') ,
                        'options' => require('country.php') ,
                    ] ,
                
                ];
                
            }
            
            /**
             * Called to calculate shipping rates for this method. Rates can be added using the add_rate() method.
             *
             * @param array $package Package array.
             */
            public function calculate_shipping($package = [])
            {
                $WebGate_Shipping_Method = new WebGate_Shipping_Method();
                $price = $WebGate_Shipping_Method->settings['price'];

                if($price >  0){
                    $weight =  0;
                    foreach ( $package['contents'] as $key => $product ) 
                    { 
                        $product_date = $product['data'];
                        $product_weight = is_numeric($product_date->get_weight()) && $product_date->get_weight() >  0 ?  $product_date->get_weight() :  optional($this->settings , false)->defult_product_weight  ;
                        $product_weight = $product_weight * $product['quantity'];
                        $weight = $weight + $product_weight ;
                    }
                    $extra_fees =  $weight > optional($this->settings , false)->extra_weight_limit ? round_up_to_correct_num($weight - optional($this->settings , false)->extra_weight_limit) * optional($this->settings , false)->fees_per_extra_weight_unit :  0;
                    $price += $extra_fees;
                    $rate = [
                        'id' => $this->id ,
                        'label' => $this->title ,
                        'cost' => $price ,
                    ];
                    $this->add_rate($rate);                        
                }
            }
        }
    });
    
    add_filter('woocommerce_shipping_methods' , function($methods){
        $methods[] = 'WebGate_Shipping_Method';
        return $methods;
    });