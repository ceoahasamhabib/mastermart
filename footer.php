<?php
/**
 * Master Mart Theme Footer
 *
 * @package MasterMart
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$hotline_phone   = mastermart_get_phone();
$whatsapp_number = mastermart_get_whatsapp();
$logo_url        = mastermart_get_logo_url();
$landing_prod    = function_exists( 'mastermart_get_landing_product' ) ? mastermart_get_landing_product() : array();
$product_price   = ! empty( $landing_prod['price'] ) ? (float) $landing_prod['price'] : (float) get_option( 'mastermart_product_price', 2000 );
$shipping_outside= (float) get_option( 'mastermart_shipping_outside', 150 );
$total_default   = $product_price + $shipping_outside;
?>

<!-- FOOTER -->
<footer class="mm-footer">
    <div class="mm-container">
        <img src="<?php echo esc_url( $logo_url ); ?>" alt="Master Mart" class="mm-footer-logo">
        <p class="mm-footer-text">
            <strong>Master Mart</strong> — Everything You Need, Every Day.<br>
            ১০০% অথেনটিক কোয়ালিটি প্রোডাক্ট ও সারা দেশে ক্যাশ অন ডেলিভারি সুবিধা।
        </p>
        <div class="mm-footer-copyright">
            © <?php echo esc_html( date( 'Y' ) ); ?> Master Mart. All Rights Reserved.
        </div>
    </div>
</footer>

<!-- FLOATING WHATSAPP BUTTON -->
<a href="https://wa.me/88<?php echo esc_attr( $whatsapp_number ); ?>?text=<?php echo rawurlencode( 'হ্যালো, আমি Master Mart থেকে Pedal Exercise Bike অর্ডার করতে চাই।' ); ?>" 
   class="mm-floating-wa" 
   target="_blank" 
   rel="noopener noreferrer" 
   aria-label="Chat on WhatsApp">
    <?php echo mastermart_svg( 'whatsapp', array( 'size' => 30 ) ); ?>
</a>

<!-- LIVE SOCIAL PROOF PURCHASE TOAST -->
<div class="mm-toast" id="mm-social-toast" role="status" aria-live="polite">
    <div class="mm-toast-icon">🛍️</div>
    <div class="mm-toast-body">
        <h5 class="mm-toast-title" id="mm-toast-user">মোঃ শরিফুল ইসলাম (মিরপুর, ঢাকা)</h5>
        <p class="mm-toast-desc" id="mm-toast-info">Pedal Exercise Bike অর্ডার করেছেন • এইমাত্র</p>
    </div>
</div>

<!-- MOBILE STICKY BOTTOM BAR -->
<div class="mm-mobile-bar">
    <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $hotline_phone ) ); ?>" class="mm-mob-call" aria-label="Call Helpline">
        <?php echo mastermart_svg( 'phone', array( 'size' => 22 ) ); ?>
    </a>
    <a href="#mmOrderSection" class="mm-mob-order">
        <span>অর্ডার করুন • <strong id="mm-mob-price">৳<?php echo esc_html( number_format( $total_default, 0 ) ); ?></strong></span>
    </a>
</div>

<?php wp_footer(); ?>
</body>
</html>
