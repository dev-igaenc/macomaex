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

    function isBackForwardNavigation() {
        try {
            var nav = performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
            return !!(nav && nav.type === 'back_forward');
        } catch (e) {}
        return false;
    }

    function recommendationSnapshotKey() {
        return 'companyAiListSnapshot:' + window.location.pathname + window.location.search;
    }

    var recommendationSnapshotMaxAgeMs = 60 * 60 * 1000;

    function removeRecommendationSnapshot(snapshotKey) {
        try {
            sessionStorage.removeItem(snapshotKey);
            if ((sessionStorage.getItem('companyAiListReturnKey') || '') === snapshotKey) {
                sessionStorage.removeItem('companyAiListReturnKey');
            }
        } catch (e) {}
    }

    function pruneRecommendationSnapshots() {
        try {
            var now = Date.now();
            var removeKeys = [];
            for (var i = 0; i < sessionStorage.length; i++) {
                var key = sessionStorage.key(i) || '';
                if (key.indexOf('companyAiListSnapshot:') !== 0) continue;
                var raw = sessionStorage.getItem(key);
                var snapshot = raw ? JSON.parse(raw) : null;
                var savedAt = Number(snapshot && snapshot.saved_at);
                if (!Number.isFinite(savedAt) || now - savedAt > recommendationSnapshotMaxAgeMs) {
                    removeKeys.push(key);
                }
            }
            removeKeys.forEach(removeRecommendationSnapshot);
        } catch (e) {}
    }

    function readRecommendationSnapshot() {
        try {
            var snapshotKey = recommendationSnapshotKey();
            var returnKey = sessionStorage.getItem('companyAiListReturnKey') || '';
            if (!isBackForwardNavigation() && returnKey !== snapshotKey) return null;
            var raw = sessionStorage.getItem(snapshotKey);
            if (!raw) return null;
            var snapshot = JSON.parse(raw);
            if (!snapshot || !snapshot.data || !snapshot.meta || !snapshot.stats) return null;
            var savedAt = Number(snapshot.saved_at);
            if (!Number.isFinite(savedAt) || Date.now() - savedAt > recommendationSnapshotMaxAgeMs) {
                removeRecommendationSnapshot(snapshotKey);
                return null;
            }
            if (returnKey === snapshotKey) sessionStorage.removeItem('companyAiListReturnKey');
            return snapshot;
        } catch (e) {
            return null;
        }
    }

    function saveRecommendationSnapshot(data, meta, stats) {
        try {
            pruneRecommendationSnapshots();
            sessionStorage.setItem(recommendationSnapshotKey(), JSON.stringify({
                data: data,
                meta: meta,
                stats: stats,
                saved_at: Date.now()
            }));
        } catch (e) {}
    }

    var restoredSnapshot = readRecommendationSnapshot();
    var aiData = normalizeData(restoredSnapshot ? restoredSnapshot.data : window.__COMPANY_LIST_AI_DATA__);
    var aiMeta = restoredSnapshot ? restoredSnapshot.meta : (window.__COMPANY_LIST_AI_META__ || {});
    var aiStats = restoredSnapshot ? restoredSnapshot.stats : (window.__COMPANY_LIST_AI_STATS__ || {});
    if (!restoredSnapshot) {
        saveRecommendationSnapshot(window.__COMPANY_LIST_AI_DATA__ || {}, aiMeta, aiStats);
    }

    var topWrap = document.querySelector('.ai-top3-swiper .swiper-wrapper');
    var restWrap = document.querySelector('.ai-rest-swiper .swiper-wrapper');
    var restHeader = document.querySelector('.ai-rest_header');
    var loadMoreWrap = document.getElementById('loadMoreWrap');
    var loadMoreBtn = document.getElementById('btnLoadMore');
    var typeBtns = document.querySelectorAll('.list-type-btn');
    var typeSlider = document.querySelector('.list-type-slider');
    var bannerStatEls = document.querySelectorAll('.ai-banner_stat_value');
    var loadingOverlay = document.getElementById('companyAiLoadingOverlay');
    var loadingDesc = document.getElementById('companyAiLoadingDesc');
    var loadingHelp = document.getElementById('companyAiLoadingHelp');
    var loadingProgressFill = document.getElementById('companyAiLoadingProgressFill');
    var loadingActions = document.getElementById('companyAiLoadingActions');
    var loadingRetryBtn = document.getElementById('companyAiLoadingRetryBtn');
    var loadingBackBtn = document.getElementById('companyAiLoadingBackBtn');
    var loadingSteps = document.querySelectorAll('#companyAiLoadingOverlay .ai-loading_step');
    var loadingTimer = null;
    var loadingHardTimeout = null;
    var loadingProgressTimer = null;
    var loadingStepTimer = null;
    var loadingDone = false;
    var loadingOpenedAt = 0;
    var loadingMinVisibleMs = 2200;
    var loadingTimeoutMs = 8000;
    var loadingStepSegments = 6;
    var loadingStepDoneCount = 0;
    var loadingFinishRequested = false;
    var forceTimeoutMode = false;
    var loadingSessionKeyPrefix = 'companyAiLoadingShown:';
    var loadingSourceKey = '';
    var shouldShowLoadingOnce = true;
    try {
        forceTimeoutMode = (new URLSearchParams(window.location.search).get('qa_timeout') === '1');
    } catch (e) {
        forceTimeoutMode = false;
    }

    function normalizeDigits(value, length) {
        var digits = String(value || '').replace(/[^0-9]/g, '');
        if (length && digits.length !== length) return '';
        return digits;
    }

    function resolveLoadingSourceKey() {
        try {
            var params = new URLSearchParams(window.location.search);
            var rawBizno = params.get('bizno') || params.get('comnum') || '';
            var bizno = normalizeDigits(rawBizno, 10);
            if (bizno) return 'bizno:' + bizno;
            if (rawBizno) return 'bizno_token:' + rawBizno;

            var srcKedcd = normalizeDigits(params.get('src_kedcd'), 10);
            if (srcKedcd) return 'src_kedcd:' + srcKedcd;

            var kedcd = normalizeDigits(params.get('kedcd'), 10);
            if (kedcd) return 'kedcd:' + kedcd;
        } catch (e) {}
        return '';
    }

    function shouldShowLoadingOverlay() {
        loadingSourceKey = resolveLoadingSourceKey();
        if (!loadingSourceKey) return false;
        if (isBackForwardNavigation()) return false;
        return true;
    }

    function hideLoadingOverlayImmediately() {
        if (!loadingOverlay) return;
        loadingOverlay.classList.remove('is_open');
        loadingOverlay.setAttribute('aria-busy', 'false');
        setLoadingNoticeVisible(false);
        setLoadingActionsVisible(false);
        if (loadingProgressFill) loadingProgressFill.style.width = '100%';
    }

    function clearLoadingTimers() {
        if (loadingTimer) {
            clearTimeout(loadingTimer);
            loadingTimer = null;
        }
        if (loadingHardTimeout) {
            clearTimeout(loadingHardTimeout);
            loadingHardTimeout = null;
        }
        if (loadingProgressTimer) {
            clearInterval(loadingProgressTimer);
            loadingProgressTimer = null;
        }
        if (loadingStepTimer) {
            clearInterval(loadingStepTimer);
            loadingStepTimer = null;
        }
    }

    function setLoadingActionsVisible(visible) {
        if (!loadingActions) return;
        if (visible) loadingActions.removeAttribute('hidden');
        else loadingActions.setAttribute('hidden', 'hidden');
    }

    function setLoadingNoticeVisible(visible) {
        if (loadingDesc) {
            if (visible) loadingDesc.removeAttribute('hidden');
            else loadingDesc.setAttribute('hidden', 'hidden');
        }
        if (loadingHelp) {
            if (visible) loadingHelp.removeAttribute('hidden');
            else loadingHelp.setAttribute('hidden', 'hidden');
        }
    }

    function handleLoadingTimeout() {
        if (!loadingOverlay || loadingDone) return;
        clearLoadingTimers();
        loadingDone = false;
        loadingOverlay.classList.add('is_open');
        loadingOverlay.setAttribute('aria-busy', 'false');
        if (loadingProgressFill) loadingProgressFill.style.width = '100%';
        if (loadingDesc) loadingDesc.textContent = '응답 시간이 길어지고 있습니다.';
        if (loadingHelp) loadingHelp.textContent = '네트워크 상태를 확인한 뒤 다시 시도해 주세요.';
        setLoadingNoticeVisible(true);
        loadingSteps.forEach(function(step) { step.classList.add('done'); });
        setLoadingActionsVisible(true);
    }

    function resetLoadingSteps() {
        loadingStepDoneCount = 0;
        loadingFinishRequested = false;
        loadingSteps.forEach(function(step) {
            step.classList.remove('done');
            var textEl = step.querySelector('.ai-loading_step_text');
            if (!textEl) return;
            var pending = textEl.getAttribute('data-pending');
            if (pending) textEl.textContent = pending;
        });
    }

    function markLoadingStepDone(index) {
        if (!loadingSteps[index]) return;
        var step = loadingSteps[index];
        if (step.classList.contains('done')) return;
        step.classList.add('done');
        loadingStepDoneCount += 1;
        var textEl = step.querySelector('.ai-loading_step_text');
        if (!textEl) return;
        var done = textEl.getAttribute('data-done');
        if (done) textEl.textContent = done;
        if (loadingFinishRequested && loadingStepDoneCount >= Math.min(loadingStepSegments, loadingSteps.length)) {
            finishLoadingOverlay();
        }
    }

    function startLoadingOverlay() {
        if (!loadingOverlay) return;
        loadingOverlay.classList.add('is_open');
        loadingOverlay.setAttribute('aria-busy', 'true');
        loadingDone = false;
        loadingOpenedAt = Date.now();
        resetLoadingSteps();
        if (loadingDesc) loadingDesc.textContent = '거래 데이터를 분석해 맞춤 추천을 불러오는 중입니다.';
        if (loadingHelp) loadingHelp.textContent = '응답이 지연되면 잠시 후 자동으로 안내 메시지가 표시됩니다.';
        setLoadingNoticeVisible(false);
        if (loadingProgressFill) loadingProgressFill.style.width = '8%';
        setLoadingActionsVisible(false);
        clearLoadingTimers();
        var progress = 8;
        loadingProgressTimer = setInterval(function() {
            if (loadingDone) return;
            progress = Math.min(progress + 3, 92);
            if (loadingProgressFill) loadingProgressFill.style.width = progress + '%';
        }, 220);
        var stepIndex = 0;
        loadingTimer = setTimeout(function() {
            if (loadingDone) return;
            if (loadingDesc) loadingDesc.textContent = '응답이 지연되고 있습니다.';
            if (loadingHelp) loadingHelp.textContent = '잠시 후에도 동일하면 새로고침 후 다시 시도해 주세요.';
            setLoadingNoticeVisible(true);
        }, 5000);
        var stepIntervalMs = Math.max(1, Math.floor(loadingMinVisibleMs / Math.max(loadingStepSegments, 1)));
        clearInterval(loadingStepTimer);
        loadingStepTimer = setInterval(function() {
            if (loadingDone) return;
            markLoadingStepDone(stepIndex);
            stepIndex += 1;
            if (stepIndex >= Math.min(loadingStepSegments, loadingSteps.length)) {
                clearInterval(loadingStepTimer);
                loadingStepTimer = null;
            }
        }, stepIntervalMs);
        loadingHardTimeout = setTimeout(handleLoadingTimeout, loadingTimeoutMs);
    }

    function finishLoadingOverlay() {
        if (!loadingOverlay || loadingDone) return;
        if (forceTimeoutMode) return;
        if (loadingStepDoneCount < Math.min(loadingStepSegments, loadingSteps.length)) {
            loadingFinishRequested = true;
            return;
        }
        loadingDone = true;
        clearLoadingTimers();
        if (loadingProgressFill) loadingProgressFill.style.width = '100%';
        loadingOverlay.setAttribute('aria-busy', 'false');
        setLoadingNoticeVisible(false);
        setLoadingActionsVisible(false);
        var elapsed = loadingOpenedAt > 0 ? (Date.now() - loadingOpenedAt) : loadingMinVisibleMs;
        var waitMs = Math.max(0, loadingMinVisibleMs - elapsed);
        setTimeout(function() {
            loadingOverlay.classList.remove('is_open');
        }, waitMs + 180);
    }

    function failLoadingOverlay(message) {
        if (!loadingOverlay) return;
        clearLoadingTimers();
        loadingDone = false;
        loadingOverlay.classList.add('is_open');
        loadingOverlay.setAttribute('aria-busy', 'false');
        if (loadingProgressFill) loadingProgressFill.style.width = '100%';
        if (loadingDesc) loadingDesc.textContent = message || '추천 데이터를 불러오지 못했습니다.';
        if (loadingHelp) loadingHelp.textContent = '새로고침 후 다시 시도해 주세요.';
        setLoadingNoticeVisible(true);
        loadingSteps.forEach(function(step) { step.classList.add('done'); });
        setLoadingActionsVisible(true);
    }

    if (loadingRetryBtn) {
        loadingRetryBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }

    if (loadingBackBtn) {
        loadingBackBtn.addEventListener('click', function() {
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
            window.location.href = '/ko/company.php';
        });
    }

    shouldShowLoadingOnce = shouldShowLoadingOverlay();
    if (shouldShowLoadingOnce) startLoadingOverlay();
    else hideLoadingOverlayImmediately();

    if (!topWrap || !restWrap || !loadMoreBtn) {
        failLoadingOverlay('목록 화면을 준비하지 못했습니다.');
        return;
    }

    // 서버가 확정한 한 방향만 표시한다. 목록 페이지 내 구매/판매 탭 전환은 비활성화 상태다.
    var requestedType = window.__COMPANY_LIST_AI_REQUESTED_TYPE__ === 'seller' ? 'seller' : 'buyer';
    var currentType = requestedType;
    var restPageSize = 9;
    var restPageIndex = 0;
    var top3Swiper = null;
    var restSwiper = null;
    var isDemoMode = false;
    try {
        isDemoMode = (new URLSearchParams(window.location.search).get('entry') === 'matching_ai_demo');
    } catch (e) {
        isDemoMode = false;
    }

    function destroySwiper(instance) {
        if (!instance || typeof instance.destroy !== 'function') return null;
        instance.destroy(true, true);
        return null;
    }

    function initAiSwipers() {
        top3Swiper = destroySwiper(top3Swiper);
        restSwiper = destroySwiper(restSwiper);

        if (typeof Swiper === 'undefined' || window.innerWidth > 1024) return;
        if (isDemoMode) return;

        // 모바일에서 세로 스크롤을 우선 보장하면서 가로 스와이프만 반응하도록 옵션을 맞춘다.
        var mobileTouchOptions = {
            touchStartPreventDefault: false,
            touchStartForcePreventDefault: false,
            touchMoveStopPropagation: false,
            touchReleaseOnEdges: true,
            threshold: 10,
            passiveListeners: true,
            nested: true
        };

        var topEl = document.querySelector('.ai-top3-swiper');
        if (topEl) {
            var topOptions = Object.assign({
                slidesPerView: 'auto',
                spaceBetween: 25,
                grabCursor: true,
                resistanceRatio: 0.65
            }, mobileTouchOptions);
            top3Swiper = new Swiper(topEl, topOptions);
        }

        var restEl = document.querySelector('.ai-rest-swiper');
        if (restEl) {
            var paginationEl = restEl.querySelector('.swiper-pagination');
            var options = Object.assign({
                slidesPerView: 'auto',
                spaceBetween: 25,
                grabCursor: true,
                resistanceRatio: 0.65
            }, mobileTouchOptions);
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
        // company_detail_ai에서 LLM payload를 만들 수 있도록 카드 메타를 함께 수집
        var candidateType = String(linkEl.getAttribute('data-candidate-type') || 'chain2').trim() || 'chain2';
        var bzcCd = String(linkEl.getAttribute('data-bzc-cd') || '').trim();
        var bzcNm = String(linkEl.getAttribute('data-bzc-nm') || '').trim();
        var sido = String(linkEl.getAttribute('data-sido') || '').trim();
        var sze = parseInt(String(linkEl.getAttribute('data-sze') || '0'), 10);
        if (!Number.isFinite(sze) || sze < 0) sze = 0;
        var companyAge = parseInt(String(linkEl.getAttribute('data-company-age') || '0'), 10);
        if (!Number.isFinite(companyAge) || companyAge < 0) companyAge = 0;
        var salesGrowthPct = linkEl.getAttribute('data-sales-growth-pct');
        var debtRatioPct = linkEl.getAttribute('data-debt-ratio-pct');
        var consecProfit2y = linkEl.getAttribute('data-consec-profit-2y');
        var capitalNegative = linkEl.getAttribute('data-capital-negative');
        var oneLine = String(linkEl.getAttribute('data-one-line') || '').trim();

        var parseNullableNumber = function (value) {
            if (value == null || value === '') return null;
            var n = Number(value);
            return Number.isFinite(n) ? n : null;
        };
        var parseNullableBinary = function (value) {
            if (value == null || value === '') return null;
            return Number(value) ? 1 : 0;
        };

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
            industry: bzcCd,
            industry_nm: bzcNm,
            region: sido,
            sze: sze,
            company_age: companyAge,
            sales_growth_pct: parseNullableNumber(salesGrowthPct),
            debt_ratio_pct: parseNullableNumber(debtRatioPct),
            consec_profit_2y: parseNullableBinary(consecProfit2y),
            capital_negative: parseNullableBinary(capitalNegative),
            tags: tags,
            badges: badges,
            badge_growth: badgeGrowth,
            badge_stability: badgeStability,
            badge_venture: badgeVenture,
            radar: radar,
            trade_type: Number(linkEl.getAttribute('data-trade-type') || 1) || 1,
            ai_match: Math.round(scorePct),
            score: Number((scorePct / 20).toFixed(2)),
            candidate_type: candidateType,
            one_line: oneLine
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

        // 첫 렌더 완료 시 실제 로딩 오버레이를 닫는다.
        finishLoadingOverlay();
    }

    function syncTypeUrl(type) {
        try {
            var url = new URL(window.location.href);
            url.searchParams.set('type', type);
            window.history.replaceState({ type: type }, '', url.toString());
        } catch (e) {}
    }

    function setType(type, shouldSyncUrl) {
        type = requestedType;
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

    /*
    // 구매처/판매처 찾기 진입 방향과 다른 목록으로 전환되지 않도록 탭 이벤트를 임시 비활성화한다.
    typeBtns.forEach(function(btn) {
        btn.setAttribute('aria-pressed', btn.classList.contains('on') ? 'true' : 'false');
        btn.addEventListener('click', function() {
            var nextType = btn.getAttribute('data-type') || 'buyer';
            if (nextType === currentType) return;
            setType(nextType, true);
        });
    });
    */

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
        if (linkEl.tagName !== 'A') return;
        saveRecommendationSnapshot(aiData, aiMeta, aiStats);
        var opensInCurrentTab = event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey && linkEl.target !== '_blank';
        if (opensInCurrentTab) {
            try { sessionStorage.setItem('companyAiListReturnKey', recommendationSnapshotKey()); } catch (e) {}
        }
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
            saveRecommendationSnapshot(aiData, aiMeta, aiStats);
            renderCurrent(false);
        },
        setType: function(nextType) {
            setType(nextType, false);
        },
        render: function() {
            renderCurrent(false);
        }
    };

    updateBannerStats(aiStats);
    setType(requestedType, false);
})();
