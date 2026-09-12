<?php
/**
 * Master Mart Elementor Widget: Features
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MasterMart_Elementor_Features_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'mastermart_features';
    }

    public function get_title() {
        return esc_html__( 'Master Mart Features Grid', 'mastermart' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return array( 'mastermart-elements' );
    }

    public function get_keywords() {
        return array( 'features', 'grid', 'product', 'benefits', 'mastermart' );
    }

    protected function render() {
        echo do_shortcode( '[mastermart_features]' );
    }
}
