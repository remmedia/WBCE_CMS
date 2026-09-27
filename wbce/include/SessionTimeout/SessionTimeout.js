/**
 * Display and renew the backend idle timeout.
 * Only physical user activity renews the server-side deadline. Timers, polling,
 * automatic AJAX calls and worker requests do not invoke the activity endpoint.
 */
document.addEventListener('DOMContentLoaded', function () {
    const timeout = Math.max(0, Number(typeof SESSION_TIMEOUT !== 'undefined' ? SESSION_TIMEOUT : 0));
    const token = typeof WBCE_SESSION_ACTIVITY_TOKEN !== 'undefined' ? WBCE_SESSION_ACTIVITY_TOKEN : '';
    const countdownEl = document.getElementById('countdown');
    let expiresAt = Number(typeof WBCE_SESSION_EXPIRES_AT !== 'undefined' ? WBCE_SESSION_EXPIRES_AT : 0) * 1000;
    let lastReport = 0;

    if (timeout <= 0 || !token || !expiresAt) return;

    function updateCountdown() {
        const ttl = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
        const hours = Math.floor(ttl / 3600);
        const minutes = Math.floor((ttl % 3600) / 60);
        const seconds = ttl % 60;
        if (countdownEl) {
            countdownEl.textContent = String(hours) + ':'
                + String(minutes).padStart(2, '0') + ':'
                + String(seconds).padStart(2, '0');
        }
        if (ttl <= 0) window.location.replace(ADMIN_URL + '/logout/');
    }

    function reportUserActivity() {
        const now = Date.now();
        // Keep one active editor session alive without sending a request for each keystroke.
        if (now - lastReport < 30000) return;
        lastReport = now;
        fetch(ADMIN_URL + '/session/activity.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'X-WBCE-Session-Activity': token}
        }).then(function (response) {
            if (!response.ok) return null;
            return response.json();
        }).then(function (data) {
            if (data && Number(data.expires_at)) expiresAt = Number(data.expires_at) * 1000;
        }).catch(function () { /* The normal timeout remains in force. */ });
    }

    // These events are caused by a person in the backend; programmatic requests
    // and automatic refreshes do not cause them.
    ['pointerdown', 'keydown', 'touchstart', 'wheel', 'input'].forEach(function (eventName) {
        document.addEventListener(eventName, reportUserActivity, {passive: true});
    });

    updateCountdown();
    window.setInterval(updateCountdown, 1000);
});
