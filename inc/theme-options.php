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
                            <input type="text" id="mastermart_hotline_phone" name="mastermart_hotline_phone" value="<?php echo esc_attr( get_option( 'mastermart_hotline_phone', '01824035313' ) ); ?>" class="regular-text" placeholder="01824035313">
                            <p class="description">Displays in the header, callout banners, and mobile sticky bar.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mastermart_whatsapp_number">WhatsApp Chat Number</label></th>
                        <td>
                            <input type="text" id="mastermart_whatsapp_number" name="mastermart_whatsapp_number" value="<?php echo esc_attr( get_option( 'mastermart_whatsapp_number', '01824035313' ) ); ?>" class="regular-text" placeholder="01824035313">
                            <p class="description">Target phone number for the floating WhatsApp button.</p>
                        </td>
                    </tr>
                </table>

                <h3 style="font-size: 18px; border-bottom: 2px solid #FF6500; padding-bottom: 8px; margin-top: 30px; color: #0B192C;">🚚 Delivery Charges & Pricing</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="mastermart_product_price">Offer Price (৳)</label></th>
                        <td>
                            <input type="number" id="mastermart_product_price" name="mastermart_product_price" value="<?php echo esc_attr( get_option( 'mastermart_product_price', 2000 ) ); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mastermart_product_regular_price">Regular Price (৳)</label></th>
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

                <p class="submit" style="margin-top: 24px;">
                    <button type="submit" class="button button-primary" style="background: #FF6500; border-color: #E05A00; padding: 6px 20px; font-weight: 700; font-size: 14px;">Save Settings</button>
                </p>
            </form>
        </div>
    </div>
    <?php
}
