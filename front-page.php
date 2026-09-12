<?php
/**
 * Front Page Template - Master Mart Pedal Exercise Bike Landing Page
 *
 * @package MasterMart
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

$img_dir         = MASTERMART_URI . '/assets/images';
$hotline_phone   = mastermart_get_phone();
$product_price   = (float) get_option( 'mastermart_product_price', 2000 );
$regular_price   = (float) get_option( 'mastermart_product_regular_price', 2500 );
$shipping_inside = (float) get_option( 'mastermart_shipping_inside', 80 );
$shipping_outside= (float) get_option( 'mastermart_shipping_outside', 150 );
$default_total   = $product_price + $shipping_outside;
$default_prod_id = function_exists( 'mastermart_get_default_product_id' ) ? mastermart_get_default_product_id() : 0;
?>

<!-- 1. HERO SECTION -->
<section class="mm-hero">
    <div class="mm-container">
        <div class="mm-hero-grid">
            <!-- Left: Hero Sales Pitch -->
            <div class="mm-hero-content">
                <div class="mm-badge">
                    <?php echo mastermart_svg( 'zap', array( 'size' => 16 ) ); ?>
                    <span>সুস্থ জীবন ও সক্রিয় শরীরের বিশ্বস্ত সঙ্গী</span>
                </div>

                <h1 class="mm-hero-title">
                    ঘরে বসেই প্রতিদিন ১৫ মিনিটে <span class="highlight">সুস্থ থাকুন</span>
                </h1>

                <p class="mm-hero-lead">
                    বয়স্ক, ডায়াবেটিস রোগী, অফিস কর্মী ও ফিজিওথেরাপি ব্যবহারকারীদের জন্য আদর্শ মিনি প্যাডেল এক্সারসাইজ বাইক।
                </p>

                <div class="mm-hero-pills">
                    <span class="mm-hero-pill">🚴‍♀️ হাত ও পায়ের ব্যায়াম</span>
                    <span class="mm-hero-pill">📊 ডিজিটাল LCD ডিসপ্লে</span>
                    <span class="mm-hero-pill">🛡️ ১০০% প্রিমিয়াম মেটাল বডি</span>
                    <span class="mm-hero-pill">📦 সারা দেশে ক্যাশ অন ডেলিভারি</span>
                </div>

                <div class="mm-hero-price-wrap">
                    <span class="mm-price-label">স্পেশাল অফার মূল্য:</span>
                    <span class="mm-hero-price-current">৳<?php echo esc_html( number_format( $product_price, 0 ) ); ?></span>
                    <span class="mm-hero-price-old">৳<?php echo esc_html( number_format( $regular_price, 0 ) ); ?></span>
                    <span class="mm-hero-price-save">৳<?php echo esc_html( number_format( $regular_price - $product_price, 0 ) ); ?> সাশ্রয়!</span>
                </div>

                <a href="#mmOrderSection" class="mm-btn-cta">
                    <?php echo mastermart_svg( 'cart', array( 'size' => 22 ) ); ?>
                    <span>অর্ডার করতে এখানে ক্লিক করুন</span>
                </a>
            </div>

            <!-- Right: Product Visual Card -->
            <div class="mm-hero-visual">
                <div class="mm-hero-img-card">
                    <img src="<?php echo esc_url( $img_dir . '/hero-pedal-bike.webp' ); ?>" alt="Pedal Exercise Bike with LCD Display" fetchpriority="high">
                    <div class="mm-hero-floating-badge">
                        <?php echo mastermart_svg( 'star', array( 'size' => 16 ) ); ?>
                        <span>প্রিমিয়াম কোয়ালিটি</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. PRODUCT FEATURES SECTION -->
<section class="mm-section mm-section-alt" id="features">
    <div class="mm-container">
        <div class="mm-section-header">
            <h2 class="mm-section-title">প্রোডাক্ট বিস্তারিত ও বিশেষ সুবিধাসমূহ</h2>
            <p class="mm-section-subtitle">
                আপনার পরিবারের প্রবীণ ও ফিটনেস সচেতন সবার জন্য পারফেক্ট কমপ্যাক্ট এক্সারসাইজ সাইকেল
            </p>
        </div>

        <div class="mm-features-grid">
            <!-- Feature 1: Compact & Strong -->
            <div class="mm-feature-card">
                <div class="mm-feature-thumb">
                    <img src="<?php echo esc_url( $img_dir . '/feature-compact.jpg' ); ?>" alt="কমপ্যাক্ট ও শক্তিশালী গঠন" loading="lazy">
                </div>
                <div class="mm-feature-content">
                    <h3 class="mm-feature-title">কমপ্যাক্ট ও শক্তিশালী গঠন</h3>
                    <p class="mm-feature-desc">বাসার যেকোনো কোণে সহজে রাখা যায় এমন হালকা অথচ মজবুত বডি।</p>
                </div>
            </div>

            <!-- Feature 2: LCD Display -->
            <div class="mm-feature-card">
                <div class="mm-feature-thumb">
                    <img src="<?php echo esc_url( $img_dir . '/feature-lcd.jpg' ); ?>" alt="এলসিডি ডিসপ্লে মনিটর" loading="lazy">
                </div>
                <div class="mm-feature-content">
                    <h3 class="mm-feature-title">এলসিডি ডিসপ্লে মনিটর</h3>
                    <p class="mm-feature-desc">সময়, ক্যালরি ও গতি সহজে দেখার জন্য স্পষ্ট ডিজিটাল ডিসপ্লে।</p>
                </div>
            </div>

            <!-- Feature 3: Foldable Design -->
            <div class="mm-feature-card">
                <div class="mm-feature-thumb">
                    <img src="<?php echo esc_url( $img_dir . '/feature-foldable.jpg' ); ?>" alt="সহজ ভাঁজযোগ্য ডিজাইন" loading="lazy">
                </div>
                <div class="mm-feature-content">
                    <h3 class="mm-feature-title">সহজ ভাঁজযোগ্য ডিজাইন</h3>
                    <p class="mm-feature-desc">ব্যবহারের পর সহজেই ভাঁজ করে রাখা যায়, জায়গা কম লাগে।</p>
                </div>
            </div>

            <!-- Feature 4: Adjustable Resistance -->
            <div class="mm-feature-card">
                <div class="mm-feature-thumb">
                    <img src="<?php echo esc_url( $img_dir . '/feature-tension.jpg' ); ?>" alt="সামঞ্জস্যযোগ্য রেজিস্ট্যান্স" loading="lazy">
                </div>
                <div class="mm-feature-content">
                    <h3 class="mm-feature-title">সামঞ্জস্যযোগ্য রেজিস্ট্যান্স</h3>
                    <p class="mm-feature-desc">প্রয়োজন অনুযায়ী প্যাডেলের প্রতিরোধ ক্ষমতা কমানো-বাড়ানো যায়।</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. MID-PAGE CALLOUT BANNER -->
<section class="mm-callout-banner">
    <div class="mm-container">
        <div class="mm-callout-inner">
            <h3 class="mm-callout-text">
                📞 সরাসরি ফোনে অর্ডার করতে কল করুন: 
                <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $hotline_phone ) ); ?>" class="mm-callout-phone">
                    <?php echo esc_html( $hotline_phone ); ?>
                </a>
            </h3>
            <a href="#mmOrderSection" class="mm-btn-cta">
                <?php echo mastermart_svg( 'cart', array( 'size' => 20 ) ); ?>
                <span>এখনই অর্ডার করুন</span>
            </a>
        </div>
    </div>
</section>

<!-- 4. BENEFITS SECTION -->
<section class="mm-section">
    <div class="mm-container">
        <div class="mm-section-header">
            <h2 class="mm-section-title">কাদের জন্য এই প্যাডেল সাইকেল অপরিহার্য?</h2>
            <p class="mm-section-subtitle">
                শরীর সচল রাখা ও সুস্থ থাকার সবচেয়ে নিরাপদ ও আরামদায়ক ঘরোয়া সমাধান
            </p>
        </div>

        <div class="mm-benefits-grid">
            <div class="mm-benefit-box">
                <div class="mm-benefit-icon">🩺</div>
                <h4>ফিজিওথেরাপি ও জয়েন্ট সচলতা</h4>
                <p>হাঁটু ও গোড়ালির রক্ত সঞ্চালন স্বাভাবিক রাখে এবং জয়েন্টের দীর্ঘমেয়াদী জড়তা ও ব্যথা দূর করতে সাহায্য করে।</p>
            </div>

            <div class="mm-benefit-box">
                <div class="mm-benefit-icon">🩸</div>
                <h4>ডায়াবেটিস ও রক্তচাপ নিয়ন্ত্রণ</h4>
                <p>প্রতিদিন মাত্র ১৫-২০ মিনিট প্যাডেলিং শরীরে গ্লুকোজ লেভেল নিয়ন্ত্রণে রাখে এবং কার্ডিওভাসকুলার স্বাস্থ্যের উন্নতি করে।</p>
            </div>

            <div class="mm-benefit-box">
                <div class="mm-benefit-icon">👵</div>
                <h4>প্রবীণদের ঘরোয়া ফিটনেস</h4>
                <p>বাইরের যানজট বা রোদে না গিয়েও ঘরে বসে সোফায় বা চেয়ারে বসে নিরাপদে বসে হাত-পায়ে হালকা ব্যায়াম করার সেরা উপায়।</p>
            </div>
        </div>
    </div>
</section>

<!-- 5. INTERACTIVE FAQ ACCORDION -->
<section class="mm-faq-section" id="faq">
    <div class="mm-container">
        <div class="mm-section-header">
            <h2 class="mm-section-title">সচরাচর জিজ্ঞাসা (FAQ)</h2>
            <p class="mm-section-subtitle">প্যাডেল সাইকেল ও ডেলিভারি সংক্রান্ত প্রয়োজনীয় সাধারণ প্রশ্নের উত্তর</p>
        </div>

        <div class="mm-faq-wrap">
            <div class="mm-faq-item active">
                <div class="mm-faq-question">
                    <span>এই সাইকেল ব্যবহার করা কি নিরাপদ?</span>
                    <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                </div>
                <div class="mm-faq-answer">
                    <p>হ্যাঁ, এটি বয়স্ক ব্যক্তি ও ফিজিওথেরাপি রোগীদের জন্য নিরাপদভাবে ডিজাইন করা হয়েছে। মজবুত মেটাল বডি ব্যবহারের সময় স্থিতিশীলতা নিশ্চিত করে।</p>
                </div>
            </div>

            <div class="mm-faq-item">
                <div class="mm-faq-question">
                    <span>ডেলিভারি পেতে কত সময় লাগবে?</span>
                    <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                </div>
                <div class="mm-faq-answer">
                    <p>ঢাকার ভেতরে সাধারণত ১-২ কার্যদিবস এবং ঢাকার বাইরে ৩-৫ কার্যদিবস সময় লাগে।</p>
                </div>
            </div>

            <div class="mm-faq-item">
                <div class="mm-faq-question">
                    <span>পেমেন্ট পদ্ধতি কী?</span>
                    <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                </div>
                <div class="mm-faq-answer">
                    <p>আমরা ক্যাশ অন ডেলিভারি সুবিধা দিচ্ছি — পণ্য হাতে পেয়ে চেক করে টাকা পরিশোধ করতে পারবেন।</p>
                </div>
            </div>

            <div class="mm-faq-item">
                <div class="mm-faq-question">
                    <span>প্রোডাক্টের ওজন সীমা কত?</span>
                    <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                </div>
                <div class="mm-faq-answer">
                    <p>এটি সাধারণত ১০০ কেজি পর্যন্ত ওজন সহনশীল, তবে নির্দিষ্ট তথ্যের জন্য কাস্টমার সাপোর্টের সাথে যোগাযোগ করুন।</p>
                </div>
            </div>

            <div class="mm-faq-item">
                <div class="mm-faq-question">
                    <span>রিটার্ন বা এক্সচেঞ্জ পলিসি আছে কি?</span>
                    <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                </div>
                <div class="mm-faq-answer">
                    <p>পণ্যে কোনো ত্রুটি থাকলে হাতে পাওয়ার ২৪ ঘণ্টার মধ্যে জানালে আমরা দ্রুত রিটার্ন/এক্সচেঞ্জ ব্যবস্থা করি।</p>
                </div>
            </div>

            <div class="mm-faq-item">
                <div class="mm-faq-question">
                    <span>এটি ভাঁজ করে রাখা যায় কি?</span>
                    <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                </div>
                <div class="mm-faq-answer">
                    <p>হ্যাঁ, ব্যবহারের পর সহজেই ভাঁজ করে আলমারি বা খাটের নিচে রেখে দেওয়া যায়।</p>
                </div>
            </div>

            <div class="mm-faq-item">
                <div class="mm-faq-question">
                    <span>এলসিডি ডিসপ্লেতে কী কী তথ্য দেখা যায়?</span>
                    <span class="mm-faq-icon"><?php echo mastermart_svg( 'chevron-down', array( 'size' => 18 ) ); ?></span>
                </div>
                <div class="mm-faq-answer">
                    <p>সময়, গতি, দূরত্ব ও পোড়া ক্যালরির স্পষ্ট হিসাব ডিজিটাল মনিটরে দেখা যায়।</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 6. 1-CLICK EXPRESS COD CHECKOUT FORM -->
<section class="mm-checkout-section" id="mmOrderSection">
    <div class="mm-container">
        <div class="mm-checkout-card">
            <div class="mm-checkout-header">
                <h2>ক্যাশ অন ডেলিভারি অর্ডার ফরম</h2>
                <p>ডেলিভারির ঠিকানার ঘরগুলো পূরণ করুন এবং “অর্ডার কনফার্ম করুন” বাটনে ক্লিক করুন</p>
            </div>

            <form id="mm-checkout-form" name="checkout" class="checkout woocommerce-checkout" method="post" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" novalidate>
                <input type="hidden" id="mm-product-id" name="product_id" value="<?php echo esc_attr( $default_prod_id ); ?>">

                <div class="mm-form-grid">
                    <!-- Left Column: Customer Information -->
                    <div class="mm-form-left">
                        <h3 class="mm-form-heading">
                            <span class="num">১</span>
                            <span>আপনার ডেলিভারি তথ্য দিন</span>
                        </h3>

                        <!-- Customer Full Name -->
                        <div class="mm-form-group">
                            <label for="billing_first_name">আপনার সম্পূর্ণ নাম <span class="req">*</span></label>
                            <input type="text" id="billing_first_name" name="billing_first_name" placeholder="যেমন: মোঃ কামরুল ইসলাম" required autocomplete="name">
                        </div>

                        <!-- Customer Mobile Number -->
                        <div class="mm-form-group">
                            <label for="billing_phone">১১ ডিজিটের মোবাইল নাম্বার <span class="req">*</span></label>
                            <input type="tel" id="billing_phone" name="billing_phone" placeholder="01XXXXXXXXX" required autocomplete="tel" oninput="mastermartValidatePhone(this)">
                            <span class="mm-val-msg" id="mm-phone-msg"></span>
                        </div>

                        <!-- Delivery Zone Selection -->
                        <div class="mm-form-group">
                            <label>ডেলিভারি এরিয়া নির্বাচন করুন <span class="req">*</span></label>
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
                        <div class="mm-form-group">
                            <label for="billing_address_1">সম্পূর্ণ ঠিকানা (জেলা, থানা ও এলাকা/রোড নং) <span class="req">*</span></label>
                            
                            <!-- District Quick Select Chips -->
                            <div class="mm-chips-wrap">
                                <span class="mm-chips-label">কুইক সিলেক্ট:</span>
                                <span class="mm-chip" onclick="mastermartQuickDistrict('ঢাকা')">ঢাকা</span>
                                <span class="mm-chip" onclick="mastermartQuickDistrict('চট্টগ্রাম')">চট্টগ্রাম</span>
                                <span class="mm-chip" onclick="mastermartQuickDistrict('সিলেট')">সিলেট</span>
                                <span class="mm-chip" onclick="mastermartQuickDistrict('রাজশাহী')">রাজশাহী</span>
                                <span class="mm-chip" onclick="mastermartQuickDistrict('খুলনা')">খুলনা</span>
                                <span class="mm-chip" onclick="mastermartQuickDistrict('গাজীপুর')">গাজীপুর</span>
                                <span class="mm-chip" onclick="mastermartQuickDistrict('নারায়ণগঞ্জ')">নারায়ণগঞ্জ</span>
                                <span class="mm-chip" onclick="mastermartQuickDistrict('কুমিল্লা')">কুমিল্লা</span>
                            </div>

                            <textarea id="billing_address_1" name="billing_address_1" rows="3" placeholder="আপনার জেলা, থানা ও গ্রাম/এলাকার নাম বিস্তারিত লিখুন" required autocomplete="street-address"></textarea>
                        </div>

                        <!-- Special Instructions -->
                        <div class="mm-form-group">
                            <label for="order_comments">অতিরিক্ত নির্দেশনা (ঐচ্ছিক)</label>
                            <input type="text" id="order_comments" name="order_comments" placeholder="যেমন: ডেলিভারির পূর্বে কল দিবেন">
                        </div>

                        <!-- WooCommerce Order Attribution Fields -->
                        <?php
                        if ( function_exists( 'wc_get_template' ) ) {
                            wc_get_template( 'checkout/order-attribution.php' );
                        }
                        ?>
                    </div>

                    <!-- Right Column: Order Summary & Place Order Button -->
                    <div class="mm-form-right">
                        <div class="mm-summary-box">
                            <h3>অর্ডার সামারি</h3>

                            <div class="mm-summary-product">
                                <img src="<?php echo esc_url( $img_dir . '/hero-pedal-bike.webp' ); ?>" alt="Pedal Bike">
                                <div class="mm-summary-product-info">
                                    <h4>Pedal Exercise Bike with LCD Display</h4>
                                    <span class="mm-summary-product-price">৳<?php echo esc_html( number_format( $product_price, 0 ) ); ?> × ১</span>
                                </div>
                            </div>

                            <div class="mm-sum-row">
                                <span>প্রোডাক্ট মূল্য</span>
                                <strong>৳<?php echo esc_html( number_format( $product_price, 0 ) ); ?></strong>
                            </div>

                            <div class="mm-sum-row">
                                <span>ডেলিভারি চার্জ</span>
                                <strong id="mm-line-shipping">৳<?php echo esc_html( $shipping_outside ); ?></strong>
                            </div>

                            <div class="mm-sum-row total">
                                <span>সর্বমোট বিল</span>
                                <span class="total-amount" id="mm-line-total">৳<?php echo esc_html( number_format( $default_total, 0 ) ); ?></span>
                            </div>

                            <button type="submit" class="mm-submit-btn" id="mm-place-order-btn">
                                <?php echo mastermart_svg( 'check', array( 'size' => 20 ) ); ?>
                                <span>👉 অর্ডার কনফার্ম করুন (ক্যাশ অন ডেলিভারি)</span>
                            </button>

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
</section>

<?php
get_footer();
