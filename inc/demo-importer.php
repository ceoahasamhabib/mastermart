<?php
/**
 * Master Mart 1-Click Demo Importer & Automated Dependency Installer
 *
 * Checks required plugin dependencies (WooCommerce & Elementor), installs/activates
 * them automatically, and imports full demo content (WooCommerce products, landing page,
 * policies, navigation menus, and WooCommerce settings) permanently into the WordPress database.
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Demo Importer admin menu under Master Mart and Appearance.
 */
function mastermart_register_demo_importer_menu() {
    // Under Master Mart top menu
    add_submenu_page(
        'mastermart-settings',
        __( '1-Click Demo Import', 'mastermart' ),
        __( '1-Click Demo Import', 'mastermart' ),
        'manage_options',
        'mastermart-demo-importer',
        'mastermart_render_demo_importer_page'
    );

    // Under Appearance menu
    add_theme_page(
        __( 'Master Mart Demo Import', 'mastermart' ),
        __( 'Master Mart ডেমো ইম্পোর্ট', 'mastermart' ),
        'manage_options',
        'mastermart-demo-importer',
        'mastermart_render_demo_importer_page'
    );
}
add_action( 'admin_menu', 'mastermart_register_demo_importer_menu' );

/**
 * Helper: Check if a plugin is active.
 */
function mastermart_is_plugin_active( $plugin_path ) {
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    return is_plugin_active( $plugin_path );
}

/**
 * Helper: Check if a plugin is installed.
 */
function mastermart_is_plugin_installed( $plugin_path ) {
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $installed = get_plugins();
    return isset( $installed[ $plugin_path ] );
}

/**
 * Helper: Install and activate a plugin from WordPress.org repository.
 */
function mastermart_install_and_activate_plugin( $slug, $file_path ) {
    if ( mastermart_is_plugin_active( $file_path ) ) {
        return true;
    }

    require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    if ( ! mastermart_is_plugin_installed( $file_path ) ) {
        $api = plugins_api( 'plugin_information', array(
            'slug'   => $slug,
            'fields' => array( 'sections' => false ),
        ) );

        if ( is_wp_error( $api ) ) {
            return false;
        }

        $skin     = new WP_Ajax_Upgrader_Skin();
        $upgrader = new Plugin_Upgrader( $skin );
        $result   = $upgrader->install( $api->download_link );

        if ( is_wp_error( $result ) || ! $result ) {
            return false;
        }
    }

    $activated = activate_plugin( $file_path );
    return ! is_wp_error( $activated );
}

/**
 * Handle Demo Import & Plugin Installation Actions.
 */
function mastermart_handle_importer_actions() {
    if ( ! isset( $_POST['mastermart_importer_action'] ) ) {
        return;
    }

    if ( ! check_admin_referer( 'mastermart_demo_nonce_action', 'mastermart_demo_nonce' ) ) {
        return;
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $action = sanitize_text_field( $_POST['mastermart_importer_action'] );

    // Action 1: Auto Install Required Plugins
    if ( 'install_plugins' === $action ) {
        mastermart_install_and_activate_plugin( 'woocommerce', 'woocommerce/woocommerce.php' );
        mastermart_install_and_activate_plugin( 'elementor', 'elementor/elementor.php' );

        wp_safe_redirect( admin_url( 'admin.php?page=mastermart-demo-importer&plugins_installed=1' ) );
        exit;
    }

    // Action 2: Import Full Demo Content Permanently
    if ( 'import_demo_content' === $action ) {
        mastermart_execute_demo_import();
        wp_safe_redirect( admin_url( 'admin.php?page=mastermart-demo-importer&demo_imported=1' ) );
        exit;
    }
}
add_action( 'admin_init', 'mastermart_handle_importer_actions' );

/**
 * Helper: Upload local demo image to WordPress Media Library.
 */
function mastermart_upload_demo_image( $file_path, $title ) {
    if ( ! file_exists( $file_path ) ) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $filename   = basename( $file_path );
    $upload_dir = wp_upload_dir();

    // Check if attachment already exists
    global $wpdb;
    $existing_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND guid LIKE %s LIMIT 1",
        '%' . $wpdb->esc_like( $filename ) . '%'
    ) );
    if ( $existing_id ) {
        return (int) $existing_id;
    }

    if ( wp_mkdir_p( $upload_dir['path'] ) ) {
        $target_file = $upload_dir['path'] . '/' . $filename;
    } else {
        $target_file = $upload_dir['basedir'] . '/' . $filename;
    }

    copy( $file_path, $target_file );

    $wp_filetype = wp_check_filetype( $filename, null );
    $attachment  = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_text_field( $title ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    $attach_id = wp_insert_attachment( $attachment, $target_file );
    if ( ! is_wp_error( $attach_id ) && $attach_id ) {
        $attach_data = wp_generate_attachment_metadata( $attach_id, $target_file );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        return (int) $attach_id;
    }

    return 0;
}

/**
 * Helper: Ensure page exists without creating duplicate slugs.
 */
function mastermart_ensure_page( $slug, $title, $template = '', $content = '' ) {
    $existing = get_page_by_path( $slug );
    if ( $existing ) {
        if ( $template && get_post_meta( $existing->ID, '_wp_page_template', true ) !== $template ) {
            update_post_meta( $existing->ID, '_wp_page_template', $template );
        }
        return (int) $existing->ID;
    }

    $page_id = wp_insert_post( array(
        'post_title'     => $title,
        'post_name'      => $slug,
        'post_content'   => $content,
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed',
    ) );

    if ( $page_id && ! is_wp_error( $page_id ) && $template ) {
        update_post_meta( $page_id, '_wp_page_template', $template );
    }

    return (int) $page_id;
}

/**
 * Execute Complete Master Mart Demo Import.
 */
function mastermart_execute_demo_import() {
    $theme_dir = MASTERMART_DIR;

    // 1. Ensure WooCommerce Product Exists with Category & Gallery
    $product_id = 0;
    if ( class_exists( 'WooCommerce' ) ) {
        // Create category
        $cat_name = 'ফিটনেস ও হেলথ কেয়ার';
        $cat_slug = 'fitness-health';
        $term     = term_exists( $cat_name, 'product_cat' );
        if ( ! $term ) {
            $term = wp_insert_term( $cat_name, 'product_cat', array( 'slug' => $cat_slug ) );
        }
        $cat_id = ( ! is_wp_error( $term ) && isset( $term['term_id'] ) ) ? (int) $term['term_id'] : 0;

        $p_slug = 'pedal-cyclelcd';
        $existing_prod = get_page_by_path( $p_slug, OBJECT, 'product' );

        if ( ! $existing_prod ) {
            $product = new WC_Product_Simple();
            $product->set_name( 'Pedal Exercise Bike with LCD Display-(Premium Quality)' );
            $product->set_slug( $p_slug );
            $product->set_sku( 'MM-PEDAL-01' );
            $product->set_status( 'publish' );
            $product->set_catalog_visibility( 'visible' );
            $product->set_regular_price( '2500' );
            $product->set_sale_price( '2000' );
            $product->set_price( '2000' );
            $product->set_description( 'ঘরে বসেই প্রতিদিন ১৫ মিনিটে সুস্থ থাকুন। বয়স্ক, ডায়াবেটিস রোগী, অফিস কর্মী ও ফিজিওথেরাপি ব্যবহারকারীদের জন্য আদর্শ মিনি প্যাডেল এক্সারসাইজ বাইক।' );
            $product->set_short_description( 'বয়স্ক, ডায়াবেটিস রোগী, অফিস কর্মী ও ফিজিওথেরাপি ব্যবহারকারীদের জন্য আদর্শ।' );
            $product->set_manage_stock( false );
            $product->set_stock_status( 'instock' );

            if ( $cat_id ) {
                $product->set_category_ids( array( $cat_id ) );
            }

            // Featured Image
            $hero_img = $theme_dir . '/assets/images/hero-pedal-bike.webp';
            if ( file_exists( $hero_img ) ) {
                $thumb_id = mastermart_upload_demo_image( $hero_img, 'Pedal Exercise Bike' );
                if ( $thumb_id ) {
                    $product->set_image_id( $thumb_id );
                }
            }

            // Gallery Images
            $gallery_files = array(
                $theme_dir . '/assets/images/feature-compact.jpg',
                $theme_dir . '/assets/images/feature-lcd.jpg',
                $theme_dir . '/assets/images/feature-resistance.jpg',
                $theme_dir . '/assets/images/feature-physio.jpg',
            );
            $gallery_ids = array();
            foreach ( $gallery_files as $gfile ) {
                if ( file_exists( $gfile ) ) {
                    $gid = mastermart_upload_demo_image( $gfile, basename( $gfile ) );
                    if ( $gid ) {
                        $gallery_ids[] = $gid;
                    }
                }
            }
            if ( ! empty( $gallery_ids ) ) {
                $product->set_gallery_image_ids( $gallery_ids );
            }

            $product_id = $product->save();
        } else {
            $product_id = $existing_prod->ID;
        }

        update_option( 'mastermart_pedal_product_id', $product_id );
        update_option( 'mastermart_featured_product_id', $product_id );

        // Ensure WooCommerce default pages exist (Shop, Cart, Checkout, My Account)
        if ( class_exists( 'WC_Install' ) ) {
            WC_Install::create_pages();
        }

        // Configure WooCommerce Currency & Payment Settings
        update_option( 'woocommerce_currency', 'BDT' );
        update_option( 'woocommerce_currency_pos', 'left' );
        update_option( 'woocommerce_price_thousand_sep', ',' );
        update_option( 'woocommerce_price_decimal_sep', '.' );
        update_option( 'woocommerce_price_num_decimals', 0 );

        // Enable Cash On Delivery
        $cod_settings = get_option( 'woocommerce_cod_settings', array() );
        $cod_settings['enabled'] = 'yes';
        $cod_settings['title']   = 'ক্যাশ অন ডেলিভারি (Cash On Delivery)';
        $cod_settings['description'] = 'পণ্য হাতে পেয়ে চেক করে ডেলিভারিম্যানকে মূল্য পরিশোধ করুন।';
        update_option( 'woocommerce_cod_settings', $cod_settings );
    }

    // 2. Create Landing Page and Policy Pages
    $landing_id = mastermart_ensure_page( 
        'pedal-exercise-bike', 
        'Pedal Exercise Bike with LCD Display — Master Mart', 
        'templates/template-landing-page.php',
        '[mastermart_hero][mastermart_features][mastermart_faq][mastermart_checkout]'
    );

    // Set Front Page to Landing Page
    if ( $landing_id ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $landing_id );
    }

    // Essential Policy Pages
    mastermart_ensure_page( 'shipping-policy', 'ডেলিভারি ও শিপিং পলিসি', '', '<p>সারা দেশে ২-৩ দিনের মধ্যে হোম ডেলিভারি এবং ঢাকার ভেতরে ২৪-৪৮ ঘণ্টার মধ্যে ক্যাশ অন ডেলিভারি সুবিধা।</p>' );
    mastermart_ensure_page( 'refund-policy', 'রিটার্ন ও রিফান্ড পলিসি', '', '<p>পণ্য গ্রহণের সময় কোনো ত্রুটি থাকলে তাৎক্ষণিক ডেলিভারিম্যানকে রিটার্ন করুন অথবা ২৪ ঘণ্টার মধ্যে আমাদের হেল্পলাইনে জানান।</p>' );
    mastermart_ensure_page( 'privacy-policy', 'গোপনীয়তা নীতি (Privacy Policy)', '', '<p>গ্রাহকের ব্যক্তিগত ও অর্ডারের তথ্য ১০০% নিরাপদ ও সুরক্ষিত রাখা হয়।</p>' );
    mastermart_ensure_page( 'terms-conditions', 'শর্তাবলী ও নিয়মাবলী (Terms & Conditions)', '', '<p>Master Mart থেকে অর্ডারকৃত পণ্যের শর্তাবলী ও নিয়মনীতিসমূহ।</p>' );
    mastermart_ensure_page( 'contact', 'যোগাযোগ ও হেল্পলাইন (Contact Us)', '', '<p>হটলাইন: 01805060688 | হোয়াটসঅ্যাপ: 01805060688 | ইমেইল: support@mastermart.com</p>' );
    mastermart_ensure_page( 'about-us', 'আমাদের সম্পর্কে (About Us)', '', '<p>Master Mart বাংলাদেশের একটি বিশ্বস্ত অনলাইন শপ, যা গুণগত মানসম্পন্ন ফিটনেস ও লাইফস্টাইল পণ্য পৌঁছে দেয় গ্রাহকের দোরগোড়ায়।</p>' );

    // 3. Create Primary Navigation Menu
    $primary_menu_name = 'Master Mart Primary Menu';
    $primary_menu      = wp_get_nav_menu_object( $primary_menu_name );
    if ( ! $primary_menu ) {
        $p_menu_id = wp_create_nav_menu( $primary_menu_name );
        if ( ! is_wp_error( $p_menu_id ) ) {
            // Home
            wp_update_nav_menu_item( $p_menu_id, 0, array(
                'menu-item-title'  => 'হোম',
                'menu-item-url'    => home_url( '/' ),
                'menu-item-status' => 'publish',
            ) );
            // Shop
            $shop_url = function_exists( 'wc_get_page_id' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' );
            wp_update_nav_menu_item( $p_menu_id, 0, array(
                'menu-item-title'  => 'শপ / সকল পণ্য',
                'menu-item-url'    => $shop_url,
                'menu-item-status' => 'publish',
            ) );
            // Features
            wp_update_nav_menu_item( $p_menu_id, 0, array(
                'menu-item-title'  => 'সুবিধাসমূহ',
                'menu-item-url'    => home_url( '/#features' ),
                'menu-item-status' => 'publish',
            ) );
            // FAQ / Reviews
            wp_update_nav_menu_item( $p_menu_id, 0, array(
                'menu-item-title'  => 'প্রশ্নোত্তর ও রিভিউ',
                'menu-item-url'    => home_url( '/#faq' ),
                'menu-item-status' => 'publish',
            ) );
            // Order Now
            wp_update_nav_menu_item( $p_menu_id, 0, array(
                'menu-item-title'  => '👉 অর্ডার করুন',
                'menu-item-url'    => home_url( '/#mmOrderSection' ),
                'menu-item-status' => 'publish',
            ) );

            $locations = get_theme_mod( 'nav_menu_locations', array() );
            $locations['primary'] = $p_menu_id;
            $locations['mobile']  = $p_menu_id;
            set_theme_mod( 'nav_menu_locations', $locations );
        }
    }

    // 4. Create Footer Navigation Menu
    $footer_menu_name = 'Master Mart Footer Menu';
    $footer_menu      = wp_get_nav_menu_object( $footer_menu_name );
    if ( ! $footer_menu ) {
        $f_menu_id = wp_create_nav_menu( $footer_menu_name );
        if ( ! is_wp_error( $f_menu_id ) ) {
            $policies = array(
                'শিপিং পলিসি'      => home_url( '/shipping-policy/' ),
                'রিটার্ন ও রিফান্ড' => home_url( '/refund-policy/' ),
                'গোপনীয়তা নীতি'    => home_url( '/privacy-policy/' ),
                'শর্তাবলী'         => home_url( '/terms-conditions/' ),
                'যোগাযোগ'          => home_url( '/contact/' ),
                'আমাদের সম্পর্কে'   => home_url( '/about-us/' ),
            );
            foreach ( $policies as $p_title => $p_url ) {
                wp_update_nav_menu_item( $f_menu_id, 0, array(
                    'menu-item-title'  => $p_title,
                    'menu-item-url'    => $p_url,
                    'menu-item-status' => 'publish',
                ) );
            }
            $locations = get_theme_mod( 'nav_menu_locations', array() );
            $locations['footer'] = $f_menu_id;
            set_theme_mod( 'nav_menu_locations', $locations );
        }
    }

    // 5. Configure Theme Options Defaults
    $demo_phone = '01805060688';
    update_option( 'mastermart_hotline_phone', $demo_phone );
    update_option( 'mastermart_whatsapp_number', $demo_phone );
    update_option( 'mastermart_shipping_inside', 80 );
    update_option( 'mastermart_shipping_outside', 120 );
    update_option( 'mastermart_product_price', 2000 );
    update_option( 'mastermart_product_regular_price', 2500 );
    update_option( 'mastermart_announcement_text', '🔥 সীমিত সময়ের স্পেশাল অফার! স্টক শেষ হওয়ার আগেই অর্ডার কনফার্ম করুন।' );

    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Render Demo Importer Admin Page.
 */
function mastermart_render_demo_importer_page() {
    $wc_installed  = mastermart_is_plugin_installed( 'woocommerce/woocommerce.php' );
    $wc_active     = mastermart_is_plugin_active( 'woocommerce/woocommerce.php' );
    $el_installed  = mastermart_is_plugin_installed( 'elementor/elementor.php' );
    $el_active     = mastermart_is_plugin_active( 'elementor/elementor.php' );

    $plugins_ready = $wc_active && $el_active;
    ?>
    <div class="wrap" style="max-width: 900px; margin: 25px auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;">
        <!-- Header Banner -->
        <div style="background: linear-gradient(135deg, #0B192C 0%, #1E3E62 100%); color: #fff; padding: 25px 30px; border-radius: 12px 12px 0 0; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h1 style="color: #fff; margin: 0 0 8px 0; font-size: 24px; font-weight: 700;">
                    🛒 Master Mart — ১-ক্লিক ডেমো ইম্পোর্ট (1-Click Demo Import)
                </h1>
                <p style="margin: 0; color: #94A3B8; font-size: 14px;">
                    সম্পূর্ণ ডেমো কনটেন্ট, WooCommerce প্রোডাক্ট, ল্যান্ডিং পেজ, পলিসি পেজ এবং মেনু ১ ক্লিকে ইনস্টল করুন।
                </p>
            </div>
            <span style="background: #FF6500; color: #fff; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700;">
                v1.0.0 Ready
            </span>
        </div>

        <div style="background: #ffffff; border: 1px solid #E2E8F0; border-top: none; border-radius: 0 0 12px 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            
            <?php if ( isset( $_GET['plugins_installed'] ) ) : ?>
                <div class="notice notice-success is-dismissible" style="margin: 0 0 20px 0; padding: 12px; border-left-color: #10B981;">
                    <p style="font-size: 15px; margin: 0; color: #065F46; font-weight: 600;">
                        ✅ WooCommerce এবং Elementor প্লাগইন সফলভাবে ইনস্টল ও সক্রিয় করা হয়েছে!
                    </p>
                </div>
            <?php endif; ?>

            <?php if ( isset( $_GET['demo_imported'] ) ) : ?>
                <div class="notice notice-success is-dismissible" style="margin: 0 0 25px 0; padding: 16px; border-left-color: #10B981; background: #ECFDF5;">
                    <h3 style="margin: 0 0 8px 0; color: #065F46; font-size: 18px;">🎉 অভিনন্দন! ডেমো সফলভাবে ইম্পোর্ট সম্পন্ন হয়েছে!</h3>
                    <p style="margin: 0 0 12px 0; color: #047857; font-size: 14px;">
                        WooCommerce প্রোডাক্ট, ইমেজ গ্যালারি, ল্যান্ডিং পেজ, মেনু এবং থিম অপশন ডাটাবেজে পারফেক্টলি সেট হয়েছে।
                    </p>
                    <div style="display: flex; gap: 10px;">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" class="button button-primary" style="background: #FF6500; border-color: #FF6500; padding: 4px 18px; font-weight: 700;">
                            🌐 লাইভ সাইট দেখুন
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product' ) ); ?>" target="_blank" class="button" style="font-weight: 600;">
                            📦 প্রোডাক্ট তালিকা দেখুন
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Step 1: Plugin Dependency Status -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                <h3 style="margin-top: 0; color: #0B192C; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                    <span>১. প্রয়োজনীয় প্লাগইন স্ট্যাটাস (Plugin Dependencies)</span>
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <!-- WooCommerce -->
                    <div style="background: #fff; padding: 14px; border-radius: 6px; border: 1px solid <?php echo $wc_active ? '#10B981' : '#F59E0B'; ?>; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="font-size: 15px; color: #1E293B;">WooCommerce</strong>
                            <div style="font-size: 13px; color: <?php echo $wc_active ? '#059669' : '#D97706'; ?>; margin-top: 2px;">
                                <?php echo $wc_active ? '● সক্রিয় (Active)' : ( $wc_installed ? '○ নিষ্ক্রিয় (Inactive)' : '✕ ইনস্টল করা নেই (Not Installed)' ); ?>
                            </div>
                        </div>
                        <span style="font-size: 20px;"><?php echo $wc_active ? '✅' : '⚠️'; ?></span>
                    </div>

                    <!-- Elementor -->
                    <div style="background: #fff; padding: 14px; border-radius: 6px; border: 1px solid <?php echo $el_active ? '#10B981' : '#F59E0B'; ?>; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong style="font-size: 15px; color: #1E293B;">Elementor</strong>
                            <div style="font-size: 13px; color: <?php echo $el_active ? '#059669' : '#D97706'; ?>; margin-top: 2px;">
                                <?php echo $el_active ? '● সক্রিয় (Active)' : ( $el_installed ? '○ নিষ্ক্রিয় (Inactive)' : '✕ ইনস্টল করা নেই (Not Installed)' ); ?>
                            </div>
                        </div>
                        <span style="font-size: 20px;"><?php echo $el_active ? '✅' : '⚠️'; ?></span>
                    </div>
                </div>

                <?php if ( ! $plugins_ready ) : ?>
                    <form method="post" action="" style="margin-top: 15px;">
                        <?php wp_nonce_field( 'mastermart_demo_nonce_action', 'mastermart_demo_nonce' ); ?>
                        <input type="hidden" name="mastermart_importer_action" value="install_plugins">
                        <button type="submit" class="button" style="background: #2563EB; color: #fff; border-color: #1D4ED8; padding: 6px 18px; font-weight: 600; cursor: pointer;">
                            ⚡ ১-ক্লিকে প্রয়োজনীয় প্লাগইন ইনস্টল ও সক্রিয় করুন
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Step 2: 1-Click Import Content -->
            <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 20px;">
                <h3 style="margin-top: 0; color: #0B192C; font-size: 16px;">
                    ২. সম্পূর্ণ ডেমো প্যাকেজ ইম্পোর্ট (Import Full Demo Package)
                </h3>
                <p style="color: #64748B; font-size: 14px; line-height: 1.6;">
                    এই বাটনে ক্লিক করার সাথে সাথে স্বয়ংক্রিয়ভাবে নিম্নোক্ত বিষয়গুলো ডাটাবেজে কনফিগার হয়ে যাবে:
                </p>

                <ul style="color: #334155; font-size: 14px; line-height: 1.8; margin-left: 20px; list-style: disc;">
                    <li><strong>WooCommerce Product:</strong> "Pedal Exercise Bike with LCD Display" (প্রাইস: ৳২,০০০, রেগুলার: ৳২,৫০০, ছবি ও গ্যালারি)।</li>
                    <li><strong>High-Converting Landing Page:</strong> সম্পূর্ণ বাংলা রেডিমেড কনটেন্ট ও ১-ক্লিক ক্যাশ অন ডেলিভারি চেকআউট সেকশন।</li>
                    <li><strong>৬টি পলিসি পেজ:</strong> শিপিং পলিসি, রিটার্ন পলিসি, প্রাইভেসি পলিসি, শর্তাবলী, কন্টাক্ট এবং এবাউট আস পেজ।</li>
                    <li><strong>ন্যাভিগেশন মেনু:</strong> হেডার প্রাইমারি মেনু, মোবাইল মেনু ও ফুটার মেনু অটো ক্রিয়েট ও অ্যাসাইন।</li>
                    <li><strong>WooCommerce সেটিংস:</strong> বাংলাদেশি টাকা (BDT ৳) কারেন্সি, ঢাকা (৳৮০) ও ঢাকার বাইরে (৳১৫০) ডেলিভারি ফি এবং ক্যাশ অন ডেলিভারি গেটওয়ে সক্রিয়করণ।</li>
                </ul>

                <form method="post" action="" onsubmit="return confirm('আপনি কি নিশ্চিত যে সম্পূর্ণ ডেমো কনটেন্ট ইম্পোর্ট করতে চান? এতে প্রয়োজনীয় পেজ ও প্রোডাক্ট তৈরি হবে।');">
                    <?php wp_nonce_field( 'mastermart_demo_nonce_action', 'mastermart_demo_nonce' ); ?>
                    <input type="hidden" name="mastermart_importer_action" value="import_demo_content">
                    
                    <button type="submit" class="button button-primary" style="background: linear-gradient(135deg, #FF6500 0%, #E05A00 100%); border-color: #FF6500; font-size: 16px; padding: 10px 28px; font-weight: 700; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(255, 101, 0, 0.3); cursor: pointer;">
                        🚀 ১-ক্লিক ডেমো ইম্পোর্ট করুন (Import Everything)
                    </button>
                </form>
            </div>

            <!-- Manual File Import Info -->
            <div style="margin-top: 25px; padding: 15px; border-top: 1px dashed #CBD5E1; color: #64748B; font-size: 13px;">
                💡 <strong>ম্যানুয়াল ইম্পোর্ট ব্যাকআপ:</strong> থিমের <code>demo-data/</code> ফোল্ডারে <code>content.xml</code>, <code>theme-options.json</code>, এবং ফুল ডাটাবেজ ব্যাকআপ <code>mastermart.sql</code> দেওয়া আছে। এছাড়া <strong>One Click Demo Import (OCDI)</strong> প্লাগইনও সম্পূর্ণরূপে সাপোর্টেড।
            </div>

        </div>
    </div>
    <?php
}

/**
 * Register OCDI (One Click Demo Import) Plugin Compatibility Filters.
 */
function mastermart_ocdi_import_files() {
    $demo_dir = MASTERMART_DIR . '/demo-data';
    return array(
        array(
            'import_file_name'             => 'Master Mart — Pedal Exercise Bike Demo',
            'categories'                   => array( 'Landing Page', 'Fitness' ),
            'local_import_file'            => $demo_dir . '/content.xml',
            'local_import_json'            => array(
                array(
                    'file_path'     => $demo_dir . '/theme-options.json',
                    'option_name'   => 'mastermart_settings_group',
                ),
            ),
            'import_preview_image_url'     => MASTERMART_URI . '/screenshot.png',
            'import_notice'                => __( 'After import, your landing page and WooCommerce products will be fully configured.', 'mastermart' ),
            'preview_url'                  => 'https://github.com/ceoahasamhabib/mastermart',
        ),
    );
}
add_filter( 'ocdi/import_files', 'mastermart_ocdi_import_files' );
add_filter( 'pt-ocdi/import_files', 'mastermart_ocdi_import_files' );

/**
 * OCDI After Import Callback.
 */
function mastermart_ocdi_after_import_setup() {
    mastermart_execute_demo_import();
}
add_action( 'ocdi/after_import', 'mastermart_ocdi_after_import_setup' );
add_action( 'pt-ocdi/after_import', 'mastermart_ocdi_after_import_setup' );
