<?php
add_filter( 'display_post_states', 'tjr_processes_page_display_post_states', 10, 2 );
function tjr_processes_page_display_post_states( $post_states, $post ) {
    $process_page_id =  get_option('tjr_settings_processes_page');
    $process_page_id =  !empty( $process_page_id ) ? $process_page_id[0] : 0;

    if( $post->ID == $process_page_id ) {
        $post_states[] = __('TagerZone Processes Page', 'Tjr');
    }
    return $post_states;
}
function available_processes( $method ,$view_for, $nu = 0)
{
    $processes = array();
    if ($method == 'dhl') {
        if($nu && $nu > 0 ){
            $processes = array(
                'getStatus' => array('title'=> __('Get Status','tjr')),
                'Tracking' => array('title'=> __('Get Tracking','tjr')),
                'downloadPdf' => array('title'=> __('Download Pdf','tjr')),
            );
            // if(production == false){
            //     $processes['cancelShipment'] =  array('title'=> __('Cancel Shipment','tjr'),'view_for'=>'mangers');
            //     $processes['regenerateShipment'] =  array('title'=> __('Regenerate Shipment','tjr'),'view_for'=>'mangers');
            //     $processes['getPDF'] =  array('title'=> __('Shipment PDF','tjr'),'view_for'=>'mangers');
            
            // }
        }else {
            $processes['addShipMPS'] =  array('title'=> __('Add Shipping','tjr'));
        }
    }elseif ($method == 'smsa') {
        if($nu && $nu > 0 ){
            $processes = array(
                'getStatus' => array('title'=> __('Get Status','tjr')),
                //'downloadPdf' => array('title'=> __('Download Pdf','tjr')),
                'getTracking' => array('title'=> __('Get Tracking','tjr')),
            );
            if(production == false){
                $processes['cancelShipment'] =  array('title'=> __('Cancel Shipment','tjr'),'view_for'=>'mangers');
                $processes['regenerateShipment'] =  array('title'=> __('Regenerate Shipment','tjr'),'view_for'=>'mangers');
                $processes['getPDF'] =  array('title'=> __('Shipment PDF','tjr'),'view_for'=>'mangers');
            
            }
        }else {
            $processes['addShipMPS'] =  array('title'=> __('Add Shipping','tjr'));
        }
    }elseif ($method == 'aramex') {
        if($nu && $nu > 0 ){
            $processes = array(
                'getTracking' => array('title'=> __('Get Tracking','tjr')),
                'downloadPdf' => array('title'=> __('Download Pdf','tjr')),
            );
            if( production == false ){
                $processes['cancelShipment'] =  array('title'=> __('Cancel Shipment','tjr'),'view_for'=>'mangers');
                $processes['regenerateShipment'] =  array('title'=> __('Regenerate Shipment','tjr'),'view_for'=>'mangers');
            }
        }else {
            // $processes['addShipMPS'] =  array('title'=> __('Add Shipping','tjr'));
        }
    }
    if($view_for == 'client'){
        foreach ($processes as $key => $process) {
            if(isset($process['view_for']) && $process['view_for'] == 'mangers'){
                unset($processes[$key]);
            }
        }    
    }
    return $processes ;
}