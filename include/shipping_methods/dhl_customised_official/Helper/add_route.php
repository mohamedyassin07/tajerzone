<?php
    add_action('init' , function(){
        add_rewrite_rule('^awb/pdf' , 'index.php' , 'top');
        add_rewrite_rule('^awb/show-pdf' , 'index.php' , 'top');
        add_rewrite_rule('^awb/status' , 'index.php' , 'top');
        add_rewrite_rule('^awb/tracking' , 'index.php' , 'top');
        add_rewrite_rule('^awb/tracking' , 'index.php' , 'top');
        add_rewrite_rule('^tjr/processes' ,"index.php" , 'top');
        //add_rewrite_rule('^tjr/processes' ,TJR_URL."views/frontend/processes_page_content.php" , 'top');
        flush_rewrite_rules();
    });
    
    add_action('init' , function($input){
        try
        {
            $method = new AWB_Url();
            $flag = false;
            
            if($method->check_url_awb('/awb/pdf'))
            {
                $method->getPdf();
                $flag = true;

            }
            else if($method->check_url_awb('/awb/show-pdf'))
            {
                $method->getShowPdf();
                $flag = true;
            }
            else if($method->check_url_awb('/awb/status'))
            {
                $method->getStatus();
                $flag = true;
            }
            else if($method->check_url_awb('/awb/tracking'))
            {
                $method->getTracking();
                $flag = true;
            }
            
            if($flag)
            {
                wp_redirect($method->getUrl());
                exit;
            }
            
        } catch( Exception $exception )
        {
        
        }
        
        
    });
    
    class AWB_Url
    {
        public function __construct()
        {
            $this->order = new WC_Order(optional($_GET , '')->order_id);
            $this->res = optional($_GET , '');
            $this->awb_number = get_post_meta($this->order->get_id() , 'awb_number' , true);
            $this->SMSA = new SMSA_API();
        }
        
        public function check_url_awb($url)
        {
            return false !== strpos($_SERVER['REQUEST_URI'] , $url);
        }
        
        public function getUrl()
        {
            if(optional($_GET , '')->is_admin)
            {
                return get_admin_url('' , "/post.php?post={$this->order->get_id()}&action=edit");
            }
            
            return $this->order->get_view_order_url();
        }
        
        public function getPdf()
        {
            $base64 = $this->SMSA->downloadPdf($this->awb_number);
            
            if($base64 instanceof Exception)
            {
                if($this->is_admin())
                {
                    $_SESSION['awb_admin_error_message'] = __('get pdf error : ' , 'webgate') . $base64->getMessage();
                }
                else
                {
                    wc_add_notice(__('awb pdf error ' , 'webgate') , 'error');
                }
            }
            
        }
        
        public function getShowPdf()
        {
            $base64 = $this->SMSA->showPdf($this->awb_number);
            
            if($base64 instanceof Exception)
            {
                if($this->is_admin())
                {
                    $_SESSION['awb_admin_error_message'] = __('Show pdf error : ' , 'webgate') . $base64->getMessage();
                }
                else
                {
                    wc_add_notice(__('Show pdf error ' , 'webgate') , 'error');
                }
            }
        }
        
        private function is_admin()
        {
            return current_user_can('editor')
                || current_user_can('administrator')
                && optional($_GET , '')->is_admin;
        }
        
        public function getStatus()
        {
            $status = $this->SMSA->getStatus($this->awb_number);
            
            if($status instanceof Exception)
            {
                if($this->is_admin())
                {
                    $_SESSION['awb_admin_error_message'] = __('update status error : ' , 'webgate') . $status->getMessage();
                }
            }
            else
            {
                update_post_meta($this->order->get_id() , 'awb_status' , $status);
                
                if($this->is_admin())
                {
                    $_SESSION['awb_admin_success_message'] = __('awb_status update');
                }
                else
                {
                    wc_add_notice(__('awb status update' , 'webgate') , 'success');
                }
            }
        }
        
        public function getTracking()
        {
            $tracking = $this->SMSA->getTracking($this->awb_number);
            
            if($tracking instanceof Exception)
            {
                if($this->is_admin())
                {
                    $_SESSION['awb_admin_error_message'] = __('get Tracking error : ' , 'webgate') . $tracking->getMessage();
                }
                else
                {
                    wc_add_notice(__('get Tracking error' , 'webgate') , 'error');
                }
                $_SESSION['AvbTracking'] = [];
            }
            else
            {
                $_SESSION['AvbTracking'] = $tracking;
                if($this->is_admin())
                {
                    $_SESSION['awb_admin_success_message'] = __('get Tracking Success' , 'webgate');
                }
                else
                {
                    wc_add_notice(__('get Tracking Success' , 'webgate') , 'success');
                }
            }
        }
    }
    
