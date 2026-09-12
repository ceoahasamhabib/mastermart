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
 * Get the Master Mart default/featured product ID.
 */
function mastermart_get_default_product_id() {
    // 1. Check custom product selected in Theme Options
    $custom_id = get_option( 'mastermart_featured_product_id', 0 );
    if ( $custom_id && function_exists( 'wc_get_product' ) && wc_get_product( $custom_id ) ) {
        return (int) $custom_id;
    }

    // 2. Check cached pedal product ID
    $product_id = get_option( 'mastermart_pedal_product_id', 0 );
    if ( $product_id && function_exists( 'wc_get_product' ) && wc_get_product( $product_id ) ) {
        return (int) $product_id;
    }

    // 3. Try finding by slug
    $existing = get_page_by_path( 'pedal-cyclelcd', OBJECT, 'product' );
    if ( $existing ) {
        update_option( 'mastermart_pedal_product_id', $existing->ID );
        return (int) $existing->ID;
    }

    // 4. Try finding by title
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

    // 5. Try finding ANY published WooCommerce product
    $any_query = new WP_Query( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( ! empty( $any_query->posts[0] ) ) {
        update_option( 'mastermart_pedal_product_id', $any_query->posts[0] );
        return (int) $any_query->posts[0];
    }

    // 6. Auto-create product if none exists
    return mastermart_create_pedal_product();
}

/**
 * Get full landing product data with WooCommerce integration and fallback.
 */
function mastermart_get_landing_product() {
    $product_id = mastermart_get_default_product_id();
    $wc_prod    = ( $product_id && function_exists( 'wc_get_product' ) ) ? wc_get_product( $product_id ) : null;
    $default_img= MASTERMART_URI . '/assets/images/hero-pedal-bike.webp';

    $data = array(
        'id'                => $product_id,
        'name'              => 'Pedal Exercise Bike with LCD Display-(Premium Quality)',
        'price'             => (float) get_option( 'mastermart_product_price', 2000 ),
        'regular_price'     => (float) get_option( 'mastermart_product_regular_price', 2500 ),
        'sale_price'        => (float) get_option( 'mastermart_product_price', 2000 ),
        'image_url'         => $default_img,
        'gallery_urls'      => array(),
        'description'       => 'ঘরে বসেই প্রতিদিন ১৫ মিনিটে সুস্থ থাকুন। বয়স্ক, ডায়াবেটিস রোগী, অফিস কর্মী ও ফিজিওথেরাপি ব্যবহারকারীদের জন্য আদর্শ মিনি প্যাডেল এক্সারসাইজ বাইক।',
        'short_description' => 'বয়স্ক, ডায়াবেটিস রোগী, অফিস কর্মী ও ফিজিওথেরাপি ব্যবহারকারীদের জন্য আদর্শ।',
        'in_stock'          => true,
        'wc_product'        => $wc_prod,
    );

    if ( $wc_prod ) {
        $data['name']              = $wc_prod->get_name();
        $price                     = $wc_prod->get_price();
        $reg_price                 = $wc_prod->get_regular_price();
        $sale_price                = $wc_prod->get_sale_price();

        $data['price']             = $price !== '' ? (float) $price : $data['price'];
        $data['regular_price']     = $reg_price !== '' ? (float) $reg_price : ( $data['price'] * 1.25 );
        $data['sale_price']        = $sale_price !== '' ? (float) $sale_price : $data['price'];
        $data['in_stock']          = $wc_prod->is_in_stock();

        $short = $wc_prod->get_short_description();
        if ( ! empty( $short ) ) {
            $data['short_description'] = wp_strip_all_tags( $short );
        }

        $desc = $wc_prod->get_description();
        if ( ! empty( $desc ) ) {
            $data['description'] = $desc;
        }

        // Image
        $img_id = $wc_prod->get_image_id();
        if ( $img_id ) {
            $img_url = wp_get_attachment_image_url( $img_id, 'full' );
            if ( $img_url ) {
                $data['image_url'] = $img_url;
            }
        }

        // Gallery
        $gallery_ids = $wc_prod->get_gallery_image_ids();
        if ( ! empty( $gallery_ids ) ) {
            foreach ( $gallery_ids as $gid ) {
                $g_url = wp_get_attachment_image_url( $gid, 'full' );
                if ( $g_url ) {
                    $data['gallery_urls'][] = $g_url;
                }
            }
        }
    }

    return $data;
}

/**
 * Automatically create the Pedal Exercise Bike WooCommerce product with category & media.
 */
function mastermart_create_pedal_product() {
    if ( ! class_exists( 'WC_Product_Simple' ) ) {
        return 0;
    }

    // Ensure product category exists
    $cat_name = 'ফিটনেস ও হেলথ কেয়ার';
    $cat_slug = 'fitness-health';
    $term = term_exists( $cat_name, 'product_cat' );
    if ( ! $term ) {
        $term = wp_insert_term( $cat_name, 'product_cat', array( 'slug' => $cat_slug ) );
    }
    $cat_id = ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) ? (int) $term['term_id'] : 0;

    $product = new WC_Product_Simple();
    $product->set_name( 'Pedal Exercise Bike with LCD Display-(Premium Quality)' );
    $product->set_slug( 'pedal-cyclelcd' );
    $product->set_sku( 'MM-PEDAL-01' );
    $product->set_status( 'publish' );
    $product->set_catalog_visibility( 'visible' );
    $product->set_description( 'ঘরে বসেই প্রতিদিন ১৫ মিনিটে সুস্থ থাকুন। বয়স্ক, ডায়াবেটিস রোগী, অফিস কর্মী ও ফিজিওথেরাপি ব্যবহারকারীদের জন্য আদর্শ মিনি প্যাডেল এক্সারসাইজ বাইক।' );
    $product->set_short_description( 'বয়স্ক, ডায়াবেটিস রোগী, অফিস কর্মী ও ফিজিওথেরাপি ব্যবহারকারীদের জন্য আদর্শ।' );
    $product->set_regular_price( '2500' );
    $product->set_sale_price( '2000' );
    $product->set_price( '2000' );
    $product->set_manage_stock( false );
    $product->set_stock_status( 'instock' );

    if ( $cat_id ) {
        $product->set_category_ids( array( $cat_id ) );
    }

    $product_id = $product->save();

    if ( $product_id ) {
        update_option( 'mastermart_pedal_product_id', $product_id );
        update_option( 'mastermart_featured_product_id', $product_id );

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
