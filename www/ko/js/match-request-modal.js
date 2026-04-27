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

/* ---- 거래 요청하기 버튼 → 요청서 작성 먼저 ---- */
var openMatchBtn = document.getElementById('openTypeModal2');
if (openMatchBtn) {
    openMatchBtn.addEventListener('click', function () {
        // 비로그인 진입은 각 페이지가 내려준 로그인 유도 URL로만 처리한다.
        if (MATCH_REQUEST_CONTEXT.isLoggedIn === false) {
            if (MATCH_REQUEST_CONTEXT.loginCheckUrl) {
                window.location.href = MATCH_REQUEST_CONTEXT.loginCheckUrl;
            }
            return;
        }
        setRequesterCompany(MATCH_REQUEST_CONTEXT.requesterCompany || null);
        setRequestTarget(MATCH_REQUEST_CONTEXT.target || null);
        openModal('modalRequest');
    });
}

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
                var errorMessage = data && data.message ? data.message : '거래 요청서 저장 중 오류가 발생했습니다.';
                if (data && data.plan_required) {
                    alert(errorMessage);
                    window.location.href = '/ko/plan.php';
                    return;
                }
                alert(errorMessage);
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
            if (MATCH_REQUEST_CONTEXT.sourcePage === 'company_detail' && data && data.unlock_detail) {
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
