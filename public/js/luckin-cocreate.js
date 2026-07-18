(function () {
    'use strict';

    function buildDemoQr(container) {
        var size = 21;
        var cells = [];

        function inFinder(row, column, startRow, startColumn) {
            var localRow = row - startRow;
            var localColumn = column - startColumn;
            if (localRow < 0 || localRow > 6 || localColumn < 0 || localColumn > 6) {
                return null;
            }

            return localRow === 0 || localRow === 6 || localColumn === 0 || localColumn === 6 ||
                (localRow >= 2 && localRow <= 4 && localColumn >= 2 && localColumn <= 4);
        }

        for (var row = 0; row < size; row += 1) {
            for (var column = 0; column < size; column += 1) {
                var finder = inFinder(row, column, 0, 0);
                if (finder === null) finder = inFinder(row, column, 0, size - 7);
                if (finder === null) finder = inFinder(row, column, size - 7, 0);

                var on = finder === null
                    ? ((row * 17 + column * 11 + row * column * 3 + 7) % 13) < 6
                    : finder;
                cells.push('<i class="' + (on ? 'is-on' : '') + '"></i>');
            }
        }

        container.innerHTML = cells.join('');
    }

    function initializeCopyButtons() {
        document.querySelectorAll('[data-copy-target]').forEach(function (button) {
            button.addEventListener('click', async function () {
                var target = document.getElementById(button.getAttribute('data-copy-target'));
                var status = document.getElementById('copy-status');
                var label = button.querySelector('span');
                if (!target) return;

                try {
                    if (window.isSecureContext && navigator.clipboard) {
                        await navigator.clipboard.writeText(target.textContent.trim());
                    } else {
                        fallbackCopy(target.textContent.trim());
                    }

                    if (label) label.textContent = '已复制';
                    if (status) status.textContent = '话题已复制';
                } catch (error) {
                    if (label) label.textContent = '请长按复制';
                    if (status) status.textContent = '自动复制失败，请长按话题文字复制';
                }

                window.setTimeout(function () {
                    if (label) label.textContent = '复制';
                }, 1800);
            });
        });
    }

    function fallbackCopy(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();

        var copied = document.execCommand('copy');
        textarea.remove();
        if (!copied) throw new Error('Copy command was rejected');
    }

    function initializeVerifyForm() {
        var form = document.querySelector('[data-verify-form]');
        if (!form) return;

        form.addEventListener('submit', function () {
            var button = form.querySelector('[data-verify-button]');
            if (!button) return;
            button.disabled = true;
            button.innerHTML = '<span class="button-loader" aria-hidden="true"></span>正在核验链接';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-demo-qr]').forEach(buildDemoQr);
        initializeCopyButtons();
        initializeVerifyForm();

        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
}());
