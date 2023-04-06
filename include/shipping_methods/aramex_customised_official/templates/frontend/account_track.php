<?php
/*
Plugin Name:  Aramex Shipping WooCommerce
Plugin URI:   https://aramex.com
Description:  Aramex Shipping WooCommerce plugin
Version:      1.0.0
Author:       aramex.com
Author URI:   https://www.aramex.com/solutions-services/developers-solutions-center
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  aramex
Domain Path:  /languages
*/
?>
<?php
/**
*  Render "Tracking template" under account
*
* @param $order_id string order id
* @return string Template
*/

function aramex_view_order_tracking($order_id)
{

	$order = wc_get_order($order_id);
	$shipping_method = get_primary_related_shipping_method($order);
    $shipping_method_lable = explode(' ',trim(strtolower($shipping_method)));

	if( isset($shipping_method_lable[0]) && $shipping_method_lable[0] != 'aramex') {
		return;
	}
	$img = TJR_URL .'assets/img/aramex.png';
    ?>
<section class="woocommerce-customer-details" style="border: 2px dashed;padding: 2rem;margin: 2rem 0;background: #ddd;">
<header>
    <img src="<?= $img; ?>" style="max-width: 200px;margin-bottom: 30px;" />
	<h2>Aramex order tracking</h2>
  </header>
	<div style="padding-bottom: 2rem;">
		<?php echo do_shortcode( '[tjr-order-receiving-confirmation order_id='. $order_id .']'); ?>
	</div>
	<?php 
	$confirmation = get_post_meta( $order_id, 'recieving_confirmation', true);
	$confirmation = $confirmation == 1 ?  $confirmation : false;
	if(!$confirmation){
	?>
    <table class="woocommerce-table shop_table aramex_info" style="border: 2px solid;box-shadow: 3px 3px #000;">
        <tbody>
            <tr>
                <th>Aramex AWB No.</th>
                <td><?php 
                  global $wpdb;
				    $table_perfixed = $wpdb->prefix . 'comments';
				    $history = $wpdb->get_results("
								        SELECT *
								        FROM $table_perfixed
								        WHERE  `comment_post_ID` = $order_id
								        AND  `comment_type` LIKE  'order_note'
								    ");
				    $history_list = array();
				    foreach ($history as $shipment) {
				        $history_list[] = $shipment->comment_content;
				    }
				    $last_track = "";
				    if (!empty($history_list)) {
				        foreach ($history_list as $history) {
				            $awbno = strstr($history, "- Order No", true);
				            $awbno = trim($awbno, "AWB No.");
				            if (isset($awbno)) {
				                if ((int)$awbno) {
				                    $last_track = $awbno;
				                    break;
				                }
				            }
				            $awbno = trim($awbno, "Aramex Shipment Return Order AWB No.");
				            if (isset($awbno)) {
				                if ((int)$awbno) {
				                    $last_track = $awbno;
				                    break;
				                }
				            }
				        }
				    }
					$_last_track = !empty($last_track) ? $last_track : '28282828282'; // for testing
    echo (!empty($_last_track)) ? "<a target='_blank' href='https://www.aramex.com/track/results?ShipmentNumber=". esc_attr($_last_track) . "'>". esc_attr($_last_track) . "</a>"   : 'Not created'; ?>
                </td>
            </tr>
        </tbody>
    </table>
	<?php } ?>
</section>
<?php 
}