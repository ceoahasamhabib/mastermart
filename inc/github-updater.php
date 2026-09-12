<?php
/**
 * Master Mart Theme - GitHub Automatic Updater Engine
 *
 * 100% Zero-Configuration GitHub Updater:
 * - Pre-configured default repository: 'ceoahasamhabib/mastermart' (branch: main)
 * - Automatic background WordPress theme auto-updates (auto_update_theme hook)
 * - Multi-channel update detection:
 *     1. Raw style.css from GitHub main branch (fast, zero rate limits, instant)
 *     2. GitHub Releases API (/repos/{owner}/{repo}/releases/latest)
 *     3. GitHub Tags API (/repos/{owner}/{repo}/tags)
 * - Automatic folder renaming: preserves directory name 'mastermart' on zip extraction
 * - Instant update check: clears transient cache on update-core.php and themes.php
 * - 1-Click Force Sync / Re-install from GitHub directly from WordPress Admin
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MasterMart_GitHub_Updater {

    private $slug;
    private $theme_data;
    private $github_repo;
    private $github_branch;
    private $access_token;
    private $github_api_result;

    /**
     * Constructor
     */
    public function __construct() {
        $this->slug       = 'mastermart';
        $this->theme_data = wp_get_theme( $this->slug );

        // 100% Zero-Config: Always pre-configured to official repository & main branch
        $default_repo  = defined( 'MASTERMART_GITHUB_REPO' ) ? MASTERMART_GITHUB_REPO : 'ceoahasamhabib/mastermart';
        $saved_repo    = trim( (string) get_option( 'mastermart_github_repo', '' ) );
        $raw_repo      = ! empty( $saved_repo ) ? $saved_repo : $default_repo;
        $this->github_repo = trim( preg_replace( '#^https?://github\.com/#i', '', $raw_repo ), '/' );

        $default_branch = defined( 'MASTERMART_GITHUB_BRANCH' ) ? MASTERMART_GITHUB_BRANCH : 'main';
        $saved_branch   = trim( (string) get_option( 'mastermart_github_branch', '' ) );
        $this->github_branch = ! empty( $saved_branch ) ? $saved_branch : $default_branch;

        $this->access_token = trim( (string) get_option( 'mastermart_github_token', '' ) );

        // Hook into WP Update Transients
        add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_for_theme_update' ) );
        add_filter( 'site_transient_update_themes', array( $this, 'check_for_theme_update' ) );

        // Enable 100% automatic background WordPress theme updates for Master Mart
        add_filter( 'auto_update_theme', array( $this, 'enable_auto_update' ), 99, 2 );

        // Custom label in Appearance -> Themes indicating auto-update is active
        add_filter( 'theme_auto_update_setting_html', array( $this, 'custom_auto_update_label' ), 10, 3 );

        // Theme Information Popup Modal
        add_filter( 'themes_api', array( $this, 'theme_popup_information' ), 10, 3 );

        // Fix folder name after update extraction (mastermart-main -> mastermart)
        add_filter( 'upgrader_source_selection', array( $this, 'fix_directory_name' ), 10, 4 );

        // Clear transient cache on updates screens so updates appear immediately
        add_action( 'load-update-core.php', array( $this, 'clear_transient_cache' ) );
        add_action( 'load-themes.php', array( $this, 'clear_transient_cache' ) );

        // AJAX Handlers: Manual Check and 1-Click Force Sync
        add_action( 'wp_ajax_mastermart_check_github_update', array( $this, 'ajax_check_update' ) );
        add_action( 'wp_ajax_mastermart_force_sync_github', array( $this, 'ajax_force_sync_github' ) );
    }

    /**
     * Enable native WordPress auto-updates for Master Mart
     */
    public function enable_auto_update( $update, $item ) {
        if ( isset( $item->theme ) && $this->slug === $item->theme ) {
            return true;
        }
        return $update;
    }

    /**
     * Custom label in Appearance -> Themes
     */
    public function custom_auto_update_label( $html, $theme_key, $theme ) {
        if ( $this->slug === $theme_key ) {
            return '<span class="dashicons dashicons-yes-alt" style="color:#16a34a; font-size:16px; line-height:1.2; vertical-align:middle; margin-right:3px;"></span><strong style="color:#16a34a;">' . esc_html__( 'GitHub Auto-Update Active', 'mastermart' ) . '</strong> <span style="font-size:12px; color:#64748B;">(' . esc_html( $this->github_repo ) . ')</span>';
        }
        return $html;
    }

    /**
     * Clear transient cache
     */
    public function clear_transient_cache() {
        if ( current_user_can( 'update_themes' ) ) {
            delete_transient( 'mastermart_github_release_data' );
        }
    }

    /**
     * Query GitHub for latest release / version information
     */
    private function get_github_release_info() {
        if ( ! empty( $this->github_api_result ) ) {
            return $this->github_api_result;
        }

        if ( empty( $this->github_repo ) ) {
            return false;
        }

        $transient_key = 'mastermart_github_release_data';
        $cached = get_transient( $transient_key );
        if ( false !== $cached && ! isset( $_GET['force-check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $this->github_api_result = $cached;
            return $cached;
        }

        $default_zip = "https://github.com/{$this->github_repo}/archive/refs/heads/{$this->github_branch}.zip";
        $current_version = $this->theme_data->get( 'Version' );
        if ( empty( $current_version ) ) {
            $current_version = defined( 'MASTERMART_VERSION' ) ? MASTERMART_VERSION : '1.0.0';
        }

        $found_version = null;
        $download_url  = $default_zip;
        $changelog     = '';
        $release_tag   = '';

        $req_args = array(
            'timeout'    => 10,
            'headers'    => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
            ),
        );

        if ( ! empty( $this->access_token ) ) {
            $req_args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
        }

        // Method 1: Check raw style.css on main branch (Instant, fast, zero rate limit!)
        $raw_style_url = "https://raw.githubusercontent.com/{$this->github_repo}/{$this->github_branch}/style.css";
        $style_res     = wp_remote_get( $raw_style_url, array( 'timeout' => 8 ) );

        if ( ! is_wp_error( $style_res ) && 200 === wp_remote_retrieve_response_code( $style_res ) ) {
            $style_content = wp_remote_retrieve_body( $style_res );
            if ( preg_match( '/^[ \t\/*#@]*Version:\s*([^\r\n]+)/mi', $style_content, $matches ) ) {
                $style_ver = trim( $matches[1] );
                if ( ! empty( $style_ver ) ) {
                    $found_version = $style_ver;
                    $download_url  = $default_zip;
                    $changelog     = sprintf( __( 'Latest updates on GitHub (%s branch).', 'mastermart' ), $this->github_branch );
                }
            }
        }

        // Method 2: Check GitHub Releases API for tagged releases
        $api_url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
        $response = wp_remote_get( $api_url, $req_args );

        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! empty( $body ) && is_array( $body ) ) {
                $tag_name      = $body['tag_name'] ?? '';
                $clean_version = ltrim( $tag_name, 'v' );

                if ( empty( $found_version ) || version_compare( $clean_version, $found_version, '>=' ) ) {
                    $found_version = $clean_version;
                    $release_tag   = $tag_name;
                    $changelog     = $body['body'] ?? $changelog;
                    $download_url  = $body['zipball_url'] ?? $default_zip;

                    // Check if an explicit theme zip asset was uploaded to release
                    if ( ! empty( $body['assets'] ) && is_array( $body['assets'] ) ) {
                        foreach ( $body['assets'] as $asset ) {
                            if ( isset( $asset['name'] ) && preg_match( '/mastermart.*\.zip$/i', $asset['name'] ) ) {
                                $download_url = $asset['browser_download_url'];
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Method 3: Fallback check tags
        if ( empty( $found_version ) ) {
            $tags_url = "https://api.github.com/repos/{$this->github_repo}/tags";
            $tags_res = wp_remote_get( $tags_url, $req_args );

            if ( ! is_wp_error( $tags_res ) && 200 === wp_remote_retrieve_response_code( $tags_res ) ) {
                $tags_data = json_decode( wp_remote_retrieve_body( $tags_res ), true );
                if ( ! empty( $tags_data ) && is_array( $tags_data ) ) {
                    $latest_tag    = $tags_data[0];
                    $found_version = ltrim( $latest_tag['name'], 'v' );
                    $release_tag   = $latest_tag['name'];
                    $download_url  = $latest_tag['zipball_url'];
                    $changelog     = sprintf( __( 'GitHub release tag %s.', 'mastermart' ), $latest_tag['name'] );
                }
            }
        }

        if ( empty( $found_version ) ) {
            $found_version = $current_version;
        }

        $result = (object) array(
            'tag_name'     => ! empty( $release_tag ) ? $release_tag : 'v' . $found_version,
            'version'      => $found_version,
            'download_url' => $download_url,
            'body'         => ! empty( $changelog ) ? $changelog : sprintf( __( 'Master Mart version %s from GitHub.', 'mastermart' ), $found_version ),
            'html_url'     => "https://github.com/{$this->github_repo}",
        );

        // Cache for 30 minutes
        set_transient( $transient_key, $result, 30 * MINUTE_IN_SECONDS );
        $this->github_api_result = $result;
        return $result;
    }

    /**
     * Check for theme update in WordPress update transient
     */
    public function check_for_theme_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $release = $this->get_github_release_info();
        if ( ! $release ) {
            return $transient;
        }

        $current_version = $this->theme_data->get( 'Version' );
        if ( empty( $current_version ) ) {
            $current_version = defined( 'MASTERMART_VERSION' ) ? MASTERMART_VERSION : '1.0.0';
        }

        if ( version_compare( $release->version, $current_version, '>' ) ) {
            $update_data = array(
                'theme'       => $this->slug,
                'new_version' => $release->version,
                'url'         => $release->html_url,
                'package'     => $release->download_url,
            );

            $transient->response[ $this->slug ] = $update_data;
        } else {
            // Up to date
            unset( $transient->response[ $this->slug ] );
        }

        return $transient;
    }

    /**
     * Theme Details Modal in Appearance -> Themes
     */
    public function theme_popup_information( $result, $action, $args ) {
        if ( 'theme_information' !== $action || ! isset( $args->slug ) || $this->slug !== $args->slug ) {
            return $result;
        }

        $release = $this->get_github_release_info();
        if ( ! $release ) {
            return $result;
        }

        $res = new stdClass();
        $res->name          = 'Master Mart';
        $res->slug          = $this->slug;
        $res->version       = $release->version;
        $res->author        = '<a href="https://github.com/' . esc_attr( $this->github_repo ) . '">Master Mart Team</a>';
        $res->homepage      = $release->html_url;
        $res->download_link = $release->download_url;
        $res->sections      = array(
            'description' => __( 'High-converting 1-Click Cash on Delivery landing page theme for WordPress & WooCommerce with Automatic GitHub Updates.', 'mastermart' ),
            'changelog'   => nl2br( esc_html( $release->body ) ),
        );

        return $res;
    }

    /**
     * Fix directory name after GitHub zip extraction
     * GitHub zips extract to 'repo-name-commit' or 'repo-name-tag' or 'repo-name-branch'.
     * We need it to always be 'mastermart' so it updates in-place.
     */
    public function fix_directory_name( $source, $remote_source, $upgrader, $hook_extra = array() ) {
        global $wp_filesystem;

        if ( ! isset( $hook_extra['theme'] ) || $hook_extra['theme'] !== $this->slug ) {
            return $source;
        }

        $correct_dir = trailingslashit( $remote_source ) . $this->slug;

        if ( $source !== $correct_dir ) {
            $wp_filesystem->move( $source, $correct_dir, true );
            return $correct_dir;
        }

        return $source;
    }

    /**
     * AJAX handler to manually check for updates
     */
    public function ajax_check_update() {
        check_ajax_referer( 'mastermart_options_nonce', 'nonce' );

        if ( ! current_user_can( 'update_themes' ) ) {
            wp_send_json_error( array( 'message' => __( 'অনুমতি নেই।', 'mastermart' ) ) );
        }

        delete_transient( 'mastermart_github_release_data' );
        delete_site_transient( 'update_themes' );

        $release     = $this->get_github_release_info();
        $current_ver = defined( 'MASTERMART_VERSION' ) ? MASTERMART_VERSION : '1.0.0';

        if ( ! $release ) {
            wp_send_json_error( array(
                'message' => sprintf( __( 'GitHub (%s) থেকে তথ্য লোড করা যায়নি। ইন্টারনেট সংযোগ অথবা রিপোজিটরি নাম চেক করুন।', 'mastermart' ), $this->github_repo ),
            ) );
        }

        if ( version_compare( $release->version, $current_ver, '>' ) ) {
            wp_send_json_success( array(
                'has_update'  => true,
                'new_version' => $release->version,
                'current_ver' => $current_ver,
                'message'     => sprintf( __( 'নতুন আপডেট পাওয়া গেছে! ভার্সন: %1$s (বর্তমান ভার্সন: %2$s)। আপনি Dashboard -> Updates অথবা Appearance -> Themes থেকে ১-ক্লিকে আপডেট করতে পারেন।', 'mastermart' ), $release->version, $current_ver ),
                'update_url'  => admin_url( 'update-core.php' ),
            ) );
        } else {
            wp_send_json_success( array(
                'has_update'  => false,
                'current_ver' => $current_ver,
                'message'     => sprintf( __( 'আপনার থিমটি সর্বশেষ ভার্সন (%s) এ রয়েছে। GitHub রিপোজিটরি (%s) এর সাথে সম্পূর্ণ সিঙ্কড।', 'mastermart' ), $current_ver, $this->github_repo ),
            ) );
        }
    }

    /**
     * AJAX handler to force re-install / sync latest code from GitHub main branch
     */
    public function ajax_force_sync_github() {
        check_ajax_referer( 'mastermart_options_nonce', 'nonce' );

        if ( ! current_user_can( 'update_themes' ) ) {
            wp_send_json_error( array( 'message' => __( 'অনুমতি নেই।', 'mastermart' ) ) );
        }

        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/theme.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $download_url = "https://github.com/{$this->github_repo}/archive/refs/heads/{$this->github_branch}.zip";

        // Download package
        $temp_file = download_url( $download_url, 60 );
        if ( is_wp_error( $temp_file ) ) {
            wp_send_json_error( array(
                'message' => sprintf( __( 'GitHub থেকে ফাইল ডাউনলোড ব্যর্থ হয়েছে: %s', 'mastermart' ), $temp_file->get_error_message() ),
            ) );
        }

        // Initialize WordPress Filesystem
        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $theme_dir = get_theme_root() . '/' . $this->slug;
        $unzip_dir = WP_CONTENT_DIR . '/upgrade/mastermart-temp-' . time();

        $wp_filesystem->mkdir( $unzip_dir );
        $unzip_result = unzip_file( $temp_file, $unzip_dir );
        @unlink( $temp_file );

        if ( is_wp_error( $unzip_result ) ) {
            $wp_filesystem->delete( $unzip_dir, true );
            wp_send_json_error( array(
                'message' => sprintf( __( 'জিপ ফাইল আনজিপ করতে সমস্যা হয়েছে: %s', 'mastermart' ), $unzip_result->get_error_message() ),
            ) );
        }

        // Find the extracted root folder (e.g. mastermart-main)
        $extracted_dirs = glob( $unzip_dir . '/*', GLOB_ONLYDIR );
        if ( empty( $extracted_dirs ) ) {
            $wp_filesystem->delete( $unzip_dir, true );
            wp_send_json_error( array( 'message' => __( 'আনজিপ করা ফোল্ডার পাওয়া যায়নি।', 'mastermart' ) ) );
        }

        $source_dir = $extracted_dirs[0];

        // Copy / Move contents to theme dir
        $copy_result = copy_dir( $source_dir, $theme_dir );
        $wp_filesystem->delete( $unzip_dir, true );

        if ( is_wp_error( $copy_result ) ) {
            wp_send_json_error( array(
                'message' => sprintf( __( 'ফাইল কপি করতে সমস্যা হয়েছে: %s', 'mastermart' ), $copy_result->get_error_message() ),
            ) );
        }

        delete_transient( 'mastermart_github_release_data' );
        delete_site_transient( 'update_themes' );

        wp_send_json_success( array(
            'message' => sprintf( __( 'GitHub (%s ব্রাঞ্চ) থেকে থিমের সকল কোড সফলভাবে সিঙ্ক এবং আপডেট করা হয়েছে!', 'mastermart' ), $this->github_branch ),
        ) );
    }
}

// Initialize updater
new MasterMart_GitHub_Updater();
