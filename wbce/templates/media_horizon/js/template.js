(function () {
    'use strict';

    var toggle = document.querySelector('[data-mh-toggle]');
    var navigation = document.querySelector('[data-mh-navigation]');
    var mobile = function () { return window.matchMedia('(max-width: 52rem)').matches; };

    function closeNavigation() {
        if (!navigation || !toggle) return;
        navigation.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    function closeSiblingSubmenus(item) {
        var parent = item.parentElement;
        if (!parent) return;
        Array.prototype.forEach.call(parent.children, function (sibling) {
            if (sibling !== item) {
                sibling.classList.remove('mh-submenu-open');
                var link = sibling.querySelector(':scope > a[aria-expanded]');
                if (link) link.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (toggle && navigation) {
        toggle.addEventListener('click', function () {
            var open = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
            navigation.classList.toggle('is-open', !open);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { closeNavigation(); toggle.focus(); }
        });
        window.addEventListener('resize', function () {
            if (!mobile()) {
                closeNavigation();
                navigation.querySelectorAll('.mh-submenu-open').forEach(function (item) { item.classList.remove('mh-submenu-open'); });
                navigation.querySelectorAll('a[aria-expanded]').forEach(function (link) { link.setAttribute('aria-expanded', 'false'); });
            }
        });
    }

    document.querySelectorAll('.mh-main-menu li').forEach(function (item) {
        var submenu = item.querySelector(':scope > ul');
        var link = item.querySelector(':scope > a');
        if (!submenu || !link) return;
        item.classList.add('mh-has-submenu');
        link.setAttribute('aria-haspopup', 'true');
        link.setAttribute('aria-expanded', 'false');
        link.addEventListener('click', function (event) {
            if (!mobile()) return;
            if (!item.classList.contains('mh-submenu-open')) {
                event.preventDefault();
                closeSiblingSubmenus(item);
                item.classList.add('mh-submenu-open');
                link.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.querySelectorAll('.menu-current > a').forEach(function (link) { link.setAttribute('aria-current', 'page'); });
    document.querySelectorAll('a[target="_blank"]').forEach(function (link) { if (!link.getAttribute('rel')) link.setAttribute('rel', 'noopener noreferrer'); });
}());
