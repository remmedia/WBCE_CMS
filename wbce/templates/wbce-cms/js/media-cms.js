/** WBCE CMS: progressive AJAX saving for non-destructive WBCE POST forms. */
(function () {
    'use strict';

    var destructive = /(?:delete|remove|uninstall|install|drop|purge|logout|upload|create|rename|send[_-]?test|manual[_-]?install|löschen|entfernen|deinstall)/i;
    var dangerVisual = /(?:delete|remove|uninstall|drop|purge|destroy|löschen|entfernen|deinstall)/i;
    var warningVisual = /(?:downgrade|warning|warnung|zurückstufen)/i;
    var navigational = /(?:saveandback|save_and_back|back|zurück|cancel|abbrechen)/i;
    var saving = /(?:save|speichern|update|aktualisieren|apply|übernehmen)/i;
    var savingAction = /(?:save|update|settings|preferences)/i;
    var mutation = /(?:toggle|activate|deactivate|enable|disable|publish|unpublish|status|reorder|sort|aktivieren|deaktivieren)/i;

    function toast(message, type) {
        if (type === true) type = 'error';
        else if (type === false || !type) type = 'success';
        if (!/^(success|error|warning|info)$/.test(type)) type = 'info';
        var region = document.querySelector('.media-cms-toast-region');
        if (!region) {
            region = document.createElement('div');
            region.className = 'media-cms-toast-region';
            region.setAttribute('role', 'status');
            region.setAttribute('aria-live', 'polite');
            document.body.appendChild(region);
        }
        var item = document.createElement('div');
        item.className = 'media-cms-toast is-' + type;
        item.setAttribute('role', type === 'error' ? 'alert' : 'status');
        item.textContent = message;
        region.appendChild(item);
        window.setTimeout(function () { if (item.parentNode) item.parentNode.removeChild(item); }, type === 'error' ? 9000 : 4500);
    }

    window.mediaCmsToast = toast;

    function promoteMessages(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var selector = '.mss-ok,.mss-error,.alert-success,.alert-danger,.alert-warning,.alert-info,.alertbox_success,.alertbox_error,.alertbox_warning,.alertbox_info';
        var messages = [];
        if (scope.nodeType === 1 && scope.matches && scope.matches(selector)) messages.push(scope);
        Array.prototype.push.apply(messages, scope.querySelectorAll(selector));
        messages.forEach(function (message) {
            if (message.dataset.mediaCmsToastDone === '1' || message.closest('.media-cms-toast-region, #toast-container')) return;
            if (message.querySelector('a,button,input,select,textarea,form,script')) return;
            var text = (message.textContent || '').replace(/\s+/g, ' ').trim();
            if (!text) return;
            var type = message.matches('.mss-error,.alert-danger,.alertbox_error') ? 'error'
                : message.matches('.alert-warning,.alertbox_warning') ? 'warning'
                : message.matches('.alert-info,.alertbox_info') ? 'info' : 'success';
            message.dataset.mediaCmsToastDone = '1';
            message.hidden = true;
            toast(text, type);
        });
    }

    function labelOf(button) {
        if (!button) return '';
        return [button.name, button.value, button.textContent, button.getAttribute('title')].filter(Boolean).join(' ');
    }

    function beginBusy(form, button) {
        if (form.getAttribute('aria-busy') === 'true') return false;
        form.classList.add('media-cms-saving');
        form.setAttribute('aria-busy', 'true');
        Array.prototype.forEach.call(form.querySelectorAll('button, input, select, textarea'), function (control) {
            control.dataset.mediaCmsWasDisabled = control.disabled ? '1' : '0';
            control.disabled = true;
        });
        if (button && (button.tagName === 'BUTTON' || /^(submit|button)$/i.test(button.type || ''))) {
            button.dataset.mediaCmsBusyLabel = button.tagName === 'INPUT' ? button.value : button.textContent;
            if (button.tagName === 'INPUT') button.value = 'Wird ausgeführt …';
            else button.textContent = 'Wird ausgeführt …';
            button.classList.add('media-cms-action-busy');
        }
        var indicator = document.createElement('span');
        indicator.className = 'media-cms-busy-indicator';
        indicator.setAttribute('role', 'status');
        indicator.textContent = 'Wird gespeichert …';
        form.appendChild(indicator);
        return true;
    }

    function endBusy(form, button) {
        form.classList.remove('media-cms-saving');
        form.removeAttribute('aria-busy');
        Array.prototype.forEach.call(form.querySelectorAll('button, input, select, textarea'), function (control) {
            if (control.dataset.mediaCmsWasDisabled === '0') control.disabled = false;
            delete control.dataset.mediaCmsWasDisabled;
        });
        if (button && button.dataset.mediaCmsBusyLabel !== undefined) {
            if (button.tagName === 'INPUT') button.value = button.dataset.mediaCmsBusyLabel;
            else button.textContent = button.dataset.mediaCmsBusyLabel;
            delete button.dataset.mediaCmsBusyLabel;
            button.classList.remove('media-cms-action-busy');
        }
        var indicator = form.querySelector('.media-cms-busy-indicator');
        if (indicator) indicator.remove();
    }

    function formSignal(form, button, action) {
        var hidden = Array.prototype.map.call(
            form.querySelectorAll('input[type="hidden"][name]'),
            function (input) { return input.name + ' ' + input.value; }
        ).join(' ');
        return [action.pathname, action.search, form.name, form.id, labelOf(button), hidden].filter(Boolean).join(' ');
    }

    function enhanceButtons(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('button, input[type="submit"], input[type="button"], a.btn').forEach(function (button) {
            if (button.classList.contains('store-button') || button.classList.contains('media-cms-language-option') || button.closest('.store-actions')) return;
            var form = button.form || button.closest('form');
            var signal = labelOf(button) + ' ' + (form ? form.getAttribute('action') || '' : '');
            button.classList.add('btn');
            button.classList.remove('btn-primary', 'btn-danger', 'btn-warning', 'btn-success', 'btn-outline-light');
            if (dangerVisual.test(signal)) button.classList.add('btn-danger');
            else if (warningVisual.test(signal)) button.classList.add('btn-warning');
            else if (navigational.test(signal) || button.type === 'reset') button.classList.add('btn-outline-secondary');
            else button.classList.add('btn-primary');
        });
    }

    function escapeSelector(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') return window.CSS.escape(value);
        return String(value).replace(/["\\]/g, '\\$&');
    }

    function initializeUserMenu() {
        var picker = document.querySelector('[data-media-cms-language]');
        if (!picker || !window.MEDIA_CMS_THEME_URL) return;
        var endpoint = window.MEDIA_CMS_THEME_URL + '/api/language.php';
        var token = '';
        var menuToggle = document.getElementById('mediaCmsUserMenu');
        function revealCurrentLanguage() {
            var activeLanguage = picker.querySelector('.media-cms-language-option.is-active');
            if (!activeLanguage || picker.clientHeight === 0) return;
            picker.scrollTop = activeLanguage.offsetTop - picker.offsetTop;
        }
        if (menuToggle) {
            menuToggle.addEventListener('click', function () {
                window.setTimeout(revealCurrentLanguage, 0);
            });
            if (window.jQuery) {
                window.jQuery(menuToggle).closest('.dropdown').on('shown.bs.dropdown', revealCurrentLanguage);
            }
        }
        picker.addEventListener('click', function (event) { event.stopPropagation(); });
        fetch(endpoint, { credentials: 'same-origin', cache: 'no-store' }).then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.json();
        }).then(function (data) {
            if (!data.success || !Array.isArray(data.languages)) throw new Error(data.message || 'Sprachen konnten nicht geladen werden.');
            token = String(data.ftan || '');
            picker.innerHTML = '';
            data.languages.forEach(function (language) {
                var code = String(language.code || '').toUpperCase();
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'media-cms-language-option';
                button.dataset.language = code;
                button.textContent = language.name || code;
                if (code === String(data.current || window.LANGUAGE_CODE || '').toUpperCase()) {
                    button.classList.add('is-active');
                    button.setAttribute('aria-current', 'true');
                }
                picker.appendChild(button);
            });
            window.requestAnimationFrame(revealCurrentLanguage);
        }).catch(function (error) {
            picker.textContent = 'Sprachauswahl nicht verfügbar';
            toast(error.message, true);
        });
        picker.addEventListener('click', function (event) {
            var button = event.target.closest('[data-language]');
            if (!button || button.classList.contains('is-active')) return;
            var language = String(button.dataset.language || '').toUpperCase();
            if (!/^[A-Z]{2}$/.test(language) || !token) return;
            var separator = token.indexOf('=');
            if (separator < 1) return;
            var body = new URLSearchParams();
            body.set(token.slice(0, separator), token.slice(separator + 1));
            body.set('language', language);
            var oldLabel = button.textContent;
            button.textContent = 'Wird geladen …';
            button.classList.add('media-cms-action-busy');
            picker.querySelectorAll('button').forEach(function (item) { item.disabled = true; });
            fetch(endpoint, { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(function (response) {
                return response.json().then(function (data) { if (!response.ok || !data.success) throw new Error(data.message || 'Sprachwechsel fehlgeschlagen.'); });
            }).then(function () { window.location.reload(); }).catch(function (error) {
                picker.querySelectorAll('button').forEach(function (item) { item.disabled = false; });
                button.textContent = oldLabel;
                button.classList.remove('media-cms-action-busy');
                toast(error.message, true);
            });
        });
    }

    function initializeAdminToolLayout() {
        if (document.querySelector('.adminModuleWrapper.addon_monitor, .adminModuleWrapper .addon-monitor')) {
            document.body.classList.add('media-cms-tool-addon-monitor');
        }
        var legacyModule = document.querySelector('.adminModuleWrapper');
        var legacyShell = legacyModule ? legacyModule.querySelector('.wbce-admin-tool-shell') : null;
        var generatedShell = document.querySelector('.content-body > .wbce-admin-tool-shell');
        var hasOwnHero = legacyModule && legacyModule.querySelector('[class*="hero"], [class*="Hero"]');
        var enclosingShell = legacyModule && legacyModule.parentElement && legacyModule.parentElement.classList.contains('wbce-admin-tool-shell') ? legacyModule.parentElement : null;
        if (enclosingShell) {
            if (hasOwnHero) {
                var outerHero = enclosingShell.firstElementChild;
                if (outerHero && outerHero.classList.contains('wbce-admin-hero')) outerHero.remove();
            }
            return;
        }
        if (legacyModule && generatedShell && hasOwnHero && !generatedShell.contains(legacyModule)) {
            generatedShell.remove();
        } else if (legacyModule && generatedShell && !hasOwnHero) {
            document.body.classList.add('media-cms-admintools', 'media-cms-tool-legacy');
            if (!generatedShell.contains(legacyModule)) {
                var moduleContainer = legacyModule.closest('.card') || legacyModule;
                if (moduleContainer.parentNode && moduleContainer.parentNode === generatedShell.parentNode) {
                    moduleContainer.parentNode.insertBefore(generatedShell, moduleContainer);
                }
            }
        } else if (legacyModule && legacyShell && !hasOwnHero) {
            document.body.classList.add('media-cms-admintools', 'media-cms-tool-legacy');
        }
    }

    function eligible(form, button) {
        var method = (form.getAttribute('method') || 'get').toLowerCase();
        var action;
        try { action = new URL(form.getAttribute('action') || window.location.href, window.location.href); } catch (error) { return false; }
        var signal = formSignal(form, button, action);

        if (method !== 'post' || action.origin !== window.location.origin || form.target) return false;
        // Creating a page ends with WBCE's normal success page and redirect.
        // Let the browser follow that flow instead of treating it as an AJAX save response.
        if (/\/pages\/add\.php$/i.test(action.pathname)) return false;
        if ((form.enctype || '').toLowerCase().indexOf('multipart/form-data') !== -1) return false;
        if (form.matches('[data-no-ajax], [data-media-cms-ajax="false"]')) return false;
        if (destructive.test(signal) || navigational.test(labelOf(button))) return false;
        return form.matches('[data-media-cms-ajax="true"]') || saving.test(labelOf(button)) || savingAction.test(action.pathname) || mutation.test(signal);
    }

    function refreshTokens(form, html) {
        if (!html || html.indexOf('<') === -1) return;
        var parsed = new DOMParser().parseFromString(html, 'text/html');
        var freshForm = form.name ? parsed.querySelector('form[name="' + escapeSelector(form.name) + '"]') : null;
        if (!freshForm && form.id) freshForm = parsed.getElementById(form.id);
        if (!freshForm) return;
        form.querySelectorAll('input[type="hidden"]').forEach(function (input) {
            var fresh = freshForm.querySelector('input[type="hidden"][name="' + escapeSelector(input.name) + '"]');
            if (fresh) input.value = fresh.value;
        });
    }

    function serverError(response, body) {
        var contentType = response.headers.get('content-type') || '';
        if (contentType.indexOf('application/json') !== -1) {
            try {
                var json = JSON.parse(body);
                return json && (json.success === false || /error|failed|denied/i.test(String(json.status || '')));
            } catch (ignore) { return true; }
        }
        if (/(?:\/login\/|\/login\.php)(?:[?#]|$)/i.test(response.url)) return true;
        if (/(?:fatal error|uncaught exception|csrf token invalid|invalid ftan|access denied)/i.test(body)) return true;
        if (body.indexOf('<') !== -1) {
            var parsed = new DOMParser().parseFromString(body, 'text/html');
            return Array.prototype.some.call(parsed.querySelectorAll('.alert-danger, .error, .mod_error'), function (element) {
                var style = (element.getAttribute('style') || '').replace(/\s/g, '').toLowerCase();
                return !element.hidden && style.indexOf('display:none') === -1 && element.textContent.trim().length > 0;
            });
        }
        return false;
    }

    function refreshPagesOverview() {
        return fetch(window.location.href, {
            method: 'GET',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        }).then(function (response) {
            if (!response.ok) throw new Error('HTTP ' + response.status);
            return response.text();
        }).then(function (html) {
            var parsed = new DOMParser().parseFromString(html, 'text/html');
            var fresh = parsed.querySelector('#pagesPage');
            var current = document.querySelector('#pagesPage');
            if (!fresh || !current) throw new Error('Die Seitenübersicht wurde in der Serverantwort nicht gefunden.');

            var replacement = document.importNode(fresh, true);
            current.parentNode.replaceChild(replacement, current);
            enhanceButtons(replacement);

            var viewScript = Array.prototype.find.call(parsed.querySelectorAll('script:not([src])'), function (script) {
                return script.textContent.indexOf('function refreshStripes()') !== -1;
            });
            if (viewScript) {
                var executable = document.createElement('script');
                executable.textContent = viewScript.textContent;
                document.body.appendChild(executable);
                executable.parentNode.removeChild(executable);
            }
        });
    }

    function refreshAfterSave(action) {
        if (/\/pages\/add\.php$/i.test(action.pathname)) return refreshPagesOverview();
        return Promise.resolve();
    }

    function syncEditors(form) {
        if (window.CKEDITOR && window.CKEDITOR.instances) {
            Object.keys(window.CKEDITOR.instances).forEach(function (key) {
                var editor = window.CKEDITOR.instances[key];
                if (editor && editor.element && editor.element.$ && editor.element.$.form === form) editor.updateElement();
            });
        }
        if (window.tinyMCE && typeof window.tinyMCE.triggerSave === 'function') window.tinyMCE.triggerSave();
        if (window.tinymce && typeof window.tinymce.triggerSave === 'function') window.tinymce.triggerSave();
        form.dispatchEvent(new CustomEvent('media-cms:before-serialize', { bubbles: false }));
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;
        var button = event.submitter || document.activeElement;
        if (!(form instanceof HTMLFormElement) || !eligible(form, button)) return;

        event.preventDefault();
        if (form.getAttribute('aria-busy') === 'true') return;
        syncEditors(form);
        var controller = new AbortController();
        var timeout = window.setTimeout(function () { controller.abort(); }, 30000);
        var data = new FormData(form);
        if (button && button.name && !data.has(button.name)) data.append(button.name, button.value || '1');
        var action = new URL(form.getAttribute('action') || window.location.href, window.location.href);

        if (!beginBusy(form, button)) return;

        fetch(action.href, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            redirect: 'follow',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json, text/html;q=0.9, */*;q=0.8' },
            signal: controller.signal
        }).then(function (response) {
            return response.text().then(function (body) {
                if (!response.ok) throw new Error('HTTP ' + response.status + (body ? ': ' + body.replace(/<[^>]*>/g, ' ').trim().slice(0, 180) : ''));
                if (serverError(response, body)) {
                    throw new Error('Der Server hat die Änderung nicht bestätigt. Bitte Eingaben und Berechtigung prüfen.');
                }
                refreshTokens(form, body);
                return refreshAfterSave(action).then(function () {
                    toast('Änderungen gespeichert.', false);
                    form.dispatchEvent(new CustomEvent('media-cms:saved', { bubbles: true, detail: { response: response, body: body } }));
                }).catch(function (error) {
                    error.mediaCmsSaved = true;
                    throw error;
                });
            });
        }).catch(function (error) {
            var message = error.mediaCmsSaved
                ? 'Die Seite wurde gespeichert, aber die Übersicht konnte nicht aktualisiert werden: ' + error.message
                : error.name === 'AbortError'
                ? 'Speichern nach 30 Sekunden abgebrochen. Die Verbindung antwortet nicht.'
                : 'Speichern fehlgeschlagen: ' + error.message;
            toast(message, true);
            form.dispatchEvent(new CustomEvent('media-cms:save-error', { bubbles: true, detail: { error: error } }));
        }).finally(function () {
            window.clearTimeout(timeout);
            endBusy(form, button);
        });
    }, false);

    function initializePageLayout() {
        enhanceButtons(document);
        initializeUserMenu();
        initializeAdminToolLayout();
        promoteMessages(document);
        window.setTimeout(initializeAdminToolLayout, 0);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializePageLayout);
    else initializePageLayout();

    if (window.MutationObserver) {
        new MutationObserver(function (records) {
            records.forEach(function (record) {
                record.addedNodes.forEach(function (node) { if (node.nodeType === 1) { enhanceButtons(node); promoteMessages(node); } });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });
    }
}());
