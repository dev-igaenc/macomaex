/**
 * 기업 핵심 정보 카드 공통 렌더 모듈
 *
 * 사용법:
 *   renderDetailLeftCard(co, scoreHtml);
 *
 * @param {Object} co        - 기업 데이터 객체
 * @param {string} scoreHtml - 점수 섹션 HTML (성장성/수익성/… 있는 페이지만 전달, 없으면 '' 또는 생략)
 *
 * 의존:
 *   - css/company-detail-left.css (공통 스타일)
 *   - inc/badges.js (companyBadgesHtml 함수)
 *   - #leftCard 엘리먼트가 페이지에 존재해야 함
 */
function renderDetailLeftCard(co, scoreHtml) {
    var el = document.getElementById('leftCard');
    if (!el) return;

    function _esc(s) {
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(String(s || '')));
        return d.innerHTML;
    }

    function _maskName(s) {
        var m = (s || '').match(/^(\(주\)|\(유\)|\(사\))?(.*)$/);
        var prefix = m[1] || '';
        var name = m[2];
        var first = prefix ? name.substring(0, 1) : name.substring(0, 2);
        return _esc(prefix + first) + '****';
    }

    var tagsHtml = '';
    (co.tags || []).forEach(function(t) {
        tagsHtml += '<span class="tag-pill">' + _esc(t) + '</span>';
    });

    var creditTooltip =
        '재무상태 등급은 내부 기준으로 산정됩니다.' +
        '<br><strong>A</strong> → 상위 (재무 우량)' +
        '<br><strong>B</strong> → 우수 (안정적)' +
        '<br><strong>C</strong> → 일반 (보통 수준)' +
        '<br><span class="credit-q-sub">※ +/- 부호는 등급 내 세분화를 의미합니다.</span>';

    var creditDt =
        '재무상태 ' +
        '<span class="credit-q-wrap">' +
            '<span class="credit-q" aria-label="도움말">?</span>' +
            '<span class="credit-q-tooltip">' + creditTooltip + '</span>' +
        '</span>';

    var badgesHtml = (typeof companyBadgesHtml === 'function') ? companyBadgesHtml(co) : '';

    el.innerHTML =
        '<div class="card-title-row"><span class="card-title-label">기업 핵심 정보</span></div>' +
        badgesHtml +
        '<div class="profile-head"><div>' +
            '<h1>' + _maskName(co.title) + '</h1>' +
            '<p class="sub">' + _esc(co.sub) + '</p>' +
        '</div></div>' +
        '<div class="industry-tags">' + tagsHtml + '</div>' +
        (scoreHtml || '') +
        '<dl class="info-grid">' +
            '<dt>설립</dt><dd>' + _esc(co.founded) + '</dd>' +
            '<dt>고용인원</dt><dd>' + _esc(co.employees) + '</dd>' +
            '<dt>기업유형</dt><dd>' + _esc(co.corp_type || '법인기업') + '</dd>' +
            '<dt>주요제품</dt><dd class="info-product-line">' + _esc(co.product_line) + '</dd>' +
            '<dt>' + creditDt + '</dt><dd>' + _esc(co.credit) + '</dd>' +
            '<dt>주소(도로명)</dt><dd class="info-masked">*********************</dd>' +
            '<dt>홈페이지</dt><dd class="info-masked">*********************</dd>' +
        '</dl>' +
        '<p class="info-mask-notice">거래 요청 및 수락 시 *** 으로 표시된 정보가 공개됩니다.</p>';
}