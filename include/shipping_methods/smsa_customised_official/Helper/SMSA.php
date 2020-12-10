<?php
    
    class SMSA
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
                return $this->createRequest()->addShipMPS((object)$data);
            } catch( Exception $exception )
            {
                return $exception;
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
            
            return new SoapClient($this->dataHelper->api_url , $options);
        }
        
        /**
         * @param $awbNo int
         *
         * @return Exception | object
         */
        public function downloadPdf($awbNo)
        {
            $pdf = $this->getPDF($awbNo);
            if($pdf instanceof Exception)
            {
                return $pdf;
            }
            else
            {
                $file = 'awb' . $awbNo . '.pdf';
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $file . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                echo $pdf;
                die();
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
            } catch( Exception $exception )
            {
                return $exception;
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
                return $this->createRequest()->getStatus((object)$data)->getStatusResult;
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
                if(is_array($xml) && $xml->count() > 0)
                {
                    $track = $xml->NewDataSet[0]->Tracking;
                    return [
                        'awb' => (string)$track->awbNo ,
                        'date' => (string)$track->Date ,
                        'activity' => (string)$track->Activity ,
                        'details' => (string)$track->Details ,
                        'location' => (string)$track->Location ,
                        'reference' => (string)$track->refNo ,
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
                $data = [ 'awbNo' => $awbNo , 'passkey' => $this->dataHelper->passkey ];
                return $this->createRequest()->cancelShipment((object)$data);
            } catch( Exception $exception )
            {
                return $exception;
            }

        }
    }