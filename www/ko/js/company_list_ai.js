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

    var topWrap = document.querySelector('.ai-top3-swiper .swiper-wrapper');
    var restWrap = document.querySelector('.ai-rest-swiper .swiper-wrapper');
    var restHeader = document.querySelector('.ai-rest_header');
    var loadMoreWrap = document.getElementById('loadMoreWrap');
    var loadMoreBtn = document.getElementById('btnLoadMore');
    var typeBtns = document.querySelectorAll('.list-type-btn');
    var typeSlider = document.querySelector('.list-type-slider');

    if (!topWrap || !restWrap || !loadMoreBtn) return;

    var currentType = 'buyer';
    var restPageSize = 9;
    var restPageIndex = 0;

    function updateTypeSlider(activeBtn) {
        if (!typeSlider || !activeBtn) return;
        typeSlider.style.width = activeBtn.offsetWidth + 'px';
        typeSlider.style.left = activeBtn.offsetLeft + 'px';
    }

    function renderCurrent(shouldScrollToRest) {
        var data = aiData[currentType] || { top3: [], rest: [] };
        var top3 = Array.isArray(data.top3) ? data.top3 : [];
        var restPool = Array.isArray(data.rest) ? data.rest : [];

        var totalRestPages = Math.ceil(restPool.length / restPageSize) || 1;
        if (restPageIndex >= totalRestPages) restPageIndex = 0;

        var start = restPageIndex * restPageSize;
        var rest = restPool.slice(start, start + restPageSize);

        topWrap.innerHTML = top3.join('');
        restWrap.innerHTML = rest.join('');

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
    if (urlType === 'seller') setType('seller', false);
    else setType('buyer', false);
})();
