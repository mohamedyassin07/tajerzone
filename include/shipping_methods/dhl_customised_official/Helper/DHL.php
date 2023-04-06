<?php
    class DHL
    {
        /**
         * @var optional
         */
        private $dataHelper;

        /**
         * @var optional
         */
        private $key;

        /**
         * @var optional
         */
        private $secret;

        /**
         * @var optional
         */
        private $apiUrl;

        const API_URL = 'https://express.api.dhl.com/mydhlapi/';

        const API_URL_TEST = 'https://express.api.dhl.com/mydhlapi/test/';

        const API_MOCK_URL = 'https://api-mock.dhl.com/mydhlapi/';

        
        public function __construct()
        {
            $this->dataHelper = optional(get_option('woocommerce_dhl_method_settings'));
            $this->key        = $this->dataHelper->pass_key;
            $this->secret     = $this->dataHelper->pass_secret;
            if( DHL_MOCK ){
                $this->apiUrl     = self::API_MOCK_URL;
            } elseif ( $this->dataHelper->dhl_sandbox == 'yes'  ) {
                $this->apiUrl     = self::API_URL_TEST;
            }else{
                $this->apiUrl     = self::API_URL;
            }

        }

        private function createRequest( $method='POST', $body = array(), $header = array(), $endPoint ){

            if( empty($this->key) && empty($this->secret)){
                return;
            }
            
            $Authorization = base64_encode($this->key . ":" . $this->secret);
            $url = $this->buildRequestUrl( $endPoint, $header );

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Basic ' . $Authorization,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                $error_msg = curl_error($curl);
            }

            curl_close($curl);

            if ( isset($error_msg) ) {
                // TODO - Handle cURL error accordingly
                return "Something went wrong: $error_msg";
            }else{
                return json_decode( $response );
            }
        }

        private function buildRequestUrl($endPoint='rates' , $data = array()){
           
            $url_query = [];
            if( $endPoint == 'rates' || !empty($data) ){
                $url_query = http_build_query( $data );
                // prr($url_query);
                $url = $this->apiUrl . $endPoint . '?' . $url_query ;
            }else{
                $url = $this->apiUrl . $endPoint; 
            }

            return $url;
        }

        public function addShipments( $body = array()  , $header = array() )
        {
            try
            {
                return $this->createRequest( $method='POST', $body, $header, 'shipments');
            } catch( Exception $exception )
            {
                return $exception;
            }
        }

        public function getRatesPost( $body = array(), $header = array() )
        {
            try
            {
                $response = $this->createRequest( $method='POST', $body, $header, 'rates');
                return $response;
            } catch( Exception $e )
            {
                return $e->getMessage();
            }
        }

        public function getRates( $body = array(), $header = array() )
        {

            try
            {
                $response = $this->createRequest( $method='GET', $body, $header, 'rates');
                return $response;
            } catch( Exception $e )
            {
                return $e->getMessage();
            }
        }

        public function LandingCost( $body = array()  , $header = array() )
        {
            try
            {
                $response = $this->createRequest( $method='POST', $body, $header, 'rates');
                return $response;
            } catch( Exception $exception )
            {
                return $exception->getMessage();
            }
        }

        public function Tracking( $body = array()  , $header = array(), $number )
        {
            try
            {
                return $this->createRequest( $method='GET', $body, $header, 'shipments/'. $number .'/tracking');
            } catch( Exception $exception )
            {
                return $exception->getMessage();
            }
        }

        public function getImage( $body = array()  , $header = array(), $number )
        {
            try
            {
                return $this->createRequest( $method='GET', $body, $header, 'shipments/'. $number .'/get-image');
            } catch( Exception $exception )
            {
                return $exception->getMessage();
            }
        }
        
       
    }