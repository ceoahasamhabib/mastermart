<?php
/**
 * Master Mart Elementor Widget: 1-Click COD Checkout
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MasterMart_Elementor_Checkout_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'mastermart_checkout';
    }

    public function get_title() {
        return esc_html__( 'Master Mart 1-Click Checkout', 'mastermart' );
    }

    public function get_icon() {
        return 'eicon-cart';
    }

    public function get_categories() {
        return array( 'mastermart-elements' );
    }

    public function get_keywords() {
        return array( 'checkout', 'order', 'form', 'cod', 'woocommerce', 'mastermart' );
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            array(
                'label' => esc_html__( 'Content Settings', 'mastermart' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'form_title',
            array(
                'label'       => esc_html__( 'Form Title', 'mastermart' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => 'ক্যাশ অন ডেলিভারি অর্ডার ফরম',
                'placeholder' => 'Enter form title',
            )
        );

        $this->add_control(
            'form_subtitle',
            array(
                'label'       => esc_html__( 'Form Subtitle', 'mastermart' ),
                'type'        => \Elementor\Controls_Manager::TEXTAREA,
                'default'     => 'ডেলিভারির ঠিকানার ঘরগুলো পূরণ করুন এবং “অর্ডার কনফার্ম করুন” বাটনে ক্লিক করুন',
                'placeholder' => 'Enter subtitle',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        echo do_shortcode( sprintf(
            '[mastermart_checkout title="%s" subtitle="%s"]',
            esc_attr( $settings['form_title'] ),
            esc_attr( $settings['form_subtitle'] )
        ) );
    }
}
