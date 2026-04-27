(function () {
'use strict';

if (!window.MlScores) {
    window.MlScores = {
        get: function () {
            try { return JSON.parse(sessionStorage.getItem('_aiCard') || 'null'); } catch (e) { return null; }
        },
        applyToRadar: function (radar, card) {
            if (!radar || !card || !card.radar || typeof card.radar !== 'object') return;
            Object.keys(card.radar).forEach(function (k) {
                var v = Number(card.radar[k]);
                if (Number.isFinite(v)) radar[k] = v;
            });
        }
    };
}
if (typeof window._isLoggedIn !== 'function') {
    window._isLoggedIn = function () {
        return !!(window.MATCH_REQUEST_CONTEXT && window.MATCH_REQUEST_CONTEXT.isLoggedIn);
    };
}
if (typeof window._requireLogin !== 'function') {
    window._requireLogin = function () {};
}

/* AI 기업 상세 스크립트 */
(function () {
'use strict';

/* 초기 데이터 */
var COMPANIES = window.MOCK_COMPANIES;
var seedCompany = (window.MATCH_REQUEST_CONTEXT && window.MATCH_REQUEST_CONTEXT.seedCompany) || null;
if (seedCompany && seedCompany.id) {
    COMPANIES[seedCompany.id] = Object.assign({}, COMPANIES[seedCompany.id] || {}, seedCompany);
}

/* 분석 템플릿 데이터 */

/* URL 파라미터 */
var params = new URLSearchParams(window.location.search);
var demoId = params.get('id') || params.get('kedcd') || '';
demoId = demoId.replace(/[^a-z0-9\-_]/gi, '');
var pageTradeType = parseInt(params.get('trade_type') || '1', 10);
if (pageTradeType !== 2) pageTradeType = 1;
var srcKedcdParam = String(params.get('src_kedcd') || '').replace(/\D/g, '');
if (srcKedcdParam.length === 10) {
    try { sessionStorage.setItem('_aiSrcKedcd', srcKedcdParam); } catch (e) {}
}
// src_kedcd가 같은 스냅샷만 사용
var expectedSrcKedcd = srcKedcdParam;
if (expectedSrcKedcd.length !== 10) {
    try { expectedSrcKedcd = String(sessionStorage.getItem('_aiSrcKedcd') || '').replace(/\D/g, ''); } catch (e0) {}
}
if (expectedSrcKedcd.length !== 10) expectedSrcKedcd = '';

function parseTagListFromLine(raw) {
    var text = String(raw || '').trim();
    if (!text) return [];
    return text
        .split(',')
        .map(function (part) {
            return String(part || '').replace(/\s+외$/u, '').trim();
        })
        .filter(function (part, idx, arr) {
            return part && arr.indexOf(part) === idx;
        });
}

function parseBadgeMetaFromCard(card) {
    var meta = {
        badge_growth: null,
        badge_stability: null,
        badge_venture: 'N'
    };
    if (!card || typeof card !== 'object') return meta;

    if (card.badge_growth != null && String(card.badge_growth).trim() !== '') {
        meta.badge_growth = String(card.badge_growth).trim();
    }
    if (card.badge_stability != null && String(card.badge_stability).trim() !== '') {
        meta.badge_stability = String(card.badge_stability).trim();
    }
    if (String(card.badge_venture || '').toUpperCase() === 'Y') {
        meta.badge_venture = 'Y';
    }

    var badges = Array.isArray(card.badges) ? card.badges : [];
    badges.forEach(function (badgeText) {
        var text = String(badgeText || '').trim();
        if (!text) return;

        var growth = text.match(/성장TOP\s*([0-9]+(?:\.[0-9]+)?)%?/);
        if (growth && growth[1]) meta.badge_growth = growth[1];

        var stability = text.match(/재무안정TOP\s*([0-9]+(?:\.[0-9]+)?)%?/);
        if (stability && stability[1]) meta.badge_stability = stability[1];

        if (text.indexOf('벤처') !== -1) meta.badge_venture = 'Y';
    });
    return meta;
}

function getCardSnapshotForDemo(demoId) {
    var id = String(demoId || '').replace(/\D/g, '');
    if (id.length !== 10) return null;

    try {
        var mapRaw = sessionStorage.getItem('_aiCardMap');
        if (mapRaw) {
            var cardMap = JSON.parse(mapRaw) || {};
            var mapCard = cardMap[id];
            var mapTradeType = parseInt(mapCard && mapCard.trade_type, 10);
            if (mapTradeType !== 2) mapTradeType = 1;
            var mapSrcKedcd = String(mapCard && mapCard.src_kedcd || '').replace(/\D/g, '');
            var mapSrcMatched = true;
            if (expectedSrcKedcd.length === 10) {
                mapSrcMatched = (mapSrcKedcd.length === 10 && mapSrcKedcd === expectedSrcKedcd);
            }
            if (mapCard && String(mapCard.kedcd || '').replace(/\D/g, '') === id && mapTradeType === pageTradeType && mapSrcMatched) {
                return mapCard;
            }
        }
    } catch (e) {}

    try {
        var lastCard = MlScores.get();
        var lastTradeType = parseInt(lastCard && lastCard.trade_type, 10);
        if (lastTradeType !== 2) lastTradeType = 1;
        var lastSrcKedcd = String(lastCard && lastCard.src_kedcd || '').replace(/\D/g, '');
        var lastSrcMatched = true;
        if (expectedSrcKedcd.length === 10) {
            lastSrcMatched = (lastSrcKedcd.length === 10 && lastSrcKedcd === expectedSrcKedcd);
        }
        if (lastCard && String(lastCard.kedcd || '').replace(/\D/g, '') === id && lastTradeType === pageTradeType && lastSrcMatched) {
            return lastCard;
        }
    } catch (e) {}

    return null;
}

// kedcd 진입 시 스냅샷 데이터 병합
if (/^\d{10}$/.test(demoId)) {
    try {
        var _card = getCardSnapshotForDemo(demoId);
        if (_card) {
            var _base = COMPANIES[demoId] || {};
            var _szeMap = {'1':'대기업','2':'중견기업','3':'중소기업'};
            var _sze = parseInt(sessionStorage.getItem('_aiExtSze') || _card.sze || 3, 10);
            var _szeLabel = _szeMap[String(_sze)] || '법인기업';
            var _badgeMeta = parseBadgeMetaFromCard(_card);
            var _cardTags = Array.isArray(_card.tags) ? _card.tags.filter(Boolean) : [];
            var _seedTags = Array.isArray(_base.tags) ? _base.tags.filter(Boolean) : [];
            var _mergedTags = _cardTags.length ? _cardTags : _seedTags;
            if (!_mergedTags.length) {
                _mergedTags = parseTagListFromLine(_base.product_line || _card.product_line || '');
            }
            var _productLine = String(_base.product_line || _card.product_line || '').trim();
            if (!_productLine && _mergedTags.length) {
                _productLine = _mergedTags.join(', ');
            }

            var _toPct = function (value) {
                var n = Number(value);
                if (!Number.isFinite(n)) return null;
                if (n < 0) n = 0;
                if (n > 100) n = 100;
                return n;
            };
            var _toFive = function (value) {
                var pct = _toPct(value);
                if (pct === null) return null;
                return +(pct / 20).toFixed(1);
            };
            var _radarNorm = normalizeRadarLikeList(_card.radar || {});

            var _scorePct = _toPct(_card.ai_match);
            if (_scorePct === null) _scorePct = _toPct(_card.display_score);
            if (_scorePct === null && Number.isFinite(Number(_card.score))) _scorePct = _toPct(Number(_card.score) * 100);

            var _growScore = _toFive(_radarNorm.growth_trend);
            if (_growScore === null) _growScore = _toFive(_radarNorm.trade_loyalty);

            var _profitScore = _toFive(_radarNorm.market_power);
            if (_profitScore === null) _profitScore = _toFive(_radarNorm.trade_diversity);
            if (_profitScore === null) _profitScore = _toFive(_radarNorm.product_fit);

            var _stableScore = _toFive(_radarNorm.financial_health);

            COMPANIES[demoId] = Object.assign({}, _base, {
                id: demoId,
                title: _card.title || _base.title || demoId,
                sub: _card.one_line || _card.industry_nm || _card.industry || _base.sub || '',
                tags: _mergedTags,
                score: _scorePct !== null ? +(_scorePct / 20).toFixed(2) : (Number.isFinite(Number(_base.score)) ? Number(_base.score) : null),
                ai_match: _scorePct !== null ? Math.round(_scorePct) : (Number.isFinite(Number(_base.ai_match)) ? Math.round(Number(_base.ai_match)) : null),
                grow: _growScore !== null ? _growScore : (Number.isFinite(Number(_base.grow)) ? Number(_base.grow) : null),
                profit: _profitScore !== null ? _profitScore : (Number.isFinite(Number(_base.profit)) ? Number(_base.profit) : null),
                stable: _stableScore !== null ? _stableScore : (Number.isFinite(Number(_base.stable)) ? Number(_base.stable) : null),
                badge_growth: _badgeMeta.badge_growth != null ? _badgeMeta.badge_growth : _base.badge_growth,
                badge_stability: _badgeMeta.badge_stability != null ? _badgeMeta.badge_stability : _base.badge_stability,
                badge_venture: _badgeMeta.badge_venture || _base.badge_venture || 'N',
                rep: '',
                founded: _base.founded || '',
                employees: _base.employees || '',
                biz_type: _szeLabel,
                credit: _base.credit || '',
                corp_type: _szeLabel,
                address: _base.address || _card.region || '',
                product_line: _productLine,
                enp_sze: _szeLabel,
                clients: [],
                ai_analysis_html: _base.ai_analysis_html || '',
                icon: 'cat-iso-1',
                region_insensitive: !!_base.region_insensitive,
                _fromEc2: true
            });
        }
    } catch(e) {}
}

if (!demoId || !COMPANIES[demoId]) {
    if (!/^\d{10}$/.test(demoId)) demoId = 'beauty-factory';
    else COMPANIES[demoId] = Object.assign({}, seedCompany || {}, { id: demoId });
}
var co = COMPANIES[demoId];

// 스냅샷이 없으면 상세 카드 복원
(function hydrateCardFromRecommendWhenMissing() {
    var targetKedcd = String(demoId || '').replace(/\D/g, '');
    if (targetKedcd.length !== 10) return;

    var currentCard = getCardSnapshotForDemo(targetKedcd);
    var hasRadar = !!(currentCard && currentCard.radar && typeof currentCard.radar === 'object' && Object.keys(currentCard.radar).length);
    var hasScore = currentCard && Number.isFinite(Number(currentCard.ai_match));

    var hydrateKey = '_aiDetailHydrated_' + targetKedcd + '_' + pageTradeType;
    if (hasRadar && hasScore) {
        try { sessionStorage.removeItem(hydrateKey); } catch (e0) {}
        return;
    }
    try { sessionStorage.removeItem(hydrateKey); } catch (e0a) {}

    var srcKedcd = String(params.get('src_kedcd') || '').replace(/\D/g, '');
    if (srcKedcd.length !== 10) {
        try { srcKedcd = String(sessionStorage.getItem('_aiSrcKedcd') || '').replace(/\D/g, ''); } catch (e1) {}
    }
    if (srcKedcd.length !== 10) return;

    // target_card API 호출
    var hydrateUrl = '/ko/company_list_ai_api.php?action=target_card'
        + '&src_kedcd=' + encodeURIComponent(srcKedcd)
        + '&tgt_kedcd=' + encodeURIComponent(targetKedcd)
        + '&trade_type=' + encodeURIComponent(pageTradeType);

    fetch(hydrateUrl, { credentials: 'same-origin' })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data || typeof data !== 'object') return;
            var found = data.item;
            if (!found || !found.radar || typeof found.radar !== 'object' || !Object.keys(found.radar).length) return;

            var scorePct = Number(found.display_score);
            if (!Number.isFinite(scorePct)) {
                var rawScore = Number(found.score);
                if (Number.isFinite(rawScore)) scorePct = (rawScore <= 1) ? (rawScore * 100) : rawScore;
            }
            if (!Number.isFinite(scorePct)) scorePct = 0;
            if (scorePct < 0) scorePct = 0;
            if (scorePct > 100) scorePct = 100;

            var snapshot = Object.assign({}, currentCard || {}, {
                kedcd: targetKedcd,
                src_kedcd: srcKedcd,
                title: String(found.enp_nm || co.title || ''),
                radar: found.radar,
                trade_type: pageTradeType,
                ai_match: Math.round(scorePct),
                score: Number((scorePct / 20).toFixed(2)),
                candidate_type: String(found.candidate_type || (currentCard && currentCard.candidate_type) || 'chain2'),
                one_line: String(found.one_line || '')
            });

            try {
                var rawMap = sessionStorage.getItem('_aiCardMap');
                var cardMap = rawMap ? (JSON.parse(rawMap) || {}) : {};
                cardMap[targetKedcd] = snapshot;
                sessionStorage.setItem('_aiCardMap', JSON.stringify(cardMap));
                sessionStorage.setItem('_aiCard', JSON.stringify(snapshot));
                sessionStorage.setItem('_aiSrcKedcd', srcKedcd);

                if (!sessionStorage.getItem(hydrateKey)) {
                    sessionStorage.setItem(hydrateKey, '1');
                    window.location.reload();
                }
            } catch (e2) {}
        })
        .catch(function () {});
})();

document.title = String(co.title || 'AI 추천 기업') + ' \xB7 AI \uCD94\uCC9C \uAE30\uC5C5 \uC0C1\uC138 \u2014 \uB9E4\uCEE4\uBA59\uC2A4';
var _demoEl = document.getElementById('demoIdDisplay');
if (_demoEl) _demoEl.textContent = demoId;

/* 점수 계산 */
var starScore = co.score;
var starFull = Math.floor(starScore);
var starPart = Math.max(0, Math.min(1, starScore - starFull));
var starClipPct = starPart > 0 ? Math.round((1 - starPart) * 100) : 0;

/* 레이더 축/레이블 */
var RADAR_LABEL_MAP_D = {
    // 신규 키
    supply_chain:    '공급망 경로',
    network_overlap: '거래처 중첩도',
    financial_health:'재무 건전성',
    market_power:    '시장 거래력',
    trade_diversity: '거래 다각화',
    scale_match:     '규모 적합도',
    company_size:    '기업 역량',
    accessibility:   '지역 접근성',
    industry_fit:    '업종 연관성',
    trade_loyalty:   '거래 지속성',
    product_fit:     '품목 보완성',
    growth_trend:    '성장 추세',
    // 구키
    supply_chain_v1: '공급망 연결강도', similarity:    '거래처 유사성',
    financial:       '재무 건전성',     activity:      '거래 활성도',
    scale_fit:       '규모 적합도',     industry:      '공급망 경로',
    network:         '거래처 중첩도',   trust:         '거래 활성도',
    finance:         '재무 건전성',     region:        '지역 접근성',
    scale:           '규모 적합도'
};

// 레이더 키 정규화
function normalizeRadarLikeList(radarObj) {
    var alias = {
        supply_chain_old: 'supply_chain',
        industry: 'supply_chain',
        network: 'network_overlap',
        similarity: 'network_overlap',
        financial: 'financial_health',
        finance: 'financial_health',
        activity: 'growth_trend',
        trust: 'growth_trend',
        scale_fit: 'scale_match',
        scale: 'scale_match',
        region: 'accessibility'
    };
    var out = {};
    Object.keys(radarObj || {}).forEach(function (key) {
        var k = String(key || '').trim().toLowerCase();
        if (!k) return;
        if (alias[k]) k = alias[k];
        var score = Number(radarObj[key]);
        if (!Number.isFinite(score)) return;
        if (score < 0) score = 0;
        if (score > 100) score = 100;
        out[k] = score;
    });
    return out;
}

// 목록과 동일한 6축 구성
function buildRadarAxesLikeList(radarObj) {
    var radarNorm = normalizeRadarLikeList(radarObj || {});
    var fixed = ['supply_chain', 'network_overlap', 'financial_health'];
    var allAxes = Object.keys(radarNorm);
    var fixedAxes = fixed.filter(function (k) {
        if (!Object.prototype.hasOwnProperty.call(radarNorm, k)) return false;
        if (k === 'network_overlap' && Number(radarNorm[k] || 0) <= 0) return false;
        return true;
    });
    var dynAxes = allAxes.filter(function (k) {
        return fixedAxes.indexOf(k) < 0;
    });
    var axisOrder = {};
    allAxes.forEach(function (k, idx) { axisOrder[k] = idx; });
    dynAxes.sort(function (a, b) {
        var sa = Number(radarNorm[a] || 0);
        var sb = Number(radarNorm[b] || 0);
        if (sa === sb) return (axisOrder[a] || 0) - (axisOrder[b] || 0);
        return sb - sa;
    });

    var slots = [
        fixedAxes[0] || null, // 0 상단
        fixedAxes[1] || null, // 1 우상
        dynAxes[0] || null,   // 2 우하
        dynAxes[1] || null,   // 3 하단
        dynAxes[2] || null,   // 4 좌하
        fixedAxes[2] || null  // 5 좌상
    ];

    var slotsFilled = slots.filter(Boolean);
    var spare = allAxes.filter(function (k) {
        return slotsFilled.indexOf(k) < 0;
    });

    var ordered = [];
    var si = 0;
    slots.forEach(function (k) {
        var axis = k;
        if (!axis) axis = spare[si++] || null;
        if (axis && ordered.indexOf(axis) < 0) ordered.push(axis);
    });

    var fallbackOrder = [
        'supply_chain',
        'network_overlap',
        'market_power',
        'accessibility',
        'scale_match',
        'financial_health',
        'trade_diversity',
        'company_size',
        'industry_fit',
        'trade_loyalty',
        'product_fit',
        'growth_trend'
    ];
    fallbackOrder.forEach(function (k) {
        if (ordered.length >= 6) return;
        if (ordered.indexOf(k) < 0) ordered.push(k);
    });

    var axes = ordered.slice(0, 6).map(function (k) {
        var score = Number(radarNorm[k] || 0);
        if (!Number.isFinite(score)) score = 0;
        if (score < 0) score = 0;
        if (score > 100) score = 100;
        return {
            key: k,
            label: RADAR_LABEL_MAP_D[k] || k,
            score: Math.round(score)
        };
    });

    return {
        radar: radarNorm,
        axes: axes
    };
}

var radar = {};
if (co && co.radar && typeof co.radar === 'object') {
    Object.keys(co.radar).forEach(function (k) {
        var n = Number(co.radar[k]);
        if (Number.isFinite(n)) radar[k] = n;
    });
}

var radarAngles = [-90, -30, 30, 90, 150, 210];
var cx = 150, cy = 130, maxR = 60;

/* 스냅샷 레이더 우선 적용 */
(function() {
    var card = getCardSnapshotForDemo(demoId);
    if (!card) return;
    if (card.radar && typeof card.radar === 'object' && Object.keys(card.radar).length) {
        radar = {};
        Object.keys(card.radar).forEach(function(k){ radar[k] = card.radar[k]; });
    }
})();

var radarPack = buildRadarAxesLikeList(radar);
radar = radarPack.radar;
var radarAxes = radarPack.axes.map(function (a) { return a.key; });
var radarLabels = radarPack.axes.map(function (a) { return a.label; });
var radarScoreMap = {};
radarPack.axes.forEach(function (a) { radarScoreMap[a.key] = a.score; });

/* 유틸 */
function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function maskName(s) { s = String(s || ''); var m = s.match(/^(\(주\)|\(유\)|\(사\))?(.*)$/); var prefix = m[1] || ''; var name = m[2]; var first = prefix ? name.substring(0,1) : name.substring(0,2); return esc(prefix + first) + '****'; }

// 좌측 카드 렌더
function renderAiDetailLeftCard(co, scoreHtml) {
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

    var allTags = Array.isArray(co.tags) ? co.tags.filter(Boolean) : [];
    if (!allTags.length) {
        allTags = parseTagListFromLine(co.product_line || '');
    }
    var firstTags = allTags.slice(0, 3);
    var moreN = Math.max(0, allTags.length - firstTags.length);

    var tagsHtml = '';
    firstTags.forEach(function(t) {
        tagsHtml += '<span class="tag-pill">' + _esc(t) + '</span>';
    });
    if (moreN > 0) {
        tagsHtml += '<span class="tag-pill-more">+' + moreN + '</span>';
    }

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
            '<dt>주소</dt><dd class="info-masked">*********************</dd>' +
            '<dt>홈페이지</dt><dd class="info-masked">*********************</dd>' +
        '</dl>' +
        '<p class="info-mask-notice">거래 요청 및 수락 시 *** 으로 표시된 정보가 공개됩니다.</p>';
}

/* 별점 HTML */
function starsHtml() {
	var h = '';
	var starPath = 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z';
	for (var i = 0; i < starFull; i++) {
		h += '<svg viewBox="0 0 24 24" fill="#ffc107"><path d="' + starPath + '"/></svg>';
	}
	if (starPart > 0.001) {
		h += '<span class="star-partial">';
		h += '<svg viewBox="0 0 24 24" fill="#ffc107" style="clip-path: inset(0 ' + starClipPct + '% 0 0);"><path d="' + starPath + '"/></svg>';
		h += '<svg viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="1.5"><path d="' + starPath + '"/></svg>';
		h += '</span>';
	}
	return h;
}

/* 좌측 영역 */
var RD_LABELS_L = ['\uC131\uC7A5\uC131','\uC218\uC775\uC131','\uC7AC\uBB34\uC548\uC815','\uC5C5\uB825','\uACE0\uC6A9\uADDC\uBAA8'];
var RD_NUM_L = 5, RD_CX_L = 105, RD_CY_L = 100, RD_MAX_R_L = 48, RD_LABEL_R_L = 68;
var RD_GRID_L = [0.2, 0.4, 0.6, 0.8, 1.0];
function rdPolarL(cx, cy, r, i) {
	var a = (Math.PI * 2 * i / RD_NUM_L) - Math.PI / 2;
	return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
}
function clampScore5(v) {
    var n = Number(v);
    if (!Number.isFinite(n)) return 0;
    if (n < 0) n = 0;
    if (n > 5) n = 5;
    return +n.toFixed(1);
}
function radarPctTo5(radarObj, keys) {
    var val = null;
    (keys || []).some(function (k) {
        var n = Number(radarObj && radarObj[k]);
        if (!Number.isFinite(n)) return false;
        val = n;
        return true;
    });
    if (!Number.isFinite(val)) return null;
    if (val < 0) val = 0;
    if (val > 100) val = 100;
    return +(val / 20).toFixed(1);
}
var careerScoreL = Math.min(5, Math.max(1, parseInt(co.founded) ? (new Date().getFullYear() - parseInt(co.founded)) / 5 : 3)).toFixed(1);
var empNumL = parseInt(co.employees) || 50;
var employScoreL = Math.min(5, Math.max(1, Math.log2(empNumL / 5))).toFixed(1);
var cardForLeft = getCardSnapshotForDemo(demoId);
var leftRadarNorm = normalizeRadarLikeList((cardForLeft && cardForLeft.radar) || radar || {});
var growL = radarPctTo5(leftRadarNorm, ['growth_trend', 'trade_loyalty']);
if (growL === null) growL = Number(co.grow);
var profitL = radarPctTo5(leftRadarNorm, ['market_power', 'trade_diversity', 'product_fit']);
if (profitL === null) profitL = Number(co.profit);
var stableL = radarPctTo5(leftRadarNorm, ['financial_health']);
if (stableL === null) stableL = Number(co.stable);
var radarScoresL = [clampScore5(growL), clampScore5(profitL), clampScore5(stableL), clampScore5(careerScoreL), clampScore5(employScoreL)];

var svgHL = '';
RD_GRID_L.forEach(function(lv) {
	var pts = [];
	for (var i = 0; i < RD_NUM_L; i++) { var p = rdPolarL(RD_CX_L, RD_CY_L, RD_MAX_R_L * lv, i); pts.push(p.x + ',' + p.y); }
	svgHL += '<polygon points="' + pts.join(' ') + '" class="radar-grid"/>';
});
for (var i = 0; i < RD_NUM_L; i++) {
	var p = rdPolarL(RD_CX_L, RD_CY_L, RD_MAX_R_L, i);
	svgHL += '<line x1="' + RD_CX_L + '" y1="' + RD_CY_L + '" x2="' + p.x + '" y2="' + p.y + '" stroke="#E8EBF0" stroke-width="0.5"/>';
}
var normL = radarScoresL.map(function(s) { return s / 5; });
var dpL = [];
for (var i = 0; i < RD_NUM_L; i++) { var p = rdPolarL(RD_CX_L, RD_CY_L, RD_MAX_R_L * normL[i], i); dpL.push(p.x + ',' + p.y); }
svgHL += '<polygon points="' + dpL.join(' ') + '" class="radar-shape"/>';
for (var i = 0; i < RD_NUM_L; i++) {
	var p = rdPolarL(RD_CX_L, RD_CY_L, RD_MAX_R_L * normL[i], i);
	svgHL += '<circle cx="' + p.x + '" cy="' + p.y + '" class="radar-dot"/>';
}
for (var i = 0; i < RD_NUM_L; i++) {
	var p = rdPolarL(RD_CX_L, RD_CY_L, RD_LABEL_R_L, i);
	var anc = 'middle', dx = 0;
	if (p.x < RD_CX_L - 5) { anc = 'end'; dx = -2; }
	else if (p.x > RD_CX_L + 5) { anc = 'start'; dx = 2; }
	var dy = 0;
	if (p.y < RD_CY_L - 10) dy = -3;
	else if (p.y > RD_CY_L + 10) dy = 8;
	else dy = 3;
	svgHL += '<text x="' + (p.x + dx) + '" y="' + (p.y + dy) + '" text-anchor="' + anc + '" class="radar-label">' + RD_LABELS_L[i] + '</text>';
}
var scoreItemsL = '';
RD_LABELS_L.forEach(function(lbl, i) {
	scoreItemsL += '<div class="radar-score-item"><span class="radar-score-label">' + lbl + '</span><span class="radar-score-val">' + radarScoresL[i].toFixed(1) + '</span></div>';
});
var avgScoreL = (radarScoresL.reduce(function(a, b) { return a + b; }, 0) / radarScoresL.length);
scoreItemsL += '<div class="radar-score-item radar-score-total"><span class="radar-score-label">\uC885\uD569</span><span class="radar-score-val radar-score-val--total">' + avgScoreL.toFixed(1) + '</span></div>';
var radarCardHtml = '<div class="detail-radar-card" style="padding:0;margin:16px 0;background:none;border:none;">' +
	'<div class="radar-scores">' + scoreItemsL + '</div></div>';

// 좌측 카드 삽입
renderAiDetailLeftCard(co, radarCardHtml);
window._matchCompany = { id: demoId, name: co.title };

/* 우측 영역 */
// 적합도 점수 계산
var _radarVals = Object.values(radar);
var fitScore = _radarVals.length ? Math.round(_radarVals.reduce(function(s,v){return s+v;},0) / _radarVals.length) : 0;

/* EC2 점수 우선 적용 */
(function() {
    var card = getCardSnapshotForDemo(demoId);
    if (!card) return;
    var ec2Score = Number(card.ai_match);
    if (!Number.isFinite(ec2Score)) ec2Score = Number(card.display_score);
    if (!Number.isFinite(ec2Score) && Number.isFinite(Number(card.score))) ec2Score = Number(card.score) * 100;
    if (Number.isFinite(ec2Score)) {
        if (ec2Score < 0) ec2Score = 0;
        if (ec2Score > 100) ec2Score = 100;
        fitScore = Math.round(ec2Score);
    }
})();

// 레이더 SVG
function toRad(deg) { return deg * Math.PI / 180; }
function snapRadar(v) { return Math.max(25, Math.round(v / 25) * 25); }
var pts = [];
radarAxes.forEach(function (ax, i) {
	var val = snapRadar(radarScoreMap[ax] || 0) / 100;
	var rad = toRad(radarAngles[i]);
	pts.push({ x: Math.round((cx + maxR * val * Math.cos(rad)) * 10) / 10, y: Math.round((cy + maxR * val * Math.sin(rad)) * 10) / 10 });
});
var polyStr = pts.map(function (p) { return p.x + ',' + p.y; }).join(' ');

var rings = [25, 50, 75, 100];
var gridPolys = rings.map(function (rv) {
	return radarAngles.map(function (a) {
		var rad = toRad(a);
		return Math.round((cx + maxR * (rv / 100) * Math.cos(rad)) * 10) / 10 + ',' + Math.round((cy + maxR * (rv / 100) * Math.sin(rad)) * 10) / 10;
	}).join(' ');
});

var axLines = radarAngles.map(function (a) {
	var rad = toRad(a);
	return { x1: cx, y1: cy, x2: Math.round((cx + maxR * Math.cos(rad)) * 10) / 10, y2: Math.round((cy + maxR * Math.sin(rad)) * 10) / 10 };
});

var lblPos = radarAngles.map(function (a) {
	var rad = toRad(a);
	return { x: Math.round((cx + (maxR + 32) * Math.cos(rad)) * 10) / 10, y: Math.round((cy + (maxR + 32) * Math.sin(rad)) * 10) / 10 };
});

var svgHtml = '<svg viewBox="0 0 300 270" class="detail-radar-svg">';
gridPolys.forEach(function (g) { svgHtml += '<polygon points="' + g + '" fill="none" stroke="#e5e7eb" stroke-width="0.8"/>'; });
axLines.forEach(function (al) { svgHtml += '<line x1="' + al.x1 + '" y1="' + al.y1 + '" x2="' + al.x2 + '" y2="' + al.y2 + '" stroke="#e5e7eb" stroke-width="0.6"/>'; });
svgHtml += '<polygon points="' + polyStr + '" fill="rgba(109,40,217,0.12)" stroke="rgba(109,40,217,0.6)" stroke-width="1.8" id="radarPoly"/>';
pts.forEach(function (p, i) {
	svgHtml += '<circle cx="' + p.x + '" cy="' + p.y + '" r="3.5" fill="rgba(109,40,217,0.8)" class="radar-dot" data-axis="' + i + '"/>';
});
lblPos.forEach(function (lp, li) {
	var anchor = 'middle';
	if (lp.x < cx - 20) anchor = 'end';
	else if (lp.x > cx + 20) anchor = 'start';
	var fillColor = '#555';
	var suffix = '';
	svgHtml += '<text x="' + lp.x + '" y="' + (lp.y + 5) + '" text-anchor="' + anchor + '" font-size="13" font-weight="500" fill="' + fillColor + '" class="radar-label" data-axis="' + li + '">' + radarLabels[li] + suffix + '</text>';
});
svgHtml += '</svg>';

// 범례
var AXIS_DESC_MAP = {
    // 신규 키
    supply_chain:    '기존 거래망으로 접근 가능',
    network_overlap: '공통 거래처 연결 유사도',
    financial_health:'신용등급 · 부채 안정성',
    market_power:    '업계 거래 규모 · 인지도',
    trade_diversity: '거래처 집중도 낮을수록 우수',
    scale_match:     '매출 · 자산 규모 유사성',
    company_size:    '기업 매출 · 자산 규모',
    accessibility:   '물류 · 방문 접근 편의',
    industry_fit:    '업종 코드 유사도',
    trade_loyalty:   '평균 거래 유지 기간',
    product_fit:     '품목 상호 보완 관계',
    growth_trend:    '최근 매출 성장 흐름',
    // 구키
    similarity: '귀사와 비슷한 거래처를 보유',
    financial:  '매출, 자산, 재무 상태가 건전',
    activity:   '다양한 기업과 활발히 거래 중',
    scale_fit:  '거래하기에 적절한 기업 규모',
    industry:   '거래 네트워크상 얼마나 가까운지',
    network:    '귀사와 비슷한 거래처를 보유',
    trust:      '다양한 기업과 활발히 거래 중',
    finance:    '신용점수와 부채비율 기반 재무 안정성',
    region:     '물류/방문이 편리한 거리',
    scale:      '거래하기에 적절한 기업 규모'
};
var legendHtml = '<div class="detail-radar_legend">';
radarAxes.forEach(function (ax, di) {
    var title = RADAR_LABEL_MAP_D[ax] || ax;
    var desc  = AXIS_DESC_MAP[ax] || '';
    var regionTip = (ax === 'accessibility')
        ? '<span class="region-tip-wrap"><i class="region-tip-icon">?</i><span class="region-tip-tooltip">IT·무역·금융 등 전국 거래 업종은 거리와 무관하게 100점이 부여됩니다.</span></span>'
        : '';
    legendHtml += '<div class="detail-radar_legend_item" data-axis="' + di + '">' +
        '<div class="detail-radar_legend_text"><strong>' + title + regionTip + '</strong><span>' + desc + '</span></div>' +
        '<span class="detail-radar_legend_score">' + (radarScoreMap[ax] || 0) + '</span></div>';
});
legendHtml += '</div>';

var regionNotice = '';

(function renderRightCard() {
	var loggedIn = window._isLoggedIn && window._isLoggedIn();
	function buildInner(isMock) {
		var scoreNum = isMock ? '--' : fitScore;
		var companyName = isMock ? '○○○○' : maskName(co.title);
		return '<div class="match-box-inner">' +
			'<div class="fit-header">' +
				'<div class="fit-header-top">' +
					'<div class="fit-header-companies">우리회사 <strong>A기업</strong> <span class="fit-header-arrow">⇄</span> 추천 <strong>B기업</strong></div>' +
					'<div class="fit-header-score"><span class="fit-header-score-num">' + scoreNum + '</span><span class="fit-header-score-unit">%</span></div>' +
				'</div>' +
				'<div class="fit-header-label">AI 적합도 예측</div>' +
			'</div>' +
			'<div class="detail-radar">' +
				'<div class="detail-radar_row">' +
					'<div class="detail-radar_chart">' + svgHtml + '</div>' +
					legendHtml +
				'</div>' +
			'</div>' +
		'</div>';
	}
	var overlayHtml = '<div class="match-box-login-overlay">' +
		'<div class="auth-login-modal" style="position:relative;">' +
			'<button class="auth-login-modal_close" onclick="document.getElementById(\'rightCard\').classList.remove(\'match-box-blur\')" aria-label="닫기">' +
				'<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
			'</button>' +
			'<div class="auth-login-modal_icon">' +
				'<svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/></svg>' +
			'</div>' +
			'<h3 class="auth-login-modal_title">로그인이 필요합니다</h3>' +
			'<p class="auth-login-modal_desc">AI 적합도 예측 결과는<br>로그인 후 확인하실 수 있습니다.</p>' +
			'<div class="auth-login-modal_btns">' +
				'<a href="/bbs/register.php" class="auth-login-modal_btn auth-login-modal_btn--cancel">회원가입</a>' +
				'<a href="/bbs/login.php" class="auth-login-modal_btn auth-login-modal_btn--login">로그인</a>' +
			'</div>' +
		'</div>' +
	'</div>';
	document.getElementById('rightCard').innerHTML = buildInner(!loggedIn) + overlayHtml;
	if (!loggedIn) document.getElementById('rightCard').classList.add('match-box-blur');
	else document.getElementById('rightCard').classList.remove('match-box-blur');
})();
window._renderRightCard = function() {
	var loggedIn = window._isLoggedIn && window._isLoggedIn();
	function buildInner(isMock) {
		var scoreNum = isMock ? '--' : fitScore;
		var companyName = isMock ? '○○○○' : maskName(co.title);
		return '<div class="match-box-inner">' +
			'<div class="fit-header">' +
				'<div class="fit-header-top">' +
					'<div class="fit-header-companies">우리회사 <strong>A기업</strong> <span class="fit-header-arrow">⇄</span> 추천 <strong>B기업</strong></div>' +
					'<div class="fit-header-score"><span class="fit-header-score-num">' + scoreNum + '</span><span class="fit-header-score-unit">%</span></div>' +
				'</div>' +
				'<div class="fit-header-label">AI 적합도 예측</div>' +
			'</div>' +
			'<div class="detail-radar">' +
				'<div class="detail-radar_row">' +
					'<div class="detail-radar_chart">' + svgHtml + '</div>' +
					legendHtml +
				'</div>' +
			'</div>' +
		'</div>';
	}
	var overlayHtml = '<div class="match-box-login-overlay">' +
		'<div class="auth-login-modal" style="position:relative;">' +
			'<button class="auth-login-modal_close" onclick="document.getElementById(\'rightCard\').classList.remove(\'match-box-blur\')" aria-label="닫기">' +
				'<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
			'</button>' +
			'<div class="auth-login-modal_icon">' +
				'<svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/></svg>' +
			'</div>' +
			'<h3 class="auth-login-modal_title">로그인이 필요합니다</h3>' +
			'<p class="auth-login-modal_desc">AI 적합도 예측 결과는<br>로그인 후 확인하실 수 있습니다.</p>' +
			'<div class="auth-login-modal_btns">' +
				'<a href="/bbs/register.php" class="auth-login-modal_btn auth-login-modal_btn--cancel">회원가입</a>' +
				'<a href="/bbs/login.php" class="auth-login-modal_btn auth-login-modal_btn--login">로그인</a>' +
			'</div>' +
		'</div>' +
	'</div>';
	document.getElementById('rightCard').innerHTML = buildInner(!loggedIn) + overlayHtml;
	if (!loggedIn) document.getElementById('rightCard').classList.add('match-box-blur');
	else document.getElementById('rightCard').classList.remove('match-box-blur');
	if (window._bindRadarHover) window._bindRadarHover();
};

// 지역 접근성 툴팁(모바일)
document.querySelectorAll('.region-tip-icon').forEach(function(icon) {
	icon.addEventListener('click', function(e) {
		e.stopPropagation();
		var wrap = this.closest('.region-tip-wrap');
		wrap.classList.toggle('open');
	});
});
document.addEventListener('click', function() {
	document.querySelectorAll('.region-tip-wrap.open').forEach(function(el) { el.classList.remove('open'); });
});

/* 비로그인 우측 카드 처리 */
(function initRightCardBlur() {
	var rightCard = document.getElementById('rightCard');
	if (!rightCard) return;
	// 초기 블러
	rightCard.classList.add('match-box-blur');
	var authWaitBlur = setInterval(function() {
		if (window._isLoggedIn && window._renderRightCard) {
			clearInterval(authWaitBlur);
			window._renderRightCard(); // 로그인 상태에 맞게 재렌더
			var sw = document.getElementById('authSwitch');
			if (sw) sw.addEventListener('click', function() {
				setTimeout(window._renderRightCard, 50);
			});
		}
	}, 100);
})();

/* 레이더 호버 */
window._bindRadarHover = function() {
	var legendItems = document.querySelectorAll('.detail-radar_legend_item');
	var radarDots = document.querySelectorAll('.radar-dot');
	var radarLabelEls = document.querySelectorAll('.radar-label');

	function highlightAxis(idx) {
		legendItems.forEach(function (item, i) {
			if (i === idx) item.classList.add('is-hovered');
			else item.classList.remove('is-hovered');
		});
		radarDots.forEach(function (dot, i) {
			if (i === idx) { dot.setAttribute('r', '6'); dot.setAttribute('fill', 'rgba(109,40,217,1)'); }
			else { dot.setAttribute('r', '3.5'); dot.setAttribute('fill', 'rgba(109,40,217,0.8)'); }
		});
		radarLabelEls.forEach(function (lbl, i) {
			if (i === idx) { lbl.setAttribute('font-weight', '700'); lbl.setAttribute('fill', '#4c1d95'); }
			else {
				lbl.setAttribute('font-weight', '500');
				lbl.setAttribute('fill', '#555');
			}
		});
	}
	function clearHighlight() {
		legendItems.forEach(function (item) { item.classList.remove('is-hovered'); });
		radarDots.forEach(function (dot) { dot.setAttribute('r', '3.5'); dot.setAttribute('fill', 'rgba(109,40,217,0.8)'); });
		radarLabelEls.forEach(function (lbl) {
			lbl.setAttribute('font-weight', '500');
			lbl.setAttribute('fill', '#555');
		});
	}

	legendItems.forEach(function (item) {
		var idx = parseInt(item.getAttribute('data-axis'), 10);
		item.addEventListener('mouseenter', function () { highlightAxis(idx); });
		item.addEventListener('mouseleave', clearHighlight);
	});
	radarDots.forEach(function (dot) {
		var idx = parseInt(dot.getAttribute('data-axis'), 10);
		dot.addEventListener('mouseenter', function () { highlightAxis(idx); });
		dot.addEventListener('mouseleave', clearHighlight);
	});
	radarLabelEls.forEach(function (lbl) {
		var idx = parseInt(lbl.getAttribute('data-axis'), 10);
		lbl.addEventListener('mouseenter', function () { highlightAxis(idx); });
		lbl.addEventListener('mouseleave', clearHighlight);
	});
};
window._bindRadarHover();

/* AI 분석 버튼 */
(function initAiAnalysis() {
	var btn = document.getElementById('btnAiAnalyze');
	var body = document.getElementById('aiAnalysisBody');
	var blurBg = document.getElementById('aiBlurBg');
	var ctaOverlay = document.getElementById('aiCtaOverlay');
	var benefitText = document.getElementById('aiCtaBenefitText');
	if (!btn || !body) return;
	var richSrc = (window.RICH_ANALYSIS && window.RICH_ANALYSIS[demoId]) || co.ai_analysis_html || '';
	var demoHtml = richSrc.replace(/[\u{1F300}-\u{1F9FF}\u{2600}-\u{2B55}\u{FE00}-\u{FEFF}]/gu, '');

	function updateBenefitText() {
		if (!benefitText) return;
		benefitText.innerHTML = '두 기업 간 시너지, 거래 적합도, 협력 전략까지<br>AI가 심층 분석한 맞춤 리포트를 받아보세요.';
	}
	updateBenefitText();
	var authWait2 = setInterval(function() {
		if (window._isLoggedIn) {
			clearInterval(authWait2);
			updateBenefitText();
			var sw = document.getElementById('authSwitch');
			if (sw) sw.addEventListener('click', function() { setTimeout(updateBenefitText, 50); });
		}
	}, 100);

	var creditConfirmed = false;

	function buildAnalysisHtml(a, radar, score) {
		var h = '';
		var radarLabels = RADAR_LABEL_MAP_D;

		// 1) 요약
		h += '<h3>1. 거래 밸류에이션 요약</h3>';
		if (a.summary) h += '<p>' + esc(a.summary) + '</p>';
		if (radar) {
			h += '<table><thead><tr><th>핵심 지표</th><th>점수</th></tr></thead><tbody>';
			Object.keys(radarLabels).forEach(function(k) {
				if (radar[k] != null) {
					h += '<tr><td>' + radarLabels[k] + '</td><td>' + Math.round(radar[k]) + '</td></tr>';
				}
			});
			h += '</tbody></table>';
		}
		if (a.reasons && a.reasons.length) {
			h += '<ul>';
			a.reasons.forEach(function(r) { h += '<li>' + esc(r) + '</li>'; });
			h += '</ul>';
		}

		// 2) 상세
		h += '<hr><h3>2. 상세 분석 및 시너지 도출</h3>';
		if (a.company_brief) h += '<p>' + esc(a.company_brief) + '</p>';
		if (a.strengths) h += '<h4>강점</h4><p>' + esc(a.strengths) + '</p>';
		if (a.caution)   h += '<h4>유의사항</h4><p>' + esc(a.caution) + '</p>';

		// 3) 전략
		if (a.strategies && a.strategies.length) {
			h += '<hr><h3>3. 실무형 협력 전략 (Action Plan)</h3>';
			a.strategies.forEach(function(s) {
				h += '<h4>' + esc(s.title) + '</h4><p>' + esc(s.content) + '</p>';
				if (s.effect) h += '<p><strong>기대 효과:</strong> ' + esc(s.effect) + '</p>';
			});
		}

		// 4) 종합
		var ef = a.expected_effects || {};
		if (ef.headline || ef.financial || ef.strategic) {
			h += '<hr><h3>4. 종합 제언 및 기대 효과</h3>';
			if (ef.headline) h += '<blockquote>' + esc(ef.headline) + '</blockquote>';
			h += '<ul>';
			if (ef.financial) h += '<li><strong>재무적 기대:</strong> ' + esc(ef.financial) + '</li>';
			if (ef.strategic) h += '<li><strong>전략적 가치:</strong> ' + esc(ef.strategic) + '</li>';
			h += '</ul>';
		}

		if (a.note) h += '<p class="ai-analysis-note">' + esc(a.note) + '</p>';
		if (a.source === 'template') {
			h += '<p class="ai-analysis-note" style="color:#aaa;">※ AI 서버 응답 대기 중입니다. 잠시 후 다시 시도해 주세요.</p>';
		}
		return h || '<p>분석 결과를 가져올 수 없습니다.</p>';
	}

	function showAnalysisHtml(html) {
		var wrap = document.createElement('div');
		wrap.className = 'ai-analysis-result';
		wrap.innerHTML = html;
		body.innerHTML = '';
		body.appendChild(wrap);
		body.classList.add('ai-analysis-result-ready');
		var items = wrap.querySelectorAll('h2, h3, h4, table, blockquote, li, p');
		items.forEach(function(el) { el.style.opacity='0'; el.style.transform='translateY(8px)'; el.style.transition='opacity 0.3s, transform 0.3s'; });
		items.forEach(function(el, i) { setTimeout(function() { el.style.opacity='1'; el.style.transform='translateY(0)'; }, i * 60); });
		setTimeout(function() { btn.disabled = false; }, items.length * 60);
	}

	function runAnalysis() {
		btn.disabled = true;
		if (blurBg) blurBg.style.display = 'none';
		if (ctaOverlay) ctaOverlay.style.display = 'none';
		body.style.display = '';
		body.classList.remove('ai-analysis-result-ready');
		body.innerHTML = '<p class="ai-analysis-loading" role="status"><span class="ai-dot-pulse" aria-hidden="true"></span> AI가 기업 정보를 분석하고 있습니다…</p>';

			// 분석 요청값 구성
			var card = getCardSnapshotForDemo(demoId);
		var srcKedcd = '';
		try { srcKedcd = sessionStorage.getItem('_aiSrcKedcd') || ''; } catch(e) {}
		var tgtKedcd      = card ? (card.kedcd         || '') : '';
		var tradeType     = card ? (card.trade_type    || 1)  : 1;
		var candidateType = card ? (card.candidate_type|| 'chain2') : 'chain2';

		// 키 없으면 데모 HTML 표시
		if (!srcKedcd || !tgtKedcd) {
			setTimeout(function() {
				showAnalysisHtml(demoHtml || '<p>분석 문구가 준비되지 않았습니다.</p>');
			}, 400);
			return;
		}

		var url = '/ko/company_list_ai_api.php?action=analyze'
			+ '&src_kedcd='      + encodeURIComponent(srcKedcd)
			+ '&tgt_kedcd='      + encodeURIComponent(tgtKedcd)
			+ '&trade_type='     + encodeURIComponent(tradeType)
			+ '&candidate_type=' + encodeURIComponent(candidateType);

		fetch(url)
			.then(function(r) { return r.json(); })
			.then(function(data) {
				if (data.error) throw new Error(data.error);
				showAnalysisHtml(buildAnalysisHtml(data.analysis || {}, data.radar, data.score));
			})
			.catch(function(err) {
				body.innerHTML = '<div class="ai-analysis-result"><p style="color:#e53935;">분석 중 오류가 발생했습니다: ' + esc(err && err.message ? err.message : '알 수 없는 오류') + '</p></div>';
				body.classList.add('ai-analysis-result-ready');
				btn.disabled = false;
			});
	}

	btn.addEventListener('click', function () {
		// 비로그인 차단
		if (window._isLoggedIn && !window._isLoggedIn()) {
			if (window._requireLogin) window._requireLogin('AI 기업 분석');
			return;
		}
		// 크레딧 안내
		if (!creditConfirmed) {
			window._confirm({
				icon: 'purple',
				iconSvg: '<path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3z"/>',
				title: 'AI 기업 분석을 시작할까요?',
				desc: '<strong style="color:#6d28d9;">0.5 크레딧</strong>이 차감됩니다.<br><span style="font-size:12px;color:#999;">동일 분석은 6개월내 무료</span>',
				confirmText: '분석하기',
				cancelText: '취소',
				confirmClass: 'purple',
				onConfirm: function () {
					creditConfirmed = true;
					runAnalysis();
				}
			});
			return;
		}
		runAnalysis();
	});
})();


})();
/* 헤더 검색 핸들러 */
(function () {
  var _pendingComnum = '';
  var _pendingKedcd  = '';

  function autoHypen(v) {
    v = (v || '').replace(/[^0-9]/g, '');
    if (v.length < 4) return v;
    if (v.length < 6) return v.substr(0,3) + '-' + v.substr(3,2);
    return v.substr(0,3) + '-' + v.substr(3,2) + '-' + v.substr(5,5);
  }

  function openVerify(name) {
    var ov = document.getElementById('modal_verify_overlay');
    var el = document.getElementById('modal_verify_company');
    if (el && name) el.textContent = name;
    if (ov) { ov.classList.add('is_open'); ov.setAttribute('aria-hidden', 'false'); }
  }

  function doSearch() {
    var inp = document.getElementById('comnum');
    if (inp) inp.blur();
    var comnum = inp ? inp.value : '';
    var digits = comnum.replace(/[^0-9]/g, '');

    if (!digits.length) {
      if (window.openAiGuideModal) openAiGuideModal({ title: 'AI 추천 안내', text: '사업자 번호를 입력해 주세요.', text2: '사업자 번호를 입력하고 AI 추천받기를 누르면 AI 분석을 통해 거래처·판매처를 추천해드립니다.' });
      return;
    }
    if (digits.length !== 10) {
      if (window.openAiGuideModal) openAiGuideModal({ title: '사업자번호 재확인', text: '입력하신 사업자번호 형식이 올바르지 않습니다.', text2: '사업자번호를 다시 확인한 뒤 AI 추천받기를 눌러주세요.' });
      return;
    }

    _pendingComnum = digits;
    _pendingKedcd  = '';

    if (digits === '1868101689') { _pendingKedcd = '0008300204'; openVerify('(주)이가이엔씨'); return; }

    fetch('get_set.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: new URLSearchParams({ type: 'main_comp_chk', comnum: comnum }).toString()
    })
    .then(function(r) { return r.text(); })
    .then(function(txt) {
      var p = (txt || '').split('|');
      if (p[0] === '1000') {
        try {
          var info = JSON.parse(p.slice(1).join('|'));
          var enp  = info.CONTENTS && info.CONTENTS.ENPintr0000;
          var compName = (enp && (enp.enp_nm_trd || enp.enp_nm)) || '';
          _pendingKedcd = String(enp && enp.kedcd || '').replace(/\D/g, '').padStart(10, '0');
          // 콜드스타트 데이터 저장
          try {
            sessionStorage.setItem('_aiExtBzcCd', String(enp && enp.bzc_cd || ''));
            sessionStorage.setItem('_aiExtSido',  String(enp && enp.loc_addra || '').trim().slice(0,3));
            sessionStorage.setItem('_aiExtSze',   String(enp && enp.enp_sze  || '3'));
          } catch(e2) {}
          openVerify(compName);
        } catch(e) { openVerify(''); }
      }
      else if (p[1] === '02') { if (window.openAiGuideModal) openAiGuideModal({ title: '사업자번호 재확인', text: '입력하신 사업자번호로 기업 정보를 찾지 못했습니다.', text2: '사업자번호를 다시 확인해 주세요.' }); }
      else { if (window.openAiGuideModal) openAiGuideModal({ title: '조회 오류 안내', text: '사업자번호 조회 중 일시적인 오류가 발생했습니다.', text2: '잠시 후 다시 시도해 주세요.' }); }
    })
    .catch(function() {
      if (window.openAiGuideModal) openAiGuideModal({ title: '조회 오류 안내', text: '사업자번호 조회 중 일시적인 오류가 발생했습니다.', text2: '잠시 후 다시 시도해 주세요.' });
    });
  }

  // 이벤트 바인딩
  var form = document.getElementById('match');
  var btn  = document.getElementById('main_submit');
  var inp  = document.getElementById('comnum');
  if (form) form.addEventListener('submit', function(e) { e.preventDefault(); doSearch(); });
  if (btn)  btn.addEventListener('click',   function(e) { e.preventDefault(); doSearch(); });
  if (inp) {
    inp.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); doSearch(); } });
    inp.addEventListener('input',   function() { this.value = autoHypen(this.value); });
  }

  // 확인 버튼 -> 목록 이동
  var vGo = document.getElementById('modal_verify_btn_ai');
  if (vGo) {
    vGo.addEventListener('click', function() {
      if (window.closeVerifyModal) window.closeVerifyModal();
      var url = '/ko/company_list_ai.php?type=buyer';
      var _kedcd = _pendingKedcd || _pendingComnum;
      if (_kedcd) url += '&comnum=' + encodeURIComponent(_kedcd);
      if (typeof showAiLoading === 'function') { showAiLoading(url); }
      else { location.href = url; }
    });
  }
})();
})();
