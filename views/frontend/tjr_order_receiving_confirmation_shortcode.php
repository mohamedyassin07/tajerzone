<?php
$order_id = $data['order_id'];
$order =  new WC_Order($order_id);
$has_access =  false;

if( $order->get_user_id() == get_current_user_id() ){
    $has_access =  true;
}
if(!$has_access){
    echo __('You don\'t have acess to Confirmed Received this order ' , 'tjr');
    return;
}

update_post_meta( $order_id, 'recieving_confirmation',0);
$confirmation = get_post_meta( $order_id, 'recieving_confirmation', true);
$confirmation = $confirmation == 1 ?  $confirmation : false;
if($confirmation){
    echo __('This order is already Confirmed Received' , 'tjr');
    return;
}

$status =  $order->get_status();
// prr($status);
if($status == 'completed'){
    echo __('This is Order is already marked as Completed' , 'tjr');
}elseif ( $status == 'shipped' || $status == 'on-hold' || $status == 'processing' ) {
    $duration = option_val('tjr_settings_order_receiving_confirmation_duration');
    $duration = is_numeric($duration) ? $duration : 14;
    $duration = $duration *  24*60*60; // in seconds
    $duration = $duration - (current_time('timestamp') -  $order->get_date_created()->getTimestamp());
    $duration = $duration > 0 ? $duration : false;
    if(!$duration){
        update_post_meta( $order_id, 'recieving_confirmation',1);
        echo __('it\'s too late to change approve this order receiving' , 'tjr');
    }else { 
        ?>
        <div id='tjr_approving_div'>
            <h3 class='center'><?= __('Please Approve your Order Receiving Once you already Received it' , 'tjr');?></h3>   
            <label class='center' style="
                display: flex;
                flex-direction: row;
                align-items: center;
                align-content: center;
                justify-content: flex-start;
            "><?= __('I Recieved the Order' , 'tjr');?>
            <input id='tjr_approving_checkbox' class='center' type="checkbox" value="1" style="margin: 0 10px;">
            <button id='tjr_approving_button'><?= __('Approve' , 'tjr');?></button>
            </label>
        </div>
        <div id='tjr_approving_msg'>
        </div>

        <script>
            jQuery(document).ready( function(){	
                jQuery( "#tjr_approving_button" ).click(function(e) { 
                    e.preventDefault();
                    var msg = '';
                    var checked =  jQuery( "#tjr_approving_checkbox" ).val();
                    if (jQuery('#tjr_approving_checkbox').is(':checked')) {
                        jQuery.ajax({
                            url : '<?= admin_url('admin-ajax.php') ?>',
                            type : 'post',
                            data : {
                                action : 'tjr_recieving_confirmation',
                                order_id : <?= $order_id;?> ,
                            },
                            beforeSend : function(response) {
                                msg = "</br></br><h3>" + 'Please Wait . . . . .' + "</h3></br></br>";
                            },
                            success : function(response) {
                                msg = response.data;
                                jQuery('#tjr_approving_msg').html('');    
                                jQuery('#tjr_approving_div').html('</br></br><h3>'+msg+'</h3></br></br>');
                            }
                        });
                    }else{
                        msg = "</br></br><h3>Please mark the Select</h3></br></br>";
                    }
                    jQuery('#tjr_approving_msg').html('');  
                    jQuery('#tjr_approving_msg').html(msg);          
                });
            });    
        </script>


<?php }
}else {
    echo __('You Can\'t Approve Recieving this order at least Now' , 'tjr');
}
?>