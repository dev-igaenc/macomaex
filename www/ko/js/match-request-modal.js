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

/* ---- 모달 열기/닫기 (전역 노출 — 페이지 내 다른 코드에서도 사용 가능) ---- */
window.openModal = function (id) {
    var sw = window.innerWidth - document.documentElement.clientWidth;
    document.documentElement.style.setProperty('--scrollbar-w', sw + 'px');
    document.documentElement.classList.add('hide-scroll');
    document.getElementById(id).classList.add('open');
};
window.closeModal = function (id) {
    document.getElementById(id).classList.remove('open');
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
        if (window._isLoggedIn && !window._isLoggedIn()) { window._requireLogin('거래 요청'); return; }
        openModal('modalRequest');
    });
}

/* ---- 거래 타입 카드 라디오 동기화 ---- */
document.querySelectorAll('.type-card').forEach(function (card) {
    card.addEventListener('click', function () {
        document.querySelectorAll('.type-card').forEach(function (c) { c.classList.remove('selected'); });
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
        var footer = document.querySelector('#modalRequest .modal-footer');
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

/* ---- 추가 문의사항: 포커스 시 기본 텍스트 지우기 ---- */
(function () {
    var DEFAULT_NOTE = '담당자님, 안녕하세요. 귀사를 확인하고 연락드립니다. 신규 거래처를 검토 중이며, 귀사와 적합한 경우 거래 진행을 희망합니다. 상세 조건은 협의를 통해 논의하고자 합니다.';
    var ta = document.getElementById('reqExtraNote');
    if (!ta) return;
    if (ta.value === DEFAULT_NOTE) ta.style.color = '#a0aec0';
    ta.addEventListener('focus', function () { if (ta.value === DEFAULT_NOTE) { ta.value = ''; ta.style.color = ''; } });
    ta.addEventListener('blur',  function () { if (!ta.value.trim()) { ta.value = DEFAULT_NOTE; ta.style.color = '#a0aec0'; } });
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
        var dirLabel = dir ? dir.parentElement.textContent.trim() : '';
        var typeLabels = typeChecks.map(function (c) { return c.parentElement.textContent.trim(); });
        var matchType = document.querySelector('input[name="match_type"]:checked');
        var matchLabel = matchType && matchType.value === 'tm' ? 'TM 서포트' : '일반';
        /* 상세 필드 → 레이블·값 배열로 변환 */
        var detail = result.data.req_item
            ? [{ typeLabel: '거래 희망 품목', fields: [{ label: '거래 희망 품목', value: result.data.req_item }] }]
            : [];

        /* localStorage 저장 */
        try {
            var mc = window._matchCompany || {};
            var requests = JSON.parse(localStorage.getItem('macomaex_matchRequests') || '[]');
            requests.unshift({
                id: Date.now(),
                timestamp: new Date().toISOString(),
                companyId:   mc.id   || '',
                companyName: mc.name || '',
                matchType:   matchLabel,
                direction:   dirLabel,
                types:       typeLabels,
                detail:      detail,
                extra:       extraNote.value.trim(),
                status:      'N'
            });
            localStorage.setItem('macomaex_matchRequests', JSON.stringify(requests.slice(0, 50)));
        } catch(e) {}

        alert('[시안 데모]\n거래 타입: ' + matchLabel + '\n거래 방향: ' + dirLabel + '\n거래 유형: ' + typeLabels.join(', ') + (extraNote.value.trim() ? '\n추가 문의: ' + extraNote.value.trim() : ''));
        closeModal('modalType');
    });
}

})();