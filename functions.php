<?php
/**
 * Master Mart Theme Functions and Definitions
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define theme constants.
define( 'MASTERMART_VERSION', '1.0.0' );
define( 'MASTERMART_DIR', get_template_directory() );
define( 'MASTERMART_URI', get_template_directory_uri() );

/**
 * Load core modular files in logical execution order.
 */
require_once MASTERMART_DIR . '/inc/helpers.php';
require_once MASTERMART_DIR . '/inc/setup.php';
require_once MASTERMART_DIR . '/inc/enqueue.php';
require_once MASTERMART_DIR . '/inc/ajax.php';
require_once MASTERMART_DIR . '/inc/tracking.php';
require_once MASTERMART_DIR . '/inc/theme-options.php';

// Load WooCommerce integration if WooCommerce is active.
if ( class_exists( 'WooCommerce' ) ) {
    require_once MASTERMART_DIR . '/inc/woocommerce.php';
} else {
    add_action( 'plugins_loaded', function() {
        if ( class_exists( 'WooCommerce' ) ) {
            require_once MASTERMART_DIR . '/inc/woocommerce.php';
        }
    } );
}
