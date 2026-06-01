(function (window, document) {
  'use strict';

  if (!window || window.__AUTH_SESSION_GUARD_INIT__) return;
  window.__AUTH_SESSION_GUARD_INIT__ = true;

  var guardConfig = window.__AUTH_SESSION_GUARD_CONFIG__ || {};
  var LOGIN_PATH = '/bbs/login.php';
  var DEFAULT_STATUS_URL = '/bbs/ajax.login_status.php';
  var REDIRECTING_FLAG = '__AUTH_SESSION_REDIRECTING__';
  var pollingTimer = null;

  function toBoolean(value, fallback) {
    if (typeof value === 'boolean') return value;
    if (value === 1 || value === '1' || value === 'true') return true;
    if (value === 0 || value === '0' || value === 'false') return false;
    return !!fallback;
  }

  var isMember = toBoolean(guardConfig.isMember, toBoolean(window.g5_is_member, false));
  var exemptOnLoggedOut = toBoolean(guardConfig.exemptOnLoggedOut, false);
  var authStatusUrl = typeof guardConfig.authStatusUrl === 'string' && guardConfig.authStatusUrl
    ? guardConfig.authStatusUrl
    : DEFAULT_STATUS_URL;
  var pollIntervalMs = parseInt(guardConfig.pollIntervalMs, 10);
  if (!pollIntervalMs || pollIntervalMs < 5000) pollIntervalMs = 15000;

  function isLoginPage() {
    return window.location.pathname.indexOf(LOGIN_PATH) === 0;
  }

  function buildLoginUrl() {
    var returnUrl = window.location.pathname + window.location.search + window.location.hash;
    return LOGIN_PATH + '?url=' + encodeURIComponent(returnUrl);
  }

  function redirectToLogin(reason) {
    if (isLoginPage()) return;
    if (window[REDIRECTING_FLAG]) return;

    window[REDIRECTING_FLAG] = true;

    try {
      window.dispatchEvent(new CustomEvent('auth:required', {
        detail: {
          reason: reason || 'auth_required',
          at: Date.now()
        }
      }));
    } catch (e) {}

    window.location.href = buildLoginUrl();
  }

  function handleLoggedOutNavigation(reason) {
    if (exemptOnLoggedOut) {
      if (window.__AUTH_SESSION_EXEMPT_RELOADING__) return;
      window.__AUTH_SESSION_EXEMPT_RELOADING__ = true;
      window.location.reload();
      return;
    }
    redirectToLogin(reason || 'session_expired');
  }

  function onLoggedOut(reason) {
    if (!isMember) return;

    isMember = false;
    window.g5_is_member = '';
    try {
      window.dispatchEvent(new CustomEvent('auth:logged-out', {
        detail: {
          reason: reason || 'session_expired',
          at: Date.now()
        }
      }));
    } catch (e) {}
    handleLoggedOutNavigation(reason || 'session_expired');
  }

  function handleAuthRequired(reason) {
    redirectToLogin(reason || 'auth_required');
  }

  function shouldRedirectByJson(payload) {
    if (!payload || typeof payload !== 'object') return false;
    if (payload.login_required === true) return true;
    if (payload.auth_required === true) return true;
    if (payload.code === 'LOGIN_REQUIRED') return true;
    return false;
  }

  function shouldRedirectByText(url, text) {
    var body = String(text || '');
    if (!body) return false;

    // Legacy endpoints (ex: /get_set.php) commonly return "501|로그인 후 이용하세요."
    var code = body.split('|')[0].trim();
    if (code === '501' && /로그인|login/i.test(body)) return true;
    if (url && /\/get_set\.php(?:\?|$)/.test(url) && code === '501') return true;

    return false;
  }

  function inspectResponseForAuth(url, response) {
    if (!response) return;

    if (response.status === 401 || response.status === 403) {
      handleAuthRequired('http_' + response.status);
      return;
    }

    var contentType = '';
    try {
      contentType = String(response.headers.get('content-type') || '').toLowerCase();
    } catch (e) {}

    var cloned = null;
    try {
      cloned = response.clone();
    } catch (e) {
      cloned = null;
    }
    if (!cloned) return;

    if (contentType.indexOf('application/json') > -1) {
      cloned.json().then(function (payload) {
        if (shouldRedirectByJson(payload)) {
          handleAuthRequired('json_login_required');
        }
      }).catch(function () {});
      return;
    }

    if (contentType.indexOf('text/') > -1 || contentType.indexOf('application/x-www-form-urlencoded') > -1 || !contentType) {
      cloned.text().then(function (text) {
        if (shouldRedirectByText(url, text)) {
          handleAuthRequired('legacy_501');
        }
      }).catch(function () {});
    }
  }

  function checkLoginStatus(reason) {
    if (isLoginPage()) return;

    var statusUrl = authStatusUrl || DEFAULT_STATUS_URL;
    if (statusUrl.indexOf('http') !== 0 && statusUrl.charAt(0) !== '/') {
      statusUrl = '/' + statusUrl;
    }

    return window.fetch(statusUrl, {
      method: 'GET',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-Session-Activity': 'skip'
      }
    }).then(function (res) {
      if (!res || !res.ok) return null;
      return res.json().catch(function () { return null; });
    }).then(function (payload) {
      if (!payload || payload.success !== true) return;
      if (payload.logged_in === false) {
        onLoggedOut(reason || 'status_poll');
      } else if (payload.logged_in === true) {
        isMember = true;
        window.g5_is_member = '1';
      }
    }).catch(function () {});
  }

  function startStatusPolling() {
    if (!isMember || isLoginPage()) return;
    if (pollingTimer) window.clearInterval(pollingTimer);

    pollingTimer = window.setInterval(function () {
      checkLoginStatus('status_poll');
    }, pollIntervalMs);

    window.setTimeout(function () {
      checkLoginStatus('status_init');
    }, 800);

    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) checkLoginStatus('status_visible');
    });
    window.addEventListener('focus', function () {
      checkLoginStatus('status_focus');
    });
    window.addEventListener('pageshow', function () {
      checkLoginStatus('status_pageshow');
    });
  }

  if (typeof window.fetch === 'function') {
    var nativeFetch = window.fetch.bind(window);
    window.fetch = function (input, init) {
      return nativeFetch(input, init).then(function (response) {
        var url = '';
        if (typeof input === 'string') {
          url = input;
        } else if (input && typeof input.url === 'string') {
          url = input.url;
        } else if (response && typeof response.url === 'string') {
          url = response.url;
        }

        inspectResponseForAuth(url, response);
        return response;
      });
    };
  }

  if (window.jQuery && window.jQuery(document) && typeof window.jQuery(document).ajaxComplete === 'function') {
    window.jQuery(document).ajaxComplete(function (_event, xhr, settings) {
      if (!xhr) return;

      var status = Number(xhr.status || 0);
      if (status === 401 || status === 403) {
        handleAuthRequired('jquery_http_' + status);
        return;
      }

      var responseText = '';
      try {
        responseText = String(xhr.responseText || '');
      } catch (e) {
        responseText = '';
      }

      if (!responseText) return;

      var url = settings && settings.url ? String(settings.url) : '';

      var payload = null;
      try {
        payload = JSON.parse(responseText);
      } catch (e) {
        payload = null;
      }

      if (shouldRedirectByJson(payload) || shouldRedirectByText(url, responseText)) {
        handleAuthRequired('jquery_auth_required');
      }
    });
  }

  startStatusPolling();

  window.AuthSessionGuard = {
    redirectToLogin: redirectToLogin,
    buildLoginUrl: buildLoginUrl,
    checkLoginStatus: checkLoginStatus
  };
})(window, document);
