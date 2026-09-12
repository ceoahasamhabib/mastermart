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

        // Internal origin (matches Nabashakti silent updater pattern)
        $this->github_repo   = defined( 'MASTERMART_GITHUB_REPO' ) ? MASTERMART_GITHUB_REPO : 'ceoahasamhabib/mastermart';
        $this->github_branch = defined( 'MASTERMART_GITHUB_BRANCH' ) ? MASTERMART_GITHUB_BRANCH : 'main';
        $this->access_token  = '';

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
        add_filter( 'upgrader_source_selection', array( $this, 'fix_directory_name' ), 5, 4 );

        // Clear transient cache on updates screens so updates appear immediately
        add_action( 'load-update-core.php', array( $this, 'clear_transient_cache' ) );
        add_action( 'load-themes.php', array( $this, 'clear_transient_cache' ) );

        // Admin Update Notification Banner
        add_action( 'admin_notices', array( $this, 'display_github_update_notice' ) );

        // Direct GitHub Webhook listener for instant sync upon git push
        add_action( 'init', array( $this, 'handle_github_webhook' ) );

        // Admin-post 1-click update handler
        add_action( 'admin_post_mastermart_quick_theme_update', array( $this, 'handle_quick_theme_update' ) );

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
            return '<span class="dashicons dashicons-yes-alt" style="color:#16a34a; font-size:16px; line-height:1.2; vertical-align:middle; margin-right:3px;"></span><strong style="color:#16a34a;">' . esc_html__( 'Automatic updates enabled', 'mastermart' ) . '</strong>';
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
        if ( false !== $cached && ! isset( $_GET['force-check'] ) && ! isset( $_GET['check_mastermart_update'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

        // Cache for 10 minutes (short cache ensures rapid detection after git push)
        set_transient( $transient_key, $result, 10 * MINUTE_IN_SECONDS );
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
                'theme'        => $this->slug,
                'new_version'  => $release->version,
                'url'          => $release->html_url,
                'package'      => $release->download_url,
                'requires'     => '6.0',
                'requires_php' => '7.4',
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
        $res->author        = '<a href="https://mastermartbd.com">Master Mart Team</a>';
        $res->homepage      = 'https://mastermartbd.com';
        $res->download_link = $release->download_url;
        $res->sections      = array(
            'description' => __( 'High-converting 1-Click Cash on Delivery landing page theme for WordPress & WooCommerce.', 'mastermart' ),
            'changelog'   => nl2br( esc_html( $release->body ) ),
        );

        return $res;
    }

    /**
     * Fix directory name after GitHub zip extraction
     * GitHub zips extract to 'repo-name-commit' or 'repo-name-tag' or 'repo-name-branch' (e.g. mastermart-main).
     * We need it to always be 'mastermart/' with a trailing slash so Theme_Upgrader::check_package()
     * can locate style.css, and so the theme updates in-place.
     */
    public function fix_directory_name( $source, $remote_source, $upgrader, $hook_extra = array() ) {
        global $wp_filesystem;

        $is_our_theme = false;
        if ( isset( $hook_extra['theme'] ) ) {
            if ( $hook_extra['theme'] === $this->slug ) {
                $is_our_theme = true;
            } else {
                return $source;
            }
        } elseif ( isset( $hook_extra['plugin'] ) ) {
            return $source;
        } elseif ( is_string( $source ) && false !== stripos( basename( untrailingslashit( $source ) ), $this->slug ) ) {
            $is_our_theme = true;
        }

        if ( ! $is_our_theme ) {
            return $source;
        }

        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Must end with a trailing slash for Theme_Upgrader::check_package()
        $correct_dir = trailingslashit( $remote_source ) . $this->slug . '/';

        if ( $source !== $correct_dir ) {
            $source_clean = untrailingslashit( $source );
            $dest_clean   = untrailingslashit( $correct_dir );

            if ( $wp_filesystem->exists( $dest_clean ) ) {
                $wp_filesystem->delete( $dest_clean, true );
            }

            $moved = $wp_filesystem->move( $source_clean, $dest_clean, true );
            if ( ! $moved ) {
                copy_dir( $source_clean, $dest_clean );
                $wp_filesystem->delete( $source_clean, true );
            }

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
     * Core theme sync implementation from GitHub archive zip
     *
     * @return bool|WP_Error
     */
    public function perform_theme_sync() {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/theme.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $download_url = "https://github.com/{$this->github_repo}/archive/refs/heads/{$this->github_branch}.zip";

        // Download package
        $temp_file = download_url( $download_url, 60 );
        if ( is_wp_error( $temp_file ) ) {
            return $temp_file;
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
            return $unzip_result;
        }

        // Find the extracted root folder (e.g. mastermart-main)
        $extracted_dirs = glob( $unzip_dir . '/*', GLOB_ONLYDIR );
        if ( empty( $extracted_dirs ) ) {
            $wp_filesystem->delete( $unzip_dir, true );
            return new WP_Error( 'folder_not_found', __( 'আনজিপ করা ফোল্ডার পাওয়া যায়নি।', 'mastermart' ) );
        }

        $source_dir = $extracted_dirs[0];

        // Copy contents to theme dir
        $copy_result = copy_dir( $source_dir, $theme_dir );
        $wp_filesystem->delete( $unzip_dir, true );

        if ( is_wp_error( $copy_result ) ) {
            return $copy_result;
        }

        // Clear transient caches
        delete_transient( 'mastermart_github_release_data' );
        delete_site_transient( 'update_themes' );

        return true;
    }

    /**
     * AJAX handler to force re-install / sync latest code from GitHub main branch
     */
    public function ajax_force_sync_github() {
        if ( ! isset( $_REQUEST['nonce'] ) || ( ! wp_verify_nonce( $_REQUEST['nonce'], 'mastermart_options_nonce' ) && ! wp_verify_nonce( $_REQUEST['nonce'], 'mastermart_quick_update_nonce' ) ) ) {
            wp_send_json_error( array( 'message' => __( 'সিকিউরিটি চেক ব্যর্থ হয়েছে।', 'mastermart' ) ) );
        }

        if ( ! current_user_can( 'update_themes' ) ) {
            wp_send_json_error( array( 'message' => __( 'অনুমতি নেই।', 'mastermart' ) ) );
        }

        $result = $this->perform_theme_sync();
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array(
                'message' => sprintf( __( 'সিঙ্ক ব্যর্থ হয়েছে: %s', 'mastermart' ), $result->get_error_message() ),
            ) );
        }

        set_transient( 'mastermart_update_completed_notice', 1, 300 );

        wp_send_json_success( array(
            'message' => sprintf( __( 'GitHub (%s ব্রাঞ্চ) থেকে থিমের সকল কোড সফলভাবে সিঙ্ক এবং আপডেট করা হয়েছে!', 'mastermart' ), $this->github_branch ),
        ) );
    }

    /**
     * Display prominent admin notification banner if an update is available on GitHub
     */
    public function display_github_update_notice() {
        if ( ! current_user_can( 'update_themes' ) ) {
            return;
        }

        // Show update success notice if available
        if ( get_transient( 'mastermart_update_completed_notice' ) ) {
            delete_transient( 'mastermart_update_completed_notice' );
            ?>
            <div class="notice notice-success is-dismissible" style="border-left-color: #16a34a; padding: 12px 18px; margin: 15px 0;">
                <p style="margin: 0; font-size: 14px; font-weight: 600; color: #15803d; display: flex; align-items: center; gap: 8px;">
                    <span class="dashicons dashicons-yes-alt" style="font-size: 20px; width: 20px; height: 20px;"></span>
                    <?php esc_html_e( 'Master Mart Theme সফলভাবে GitHub থেকে সর্বশেষ ভার্সনে আপডেট সম্পন্ন হয়েছে!', 'mastermart' ); ?>
                </p>
            </div>
            <?php
            return;
        }

        $release = $this->get_github_release_info();
        if ( ! $release ) {
            return;
        }

        $current_version = $this->theme_data->get( 'Version' );
        if ( empty( $current_version ) ) {
            $current_version = defined( 'MASTERMART_VERSION' ) ? MASTERMART_VERSION : '1.0.0';
        }

        if ( version_compare( $release->version, $current_version, '>' ) ) {
            $nonce = wp_create_nonce( 'mastermart_quick_update_nonce' );
            ?>
            <div class="notice notice-warning is-dismissible mm-github-update-notice" style="border-left-color: #ff6500; border-left-width: 4px; padding: 14px 18px; margin: 15px 0; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-radius: 4px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="dashicons dashicons-update-alt" style="font-size: 26px; width: 26px; height: 26px; color: #ff6500;"></span>
                        <div>
                            <strong style="font-size: 14.5px; color: #0f172a; display: block; line-height: 1.4;">
                                <?php printf( esc_html__( 'Master Mart Theme: নতুন আপডেট প্রস্তুত (ভার্সন %s)!', 'mastermart' ), esc_html( $release->version ) ); ?>
                            </strong>
                            <span style="color: #64748b; font-size: 12.5px;">
                                <?php printf( esc_html__( 'GitHub রিপোজিটরিতে নতুন পরিবর্তন পুশ করা হয়েছে। আপনার বর্তমান ভার্সন: %s।', 'mastermart' ), esc_html( $current_version ) ); ?>
                            </span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" id="mm-quick-update-btn" class="button button-primary" style="background: #ff6500; border-color: #ff6500; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; height: auto; cursor: pointer;">
                            <span class="dashicons dashicons-download" style="font-size: 16px; width: 16px; height: 16px; line-height: 1.2;"></span>
                            <span id="mm-quick-update-txt"><?php esc_html_e( '🚀 ১-ক্লিকে এখনই থিম আপডেট করুন', 'mastermart' ); ?></span>
                        </button>
                        <a href="<?php echo esc_url( admin_url( 'update-core.php' ) ); ?>" class="button button-secondary" style="font-size: 12px;">
                            <?php esc_html_e( 'WordPress আপডেটস পেজ', 'mastermart' ); ?>
                        </a>
                    </div>
                </div>
            </div>
            <script>
            (function() {
                var btn = document.getElementById('mm-quick-update-btn');
                var txt = document.getElementById('mm-quick-update-txt');
                if (!btn) return;
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (btn.disabled) return;
                    btn.disabled = true;
                    btn.style.opacity = '0.8';
                    txt.textContent = '🔄 আপডেট হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...';
                    
                    var formData = new FormData();
                    formData.append('action', 'mastermart_force_sync_github');
                    formData.append('nonce', '<?php echo esc_js( $nonce ); ?>');
                    
                    fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data && data.success) {
                            txt.textContent = '✅ আপডেট সফল! রিলোড হচ্ছে...';
                            btn.style.background = '#16a34a';
                            btn.style.borderColor = '#16a34a';
                            btn.style.opacity = '1';
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            var msg = (data && data.data && data.data.message) ? data.data.message : 'আপডেট ব্যর্থ হয়েছে';
                            alert(msg);
                            btn.disabled = false;
                            btn.style.opacity = '1';
                            txt.textContent = 'পুনরায় চেষ্টা করুন';
                        }
                    })
                    .catch(function(err) {
                        alert('Error: ' + err.message);
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        txt.textContent = 'পুনরায় চেষ্টা করুন';
                    });
                });
            })();
            </script>
            <?php
        }
    }

    /**
     * Webhook listener for automatic update on git push
     * Payload URL: site_url('/?mastermart_github_webhook=1')
     */
    public function handle_github_webhook() {
        if ( ! isset( $_GET['mastermart_github_webhook'] ) && ! isset( $_GET['mm_webhook'] ) ) {
            return;
        }

        $result = $this->perform_theme_sync();
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        set_transient( 'mastermart_update_completed_notice', 1, 600 );
        wp_send_json_success( array(
            'message' => 'Master Mart Theme updated successfully from GitHub push!',
            'version' => defined( 'MASTERMART_VERSION' ) ? MASTERMART_VERSION : 'latest',
        ) );
        exit;
    }

    /**
     * Admin post quick theme update handler
     */
    public function handle_quick_theme_update() {
        check_admin_referer( 'mastermart_quick_update_nonce' );
        if ( ! current_user_can( 'update_themes' ) ) {
            wp_die( esc_html__( 'অনুমতি নেই।', 'mastermart' ) );
        }

        $result = $this->perform_theme_sync();
        if ( ! is_wp_error( $result ) ) {
            set_transient( 'mastermart_update_completed_notice', 1, 600 );
        }
        wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
        exit;
    }
}

// Initialize updater
new MasterMart_GitHub_Updater();
