<?php
    class SMSA_API
    {
        /**
         * @var optional
         */
        private $dataHelper;

        
        public function __construct()
        {
            $this->dataHelper = optional(get_option('woocommerce_webgate_method_settings'));
        }
        
        /**
         * @param $data array
         *
         * @return Exception | object
         */
        public function addShipMPS($data)
        {
            try
            {
                $data = array_merge($data , [ 'passKey' => $this->dataHelper->passkey ]);
                $response = $this->createRequest()->addShipMPS((object)$data);

            } catch( Exception $exception )
            {

                $response = $exception->getMessage();
            }

            return $response;
        }

        public function getAllRetails()
        {
            try
            {
                $data = [ 'passKey' => $this->dataHelper->passkey ];
                return $this->createRequest()->getAllRetails((object)$data)->getAllRetailsResult;
            } catch( SoapFault $e )
            {
                return $e->faultstring;
            }
        }

        public function getShipCharges($data)
        {
            try
            {
                $data = array_merge($data , [ 'passKey' => $this->dataHelper->passkey ]);
                return $this->createRequest()->getShipCharges((object)$data)->getShipChargesResult;
            } catch( SoapFault $e )
            {
                return $e->faultstring;
            }
        }
        
        private function createRequest()
        {
            $options = [
                'style' => SOAP_RPC ,
                'use' => SOAP_ENCODED ,
                'soap_version' => SOAP_1_1 ,
                'cache_wsdl' => WSDL_CACHE_NONE ,
                'connection_timeout' => 15 ,
                'trace' => true ,
                'encoding' => 'UTF-8' ,
                'exceptions' => true ,
                'passKey' => $this->dataHelper->passkey ,
            ];
            
            return new SoapClient( $this->dataHelper->api_url , $options );
        }
        
        /**
         * @param $awbNo int
         *
         * @return Exception | object
         */
        public function downloadPdf($awbNo)
        {
            $pdf = $this->getPDF($awbNo);
            if( empty( $pdf ) )
            {
                return 'pdf';
            }
            else
            {
                return $pdf;
            }
        }
        
        /**
         * @param $awbNo int
         *
         * @return Exception | object
         */
        public function showPdf($awbNo)
        {
            $pdf = $this->getPDF($awbNo);
    
            if($pdf instanceof Exception)
            {
                return $pdf;
            }
            else
            {
                header('Content-type: application/pdf');
                echo $pdf;
                die();
            }
        }
        
        /**
         * @param $awbNo int
         *
         * @return Exception | object
         */
        public function getPDF($awbNo)
        {
            try
            {
                $data = [ 'awbNo' => $awbNo , 'passKey' => $this->dataHelper->passkey ];
                return $this->createRequest()->getPDF((object)$data)->getPDFResult;
            } catch( SoapFault $e )
            {
                return $e->faultstring;
            }
        }
        
        /**
         * @param $awbNo int
         *
         * @return Exception | object
         */
        public function getStatus($awbNo)
        {
            
            try
            {
                $data = [ 'awbNo' => $awbNo , 'passkey' => $this->dataHelper->passkey ];
                // return $this->createRequest()->getStatus((object)$data)->getStatusResult;
                $getStatus = $this->createRequest()->getStatus((object)$data);
                $getStatus = isset( $getStatus->getStatusResult ) ? $getStatus->getStatusResult : [];
                return $getStatus;
            } catch( Exception $exception )
            {
                return $exception;
            }
        }
        
        /**
         * @param $awbNo int
         *
         * @return Exception | array
         */
        public function getTracking($awbNo)
        {
            try
            {
                $data = [ 'awbNo' => $awbNo , 'passkey' => $this->dataHelper->passkey ];
                $result = $this->createRequest()->getTracking((object)$data);
                $xml = @simplexml_load_string($result->getTrackingResult->any);
                if( is_array($xml) || is_object($xml) )
                {
                  
                    $track = $xml->NewDataSet[0]->Tracking;
                    return [
                        'Awb Number' => (string)$track->awbNo ,
                        'Date' => (string)$track->Date ,
                        'Activity' => (string)$track->Activity ,
                        'Details' => (string)$track->Details ,
                        'Location' => (string)$track->Location ,
                        'Reference' => (string)$track->refNo ,
                    ];
                }
                return [];
            } catch( Exception $exception )
            {
                return $exception;
            }
        }
        /**
         * @param $awbNo int
         *
         * @return Exception | array
         */
        public function cancelShipment($awbNo)
        {
            try
            {
                $data = [ 
                    'awbNo' => $awbNo ,
                    'passkey' => $this->dataHelper->passkey ,
                    'reas' => 'Testing Smsa Api',
                ];
                return $this->createRequest()->cancelShipment((object)$data)->cancelShipmentResult;
            } catch( SoapFault $e )
            {
                return $e->faultstring;
            }

        }

        public function saphOrderReady($data)
        {
            try
            {   
                $data = array_merge([ 'passKey' => $this->dataHelper->passkey ] , $data );
                // return $data;
                return $this->createRequest()->saphOrderReady((object)$data)->saphOrderReadyResult;
            } catch( SoapFault $e )
            {
                return $e->faultstring;
            }
        }
    }