<?php
/**
 * Master Mart Elementor Widget: Hero Banner
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MasterMart_Elementor_Hero_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'mastermart_hero';
    }

    public function get_title() {
        return esc_html__( 'Master Mart Hero Banner', 'mastermart' );
    }

    public function get_icon() {
        return 'eicon-banner';
    }

    public function get_categories() {
        return array( 'mastermart-elements' );
    }

    public function get_keywords() {
        return array( 'hero', 'banner', 'product', 'cta', 'mastermart' );
    }

    protected function render() {
        echo do_shortcode( '[mastermart_hero]' );
    }
}
