<?php
/**
 * Master Mart WooCommerce Integration & Auto-Bootstrapper
 *
 * Ensures the Pedal Exercise Bike product is automatically created,
 * priced, and ready for instant purchases without manual admin setup.
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get the Master Mart default product ID.
 */
function mastermart_get_default_product_id() {
    $product_id = get_option( 'mastermart_pedal_product_id', 0 );
    if ( $product_id && wc_get_product( $product_id ) ) {
        return (int) $product_id;
    }

    // Try finding by slug
    $existing = get_page_by_path( 'pedal-cyclelcd', OBJECT, 'product' );
    if ( $existing ) {
        update_option( 'mastermart_pedal_product_id', $existing->ID );
        return (int) $existing->ID;
    }

    // Try finding by title
    $query = new WP_Query( array(
        'post_type'      => 'product',
        'title'          => 'Pedal Exercise Bike with LCD Display-(Premium Quality)',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( ! empty( $query->posts[0] ) ) {
        update_option( 'mastermart_pedal_product_id', $query->posts[0] );
        return (int) $query->posts[0];
    }

    // Auto-create product if not found
    return mastermart_create_pedal_product();
}

/**
 * Automatically create the Pedal Exercise Bike WooCommerce product.
 */
function mastermart_create_pedal_product() {
    if ( ! class_exists( 'WC_Product_Simple' ) ) {
        return 0;
    }

    $product = new WC_Product_Simple();
    $product->set_name( 'Pedal Exercise Bike with LCD Display-(Premium Quality)' );
    $product->set_slug( 'pedal-cyclelcd' );
    $product->set_status( 'publish' );
    $product->set_catalog_visibility( 'visible' );
    $product->set_description( 'ঘরে বসেই প্রতিদিন ১৫ মিনিটে সুস্থ থাকুন। বয়স্ক, ডায়াবেটিস রোগী, অফিস কর্মী ও ফিজিওথেরাপি ব্যবহারকারীদের জন্য আদর্শ।' );
    $product->set_short_description( 'বয়স্ক, ডায়াবেটিস রোগী, অফিস কর্মী ও ফিজিওথেরাপি ব্যবহারকারীদের জন্য আদর্শ।' );
    $product->set_regular_price( '2500' );
    $product->set_sale_price( '2000' );
    $product->set_price( '2000' );
    $product->set_manage_stock( false );
    $product->set_stock_status( 'instock' );

    $product_id = $product->save();

    if ( $product_id ) {
        update_option( 'mastermart_pedal_product_id', $product_id );

        // Attach image if available
        $img_file = MASTERMART_DIR . '/assets/images/hero-pedal-bike.webp';
        if ( file_exists( $img_file ) ) {
            mastermart_attach_product_image( $product_id, $img_file );
        }
    }

    return (int) $product_id;
}

/**
 * Helper to attach local image to WooCommerce product.
 */
function mastermart_attach_product_image( $post_id, $file_path ) {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $upload_dir = wp_upload_dir();
    $filename   = basename( $file_path );

    if ( wp_mkdir_p( $upload_dir['path'] ) ) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    if ( ! file_exists( $file ) ) {
        copy( $file_path, $file );
    }

    $wp_filetype = wp_check_filetype( $filename, null );
    $attachment  = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $filename ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    wp_update_attachment_metadata( $attach_id, $attach_data );
    set_post_thumbnail( $post_id, $attach_id );
}

// Auto-run bootstrap on init if product missing
add_action( 'init', function() {
    if ( is_admin() || ! empty( $_POST['action'] ) ) {
        mastermart_get_default_product_id();
    }
} );
