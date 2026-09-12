<?php
/**
 * Master Mart Elementor Widget: FAQ Accordion
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MasterMart_Elementor_FAQ_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'mastermart_faq';
    }

    public function get_title() {
        return esc_html__( 'Master Mart FAQ Accordion', 'mastermart' );
    }

    public function get_icon() {
        return 'eicon-help-o';
    }

    public function get_categories() {
        return array( 'mastermart-elements' );
    }

    public function get_keywords() {
        return array( 'faq', 'accordion', 'questions', 'answers', 'mastermart' );
    }

    protected function render() {
        echo do_shortcode( '[mastermart_faq]' );
    }
}
