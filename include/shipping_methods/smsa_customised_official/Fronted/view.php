
<section class="woocommerce-customer-details">

  <h2><?= __('AWB Method' , 'webgate') ?></h2>

  <section>

    <div><b>Status </b><span><?= get_post_meta($order_id , 'awb_status' , true) ?></span></div>
    <div><b>Number </b><span><?= get_post_meta($order_id , 'awb_number' , true) ?></span></div>
      
      
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


  </section>


</section>