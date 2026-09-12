<?php
/**
 * Master Mart AJAX Order Engine
 *
 * Handles 1-Click Express Cash on Delivery checkout,
 * real WooCommerce order generation, phone verification,
 * courier ratio validation, and order attribution.
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_mastermart_express_order', 'mastermart_ajax_express_order' );
add_action( 'wp_ajax_nopriv_mastermart_express_order', 'mastermart_ajax_express_order' );

function mastermart_ajax_express_order() {
    // Ultra-Fast Performance: Defer/disable transactional emails during AJAX checkout to eliminate blocking SMTP/DNS socket timeouts
    add_filter( 'woocommerce_defer_transactional_emails', '__return_true' );
    add_filter( 'woocommerce_email_enabled_customer_processing_order', '__return_false' );
    add_filter( 'woocommerce_email_enabled_new_order', '__return_false' );
    add_filter( 'pre_wp_mail', '__return_true', 999 );

    // Nonce verification
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'mastermart_nonce' ) ) {
        wp_send_json_error( array(
            'message' => esc_html__( 'নিরাপত্তা যাচাই ব্যর্থ হয়েছে। অনুগ্রহ করে পেজটি রিফ্রেশ করে আবার চেষ্টা করুন।', 'mastermart' ),
        ) );
    }

    // Input sanitization
    $customer_name    = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
    $customer_phone   = isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '';
    $customer_address = isset( $_POST['customer_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['customer_address'] ) ) : '';
    $order_comments   = isset( $_POST['order_comments'] ) ? sanitize_textarea_field( wp_unslash( $_POST['order_comments'] ) ) : '';
    $delivery_zone    = isset( $_POST['delivery_zone'] ) ? sanitize_text_field( wp_unslash( $_POST['delivery_zone'] ) ) : 'outside';
    $quantity         = isset( $_POST['quantity'] ) ? max( 1, absint( $_POST['quantity'] ) ) : 1;
    $product_id       = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

    // Validate Required Fields
    if ( empty( $customer_name ) || empty( $customer_phone ) || empty( $customer_address ) ) {
        wp_send_json_error( array(
            'message' => esc_html__( 'অনুগ্রহ করে আপনার নাম, মোবাইল নাম্বার এবং সম্পূর্ণ ঠিকানা সঠিকভাবে লিখুন।', 'mastermart' ),
        ) );
    }

    // Bangladeshi Phone Number Validation: Exactly 11 digits starting with 01
    $clean_phone = preg_replace( '/[^0-9]/', '', $customer_phone );
    if ( strlen( $clean_phone ) !== 11 || substr( $clean_phone, 0, 2 ) !== '01' ) {
        wp_send_json_error( array(
            'message' => esc_html__( 'অনুগ্রহ করে সঠিক ১১ সংখ্যার মোবাইল নাম্বার প্রদান করুন (যেমন: 017XXXXXXXX)।', 'mastermart' ),
        ) );
    }

    // Courier Delivery Ratio / Fraud Checker integration if available
    $min_delivery_rate = (int) get_option( 'fraud_checker_minimum_rate', 30 );
    if ( function_exists( 'fraud_checker_get_phone_validation_data' ) ) {
        $val_data = fraud_checker_get_phone_validation_data( $clean_phone );
        if ( $val_data && isset( $val_data['rate'] ) && ! empty( $val_data['total_orders'] ) ) {
            $past_rate = floatval( $val_data['rate'] );
            if ( $past_rate < $min_delivery_rate ) {
                wp_send_json_error( array(
                    'message' => sprintf(
                        esc_html__( 'আপনার পূর্ববর্তী পার্সেল ডেলিভারি সাক্সেস রেট %s%% (অনুমোদিত সর্বনিম্ন %s%% এর নিচে)। ক্যাশ অন ডেলিভারিতে অর্ডার সম্পন্ন করতে সরাসরি আমাদের কাস্টমার সার্ভিসে যোগাযোগ করুন।', 'mastermart' ),
                        $past_rate,
                        $min_delivery_rate
                    ),
                ) );
            }
        }
    }

    // Verify WooCommerce is active
    if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_product' ) || ! function_exists( 'wc_create_order' ) ) {
        wp_send_json_error( array(
            'message' => esc_html__( 'WooCommerce প্লাগইনটি সক্রিয় করা নেই। অনুগ্রহ করে WordPress ড্যাশবোর্ড থেকে WooCommerce সক্রিয় করুন।', 'mastermart' ),
        ) );
    }

    // If product_id is not passed or zero, look up or create Master Mart Pedal Cycle product
    if ( ! $product_id && function_exists( 'mastermart_get_default_product_id' ) ) {
        $product_id = mastermart_get_default_product_id();
    }

    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_purchasable() ) {
        wp_send_json_error( array(
            'message' => esc_html__( 'পণ্যটি বর্তমানে স্টক আউট অথবা উপলভ্য নয়।', 'mastermart' ),
        ) );
    }

    // Delivery Fee calculation
    $shipping_inside  = (float) get_option( 'mastermart_shipping_inside', 80 );
    $shipping_outside = (float) get_option( 'mastermart_shipping_outside', 150 );
    $shipping_cost    = ( 'inside' === $delivery_zone ) ? $shipping_inside : $shipping_outside;

    // Parse Customer Name
    $name_parts = explode( ' ', trim( $customer_name ), 2 );
    $first_name = $name_parts[0];
    $last_name  = isset( $name_parts[1] ) ? $name_parts[1] : '';

    // Fire standard checkout validation hooks for any listening courier plugins
    $_POST['billing_first_name'] = $first_name;
    $_POST['billing_last_name']  = $last_name;
    $_POST['billing_phone']      = $clean_phone;
    $_POST['billing_address_1']  = $customer_address;
    $_POST['payment_method']     = 'cod';

    try {
        do_action( 'woocommerce_checkout_process' );
        $validation_errors = new WP_Error();
        do_action( 'woocommerce_after_checkout_validation', $_POST, $validation_errors );
        if ( $validation_errors->has_errors() ) {
            wp_send_json_error( array( 'message' => $validation_errors->get_error_message() ) );
        }
    } catch ( Exception $e ) {
        // Continue if non-standard exception
    }

    // Create WooCommerce Order
    $order = wc_create_order();

    // Attach Customer IP & User Agent (Crucial for Meta CAPI & Google Analytics attribution)
    if ( class_exists( 'WC_Geolocation' ) ) {
        $order->set_customer_ip_address( WC_Geolocation::get_ip_address() );
    } elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
        $order->set_customer_ip_address( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) );
    }
    if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
        $order->set_customer_user_agent( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) );
    }

    // Add Product line item
    $order->add_product( $product, $quantity );

    // Billing info
    $fake_email = $clean_phone . '@guest.mastermart.com';
    $order->set_billing_first_name( $first_name );
    $order->set_billing_last_name( $last_name );
    $order->set_billing_phone( $clean_phone );
    $order->set_billing_email( $fake_email );
    $order->set_billing_address_1( $customer_address );
    $order->set_billing_country( 'BD' );

    // Shipping info
    $order->set_shipping_first_name( $first_name );
    $order->set_shipping_last_name( $last_name );
    $order->set_shipping_phone( $clean_phone );
    $order->set_shipping_address_1( $customer_address );
    $order->set_shipping_country( 'BD' );

    // Customer note
    if ( ! empty( $order_comments ) ) {
        $order->set_customer_note( $order_comments );
    }

    // Add Shipping Method line
    $shipping_title = ( 'inside' === $delivery_zone )
        ? esc_html__( 'ঢাকার ভেতরে হোম ডেলিভারি', 'mastermart' )
        : esc_html__( 'ঢাকার বাইরে সারা দেশে হোম ডেলিভারি', 'mastermart' );

    $shipping_item = new WC_Order_Item_Shipping();
    $shipping_item->set_method_title( $shipping_title );
    $shipping_item->set_total( $shipping_cost );
    $order->add_item( $shipping_item );

    // Payment Method: Cash on Delivery
    $order->set_payment_method( 'cod' );
    $order->set_payment_method_title( esc_html__( 'ক্যাশ অন ডেলিভারি (Cash on Delivery)', 'mastermart' ) );

    // Capture WooCommerce Order Attribution (UTMs, Referrer, Source)
    $attribution_keys = array(
        'source_type', 'referrer', 'utm_source', 'utm_medium',
        'utm_campaign', 'utm_term', 'utm_content', 'device_type'
    );
    foreach ( $attribution_keys as $attr_k ) {
        $attr_val = ! empty( $_POST['wc_order_attribution_' . $attr_k] ) ? sanitize_text_field( wp_unslash( $_POST['wc_order_attribution_' . $attr_k] ) ) : '';
        if ( ! empty( $attr_val ) ) {
            $order->update_meta_data( '_wc_order_attribution_' . $attr_k, $attr_val );
        }
    }

    // Save extra meta
    $order->update_meta_data( '_mastermart_delivery_zone', $delivery_zone );
    $order->update_meta_data( '_mastermart_express_order', '1' );

    // Calculate totals & set status
    $order->calculate_totals();
    $order->update_status( 'processing', esc_html__( '1-ক্লিক এক্সপ্রেস ক্যাশ অন ডেলিভারি অর্ডার তৈরি হয়েছে।', 'mastermart' ) );

    // Reduce stock levels
    wc_reduce_stock_levels( $order->get_id() );

    // Allow standard checkout order processed hooks
    do_action( 'woocommerce_checkout_create_order', $order, $_POST );
    do_action( 'woocommerce_checkout_order_processed', $order->get_id(), $_POST, $order );

    // Empty WooCommerce cart if active
    if ( function_exists( 'WC' ) && WC()->cart ) {
        WC()->cart->empty_cart();
    }

    // Build Redirect URL
    $received_url = $order->get_checkout_order_received_url();

    wp_send_json_success( array(
        'order_id'     => $order->get_id(),
        'redirect_url' => $received_url,
        'message'      => esc_html__( 'আপনার অর্ডারটি সফলভাবে গৃহীত হয়েছে!', 'mastermart' ),
    ) );
}
