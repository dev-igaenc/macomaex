(function (window, document, $) {
    'use strict';

    // PHP에서 내려준 페이지 설정값
    var cfg = window.MATCHING_LIST_CONFIG || {};
    var pageSize = parseInt(cfg.freeLimit, 10) || 9;
    var canLoadMore = !!cfg.canLoadMore;
    var isServerPaged = !!cfg.isServerPaged;
    var totalByType = cfg.totalByType || { buyer: 0, seller: 0 };
    var currentType = cfg.currentType === 'seller' ? 'seller' : 'buyer';
    var currentCat = cfg.currentCat || 'all';
    var categories = Array.isArray(cfg.listCategories) ? cfg.listCategories : [];
    var showCount = { buyer: pageSize, seller: pageSize };
    var isLoadingMore = false;

    var sortLabelMap = {
        rank: '랭킹순',
        grow: '성장성순',
        profit: '수익성순',
        stable: '안정성순'
    };

    // 안전한 HTML 출력용 이스케이프
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str == null ? '' : String(str)));
        return div.innerHTML;
    }

    function getLoginReturnUrl() {
        return '/bbs/login.php?url=' + encodeURIComponent(window.location.pathname + window.location.search);
    }

    function openLoginRequiredModal(descriptionHtml) {
        var modalId = 'matchingListLoginRequiredModal';
        var overlay = document.getElementById(modalId);

        function closeModal() {
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
                + '  <div class="login-guard__modal" role="dialog" aria-modal="true" aria-labelledby="matchingListLoginModalTitle">'
                + '    <button type="button" class="login-guard__close" aria-label="닫기">'
                + '      <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
                + '    </button>'
                + '    <div class="login-guard__icon" aria-hidden="true">'
                + '      <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/></svg>'
                + '    </div>'
                + '    <h3 class="login-guard__title" id="matchingListLoginModalTitle">로그인이 필요합니다</h3>'
                + '    <p class="login-guard__desc" id="matchingListLoginModalDesc"></p>'
                + '    <div class="login-guard__actions">'
                + '      <a href="/bbs/register.php" class="login-guard__btn login-guard__btn--ghost">회원가입</a>'
                + '      <a href="' + getLoginReturnUrl() + '" class="login-guard__btn login-guard__btn--primary">로그인</a>'
                + '    </div>'
                + '  </div>'
                + '</div>';
            document.body.appendChild(overlay);

            var dim = overlay.querySelector('.login-guard__overlay');
            if (dim) dim.addEventListener('click', closeModal);

            var closeBtn = overlay.querySelector('.login-guard__close');
            if (closeBtn) closeBtn.addEventListener('click', closeModal);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
            });
        }

        var descEl = overlay.querySelector('#matchingListLoginModalDesc');
        if (descEl) descEl.innerHTML = descriptionHtml || '더보기는<br>로그인 후 이용하실 수 있습니다.';

        var loginBtn = overlay.querySelector('.login-guard__btn--primary');
        if (loginBtn) loginBtn.setAttribute('href', getLoginReturnUrl());

        overlay.classList.add('is-open');
        document.documentElement.classList.add('hide-scroll');
    }

    // 카테고리 이동 시 정렬은 항상 랭킹순으로 고정
    function buildCategoryHref(catSlug) {
        var params = new URLSearchParams(window.location.search);
        params.set('cat', catSlug);
        params.delete('offset');
        if (!params.get('type')) params.set('type', currentType);
        params.set('sort', 'rank');
        return '?' + params.toString();
    }

    // 사이드바/모바일 카테고리 링크의 type/sort 파라미터 동기화
    function syncNavLinkParams() {
        var selectors = [
            '.list-sidebar a[href]',
            '.list-cat-tabs a[href]',
            '.list-cat-sub a[href]'
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

            url.searchParams.set('type', currentType);
            url.searchParams.set('sort', 'rank');
            url.searchParams.delete('offset');

            a.setAttribute('href', url.pathname + '?' + url.searchParams.toString());
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
        });

        var activeTab = tabsEl.querySelector('a.on');
        if (activeTab) {
            setTimeout(function () {
                activeTab.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
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

    // 구매/판매 탭, 정렬, 더보기 등 목록 동작
    function initTypeSwitchAndList() {
        var wrap = document.querySelector('.list-type-switch');
        if (!wrap) return;

        var slider = wrap.querySelector('.list-type-slider');
        var btns = wrap.querySelectorAll('.list-type-btn');
        var moreBtn = document.getElementById('listMoreBtn');
        var sortSelect = document.getElementById('sortSelect');
        var grid = document.querySelector('.list-grid');
        if (!grid) return;

        initSortDropdown(sortSelect);

        // 탭 슬라이더 위치/크기 업데이트
        function updateSlider() {
            var active = wrap.querySelector('.list-type-btn.on');
            if (!slider || !active) return;
            var wrapRect = wrap.getBoundingClientRect();
            var btnRect = active.getBoundingClientRect();
            slider.style.width = btnRect.width + 'px';
            slider.style.left = (btnRect.left - wrapRect.left) + 'px';
        }

        // 현재 타입(buyer/seller)에 맞는 카드만 표시
        function renderType(type) {
            currentType = type;
            var cards = document.querySelectorAll('.list-grid .mCompanyCard');
            var matched = [];

            cards.forEach(function (card) {
                if (card.getAttribute('data-type') === type) matched.push(card);
                card.style.display = 'none';
            });

            var visibleCount = isServerPaged ? matched.length : Math.min(showCount[type], matched.length);
            for (var i = 0; i < visibleCount; i++) matched[i].style.display = '';

            var countEl = document.querySelector('.list-count strong');
            if (countEl) countEl.textContent = totalByType[type] || 0;

            if (moreBtn) {
                var totalCountForType = totalByType[type] || 0;
                var shouldShowMoreBtn = totalCountForType > visibleCount;
                moreBtn.style.display = shouldShowMoreBtn ? '' : 'none';
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
            var buyers = [];
            var sellers = [];

            cards.forEach(function (card) {
                (card.getAttribute('data-type') === 'seller' ? sellers : buyers).push(card);
            });

            var compareFn;
            compareFn = function (a, b) {
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

            buyers.sort(compareFn);
            sellers.sort(compareFn);

            grid.innerHTML = '';
            buyers.concat(sellers).forEach(function (card) {
                grid.appendChild(card);
            });
        }

        // 서버에서 새 목록으로 교체 로드
        function fetchAndReplaceGrid(url, preserveScroll) {
            var prevScrollY = window.scrollY || window.pageYOffset || 0;
            isLoadingMore = true;
            if (moreBtn) moreBtn.disabled = true;

            return fetch(url.toString(), { credentials: 'same-origin' })
                .then(function (res) { return res.text(); })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var nextGrid = doc.querySelector('.list-grid');
                    if (!nextGrid) throw new Error('list-grid not found');

                    grid.innerHTML = nextGrid.innerHTML;
                    if (!isServerPaged) sortCards(sortSelect ? (sortSelect.value || 'rank') : 'rank');
                    renderType(currentType);
                    if (preserveScroll) window.scrollTo(0, prevScrollY);
                    window.history.replaceState({ type: currentType }, '', url.toString());
                })
                .catch(function () {
                    window.location.href = url.toString();
                })
                .finally(function () {
                    isLoadingMore = false;
                    if (moreBtn) moreBtn.disabled = false;
                });
        }

        // 서버에서 다음 목록을 이어붙여 로드
        function fetchAndAppendGrid(url, preserveScroll) {
            var prevScrollY = window.scrollY || window.pageYOffset || 0;
            isLoadingMore = true;
            if (moreBtn) moreBtn.disabled = true;

            return fetch(url.toString(), { credentials: 'same-origin' })
                .then(function (res) { return res.text(); })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var nextGrid = doc.querySelector('.list-grid');
                    if (!nextGrid) throw new Error('list-grid not found');

                    var nodes = Array.from(nextGrid.querySelectorAll('.mCompanyCard'));
                    if (!nodes.length) return;
                    nodes.forEach(function (card) {
                        grid.appendChild(card);
                    });

                    if (!isServerPaged) sortCards(sortSelect ? (sortSelect.value || 'rank') : 'rank');
                    renderType(currentType);
                    if (preserveScroll) window.scrollTo(0, prevScrollY);
                    window.history.replaceState({ type: currentType }, '', url.toString());
                })
                .catch(function () {
                    window.location.href = url.toString();
                })
                .finally(function () {
                    isLoadingMore = false;
                    if (moreBtn) moreBtn.disabled = false;
                });
        }

        // 타입 전환 처리 (server/client 모드 분기)
        function setType(type) {
            if (isServerPaged && type !== currentType) {
                var moveUrl = new URL(window.location.href);
                moveUrl.searchParams.set('type', type);
                moveUrl.searchParams.delete('offset');
                window.location.href = moveUrl.toString();
                return;
            }

            btns.forEach(function (btn) {
                var isActive = btn.getAttribute('data-type') === type;
                btn.classList.toggle('on', isActive);
                btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            updateSlider();
            renderType(type);

            var url = new URL(window.location.href);
            url.searchParams.set('type', type);
            window.history.replaceState({ type: type }, '', url.toString());

            syncNavLinkParams();
        }

        btns.forEach(function (btn) {
            btn.setAttribute('aria-pressed', btn.classList.contains('on') ? 'true' : 'false');
            btn.addEventListener('click', function () {
                setType(btn.getAttribute('data-type'));
            });
        });

        var urlType = new URLSearchParams(window.location.search).get('type');
        setType(urlType === 'seller' ? 'seller' : 'buyer');

        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                if (isServerPaged) {
                    if (isLoadingMore) return;
                    var selectedSort = sortSelect.value || 'rank';
                    var replaceUrl = new URL(window.location.href);
                    replaceUrl.searchParams.set('sort', selectedSort);
                    replaceUrl.searchParams.set('offset', 0);
                    fetchAndReplaceGrid(replaceUrl, true);
                    return;
                }

                showCount[currentType] = pageSize;
                sortCards(sortSelect.value || 'rank');
                renderType(currentType);
                syncNavLinkParams();
            });

            if (!isServerPaged) sortCards(sortSelect.value || 'rank');
            renderType(currentType);
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
                    var currentShown = document.querySelectorAll('.list-grid .mCompanyCard[data-type="' + currentType + '"]').length;
                    if (currentShown >= (totalByType[currentType] || 0)) {
                        renderType(currentType);
                        return;
                    }

                    var appendUrl = new URL(window.location.href);
                    appendUrl.searchParams.set('offset', currentShown);
                    appendUrl.searchParams.set('sort', sortSelect ? (sortSelect.value || 'rank') : 'rank');
                    fetchAndAppendGrid(appendUrl, true);
                    return;
                }

                showCount[currentType] += pageSize;
                renderType(currentType);
            });
        }

        window.addEventListener('resize', updateSlider);
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
                planUrl: '/ko/plan.php',
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
    initSidebarAccordion();
    initTypeSwitchAndList();
    initMatchingPopupLogic();
})(window, document, window.jQuery);
