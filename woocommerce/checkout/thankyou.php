<?php
/**
 * Master Mart Custom Thank You / Order Received Page
 *
 * @package MasterMart
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$hotline = mastermart_get_phone();
$logo_url = mastermart_get_logo_url();
?>
<div class="mm-thankyou-wrapper" style="max-width: 780px; margin: 40px auto; padding: 0 16px; font-family: var(--mm-font-base);">
    <?php if ( $order ) : ?>
        <?php if ( $order->has_status( 'failed' ) ) : ?>
            <div style="background: #FEF2F2; border: 2px solid #FCA5A5; border-radius: 16px; padding: 30px; text-align: center;">
                <h2 style="color: #DC2626; margin: 0 0 10px;">অর্ডার সম্পন্ন হতে সমস্যা হয়েছে</h2>
                <p style="color: #7F1D1D; margin: 0 0 20px;">অনুগ্রহ করে সরাসরি আমাদের কাস্টমার সাপোর্টে কল করুন।</p>
                <a href="tel:<?php echo esc_attr( $hotline ); ?>" style="display: inline-block; background: #DC2626; color: #fff; padding: 12px 24px; border-radius: 9999px; text-decoration: none; font-weight: 700;">📞 কল করুন: <?php echo esc_html( $hotline ); ?></a>
            </div>
        <?php else : ?>

            <!-- Success Card -->
            <div style="background: #FFFFFF; border: 2px solid #E2E8F0; border-radius: 20px; box-shadow: 0 12px 35px rgba(11, 25, 44, 0.08); overflow: hidden;">
                <!-- Header -->
                <div style="background: linear-gradient(135deg, #0B192C 0%, #1E3E62 100%); color: #FFFFFF; padding: 36px 24px; text-align: center; border-bottom: 3px solid #FF6500;">
                    <div style="width: 64px; height: 64px; background: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h1 style="font-size: 26px; font-weight: 900; margin: 0 0 8px; color: #ffffff;">অভিনন্দন! আপনার অর্ডার সফল হয়েছে</h1>
                    <p style="color: #94A3B8; font-size: 15px; margin: 0;">আমাদের কাস্টমার কেয়ার প্রতিনিধি শীঘ্রই আপনার সাথে ফোনে যোগাযোগ করবেন।</p>
                </div>

                <!-- Order Meta Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; padding: 20px 24px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
                    <div>
                        <span style="font-size: 12px; color: #64748B; display: block; font-weight: 600;">অর্ডার নম্বর</span>
                        <strong style="font-size: 15px; color: #0B192C;">#<?php echo esc_html( $order->get_order_number() ); ?></strong>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #64748B; display: block; font-weight: 600;">তারিখ</span>
                        <strong style="font-size: 15px; color: #0B192C;"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #64748B; display: block; font-weight: 600;">পেমেন্ট পদ্ধতি</span>
                        <strong style="font-size: 15px; color: #0B192C;"><?php echo esc_html( $order->get_payment_method_title() ); ?></strong>
                    </div>
                    <div>
                        <span style="font-size: 12px; color: #64748B; display: block; font-weight: 600;">সর্বমোট টাকা (COD)</span>
                        <strong style="font-size: 17px; color: #FF6500;"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
                    </div>
                </div>

                <!-- Receipt Body -->
                <div style="padding: 26px 24px;">
                    <h3 style="font-size: 17px; font-weight: 800; color: #0B192C; margin: 0 0 16px; border-bottom: 1.5px solid #E2E8F0; padding-bottom: 8px;">অর্ডারের বিবরণ</h3>
                    
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
                        <tbody>
                            <?php foreach ( $order->get_items() as $item_id => $item ) : ?>
                                <tr style="border-bottom: 1px solid #F1F5F9;">
                                    <td style="padding: 12px 0; font-size: 14.5px; color: #1E293B;">
                                        <strong><?php echo esc_html( $item->get_name() ); ?></strong> × <?php echo esc_html( $item->get_quantity() ); ?>
                                    </td>
                                    <td style="padding: 12px 0; text-align: right; font-weight: 700; color: #0B192C;">
                                        <?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php foreach ( $order->get_order_item_totals() as $key => $total ) : ?>
                                <tr style="border-bottom: 1px solid #F1F5F9;">
                                    <td style="padding: 10px 0; font-size: 14px; color: #64748B;"><?php echo esc_html( $total['label'] ); ?></td>
                                    <td style="padding: 10px 0; text-align: right; font-weight: 700; color: <?php echo 'order_total' === $key ? '#FF6500; font-size: 18px;' : '#0B192C;'; ?>">
                                        <?php echo wp_kses_post( $total['value'] ); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Customer Delivery Information -->
                    <div style="background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                        <h4 style="margin: 0 0 10px; font-size: 15px; font-weight: 800; color: #0B192C;">ডেলিভারি ঠিকানা</h4>
                        <p style="margin: 0 0 4px; font-size: 14px; color: #334155;"><strong>নাম:</strong> <?php echo esc_html( $order->get_formatted_billing_full_name() ); ?></p>
                        <p style="margin: 0 0 4px; font-size: 14px; color: #334155;"><strong>মোবাইল:</strong> <?php echo esc_html( $order->get_billing_phone() ); ?></p>
                        <p style="margin: 0; font-size: 14px; color: #334155;"><strong>ঠিকানা:</strong> <?php echo esc_html( $order->get_billing_address_1() ); ?></p>
                    </div>

                    <!-- Instructions Notice -->
                    <div style="background: #FFF7ED; border-left: 4px solid #FF6500; padding: 14px 16px; border-radius: 0 8px 8px 0; margin-bottom: 24px;">
                        <p style="margin: 0; font-size: 13.5px; color: #9A3412; line-height: 1.5;">
                            📌 <strong>জরুরি তথ্য:</strong> পার্সেল পাঠানোর পূর্বে আমাদের অফিস থেকে কল করে ঠিকানা নিশ্চিত করা হবে। অনুগ্রহ করে আপনার মোবাইল সচল রাখুন।
                        </p>
                    </div>

                    <div style="text-align: center;">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display: inline-block; background: #0B192C; color: #FFFFFF; padding: 13px 28px; border-radius: 9999px; text-decoration: none; font-weight: 800; font-size: 15px;">
                            ← হোম পেজে ফিরে যান
                        </a>
                    </div>
                </div>
            </div>

            <!-- GA4 eCommerce DataLayer & Meta Pixel Purchase Tracking -->
            <script>
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: 'purchase',
                ecommerce: {
                    transaction_id: '<?php echo esc_js( $order->get_order_number() ); ?>',
                    value: <?php echo esc_js( (float) $order->get_total() ); ?>,
                    tax: <?php echo esc_js( (float) $order->get_total_tax() ); ?>,
                    shipping: <?php echo esc_js( (float) $order->get_shipping_total() ); ?>,
                    currency: 'BDT',
                    items: [
                        <?php foreach ( $order->get_items() as $item ) : ?>
                        {
                            item_id: '<?php echo esc_js( $item->get_product_id() ); ?>',
                            item_name: '<?php echo esc_js( $item->get_name() ); ?>',
                            price: <?php echo esc_js( (float) $order->get_item_total( $item, false, false ) ); ?>,
                            quantity: <?php echo esc_js( (int) $item->get_quantity() ); ?>
                        },
                        <?php endforeach; ?>
                    ]
                }
            });

            if (typeof fbq === 'function') {
                fbq('track', 'Purchase', {
                    content_name: 'Pedal Exercise Bike with LCD Display',
                    content_type: 'product',
                    value: <?php echo esc_js( (float) $order->get_total() ); ?>,
                    currency: 'BDT',
                    num_items: <?php echo esc_js( (int) $order->get_item_count() ); ?>
                });
            }
            </script>

        <?php endif; ?>
    <?php else : ?>
        <p style="text-align: center;"><?php esc_html_e( 'ধন্যবাদ! আপনার অর্ডার সফলভাবে প্রক্রিয়া করা হচ্ছে।', 'mastermart' ); ?></p>
    <?php endif; ?>
</div>
