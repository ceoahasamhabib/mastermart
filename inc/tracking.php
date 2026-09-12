<?php
/**
 * Master Mart Tracking & eCommerce DataLayer Engine
 *
 * Provides full Google Analytics 4 (GA4) / Google Tag Manager (GTM)
 * eCommerce DataLayer integration and Meta (Facebook) Pixel / CAPI events.
 *
 * Supported events:
 * - view_item / ViewContent (on product landing page)
 * - begin_checkout / InitiateCheckout (on interaction with COD form)
 * - purchase / Purchase (on thankyou invoice receipt page)
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Initialize DataLayer & Meta Pixel in <head>.
 */
function mastermart_head_tracking() {
    $pixel_id = get_option( 'mastermart_facebook_pixel_id', '' );
    $gtm_id   = get_option( 'mastermart_gtm_id', '' );
    $custom_h = get_option( 'mastermart_header_scripts', '' );
    ?>
    <!-- Master Mart Global eCommerce DataLayer Initialization -->
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    </script>
    <?php

    // Google Tag Manager
    if ( ! empty( $gtm_id ) ) :
        ?>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');</script>
        <!-- End Google Tag Manager -->
        <?php
    endif;

    // Meta Pixel
    if ( ! empty( $pixel_id ) ) :
        ?>
        <!-- Meta Pixel Code -->
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo esc_js( $pixel_id ); ?>');
        fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=<?php echo esc_attr( $pixel_id ); ?>&ev=PageView&noscript=1"
        /></noscript>
        <!-- End Meta Pixel Code -->
        <?php
    endif;

    // Custom Header Scripts
    if ( ! empty( $custom_h ) ) {
        echo $custom_h; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
add_action( 'wp_head', 'mastermart_head_tracking', 5 );

/**
 * 2. Output GTM noscript in body open.
 */
function mastermart_body_open_tracking() {
    $gtm_id = get_option( 'mastermart_gtm_id', '' );
    if ( ! empty( $gtm_id ) ) :
        ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $gtm_id ); ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <?php
    endif;
}
add_action( 'wp_body_open', 'mastermart_body_open_tracking', 5 );

/**
 * 3. eCommerce DataLayer: view_item event on Product / Landing Page.
 */
function mastermart_ecommerce_view_item_datalayer() {
    if ( is_front_page() || is_page_template( 'templates/template-landing-page.php' ) || is_product() ) {
        $price   = (float) get_option( 'mastermart_product_price', 2000 );
        $prod_id = function_exists( 'mastermart_get_default_product_id' ) ? mastermart_get_default_product_id() : 'pedal-cycle-lcd';
        ?>
        <!-- GA4 eCommerce view_item & Meta Pixel ViewContent -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: 'view_item',
                ecommerce: {
                    currency: 'BDT',
                    value: <?php echo esc_js( $price ); ?>,
                    items: [{
                        item_id: '<?php echo esc_js( $prod_id ); ?>',
                        item_name: 'Pedal Exercise Bike with LCD Display',
                        price: <?php echo esc_js( $price ); ?>,
                        item_brand: 'Master Mart',
                        item_category: 'Fitness Equipment',
                        quantity: 1
                    }]
                }
            });

            if (typeof fbq === 'function') {
                fbq('track', 'ViewContent', {
                    content_name: 'Pedal Exercise Bike with LCD Display',
                    content_category: 'Fitness Equipment',
                    content_ids: ['<?php echo esc_js( $prod_id ); ?>'],
                    content_type: 'product',
                    value: <?php echo esc_js( $price ); ?>,
                    currency: 'BDT'
                });
            }
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'mastermart_ecommerce_view_item_datalayer', 10 );

/**
 * 4. Custom Footer Scripts.
 */
function mastermart_footer_tracking() {
    $custom_f = get_option( 'mastermart_footer_scripts', '' );
    if ( ! empty( $custom_f ) ) {
        echo $custom_f; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
add_action( 'wp_footer', 'mastermart_footer_tracking', 99 );
