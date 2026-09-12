<?php
/**
 * Master Mart Theme Header
 *
 * @package MasterMart
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$hotline_phone = mastermart_get_phone();
$logo_url      = mastermart_get_logo_url();
$announcement  = get_option( 'mastermart_announcement_text', '🔥 আজকের স্পেশাল ডিসকাউন্ট অফার! ক্যাশ অন ডেলিভারি সারা বাংলাদেশে' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- 1. TOP URGENCY ANNOUNCEMENT BAR -->
<div class="mm-urgency-bar">
    <div class="mm-container">
        <div class="mm-urgency-inner">
            <span class="mm-urgency-text"><?php echo esc_html( $announcement ); ?></span>
            <span class="mm-timer-badge">
                <?php echo mastermart_svg( 'clock', array( 'size' => 15 ) ); ?>
                <span>অফার শেষ:</span>
                <strong id="mm-timer-display">04:38:22</strong>
            </span>
        </div>
    </div>
</div>

<!-- 2. SITE BRANDING HEADER -->
<header class="mm-header">
    <div class="mm-container">
        <div class="mm-header-inner">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mm-brand-link" aria-label="Master Mart Home">
                <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="mm-logo-img">
            </a>

            <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $hotline_phone ) ); ?>" class="mm-header-hotline">
                <?php echo mastermart_svg( 'phone', array( 'size' => 18 ) ); ?>
                <span><?php echo esc_html( $hotline_phone ); ?></span>
            </a>
        </div>
    </div>
</header>
