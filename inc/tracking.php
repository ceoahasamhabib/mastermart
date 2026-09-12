<?php
/**
 * Master Mart Tracking & Analytics Engine
 *
 * Supports Meta Pixel, Conversion API helpers, Google Tag Manager,
 * and custom tracking scripts.
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Output Meta Pixel & GTM in <head>.
 */
function mastermart_head_tracking() {
    $pixel_id = get_option( 'mastermart_facebook_pixel_id', '' );
    $gtm_id   = get_option( 'mastermart_gtm_id', '' );
    $custom_h = get_option( 'mastermart_header_scripts', '' );

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

    if ( ! empty( $custom_h ) ) {
        echo $custom_h; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
add_action( 'wp_head', 'mastermart_head_tracking', 20 );

/**
 * Output Body/Footer scripts.
 */
function mastermart_footer_tracking() {
    $custom_f = get_option( 'mastermart_footer_scripts', '' );
    if ( ! empty( $custom_f ) ) {
        echo $custom_f; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
add_action( 'wp_footer', 'mastermart_footer_tracking', 99 );
