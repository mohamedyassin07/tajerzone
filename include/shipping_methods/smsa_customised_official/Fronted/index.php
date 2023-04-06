<?php    

        $child_orders = array(
            'post_parent' => $order_id,
            'post_type' => 'shop_order',
        );
        $child_orders = get_children($child_orders);
        if(is_array($child_orders) &&  count($child_orders) >  0){
            foreach ($child_orders as $child) {
                $orders[] = $child->ID;  
            }
        }else {
            $orders[] = $order_id ; 
        }
        foreach ($orders as $order_id) {
            $seller_id = dokan_get_seller_id_by_order( $order_id );
            $seller = new WP_User($seller_id);
            $seller_adress =  $seller->dokan_profile_settings['address'];
    
            // get order details data...
            $order = new WC_Order($order_id);
            $WebGate_Shipping_Method = new WebGate_Shipping_Method();
            $dataHelper = optional($WebGate_Shipping_Method->settings);
            $shipping_methods = $order->get_shipping_method();
            $payment_method = $order->get_payment_method();
                $order->update_meta_data('_shipping_method_awb' , '_smsa');
                $order->save();
                
                $SMSA = new SMSA_API();
                
                $customer_name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
                $currency_code = $order->get_currency();
                $currency_symbol = get_woocommerce_currency_symbol($currency_code);
                
                $weight = 0;
                foreach($order->get_items() as $item)
                {
                    $_weight = get_post_meta($item->get_data()['product_id'] , '_weight' , true);
                    if($_weight)
                    {
                        $weight += $_weight * $item->get_quantity();
                    }
                }
                
                
                $data = [
                    'passKey' => optional($WebGate_Shipping_Method->settings)->passkey ,
                    'refNo' => $order->get_id() ,
                    'sentDate' => date('Y-m-d H:i:s') ,
                    'idNo' => $order->get_id() ,
                    'cName' => $customer_name ,
                    'cntry' => $order->get_shipping_country() , // 'Riyadh'
                    'cCity' => $order->get_shipping_city() ,
                    'cZip' => $order->get_shipping_postcode() ,
                    'cPOBox' => $order->get_shipping_postcode() ,
                    'cMobile' => $order->get_billing_phone() ,
                    'cTel1' => $order->get_billing_phone() ,
                    'cTel2' => '' ,
                    'cAddr1' => $order->get_shipping_address_1() ,
                    'cAddr2' => $order->get_shipping_address_2() ,
                    'shipType' => 'DLV' ,
                    'PCs' => $order->get_item_count() ,
                    'cEmail' => $order->get_billing_email(),
                    'carrValue' => '' ,
                    'carrCurr' => $currency_symbol ,
                    'codAmt' => $payment_method == 'cod' ? $order->get_total() : '0' ,
                    'weight' => $weight ,
                    'custVal' => '' ,
                    'custCurr' => $currency_code ,
                    'insrAmt' => '' ,
                    'insrCurr' => '' ,
                    'itemDesc' => '' ,
                    'sName' => $seller->display_name,
                    'sContact' => $dataHelper->shipper_contact ,
                    'sAddr1' => $seller_adress['street_1'] ,
                    'sAddr2' => $seller_adress['street_2'],
                    'sCity' => $seller_adress['city'],
                    'sPhone' => $seller->dokan_profile_settings['phone'],
                    'sCntry' => $seller_adress['country'] ,
                    'prefDelvDate' => '' ,
                    'gpsPoints' => '' ,
                ];
                $log_data = [
                    'order_id' => $order->get_Id() ,
                    'customer_id' => $order->get_customer_id() ,
                    'customer_name' => $customer_name ,
                    'vendor_id' => $seller_id,
                    'tjr_shipping_method' =>  'SMSA',
                ];
                
                // send data soap
                $addShipMPS = $SMSA->addShipMPS($data);
                if($addShipMPS instanceof Exception)
                {
                    // submit log
                    wp_insert_post([
                        'post_type' => 'tjr_log' ,
                        'post_excerpt' => $addShipMPS->getMessage(),
                        'meta_input' => $log_data ,
                        'post_status'   => 'publish',
                    ]);
                }elseif ($addShipMPS->addShipMPSResult =='Failed :: Invalid Passkey') {
                    // submit log
                    wp_insert_post([
                        'post_type' => 'tjr_log' ,
                        'post_excerpt' => $addShipMPS->addShipMPSResult,
                        'meta_input' => $log_data ,
                        'post_status'   => 'publish',
                    ]);                
                }
                else
                {
                    $awd_status = $SMSA->getStatus($addShipMPS->addShipMPSResult);
                    // set awd_number order
                    if(is_numeric($addShipMPS->addShipMPSResult) && $addShipMPS->addShipMPSResult > 0 )
                    {
                        // get status
                        $order->update_meta_data('awb_number' , $addShipMPS->addShipMPSResult);
                        $order->update_meta_data('awb_status' , ($awd_status instanceof Exception) ? '' : $awd_status);
                        $order->save_meta_data();
                    }
                    
                    $log_data['awb_number'] = $addShipMPS->addShipMPSResult;
                    $log_data['awb_status'] = ($awd_status instanceof Exception) ? '' : $awd_status ;
    
                    // submit log
                    wp_insert_post([
                        'post_type' => 'tjr_log' ,
                        'post_excerpt' => $addShipMPS->addShipMPSResult ,
                        'meta_input' => $log_data ,
                        'post_status'   => 'publish',
                    ]);
                }
            
        }
    
