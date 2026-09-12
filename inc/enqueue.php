<?php
/**
 * Master Mart Enqueue Scripts & Styles
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mastermart_scripts() {
    // Google Fonts (Plus Jakarta Sans + Hind Siliguri for Bengali)
    wp_enqueue_style(
        'mastermart-fonts',
        'https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap',
        array(),
        null
    );

    // Theme Main Stylesheet
    wp_enqueue_style( 'mastermart-style', get_stylesheet_uri(), array(), MASTERMART_VERSION );

    // Modern Design System Styles
    wp_enqueue_style(
        'mastermart-main',
        MASTERMART_URI . '/assets/css/main.css',
        array( 'mastermart-style' ),
        MASTERMART_VERSION
    );

    // Checkout & Order Form Styles
    wp_enqueue_style(
        'mastermart-checkout',
        MASTERMART_URI . '/assets/css/checkout.css',
        array( 'mastermart-main' ),
        MASTERMART_VERSION
    );

    // Responsive Mobile-First Styles
    wp_enqueue_style(
        'mastermart-responsive',
        MASTERMART_URI . '/assets/css/responsive.css',
        array( 'mastermart-checkout' ),
        MASTERMART_VERSION
    );

    // Express Order & Interactive JS
    wp_enqueue_script(
        'mastermart-express',
        MASTERMART_URI . '/assets/js/express-order.js',
        array(),
        MASTERMART_VERSION,
        true
    );

    // Localize Script for AJAX & Pricing
    $product_price    = (float) get_option( 'mastermart_product_price', 2000 );
    $shipping_inside  = (float) get_option( 'mastermart_shipping_inside', 80 );
    $shipping_outside = (float) get_option( 'mastermart_shipping_outside', 120 );

    wp_localize_script( 'mastermart-express', 'mastermart_ajax', array(
        'ajax_url'         => admin_url( 'admin-ajax.php' ),
        'nonce'            => wp_create_nonce( 'mastermart_nonce' ),
        'product_price'    => $product_price,
        'shipping_inside'  => $shipping_inside,
        'shipping_outside' => $shipping_outside,
        'currency_symbol'  => '৳',
        'strings'          => array(
            'processing'      => esc_html__( 'অর্ডার প্রসেস হচ্ছে...', 'mastermart' ),
            'order_btn_text'  => esc_html__( '👉 অর্ডার কনফার্ম করুন (ক্যাশ অন ডেলিভারি)', 'mastermart' ),
            'phone_err'       => esc_html__( 'অনুগ্রহ করে সঠিক ১১ ডিজিটের মোবাইল নাম্বার দিন (যেমন: 01XXXXXXXXX)', 'mastermart' ),
            'required_err'    => esc_html__( 'অনুগ্রহ করে নাম, মোবাইল নাম্বার এবং সম্পূর্ণ ঠিকানা পূরণ করুন।', 'mastermart' ),
            'server_err'      => esc_html__( 'সার্ভার রেসপন্স করছে না। অনুগ্রহ করে সরাসরি ফোনে যোগাযোগ করুন।', 'mastermart' ),
        ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'mastermart_scripts' );
