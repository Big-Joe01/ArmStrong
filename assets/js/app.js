/* =============================================================================
   Armstrong — public site scripts (vanilla JS, no dependencies)
   ========================================================================== */
(function () {
    'use strict';

    /** Mobile navigation drawer. */
    function initMobileNav() {
        var toggle = document.querySelector('[data-nav-toggle]');
        var drawer = document.querySelector('[data-mobile-nav]');
        if (!toggle || !drawer) return;

        function close() {
            drawer.classList.remove('is-open');
            toggle.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        toggle.addEventListener('click', function () {
            var open = drawer.classList.toggle('is-open');
            toggle.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
        });

        drawer.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', close);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });
    }

    /** Sticky header shadow on scroll. */
    function initHeaderScroll() {
        var header = document.querySelector('[data-site-header]');
        if (!header) return;
        var onScroll = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /** Product / property image galleries. */
    function initGalleries() {
        document.querySelectorAll('[data-gallery]').forEach(function (gallery) {
            var main = gallery.querySelector('[data-gallery-main]');
            var thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
            if (!main || !thumbs.length) return;

            thumbs.forEach(function (thumb) {
                thumb.addEventListener('click', function () {
                    var full = thumb.getAttribute('data-full') || thumb.querySelector('img').src;
                    main.src = full;
                    thumbs.forEach(function (t) { t.classList.remove('is-active'); });
                    thumb.classList.add('is-active');
                });
            });
        });
    }

    /** Option chips (size / colour selection). */
    function initOptionChips() {
        document.querySelectorAll('[data-chip-group]').forEach(function (group) {
            var field = group.getAttribute('data-chip-group');
            group.querySelectorAll('.chip').forEach(function (chip) {
                chip.addEventListener('click', function () {
                    group.querySelectorAll('.chip').forEach(function (c) { c.classList.remove('is-selected'); });
                    chip.classList.add('is-selected');
                    var target = document.querySelector('[name="' + field + '"]');
                    if (target) target.value = chip.getAttribute('data-value') || chip.textContent.trim();
                });
            });
        });
    }

    /** Quantity steppers. */
    function initQuantity() {
        document.querySelectorAll('[data-qty]').forEach(function (wrap) {
            var input = wrap.querySelector('input');
            if (!input) return;
            wrap.querySelectorAll('[data-qty-btn]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var step = btn.getAttribute('data-qty-btn') === 'up' ? 1 : -1;
                    var min = parseInt(input.getAttribute('min') || '1', 10);
                    var max = parseInt(input.getAttribute('max') || '999999', 10);
                    var value = parseInt(input.value || '1', 10) + step;
                    if (value < min) value = min;
                    if (value > max) value = max;
                    input.value = value;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            });
        });
    }

    /** Fulfilment method: show/hide delivery fields and update totals. */
    function initFulfilment() {
        var form = document.querySelector('[data-order-form]');
        if (!form) return;

        var deliveryBlock = form.querySelector('[data-delivery-block]');
        var radios = form.querySelectorAll('[name="fulfilment_method"]');
        var summaryFee = form.querySelector('[data-summary-fee]');
        var summaryTotal = form.querySelector('[data-summary-total]');
        var subtotalEl = form.querySelector('[data-subtotal]');
        var subtotal = subtotalEl ? parseFloat(subtotalEl.getAttribute('data-subtotal') || '0') : 0;
        var currency = form.getAttribute('data-currency') || '\u20a6';

        function money(value) {
            return currency + value.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function update() {
            var method = form.querySelector('[name="fulfilment_method"]:checked');
            var isDelivery = method && method.value === 'delivery';

            if (deliveryBlock) {
                deliveryBlock.classList.toggle('hide', !isDelivery);
                deliveryBlock.querySelectorAll('input, select, textarea').forEach(function (el) {
                    if (el.name === 'delivery_rate_id' || el.name === 'delivery_address' || el.name === 'delivery_city' || el.name === 'delivery_state') {
                        el.required = isDelivery;
                    }
                });
            }

            var fee = 0;
            if (isDelivery) {
                var rate = form.querySelector('[name="delivery_rate_id"]:checked') || form.querySelector('[name="delivery_rate_id"]');
                if (rate) {
                    var selected = rate.type === 'radio'
                        ? form.querySelector('[name="delivery_rate_id"]:checked')
                        : rate.options[rate.selectedIndex];
                    if (selected) fee = parseFloat(selected.getAttribute('data-fee') || '0');
                }
            }
            if (summaryFee) summaryFee.textContent = money(fee);
            if (summaryTotal) summaryTotal.textContent = money(subtotal + fee);

            form.querySelectorAll('[data-delivery-option]').forEach(function (card) {
                var input = card.querySelector('input');
                card.classList.toggle('is-selected', !!(input && input.checked));
            });
        }

        radios.forEach(function (r) { r.addEventListener('change', update); });
        form.querySelectorAll('[name="delivery_rate_id"]').forEach(function (r) { r.addEventListener('change', update); });
        update();
    }

    /** Confirmation modal helper. */
    function initConfirm() {
        document.addEventListener('click', function (e) {
            var el = e.target.closest('[data-confirm]');
            if (!el) return;
            if (!window.confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    /** Flash message auto-dismiss. */
    function initFlashes() {
        document.querySelectorAll('[data-dismiss]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var alert = btn.closest('.alert');
                if (alert) alert.remove();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initMobileNav();
        initHeaderScroll();
        initGalleries();
        initOptionChips();
        initQuantity();
        initFulfilment();
        initConfirm();
        initFlashes();
    });
})();
