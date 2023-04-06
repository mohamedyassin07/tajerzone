<?php   

function tjr_create_aramex_ship($order_id)
{

    $post = array();
    $settings = new Aramex_Shipping_Method();
    if ($settings->settings['sandbox_flag'] == 1) {
        $path = 'https://ws.dev.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?singleWsdl';
    } else {
        $path = TJR_URL . 'include/shipping_methods/aramex_customised_official/wsdl/shipping.wsdl';
    }
    $digits = 6;
    $title_code = rand(pow(10, $digits-1), pow(10, $digits)-1);
    //SOAP object
    $soapClient = new SoapClient($path, array('soap_version' => SOAP_1_1));
    $order = new WC_Order($order_id);
    /* here's your form processing */
    $seller_id = dokan_get_seller_id_by_order( $order_id );
    $seller = new WP_User($seller_id);
    $seller_adress =  $seller->dokan_profile_settings['address'];
    $customer_name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
    $store_info  = dokan_get_store_info( $seller_id ); // Get the store data
    $store_name  = $store_info['store_name']; 
    $items = $order->get_items();
    $descriptionOfGoods = '';
        foreach ($items as $itemvv) {
            $descriptionOfGoods .= $itemvv['product_id'] . ' - ' . trim($itemvv['name'] . ' ');
        }
        $descriptionOfGoods = substr($descriptionOfGoods, 0, 65);
        $aramex_items_counter = 0;
        $totalItems = (trim($order->get_item_count()) == '') ? 1 : (int)$order->get_item_count();
        $aramex_atachments = array();
        $weight = 0;
            foreach($order->get_items() as $item)
            {
                $_weight = get_post_meta($item->get_data()['product_id'] , '_weight' , true);
                if($_weight)
                {
                    $weight += $_weight * $item->get_quantity();
                }
            }
            
        $totalWeight = $weight;
        // prr($totalWeight);
        $params = array();
        // prr($settings->settings);
        $AccountNumber_1 = $settings->settings['account_number'];
        $AccountPin_1 =  $settings->settings['account_pin'];
        
        //shipper parameters
        $params['Shipper'] = array(
            'Reference1' => $order_id, //'ref11111',
            'Reference2' => '',
            'AccountNumber' => $AccountNumber_1,
            'AccountPin' => $AccountPin_1,
            //Party Address
            'PartyAddress' => array(
                'Line1' => $seller_adress['street_1'], //'13 Mecca St',
                'Line2' => $seller_adress['street_2'],
                'Line3' => '',
                'City' => $seller_adress['city'], //'Dubai',
                // 'StateOrProvinceCode' => $post['aramex_shipment_shipper_state'], //'',
                'PostCode' => $order->get_shipping_postcode(),
                'CountryCode' => $seller_adress['country'], //'AE'
            ),
            //Contact Info
            'Contact' => array(
                'Department' => '',
                'PersonName' => $seller->display_name, //'Suheir',
                'Title' => '',
                'CompanyName' => $store_name, //'Aramex',
                'PhoneNumber1' => $seller->dokan_profile_settings['phone'], //'55555555',
                'PhoneNumber1Ext' => '',
                'PhoneNumber2' => '',
                'PhoneNumber2Ext' => '',
                'FaxNumber' => '',
                'CellPhone' => $seller->dokan_profile_settings['phone'],
                'EmailAddress' => $order->get_billing_email(), //'',
                'Type' => ''
            ),
        );
        //consinee parameters
        $params['Consignee'] = array(
            'Reference1' => $order_id, //'',
            'Reference2' => '',
            'AccountNumber' => $AccountNumber_1,
            'AccountPin' => $AccountPin_1,
            //Party Address
            'PartyAddress' => array(
                'Line1' => $order->get_shipping_address_1(), //'15 ABC St',
                'Line2' => $order->get_shipping_address_2(),
                'Line3' => '',
                'City' => $order->get_shipping_city(), //'Amman',
                'StateOrProvinceCode' => '',
                'PostCode' => $order->get_shipping_postcode(),
                'CountryCode' => $order->get_shipping_country(), //'JO'
            ),
            //Contact Info
            'Contact' => array(
                'Department' => '',
                'PersonName' => $customer_name, //'Mazen',
                'Title' => '',
                'CompanyName' => 'Aramex', //'Aramex',
                'PhoneNumber1' => $order->get_billing_phone(), //'6666666',
                'PhoneNumber1Ext' => '',
                'PhoneNumber2' => '',
                'PhoneNumber2Ext' => '',
                'FaxNumber' => '',
                'CellPhone' => $order->get_billing_phone(),
                'EmailAddress' => $order->get_billing_email(), //'mazen@aramex.com',
                'Type' => ''
            )
        );
        
        
        
        ////// add COD
        // $services = array();
        // if ($post['aramex_shipment_info_product_type'] == "CDA") {
        //     if ($post['aramex_shipment_info_service_type'] == null) {
        //         array_push($services, "");
        //     } elseif (!in_array("", $post['aramex_shipment_info_service_type'])) {
        //         $services = array_merge($services, $post['aramex_shipment_info_service_type']);
        //         array_push($services, "");
        //     } else {
        //         $services = array_merge($services, $post['aramex_shipment_info_service_type']);
        //     }
        // } else {
        //     if ($post['aramex_shipment_info_service_type'] == null) {
        //         $post['aramex_shipment_info_service_type'] = array();
        //     }

        //     $services = array_merge($services, $post['aramex_shipment_info_service_type']);
        // }

        // $services = implode(',', $services);

        ///// add COD end
        // Other Main Shipment Parameters
        // $params['ForeignHAWB'] = $post['aramex_shipment_info_foreignhawb'];
        // $params['Reference1'] = $post['aramex_shipment_info_reference']; //'Shpt0001';
        $params['Reference2'] = '';
        $params['Reference3'] = '';
        // $params['ForeignHAWB'] = $post['aramex_shipment_info_foreignhawb'];
        $params['TransportType'] = 0;
        $params['ShippingDateTime'] = time();
        $params['DueDate'] = time() + (7 * 24 * 60 * 60);
        $params['PickupLocation'] = 'Reception';
        $params['PickupGUID'] = '';
        // $params['Comments'] = $post['aramex_shipment_info_comment'];
        $params['AccountingInstrcutions'] = '';
        $params['OperationsInstructions'] = '';
        $params['Details'] = array(
            'Dimensions' => array(
                'Length' => '0',
                'Width' => '0',
                'Height' => '0',
                'Unit' => 'cm'
            ),
            'ActualWeight' => array('Value' => $totalWeight, 'Unit' => get_option('woocommerce_weight_unit')),
            'ProductGroup' => 'DOM',
            //'EXP , DOM',
            'ProductType' => 'OND',
            //,'PDX, ONP, OND, SMD'
            'PaymentType' => 'P', // 'C'
            // 'PaymentOptions' => $post['aramex_shipment_info_payment_option'],
            // 'Services' => $services,
            'NumberOfPieces' => 1,
            // 'DescriptionOfGoods' => (trim($post['aramex_shipment_description']) == '') ? $descriptionOfGoods : trim(substr($post['aramex_shipment_description'], 0, 65)),
            // 'GoodsOriginCountry' => $post['aramex_shipment_shipper_country'],
            //'JO',
            'Items' => 1
        );
        // if (count($aramex_atachments)) {
        //     $params['Attachments'] = $aramex_atachments;
        // }
        // if ($post['aramex_shipment_info_service_type'] != null)
        // {
        //     $hasCODS= array_search("CODS",$post['aramex_shipment_info_service_type'],false);
        //     if ($hasCODS !== false)
        //     {         
        //         $params['Details']['CashOnDeliveryAmount'] = array(
        //             'Value' => $post['aramex_shipment_info_cod_amount'],
        //             'CurrencyCode' => $post['aramex_shipment_currency_code']
        //             );
        //     }
        //     else
        //     {
        //         $params['Details']['CashOnDeliveryAmount'] = null ;
        //     }
        // }
        // else
        // {
        //     $params['Details']['CashOnDeliveryAmount'] = null ;       
        // }
        // $params['Details']['CustomsValueAmount'] = array(
        //     'Value' => $post['aramex_shipment_info_custom_amount'],
        //     'CurrencyCode' => $post['aramex_shipment_currency_code_custom']
        // );
        
        // $params['Details']['InsuranceAmount'] = array(
        //     'Value' => $post['insurance_amount'],
        //     'CurrencyCode' => $post['aramex_shipment_currency_code']
        // );
        
        // $params['ShipmentDetails']['InsuranceAmount'] =  $post['insurance_amount'];
        
        // $CurrencyCode = $post['aramex_shipment_currency_code'];
        // if (trim($CurrencyCode) === "") {
        //     $CurrencyCode = $post['aramex_shipment_currency_code_custom'];
        // }
        
        // $params['Details']['CashAdditionalAmount'] = array(
        //     'Value' => $post['aramex_shipment_info_cash_additional_amount'],
        //     'CurrencyCode' => $CurrencyCode
        // );

        $major_par['Shipments'][] = $params;
        $major_par['ClientInfo'] = _tjr_getClientInfo($settings);
        $report_id = (int)_tjr_getClientInfo($settings)['report_id'];
        if (!$report_id) {
            $report_id = 9729;
        }
        $major_par['LabelInfo'] = array(
            'ReportID' => $report_id,
            'ReportType' => 'URL'
        );
        // prr($major_par);
        
        try {
            $auth_call = $soapClient->CreateShipments($major_par);
            $log_data = [
                'order_id' => $order->get_Id() ,
                'customer_id' => $order->get_customer_id() ,
                'customer_name' => $customer_name ,
                'vendor_id' => $seller_id,
                'tjr_shipping_method' =>  'ARAMEX',
            ];

            if ($auth_call->HasErrors) {
                $notification_string = [];
                if (count(( array )$auth_call->Shipments) < 1) {
                    if (count((array)$auth_call->Notifications->Notification) > 1) {
                        
                        foreach ($auth_call->Notifications->Notification as $key => $notify_error) {
                            $notification_string[] = __('Aramex: ' . $key . ' - ' . $notify_error);
                        }
                    } else {
                        $notification_string = __('Aramex: ' . $auth_call->Notifications->Notification->Code . ' - ' . $auth_call->Notifications->Notification->Message);
                    }
                } else {
                    if ( isset( $auth_call->Shipments->ProcessedShipment->Notifications->Notification ) && count(array($auth_call->Shipments->ProcessedShipment->Notifications->Notification)) > 1) {
                        $notification_string = [];
                        foreach ((array) $auth_call->Shipments->ProcessedShipment->Notifications->Notification as $notification_error) {
                            $notification_string[] = 'Aramex: ' . $notification_error->Code . ' - ' . $notification_error->Message . '';
                        }
                    } else{
                        $notification_error = $auth_call->Shipments->ProcessedShipment->Notifications->Notification;
                        $notification_string = 'Aramex: ' . $notification_error->Code . ' - ' . $notification_error->Message . '';
                    }
                }
                wp_insert_post([
                    'post_type' => 'tjr_log',
                    'post_title' => 'tjr_log-' . $title_code,
                    'post_excerpt' => implode(', ', (array) $notification_string),
                    'meta_input' => $log_data ,
                    'post_status'   => 'publish',
                ]);
                // prr($notification_string);
            } else {

                $order->update_meta_data('awb_number' , $auth_call->Shipments->ProcessedShipment->ID );
                $order->update_meta_data('awb_status' , 'Data Recived');
                $order->save_meta_data();
                wp_insert_post([
                    'post_type' => 'tjr_log',
                    'post_title' => 'tjr_log-' . $title_code,
                    'post_excerpt' => $auth_call->Shipments->ProcessedShipment->ID,
                    'meta_input' => $log_data ,
                    'post_status'   => 'publish',
                ]);
                $commentdata = array(
                    'comment_post_ID' => $order_id,
                    'comment_author' => '',
                    'comment_author_email' => '',
                    'comment_author_url' => '',
                    'comment_content' => "AWB No. " . $auth_call->Shipments->ProcessedShipment->ID . " - Order No. " . $auth_call->Shipments->ProcessedShipment->Reference1,
                    'comment_type' => 'order_note',
                    'user_id' => "0",
                );
                wp_new_comment($commentdata);
                $order = new WC_Order($order_id);
                $order->add_order_note($commentdata['comment_content']);
                $order->save();
                if (!empty($order)) {
                    $order->update_status('on-hold', __('Aramex shipment created.', 'aramex'));
                }

                    /* sending mail */
                    global $woocommerce;
                    $mailer = $woocommerce->mailer();
                    $message_body = sprintf(__('<p>Dear <b>%s</b> </p>'), $post['aramex_shipment_receiver_name']);
                    $message_body .= sprintf(__('<p>Your order is #%s </p>'),
                        $auth_call->Shipments->ProcessedShipment->Reference1);
                    $message_body .= sprintf(__('<p>Created Airway bill number: %s </p>'),
                        $auth_call->Shipments->ProcessedShipment->ID);
                    $message_body .= __('<p>You can track shipment on <a href="http://www.aramex.com/express/track.aspx">http://www.aramex.com/express/track.aspx</a> </p>');
                    $message_body .= __('<p>If you have any questions, please feel free to contact us <b>support@example.com</b> </p>',
                        'aramex');
                    $message = $mailer->wrap_message(
                    // Message head and message body.
                        sprintf(__('Aramex shipment #%s created', 'aramex'), $order->get_order_number()),
                        $message_body);

                    if (isset($post['aramex_email_customer']) && $post['aramex_email_customer'] == 'yes') {
                        // Cliente email
                        $to = array();
                        $to[] = $order->billing_email;
                        $to[] = $info['copyInfo']['copy_to'];
                        $emailsTo = implode(',', $to);
                        if (trim($info['copyInfo']['copy_to']) == "") {
                            $emailsTo = trim($emailsTo, ',');
                        }
                        $mailheader = array();
                        if ($info['copyInfo']['copy_method'] == "1" && trim($info['copyInfo']['copy_to']) != "") {
                            $emails = explode(',', trim($info['copyInfo']['copy_to']));
                            foreach ($emails as $email) {
                                $mailheader[] = 'Bcc: ' . $email;
                            }
                        }
                        if ($info['copyInfo']['copy_method'] == "0" && trim($info['copyInfo']['copy_to']) != "") {
                            $emails = explode(',', trim($info['copyInfo']['copy_to']));
                            foreach ((array)$emails as $email) {
                                $mailheader[] = 'Cc: ' . $email;
                            }
                        }
                        try {
                            $mailer->send($emailsTo,
                                sprintf(__('Aramex shipment #%s created', 'aramex'), $order->get_order_number()),
                                $message,
                                $mailheader);
                        } catch (Exception $ex) {
                            return $ex->getMessage();
                        }
                    }

                    // aramex_errors()->add('success',
                    //     __('Aramex Shipment Number: ',
                    //         'aramex') . $auth_call->Shipments->ProcessedShipment->ID . __(' has been created.',
                    //         'aramex'));
                
            }
        } catch (Exception $e) {
            $aramex_errors = true;   
            return $e;
        }     

    }
/**
 * Get info about Admin
 *
 * @param string $nonce Nonce
 * @return array
 */
function _tjr_getClientInfo( $settings )
{
    return array(
        'AccountCountryCode' => $settings->settings['account_country_code'],
        'AccountEntity' => $settings->settings['account_entity'],
        'AccountNumber' => $settings->settings['account_number'],
        'AccountPin' => $settings->settings['account_pin'],
        'UserName' => $settings->settings['user_name'],
        'Password' => $settings->settings['password'],
        'Version' => 'v1.0',
        'Source' => 31,
        'address' => $settings->settings['address'],
        'city' => $settings->settings['city'],
        'state' => $settings->settings['state'],
        'postalcode' => $settings->settings['postalcode'],
        'country' => $settings->settings['country'],
        'name' => $settings->settings['name'],
        'company' => $settings->settings['company'],
        'phone' => $settings->settings['phone'],
        'email' => $settings->settings['email_origin'],
        'report_id' => $settings->settings['report_id'],
    );
}
        

