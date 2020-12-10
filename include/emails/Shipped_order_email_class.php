<?php
class WC_Confirmed_Order_Email  extends WC_Email {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'client_order_shipped';
        $this->title          = __( 'Client Order Shipped', 'tjr' );
        $this->description    = __( 'Order Shipped email is sent to customers when their orders shipped, to let them approve recieving it.', 'tjr' );
        $this->template_html  = 'emails/client_order_shipped.php';
        $this->template_plain = 'emails/plain/vendor-new-order.php';
        $this->template_base  = views;
        $this->placeholders   = array(
            '{site_title}'   => $this->get_blogname(),
            '{order_date}'   => '',
            '{order_number}' => '',
            '{confirmation_link}' =>'',
        );

        add_filter( 'woo_tjr_send_shipped_mail', [ $this, 'prevent_sub_order_admin_email' ], 10, 2 );

        add_action( 'woocommerce_order_status_pending_to_order_confirmed', array( $this, 'trigger' ) );
        add_action( 'woocommerce_order_status_processing_to_order_confirmed', array( $this, 'trigger' ) );

        parent::__construct();

        // Other settings.
        $this->recipient = 'customer@ofthe.order';
    }

    /**
     * Get email subject.
     *
     * @since  3.1.0
     * @return string
     */
    public function get_default_subject() {
        //return __( '[{site_title}] New customer order ({order_number}) - {order_date}', 'tjr' );
        return __('Verify Recieving Order #{order_number} from {site_title}','tjr');
    }

    /**
     * Get email heading.
     *
     * @since  3.1.0
     * @return string
     */
    public function get_default_heading() {
        return __( current_action() .  ' Verify Order #{order_number} Recieving <a href="{confirmation_link}">Verify Now</a>', 'tjr' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @param int $order_id The Order ID.
     * @param array $order.
     */
    public function trigger( $order_id, $order = false ) {
        remote_pre('email '  .  $order_id);

        if ( ! $this->is_enabled() ) {
            return;
        }

        $this->setup_locale();
        if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order_id );
        }

        if ( is_a( $order, 'WC_Order' ) ) {
            $this->object                               = $order;
            $this->placeholders['{order_date}']         = wc_format_datetime( $this->object->get_date_created() );
            $this->placeholders['{order_number}']       = $this->object->get_order_number();
            $this->placeholders['{confirmation_link}']  = $this->order_receiving_confirmation_link($this->object->get_order_number());
        }

        $sellers = dokan_get_seller_id_by_order( $order_id );
        if ( empty( $sellers ) ) {
            return;
        }

        // check has sub order
        if ( !$order->get_meta( 'has_sub_order' ) ) {
        	$this->order_info = dokan_get_vendor_order_details( $order_id, $sellers );
	        $this->send( $order->get_billing_email() , $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }
        $this->restore_locale();
    }

    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html, 
            
            array(
                'order'         => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'    => false,
                'email'         => $this,
                'order_info'    => $this->order_info,
            ),
            
            'dokan', 
            
            $this->template_base
        );
    }

    /**
     * Get content plain.
     *
     * @access public
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain, array(
                'order'         => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'    => true,
                'email'         => $this,
                'order_info'    => $this->order_info,
            ), 'dokan/', $this->template_base
        );
    }

    /**
     * Initialise settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __( 'Enable/Disable', 'tjr' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'tjr' ),
                'default' => 'yes',
            ),
            'subject'    => array(
                'title'       => __( 'Subject', 'tjr' ),
                'type'        => 'text',
                'desc_tip'    => true,
                /* translators: %s: list of placeholders */
                'description' => sprintf( __( 'Available placeholders: %s', 'tjr' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'tjr' ),
                'type'        => 'text',
                'desc_tip'    => true,
                /* translators: %s: list of placeholders */
                'description' => sprintf( __( 'Available placeholders: %s', 'tjr' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'email_type' => array(
                'title'       => __( 'Email type', 'tjr' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'tjr' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
            'message_content' => array(
                'title'       => __( 'Message Content', 'tjr' ),
                'type'        => 'textarea',
                'description' => __( 'Custom content undetr the heading', 'tjr' ),
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Prevent sub-order email for admin
     *
     * @param $bool
     * @param $order
     *
     * @return bool
     */
    public function prevent_sub_order_admin_email( $bool, $order ) {
        if ( ! $order ) {
            return $bool;
        }

        if ( $order->get_parent_id() ) {
            return false;
        }

        return true;
    }
}


