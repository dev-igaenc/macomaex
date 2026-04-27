(function() {
    'use strict';

    function toHtmlArray(arr) {
        if (!Array.isArray(arr)) return [];
        return arr
            .map(function(item) {
                if (typeof item === 'string') return item;
                if (item && typeof item.card_html === 'string') return item.card_html;
                return '';
            })
            .filter(Boolean);
    }

    function normalizeSide(side) {
        if (!side || typeof side !== 'object') return { top3: [], rest: [] };
        var top3 = toHtmlArray(side.top3);
        var rest = toHtmlArray(side.rest);
        if (!rest.length) rest = toHtmlArray(side.list);
        return { top3: top3, rest: rest };
    }

    function normalizeData(raw) {
        if (!raw || typeof raw !== 'object') return { buyer: { top3: [], rest: [] }, seller: { top3: [], rest: [] } };
        var buyerSrc = raw.buyer || raw.buy || {};
        var sellerSrc = raw.seller || raw.sell || {};
        return {
            buyer: normalizeSide(buyerSrc),
            seller: normalizeSide(sellerSrc)
        };
    }

    var aiData = normalizeData(window.__COMPANY_LIST_AI_DATA__);
    var aiMeta = window.__COMPANY_LIST_AI_META__ || {};
    var aiStats = window.__COMPANY_LIST_AI_STATS__ || {};

    var topWrap = document.querySelector('.ai-top3-swiper .swiper-wrapper');
    var restWrap = document.querySelector('.ai-rest-swiper .swiper-wrapper');
    var restHeader = document.querySelector('.ai-rest_header');
    var loadMoreWrap = document.getElementById('loadMoreWrap');
    var loadMoreBtn = document.getElementById('btnLoadMore');
    var typeBtns = document.querySelectorAll('.list-type-btn');
    var typeSlider = document.querySelector('.list-type-slider');
    var bannerStatEls = document.querySelectorAll('.ai-banner_stat_value');

    if (!topWrap || !restWrap || !loadMoreBtn) return;

    var currentType = 'buyer';
    var restPageSize = 9;
    var restPageIndex = 0;

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getNotice(type, key, fallback) {
        var side = aiMeta && typeof aiMeta === 'object' ? aiMeta[type] : null;
        if (side && typeof side[key] === 'string' && side[key].trim() !== '') return side[key];
        return fallback;
    }

    function buildEmptyStateHtml(message) {
        return '<div class="ai-empty-state"><p class="ai-empty-state_text">' + escapeHtml(message) + '</p></div>';
    }

    function updateTypeSlider(activeBtn) {
        if (!typeSlider || !activeBtn) return;
        typeSlider.style.width = activeBtn.offsetWidth + 'px';
        typeSlider.style.left = activeBtn.offsetLeft + 'px';
    }

    function parseTotal(value) {
        var n = Number(value);
        if (!Number.isFinite(n) || n < 0) return null;
        return Math.round(n);
    }

    function fallbackTotal(side) {
        if (!side || typeof side !== 'object') return 0;
        var top3 = Array.isArray(side.top3) ? side.top3.length : 0;
        var rest = Array.isArray(side.rest) ? side.rest.length : 0;
        return top3 + rest;
    }

    function updateBannerStats(stats) {
        if (!bannerStatEls || bannerStatEls.length < 3) return;
        var buyerTotal = parseTotal(stats && stats.buyer_total);
        var sellerTotal = parseTotal(stats && stats.seller_total);
        if (buyerTotal === null) buyerTotal = fallbackTotal(aiData.buyer);
        if (sellerTotal === null) sellerTotal = fallbackTotal(aiData.seller);
        bannerStatEls[1].textContent = String(buyerTotal);
        bannerStatEls[2].textContent = String(sellerTotal);
    }

    function renderCurrent(shouldScrollToRest) {
        var data = aiData[currentType] || { top3: [], rest: [] };
        var top3 = Array.isArray(data.top3) ? data.top3 : [];
        var restPool = Array.isArray(data.rest) ? data.rest : [];

        var totalRestPages = Math.ceil(restPool.length / restPageSize) || 1;
        if (restPageIndex >= totalRestPages) restPageIndex = 0;

        var start = restPageIndex * restPageSize;
        var rest = restPool.slice(start, start + restPageSize);

        topWrap.innerHTML = top3.length
            ? top3.join('')
            : buildEmptyStateHtml(getNotice(currentType, 'top3_notice', '추천 결과가 없습니다.'));
        restWrap.innerHTML = rest.length
            ? rest.join('')
            : buildEmptyStateHtml(getNotice(currentType, 'rest_notice', '추가 추천 결과가 없습니다.'));

        if (loadMoreWrap) {
            loadMoreWrap.style.display = totalRestPages > 1 ? 'flex' : 'none';
        }

        if (shouldScrollToRest && restHeader) {
            restHeader.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function syncTypeUrl(type) {
        try {
            var url = new URL(window.location.href);
            url.searchParams.set('type', type);
            window.history.replaceState({ type: type }, '', url.toString());
        } catch (e) {}
    }

    function setType(type, shouldSyncUrl) {
        if (type !== 'buyer' && type !== 'seller') type = 'buyer';
        currentType = type;
        restPageIndex = 0;
        var activeBtn = null;
        typeBtns.forEach(function(b) {
            var on = b.getAttribute('data-type') === type;
            b.classList.toggle('on', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
            if (on) activeBtn = b;
        });
        if (activeBtn) updateTypeSlider(activeBtn);
        if (shouldSyncUrl) syncTypeUrl(type);
        renderCurrent(false);
    }

    typeBtns.forEach(function(btn) {
        btn.setAttribute('aria-pressed', btn.classList.contains('on') ? 'true' : 'false');
        btn.addEventListener('click', function() {
            var nextType = btn.getAttribute('data-type') || 'buyer';
            if (nextType === currentType) return;
            setType(nextType, true);
        });
    });

    loadMoreBtn.addEventListener('click', function() {
        var data = aiData[currentType] || { rest: [] };
        var restPool = Array.isArray(data.rest) ? data.rest : [];
        var totalRestPages = Math.ceil(restPool.length / restPageSize) || 1;
        restPageIndex = (restPageIndex + 1) % totalRestPages;
        renderCurrent(true);
    });

    window.addEventListener('resize', function() {
        var activeBtn = document.querySelector('.list-type-btn.on');
        if (activeBtn) updateTypeSlider(activeBtn);
    });

    // 외부에서 실제 API 응답 연결 시 데이터 주입 가능하게 노출
    window.COMPANY_LIST_AI = {
        setData: function(nextData) {
            aiData = normalizeData(nextData);
            var nextStats = {};
            if (nextData && typeof nextData === 'object') {
                var buyerSrc = nextData.buyer || nextData.buy || {};
                var sellerSrc = nextData.seller || nextData.sell || {};
                nextStats.buyer_total = parseTotal(buyerSrc.total);
                nextStats.seller_total = parseTotal(sellerSrc.total);
            }
            aiStats = {
                buyer_total: nextStats.buyer_total !== null ? nextStats.buyer_total : fallbackTotal(aiData.buyer),
                seller_total: nextStats.seller_total !== null ? nextStats.seller_total : fallbackTotal(aiData.seller)
            };
            updateBannerStats(aiStats);
            restPageIndex = 0;
            renderCurrent(false);
        },
        setType: function(nextType) {
            setType(nextType, false);
        },
        render: function() {
            renderCurrent(false);
        }
    };

    var urlType = null;
    try {
        urlType = new URLSearchParams(window.location.search).get('type');
    } catch (e) {}
    updateBannerStats(aiStats);
    if (urlType === 'seller') setType('seller', false);
    else setType('buyer', false);
})();
