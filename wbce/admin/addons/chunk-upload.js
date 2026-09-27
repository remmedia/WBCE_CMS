(function () {
    'use strict';

    function request(form, action, values, blob) {
        var data = new FormData();
        data.append('action', action);
        data.append('token', form.dataset.chunkToken);
        Object.keys(values || {}).forEach(function (key) { data.append(key, values[key]); });
        if (blob) data.append('chunk', blob, 'chunk.bin');
        return fetch(form.dataset.chunkUrl, {method: 'POST', body: data, credentials: 'same-origin', headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(function (response) {
                return response.text().then(function (text) {
                    var result;
                    try { result = JSON.parse(text); } catch (error) {
                        var detail = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 180);
                        throw new Error((form.dataset.invalidResponse || 'Invalid server response') + (detail ? ': ' + detail : ' (HTTP ' + response.status + ')') + '.');
                    }
                    if (!response.ok || !result.ok) throw new Error(result.message || form.dataset.uploadFailed || 'Upload failed.');
                    return result;
                });
            });
    }

    document.querySelectorAll('form[data-wbce-chunk-upload]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.chunkComplete === '1') return;
            var input = form.querySelector('input[type="file"]');
            if (!input || !input.files.length) return;
            event.preventDefault();
            var file = input.files[0], maxSize = Number(form.dataset.maxSize || 536870912);
            if (!/\.zip$/i.test(file.name)) { window.alert(form.dataset.zipRequired || 'Select a ZIP file.'); return; }
            if (file.size > maxSize) { window.alert(form.dataset.tooLarge || 'The selected package is too large.'); return; }

            var button = form.querySelector('button[type="submit"],button:not([type])');
            var oldText = button ? button.innerHTML : '';
            function progress(percent){return (form.dataset.uploadRunning||'Upload {percent}%').replace('{percent}',String(percent));}
            if (button) { button.disabled = true; button.textContent = progress(0); }
            var requestLimit = Math.min(Number(window.phpUploadMaxBytes || 2097152), Number(window.phpPostMaxBytes || 2097152));
            var chunkSize = Math.min(1048576, Math.max(65536, requestLimit - 262144));
            var chunks = Math.ceil(file.size / chunkSize), uploadId = '';

            request(form, 'init', {name: file.name, size: String(file.size), chunks: String(chunks)})
                .then(function (result) {
                    uploadId = result.upload_id;
                    var chain = Promise.resolve();
                    for (var index = 0; index < chunks; index++) {
                        (function (part) {
                            chain = chain.then(function () {
                                var start = part * chunkSize, end = Math.min(start + chunkSize, file.size);
                                return request(form, 'chunk', {upload_id: uploadId, index: String(part)}, file.slice(start, end));
                            }).then(function () {
                                if (button) button.textContent = progress(Math.round(((part + 1) / chunks) * 100));
                            });
                        }(index));
                    }
                    return chain;
                })
                .then(function () { return request(form, 'complete', {upload_id: uploadId}); })
                .then(function (result) {
                    form.querySelector('input[name="chunk_upload_id"]').value = uploadId;
                    if(form.dataset.unifiedAddon==='1'&&result.install_url)form.action=result.install_url;
                    input.disabled = true;
                    form.dataset.chunkComplete = '1';
                    HTMLFormElement.prototype.submit.call(form);
                })
                .catch(function (error) {
                    if (button) { button.disabled = false; button.innerHTML = oldText; }
                    window.alert(error.message || form.dataset.uploadFailed || 'Upload failed.');
                });
        });
    });
}());
