<?php 
$order = wc_get_order( $order_id );
$shipping_method = get_primary_related_shipping_method($order);
$shipping_method_lable = explode(' ',trim(strtolower($shipping_method)));
$heading = 'h2';
$title = '';
$img = '';
if(strpos(" " .strtolower($shipping_method),'smsa')){
  $img = TJR_URL .'assets/img/smsa.png';
  $awb_no     = get_post_meta($order_id,'awb_number',true);
  $awb_no     = $awb_no >   0  ?  $awb_no : '' ; // Just for testing
  $awb_status = get_post_meta($order_id,'awb_status',true);
  $title = "<$heading>".$shipping_method."</$heading>";
  $style = "background: #d5d5d5;
    color: #161616;
    padding: 2rem;
    margin: 1rem 0;
    border: 2px dashed;";
}elseif (strpos(" " .strtolower($shipping_method),'aramex')){
  $awb_no     =  get_post_meta($order_id,'awb_number',true);
  $awb_no     =  $awb_no >   0  ?  $awb_no : '' ; // Just for testing
  $awb_status =  get_post_meta($order_id,'awb_status',true);
  $title = "<$heading>".__("Shipping Method is : ",'tjr').$shipping_method.__(" with  AWB NO : ",'tjr').'<span id="tjr_tracking_no">'.$awb_no."</span></$heading>";
  $style = "background: #d5d5d5;
    color: #161616;
    padding: 2rem;
    margin: 1rem 0;
    border: 2px dashed;";

}

?>
<section class="woocommerce-customer-details" style="<?= $style ?>">
  <header>
    <img src="<?= $img; ?>" />
    <?= $title ?>
  </header>

  <section>
     <?php 
        echo do_shortcode( '[tjr-order-receiving-confirmation order_id='. $order_id .']');
        $confirmation = get_post_meta( $order_id, 'recieving_confirmation', true);
        $confirmation = $confirmation == 1 ?  $confirmation : false;
        if(!$confirmation){
     ?>
    <div><b>Shipping Status : </b><span><?= get_post_meta($order_id , 'awb_status' , true) ?></span></div>
    <div><b>Tracking Number : </b><span><?= get_post_meta($order_id , 'awb_number' , true) ?></span></div>
      
      
      <?php
          
          $AvbTracking = optional($_SESSION)->AvbTracking;
          if(is_array($AvbTracking))
          {
              ?>
            <br><br>
            <h2><?= __('Tracking' , 'webgate') ?></h2>
              <?php
              unset($_SESSION['AvbTracking']);
              foreach($AvbTracking as $key => $val)
              {
                  ?>
                <div><b><?= $key ?> : </b><span><?= $val ?></span></div>
                  <?php
              }
          }
      ?>

    <br><br>

    <a href='<?= get_site_url('' , "/awb/status?order_id={$order_id}") ?>' class="woocommerce-Button button">
        <?= __('Update Status' , 'webgate') ?>
    </a>

    <a href='<?= get_site_url('' , "/awb/tracking?order_id={$order_id}") ?>' class="woocommerce-Button button">
        <?= __('get Tracking' , 'webgate') ?>
    </a>

    <a href='<?= get_site_url('' , "/awb/pdf?order_id={$order_id}") ?>' target='_blank'
       class="woocommerce-Button button">
        <?= __('Print' , 'webgate') ?>
    </a>

    <a href='<?= get_site_url('' , "/awb/show-pdf?order_id={$order_id}") ?>' target='_blank'
       class="woocommerce-Button button">
        <?= __('Show Print' , 'webgate') ?>
    </a>

     <?php } ?>
  </section>


</section>