<?php
/**
 * Master Mart Theme Options & Admin Panel
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Admin Menu for Master Mart.
 */
function mastermart_add_admin_menu() {
    add_menu_page(
        __( 'Master Mart Settings', 'mastermart' ),
        __( 'Master Mart', 'mastermart' ),
        'manage_options',
        'mastermart-settings',
        'mastermart_render_settings_page',
        'dashicons-cart',
        59
    );
}
add_action( 'admin_menu', 'mastermart_add_admin_menu' );

/**
 * Register Settings.
 */
function mastermart_register_settings() {
    register_setting( 'mastermart_settings_group', 'mastermart_hotline_phone' );
    register_setting( 'mastermart_settings_group', 'mastermart_whatsapp_number' );
    register_setting( 'mastermart_settings_group', 'mastermart_shipping_inside' );
    register_setting( 'mastermart_settings_group', 'mastermart_shipping_outside' );
    register_setting( 'mastermart_settings_group', 'mastermart_product_price' );
    register_setting( 'mastermart_settings_group', 'mastermart_product_regular_price' );
    register_setting( 'mastermart_settings_group', 'mastermart_featured_product_id' );
    register_setting( 'mastermart_settings_group', 'mastermart_facebook_pixel_id' );
    register_setting( 'mastermart_settings_group', 'mastermart_gtm_id' );
    register_setting( 'mastermart_settings_group', 'mastermart_announcement_text' );
    register_setting( 'mastermart_settings_group', 'mastermart_header_scripts' );
    register_setting( 'mastermart_settings_group', 'mastermart_footer_scripts' );
}
add_action( 'admin_init', 'mastermart_register_settings' );

/**
 * Render Settings Page.
 */
function mastermart_render_settings_page() {
    ?>
    <div class="wrap" style="max-width: 900px; margin: 30px auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <div style="background: #0B192C; padding: 24px 30px; border-radius: 12px 12px 0 0; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800;">Master Mart Settings</h1>
                <p style="color: #94A3B8; margin: 6px 0 0; font-size: 14px;">Landing Page, Checkout, Shipping & Tracking Control</p>
            </div>
            <img src="<?php echo esc_url( MASTERMART_URI . '/assets/images/logo.jpg' ); ?>" alt="Logo" style="height: 48px; border-radius: 8px; background: #fff; padding: 4px;">
        </div>

        <div style="background: #ffffff; border: 1px solid #E2E8F0; border-top: none; border-radius: 0 0 12px 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'mastermart_settings_group' ); ?>

                <h3 style="font-size: 18px; border-bottom: 2px solid #FF6500; padding-bottom: 8px; margin-top: 0; color: #0B192C;">📞 Contact & Support Hotline</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="mastermart_hotline_phone">Hotline Phone Number</label></th>
                        <td>
                            <input type="text" id="mastermart_hotline_phone" name="mastermart_hotline_phone" value="<?php echo esc_attr( get_option( 'mastermart_hotline_phone', '01805060688' ) ); ?>" class="regular-text" placeholder="01805060688">
                            <p class="description">Displays in the header, callout banners, and mobile sticky bar.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mastermart_whatsapp_number">WhatsApp Chat Number</label></th>
                        <td>
                            <input type="text" id="mastermart_whatsapp_number" name="mastermart_whatsapp_number" value="<?php echo esc_attr( get_option( 'mastermart_whatsapp_number', '01805060688' ) ); ?>" class="regular-text" placeholder="01805060688">
                            <p class="description">Target phone number for the floating WhatsApp button.</p>
                        </td>
                    </tr>
                </table>

                <h3 style="font-size: 18px; border-bottom: 2px solid #FF6500; padding-bottom: 8px; margin-top: 30px; color: #0B192C;">🛒 Landing Page WooCommerce Product</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="mastermart_featured_product_id">Select WooCommerce Product</label></th>
                        <td>
                            <?php
                            $selected_prod = get_option( 'mastermart_featured_product_id', 0 );
                            if ( class_exists( 'WooCommerce' ) ) {
                                $products = get_posts( array(
                                    'post_type'      => 'product',
                                    'posts_per_page' => 100,
                                    'post_status'    => 'publish',
                                    'orderby'        => 'title',
                                    'order'          => 'ASC',
                                ) );
                                ?>
                                <select id="mastermart_featured_product_id" name="mastermart_featured_product_id" class="regular-text" style="max-width: 400px;">
                                    <option value="0"><?php esc_html_e( '— Auto Detect / Pedal Exercise Bike —', 'mastermart' ); ?></option>
                                    <?php foreach ( $products as $p ) : 
                                        $wc_p = wc_get_product( $p->ID );
                                        $p_price = $wc_p ? $wc_p->get_price() : '';
                                        ?>
                                        <option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( $selected_prod, $p->ID ); ?>>
                                            <?php echo esc_html( $p->post_title ); ?> (৳<?php echo esc_html( $p_price ); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Choose which WooCommerce product to sell directly on the landing page with 1-click COD checkout. Its price, image, and title will automatically bind to the landing page!</p>
                                <?php
                            } else {
                                echo '<p class="description" style="color:#e11d48;">WooCommerce is not active. The landing page is using fallback defaults.</p>';
                            }
                            ?>
                        </td>
                    </tr>
                </table>

                <h3 style="font-size: 18px; border-bottom: 2px solid #FF6500; padding-bottom: 8px; margin-top: 30px; color: #0B192C;">🚚 Delivery Charges & Pricing Fallbacks</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="mastermart_product_price">Fallback Offer Price (৳)</label></th>
                        <td>
                            <input type="number" id="mastermart_product_price" name="mastermart_product_price" value="<?php echo esc_attr( get_option( 'mastermart_product_price', 2000 ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mastermart_product_regular_price">Fallback Regular Price (৳)</label></th>
                        <td>
                            <input type="number" id="mastermart_product_regular_price" name="mastermart_product_regular_price" value="<?php echo esc_attr( get_option( 'mastermart_product_regular_price', 2500 ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mastermart_shipping_inside">Inside Dhaka Shipping (৳)</label></th>
                        <td>
                            <input type="number" id="mastermart_shipping_inside" name="mastermart_shipping_inside" value="<?php echo esc_attr( get_option( 'mastermart_shipping_inside', 80 ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mastermart_shipping_outside">Outside Dhaka / Nationwide (৳)</label></th>
                        <td>
                            <input type="number" id="mastermart_shipping_outside" name="mastermart_shipping_outside" value="<?php echo esc_attr( get_option( 'mastermart_shipping_outside', 150 ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>

                <h3 style="font-size: 18px; border-bottom: 2px solid #FF6500; padding-bottom: 8px; margin-top: 30px; color: #0B192C;">🎯 Pixel & Analytics Tracking</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="mastermart_facebook_pixel_id">Meta (Facebook) Pixel ID</label></th>
                        <td>
                            <input type="text" id="mastermart_facebook_pixel_id" name="mastermart_facebook_pixel_id" value="<?php echo esc_attr( get_option( 'mastermart_facebook_pixel_id', '' ) ); ?>" class="regular-text" placeholder="e.g. 123456789012345">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mastermart_gtm_id">Google Tag Manager (GTM) ID</label></th>
                        <td>
                            <input type="text" id="mastermart_gtm_id" name="mastermart_gtm_id" value="<?php echo esc_attr( get_option( 'mastermart_gtm_id', '' ) ); ?>" class="regular-text" placeholder="e.g. GTM-XXXXXXX">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mastermart_announcement_text">Urgency Bar Announcement</label></th>
                        <td>
                            <input type="text" id="mastermart_announcement_text" name="mastermart_announcement_text" value="<?php echo esc_attr( get_option( 'mastermart_announcement_text', '🔥 আজকের স্পেশাল ডিসকাউন্ট অফার! ক্যাশ অন ডেলিভারি সারা বাংলাদেশে' ) ); ?>" class="large-text">
                        </td>
                    </tr>
                </table>

                <h3 style="font-size: 18px; border-bottom: 2px solid #FF6500; padding-bottom: 8px; margin-top: 30px; color: #0B192C;">🛡️ থিম লাইসেন্স স্ট্যাটাস</h3>
                <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 10px; padding: 18px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div>
                        <strong style="color: #0B192C; font-size: 15px; display: block;">Master Mart Commercial License</strong>
                        <span style="color: #64748B; font-size: 13px;">
                            <?php if ( function_exists( 'mastermart_is_licensed' ) && mastermart_is_licensed() ) : ?>
                                <span style="color: #16a34a; font-weight: 700;">✓ সক্রিয় (ACTIVE) - লাইসেন্স কোড: 105694</span>
                            <?php else : ?>
                                <span style="color: #dc2626; font-weight: 700;">✕ নিষ্ক্রিয় (INACTIVE)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'themes.php?page=mastermart-license' ) ); ?>" class="button button-secondary" style="font-weight: 600;">
                        লাইসেন্স ম্যানেজ করুন →
                    </a>
                </div>

                <p class="submit" style="margin-top: 28px;">
                    <button type="submit" class="button button-primary" style="background: #FF6500; border-color: #E05A00; padding: 8px 24px; font-weight: 800; font-size: 15px;">Save Settings</button>
                </p>
            </form>
        </div>
    </div>
    <?php

}
