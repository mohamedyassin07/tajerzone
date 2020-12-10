<?php

/**
 * NOTE: This code example uses the generic vendor prefix 'prefix_' and omits text domains where
 * the WordPress internationalization functions are used. You should replace 'prefix_' with your
 * own prefix and insert your text domain where appropriate when incorporating this code into your
 * plugin or theme.
 */

/**
 * Adds an '_orders' tab to the Dokan settings navigation menu.
 *
 * @param array $menu_items
 *
 * @return array
 */
function prefix_add__orders_tab( $menu_items ) {
    $_orders = [
        'title'      => __( ' Orders' ),
        'icon'       => '<i class="fa fa-user-circle"></i>',
        'url'        => dokan_get_navigation_url( 'settings/_orders' ),
        'pos'        => 90,
        'permission' => 'dokan_view_store_settings_menu',
    ];

    return $menu_items;
}

add_filter( 'dokan_get_dashboard_nav', 'prefix_add__orders_tab' );

/**
 * Sets the title for the '_orders' settings tab.
 *
 * @param string $title
 * @param string $tab
 *
 * @return string Title for tab with slug $tab
 */
function prefix_set__orders_tab_title( $title, $tab ) {
    if ( '_orders' === $tab ) {
        $title = __( ' Orders' );
    }

    return $title;
}

add_filter( 'dokan_dashboard_settings_heading_title', 'prefix_set__orders_tab_title', 10, 2 );

/**
 * Sets the help text for the '_orders' settings tab.
 *
 * @param string $help_text
 * @param string $tab
 *
 * @return string Help text for tab with slug $tab
 */
function prefix_set__orders_tab_help_text( $help_text, $tab ) {
    if ( '_orders' === $tab ) {
        $help_text = __( 'this is the  Custome orders page.' );
    }

    return $help_text;
}

add_filter( 'dokan_dashboard_settings_helper_text', 'prefix_set__orders_tab_help_text', 10, 2 );

/**
 * Outputs the content for the '_orders' settings tab.
 *
 * @param array $query_vars WP query vars
 */
function prefix_output_help_tab_content( $query_vars ) {










    $order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;

    if ( $order_id ) {
        $_nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

        if ( wp_verify_nonce( $_nonce, 'dokan_view_order' ) && current_user_can( 'dokan_view_order' ) ) {
            view('seller_dashboard/orders/details');
        } else if ( isset ( $_REQUEST['_view_mode'] ) && 'email' == $_REQUEST['_view_mode'] && current_user_can( 'dokan_view_order' ) ) {
            dokan_get_template_part( 'orders/details' );
            view('seller_dashboard/orders/details');
        } else {
            dokan_get_template_part( 'global/dokan-error', '', array( 'deleted' => false, 'message' => __( 'You have no permission to view this order', 'dokan-lite' ) ) );
        }

    } else {
        dokan_get_template_part( 'orders/date-export' );
        dokan_get_template_part( 'orders/listing' );
    }









    if ( isset( $query_vars['settings'] ) && '_orders' === $query_vars['settings'] ) {
        if ( ! current_user_can( 'dokan_view_store_settings_menu' ) ) {
            dokan_get_template_part ('global/dokan-error', '', [
                'deleted' => false,
                'message' => __( 'You have no permission to view this page', 'dokan-lite' )
            ] );
        } else {
            $user_id        = get_current_user_id();
            $bio            = get_user_meta( $user_id, 'prefix_bio', true );
            $birthdate      = get_user_meta( $user_id, 'prefix_birthdate', true );
            $favorite_color = get_user_meta( $user_id, 'prefix_favorite_color', true );

            ?>
            <form method="post" id="settings-form"  action="" class="dokan-form-horizontal">
                <?php wp_nonce_field( 'dokan__orders_settings_nonce' ); ?>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="bio">
                        <?php esc_html_e( 'Bio' ); ?>
                    </label>
                    <div class="dokan-w5">
                        <textarea class="dokan-form-control" name="bio" id="bio" placeholder="<?php esc_attr_e( 'Tell your story' ); ?>"><?php echo esc_html( $bio ); ?></textarea>
                        <p class="help-block"><?php esc_html_e( 'Tell your customers a little _orders yourself.' ); ?></p>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="birthdate">
                        <?php esc_html_e( 'Birthdate' ); ?>
                    </label>
                    <div class="dokan-w5">
                        <input class="dokan-form-control" type="date" name="birthdate" id="birthdate" value="<?php echo esc_attr( $birthdate ); ?>">
                    </div>
                </div>

                <div class="dokan-form-group">
                    <label class="dokan-w3 dokan-control-label" for="favorite_color">
                        <?php esc_html_e( 'Favorite Color' ); ?>
                    </label>
                    <div class="dokan-w5">
                        <select class="dokan-form-control" name="favorite_color" id="favorite_color">
                            <?php
                            $colors = [
                                ''       => __( 'Select a color' ),
                                'red'    => __( 'Red' ),
                                'orange' => __( 'Orange' ),
                                'yellow' => __( 'Yellow' ),
                                'green'  => __( 'Green' ),
                                'blue'   => __( 'Blue' ),
                                'other'  => __( 'Other' ),
                            ];

                            foreach ( $colors as $value => $label ) {
                                printf( 
                                    '<option value="%s" %s>%s</option>',
                                    $value,
                                    selected( $value, $favorite_color, false ),
                                    $label
                                );
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="dokan-form-group">
                    <div class="dokan-w4 ajax_prev dokan-text-left" style="margin-left: 25%">
                        <input type="submit" name="dokan_update__orders_settings" class="dokan-btn dokan-btn-danger dokan-btn-theme" value="<?php esc_attr_e( 'Update Settings' ); ?>">
                    </div>
                </div>
            </form>

            <style>
                #settings-form p.help-block {
                    margin-bottom: 0;
                }
            </style>
            <?php
        }
    }
}

add_action( 'dokan_render_settings_content', 'prefix_output_help_tab_content' );

/**
 * Saves the settings on the '_orders' tab.
 *
 * Hooked with priority 5 to run before WeDevs\Dokan\Dashboard\Templates::ajax_settings()
 */
function prefix_save__orders_settings() {
    $user_id   = dokan_get_current_user_id();
    $post_data = wp_unslash( $_POST );
    $nonce     = isset( $post_data['_wpnonce'] ) ? $post_data['_wpnonce'] : '';

    // Bail if another settings tab is being saved
    if ( ! wp_verify_nonce( $nonce, 'dokan__orders_settings_nonce' ) ) {
        return;
    }

    $bio            = sanitize_text_field( $post_data['bio'] );
    $birthdate      = sanitize_text_field( $post_data['birthdate'] );
    $favorite_color = sanitize_text_field( $post_data['favorite_color'] );

    // Require that the user is 18 years of age or older
    $eighteen_years_ago = strtotime( '-18 years 00:00:00' );

    if ( $birthdate && strtotime( $birthdate ) > $eighteen_years_ago ) {
        wp_send_json_error( __( 'You must be at least eighteen years old - is your birthdate correct?' ) );
    }

    update_user_meta( $user_id, 'prefix_bio', $bio );
    update_user_meta( $user_id, 'prefix_birthdate', $birthdate );
    update_user_meta( $user_id, 'prefix_favorite_color', $favorite_color );

    wp_send_json_success( array(
        'msg' => __( 'Your information has been saved successfully' ),
    ) );
}

add_action( 'wp_ajax_dokan_settings', 'prefix_save__orders_settings', 5 );