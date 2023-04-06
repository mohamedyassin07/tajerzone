<?php    

function tjr_create_dhl_ship( $order_id )
{
    if( empty( $order_id ) ) {
      return;
    }
    $seller_id = dokan_get_seller_id_by_order( $order_id );
    $seller = new WP_User($seller_id);
    $seller_adress =  $seller->dokan_profile_settings['address'];
    // prr( $seller->dokan_profile_settings );
    // get order details data...
    $order = new WC_Order($order_id);
    $DHL_Shipping_Method = new DHL_Shipping_Method();
    $dataHelper = optional($DHL_Shipping_Method->settings);
    $shipping_methods = $order->get_shipping_method();
    $payment_method = $order->get_payment_method();
    // prr($dataHelper);
    if($shipping_methods != $dataHelper->title)
    {
        $order->update_meta_data('_shipping_method_awb' , '_dhl');
        $order->save();
                      
        $customer_name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();
        $currency_code = $order->get_currency();
        $currency_symbol = get_woocommerce_currency_symbol($currency_code);
        
        $weight = 0;
        $_item_line = [];
        $i = 0;
        foreach($order->get_items() as $key => $item)
        { $i++;
          
            $_weight = get_post_meta($item->get_data()['product_id'] , '_weight' , true);
            if($_weight)
            {
                $weight += $_weight * $item->get_quantity();
            }
            $product      = $item->get_product();
            $price = $product->get_price();
            $_item_line[] = [
              "number"=> $i,
              "description"=> $product->get_description(),
              "price"=> intval($price),
              "quantity"=> [
                "value"=> $item->get_quantity(),
                "unitOfMeasurement"=> "GM"
              ],
              "manufacturerCountry"=> "SA",
              "weight"=> [
                "netValue"=> $weight,
                "grossValue"=> $weight
              ]
            ];
        }
        
        $body = array();
        $line_item = $_item_line;
        $plannedShippingDateAndTime = date("Y-m-d",strtotime(date("Y-m-d"). " +2 days ")) . 'T' . date("H:m:s") . ' GMT' . date("P") ;

        $productCode = 'P';
        if ( $seller_adress['country'] == $order->get_shipping_country() ) {
          $productCode = 'N';
        }

        $data = [
          "plannedShippingDateAndTime"=> $plannedShippingDateAndTime,
          "pickup"=> [
            "isRequested"=> false
          ],
          "productCode"=> "N",
          "getRateEstimates"=> false,
          "accounts"=> [
            [
              "number"=> "460992828",
              "typeCode"=> "shipper"
            ]
          ],
          "outputImageProperties"=> [
            "printerDPI"=> 300,
            "encodingFormat"=> "pdf",
            "imageOptions"=> [
              [
                "typeCode"=> "waybillDoc",
                "templateName"=> "ARCH_8x4",
                "isRequested"=> true,
                "hideAccountNumber"=> false,
                "numberOfCopies"=> 1
              ],
              [
                "typeCode"=> "label",
                "templateName"=> "ECOM26_84_001",
                "isRequested"=> true
              ]
            ],
            "splitTransportAndWaybillDocLabels"=> true,
            "allDocumentsInOneImage"=> false,
            "splitDocumentsByPages"=> true,
            "splitInvoiceAndReceipt"=> true,
            "receiptAndLabelsInOneImage"=> false
          ],
          "customerDetails"=> [
            "shipperDetails"=> [
              "postalAddress"=> [
                "postalCode"   => $seller_adress['zip'],
                "cityName"     => $seller_adress['city'],
                "countryCode"  => $seller_adress['country'],
                "addressLine1" => $seller_adress['street_1'],
              ],
              "contactInformation"=> [
                "phone"       => $seller->dokan_profile_settings['phone'],
                "companyName" => $seller->dokan_profile_settings['store_name'],
                "fullName"    => $seller->display_name
              ]
            ],
            "receiverDetails"=> [
              "postalAddress"=> [
                "postalCode"=> $order->get_shipping_postcode(),
                "cityName"=> $order->get_shipping_city(),
                "countryCode"=> $order->get_shipping_country(),
                "addressLine1"=> $order->get_shipping_address_1(),
              ],
              "contactInformation"=> [
                "email"=> $order->get_billing_email(),
                "phone"=> $order->get_billing_phone(),
                "companyName"=> $order->get_billing_email(),
                "fullName"=> $customer_name
              ]
            ]
          ],
          "content"=> [
            "packages"=> [
              [
                "typeCode"=> "2BP",
                "weight"=> $weight,
                "dimensions"=> [
                  "length"=> 1,
                  "width"=> 1,
                  "height"=> 1
                ]
              ]
            ],
            "isCustomsDeclarable"=> false,
            "description"=> "Shipment Description",
            "incoterm"=> "DAP",
            "unitOfMeasurement"=> "metric"
          ],
          "getTransliteratedResponse"=> false,
          // "estimatedDeliveryDate"=> [
          //   "isRequested"=> false,
          //   "typeCode"=> "QDDC"
          // ],
          "getAdditionalInformation"=> [
            [
              "typeCode"=> "pickupDetails",
              "isRequested"=> true
            ]
          ]
        ];
        
        $log_data = [
            'order_id' => $order->get_Id() ,
            'customer_id' => $order->get_customer_id() ,
            'customer_name' => $customer_name ,
            'vendor_id' => $seller_id,
            'tjr_shipping_method' =>  'DHL',
        ];
        $dhl = new DHL();
        $json_body = json_encode($data);
        // send data soap
        $addShipments = $dhl->addShipments( $json_body, $header = [] );

        if( ! isset( $addShipments->shipmentTrackingNumber ) ){
            // submit log
            wp_insert_post([
                'post_type' => 'tjr_log' ,
                'post_excerpt' => print_r($addShipments),
                'meta_input' => $log_data ,
            ]);

        } else {
            $shipments_status = $dhl->Tracking( $body = array()  , $header = array(), $addShipments->shipmentTrackingNumber );
            
          
            // set awd_number order
            if(is_numeric($addShipments->shipmentTrackingNumber) && $addShipments->shipmentTrackingNumber > 0 )
            {
                // get status
                $order->update_meta_data('awb_number' , $addShipments->shipmentTrackingNumber );
                $order->update_meta_data('awb_status' , isset($shipments_status->shipments[0]->status) ? $shipments_status->shipments[0]->status : 'error');
                $order->save_meta_data();
            }

            if( isset( $addShipments->documents[0] ) ) {
              $order->update_meta_data('awb_lable' , $addShipments->documents[0]->content );
              $order->save_meta_data();
            }
            
            $log_data['awb_number'] = $addShipments->shipmentTrackingNumber;
            $log_data['awb_status'] = ($shipments_status instanceof Exception) ? '' : $shipments_status ;

            // submit log
            wp_insert_post([
                'post_type' => 'tjr_log' ,
                'post_excerpt' => $addShipments->shipmentTrackingNumber ,
                'meta_input' => $log_data ,
            ]);
        }
    }         
}

     
    // Add a custom metabox only for shop_order post type (order edit pages)
    add_action('woocommerce_order_details_after_customer_details' , function(){
        $order_id = optional($_GET,get_query_var('view-order'))->{'view-order'};
        
        if(get_post_meta($order_id , '_shipping_method_awb' , true) == '_DHL')
        {
            require __DIR__ . '/view.php';
            awb_admin_message_clear();
        }
    });