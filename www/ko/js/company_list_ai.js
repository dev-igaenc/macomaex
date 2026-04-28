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
    var top3Swiper = null;
    var restSwiper = null;

    function destroySwiper(instance) {
        if (!instance || typeof instance.destroy !== 'function') return null;
        instance.destroy(true, true);
        return null;
    }

    function initAiSwipers() {
        top3Swiper = destroySwiper(top3Swiper);
        restSwiper = destroySwiper(restSwiper);

        if (typeof Swiper === 'undefined' || window.innerWidth > 1024) return;

        var topEl = document.querySelector('.ai-top3-swiper');
        if (topEl) {
            top3Swiper = new Swiper(topEl, {
                slidesPerView: 'auto',
                spaceBetween: 25,
                grabCursor: true,
                resistanceRatio: 0.65
            });
        }

        var restEl = document.querySelector('.ai-rest-swiper');
        if (restEl) {
            var paginationEl = restEl.querySelector('.swiper-pagination');
            var options = {
                slidesPerView: 'auto',
                spaceBetween: 25,
                grabCursor: true,
                resistanceRatio: 0.65
            };
            if (paginationEl) {
                options.pagination = {
                    el: paginationEl,
                    clickable: true
                };
            }
            restSwiper = new Swiper(restEl, options);
        }
    }

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

    function radarLabelToKey(label) {
        var map = {
            '공급망 경로': 'supply_chain',
            '거래처 중첩도': 'network_overlap',
            '재무 건전성': 'financial_health',
            '시장 거래력': 'market_power',
            '거래 다각화': 'trade_diversity',
            '규모 적합도': 'scale_match',
            '기업 역량': 'company_size',
            '지역 접근성': 'accessibility',
            '업종 연관성': 'industry_fit',
            '거래 지속성': 'trade_loyalty',
            '품목 보완성': 'product_fit',
            '성장 추세': 'growth_trend'
        };
        return map[String(label || '').trim()] || '';
    }

    function collectCardSnapshot(linkEl) {
        if (!linkEl) return null;
        var kedcd = String(linkEl.getAttribute('data-kedcd') || '').replace(/[^0-9]/g, '');
        if (kedcd.length !== 10) return null;
        // 목록 -> 상세 이동 시 추천 기준기업(src)도 같이 저장
        var srcKedcd = String(linkEl.getAttribute('data-src-kedcd') || '').replace(/[^0-9]/g, '');
        if (srcKedcd.length !== 10) srcKedcd = '';

        var cardEl = linkEl.querySelector('.mCompanyCard') || linkEl;
        var titleEl = cardEl.querySelector('.mCompanyCard_title');
        var scorePct = Number(linkEl.getAttribute('data-score') || 0);
        if (!Number.isFinite(scorePct) || scorePct < 0) scorePct = 0;

        var tags = [];
        cardEl.querySelectorAll('.mCompanyCard_desc_tag').forEach(function(el) {
            var t = String(el.textContent || '').trim();
            if (t) tags.push(t);
        });

        var badges = [];
        cardEl.querySelectorAll('.mCompanyCard_badge').forEach(function(el) {
            var b = String(el.textContent || '').trim();
            if (b) badges.push(b);
        });
        var badgeGrowth = null;
        var badgeStability = null;
        var badgeVenture = 'N';
        badges.forEach(function(text) {
            var growth = String(text || '').match(/성장TOP\s*([0-9]+(?:\.[0-9]+)?)%?/);
            if (growth && growth[1]) badgeGrowth = growth[1];
            var stability = String(text || '').match(/재무안정TOP\s*([0-9]+(?:\.[0-9]+)?)%?/);
            if (stability && stability[1]) badgeStability = stability[1];
            if (String(text || '').indexOf('벤처') !== -1) badgeVenture = 'Y';
        });

        var radar = {};
        cardEl.querySelectorAll('.radar-card_bar').forEach(function(row) {
            var labelEl = row.querySelector('.radar-card_bar_label');
            var valueEl = row.querySelector('.radar-card_bar_value');
            var key = radarLabelToKey(labelEl ? labelEl.textContent : '');
            var value = Number(valueEl ? valueEl.textContent : 0);
            if (!key || !Number.isFinite(value)) return;
            radar[key] = value;
        });

        return {
            kedcd: kedcd,
            src_kedcd: srcKedcd,
            title: titleEl ? String(titleEl.textContent || '').trim() : '',
            tags: tags,
            badges: badges,
            badge_growth: badgeGrowth,
            badge_stability: badgeStability,
            badge_venture: badgeVenture,
            radar: radar,
            trade_type: Number(linkEl.getAttribute('data-trade-type') || 1) || 1,
            ai_match: Math.round(scorePct),
            score: Number((scorePct / 20).toFixed(2)),
            candidate_type: 'chain2'
        };
    }

    function saveCardSnapshot(linkEl) {
        var snapshot = collectCardSnapshot(linkEl);
        if (!snapshot) return;
        try {
            var cardMap = {};
            var rawMap = sessionStorage.getItem('_aiCardMap');
            if (rawMap) cardMap = JSON.parse(rawMap) || {};
            cardMap[snapshot.kedcd] = snapshot;
            sessionStorage.setItem('_aiCardMap', JSON.stringify(cardMap));
            sessionStorage.setItem('_aiCard', JSON.stringify(snapshot));

            var srcKedcd = String(snapshot.src_kedcd || '').replace(/[^0-9]/g, '');
            try {
                // data-src-kedcd가 비어있으면 현재 목록 URL의 src/kedcd를 fallback으로 사용
                if (srcKedcd.length !== 10) {
                    var qs = new URLSearchParams(window.location.search);
                    srcKedcd = String(qs.get('src_kedcd') || qs.get('kedcd') || '').replace(/[^0-9]/g, '');
                }
            } catch (e) {}
            if (srcKedcd.length === 10) sessionStorage.setItem('_aiSrcKedcd', srcKedcd);
        } catch (e) {}
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
            loadMoreWrap.style.display = restPool.length > 0 ? 'flex' : 'none';
        }

        initAiSwipers();

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

        if (totalRestPages > 1) {
            restPageIndex = (restPageIndex + 1) % totalRestPages;
        } else if (restPool.length > 1) {
            // 1페이지뿐이어도 버튼 클릭 시 카드 순서를 회전해 "다른 추천 보기" 동작 작동
            var rotateBy = Math.min(3, restPool.length - 1);
            for (var i = 0; i < rotateBy; i++) {
                restPool.push(restPool.shift());
            }
            data.rest = restPool;
            aiData[currentType] = data;
            restPageIndex = 0;
        }

        renderCurrent(true);
    });

    // 카드 클릭 시 상세 페이지에서 사용할 스냅샷을 저장한다.
    document.addEventListener('click', function(event) {
        var linkEl = event.target && event.target.closest ? event.target.closest('.company-ai-link') : null;
        if (!linkEl) return;
        saveCardSnapshot(linkEl);
    });

    window.addEventListener('resize', function() {
        var activeBtn = document.querySelector('.list-type-btn.on');
        if (activeBtn) updateTypeSlider(activeBtn);
        initAiSwipers();
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
