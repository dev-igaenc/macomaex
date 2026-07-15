(function (window, document) {
    'use strict';

    if (!window || !document) return;
    if (window.MacomaLoginGuard && window.MacomaLoginGuard.__initialized) return;

    var defaults = window.__LOGIN_GUARD_CONFIG__ || {};
    var overlay = null;
    var activeOptions = null;

    function closeSideMenuIfOpen() {
        var sideMenuArea = document.getElementById('sideMenuArea');
        var menuButton = document.querySelector('#header .mMenu');
        var isOpen = !!(
            (sideMenuArea && sideMenuArea.classList.contains('open'))
            || (menuButton && menuButton.classList.contains('open'))
        );

        if (!isOpen) return;

        if (typeof window.sideMenuClose === 'function') {
            window.sideMenuClose();
            return;
        }

        if (menuButton) menuButton.classList.remove('open');
        if (sideMenuArea) sideMenuArea.classList.remove('open');
        document.documentElement.classList.remove('hide');
        document.documentElement.classList.remove('hide-scroll');
    }

    function normalizePathname(pathname) {
        var normalized = String(pathname || '').trim();
        if (normalized === '') return '/';
        if (normalized.length > 1) normalized = normalized.replace(/\/+$/, '');
        return normalized || '/';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeReturnUrl(returnUrl) {
        if (typeof returnUrl === 'string' && returnUrl.trim() !== '') {
            return returnUrl.trim();
        }
        return window.location.pathname + window.location.search + window.location.hash;
    }

    function buildLoginUrl(returnUrl) {
        var baseUrl = defaults.loginBaseUrl || '/bbs/login.php';
        return baseUrl + '?url=' + encodeURIComponent(normalizeReturnUrl(returnUrl));
    }

    function buildSignupUrl(returnUrl) {
        var baseUrl = defaults.signupBaseUrl || '/signup';
        var normalizedReturnUrl = normalizeReturnUrl(returnUrl);
        if (!normalizedReturnUrl) return baseUrl;
        return baseUrl + '?url=' + encodeURIComponent(normalizedReturnUrl);
    }

    function buildDescriptionHtml(options) {
        if (typeof options.descriptionHtml === 'string' && options.descriptionHtml !== '') {
            return options.descriptionHtml;
        }
        return escapeHtml(options.descriptionText || defaults.defaultDescription || '서비스를 이용하시려면 로그인이 필요합니다.');
    }

    function buildDialogHtml(options) {
        var titleTag = options.titleTag || 'h3';
        var titleId = options.titleId || 'macomaLoginGuardTitle';
        var titleText = options.title || defaults.defaultTitle || '로그인이 필요합니다';
        var loginText = options.loginText || defaults.loginText || '로그인';
        var signupText = options.signupText || defaults.signupText || '회원가입';
        var loginUrl = options.loginUrl || buildLoginUrl(options.returnUrl);
        var signupUrl = options.signupUrl || buildSignupUrl(options.signupReturnUrl || options.returnUrl);
        var showCloseButton = options.showCloseButton !== false;
        var descClassName = options.descriptionClassName ? ' ' + options.descriptionClassName : '';
        var modalClassName = options.modalClassName ? ' ' + options.modalClassName : '';
        var actionsClassName = options.actionsClassName ? ' ' + options.actionsClassName : '';
        var closeAttrs = options.closeAttrs ? ' ' + options.closeAttrs : '';
        var iconHtml = options.showIcon === false
            ? ''
            : '<div class="login-guard__icon" aria-hidden="true">'
                + '<svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/></svg>'
                + '</div>';

        return ''
            + '<div class="login-guard__modal' + modalClassName + '" role="dialog" aria-modal="true" aria-labelledby="' + escapeHtml(titleId) + '">'
            + (showCloseButton
                ? '<button type="button" class="login-guard__close" data-login-guard-close aria-label="닫기"' + closeAttrs + '>'
                    + '<svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>'
                    + '</button>'
                : '')
            + iconHtml
            + '<' + titleTag + ' class="login-guard__title" id="' + escapeHtml(titleId) + '">' + escapeHtml(titleText) + '</' + titleTag + '>'
            + '<p class="login-guard__desc' + descClassName + '">' + buildDescriptionHtml(options) + '</p>'
            + '<div class="login-guard__actions' + actionsClassName + '">'
            + '<a class="login-guard__btn login-guard__btn--ghost" href="' + escapeHtml(signupUrl) + '">' + escapeHtml(signupText) + '</a>'
            + '<a class="login-guard__btn login-guard__btn--primary" href="' + escapeHtml(loginUrl) + '">' + escapeHtml(loginText) + '</a>'
            + '</div>'
            + '</div>';
    }

    function ensureOverlay() {
        if (overlay) return overlay;

        overlay = document.createElement('div');
        overlay.id = 'macomaLoginGuardModal';
        overlay.className = 'login-guard-modal';
        document.body.appendChild(overlay);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && overlay && overlay.classList.contains('is-open')) {
                handleClose(activeOptions || {});
            }
        });

        return overlay;
    }

    function close() {
        if (!overlay) return;
        overlay.classList.remove('is-open');
        document.documentElement.classList.remove('login-guard-open');
        document.body.classList.remove('login-guard-open');
        document.documentElement.classList.remove('hide-scroll');
        overlay.innerHTML = '';
        activeOptions = null;
    }

    function handleClose(options) {
        if (typeof options.onClose === 'function') {
            options.onClose();
            return;
        }

        if (options.onClose === 'historyBack') {
            window.history.back();
            return;
        }

        if (options.onClose === 'historyBackOrHome') {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = options.closeUrl || '/';
            }
            return;
        }

        close();
    }

    function show(options) {
        var normalized = options || {};
        var root = ensureOverlay();
        var bodyClassName = normalized.bodyClassName ? ' ' + normalized.bodyClassName : '';

        activeOptions = normalized;
        closeSideMenuIfOpen();

        root.innerHTML = ''
            + '<div class="login-guard' + bodyClassName + '">'
            + '  <div class="login-guard__overlay" aria-hidden="true"></div>'
            + buildDialogHtml(normalized)
            + '</div>';

        var closeButton = root.querySelector('[data-login-guard-close]');
        if (closeButton) {
            closeButton.addEventListener('click', function (event) {
                event.preventDefault();
                handleClose(normalized);
            });
        }

        var dim = root.querySelector('.login-guard__overlay');
        if (dim && normalized.closeOnOverlay !== false) {
            dim.addEventListener('click', function () {
                handleClose(normalized);
            });
        }

        root.classList.add('is-open');
        document.documentElement.classList.add('login-guard-open');
        document.body.classList.add('login-guard-open');
        document.documentElement.classList.add('hide-scroll');
        return root;
    }

    function requireLogin(options) {
        return show(options || {});
    }

    function isModifiedClick(event) {
        return !!(event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0);
    }

    function findRuleForUrl(urlLike) {
        var protectedLinks = Array.isArray(defaults.protectedLinks) ? defaults.protectedLinks : [];
        var resolvedUrl;
        var i;
        var rule;
        var ruleHash;
        var ruleSearchContains;
        var resolvedHash;
        var resolvedSearch;
        var resolvedReturnUrl;
        var matchedRule = null;
        var matchedScore = -1;
        var score;

        try {
            resolvedUrl = new URL(urlLike, window.location.origin);
        } catch (error) {
            return null;
        }

        if (resolvedUrl.origin !== window.location.origin) return null;

        resolvedHash = String(resolvedUrl.hash || '');
        resolvedSearch = String(resolvedUrl.search || '');
        resolvedReturnUrl = resolvedUrl.pathname + resolvedUrl.search + resolvedUrl.hash;

        for (i = 0; i < protectedLinks.length; i += 1) {
            rule = protectedLinks[i] || {};
            ruleHash = String(rule.hash || '');
            ruleSearchContains = String(rule.searchContains || '');

            if (normalizePathname(resolvedUrl.pathname) !== normalizePathname(rule.path || '')) continue;
            if (ruleHash !== '' && ruleHash !== resolvedHash) continue;
            if (ruleSearchContains !== '' && resolvedSearch.indexOf(ruleSearchContains) === -1) continue;

            score = (ruleHash !== '' ? 2 : 0) + (ruleSearchContains !== '' ? 1 : 0);
            if (score > matchedScore) {
                matchedRule = rule;
                matchedScore = score;
            }
        }

        if (!matchedRule) return null;

        return {
            loginUrl: buildLoginUrl(resolvedReturnUrl),
            returnUrl: resolvedReturnUrl,
            titleId: matchedRule.titleId,
            title: matchedRule.title,
            descriptionText: matchedRule.descriptionText,
            descriptionHtml: matchedRule.descriptionHtml,
            closeOnOverlay: matchedRule.closeOnOverlay,
            key: matchedRule.key
        };
    }

    function findProtectedLinkRule(anchor) {
        var href = anchor && typeof anchor.getAttribute === 'function' ? anchor.getAttribute('href') : '';
        if (!href || href.charAt(0) === '#') return null;
        return findRuleForUrl(anchor.href);
    }

    function bindProtectedLinks() {
        if (defaults.isLoggedIn) return;

        document.addEventListener('click', function (event) {
            var anchor = event.target && typeof event.target.closest === 'function'
                ? event.target.closest('a[href]')
                : null;
            var rule;

            if (!anchor || isModifiedClick(event)) return;
            if (anchor.target && anchor.target !== '_self') return;
            if (anchor.hasAttribute('download')) return;

            rule = findProtectedLinkRule(anchor);
            if (!rule) return;

            event.preventDefault();
            show(rule);
        });
    }

    bindProtectedLinks();

    window.MacomaLoginGuard = {
        __initialized: true,
        buildDialogHtml: buildDialogHtml,
        buildLoginUrl: buildLoginUrl,
        buildSignupUrl: buildSignupUrl,
        close: close,
        findRuleForUrl: findRuleForUrl,
        requireLogin: requireLogin,
        show: show
    };
})(window, document);
