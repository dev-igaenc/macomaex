(function (window, document, $) {
    'use strict';

    // BFCache(뒤로가기 복원)로 이전 로그인 상태 DOM이 남는 경우를 방지한다.
    window.addEventListener('pageshow', function (event) {
        if (event && event.persisted) {
            window.location.reload();
        }
    });

    // PHP에서 내려준 페이지 설정값
    var cfg = window.MATCHING_LIST_CONFIG || {};
    var pageSize = parseInt(cfg.pageSize, 10) || parseInt(cfg.freeLimit, 10) || 9;
    var canLoadMore = !!cfg.canLoadMore;
    var isServerPaged = !!cfg.isServerPaged;
    var totalByType = cfg.totalByType || { buyer: 0, seller: 0 };
    var totalCount = parseInt(cfg.totalCount, 10);
    if (isNaN(totalCount)) {
        totalCount = (parseInt(totalByType.buyer, 10) || 0) + (parseInt(totalByType.seller, 10) || 0);
    }
    var currentCat = cfg.currentCat || 'all';
    var categories = Array.isArray(cfg.listCategories) ? cfg.listCategories : [];
    var isLoadingMore = false;
    var moreBtnDefaultText = '더보기';

    var sortLabelMap = {
        rank: '랭킹순',
        grow: '성장성순',
        stable: '안정성순'
    };

    // 안전한 HTML 출력용 이스케이프
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str == null ? '' : String(str)));
        return div.innerHTML;
    }

    function centerItemInScroller(scroller, item, behavior) {
        if (!scroller || !item || !scroller.clientWidth) return;

        var targetLeft = item.offsetLeft - ((scroller.clientWidth - item.offsetWidth) / 2);
        var maxLeft = scroller.scrollWidth - scroller.clientWidth;
        targetLeft = Math.max(0, Math.min(targetLeft, maxLeft));

        if (typeof scroller.scrollTo === 'function') {
            try {
                scroller.scrollTo({
                    left: targetLeft,
                    behavior: behavior || 'auto'
                });
                return;
            } catch (err) {}
        }

        scroller.scrollLeft = targetLeft;
    }

    function centerActiveCategoryItem(scroller, behavior) {
        if (!scroller) return;

        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                centerItemInScroller(scroller, scroller.querySelector('a.on'), behavior);
            });
        });
    }

    function getLoginReturnUrl() {
        if (window.MacomaLoginGuard && typeof window.MacomaLoginGuard.buildLoginUrl === 'function') {
            return window.MacomaLoginGuard.buildLoginUrl(window.location.pathname + window.location.search);
        }
        return '/bbs/login.php?url=' + encodeURIComponent(window.location.pathname + window.location.search);
    }

    function openLoginRequiredModal(descriptionHtml) {
        if (window.MacomaLoginGuard && typeof window.MacomaLoginGuard.show === 'function') {
            window.MacomaLoginGuard.show({
                titleId: 'matchingListLoginModalTitle',
                descriptionHtml: descriptionHtml || '더보기는<br>로그인 후 이용하실 수 있습니다.',
                returnUrl: window.location.pathname + window.location.search
            });
            return;
        }

        window.location.href = getLoginReturnUrl();
    }

    // 카테고리 이동 시 정렬은 항상 랭킹순으로 고정
    function buildCategoryHref(catSlug) {
        var params = new URLSearchParams(window.location.search);
        params.set('category', catSlug);
        params.delete('cat');
        params.delete('offset');
        params.set('page_size', pageSize);
        params.delete('type');
        params.set('sort', 'rank');
        return '/companies?' + params.toString();
    }

    // 사이드바/모바일 카테고리 링크의 sort/page 파라미터 동기화
    function syncNavLinkParams() {
        var selectors = [
            '.list-sidebar a[href]',
            '.list-cat-tabs a[href]',
            '.list-cat-sub a[href]',
            '.list-cat-dropdown a[href]'
        ];

        var links = document.querySelectorAll(selectors.join(','));
        links.forEach(function (a) {
            if (a.classList.contains('list-sidebar_ai')) return;
            var href = a.getAttribute('href') || '';
            if (!href || href.indexOf('javascript:') === 0 || href.indexOf('#') === 0) return;

            var url;
            try {
                url = new URL(a.href, window.location.origin);
            } catch (err) {
                return;
            }

            url.searchParams.delete('type');
            url.searchParams.set('sort', 'rank');
            url.searchParams.set('page_size', pageSize);
            url.searchParams.delete('offset');
            if (url.searchParams.has('cat') && !url.searchParams.has('category')) {
                url.searchParams.set('category', url.searchParams.get('cat'));
            }
            url.searchParams.delete('cat');

            a.setAttribute('href', '/companies?' + url.searchParams.toString());
        });
    }

    // 모바일 상단 카테고리 탭 렌더링
    function initMobileCategoryTabs() {
        var tabsEl = document.getElementById('catTabs');
        var subEl = document.getElementById('catSubSwiper');
        if (!tabsEl || !categories.length) return;

        var activeGroup = null;
        categories.forEach(function (lg) {
            if (currentCat === lg.slug) activeGroup = lg.slug;
            (lg.sub || []).forEach(function (sub) {
                if (currentCat === sub.slug) activeGroup = lg.slug;
            });
        });

        var tabsHtml = '';
        tabsHtml += '<a href="' + buildCategoryHref('all') + '" class="' + (((currentCat === 'all' || currentCat === '') && !activeGroup) ? 'on' : '') + '">전체</a>';
        categories.forEach(function (lg) {
            var isOn = activeGroup === lg.slug;
            tabsHtml += '<a href="' + buildCategoryHref(lg.slug) + '" class="' + (isOn ? 'on' : '') + '" data-group="' + escapeHtml(lg.slug) + '">' + escapeHtml(lg.label) + '</a>';
        });
        tabsEl.innerHTML = tabsHtml;

        function renderSub(groupSlug) {
            if (!subEl) return;
            var group = null;
            categories.forEach(function (lg) {
                if (lg.slug === groupSlug) group = lg;
            });
            if (!group) {
                subEl.innerHTML = '';
                return;
            }

            var html = '';
            html += '<a href="' + buildCategoryHref(group.slug) + '" class="' + (currentCat === group.slug ? 'on' : '') + '">전체</a>';
            (group.sub || []).forEach(function (sub) {
                html += '<a href="' + buildCategoryHref(sub.slug) + '" class="' + (currentCat === sub.slug ? 'on' : '') + '">' + escapeHtml(sub.label) + '</a>';
            });
            subEl.innerHTML = html;
            centerActiveCategoryItem(subEl, 'auto');
        }

        if (activeGroup) renderSub(activeGroup);

        tabsEl.addEventListener('click', function (e) {
            var a = e.target.closest('a[data-group]');
            if (!a) return;
            if (a.classList.contains('on')) return;

            e.preventDefault();
            tabsEl.querySelectorAll('a').forEach(function (el) {
                el.classList.remove('on');
            });
            a.classList.add('on');
            renderSub(a.getAttribute('data-group'));
            centerItemInScroller(tabsEl, a, 'smooth');
        });

        var activeTab = tabsEl.querySelector('a.on');
        if (activeTab) {
            centerActiveCategoryItem(tabsEl, 'auto');
        }
    }

    function initMobileCategoryDropdown() {
        var wrap = document.getElementById('catDropdown');
        var btn = document.getElementById('catDropdownBtn');
        var panel = document.getElementById('catDropdownPanel');
        if (!wrap || !btn || !panel) return;

        function closePanel() {
            wrap.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false');
        }

        function openPanel() {
            wrap.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true');
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (wrap.classList.contains('is-open')) closePanel();
            else openPanel();
        });

        panel.addEventListener('click', function (e) {
            if (e.target.closest('a[href]')) closePanel();
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) closePanel();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePanel();
        });

        var active = panel.querySelector('a.on');
        if (active) {
            setTimeout(function () {
                active.scrollIntoView({ block: 'nearest', inline: 'nearest' });
            }, 100);
        }
    }

    // 좌측 카테고리 아코디언 열고 닫기
    function initSidebarAccordion() {
        var groups = document.querySelectorAll('.list-sidebar_group');
        if (!groups.length) return;

        function openOnlyThis(targetGroup) {
            groups.forEach(function (g) {
                var isThis = g === targetGroup;
                g.classList.toggle('is-open', isThis);
                var header = g.querySelector('.list-sidebar_group_header');
                if (header) header.setAttribute('aria-expanded', isThis ? 'true' : 'false');
            });
        }

        groups.forEach(function (group) {
            var header = group.querySelector('.list-sidebar_group_header');
            if (!header) return;

            header.addEventListener('click', function (e) {
                var toggleEl = e.target.closest('.list-sidebar_group_toggle');
                var linkEl = header.querySelector('.list-sidebar_group_title_link');

                if (toggleEl) {
                    if (group.classList.contains('is-open')) {
                        group.classList.remove('is-open');
                        header.setAttribute('aria-expanded', 'false');
                    } else {
                        openOnlyThis(group);
                    }
                    return;
                }

                if (linkEl && linkEl.href) {
                    window.location.href = linkEl.href;
                }
            });

            header.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    var linkEl = header.querySelector('.list-sidebar_group_title_link');
                    if (linkEl && linkEl.href) {
                        e.preventDefault();
                        window.location.href = linkEl.href;
                    }
                } else if (e.key === ' ') {
                    e.preventDefault();
                    var toggleEl = header.querySelector('.list-sidebar_group_toggle');
                    if (toggleEl) toggleEl.click();
                }
            });
        });
    }

    // 커스텀 정렬 드롭다운 동작
    function initSortDropdown(sortSelect) {
        var btn = document.getElementById('sortDropdownBtn');
        var menu = document.getElementById('sortDropdownMenu');
        var label = document.getElementById('sortDropdownLabel');
        if (!sortSelect || !btn || !menu || !label) return;

        function openMenu() {
            btn.classList.add('open');
            menu.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            btn.classList.remove('open');
            menu.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }

        function syncSelected(value) {
            menu.querySelectorAll('.sort-dropdown-item').forEach(function (item) {
                item.classList.toggle('selected', item.getAttribute('data-val') === value);
            });
            label.textContent = sortLabelMap[value] || sortLabelMap.rank;
        }

        syncSelected(sortSelect.value || 'rank');

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (menu.classList.contains('open')) closeMenu();
            else openMenu();
        });

        menu.addEventListener('click', function (e) {
            var item = e.target.closest('.sort-dropdown-item');
            if (!item) return;
            var nextValue = item.getAttribute('data-val') || 'rank';
            sortSelect.value = nextValue;
            syncSelected(nextValue);
            closeMenu();
            sortSelect.dispatchEvent(new Event('change', { bubbles: true }));
        });

        document.addEventListener('click', closeMenu);

        sortSelect.addEventListener('change', function () {
            syncSelected(sortSelect.value || 'rank');
        });
    }

    // 정렬, 더보기 등 목록 동작
    function initTypeSwitchAndList() {
        var moreBtn = document.getElementById('listMoreBtn');
        var sortSelect = document.getElementById('sortSelect');
        var grid = document.querySelector('.list-grid');
        if (!grid) return;
        var activeFetchController = null;

        initSortDropdown(sortSelect);
        if (moreBtn && moreBtn.textContent) moreBtnDefaultText = moreBtn.textContent.trim() || moreBtnDefaultText;

        function buildFetchOptions(controller) {
            var options = { credentials: 'same-origin' };
            if (controller) options.signal = controller.signal;
            return options;
        }

        function abortActiveRequest() {
            if (!activeFetchController) return;
            activeFetchController.abort();
            activeFetchController = null;
        }

        // 현재 목록 상태 렌더링
        function renderList() {
            var cards = document.querySelectorAll('.list-grid .mCompanyCard');
            cards.forEach(function (card) {
                card.style.display = '';
            });

            var visibleCount = cards.length;

            var countEl = document.querySelector('.list-count strong');
            if (countEl) countEl.textContent = totalCount || visibleCount;

            if (moreBtn) {
                var shouldShowMoreBtn = (totalCount || 0) > visibleCount;
                moreBtn.style.display = shouldShowMoreBtn ? '' : 'none';
                moreBtn.textContent = moreBtnDefaultText;
                moreBtn.disabled = false;
            }
        }

        // 카드 data-* 점수값 읽기
        function getScore(card, key) {
            var raw = card.getAttribute('data-' + key);
            if (raw === null || raw === '') return null;
            var value = parseFloat(raw);
            return isNaN(value) ? null : value;
        }

        // 선택한 기준으로 카드 정렬
        function sortCards(key) {
            var cards = Array.from(grid.querySelectorAll('.mCompanyCard'));
            var compareFn = function (a, b) {
                var aValue = getScore(a, key === 'rank' ? 'total' : key);
                var bValue = getScore(b, key === 'rank' ? 'total' : key);
                var aRank = parseInt(a.getAttribute('data-rank'), 10) || 0;
                var bRank = parseInt(b.getAttribute('data-rank'), 10) || 0;

                if (aValue === null && bValue === null) return aRank - bRank;
                if (aValue === null) return 1;
                if (bValue === null) return -1;
                if (bValue === aValue) return aRank - bRank;
                return bValue - aValue;
            };

            cards.sort(compareFn);

            grid.innerHTML = '';
            cards.forEach(function (card) {
                grid.appendChild(card);
            });
        }

        // 조회 속도 개선용 JSON 형태 전달
        function buildApiUrl(urlLike) {
            var apiUrl = new URL(urlLike.toString(), window.location.origin);
            apiUrl.pathname = '/ko/matching_list_api.php';
            return apiUrl;
        }

        function parseCardsFromGridHtml(gridHtml) {
            var holder = document.createElement('div');
            holder.innerHTML = gridHtml || '';
            return Array.from(holder.querySelectorAll('.mCompanyCard'));
        }

        // 서버에서 새 목록으로 교체 로드
        function fetchAndReplaceGrid(url, preserveScroll) {
            var prevScrollY = window.scrollY || window.pageYOffset || 0;
            abortActiveRequest();
            activeFetchController = new AbortController();
            isLoadingMore = true;
            grid.classList.add('is-loading');
            if (moreBtn) {
                moreBtn.disabled = true;
                moreBtn.textContent = '불러오는 중...';
            }

            var apiUrl = buildApiUrl(url);
            return fetch(apiUrl.toString(), buildFetchOptions(activeFetchController))
                .then(function (res) { return res.json(); })
                .then(function (payload) {
                    if (!payload || payload.ok !== true || !payload.gridHtml) throw new Error('grid payload not found');
                    if (payload.totalCount != null) totalCount = parseInt(payload.totalCount, 10) || 0;

                    grid.innerHTML = payload.gridHtml;
                    if (!isServerPaged) sortCards(sortSelect ? (sortSelect.value || 'rank') : 'rank');
                    renderList();
                    syncNavLinkParams();
                    if (preserveScroll) window.scrollTo(0, prevScrollY);
                    window.history.replaceState({}, '', url.toString());
                })
                .catch(function (err) {
                    if (err && err.name === 'AbortError') return;
                    window.location.href = url.toString();
                })
                .finally(function () {
                    activeFetchController = null;
                    isLoadingMore = false;
                    grid.classList.remove('is-loading');
                    if (moreBtn) {
                        moreBtn.disabled = false;
                        moreBtn.textContent = moreBtnDefaultText;
                    }
                });
        }

        // 서버에서 다음 목록을 이어붙여 로드
        function fetchAndAppendGrid(url, preserveScroll) {
            var prevScrollY = window.scrollY || window.pageYOffset || 0;
            abortActiveRequest();
            activeFetchController = new AbortController();
            isLoadingMore = true;
            if (moreBtn) {
                moreBtn.disabled = true;
                moreBtn.textContent = '불러오는 중...';
            }

            var apiUrl = buildApiUrl(url);
            return fetch(apiUrl.toString(), buildFetchOptions(activeFetchController))
                .then(function (res) { return res.json(); })
                .then(function (payload) {
                    if (!payload || payload.ok !== true || payload.gridHtml == null) throw new Error('grid payload not found');
                    if (payload.totalCount != null) totalCount = parseInt(payload.totalCount, 10) || 0;

                    var nodes = parseCardsFromGridHtml(payload.gridHtml);
                    if (!nodes.length) {
                        totalCount = document.querySelectorAll('.list-grid .mCompanyCard').length;
                        renderList();
                        return;
                    }
                    nodes.forEach(function (card) {
                        grid.appendChild(card);
                    });

                    if (!isServerPaged) sortCards(sortSelect ? (sortSelect.value || 'rank') : 'rank');
                    renderList();
                    if (preserveScroll) window.scrollTo(0, prevScrollY);
                    window.history.replaceState({}, '', url.toString());
                })
                .catch(function (err) {
                    if (err && err.name === 'AbortError') return;
                    window.location.href = url.toString();
                })
                .finally(function () {
                    activeFetchController = null;
                    isLoadingMore = false;
                    if (moreBtn) {
                        moreBtn.disabled = false;
                        moreBtn.textContent = moreBtnDefaultText;
                    }
                });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                if (isServerPaged) {
                    if (isLoadingMore) return;
                    var selectedSort = sortSelect.value || 'rank';
                    var replaceUrl = new URL(window.location.href);
                    replaceUrl.searchParams.set('sort', selectedSort);
                    replaceUrl.searchParams.set('offset', 0);
                    replaceUrl.searchParams.set('page_size', pageSize);
                    replaceUrl.searchParams.delete('type');
                    fetchAndReplaceGrid(replaceUrl, true);
                    return;
                }

                sortCards(sortSelect.value || 'rank');
                renderList();
                syncNavLinkParams();
            });

            if (!isServerPaged) sortCards(sortSelect.value || 'rank');
            renderList();
            syncNavLinkParams();
        }

        if (moreBtn) {
            moreBtn.addEventListener('click', function () {
                if (!canLoadMore) {
                    openLoginRequiredModal('더보기는<br>로그인 후 이용하실 수 있습니다.');
                    return;
                }

                if (isServerPaged) {
                    if (isLoadingMore) return;
                    var currentShown = document.querySelectorAll('.list-grid .mCompanyCard').length;
                    if (currentShown >= (totalCount || 0)) {
                        renderList();
                        return;
                    }

                    var appendUrl = new URL(window.location.href);
                    appendUrl.searchParams.set('offset', currentShown);
                    appendUrl.searchParams.set('sort', sortSelect ? (sortSelect.value || 'rank') : 'rank');
                    appendUrl.searchParams.set('page_size', pageSize);
                    appendUrl.searchParams.delete('type');
                    fetchAndAppendGrid(appendUrl, true);
                    return;
                }

                renderList();
            });
        }
    }

    // 매칭 요청 팝업(템플릿/메시지/검증) 동작
    function initMatchingPopupLogic() {
        if (!$) return;

        var matchingMessageTemplates = {
            distributor: [
                { title: '신규 제품 소싱', body: '판매 가능한 신규 제품을 공급할 제조사를 찾고 있습니다. 테스트 거래 후 지속적인 거래를 희망합니다.' },
                { title: '제조 파트너 발굴', body: '안정적인 제품 공급이 가능한 제조사를 찾고 있습니다. 협의를 통해 장기 거래를 검토하고자 합니다.' },
                { title: 'OEM/ODM 생산 협력', body: '자체 브랜드 제품 생산을 위해 OEM 또는 ODM 협력 가능한 제조사를 찾고 있습니다.' },
                { title: '신규 상품 발굴', body: '판매 경쟁력 있는 신규 제품을 발굴하기 위해 협력 가능한 제조 파트너를 찾고 있습니다.' },
                { title: '안정적인 공급처 확보', body: '지속적으로 제품 공급이 가능한 제조사를 찾고 있습니다. 상호 협의를 통해 거래 조건을 논의하고자 합니다.' }
            ],
            manufacturer: [
                { title: '유통 파트너 발굴', body: '자사 제품의 판매 확대를 위해 유통 및 판매 협력 가능한 기업을 찾고 있습니다. 안정적이고 장기적인 거래 파트너를 희망합니다.' },
                { title: '신규 판매 채널 확보', body: '제품 판매처를 확대하기 위해 협업 가능한 유통사를 찾고 있습니다. 상호 협의를 통해 거래 조건을 논의하고자 합니다.' },
                { title: '수출/해외 유통 협력', body: '자사 제품의 해외 시장 진출을 위해 유통 및 판매 협력 가능한 파트너를 찾고 있습니다.' },
                { title: '신규 브랜드 유통 협력', body: '신제품 출시와 함께 판매 확대가 가능한 유통사를 찾고 있습니다. 초기 협의 후 장기적인 거래를 희망합니다.' },
                { title: '안정적인 판매 파트너 확보', body: '지속적인 제품 공급이 가능한 유통 및 판매 파트너를 찾고 있습니다. 안정적인 거래 관계 구축을 희망합니다.' }
            ]
        };

        function getCurrentBizRole() {
            var selected = $('input[name="biz_role_ui"]:checked').val();
            return selected === 'manufacturer' ? 'manufacturer' : 'distributor';
        }

        // 업종 역할에 맞는 템플릿 카드 생성
        function renderMatchingTemplateCards() {
            var role = getCurrentBizRole();
            var rows = matchingMessageTemplates[role] || [];
            var html = '';
            for (var i = 0; i < rows.length; i++) {
                var n = i + 1;
                html += ''
                    + '<div class="req-tpl-card" data-idx="' + i + '">'
                    + '  <div class="req-tpl-head">'
                    + '    <input type="checkbox" class="reqTplChk" value="' + i + '" id="reqTplChk_' + i + '">'
                    + '    <h6>' + n + '. ' + rows[i].title + '</h6>'
                    + '  </div>'
                    + '  <p>' + rows[i].body + '</p>'
                    + '</div>';
            }
            $('#reqTplGrid').html(html);
        }

        // 선택 템플릿 + 추가 입력을 합쳐 최종 메시지 구성
        function updateMatchingMessageContent() {
            var role = getCurrentBizRole();
            var rows = matchingMessageTemplates[role] || [];
            var selectedTexts = [];

            $('.reqTplChk:checked').each(function () {
                var idx = parseInt($(this).val(), 10);
                if (!isNaN(idx) && rows[idx]) selectedTexts.push(rows[idx].body);
            });

            var extra = $.trim($('#match_content_extra').val() || '');
            var merged = selectedTexts.join('\n\n');
            if (extra) merged = merged ? (merged + '\n\n' + extra) : extra;
            $('#match_content').val(merged);
        }

        $('body').on('click', '.requestTypeBtn', function () {
            var value = $(this).data('value') === 'TM' ? 'TM' : 'BASIC';
            $('#request_type').val(value);

            $('.requestTypeBtn').removeClass('is-active').css({
                borderColor: '#d9dce3',
                background: '#fff',
                color: '#333'
            });

            $(this).addClass('is-active').css({
                borderColor: '#3856ff',
                background: '#3856ff',
                color: '#fff'
            });

            if (value === 'TM') {
                $('#requestTypeDesc').text('TM 요청은 1건당 30크레딧이 차감되며, 24시간 동안 상대 기업 응답이 없으면 운영팀이 오프라인 연락을 지원합니다.');
            } else {
                $('#requestTypeDesc').text('기본 요청은 상대 기업에 매칭 요청만 전달되며 1건당 10크레딧이 차감됩니다.');
            }
        });

        $('body').on('change', 'input[name="biz_role_ui"]', function () {
            renderMatchingTemplateCards();
            updateMatchingMessageContent();
        });

        $('body').on('click', '.req-tpl-card', function (e) {
            if ($(e.target).is('input')) return;
            var $chk = $(this).find('.reqTplChk');
            $chk.prop('checked', !$chk.prop('checked')).trigger('change');
        });

        $('body').on('change', '.reqTplChk', function () {
            $(this).closest('.req-tpl-card').toggleClass('is-on', $(this).is(':checked'));
            updateMatchingMessageContent();
        });

        $('body').on('input', '#match_content_extra', function () {
            updateMatchingMessageContent();
        });

        if (window.CompanyDetailActions && typeof window.CompanyDetailActions.init === 'function') {
            window.CompanyDetailActions.init({
                getSetUrl: '/get_set.php',
                detailTrigger: '.js-detail-popup',
                popupSelector: '.popup_submit',
                requestTypeSelector: '#request_type',
                receiverNameSelector: '#recv',
                planUrl: '/plans',
                loginUrl: function () {
                    return '/bbs/login.php?url=' + encodeURIComponent(window.location.pathname + window.location.search);
                },
                beforeSubmit: function () {
                    updateMatchingMessageContent();
                    if (!$.trim($('#match_content').val())) {
                        if (typeof window.jAlert === 'function') {
                            window.jAlert('매칭 메시지 선택(선택) 또는 추가 요청사항(선택) 중 1개 이상 입력해 주세요.', '알림');
                        } else {
                            window.alert('매칭 메시지 선택(선택) 또는 추가 요청사항(선택) 중 1개 이상 입력해 주세요.');
                        }
                        return false;
                    }
                    return true;
                },
                onPopupReady: function (kedcd, parts) {
                    $('#match_content_extra').val('');
                    renderMatchingTemplateCards();
                    updateMatchingMessageContent();
                    if (parts && parts[2]) $('#recv').text(parts[2]);
                }
            });
        }
    }

    // 페이지 초기화
    initMobileCategoryTabs();
    initMobileCategoryDropdown();
    initSidebarAccordion();
    initTypeSwitchAndList();
    initMatchingPopupLogic();
})(window, document, window.jQuery);
