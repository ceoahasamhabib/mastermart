<?php
/**
 * Master Mart - Elementor Integration & Custom Widgets Engine
 *
 * Allows users to edit the landing page seamlessly with Elementor.
 * Registers custom widget categories, Elementor widgets, and shortcodes.
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Register Elementor Widget Category
 */
function mastermart_add_elementor_widget_categories( $elements_manager ) {
    $elements_manager->add_category(
        'mastermart-elements',
        array(
            'title' => esc_html__( '🛒 Master Mart Elements', 'mastermart' ),
            'icon'  => 'fa fa-shopping-bag',
        )
    );
}
add_action( 'elementor/elements/categories_registered', 'mastermart_add_elementor_widget_categories' );

/**
 * 2. Register Theme Location support for Elementor Pro Theme Builder
 */
function mastermart_register_elementor_locations( $elementor_theme_manager ) {
    $elementor_theme_manager->register_location( 'header' );
    $elementor_theme_manager->register_location( 'footer' );
    $elementor_theme_manager->register_location( 'single' );
}
add_action( 'elementor/theme/register_locations', 'mastermart_register_elementor_locations' );

/**
 * 3. Register Custom Elementor Widgets
 */
function mastermart_register_elementor_widgets( $widgets_manager ) {
    if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
        return;
    }

    // Include and register custom widgets
    require_once MASTERMART_DIR . '/inc/elementor-widgets/widget-checkout.php';
    require_once MASTERMART_DIR . '/inc/elementor-widgets/widget-hero.php';
    require_once MASTERMART_DIR . '/inc/elementor-widgets/widget-features.php';
    require_once MASTERMART_DIR . '/inc/elementor-widgets/widget-faq.php';

    $widgets_manager->register( new \MasterMart_Elementor_Checkout_Widget() );
    $widgets_manager->register( new \MasterMart_Elementor_Hero_Widget() );
    $widgets_manager->register( new \MasterMart_Elementor_Features_Widget() );
    $widgets_manager->register( new \MasterMart_Elementor_FAQ_Widget() );
}
add_action( 'elementor/widgets/register', 'mastermart_register_elementor_widgets' );

/**
 * 4. Helper Function: Check if current page is built with or editing in Elementor
 */
function mastermart_is_elementor_page() {
    if ( ! class_exists( '\Elementor\Plugin' ) ) {
        return false;
    }

    $post_id = get_the_ID();
    if ( ! $post_id ) {
        return false;
    }

    if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
        return true;
    }

    return \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor();
}

/**
 * 5. Shortcodes for Drag-and-Drop and universal builder compatibility
 */

// Shortcode: [mastermart_checkout]
add_shortcode( 'mastermart_checkout', 'mastermart_shortcode_checkout' );
function mastermart_shortcode_checkout( $atts ) {
    $atts = shortcode_atts( array(
        'title'    => 'ক্যাশ অন ডেলিভারি অর্ডার ফরম',
        'subtitle' => 'ডেলিভারির ঠিকানার ঘরগুলো পূরণ করুন এবং “অর্ডার কনফার্ম করুন” বাটনে ক্লিক করুন',
    ), $atts, 'mastermart_checkout' );

    $product_price    = (float) get_option( 'mastermart_product_price', 2000 );
    $shipping_inside  = (float) get_option( 'mastermart_shipping_inside', 80 );
    $shipping_outside = (float) get_option( 'mastermart_shipping_outside', 150 );
    $default_total    = $product_price + $shipping_outside;
    $default_prod_id  = get_option( 'mastermart_primary_product_id', 0 );
    $img_dir          = MASTERMART_URI . '/assets/images';

    ob_start();
    ?>
    <div class="mm-checkout-section mm-elementor-checkout-wrap" id="mmOrderSection">
        <div class="mm-container" style="max-width: 1100px; margin: 0 auto;">
            <div class="mm-checkout-card">
                <div class="mm-checkout-header">
                    <h2><?php echo esc_html( $atts['title'] ); ?></h2>
                    <p><?php echo esc_html( $atts['subtitle'] ); ?></p>
                </div>

                <form id="mm-checkout-form" name="checkout" class="checkout woocommerce-checkout" method="post" action="<?php echo esc_url( function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '#' ); ?>" novalidate>
                    <input type="hidden" id="mm-product-id" name="product_id" value="<?php echo esc_attr( $default_prod_id ); ?>">

                    <div class="mm-form-grid">
                        <!-- Left Column: Customer Information with Standard WooCommerce DOM Classes -->
                        <div class="mm-form-left woocommerce-billing-fields">
                            <h3 class="mm-form-heading">
                                <span class="num">১</span>
                                <span>আপনার ডেলিভারি তথ্য দিন</span>
                            </h3>

                            <div class="woocommerce-billing-fields__field-wrapper">
                                <!-- Customer Full Name -->
                                <p class="form-row form-row-wide mm-form-group validate-required" id="billing_first_name_field">
                                    <label for="billing_first_name">আপনার সম্পূর্ণ নাম <abbr class="required" title="required">*</abbr></label>
                                    <span class="woocommerce-input-wrapper">
                                        <input type="text" class="input-text" id="billing_first_name" name="billing_first_name" placeholder="যেমন: মোঃ কামরুল ইসলাম" required autocomplete="name">
                                    </span>
                                </p>

                                <!-- Customer Mobile Number -->
                                <p class="form-row form-row-wide mm-form-group validate-required validate-phone" id="billing_phone_field">
                                    <label for="billing_phone">১১ ডিজিটের মোবাইল নাম্বার <abbr class="required" title="required">*</abbr></label>
                                    <span class="woocommerce-input-wrapper">
                                        <input type="tel" class="input-text" id="billing_phone" name="billing_phone" placeholder="01XXXXXXXXX" required autocomplete="tel" oninput="mastermartValidatePhone(this)">
                                    </span>
                                    <span class="mm-val-msg" id="mm-phone-msg"></span>
                                </p>

                                <!-- Delivery Zone Selection -->
                                <div class="form-row form-row-wide mm-form-group" id="delivery_zone_field">
                                    <label>ডেলিভারি এরিয়া নির্বাচন করুন <abbr class="required" title="required">*</abbr></label>
                                    <div class="mm-zone-selector">
                                        <label class="mm-zone-option">
                                            <input type="radio" name="delivery_zone" value="inside" id="mm-zone-in">
                                            <div class="mm-zone-label">
                                                <span class="mm-zone-name">ঢাকার ভেতরে</span>
                                                <span class="mm-zone-cost">৳<?php echo esc_html( $shipping_inside ); ?></span>
                                            </div>
                                        </label>
                                        <label class="mm-zone-option active">
                                            <input type="radio" name="delivery_zone" value="outside" id="mm-zone-out" checked>
                                            <div class="mm-zone-label">
                                                <span class="mm-zone-name">ঢাকার বাইরে / সারা দেশ</span>
                                                <span class="mm-zone-cost">৳<?php echo esc_html( $shipping_outside ); ?></span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Full Delivery Address -->
                                <p class="form-row form-row-wide mm-form-group address-field validate-required" id="billing_address_1_field">
                                    <label for="billing_address_1">সম্পূর্ণ ঠিকানা (জেলা, থানা ও এলাকা/রোড নং) <abbr class="required" title="required">*</abbr></label>
                                    
                                    <!-- District Quick Select Chips -->
                                    <span class="mm-chips-wrap">
                                        <span class="mm-chips-label">কুইক সিলেক্ট:</span>
                                        <span class="mm-chip" onclick="mastermartQuickDistrict('ঢাকা')">ঢাকা</span>
                                        <span class="mm-chip" onclick="mastermartQuickDistrict('চট্টগ্রাম')">চট্টগ্রাম</span>
                                        <span class="mm-chip" onclick="mastermartQuickDistrict('সিলেট')">সিলেট</span>
                                        <span class="mm-chip" onclick="mastermartQuickDistrict('রাজশাহী')">রাজশাহী</span>
                                        <span class="mm-chip" onclick="mastermartQuickDistrict('খুলনা')">খুলনা</span>
                                        <span class="mm-chip" onclick="mastermartQuickDistrict('গাজীপুর')">গাজীপুর</span>
                                        <span class="mm-chip" onclick="mastermartQuickDistrict('নারায়ণগঞ্জ')">নারায়ণগঞ্জ</span>
                                        <span class="mm-chip" onclick="mastermartQuickDistrict('কুমিল্লা')">কুমিল্লা</span>
                                    </span>

                                    <span class="woocommerce-input-wrapper">
                                        <textarea class="input-text" id="billing_address_1" name="billing_address_1" rows="3" placeholder="আপনার জেলা, থানা ও গ্রাম/এলাকার নাম বিস্তারিত লিখুন" required autocomplete="street-address"></textarea>
                                    </span>
                                </p>

                                <!-- Special Instructions -->
                                <p class="form-row form-row-wide mm-form-group" id="order_comments_field">
                                    <label for="order_comments">অতিরিক্ত নির্দেশনা (ঐচ্ছিক)</label>
                                    <span class="woocommerce-input-wrapper">
                                        <input type="text" class="input-text" id="order_comments" name="order_comments" placeholder="যেমন: ডেলিভারির পূর্বে কল দিবেন">
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Right Column: Order Review / Summary Table -->
                        <div class="mm-form-right">
                            <div id="order_review" class="woocommerce-checkout-review-order mm-summary-box">
                                <h3>অর্ডার সামারি</h3>

                                <div class="mm-summary-product">
                                    <img src="<?php echo esc_url( $img_dir . '/hero-pedal-bike.webp' ); ?>" alt="Pedal Bike">
                                    <div class="mm-summary-product-info">
                                        <h4>Pedal Exercise Bike with LCD Display</h4>
                                        <span class="mm-summary-product-price">৳<?php echo esc_html( number_format( $product_price, 0 ) ); ?> × ১</span>
                                    </div>
                                </div>

                                <table class="shop_table woocommerce-checkout-review-order-table" style="width:100%; margin-bottom: 16px; border-collapse: collapse;">
                                    <tbody>
                                        <tr class="cart-subtotal mm-sum-row">
                                            <th style="text-align:left; font-weight: normal;">প্রোডাক্ট মূল্য</th>
                                            <td style="text-align:right;"><strong>৳<?php echo esc_html( number_format( $product_price, 0 ) ); ?></strong></td>
                                        </tr>
                                        <tr class="woocommerce-shipping-totals shipping mm-sum-row">
                                            <th style="text-align:left; font-weight: normal;">ডেলিভারি চার্জ</th>
                                            <td style="text-align:right;"><strong id="mm-line-shipping">৳<?php echo esc_html( $shipping_outside ); ?></strong></td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="order-total mm-sum-row total">
                                            <th style="text-align:left;">সর্বমোট বিল</th>
                                            <td style="text-align:right;"><span class="total-amount" id="mm-line-total">৳<?php echo esc_html( number_format( $default_total, 0 ) ); ?></span></td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <div id="payment" class="woocommerce-checkout-payment">
                                    <button type="submit" class="button alt wp-element-button mm-submit-btn" name="woocommerce_checkout_place_order" id="mm-place-order-btn">
                                        <?php echo mastermart_svg( 'check', array( 'size' => 20 ) ); ?>
                                        <span>👉 অর্ডার কনফার্ম করুন (ক্যাশ অন ডেলিভারি)</span>
                                    </button>
                                </div>

                                <p class="mm-security-note">
                                    <?php echo mastermart_svg( 'shield', array( 'size' => 14 ) ); ?>
                                    <span>পণ্য হাতে পেয়ে চেক করে সম্পূর্ণ ক্যাশ অন ডেলিভারিতে টাকা পরিশোধ করুন।</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Shortcode: [mastermart_hero]
add_shortcode( 'mastermart_hero', 'mastermart_shortcode_hero' );
function mastermart_shortcode_hero() {
    $product_price = (float) get_option( 'mastermart_product_price', 2000 );
    $regular_price = (float) get_option( 'mastermart_product_regular_price', 2500 );
    $savings       = $regular_price - $product_price;
    $hotline       = mastermart_get_phone();
    $img_dir       = MASTERMART_URI . '/assets/images';

    ob_start();
    ?>
    <section class="mm-hero">
        <div class="mm-container">
            <div class="mm-hero-grid">
                <div class="mm-hero-content">
                    <div class="mm-badge">
                        <span class="mm-badge-dot"></span>
                        <span>১০০% অরিজিনাল কোয়ালিটি নিশ্চিত</span>
                    </div>

                    <h1 class="mm-hero-title">
                        ঘরে বসেই সহজে এক্সারসাইজ করুন <span class="highlight">Pedal Exercise Bike</span> দিয়ে
                    </h1>

                    <p class="mm-hero-subtitle">
                        হাত ও পায়ের পেশী মজবুত করতে, রক্ত সঞ্চালন বৃদ্ধি করতে এবং জয়েন্টের ব্যথা দূর করতে ডিজিটাল LCD মনিটরযুক্ত সেরা মিনি পেডেল সাইকেল।
                    </p>

                    <div class="mm-hero-pills">
                        <span class="mm-pill"><?php echo mastermart_svg( 'check', array( 'size' => 15 ) ); ?> ডিজিটাল LCD মনিটর</span>
                        <span class="mm-pill"><?php echo mastermart_svg( 'check', array( 'size' => 15 ) ); ?> অ্যাডজাস্টেবল রেজিস্ট্যান্স</span>
                        <span class="mm-pill"><?php echo mastermart_svg( 'check', array( 'size' => 15 ) ); ?> হালকা ও পোর্টেবল</span>
                    </div>

                    <div class="mm-hero-price-wrap">
                        <div class="mm-price-box">
                            <div class="mm-price-label">অফার প্রাইস</div>
                            <div class="mm-price-val">৳<?php echo esc_html( number_format( $product_price, 0 ) ); ?></div>
                        </div>
                        <div class="mm-price-old">
                            <span class="del">৳<?php echo esc_html( number_format( $regular_price, 0 ) ); ?></span>
                            <span class="discount-badge">৳<?php echo esc_html( number_format( $savings, 0 ) ); ?> ছাড়!</span>
                        </div>
                    </div>

                    <div class="mm-hero-actions">
                        <a href="#mmOrderSection" class="mm-btn mm-btn-cta">
                            <span>অর্ডার করতে এখানে ক্লিক করুন</span>
                            <?php echo mastermart_svg( 'arrow-right', array( 'size' => 20 ) ); ?>
                        </a>
                        <a href="tel:<?php echo esc_attr( $hotline ); ?>" class="mm-btn mm-btn-phone">
                            <?php echo mastermart_svg( 'phone', array( 'size' => 18 ) ); ?>
                            <span>সরাসরি কল: <?php echo esc_html( $hotline ); ?></span>
                        </a>
                    </div>
                </div>

                <div class="mm-hero-media">
                    <div class="mm-hero-img-wrap">
                        <img src="<?php echo esc_url( $img_dir . '/hero-pedal-bike.webp' ); ?>" alt="Pedal Bike" class="mm-hero-img">
                        <div class="mm-float-badge mm-float-badge-1">
                            <span class="icon">🚴</span>
                            <span class="txt">হাত ও পা<br>উভয়ের জন্য</span>
                        </div>
                        <div class="mm-float-badge mm-float-badge-2">
                            <span class="icon">🔋</span>
                            <span class="txt">LCD ডিসপ্লে<br>সহজ মনিটরিং</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

// Shortcode: [mastermart_features]
add_shortcode( 'mastermart_features', 'mastermart_shortcode_features' );
function mastermart_shortcode_features() {
    $img_dir = MASTERMART_URI . '/assets/images';
    ob_start();
    ?>
    <section class="mm-features-section" id="features">
        <div class="mm-container">
            <div class="mm-section-header">
                <span class="mm-subtitle">কেন এই পেডেল সাইকেলটি আপনার জন্য সেরা?</span>
                <h2 class="mm-title">মিনি পেডেল সাইকেলের বিশেষ বৈশিষ্ট্যসমূহ</h2>
            </div>
            <div class="mm-features-grid">
                <div class="mm-feature-card">
                    <div class="mm-feature-img-wrap"><img src="<?php echo esc_url( $img_dir . '/feature-compact.jpg' ); ?>" alt="Compact Design"></div>
                    <div class="mm-feature-body">
                        <h3>কমপ্যাক্ট ও স্পেস-সেভিং ডিজাইন</h3>
                        <p>বাসার যেকোনো স্থানে সোফায় বা চেয়ারে বসে নিশ্চিন্তে এক্সারসাইজ করতে পারবেন। কোনো আলাদা জায়গার প্রয়োজন নেই।</p>
                    </div>
                </div>
                <div class="mm-feature-card">
                    <div class="mm-feature-img-wrap"><img src="<?php echo esc_url( $img_dir . '/feature-lcd.jpg' ); ?>" alt="Digital LCD Monitor"></div>
                    <div class="mm-feature-body">
                        <h3>মাল্টি-ফাংশন ডিজিটাল LCD ডিসপ্লে</h3>
                        <p>কত সময় ধরে এক্সারসাইজ করছেন, কতবার পেডেল ঘুরিয়েছেন এবং কত ক্যালরি বার্ন হয়েছে তা সরাসরি স্ক্রিনে দেখতে পাবেন।</p>
                    </div>
                </div>
                <div class="mm-feature-card">
                    <div class="mm-feature-img-wrap"><img src="<?php echo esc_url( $img_dir . '/feature-foldable.jpg' ); ?>" alt="Foldable & Portable"></div>
                    <div class="mm-feature-body">
                        <h3>সহজে বহনযোগ্য ও পোর্টেবল</h3>
                        <p>ওজন হালকা হওয়ায় সহজেই এক ঘর থেকে অন্য ঘরে বা অফিসে নিয়ে যেতে পারবেন। প্রবীণদের ব্যবহারের জন্য অত্যন্ত চমৎকার।</p>
                    </div>
                </div>
                <div class="mm-feature-card">
                    <div class="mm-feature-img-wrap"><img src="<?php echo esc_url( $img_dir . '/feature-tension.jpg' ); ?>" alt="Adjustable Resistance"></div>
                    <div class="mm-feature-body">
                        <h3>অ্যাডজাস্টেবল টেনশন ও রেজিস্ট্যান্স</h3>
                        <p>রেজিস্ট্যান্স নব ঘুরিয়ে আপনার প্রয়োজন অনুযায়ী পেডেলের গতি ও ভার হালকা থেকে ভারী সেট করতে পারবেন।</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}

// Shortcode: [mastermart_faq]
add_shortcode( 'mastermart_faq', 'mastermart_shortcode_faq' );
function mastermart_shortcode_faq() {
    ob_start();
    ?>
    <section class="mm-faq-section" id="faq">
        <div class="mm-container" style="max-width: 800px; margin: 0 auto;">
            <div class="mm-section-header">
                <span class="mm-subtitle">সচরাচর জিজ্ঞাসিত প্রশ্নাবলী</span>
                <h2 class="mm-title">আপনার মনে থাকা প্রশ্নের উত্তর</h2>
            </div>
            <div class="mm-faq-list">
                <div class="mm-faq-item active">
                    <div class="mm-faq-question">
                        <span>কীভাবে এই প্রোডাক্টটি অর্ডার করব?</span>
                        <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                    </div>
                    <div class="mm-faq-answer">
                        <p>উপরে দেওয়া অর্ডার ফরমে আপনার নাম, মোবাইল নাম্বার এবং সম্পূর্ণ ঠিকানা লিখে "অর্ডার কনফার্ম করুন" বাটনে ক্লিক করুন।</p>
                    </div>
                </div>
                <div class="mm-faq-item">
                    <div class="mm-faq-question">
                        <span>ডেলিভারি পেতে কতদিন সময় লাগবে?</span>
                        <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                    </div>
                    <div class="mm-faq-answer">
                        <p>ঢাকার মধ্যে ২৪ থেকে ৪৮ ঘণ্টার মধ্যে এবং ঢাকার বাইরে ২ থেকে ৩ কার্যদিবসের মধ্যে হোম ডেলিভারি পেয়ে যাবেন।</p>
                    </div>
                </div>
                <div class="mm-faq-item">
                    <div class="mm-faq-question">
                        <span>পণ্য হাতে পেয়ে টাকা দেওয়ার সুবিধা আছে কি?</span>
                        <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                    </div>
                    <div class="mm-faq-answer">
                        <p>হ্যাঁ, সম্পূর্ণ ক্যাশ অন ডেলিভারি সুবিধা রয়েছে। প্রোডাক্ট রিসিভ করে চেক করে ডেলিভারিম্যানকে টাকা পরিশোধ করবেন।</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
}
