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
define( 'MASTERMART_VERSION', '1.0.1' );
define( 'MASTERMART_DIR', get_template_directory() );
define( 'MASTERMART_URI', get_template_directory_uri() );
define( 'MASTERMART_GITHUB_REPO', 'ceoahasamhabib/mastermart' );
define( 'MASTERMART_GITHUB_BRANCH', 'main' );

/**
 * Load core modular files in logical execution order.
 */
require_once MASTERMART_DIR . '/inc/helpers.php';
require_once MASTERMART_DIR . '/inc/setup.php';
require_once MASTERMART_DIR . '/inc/enqueue.php';
require_once MASTERMART_DIR . '/inc/ajax.php';
require_once MASTERMART_DIR . '/inc/tracking.php';
require_once MASTERMART_DIR . '/inc/theme-options.php';
require_once MASTERMART_DIR . '/inc/license.php';
require_once MASTERMART_DIR . '/inc/elementor.php';
require_once MASTERMART_DIR . '/inc/demo-importer.php';
require_once MASTERMART_DIR . '/inc/github-updater.php';
require_once MASTERMART_DIR . '/inc/woocommerce.php';
