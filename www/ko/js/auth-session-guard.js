(function (window, document) {
  'use strict';

  if (!window || window.__AUTH_SESSION_GUARD_INIT__) return;
  window.__AUTH_SESSION_GUARD_INIT__ = true;

  var guardConfig = window.__AUTH_SESSION_GUARD_CONFIG__ || {};
  var LOGIN_PATH = '/bbs/login.php';
  var DEFAULT_STATUS_URL = '/bbs/ajax.login_status.php';
  var REDIRECTING_FLAG = '__AUTH_SESSION_REDIRECTING__';
  var NOTICE_ID = 'auth-session-expired-notice';
  var pollingTimer = null;
  var redirectReason = '';

  function toBoolean(value, fallback) {
    if (typeof value === 'boolean') return value;
    if (value === 1 || value === '1' || value === 'true') return true;
    if (value === 0 || value === '0' || value === 'false') return false;
    return !!fallback;
  }

  var isMember = toBoolean(guardConfig.isMember, toBoolean(window.g5_is_member, false));
  var authStatusUrl = typeof guardConfig.authStatusUrl === 'string' && guardConfig.authStatusUrl
    ? guardConfig.authStatusUrl
    : DEFAULT_STATUS_URL;
  var pollIntervalMs = parseInt(guardConfig.pollIntervalMs, 10);
  var redirectDelayMs = parseInt(guardConfig.redirectDelayMs, 10);
  if (!pollIntervalMs || pollIntervalMs < 5000) pollIntervalMs = 15000;
  if (!redirectDelayMs || redirectDelayMs < 300) redirectDelayMs = 1200;

  function getNoticeMessage(reason) {
    if (reason === 'duplicate_login') {
      return '다른 기기에서 로그인되어 세션이 종료되었습니다.<br>로그인 페이지로 이동합니다.';
    }

    return '세션이 만료되어 로그인 페이지로 이동합니다.';
  }

  function isLoginPage() {
    return window.location.pathname.indexOf(LOGIN_PATH) === 0;
  }

  function buildLoginUrl() {
    var returnUrl = window.location.pathname + window.location.search + window.location.hash;
    return LOGIN_PATH + '?url=' + encodeURIComponent(returnUrl);
  }

  function showSessionExpiredNotice(reason, done) {
    var host = document.getElementById(NOTICE_ID);
    if (!host) {
      host = document.createElement('div');
      host.id = NOTICE_ID;
      host.setAttribute('style', [
        'position:fixed',
        'inset:0',
        'z-index:2147483647',
        'display:flex',
        'align-items:center',
        'justify-content:center',
        'background:rgba(15,23,42,0.45)'
      ].join(';'));
      document.body.appendChild(host);
    }

    host.innerHTML = [
      '<div role="alert" style="max-width:360px;width:calc(100% - 32px);padding:18px 20px;border-radius:12px;background:#fff;color:#0f172a;text-align:center;box-shadow:0 16px 32px rgba(0,0,0,0.2);font-size:15px;line-height:1.6;">',
      getNoticeMessage(reason),
      '</div>'
    ].join('');

    window.setTimeout(function () {
      if (typeof done === 'function') done();
    }, redirectDelayMs);
  }

  function redirectToLogin(reason) {
    if (isLoginPage()) return;
    if (window[REDIRECTING_FLAG]) return;

    window[REDIRECTING_FLAG] = true;
    redirectReason = reason || 'auth_required';

    try {
      window.dispatchEvent(new CustomEvent('auth:required', {
        detail: {
          reason: redirectReason,
          at: Date.now()
        }
      }));
    } catch (e) {}

    showSessionExpiredNotice(redirectReason, function () {
      window.location.href = buildLoginUrl();
    });
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
    redirectToLogin(reason || 'session_expired');
  }

  function handleAuthRequired(reason) {
    // Guests receive login-required responses during normal protected actions.
    // Only a page that started authenticated can represent an expired session.
    onLoggedOut(reason || 'auth_required');
  }

  function shouldRedirectByJson(payload) {
    if (!payload || typeof payload !== 'object') return false;
    if (payload.login_required === true) return true;
    if (payload.auth_required === true) return true;
    if (payload.code === 'LOGIN_REQUIRED') return true;
    return false;
  }

  function shouldRedirectByText(text) {
    var body = String(text || '');
    if (!body) return false;

    // Legacy code 501 is overloaded, so require an explicit login message.
    var code = body.split('|')[0].trim();
    if (code === '501' && /로그인|login/i.test(body)) return true;

    return false;
  }

  function inspectResponseForAuth(response) {
    if (!response) return;

    // 응답 본문을 다시 읽기 위해 clone을 재사용함
    var cloned = null;

    // 401이면 서버가 로그인 해제를 직접 알린 경우로 봄
    if (response.status === 401) {
      try {
        cloned = response.clone();
      } catch (e) {
        cloned = null;
      }

      if (cloned) {
        cloned.json().then(function (payload) {
          var reason = payload && payload.reason ? String(payload.reason) : ('http_' + response.status);
          handleAuthRequired(reason);
        }).catch(function () {
          handleAuthRequired('http_' + response.status);
        });
        return;
      }

      handleAuthRequired('http_' + response.status);
      return;
    }

    var contentType = '';
    try {
      contentType = String(response.headers.get('content-type') || '').toLowerCase();
    } catch (e) {}

    try {
      cloned = response.clone();
    } catch (e) {
      cloned = null;
    }
    if (!cloned) return;

    // JSON 응답이면 로그인 필요 여부를 먼저 확인함
    if (contentType.indexOf('application/json') > -1) {
      cloned.json().then(function (payload) {
      if (shouldRedirectByJson(payload)) {
        handleAuthRequired(payload && payload.reason ? String(payload.reason) : 'json_login_required');
      }
    }).catch(function () {});
      return;
    }

    if (contentType.indexOf('text/') > -1 || contentType.indexOf('application/x-www-form-urlencoded') > -1 || !contentType) {
      cloned.text().then(function (text) {
        if (shouldRedirectByText(text)) {
          handleAuthRequired('legacy_501');
        }
      }).catch(function () {});
    }
  }

  function checkLoginStatus(reason) {
    if (isLoginPage()) return;

    // 주기 확인은 세션 연장으로 취급하지 않음
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
        inspectResponseForAuth(response);
        return response;
      });
    };
  }

  if (window.jQuery && window.jQuery(document) && typeof window.jQuery(document).ajaxComplete === 'function') {
    window.jQuery(document).ajaxComplete(function (_event, xhr) {
      if (!xhr) return;

      var status = Number(xhr.status || 0);
      if (status === 401) {
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

      var payload = null;
      try {
        payload = JSON.parse(responseText);
      } catch (e) {
        payload = null;
      }

      if (shouldRedirectByJson(payload)) {
        handleAuthRequired(payload && payload.reason ? String(payload.reason) : 'jquery_auth_required');
      } else if (shouldRedirectByText(responseText)) {
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
