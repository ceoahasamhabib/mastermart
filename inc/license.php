<?php
/**
 * Master Mart Theme License & Protection System
 *
 * Prevents unauthorized cloning and use of the Master Mart theme.
 * Requires license key '105694' or 'MASTERMART100' to activate theme features.
 * Shows admin activation status and full commercial licensing interface.
 *
 * @package MasterMart
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Master valid license keys
if ( ! defined( 'MASTERMART_LICENSE_KEY' ) ) {
    define( 'MASTERMART_LICENSE_KEY', '105694' );
}

/**
 * Check if the theme is properly licensed and active.
 */
function mastermart_is_licensed() {
    $status = get_option( 'mastermart_license_status', '' );
    $key    = trim( (string) get_option( 'mastermart_license_key', '' ) );

    // Auto-active on localhost if not set
    if ( empty( $status ) && in_array( $_SERVER['REMOTE_ADDR'] ?? '', array( '127.0.0.1', '::1' ), true ) ) {
        update_option( 'mastermart_license_status', 'valid' );
        update_option( 'mastermart_license_key', MASTERMART_LICENSE_KEY );
        update_option( 'mastermart_license_activated_at', current_time( 'mysql' ) );
        return true;
    }

    return ( 'valid' === $status && ( MASTERMART_LICENSE_KEY === $key || 'MASTERMART100' === $key ) );
}

/**
 * Register Theme License page under Appearance menu.
 */
function mastermart_register_license_menu() {
    add_theme_page(
        __( 'Theme License', 'mastermart' ),
        __( 'Theme License (লাইসেন্স)', 'mastermart' ),
        'manage_options',
        'mastermart-license',
        'mastermart_render_license_page'
    );
}
add_action( 'admin_menu', 'mastermart_register_license_menu' );

/**
 * Render License Management Admin Page.
 */
function mastermart_render_license_page() {
    $is_licensed  = mastermart_is_licensed();
    $saved_key    = get_option( 'mastermart_license_key', '' );
    $activated_at = get_option( 'mastermart_license_activated_at', '' );
    ?>
    <div class="wrap" style="max-width: 820px; margin: 30px auto; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
        <div style="background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; overflow: hidden;">
            
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #0B192C 0%, #1E3E62 100%); padding: 32px 36px; color: #ffffff; border-bottom: 3px solid #FF6500;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                    <div>
                        <h1 style="color: #ffffff; font-size: 1.8rem; font-weight: 800; margin: 0 0 6px 0; display: flex; align-items: center; gap: 10px;">
                            🛒 Master Mart লাইসেন্স অ্যাক্টিভেশন
                        </h1>
                        <p style="color: #94A3B8; font-size: 0.95rem; margin: 0;">
                            আপনার থিমের সমস্ত প্রিমিয়াম ফিচার ও নিয়মিত আপডেট সক্রিয় রাখতে লাইসেন্স কি প্রবেশ করান।
                        </p>
                    </div>
                    <div>
                        <?php if ( $is_licensed ) : ?>
                            <span style="background: #dcfce7; color: #15803d; font-weight: 800; padding: 8px 16px; border-radius: 30px; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px;">
                                ✓ লাইসেন্স সক্রিয় (ACTIVE)
                            </span>
                        <?php else : ?>
                            <span style="background: #fee2e2; color: #b91c1c; font-weight: 800; padding: 8px 16px; border-radius: 30px; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 6px;">
                                ✕ লাইসেন্স অসক্রিয় (INACTIVE)
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div style="padding: 36px;">
                <div id="mastermart-license-ajax-msg" style="display: none; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 0.95rem; font-weight: 600;"></div>

                <?php if ( $is_licensed ) : ?>
                    <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 12px; padding: 24px; margin-bottom: 28px;">
                        <h3 style="color: #166534; margin: 0 0 10px 0; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                            🎉 অভিনন্দন! Master Mart থিম সফলভাবে সক্রিয় রয়েছে
                        </h3>
                        <p style="color: #15803d; font-size: 0.95rem; margin: 0 0 14px 0; line-height: 1.6;">
                            আপনার লাইসেন্স অনুমোদিত। সকল প্রিমিয়াম ফিচার—১-ক্লিক এক্সপ্রেস ক্যাশ অন ডেলিভারি, এলিমেন্টর ড্র্যাগ অ্যান্ড ড্রপ এডিটর, গিটহাব অটো-আপডেটার এবং ফুল ডাটালেয়ার সম্পূর্ণ আনলক করা রয়েছে।
                        </p>
                        <div style="font-size: 0.85rem; color: #475569; display: flex; gap: 20px; flex-wrap: wrap;">
                            <span><strong>লাইসেন্স কি:</strong> ************ <span style="background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 0.8rem;">সুরক্ষিত</span></span>
                            <?php if ( $activated_at ) : ?>
                                <span><strong>অ্যাক্টিভেশনের সময়:</strong> <?php echo esc_html( $activated_at ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; justify-content: flex-end;">
                        <button type="button" class="button" id="mastermart-deactivate-btn" onclick="mastermartDeactivateLicense()" style="color: #dc2626; border-color: #fca5a5; padding: 8px 18px; border-radius: 8px; font-weight: 600;">
                            লাইসেন্স নিষ্ক্রিয় করুন (Deactivate)
                        </button>
                    </div>

                <?php else : ?>

                    <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 12px; padding: 20px 24px; margin-bottom: 28px;">
                        <h3 style="color: #991b1b; margin: 0 0 8px 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                            ⚠️ থিম লাইসেন্স অ্যাক্টিভেশন প্রয়োজন
                        </h3>
                        <p style="color: #b91c1c; font-size: 0.92rem; margin: 0; line-height: 1.6;">
                            অননুমোদিত কপি প্রতিরোধে Master Mart থিমটি সুরক্ষিত। ডিফল্ট অ্যাক্টিভেশন কি: <code>105694</code>।
                        </p>
                    </div>

                    <form id="mastermart-license-form" onsubmit="mastermartSubmitLicense(event)">
                        <div style="margin-bottom: 24px;">
                            <label for="license_key_input" style="display: block; font-weight: 700; color: #1e293b; margin-bottom: 8px; font-size: 1rem;">
                                লাইসেন্স কি (License Key) প্রবেশ করান *
                            </label>
                            <input 
                                type="text" 
                                id="license_key_input" 
                                name="license_key" 
                                value="105694" 
                                style="width: 100%; max-width: 450px; padding: 12px 16px; border: 2px solid #cbd5e1; border-radius: 10px; font-size: 1.1rem; letter-spacing: 2px; font-weight: 700; outline: none;"
                                required
                            >
                            <p style="font-size: 0.85rem; color: #64748b; margin: 8px 0 0 0;">
                                আপনার অফিশিয়াল লাইসেন্স কোডটি লিখুন এবং "সক্রিয় করুন" বাটনে ক্লিক করুন (যেমন: 105694)।
                            </p>
                        </div>

                        <div style="display: flex; align-items: center; gap: 14px;">
                            <button 
                                type="submit" 
                                id="mastermart-activate-submit-btn" 
                                class="button button-primary" 
                                style="background: #FF6500; border-color: #E05A00; padding: 10px 24px; font-size: 1rem; font-weight: 700; border-radius: 10px; height: auto;"
                            >
                                🛒 থিম সক্রিয় করুন (Activate Theme)
                            </button>
                        </div>
                    </form>

                <?php endif; ?>

            </div>

            <!-- Footer Feature List -->
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 36px;">
                <h4 style="margin: 0 0 12px 0; color: #334155; font-size: 0.95rem; font-weight: 700;">
                    🛡️ লাইসেন্স সক্রিয় হলে যেসব সুবিধা আনলক হয়:
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; font-size: 0.85rem; color: #475569;">
                    <div>✓ ইনস্ট্যান্ট ১-ক্লিক ক্যাশ অন ডেলিভারি চেকআউট</div>
                    <div>✓ ১০০% এলিমেন্টর ড্র্যাগ অ্যান্ড ড্রপ এডিটেবল ল্যান্ডিং পেজ</div>
                    <div>✓ গিটহাব অটোমেটিক থিম আপডেটার ইঞ্জিন</div>
                    <div>✓ ফুল ইকমার্স ডাটালেয়ার (GTM, Pixel, CAPI, GA4)</div>
                    <div>✓ মোবাইল অ্যাপের মতো রেস্পন্সিভ আর্কিটেকচার</div>
                    <div>✓ সুপারফাস্ট লাইটস্পিড ক্যাশিং কম্প্যাটিবিলিটি</div>
                </div>
            </div>

        </div>
    </div>

    <script>
    function mastermartSubmitLicense(e) {
        e.preventDefault();
        var keyInput = document.getElementById('license_key_input');
        var msgBox   = document.getElementById('mastermart-license-ajax-msg');
        var btn      = document.getElementById('mastermart-activate-submit-btn');

        if (!keyInput || !keyInput.value.trim()) return;

        btn.disabled = true;
        btn.textContent = 'যাচাই করা হচ্ছে...';

        var formData = new FormData();
        formData.append('action', 'mastermart_activate_license');
        formData.append('license_key', keyInput.value.trim());
        formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'mastermart_license_nonce' ) ); ?>');

        fetch(ajaxurl, { method: 'POST', body: formData })
        .then(function(r){ return r.json(); })
        .then(function(res){
            msgBox.style.display = 'block';
            if (res.success) {
                msgBox.style.background = '#f0fdf4';
                msgBox.style.border = '1px solid #86efac';
                msgBox.style.color = '#166534';
                msgBox.innerHTML = '✓ ' + (res.data.message || 'লাইসেন্স সফলভাবে সক্রিয় হয়েছে!');
                setTimeout(function(){ window.location.reload(); }, 1200);
            } else {
                btn.disabled = false;
                btn.textContent = '🛒 থিম সক্রিয় করুন (Activate Theme)';
                msgBox.style.background = '#fef2f2';
                msgBox.style.border = '1px solid #fecaca';
                msgBox.style.color = '#991b1b';
                msgBox.innerHTML = '✕ ' + (res.data.message || 'সঠিক লাইসেন্স কি দিন।');
            }
        })
        .catch(function(){
            btn.disabled = false;
            btn.textContent = '🛒 থিম সক্রিয় করুন (Activate Theme)';
            msgBox.style.display = 'block';
            msgBox.style.background = '#fef2f2';
            msgBox.style.color = '#991b1b';
            msgBox.innerHTML = 'সার্ভার রেসপন্স করছে না। আবার চেষ্টা করুন।';
        });
    }

    function mastermartDeactivateLicense() {
        if (!confirm('আপনি কি নিশ্চিত যে লাইসেন্স নিষ্ক্রিয় করতে চান?')) return;
        var btn = document.getElementById('mastermart-deactivate-btn');
        if (btn) btn.disabled = true;

        var formData = new FormData();
        formData.append('action', 'mastermart_deactivate_license');
        formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'mastermart_license_nonce' ) ); ?>');

        fetch(ajaxurl, { method: 'POST', body: formData })
        .then(function(r){ return r.json(); })
        .then(function(){ window.location.reload(); });
    }
    </script>
    <?php
}

/**
 * AJAX License Activation Handler.
 */
add_action( 'wp_ajax_mastermart_activate_license', 'mastermart_ajax_activate_license' );
function mastermart_ajax_activate_license() {
    check_ajax_referer( 'mastermart_license_nonce', 'nonce' );

    $key = isset( $_POST['license_key'] ) ? sanitize_text_field( wp_unslash( $_POST['license_key'] ) ) : '';

    if ( MASTERMART_LICENSE_KEY === $key || 'MASTERMART100' === $key ) {
        update_option( 'mastermart_license_status', 'valid' );
        update_option( 'mastermart_license_key', $key );
        update_option( 'mastermart_license_activated_at', current_time( 'mysql' ) );

        wp_send_json_success( array(
            'message' => esc_html__( 'লাইসেন্স সফলভাবে সক্রিয় হয়েছে! সকল প্রিমিয়াম ফিচার আনলক করা হয়েছে।', 'mastermart' ),
        ) );
    } else {
        wp_send_json_error( array(
            'message' => esc_html__( 'ভুল লাইসেন্স কি! সঠিক লাইসেন্স কোড (যেমন: 105694) প্রবেশ করান।', 'mastermart' ),
        ) );
    }
}

/**
 * AJAX License Deactivation Handler.
 */
add_action( 'wp_ajax_mastermart_deactivate_license', 'mastermart_ajax_deactivate_license' );
function mastermart_ajax_deactivate_license() {
    check_ajax_referer( 'mastermart_license_nonce', 'nonce' );

    delete_option( 'mastermart_license_status' );
    delete_option( 'mastermart_license_key' );
    delete_option( 'mastermart_license_activated_at' );

    wp_send_json_success( array( 'message' => esc_html__( 'লাইসেন্স সফলভাবে নিষ্ক্রিয় করা হয়েছে।', 'mastermart' ) ) );
}
