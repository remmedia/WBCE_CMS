(function (window, document) {
    'use strict';
    if (window.WBCEAsyncForms) return;

    function toast(message, failed) {
        var node = document.createElement('div');
        node.className = 'wbce-admin-toast' + (failed ? ' is-error' : ' is-success');
        node.setAttribute('role', 'status');
        node.setAttribute('aria-live', 'polite');
        node.textContent = String(message || '');
        document.body.appendChild(node);
        window.setTimeout(function () { node.remove(); }, 7000);
    }

    function replaceFtan(form, html) {
        if (!html) return;
        var holder = document.createElement('div');
        holder.innerHTML = html;
        var fresh = holder.querySelector('input[type="hidden"][name]');
        if (!fresh) return;
        var escapedName = window.CSS && window.CSS.escape ? window.CSS.escape(fresh.name) : fresh.name.replace(/(["\\])/g, '\\$1');
        var current = form.querySelector('[data-wbce-ftan]') || form.querySelector('input[type="hidden"][name="' + escapedName + '"]');
        fresh.setAttribute('data-wbce-ftan', fresh.value);
        if (current) current.replaceWith(fresh);
        else form.prepend(fresh);
    }

    function refreshView(form, selector) {
        if (!selector) return Promise.resolve();
        var current = form.closest(selector) || document.querySelector(selector);
        if (!current) return Promise.resolve();
        var matches = Array.prototype.slice.call(document.querySelectorAll(selector));
        var index = Math.max(0, matches.indexOf(current));
        return fetch(window.location.href, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { if (!response.ok) throw new Error('HTTP ' + response.status); return response.text(); })
            .then(function (html) {
                var parsed = new DOMParser().parseFromString(html, 'text/html');
                var fresh = parsed.querySelectorAll(selector)[index] || parsed.querySelector(selector);
                if (!fresh) return;
                var imported = document.importNode(fresh, true);
                current.replaceWith(imported);
                imported.querySelectorAll('script').forEach(function (oldScript) {
                    var script = document.createElement('script');
                    Array.prototype.forEach.call(oldScript.attributes, function (attribute) { script.setAttribute(attribute.name, attribute.value); });
                    script.textContent = oldScript.textContent;
                    oldScript.replaceWith(script);
                });
                bind(imported);
                imported.dispatchEvent(new CustomEvent('wbce:async-view-refreshed', { bubbles: true }));
            });
    }

    function bind(root) {
        (root || document).querySelectorAll('form[data-wbce-async-form]:not([data-wbce-async-bound])').forEach(function (form) {
            form.dataset.wbceAsyncBound = '1';
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                var submitter = event.submitter || document.activeElement;
                var data = new FormData(form);
                var ftan = form.querySelector('[data-wbce-ftan]');
                if (ftan && ftan.name && ftan.getAttribute('data-wbce-ftan')) data.set(ftan.name, ftan.getAttribute('data-wbce-ftan'));
                if (submitter && submitter.name) data.set(submitter.name, submitter.value || '1');
                var buttons = Array.prototype.slice.call(form.querySelectorAll('button[type="submit"],input[type="submit"]'));
                buttons.forEach(function (button) { button.disabled = true; button.setAttribute('aria-busy', 'true'); });
                fetch(form.action, {
                    method: (form.method || 'post').toUpperCase(), body: data,
                    credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                }).then(function (response) {
                    return response.json().catch(function () { throw new Error('HTTP ' + response.status); }).then(function (payload) {
                        if (!response.ok || !payload.ok) throw Object.assign(new Error(payload.message || ('HTTP ' + response.status)), { payload: payload });
                        return payload;
                    });
                }).then(function (payload) {
                    replaceFtan(form, payload.ftan);
                    toast(payload.message || form.dataset.successMessage, false);
                    form.dispatchEvent(new CustomEvent('wbce:async-saved', { bubbles: true, detail: payload }));
                    if (submitter && (submitter.name === 'pagetree' || submitter.name === 'save_back') && payload.redirect) {
                        window.location.assign(payload.redirect);
                        return;
                    }
                    return refreshView(form, form.dataset.refreshSelector || '');
                }).catch(function (error) {
                    replaceFtan(form, error.payload && error.payload.ftan);
                    toast(error.message || form.dataset.errorMessage, true);
                }).finally(function () {
                    buttons.forEach(function (button) { button.disabled = false; button.removeAttribute('aria-busy'); });
                });
            });
        });

        (root || document).querySelectorAll('[data-wbce-async-action]:not([data-wbce-async-bound])').forEach(function (link) {
            link.dataset.wbceAsyncBound = '1';
            link.addEventListener('click', function (event) {
                event.preventDefault();
                var run = function () {
                    link.setAttribute('aria-busy', 'true');
                    link.setAttribute('aria-disabled', 'true');
                    fetch(link.href, {
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    }).then(function (response) {
                        return response.json().catch(function () { throw new Error('HTTP ' + response.status); }).then(function (payload) {
                            if (!response.ok || !payload.ok) throw new Error(payload.message || ('HTTP ' + response.status));
                            return payload;
                        });
                    }).then(function (payload) {
                        toast(payload.message || link.dataset.successMessage, false);
                        return refreshView(link, link.dataset.refreshSelector || '');
                    }).catch(function (error) {
                        toast(error.message || link.dataset.errorMessage, true);
                    }).finally(function () {
                        link.removeAttribute('aria-busy');
                        link.removeAttribute('aria-disabled');
                    });
                };
                var question = link.dataset.confirm || '';
                if (!question) { run(); return; }
                var dialog = document.createElement('dialog');
                dialog.className = 'wbce-async-confirm';
                dialog.innerHTML = '<form method="dialog"><p></p><div class="wbce-async-confirm-actions"><button value="cancel"></button><button value="confirm"></button></div></form>';
                dialog.querySelector('p').textContent = question;
                dialog.querySelector('button[value="cancel"]').textContent = link.dataset.cancelLabel || 'Cancel';
                dialog.querySelector('button[value="confirm"]').textContent = link.dataset.confirmLabel || 'OK';
                dialog.addEventListener('close', function () { var confirmed = dialog.returnValue === 'confirm'; dialog.remove(); if (confirmed) run(); });
                document.body.appendChild(dialog);
                if (typeof dialog.showModal === 'function') dialog.showModal();
                else { var confirmed = window.confirm(question); dialog.remove(); if (confirmed) run(); }
            });
        });
    }

    window.WBCEAsyncForms = { bind: bind, toast: toast };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { bind(document); });
    else bind(document);
}(window, document));
