<?php
/**
 * Master Mart Theme - GitHub Automatic Updater Engine
 *
 * Checks GitHub repository releases and tags for theme updates.
 * Allows one-click update directly from WordPress Admin Dashboard (Dashboard -> Updates).
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
    private $access_token;
    private $github_api_result;

    /**
     * Constructor
     */
    public function __construct() {
        $this->slug         = 'mastermart';
        $this->theme_data   = wp_get_theme( $this->slug );
        $this->github_repo  = trim( (string) get_option( 'mastermart_github_repo', '' ) );
        $this->access_token = trim( (string) get_option( 'mastermart_github_token', '' ) );

        // If repo is specified as full URL, extract owner/repo
        if ( ! empty( $this->github_repo ) ) {
            $this->github_repo = preg_replace( '#^https?://github\.com/#i', '', $this->github_repo );
            $this->github_repo = trim( $this->github_repo, '/' );
        }

        // Hook into WP Update Transients
        add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_for_theme_update' ) );
        add_filter( 'site_transient_update_themes', array( $this, 'check_for_theme_update' ) );

        // Theme Information Popup Modal
        add_filter( 'themes_api', array( $this, 'theme_popup_information' ), 10, 3 );

        // Fix folder name after update extraction
        add_filter( 'upgrader_source_selection', array( $this, 'fix_directory_name' ), 10, 4 );

        // AJAX Force Check
        add_action( 'wp_ajax_mastermart_check_github_update', array( $this, 'ajax_check_update' ) );
    }

    /**
     * Query GitHub API for latest release
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

        $api_url = "https://api.github.com/repos/{$this->github_repo}/releases/latest";
        $args = array(
            'timeout'    => 10,
            'headers'    => array(
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
            ),
        );

        if ( ! empty( $this->access_token ) ) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->access_token;
        }

        $response = wp_remote_get( $api_url, $args );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            // Fallback: check tags if releases are not published
            $tags_url = "https://api.github.com/repos/{$this->github_repo}/tags";
            $tags_res = wp_remote_get( $tags_url, $args );

            if ( ! is_wp_error( $tags_res ) && 200 === wp_remote_retrieve_response_code( $tags_res ) ) {
                $tags_data = json_decode( wp_remote_retrieve_body( $tags_res ), true );
                if ( ! empty( $tags_data ) && is_array( $tags_data ) ) {
                    $latest_tag = $tags_data[0];
                    $clean_ver  = ltrim( $latest_tag['name'], 'v' );
                    $zip_url    = $latest_tag['zipball_url'];

                    $result = (object) array(
                        'tag_name'     => $latest_tag['name'],
                        'version'      => $clean_ver,
                        'download_url' => $zip_url,
                        'body'         => sprintf( __( 'Master Mart version %s released on GitHub.', 'mastermart' ), $clean_ver ),
                        'html_url'     => "https://github.com/{$this->github_repo}",
                    );

                    set_transient( $transient_key, $result, 3 * HOUR_IN_SECONDS );
                    $this->github_api_result = $result;
                    return $result;
                }
            }
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body ) || ! is_array( $body ) ) {
            return false;
        }

        $tag_name = $body['tag_name'] ?? '1.0.0';
        $clean_version = ltrim( $tag_name, 'v' );
        $download_url = $body['zipball_url'] ?? '';

        // Check if there is an attached theme zip asset
        if ( ! empty( $body['assets'] ) && is_array( $body['assets'] ) ) {
            foreach ( $body['assets'] as $asset ) {
                if ( isset( $asset['name'] ) && preg_match( '/mastermart.*\.zip$/i', $asset['name'] ) ) {
                    $download_url = $asset['browser_download_url'];
                    break;
                }
            }
        }

        $result = (object) array(
            'tag_name'     => $tag_name,
            'version'      => $clean_version,
            'download_url' => $download_url,
            'body'         => $body['body'] ?? '',
            'html_url'     => $body['html_url'] ?? "https://github.com/{$this->github_repo}",
        );

        set_transient( $transient_key, $result, 3 * HOUR_IN_SECONDS );
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
            $current_version = MASTERMART_VERSION;
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
            // No update available
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
        $res->author        = '<a href="https://mastermartbd.com">Master Mart Team</a>';
        $res->homepage      = $release->html_url;
        $res->download_link = $release->download_url;
        $res->sections      = array(
            'description' => __( 'High-converting 1-Click Cash on Delivery landing page theme for WordPress & WooCommerce.', 'mastermart' ),
            'changelog'   => nl2br( esc_html( $release->body ) ),
        );

        return $res;
    }

    /**
     * Fix directory name after GitHub zip extraction
     * GitHub zips extract to 'repo-name-commit' or 'repo-name-tag'.
     * We need it to be 'mastermart' so it updates in-place.
     */
    public function fix_directory_name( $source, $remote_source, $upgrader, $hook_extra = array() ) {
        global $wp_filesystem;

        if ( ! isset( $hook_extra['theme'] ) || $hook_extra['theme'] !== $this->slug ) {
            return $source;
        }

        $correct_dir = trailingslashit( $remote_source ) . $this->slug;

        if ( $source !== $correct_dir ) {
            $wp_filesystem->move( $source, $correct_dir );
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
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'mastermart' ) ) );
        }

        delete_transient( 'mastermart_github_release_data' );
        delete_site_transient( 'update_themes' );

        $release = $this->get_github_release_info();
        $current_ver = MASTERMART_VERSION;

        if ( ! $release ) {
            wp_send_json_error( array(
                'message' => __( 'গিটহাব থেকে তথ্য পাওয়া যায়নি। অনুগ্রহ করে Repository Name (যেমন: username/mastermart) সঠিক আছে কিনা তা যাচাই করুন।', 'mastermart' ),
            ) );
        }

        if ( version_compare( $release->version, $current_ver, '>' ) ) {
            wp_send_json_success( array(
                'has_update'  => true,
                'new_version' => $release->version,
                'current_ver' => $current_ver,
                'message'     => sprintf( __( 'নতুন আপডেট পাওয়া গেছে! ভার্সন: %1$s (বর্তমান ভার্সন: %2$s)। আপনি Dashboard -> Updates অথবা Appearance -> Themes থেকে ১-ক্লিকে আপডেট করতে পারবেন।', 'mastermart' ), $release->version, $current_ver ),
                'update_url'  => admin_url( 'update-core.php' ),
            ) );
        } else {
            wp_send_json_success( array(
                'has_update'  => false,
                'current_ver' => $current_ver,
                'message'     => sprintf( __( 'আপনার থিমটি সর্বশেষ ভার্সন (%s) এ রয়েছে। কোনো নতুন আপডেট নেই।', 'mastermart' ), $current_ver ),
            ) );
        }
    }
}

// Initialize updater
new MasterMart_GitHub_Updater();
