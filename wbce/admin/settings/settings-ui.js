(function () {
    'use strict';

    function showError(form) {
        var old = document.querySelector('.wbce-settings-notice');
        if (old) old.remove();
        var notice = document.createElement('div');
        notice.className = 'wbce-settings-notice';
        notice.textContent = 'Die Einstellungen konnten nicht nachgeladen werden. Bitte versuchen Sie es erneut.';
        form.parentNode.insertBefore(notice, form);
    }

    function saveNotice(form, message, success) {
        var old = document.querySelector('.wbce-settings-save-toast');
        if (old) old.remove();

        var notice = document.createElement('div');
        notice.className = 'wbce-admin-toast wbce-settings-save-toast' + (success ? '' : ' wbce-admin-toast--error');
        notice.setAttribute('role', success ? 'status' : 'alert');
        notice.setAttribute('aria-live', success ? 'polite' : 'assertive');
        notice.textContent = message;
        document.body.appendChild(notice);

        window.setTimeout(function () {
            if (notice.isConnected) notice.remove();
        }, success ? 4500 : 8000);
    }

    function setSaving(form, saving) {
        form.classList.toggle('is-saving', saving);
        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
            button.disabled = saving;
        });
    }

    function updateCounter(input, limit, counterSelector, remainingSelector) {
        if (!input) return;
        var count = input.value.length;
        var counter = document.querySelector(counterSelector);
        var remaining = document.querySelector(remainingSelector);
        if (counter) {
            counter.textContent = count;
            counter.style.color = count > limit ? 'firebrick' : '#147d14';
        }
        if (remaining) {
            remaining.textContent = Math.max(0, limit - count);
            remaining.style.color = count > limit ? 'firebrick' : '#147d14';
        }
    }

    function refreshDynamicFields() {
        updateCounter(document.getElementById('website_title'), 30, '.title-counter', '.title-remain');
        updateCounter(document.getElementById('website_description'), 150, '.desc-counter', '.desc-remain');
        enhanceBooleanSwitches(document);
    }

    function enhanceBooleanSwitches(root) {
        var groups = {};
        root.querySelectorAll('form[name="settings"] input[type="radio"][value="true"], form[name="settings"] input[type="radio"][value="false"]').forEach(function (input) {
            if (!input.name) return;
            (groups[input.name] || (groups[input.name] = [])).push(input);
        });
        Object.keys(groups).forEach(function (name) {
            var inputs = groups[name];
            if (inputs.length !== 2 || !inputs.some(function (input) { return input.value === 'true'; }) || !inputs.some(function (input) { return input.value === 'false'; })) return;
            var container = inputs[0].parentElement && inputs[0].parentElement.parentElement;
            if (!container || container.querySelectorAll('input[type="radio"]').length !== 2) return;
            container.classList.add('wbce-boolean-switch');
            container.setAttribute('role', 'group');
            var sync = function () {
                container.classList.toggle('is-enabled', inputs.some(function (input) { return input.value === 'true' && input.checked; }));
            };
            inputs.forEach(function (input) {
                input.addEventListener('change', sync);
            });
            sync();
        });

        // PAGE_TRASH uses the legacy values "inline" and "disabled". It is
        // still a two-state option and therefore receives the same switch.
        var trashInline = root.querySelector('#page_trash_inline');
        var trashDisabled = root.querySelector('#page_trash_disabled');
        if (trashInline && trashDisabled) {
            var trashContainer = trashInline.parentElement && trashInline.parentElement.parentElement;
            if (trashContainer) {
                trashContainer.classList.add('wbce-boolean-switch');
                trashContainer.setAttribute('role', 'group');
                var syncTrash = function () { trashContainer.classList.toggle('is-enabled', trashInline.checked); };
                trashInline.addEventListener('change', syncTrash);
                trashDisabled.addEventListener('change', syncTrash);
                syncTrash();
            }
        }
        var smtpAuth = root.querySelector('#wbmailer_smtp_auth');
        if (smtpAuth) smtpAuth.classList.add('wbce-boolean-checkbox');
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('.wbce-settings-mode-switch');
        if (!link) return;
        var targetUrl = link.getAttribute('data-mode-url');
        if (!targetUrl) return;
        event.preventDefault();
        event.stopPropagation();

        var form = document.querySelector('form[name="settings"]');
        if (!form || form.classList.contains('is-loading')) return;
        var sectionId = link.getAttribute('data-section') || 'settings-general-settings';
        form.classList.add('is-loading');
        link.disabled = true;
        link.setAttribute('aria-busy', 'true');

        fetch(targetUrl, {credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.text();
            })
            .then(function (html) {
                var parsed = new DOMParser().parseFromString(html, 'text/html');
                var replacement = parsed.querySelector('form[name="settings"]');
                if (!replacement) throw new Error('Settings form missing');
                form.replaceWith(document.importNode(replacement, true));
                refreshDynamicFields();
                var cleanUrl = targetUrl.split('#')[0] + '#' + sectionId;
                history.replaceState(null, '', cleanUrl);
                requestAnimationFrame(function () {
                    var target = document.getElementById(sectionId);
                    if (target) target.scrollIntoView({block: 'start'});
                });
            })
            .catch(function () {
                form.classList.remove('is-loading');
                showError(form);
            })
            .finally(function () {
                link.disabled = false;
                link.removeAttribute('aria-busy');
            });
    }, true);

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || form.name !== 'settings') return;
        event.preventDefault();
        if (form.classList.contains('is-saving')) return;

        setSaving(form, true);
        var data = new FormData(form);
        data.append('ajax', '1');
        fetch(form.action, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'},
            body: data
        })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function (result) {
                if (!result || !result.success) throw new Error((result && result.message) || 'Save failed');
                saveNotice(form, result.message, true);
            })
            .catch(function (error) {
                saveNotice(form, error.message || 'Die Einstellungen konnten nicht gespeichert werden.', false);
            })
            .finally(function () {
                setSaving(form, false);
            });
    });

    document.addEventListener('input', function (event) {
        if (event.target.id === 'website_title' || event.target.id === 'website_description') refreshDynamicFields();
    });

    document.addEventListener('change', function (event) {
        if (event.target.id === 'mailer-php' || event.target.id === 'mailer-smtp') {
            var smtp = document.getElementById('smtp-settings');
            if (smtp) smtp.style.display = event.target.id === 'mailer-smtp' && event.target.checked ? '' : 'none';
        }
        if (event.target.id === 'os-linux' || event.target.id === 'os-windows') {
            var permissions = document.getElementById('file-perms');
            if (permissions) permissions.style.display = event.target.id === 'os-linux' && event.target.checked ? '' : 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', refreshDynamicFields);
}());
