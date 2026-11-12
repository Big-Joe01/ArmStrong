/* =============================================================================
   Armstrong — administration scripts (vanilla JS, no dependencies)
   ========================================================================== */
(function () {
    'use strict';

    /** Sidebar drawer on small screens. */
    function initSidebar() {
        var toggle = document.querySelector('[data-admin-menu]');
        var sidebar = document.querySelector('[data-admin-sidebar]');
        var backdrop = document.querySelector('[data-admin-backdrop]');
        if (!toggle || !sidebar) return;

        function close() {
            sidebar.classList.remove('is-open');
            if (backdrop) backdrop.classList.remove('is-open');
        }

        toggle.addEventListener('click', function () {
            var open = sidebar.classList.toggle('is-open');
            if (backdrop) backdrop.classList.toggle('is-open', open);
        });
        if (backdrop) backdrop.addEventListener('click', close);
    }

    /** Confirmation prompts for destructive actions. */
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

    /** Repeatable key/value rows (specifications, features, amenities). */
    function initRepeaters() {
        document.querySelectorAll('[data-repeater]').forEach(function (wrap) {
            var container = wrap.querySelector('[data-repeater-rows]');
            var addBtn = wrap.querySelector('[data-repeater-add]');
            var template = wrap.querySelector('template');
            if (!container || !template) return;

            function bindRow(row) {
                var remove = row.querySelector('[data-repeater-remove]');
                if (remove) {
                    remove.addEventListener('click', function () { row.remove(); });
                }
            }

            container.querySelectorAll('[data-repeater-row]').forEach(bindRow);

            if (addBtn) {
                addBtn.addEventListener('click', function () {
                    var clone = template.content.cloneNode(true);
                    var row = clone.querySelector('[data-repeater-row]');
                    container.appendChild(clone);
                    if (row) bindRow(row);
                });
            }
        });
    }

    /** Show/hide blocks based on a select or radio value. */
    function initConditional() {
        document.querySelectorAll('[data-toggle-target]').forEach(function (controller) {
            var targetSelector = controller.getAttribute('data-toggle-target');
            var showWhen = (controller.getAttribute('data-toggle-value') || '').split(',');
            var target = document.querySelector(targetSelector);
            if (!target) return;

            function update() {
                var value = '';
                if (controller.type === 'radio' || controller.type === 'checkbox') {
                    var checked = document.querySelector('[name="' + controller.name + '"]:checked');
                    value = checked ? checked.value : '';
                } else {
                    value = controller.value;
                }
                target.classList.toggle('hide', showWhen.indexOf(value) === -1);
            }

            controller.addEventListener('change', update);
            document.querySelectorAll('[name="' + controller.name + '"]').forEach(function (el) {
                el.addEventListener('change', update);
            });
            update();
        });
    }

    /** Live-computed line totals for the walk-in sale builder. */
    function initWalkIn() {
        var form = document.querySelector('[data-walkin-form]');
        if (!form) return;

        function recalc() {
            var total = 0;
            form.querySelectorAll('[data-walkin-row]').forEach(function (row) {
                var price = parseFloat(row.getAttribute('data-price') || '0');
                var qtyInput = row.querySelector('[name="quantities[]"]');
                var qty = qtyInput ? parseInt(qtyInput.value || '0', 10) : 0;
                var line = price * (isNaN(qty) ? 0 : qty);
                var cell = row.querySelector('[data-line-total]');
                if (cell) cell.textContent = line.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                total += line;
            });
            var totalEl = form.querySelector('[data-walkin-total]');
            if (totalEl) totalEl.textContent = total.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        form.addEventListener('input', recalc);
        form.addEventListener('change', recalc);
        recalc();
    }

    /** Product picker for walk-in sales: filter the row list by search text. */
    function initWalkInSearch() {
        var search = document.querySelector('[data-product-search]');
        if (!search) return;
        var rows = document.querySelectorAll('[data-walkin-row]');
        search.addEventListener('input', function () {
            var term = search.value.toLowerCase();
            rows.forEach(function (row) {
                var text = row.getAttribute('data-search') || '';
                row.classList.toggle('hide', text.indexOf(term) === -1);
            });
        });
    }

    /** Auto-submit filter forms when a select changes. */
    function initAutoSubmit() {
        document.querySelectorAll('[data-auto-submit]').forEach(function (el) {
            el.addEventListener('change', function () {
                var form = el.closest('form');
                if (form) form.submit();
            });
        });
    }

    /** Print a section (receipts). */
    function initPrint() {
        document.querySelectorAll('[data-print]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                window.print();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSidebar();
        initConfirm();
        initRepeaters();
        initConditional();
        initWalkIn();
        initWalkInSearch();
        initAutoSubmit();
        initPrint();
    });
})();
