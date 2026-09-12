/**
 * Master Mart - Express Order & Interactive Frontend Engine
 *
 * @package MasterMart
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
    // --------------------------------------------------------------------------
    // 1. DELIVERY ZONE & LIVE ORDER TOTAL CALCULATION
    // --------------------------------------------------------------------------
    const basePrice = typeof mastermart_ajax !== 'undefined' ? parseFloat(mastermart_ajax.product_price) : 2000;
    const shipInside = typeof mastermart_ajax !== 'undefined' ? parseFloat(mastermart_ajax.shipping_inside) : 80;
    const shipOutside = typeof mastermart_ajax !== 'undefined' ? parseFloat(mastermart_ajax.shipping_outside) : 150;

    let orderQty = 1;

    function updateTotals() {
        const selectedZone = document.querySelector('input[name="delivery_zone"]:checked');
        const zoneVal = selectedZone ? selectedZone.value : 'outside';
        const qtyValEl = document.getElementById('mm-current-qty');
        const currentQty = qtyValEl ? parseInt(qtyValEl.textContent, 10) || 1 : 1;
        const productSubtotal = basePrice * currentQty;
        const shippingFee = (zoneVal === 'inside') ? shipInside : shipOutside;
        const total = productSubtotal + shippingFee;

        // Update Summary DOM
        const lineSubtotal = document.getElementById('mm-line-subtotal');
        const lineShipping = document.getElementById('mm-line-shipping');
        const lineTotal = document.getElementById('mm-line-total');
        const mobPrice = document.getElementById('mm-mob-price');
        const summaryQtyPrice = document.getElementById('mm-summary-qty-price');

        if (lineSubtotal) lineSubtotal.textContent = '৳' + productSubtotal.toLocaleString();
        if (lineShipping) lineShipping.textContent = '৳' + shippingFee;
        if (lineTotal) lineTotal.textContent = '৳' + total.toLocaleString();
        if (mobPrice) mobPrice.textContent = '৳' + total.toLocaleString();
        if (summaryQtyPrice) summaryQtyPrice.textContent = '৳' + basePrice.toLocaleString() + ' × ' + currentQty;

        // Update active class on zone options
        document.querySelectorAll('.mm-zone-option').forEach(opt => opt.classList.remove('active'));
        if (selectedZone) {
            const parentLabel = selectedZone.closest('.mm-zone-option');
            if (parentLabel) parentLabel.classList.add('active');
        }
    }

    window.mastermartChangeQty = function (delta) {
        const qtyValEl = document.getElementById('mm-current-qty');
        const hiddenQty = document.getElementById('mm-order-qty');
        let current = qtyValEl ? parseInt(qtyValEl.textContent, 10) || 1 : 1;
        current += delta;
        if (current < 1) current = 1;
        if (current > 10) current = 10;

        if (qtyValEl) qtyValEl.textContent = current;
        if (hiddenQty) hiddenQty.value = current;

        updateTotals();
    };

    const zoneRadios = document.querySelectorAll('input[name="delivery_zone"]');
    zoneRadios.forEach(radio => {
        radio.addEventListener('change', updateTotals);
    });
    updateTotals();

    // --------------------------------------------------------------------------
    // 2. 11-DIGIT BANGLADESHI PHONE NUMBER VALIDATOR
    // --------------------------------------------------------------------------
    window.mastermartValidatePhone = function (input) {
        const val = input.value.trim().replace(/[^0-9]/g, '');
        const msgEl = document.getElementById('mm-phone-msg');
        if (!msgEl) return;

        if (val.length === 0) {
            msgEl.className = 'mm-val-msg';
            msgEl.textContent = '';
        } else if (val.startsWith('01') && val.length === 11) {
            msgEl.className = 'mm-val-msg valid';
            msgEl.textContent = '✓ সঠিক মোবাইল নাম্বার';
        } else if (!val.startsWith('01')) {
            msgEl.className = 'mm-val-msg invalid';
            msgEl.textContent = 'মোবাইল নাম্বার 01 দিয়ে শুরু হতে হবে';
        } else if (val.length < 11) {
            msgEl.className = 'mm-val-msg invalid';
            msgEl.textContent = '১১ ডিজিট হতে আরও ' + (11 - val.length) + 'টি সংখ্যা বাকি';
        } else {
            msgEl.className = 'mm-val-msg invalid';
            msgEl.textContent = 'মোবাইল নাম্বার ১১ ডিজিটের বেশি হতে পারবে না';
        }
    };

    // --------------------------------------------------------------------------
    // 4. DATALAYER: BEGIN_CHECKOUT & INITIATE_CHECKOUT TRIGGER
    // --------------------------------------------------------------------------
    let hasTrackedBeginCheckout = false;
    function triggerBeginCheckout() {
        if (hasTrackedBeginCheckout) return;
        hasTrackedBeginCheckout = true;

        const selectedZone = document.querySelector('input[name="delivery_zone"]:checked');
        const zoneVal = selectedZone ? selectedZone.value : 'outside';
        const shippingFee = (zoneVal === 'inside') ? shipInside : shipOutside;
        const currentTotal = basePrice + shippingFee;
        const productIdEl = document.getElementById('mm-product-id');
        const pId = productIdEl ? productIdEl.value : 'pedal-cycle-lcd';

        // GA4 eCommerce DataLayer begin_checkout
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            event: 'begin_checkout',
            ecommerce: {
                currency: 'BDT',
                value: currentTotal,
                items: [{
                    item_id: pId,
                    item_name: 'Pedal Exercise Bike with LCD Display',
                    price: basePrice,
                    quantity: 1
                }]
            }
        });

        // Meta Pixel InitiateCheckout
        if (typeof fbq === 'function') {
            fbq('track', 'InitiateCheckout', {
                content_name: 'Pedal Exercise Bike with LCD Display',
                content_ids: [pId],
                content_type: 'product',
                value: currentTotal,
                currency: 'BDT',
                num_items: 1
            });
        }
    }

    // Attach interaction listeners to form fields
    const checkoutInputs = document.querySelectorAll('#mm-checkout-form input, #mm-checkout-form textarea');
    checkoutInputs.forEach(input => {
        input.addEventListener('focus', triggerBeginCheckout, { once: true });
        input.addEventListener('input', triggerBeginCheckout, { once: true });
    });

    // --------------------------------------------------------------------------
    // 5. AJAX EXPRESS 1-CLICK COD ORDER SUBMISSION
    // --------------------------------------------------------------------------
    const orderForm = document.getElementById('mm-checkout-form');
    if (orderForm) {
        orderForm.addEventListener('submit', function (e) {
            e.preventDefault();
            triggerBeginCheckout();

            const submitBtn = document.getElementById('mm-place-order-btn');
            const name = document.getElementById('billing_first_name').value.trim();
            const phone = document.getElementById('billing_phone').value.trim().replace(/[^0-9]/g, '');
            const address = document.getElementById('billing_address_1').value.trim();
            const comments = document.getElementById('order_comments') ? document.getElementById('order_comments').value.trim() : '';
            const selectedZone = document.querySelector('input[name="delivery_zone"]:checked');
            const zoneVal = selectedZone ? selectedZone.value : 'outside';
            const productIdEl = document.getElementById('mm-product-id');
            const productId = productIdEl ? productIdEl.value : '';

            // Validation
            if (!name || !phone || !address) {
                alert(mastermart_ajax.strings.required_err || 'অনুগ্রহ করে নাম, মোবাইল নাম্বার এবং সম্পূর্ণ ঠিকানা পূরণ করুন।');
                return;
            }

            if (!phone.startsWith('01') || phone.length !== 11) {
                alert(mastermart_ajax.strings.phone_err || 'অনুগ্রহ করে সঠিক ১১ ডিজিটের মোবাইল নাম্বার দিন (যেমন: 01XXXXXXXXX)');
                document.getElementById('billing_phone').focus();
                return;
            }

            // Disable button & show spinner state
            submitBtn.disabled = true;
            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span>⏳ ' + (mastermart_ajax.strings.processing || 'অর্ডার প্রসেস হচ্ছে...') + '</span>';

            const qtyEl = document.getElementById('mm-order-qty');
            const quantity = qtyEl ? parseInt(qtyEl.value, 10) || 1 : 1;

            const formData = new FormData(orderForm);
            formData.append('action', 'mastermart_express_order');
            formData.append('nonce', mastermart_ajax.nonce);
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            formData.append('customer_name', name);
            formData.append('customer_phone', phone);
            formData.append('customer_address', address);
            formData.append('order_comments', comments);
            formData.append('delivery_zone', zoneVal);

            fetch(mastermart_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.redirect_url) {
                    // Redirect to Thank You page
                    window.location.href = data.data.redirect_url;
                } else if (data.success) {
                    alert(data.data.message || 'আপনার অর্ডার সফল হয়েছে!');
                    window.location.reload();
                } else {
                    alert(data.data && data.data.message ? data.data.message : 'অর্ডারে ত্রুটি হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            })
            .catch(err => {
                alert(mastermart_ajax.strings.server_err || 'সার্ভার সমস্যা হয়েছে। অনুগ্রহ করে সরাসরি ফোনে যোগাযোগ করুন।');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            });
        });
    }

    // --------------------------------------------------------------------------
    // 5. INTERACTIVE FAQ ACCORDION
    // --------------------------------------------------------------------------
    const faqItems = document.querySelectorAll('.mm-faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.mm-faq-question');
        if (question) {
            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');
                // Close all other items
                faqItems.forEach(other => other.classList.remove('active'));
                // Toggle current
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        }
    });

    // --------------------------------------------------------------------------
    // 6. URGENCY COUNTDOWN TIMER
    // --------------------------------------------------------------------------
    function initCountdown() {
        let totalSeconds = 4 * 3600 + 38 * 60 + 22; // 04:38:22 initial
        const timerEl = document.getElementById('mm-timer-display');
        if (!timerEl) return;

        setInterval(() => {
            if (totalSeconds > 0) {
                totalSeconds--;
            }
            const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(totalSeconds % 60).padStart(2, '0');
            timerEl.textContent = `${hours}:${minutes}:${seconds}`;
        }, 1000);
    }
    initCountdown();

    // --------------------------------------------------------------------------
    // 7. LIVE BANGLADESHI SOCIAL PROOF TOAST
    // --------------------------------------------------------------------------
    const bdNames = [
        "মোঃ শরিফুল ইসলাম", "তানজিল আহমেদ", "জাকির হোসেন", "মাহবুবুর রহমান",
        "মোঃ কামাল উদ্দিন", "রাসেল রানা", "তারেক হাসান", "সাজিদ মাহমুদ",
        "আব্দুল্লাহ আল মামুন", "আসিফ ইকবাল", "সোহেল রানা", "ইমরান খান",
        "মোশাররফ হোসেন", "রাশেদুল ইসলাম", "সাব্বির আহমেদ", "এনামুল হক"
    ];

    const bdLocations = [
        "মিরপুর, ঢাকা", "উত্তরা, ঢাকা", "ধানমন্ডি, ঢাকা", "জিইসি, চট্টগ্রাম",
        "উপশহর, সিলেট", "বোয়ালিয়া, রাজশাহী", "সোনাডাঙ্গা, খুলনা", "টঙ্গী, গাজীপুর",
        "চাষাঢ়া, নারায়ণগঞ্জ", "কান্দিরপাড়, কুমিল্লা", "চকবাজার, বরিশাল", "পাবনা সদর"
    ];

    function triggerSocialToast() {
        const toast = document.getElementById('mm-social-toast');
        if (!toast) return;

        const u = bdNames[Math.floor(Math.random() * bdNames.length)];
        const loc = bdLocations[Math.floor(Math.random() * bdLocations.length)];

        const titleEl = document.getElementById('mm-toast-user');
        const descEl = document.getElementById('mm-toast-info');

        if (titleEl) titleEl.textContent = `${u} (${loc})`;
        if (descEl) descEl.textContent = 'Pedal Exercise Bike অর্ডার করেছেন • এইমাত্র';

        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4500);

        // Schedule next toast between 9 to 16 seconds
        setTimeout(triggerSocialToast, Math.floor(Math.random() * 7000) + 9000);
    }
    setTimeout(triggerSocialToast, 3500);

    // --------------------------------------------------------------------------
    // 8. SMOOTH SCROLL TO ORDER SECTION
    // --------------------------------------------------------------------------
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({ behavior: 'smooth' });
                    setTimeout(() => {
                        const nameInput = document.getElementById('billing_first_name');
                        if (nameInput) nameInput.focus();
                    }, 500);
                }
            }
        });
    });
});
