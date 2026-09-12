# 🛒 Master Mart - 1-Click COD WordPress & WooCommerce Landing Page Theme

High-converting, ultra-fast, mobile-first WordPress Landing Page Theme built for Bangladeshi eCommerce (Pedal Exercise Bike with LCD Display and general single-product sales).

---

## 🌟 Key Features

1. **⚡ 1-Click Express Cash on Delivery (COD) Checkout**:
   - High conversion form with live price calculation, district quick-select chips, and Bangladeshi 11-digit mobile validation.
   - Automatically generates real WooCommerce orders (`wc_create_order()`) and redirects to custom branded receipt.
   - Fully compatible with Bangladesh courier APIs (Steadfast, Pathao, RedX, eCourier).

2. **📱 100% Mobile Responsive & Touch-Optimized**:
   - Zero horizontal overflow (`overflow-x: hidden`).
   - Inputs formatted to 16px minimum font size to eliminate iOS Safari auto-zoom.
   - Mobile sticky bottom purchase bar with direct hotline calling and 1-tap scroll to checkout.
   - Touch targets designed for 48px+ finger comfort.

3. **🎨 100% Elementor Editable**:
   - Native Elementor page builder support (`the_content()` rendering in preview and canvas modes).
   - Custom Elementor Widget category: **Master Mart Elements**.
   - 4 Custom Elementor Widgets:
     - `Master Mart 1-Click Checkout`
     - `Master Mart Hero Banner`
     - `Master Mart Features Grid`
     - `Master Mart FAQ Accordion`
   - Shortcodes available for any page builder or template:
     - `[mastermart_checkout]`
     - `[mastermart_hero]`
     - `[mastermart_features]`
     - `[mastermart_faq]`

4. **🐙 GitHub Automatic Updates Engine (`inc/github-updater.php`)**:
   - Push code to GitHub and update your theme directly from **WordPress Admin Dashboard -> Updates**!
   - Built-in GitHub release and tag checker with WordPress `pre_set_site_transient_update_themes` hook.
   - Configurable from **Master Mart Settings** (Repository, Branch, Personal Access Token for private repos).
   - "Check for GitHub Updates Now" button with instant AJAX response.

5. **📊 Standard WooCommerce DOM & Full eCommerce DataLayer (`inc/tracking.php`)**:
   - Checkout form uses standard WooCommerce classes (`.woocommerce-checkout`, `.woocommerce-billing-fields`, `p.form-row`, `.shop_table`, `.input-text`).
   - Google Analytics 4 (GA4) / Google Tag Manager (GTM) standard events:
     - `view_item` (on product landing page load)
     - `begin_checkout` (on customer form interaction)
     - `purchase` (on thank-you order receipt page with full items and transaction metadata)
   - Meta (Facebook) Pixel & CAPI integration:
     - `PageView`, `ViewContent`, `InitiateCheckout`, `Purchase`.

6. **🛡️ Theme Licensing & Protection System (`inc/license.php`)**:
   - Full commercial licensing engine under **Appearance -> Theme License** (or Master Mart settings).
   - Authorized license keys: `105694` or `MASTERMART100`.
   - Automatically active on localhost / development environments.

---

## 🚀 How to Connect to GitHub & Enable Auto-Updates

### 1. Push this Theme to your GitHub Repository:
```bash
git remote add origin https://github.com/YOUR_USERNAME/mastermart.git
git branch -M main
git push -u origin main
```

### 2. Configure in WordPress Admin:
1. Go to **Master Mart -> Master Mart Settings** in your WordPress dashboard.
2. Under **GitHub Automatic Theme Updates**:
   - **GitHub Repository**: Enter `YOUR_USERNAME/mastermart`
   - **Repository Branch**: Enter `main`
   - **Personal Access Token**: Enter your GitHub PAT if the repository is private (leave empty if public).
3. Click **Save Settings**.
4. Click **🔄 Check for GitHub Updates Now** or navigate to **Dashboard -> Updates** to update whenever you push a new release or tag!

---

## 📁 File Structure

```
mastermart/
├── assets/
│   ├── css/
│   │   ├── checkout.css      # Standard WooCommerce & COD form styling
│   │   ├── main.css          # Core design system & layout
│   │   └── responsive.css    # Mobile-first overrides & iOS zoom prevention
│   ├── js/
│   │   └── express-order.js  # AJAX 1-click order engine & DataLayer triggers
│   └── images/               # WebP & JPG assets (Bike, features, logo)
├── inc/
│   ├── ajax.php              # WooCommerce COD order handler & attribution
│   ├── elementor.php         # Elementor integration & shortcodes
│   ├── elementor-widgets/    # Custom Elementor widgets (Checkout, Hero, etc.)
│   ├── enqueue.php           # Script & style enqueues
│   ├── github-updater.php    # GitHub Theme Auto-Updater engine
│   ├── helpers.php           # SVG icons, formatting & hotline helpers
│   ├── license.php           # Nobo Shokti-style license protection (105694)
│   ├── setup.php             # Theme supports & WooCommerce declarations
│   ├── theme-options.php     # Admin control panel & GitHub settings
│   ├── tracking.php          # GA4 eCommerce DataLayer & Meta Pixel
│   └── woocommerce.php       # Product auto-bootstrapper
├── templates/
│   └── template-landing-page.php # Elementor / Landing page template
├── woocommerce/
│   └── checkout/
│       └── thankyou.php      # Custom branded invoice receipt & Purchase DataLayer
├── front-page.php            # Hybrid landing page (Elementor / Native)
├── functions.php             # Core bootstrap file
├── header.php                # Urgency banner, hotline & branding
├── footer.php                # Trust badges, floating WhatsApp & mobile sticky bar
└── style.css                 # Theme metadata & CSS tokens
```

---

## 🛠️ Requirements
- WordPress 6.0 or higher
- WooCommerce 7.0 or higher
- PHP 7.4 to 8.3+
- Elementor (Free or Pro, optional for visual page building)

---
© Master Mart. All rights reserved.
