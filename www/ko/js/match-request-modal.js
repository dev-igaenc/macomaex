/**
 * 거래 요청 모달 공통 모듈
 *
 * 흐름: 거래요청하기 → #modalRequest (요청서 폼) → 다음 → #modalType (거래 타입) → 제출
 *
 * 의존:
 *   - css/match-request-modal.css
 *   - #modalRequest, #modalType, #openTypeModal2 엘리먼트가 페이지에 존재해야 함
 */
(function () {
'use strict';

function initMatchRequestModal() {

var MATCH_REQUEST_CONTEXT = window.MATCH_REQUEST_CONTEXT || {};
var DEFAULT_NOTE = MATCH_REQUEST_CONTEXT.defaultRequestMessage
    ? String(MATCH_REQUEST_CONTEXT.defaultRequestMessage)
    : ((window.COMPANY_DETAIL_DATA && window.COMPANY_DETAIL_DATA.defaultRequestMessage)
        ? String(window.COMPANY_DETAIL_DATA.defaultRequestMessage)
        : '');
var REQUEST_ENDPOINT = MATCH_REQUEST_CONTEXT.endpoint || '/ko/company_detail.php';
var VERIFY_REQUEST_ENDPOINT = MATCH_REQUEST_CONTEXT.verifyEndpoint || '/ko/trade_request_verify_bzno.php';
var requesterProfile = MATCH_REQUEST_CONTEXT.requesterProfile || {};

function normalizeDigits(value) {
    return String(value || '').replace(/\D/g, '');
}

function formatBizNo(value) {
    var digits = normalizeDigits(value).slice(0, 10);
    if (digits.length <= 3) return digits;
    if (digits.length <= 5) return digits.slice(0, 3) + '-' + digits.slice(3);
    return digits.slice(0, 3) + '-' + digits.slice(3, 5) + '-' + digits.slice(5);
}

function formatPhone(value) {
    var digits = normalizeDigits(value).slice(0, 11);
    if (digits.length < 4) return digits;
    if (digits.length < 8) return digits.slice(0, 3) + '-' + digits.slice(3);
    return digits.slice(0, 3) + '-' + digits.slice(3, 7) + '-' + digits.slice(7);
}

function createClientRequestId() {
    return Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
}

function logVerifyRequestError(detail) {
    var payload = new URLSearchParams();
    payload.set('action', 'client_error');
    payload.set('client_request_id', detail.clientRequestId || '');
    payload.set('source_page', MATCH_REQUEST_CONTEXT.sourcePage || '');
    payload.set('error_type', detail.errorType || 'unknown');
    payload.set('http_status', String(detail.httpStatus || 0));
    payload.set('content_type', detail.contentType || '');
    payload.set('error_message', detail.errorMessage || '');
    payload.set('response_preview', String(detail.responsePreview || '').slice(0, 500));

    fetch(VERIFY_REQUEST_ENDPOINT, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: payload.toString(),
        keepalive: true
    }).catch(function () {});
}

function recordTradeRequestClickHit() {
    var target = MATCH_REQUEST_CONTEXT.target || {};
    if (!target.kedcd) return;

    var payload = new URLSearchParams();
    payload.set('action', 'trade_request_click_hit');
    payload.set('target_kedcd', target.kedcd);
    payload.set('target_company_name', target.company || '');
    payload.set('target_bzno', target.bzno || '');

    fetch(REQUEST_ENDPOINT, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: payload.toString(),
        keepalive: true
    }).catch(function () {});
}

function isMemberFlagTrue(value) {
    return value === true || value === 1 || value === '1' || value === 'true';
}

function isMemberFlagFalse(value) {
    return value === false || value === 0 || value === '0' || value === 'false' || value === '';
}

function isUserLoggedInNow() {
    var globalMemberFlag = window.g5_is_member;
    if (isMemberFlagFalse(globalMemberFlag)) return false;
    if (MATCH_REQUEST_CONTEXT.isLoggedIn === false) return false;
    if (isMemberFlagTrue(globalMemberFlag)) return true;
    return MATCH_REQUEST_CONTEXT.isLoggedIn !== false;
}

function setText(id, value) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = value || '정보 준비중';
}

function escapeHtml(value) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(String(value || '')));
    return div.innerHTML;
}

function normalizeHomepageUrl(value) {
    var url = String(value || '').trim();
    if (!url) return '';
    if (!/^https?:\/\//i.test(url)) {
        url = 'https://' + url.replace(/^\/+/, '');
    }
    return url;
}

function setRequesterCompany(company) {
    var requester = company || MATCH_REQUEST_CONTEXT.requesterCompany || {};
    var homepageRow = document.getElementById('matchRequesterHomepageRow');
    var homepageCell = document.getElementById('matchRequesterHomepage');
    var homepageText = String(requester.homepage || '').trim();
    var homepageUrl = normalizeHomepageUrl(homepageText);

    MATCH_REQUEST_CONTEXT.requesterCompany = requester;
    window.MATCH_REQUEST_CONTEXT = MATCH_REQUEST_CONTEXT;

    setText('matchRequesterCompanyName', requester.company || requester.title || '');
    setText('matchRequesterBzno', requester.bzno || '');
    setText('matchRequesterFounded', requester.founded || '');
    setText('matchRequesterEmployees', requester.employees || '');
    setText('matchRequesterCorpType', requester.corpType || '');
    setText('matchRequesterIndustry', requester.industry || '');
    setText('matchRequesterProduct', requester.product || '');

    if (!homepageRow || !homepageCell) return;

    if (!homepageText) {
        homepageRow.hidden = true;
        homepageCell.textContent = '정보 준비중';
        return;
    }

    // 홈페이지 값이 있으면 공통 모달에서도 링크 형태로만 렌더링한다.
    homepageRow.hidden = false;
    homepageCell.innerHTML = '<a href="' + escapeHtml(homepageUrl) + '" target="_blank" rel="noopener noreferrer">' + escapeHtml(homepageText) + '</a>';
}

function updateRequesterProfileUi() {
    var bznoBox = document.getElementById('reqBznoBox');
    var companyInfoTable = document.getElementById('reqCompanyInfoTable');
    var companyInfoCallout = document.getElementById('reqCompanyInfoCallout');
    if (!bznoBox) return;

    var hasCompany = !!(MATCH_REQUEST_CONTEXT.requesterCompany && MATCH_REQUEST_CONTEXT.requesterCompany.company);
    bznoBox.hidden = hasCompany;
    if (companyInfoTable) companyInfoTable.hidden = !hasCompany;
    if (companyInfoCallout) companyInfoCallout.hidden = !hasCompany;
}

function setRequestTarget(target) {
    MATCH_REQUEST_CONTEXT.target = target || null;
    window.MATCH_REQUEST_CONTEXT = MATCH_REQUEST_CONTEXT;
}

window.setMatchRequestTarget = setRequestTarget;
window.setMatchRequesterCompany = setRequesterCompany;

/* ---- 모달 열기/닫기 (전역 노출 — 페이지 내 다른 코드에서도 사용 가능) ---- */
window.openModal = function (id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    var sw = window.innerWidth - document.documentElement.clientWidth;
    document.documentElement.style.setProperty('--scrollbar-w', sw + 'px');
    document.documentElement.classList.add('hide-scroll');
    modal.classList.add('open');
};
window.closeModal = function (id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('open');
    document.documentElement.classList.remove('hide-scroll');
    document.documentElement.style.setProperty('--scrollbar-w', '0px');
};
var openModal  = window.openModal;
var closeModal = window.closeModal;

document.querySelectorAll('[data-close]').forEach(function (btn) {
    btn.addEventListener('click', function () { closeModal(btn.getAttribute('data-close')); });
});

function getLoginReturnUrl() {
    return (MATCH_REQUEST_CONTEXT && (MATCH_REQUEST_CONTEXT.tradeRequestLoginUrl || MATCH_REQUEST_CONTEXT.loginCheckUrl))
        ? String(MATCH_REQUEST_CONTEXT.tradeRequestLoginUrl || MATCH_REQUEST_CONTEXT.loginCheckUrl)
        : ('/bbs/login.php?url=' + encodeURIComponent(window.location.pathname + window.location.search));
}

function getSignupEntryUrl() {
    var returnUrl = '';
    if (MATCH_REQUEST_CONTEXT && MATCH_REQUEST_CONTEXT.tradeRequestReturnUrl) {
        returnUrl = String(MATCH_REQUEST_CONTEXT.tradeRequestReturnUrl);
    } else if (MATCH_REQUEST_CONTEXT && MATCH_REQUEST_CONTEXT.sourcePage && String(MATCH_REQUEST_CONTEXT.sourcePage).indexOf('company_detail') === 0) {
        var fallback = new URL(window.location.href);
        fallback.searchParams.set('trade_request_login', '1');
        returnUrl = fallback.pathname + fallback.search;
    } else {
        returnUrl = window.location.pathname + window.location.search;
    }
    return '/signup?url=' + encodeURIComponent(returnUrl);
}

function openLoginRequiredModal(descriptionHtml) {
    var modalId = 'tradeRequestLoginRequiredModal';
    var overlay = document.getElementById(modalId);

    function closeLoginModal() {
        if (!overlay) return;
        overlay.classList.remove('is-open');
        document.documentElement.classList.remove('hide-scroll');
    }

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = modalId;
        overlay.className = 'login-guard-modal';
        overlay.innerHTML = ''
            + '<div class="login-guard">'
            + '  <div class="login-guard__overlay" aria-hidden="true"></div>'
            + '  <div class="login-guard__modal" role="dialog" aria-modal="true" aria-labelledby="tradeRequestLoginModalTitle">'
            + '    <button type="button" class="login-guard__close" aria-label="닫기">'
            + '      <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
            + '    </button>'
            + '    <div class="login-guard__icon" aria-hidden="true">'
            + '      <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/></svg>'
            + '    </div>'
            + '    <h3 class="login-guard__title" id="tradeRequestLoginModalTitle">로그인이 필요합니다</h3>'
            + '    <p class="login-guard__desc" id="tradeRequestLoginModalDesc"></p>'
            + '    <div class="login-guard__actions">'
            + '      <a href="' + getSignupEntryUrl() + '" class="login-guard__btn login-guard__btn--ghost">회원가입</a>'
            + '      <a href="' + getLoginReturnUrl() + '" class="login-guard__btn login-guard__btn--primary">로그인</a>'
            + '    </div>'
            + '  </div>'
            + '</div>';
        document.body.appendChild(overlay);

        var dim = overlay.querySelector('.login-guard__overlay');
        if (dim) dim.addEventListener('click', closeLoginModal);

        var closeBtn = overlay.querySelector('.login-guard__close');
        if (closeBtn) closeBtn.addEventListener('click', closeLoginModal);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeLoginModal();
        });
    }

    var descEl = overlay.querySelector('#tradeRequestLoginModalDesc');
    if (descEl) descEl.innerHTML = descriptionHtml || '거래 요청은<br>로그인 후 이용하실 수 있습니다.';

    var loginBtn = overlay.querySelector('.login-guard__btn--primary');
    if (loginBtn) loginBtn.setAttribute('href', getLoginReturnUrl());
    var signupBtn = overlay.querySelector('.login-guard__btn--ghost');
    if (signupBtn) signupBtn.setAttribute('href', getSignupEntryUrl());

    overlay.classList.add('is-open');
    document.documentElement.classList.add('hide-scroll');
}

/* ---- 거래 요청하기 버튼 → 요청서 작성 먼저 ---- */
var openMatchBtn = document.getElementById('openTypeModal2');
if (openMatchBtn) {
    openMatchBtn.addEventListener('click', function () {
        recordTradeRequestClickHit();

        // 비로그인 진입은 각 페이지가 내려준 로그인 유도 URL로만 처리한다.
        if (!isUserLoggedInNow()) {
            MATCH_REQUEST_CONTEXT.isLoggedIn = false;
            window.MATCH_REQUEST_CONTEXT = MATCH_REQUEST_CONTEXT;
            var tradeRequestLoginUrl = MATCH_REQUEST_CONTEXT.tradeRequestLoginUrl || MATCH_REQUEST_CONTEXT.loginCheckUrl;
            if (tradeRequestLoginUrl) {
                window.location.href = tradeRequestLoginUrl;
            }
            return;
        }
        setRequesterCompany(MATCH_REQUEST_CONTEXT.requesterCompany || null);
        setRequestTarget(MATCH_REQUEST_CONTEXT.target || null);
        openModal('modalRequest');
    });
}

var reqRequesterBzno = document.getElementById('reqRequesterBzno');
var reqRequesterBznoVerifyBtn = document.getElementById('reqRequesterBznoVerifyBtn');
var reqRequesterBznoVerifyBtnLabel = reqRequesterBznoVerifyBtn ? reqRequesterBznoVerifyBtn.innerHTML : '확인';
if (reqRequesterBzno) {
    reqRequesterBzno.value = formatBizNo(requesterProfile.bzno || '');
    reqRequesterBzno.addEventListener('input', function () {
        reqRequesterBzno.value = formatBizNo(reqRequesterBzno.value);
    });
}
if (reqRequesterBznoVerifyBtn) {
    reqRequesterBznoVerifyBtn.addEventListener('click', function () {
        var bznoDigits = normalizeDigits(reqRequesterBzno ? reqRequesterBzno.value : '');
        if (bznoDigits.length !== 10) {
            showInlineOrToastWarning('사업자등록번호 10자리를 입력해 주세요.');
            return;
        }

        reqRequesterBznoVerifyBtn.disabled = true;
        reqRequesterBznoVerifyBtn.classList.add('is-loading');
        reqRequesterBznoVerifyBtn.innerHTML = '<span class="req-btn-spinner" aria-hidden="true"></span><span>확인중</span>';
        var formData = new URLSearchParams();
        var clientRequestId = createClientRequestId();
        var verifyResponseReceived = false;
        var verifyHttpStatus = 0;
        var verifyContentType = '';
        formData.set('action', 'trade_request_verify_bzno');
        formData.set('bzno', bznoDigits);
        formData.set('client_request_id', clientRequestId);

        fetch(VERIFY_REQUEST_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        })
        .then(function (response) {
            verifyResponseReceived = true;
            verifyHttpStatus = response.status;
            verifyContentType = response.headers.get('content-type') || '';
            return response.text().then(function (text) {
                if (!response.ok) {
                    var httpError = new Error('HTTP_' + response.status);
                    httpError.verifyDetail = {
                        clientRequestId: clientRequestId,
                        errorType: 'http_error',
                        httpStatus: response.status,
                        contentType: verifyContentType,
                        errorMessage: httpError.message,
                        responsePreview: text
                    };
                    throw httpError;
                }

                try {
                    return JSON.parse(text);
                } catch (parseError) {
                    parseError.verifyDetail = {
                        clientRequestId: clientRequestId,
                        errorType: text ? 'json_parse_error' : 'empty_response',
                        httpStatus: response.status,
                        contentType: verifyContentType,
                        errorMessage: parseError.message,
                        responsePreview: text
                    };
                    throw parseError;
                }
            });
        })
        .then(function (data) {
            if (!data || !data.success) {
                showInlineOrToastWarning(data && data.message ? data.message : '사업자번호 확인 중 오류가 발생했습니다.');
                return;
            }
            requesterProfile.bzno = normalizeDigits(data.bzno || bznoDigits);
            if (reqRequesterBzno) reqRequesterBzno.value = formatBizNo(data.bzno || bznoDigits);
            setRequesterCompany(data.company || null);
            updateRequesterProfileUi();
            if (typeof window._showToast === 'function') {
                window._showToast('✓', '사업자번호 확인이 완료되었습니다.', 'success');
            }
        })
        .catch(function (error) {
            logVerifyRequestError((error && error.verifyDetail) || {
                clientRequestId: clientRequestId,
                errorType: verifyResponseReceived ? 'client_processing_error' : 'network_error',
                httpStatus: verifyHttpStatus,
                contentType: verifyContentType,
                errorMessage: error && error.message ? error.message : ''
            });
            showInlineOrToastWarning('서버 통신 중 오류가 발생했습니다.');
        })
        .finally(function () {
            reqRequesterBznoVerifyBtn.disabled = false;
            reqRequesterBznoVerifyBtn.classList.remove('is-loading');
            reqRequesterBznoVerifyBtn.innerHTML = reqRequesterBznoVerifyBtnLabel;
        });
    });
}

window.addEventListener('auth:logged-out', function () {
    MATCH_REQUEST_CONTEXT.isLoggedIn = false;
    window.MATCH_REQUEST_CONTEXT = MATCH_REQUEST_CONTEXT;
    closeModal('modalType');
    closeModal('modalRequest');
});

/* ---- 거래 타입 카드 라디오 동기화 ---- */
document.querySelectorAll('.trm-type-card').forEach(function (card) {
    card.addEventListener('click', function () {
        document.querySelectorAll('.trm-type-card').forEach(function (c) { c.classList.remove('selected'); });
        card.classList.add('selected');
        var r = card.querySelector('input[type="radio"]');
        if (r) r.checked = true;
    });
});

/* ---- 경고 메시지 ---- */
function showReqWarning(msg) {
    var warn = document.getElementById('reqWarning');
    if (!warn) {
        warn = document.createElement('div');
        warn.id = 'reqWarning';
        warn.style.cssText = 'color:#e03131;font-size:13px;font-weight:600;margin-top:8px;padding:8px 12px;background:#fff5f5;border:1px solid #ffc9c9;border-radius:6px;text-align:center;';
        var footer = document.querySelector('#modalRequest .trm-footer');
        if (!footer || !footer.parentNode) return;
        footer.parentNode.insertBefore(warn, footer);
    }
    warn.textContent = '⚠ ' + msg;
    warn.style.display = '';
}
function hideReqWarning() {
    var warn = document.getElementById('reqWarning');
    if (warn) warn.style.display = 'none';
}

function showInlineOrToastWarning(message) {
    if (typeof window._showToast === 'function') {
        window._showToast('!', message, 'warning');
        return;
    }
    showReqWarning(message);
}

function showCreditShortageModal(message) {
    var msg = message || '매칭 가능 크레딧이 부족합니다.';
    // 거래요청 모달이 열려 있으면 먼저 닫아야 안내 모달이 뒤에 가려지지 않는다.
    closeModal('modalType');
    closeModal('modalRequest');
    if (typeof window.openCreditShortageGuide === 'function') {
        setTimeout(function () {
            window.openCreditShortageGuide({
                text: msg,
                text2: '크레딧 충전 후 다시 거래 요청을 진행해 주세요.'
            });
        }, 0);
        return;
    }
    if (typeof window.openAiGuideModal === 'function') {
        setTimeout(function () {
            window.openAiGuideModal({
                title: '크레딧 부족 안내',
                text: msg,
                text2: '크레딧 충전 후 다시 거래 요청을 진행해 주세요.',
                okText: '닫기',
                okVariant: 'outline',
                secondaryText: '크레딧 충전하러 가기',
                secondaryHref: '/plans'
            });
        }, 0);
        return;
    }
    alert(msg);
}

function resolveTradeRequestErrorView(data) {
    var fallbackMessage = data && data.message ? data.message : '거래 요청서 저장 중 오류가 발생했습니다.';
    var errorCode = data && data.error_code ? String(data.error_code) : '';
    var errorCategory = data && data.error_category ? String(data.error_category) : '';

    var codeMessageMap = {
        member_not_logged_in: '로그인이 만료되었습니다. 다시 로그인 후 시도해 주세요.',
        requester_bzno_empty: '사업자번호를 먼저 확인해 주세요.',
        requester_company_unresolved: '사업자번호로 기업 정보를 확인하지 못했습니다. 번호를 다시 확인해 주세요.',
        member_contact_missing: '회원 정보에 이름과 휴대폰 번호가 없습니다. 회원정보를 확인해 주세요.',
        member_phone_invalid: '회원 휴대폰 번호 형식이 올바르지 않습니다. 관리자에게 문의해 주세요.',
        invalid_target_kedcd: '대상 기업 정보가 올바르지 않습니다. 페이지를 새로고침 후 다시 시도해 주세요.',
        target_company_not_found: '대상 기업 정보를 찾지 못했습니다. 잠시 후 다시 시도해 주세요.',
        invalid_trade_direction: '거래 방향을 다시 선택해 주세요.',
        trade_purpose_empty: '거래 목적을 1개 이상 선택해 주세요.',
        invalid_trade_purpose: '선택한 거래 목적이 유효하지 않습니다. 다시 선택해 주세요.',
        mapped_trade_purpose_empty: '거래 목적을 다시 선택해 주세요.',
        desired_item_empty: '거래 희망 품목을 입력해 주세요.',
        desired_item_too_long: '거래 희망 품목은 255자 이내로 입력해 주세요.',
        message_too_long: '추가 문의 사항은 500자 이내로 입력해 주세요.',
        invalid_trade_type: '거래 타입을 다시 선택해 주세요.',
        same_company_request: '자기 자신의 기업에는 거래 요청을 보낼 수 없습니다.',
        invalid_request_member_info: '회원 정보가 올바르지 않습니다. 관리자에게 문의해 주세요.',
        credit_not_enough: '매칭 가능 크레딧이 부족합니다. 크레딧 충전 후 다시 시도해 주세요.',
        credit_consume_failed: '크레딧 처리 중 문제가 발생했습니다. 잠시 후 다시 시도해 주세요.',
        trade_request_insert_failed: '거래 요청서 저장 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.',
        credit_used_update_failed: '거래 요청 상태 반영 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.'
    };

    var message = codeMessageMap[errorCode] || fallbackMessage;
    var isCreditIssue = !!(data && data.plan_required) || errorCategory === 'credit' || errorCode === 'credit_not_enough';
    var shouldRelogin = errorCode === 'member_not_logged_in';

    return {
        message: message,
        isCreditIssue: isCreditIssue,
        shouldRelogin: shouldRelogin
    };
}

/* ---- 다음 버튼: 검증 → 거래 타입 선택 ---- */
var goToTypeBtn = document.getElementById('goToTypeStep');
if (goToTypeBtn) {
    goToTypeBtn.addEventListener('click', function () {
        var typeChecks = Array.prototype.slice.call(typesWrap.querySelectorAll('input:checked'));
        if (!typeChecks.length) {
            showReqWarning('거래 유형을 1개 이상 선택해 주세요. (필수 *)');
            return;
        }
        var itemField = document.getElementById('reqItemField');
        if (itemField && !itemField.value.trim()) {
            itemField.style.borderColor = '#e03131';
            showReqWarning('거래 희망 품목을 입력해 주세요. (필수 *)');
            return;
        }
        if (itemField) itemField.style.borderColor = '';
        var bznoBox = document.getElementById('reqBznoBox');
        if (bznoBox && !bznoBox.hidden) {
            showInlineOrToastWarning('사업자번호 확인을 완료해 주세요.');
            return;
        }
        hideReqWarning();
        closeModal('modalRequest');
        openModal('modalType');
    });
}

/* ---- TYPE_DATA & FIELDS ---- */
// 거래 방향별 선택 UI는 화면용 id를 사용하고, 서버에서는 이 값을 trade_purpose 코드로 다시 매핑한다.
var TYPE_DATA = {
    buy: [
        { id: 'buy1', label: '제품·상품 매입' },
        { id: 'buy2', label: '원자재·부품 조달' },
        { id: 'buy3', label: 'OEM/ODM 생산 의뢰' },
        { id: 'buy4', label: '수입·조달 대행 의뢰' },
        { id: 'buy5', label: '정기 납품 요청' }
    ],
    supply: [
        { id: 'sup1', label: '제품·상품 판매 제안' },
        { id: 'sup2', label: 'OEM/ODM 생산 제안' },
        { id: 'sup3', label: '유통·판매 대행 제안' },
        { id: 'sup4', label: '수출 대행 제안' },
        { id: 'sup5', label: '정기 판매 제안' }
    ],
    collab: [
        { id: 'col1', label: '신제품 공동 개발' },
        { id: 'col2', label: '공동 마케팅·판촉' },
        { id: 'col3', label: '기술 제휴·라이선싱' }
    ]
};

var dirChips  = document.getElementById('reqDirChips');
var typesWrap = document.getElementById('reqTypeChips');
if (!dirChips || !typesWrap) return;

/* 라디오 칩 토글 */
function initRadioChips(container) {
    container.querySelectorAll('.req2-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            container.querySelectorAll('.req2-chip').forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            var radio = chip.querySelector('input');
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        });
    });
}
initRadioChips(dirChips);

function renderTypes(dir) {
    typesWrap.innerHTML = '';
    var types = TYPE_DATA[dir] || [];
    types.forEach(function (t) {
        var lbl = document.createElement('div');
        lbl.className = 'req2-chip';
        lbl.innerHTML = '<input type="checkbox" value="' + t.id + '"> ' + t.label;
        var cb = lbl.querySelector('input');
        lbl.addEventListener('click', function (e) {
            if (e.target !== cb) cb.checked = !cb.checked;
            lbl.classList.toggle('active', cb.checked);
        });
        typesWrap.appendChild(lbl);
    });
}

dirChips.querySelectorAll('input[name="req_dir"]').forEach(function (r) {
    r.addEventListener('change', function () { if (r.checked) renderTypes(r.value); });
});
renderTypes('buy');
setRequesterCompany(MATCH_REQUEST_CONTEXT.requesterCompany || null);
setRequestTarget(MATCH_REQUEST_CONTEXT.target || null);
updateRequesterProfileUi();

if (MATCH_REQUEST_CONTEXT.autoOpenOnLoad) {
    openModal('modalRequest');

    if (window.history && typeof window.history.replaceState === 'function') {
        var cleanedUrl = new URL(window.location.href);
        cleanedUrl.searchParams.delete('trade_request_login');
        window.history.replaceState({}, document.title, cleanedUrl.pathname + cleanedUrl.search + cleanedUrl.hash);
    }
}

/* ---- 추가 문의사항: 자동 메시지 버튼 ---- */
(function () {
    var ta = document.getElementById('reqExtraNote');
    var autoBtn = document.getElementById('reqAutoMessageBtn');
    if (!ta) return;
    if (!autoBtn) return;

    autoBtn.addEventListener('click', function () {
        ta.value = DEFAULT_NOTE || '';
        ta.focus();
        if (typeof ta.setSelectionRange === 'function') {
            ta.setSelectionRange(ta.value.length, ta.value.length);
        }
    });
})();

/* ---- 뒤로가기: 거래 타입 → 요청서 작성 ---- */
var backToRequestBtn = document.getElementById('backToRequest');
if (backToRequestBtn) {
    backToRequestBtn.addEventListener('click', function () {
        closeModal('modalType');
        openModal('modalRequest');
    });
}

/* ---- 거래 요청 제출 ---- */
// 최종 제출은 현재 상세 페이지로 POST 하여 PHP가 검증/insert/차감/마스킹 해제까지 한 번에 처리하게 한다.
function collectDynamicFields() {
    var itemField = document.getElementById('reqItemField');
    var val = itemField ? itemField.value.trim() : '';
    var missing = val ? [] : ['req_item'];
    return { data: { req_item: val }, missing: missing };
}

var confirmTypeBtn = document.getElementById('confirmType');
if (confirmTypeBtn) {
    confirmTypeBtn.addEventListener('click', function () {
        var typeChecks = Array.prototype.slice.call(typesWrap.querySelectorAll('input:checked'));
        if (!typeChecks.length) { alert('거래 유형을 1개 이상 선택해 주세요.'); return; }
        var result = collectDynamicFields();
        if (result.missing.length) { alert('필수 항목(*)을 모두 입력해 주세요.'); return; }
        var extraNote = document.getElementById('reqExtraNote');
        var dir = document.querySelector('input[name="req_dir"]:checked');
        var matchType = document.querySelector('input[name="match_type"]:checked');
        if (!dir || !matchType) { alert('거래 요청 정보를 다시 확인해 주세요.'); return; }

        var formData = new URLSearchParams();
        var target = MATCH_REQUEST_CONTEXT.target || {};
        if (!target.kedcd) { alert('대상 기업 정보를 다시 확인해 주세요.'); return; }

        formData.set('action', 'trade_request_insert');
        formData.set('target_kedcd', target.kedcd);
        formData.set('trade_direction', dir.value);
        typeChecks.forEach(function (c) { formData.append('trade_purpose[]', c.value); });
        formData.set('desired_item', result.data.req_item);
        formData.set('message_content', extraNote && extraNote.value.trim() ? extraNote.value.trim() : DEFAULT_NOTE);
        formData.set('trade_type', matchType.value);
        var requesterBznoDigits = normalizeDigits((reqRequesterBzno && reqRequesterBzno.value) || (requesterProfile.bzno || ''));
        if (requesterBznoDigits) {
            formData.set('requester_bzno', requesterBznoDigits);
        }

        confirmTypeBtn.disabled = true;

        // 저장은 항상 상세 페이지 POST 로직으로 보내서 페이지별 처리 차이를 없앤다.
        fetch(REQUEST_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData.toString()
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP_' + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            if (!data || !data.success) {
                var errorView = resolveTradeRequestErrorView(data || {});
                if (errorView.isCreditIssue) {
                    showCreditShortageModal(errorView.message);
                    return;
                }
                if (errorView.shouldRelogin && MATCH_REQUEST_CONTEXT && MATCH_REQUEST_CONTEXT.loginCheckUrl) {
                    alert(errorView.message);
                    window.location.href = MATCH_REQUEST_CONTEXT.loginCheckUrl;
                    return;
                }
                alert(errorView.message);
                return;
            }

            closeModal('modalType');
            closeModal('modalRequest');

            if (window._showToast) {
                window._showToast('✓', data.message || '거래 요청서가 저장되었습니다', 'success');
            }

            if (typeof window.onTradeRequestCreated === 'function') {
                window.onTradeRequestCreated(data, target);
                return;
            }

            // 상세 페이지에서는 저장 직후 마스킹 해제 상태를 반영하기 위해 새로고침한다.
            if ((MATCH_REQUEST_CONTEXT.sourcePage === 'company_detail' || MATCH_REQUEST_CONTEXT.sourcePage === 'company_detail_ai') && data && data.unlock_detail) {
                setTimeout(function () {
                    window.location.reload();
                }, window._showToast ? 1200 : 0);
            }
        })
        .catch(function () {
            alert('서버 통신 중 오류가 발생했습니다.');
        })
        .finally(function () {
            confirmTypeBtn.disabled = false;
        });
    });
}

}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMatchRequestModal);
} else {
    initMatchRequestModal();
}

})();
