// 카테고리 아이콘: <use> 참조를 실제 요소로 펼쳐서(path/circle/rect) 개별 애니메이션 가능하게 처리
(function() {
    function inlineCategoryIcons() {
        var svgs = document.querySelectorAll('.mCategories_item .cat_icon svg');
        svgs.forEach(function(svg) {
            if (svg.getAttribute('data-inline-ready') === '1') return;
            var useEl = svg.querySelector('use');
            if (!useEl) return;

            var ref = useEl.getAttribute('href') || useEl.getAttribute('xlink:href');
            if (!ref || ref.charAt(0) !== '#') return;

            var symbol = document.querySelector(ref);
            if (!symbol) return;

            var viewBox = symbol.getAttribute('viewBox');
            if (viewBox) {
                svg.setAttribute('viewBox', viewBox);
            }

            while (svg.firstChild) {
                svg.removeChild(svg.firstChild);
            }

            Array.prototype.forEach.call(symbol.childNodes, function(node) {
                if (node.nodeType === 1) {
                    svg.appendChild(node.cloneNode(true));
                }
            });

            svg.setAttribute('data-inline-ready', '1');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inlineCategoryIcons);
    } else {
        inlineCategoryIcons();
    }
})();

// 히어로 인라인 영상 재생 상태 동기화
(function(){
    var wrap = document.getElementById('heroInterview');
    if (!wrap) return;
    var slides = wrap.querySelectorAll('.mHeroSlide');
    if (!slides.length) return;
    var heroVideo = document.getElementById('mHeroVideo');
    if (!heroVideo) return;

    function syncHeroVideo() {
        var activeSlide = heroVideo.closest('.mHeroSlide');
        var visible = activeSlide && activeSlide.classList.contains('active');
        if (visible) {
            var p = heroVideo.play();
            if (p && p.catch) p.catch(function(){});
        } else {
            try {
                heroVideo.pause();
                heroVideo.currentTime = 0;
            } catch (e) {}
        }
    }

    syncHeroVideo();
})();

// 사업자등록번호 입력창 타이핑 효과
(function () {
    var input = document.getElementById('comnum_index');
    if (!input) return;

    var text = '사업자등록번호를 입력해보세요';
    var index = 0;
    var isTyping = true;
    var timerId = null;

    function queueNextTick(delay) {
        timerId = window.setTimeout(tick, delay);
    }

    function tick() {
        if (input === document.activeElement) return;

        if (isTyping) {
            index++;
            if (index > text.length) {
                isTyping = false;
                queueNextTick(2000);
                return;
            }
        } else {
            index--;
            if (index < 0) {
                isTyping = true;
                index = 0;
                queueNextTick(500);
                return;
            }
        }

        input.placeholder = text.substring(0, index) + '|';
        queueNextTick(isTyping ? 100 : 50);
    }

    queueNextTick(500);

    input.addEventListener('focus', function () {
        if (timerId) {
            window.clearTimeout(timerId);
            timerId = null;
        }
        input.placeholder = '';
    });

    input.addEventListener('blur', function () {
        if (input.value) return;
        index = 0;
        isTyping = true;
        if (timerId) {
            window.clearTimeout(timerId);
        }
        tick();
    });
})();

// 히어로 영상 앰비언트 배경
(function(){
    var video = document.getElementById('mHeroVideo');
    var canvas = document.getElementById('mHeroVideoAmbient');
    if (!video || !canvas || !canvas.getContext) return;

    var ctx = canvas.getContext('2d');
    if (!ctx) return;

    canvas.width = 32;
    canvas.height = 18;

    var STRIP_RATIO = 0.10;
    var FADE_ALPHA = 0.45;
    var isFirstFrame = true;

    function drawAmbientFrame() {
        if (window.innerWidth < 1025) return;
        if (video.readyState < 2) return;

        var videoWidth = video.videoWidth;
        var videoHeight = video.videoHeight;
        if (!videoWidth || !videoHeight) return;

        var stripHeight = Math.max(1, Math.round(videoHeight * STRIP_RATIO));
        var halfHeight = canvas.height / 2;

        ctx.globalAlpha = isFirstFrame ? 1 : FADE_ALPHA;
        ctx.drawImage(video, 0, 0, videoWidth, stripHeight, 0, 0, canvas.width, halfHeight);
        ctx.drawImage(video, 0, videoHeight - stripHeight, videoWidth, stripHeight, 0, halfHeight, canvas.width, halfHeight);
        ctx.globalAlpha = 1;
        isFirstFrame = false;
    }

    video.addEventListener('loadeddata', function () {
        isFirstFrame = true;
        drawAmbientFrame();
    });

    if (window.requestAnimationFrame) {
        (function loop() {
            drawAmbientFrame();
            requestAnimationFrame(loop);
        })();
    } else {
        setInterval(drawAmbientFrame, 40);
    }
})();

// 사용방법 영상 모달 재생
(function () {
    var heroToggle = document.getElementById('mHeroVideoToggle');
    var TEMP_VIDEO_SRC = heroToggle ? (heroToggle.getAttribute('data-video-src') || '') : '';
    var modal = document.getElementById('mVideoModal');
    var player = document.getElementById('mVideoPlayer');
    if (!modal || !player) return;

    var playBtns = document.querySelectorAll('.mVisual_interview_play, .mVisual_video_play, .mReviews_play');
    var heroVideo = document.getElementById('mHeroVideo');
    var heroSlide = document.querySelector('.mHeroSlide--video');

    function openVideo(src) {
        var resumeAt = 0;
        if (heroVideo) {
            try { heroVideo.pause(); } catch (e) {}
            if (heroVideo.getAttribute('src') === src) {
                resumeAt = heroVideo.currentTime || 0;
            }
        }
        if (player.getAttribute('src') !== src) {
            player.setAttribute('src', src);
        }
        if (resumeAt > 0.3) {
            var seek = function () {
                try { player.currentTime = resumeAt; } catch (e) {}
            };
            if (player.readyState >= 1) seek();
            else player.addEventListener('loadedmetadata', seek, { once: true });
        } else {
            var resetToStart = function () {
                try { player.currentTime = 0; } catch (e) {}
            };
            if (player.readyState >= 1) resetToStart();
            else player.addEventListener('loadedmetadata', resetToStart, { once: true });
        }
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.documentElement.style.overflow = 'hidden';
        var p = player.play();
        if (p && p.catch) p.catch(function () {});
    }

    function closeVideo() {
        try { player.pause(); } catch (e) {}
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.documentElement.style.overflow = '';
        if (heroVideo && heroSlide && heroSlide.classList.contains('active')) {
            var hp = heroVideo.play();
            if (hp && hp.catch) hp.catch(function () {});
        }
    }

    playBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openVideo(btn.getAttribute('data-video-src') || TEMP_VIDEO_SRC);
        });
    });

    if (heroSlide) {
        heroSlide.addEventListener('click', function (e) {
            if (window.innerWidth > 720) return;
            if (e.target.closest('.mVisual_video_play')) return;
            e.preventDefault();
            openVideo((playBtns[0] && playBtns[0].getAttribute('data-video-src')) || TEMP_VIDEO_SRC);
        });
    }

    modal.querySelectorAll('[data-video-close]').forEach(function (el) {
        el.addEventListener('click', closeVideo);
    });

    document.addEventListener('keydown', function (e) {
        if ((e.key === 'Escape' || e.keyCode === 27) && modal.classList.contains('open')) {
            closeVideo();
        }
    });
})();

// 카테고리 그리드 전체보기 토글 (데스크탑/태블릿 — 모바일은 캐러셀이 전체를 담당)
(function() {
    var grid = document.getElementById('mCategoriesGrid');
    var btn = document.getElementById('catToggleBtn');
    if (!grid || !btn) return;

    var label = btn.querySelector('.cat_label');

    function sync() {
        var open = grid.classList.contains('expanded');
        if (label) label.textContent = open ? '접기' : '전체보기';
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    btn.addEventListener('click', function() {
        grid.classList.toggle('expanded');
        sync();
        btn.classList.remove('is-pulse');
        void btn.offsetWidth;
        btn.classList.add('is-pulse');
    });

    btn.addEventListener('animationend', function(e) {
        if (e.animationName === 'catMoreSpin') btn.classList.remove('is-pulse');
    });

    sync();
})();

// 모바일 전용: 카테고리 2줄 스와이프 캐러셀 (대분류 18개)
(function() {
    var wrap = document.querySelector('.mCategories_carousel_wrap');
    var el = document.querySelector('.mCategories_swiper');
    var catSwiper = null;
    function initCategoriesCarousel() {
        if (!el || !wrap) return;
        if (window.innerWidth > 640) {
            if (catSwiper) {
                catSwiper.destroy(true, true);
                catSwiper = null;
            }
            return;
        }
        if (catSwiper) return;
        catSwiper = new Swiper(el, {
            slidesPerView: 4.15,
            spaceBetween: 14,
            grid: { rows: 2, fill: 'row' },
            freeMode: true,
            resistance: true,
            resistanceRatio: 0.85,
            grabCursor: true,
            pagination: {
                el: '.mCategories_carousel_pagination',
                type: 'progressbar',
            },
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCategoriesCarousel);
    } else {
        initCategoriesCarousel();
    }
    window.addEventListener('resize', function() {
        var w = window.innerWidth;
        if (w <= 640 && !catSwiper) initCategoriesCarousel();
        else if (w > 640 && catSwiper) {
            catSwiper.destroy(true, true);
            catSwiper = null;
        }
    });
})();

    // 매칭 요청 템플릿(유통사/제조사)
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

    // 현재 선택된 업종 역할 확인
    function getCurrentBizRole() {
        var selected = $('input[name="biz_role_ui"]:checked').val();
        return selected === 'manufacturer' ? 'manufacturer' : 'distributor';
    }

    // 선택 역할에 맞는 템플릿 카드 렌더링
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

    // 선택 템플릿 + 추가 입력을 합쳐 최종 메시지 생성
    function updateMatchingMessageContent() {
        var role = getCurrentBizRole();
        var rows = matchingMessageTemplates[role] || [];
        var selectedTexts = [];
        $('.reqTplChk:checked').each(function () {
            var idx = parseInt($(this).val(), 10);
            if (!isNaN(idx) && rows[idx]) selectedTexts.push(rows[idx].body);
        });
        var extra = $.trim($('#match_content_extra').val() || '');
        var merged = selectedTexts.join("\n\n");
        if (extra) merged = merged ? (merged + "\n\n" + extra) : extra;
        $('#match_content').val(merged);
    }

    // 요청 방식(BASIC/TM) 버튼 처리
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

    // 역할 변경 시 템플릿 새로 구성
    $('body').on('change', 'input[name="biz_role_ui"]', function () {
        renderMatchingTemplateCards();
        updateMatchingMessageContent();
    });

    // 카드 클릭으로 체크 토글
    $('body').on('click', '.req-tpl-card', function (e) {
        if ($(e.target).is('input')) return;
        var $chk = $(this).find('.reqTplChk');
        $chk.prop('checked', !$chk.prop('checked')).trigger('change');
    });

    // 체크박스 변경 시 메시지 동기화
    $('body').on('change', '.reqTplChk', function () {
        $(this).closest('.req-tpl-card').toggleClass('is-on', $(this).is(':checked'));
        updateMatchingMessageContent();
    });

    // 추가 요청사항 입력 반영
    $('body').on('input', '#match_content_extra', function () {
        updateMatchingMessageContent();
    });

    // 상세/요청 팝업 공통 액션 초기화
    if (window.CompanyDetailActions && typeof window.CompanyDetailActions.init === 'function') {
        window.CompanyDetailActions.init({
            getSetUrl: '/get_set.php',
            detailOpenMode: 'page',
            popupSelector: '.popup_submit',
            requestTypeSelector: '#request_type',
            receiverNameSelector: '#recv',
            planUrl: '/plans',
            loginUrl: function () {
                return '/bbs/login.php?url=' + encodeURIComponent(location.pathname + location.search);
            },
            beforeSubmit: function () {
                updateMatchingMessageContent();
                if (!$.trim($('#match_content').val())) {
                    if (typeof jAlert === 'function') jAlert('매칭 메시지 선택(선택) 또는 추가 요청사항(선택) 중 1개 이상 입력해 주세요.', '알림');
                    else alert('매칭 메시지 선택(선택) 또는 추가 요청사항(선택) 중 1개 이상 입력해 주세요.');
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

    // 메인 인터뷰 카드 클릭을 공통 거래요청 모달로 연결
    (function initHomepageReviewRequestModal() {
        var reviewLinks = document.querySelectorAll('.mReviews_card_link[data-req-company]');
        var context = window.MATCH_REQUEST_CONTEXT || {};

        function parseTarget(link) {
            var raw = link.getAttribute('data-req-target') || '';
            if (!raw) return null;
            try {
                return JSON.parse(raw);
            } catch (e) {
                return null;
            }
        }

        function buildLoginUrl(target) {
            var loginReturnUrl = new URL(window.location.href);
            loginReturnUrl.searchParams.set('trade_request_login', '1');
            if (target && target.kedcd) {
                loginReturnUrl.searchParams.set('trade_request_target', target.kedcd);
            }
            loginReturnUrl.hash = 'mReviews';
            return '/bbs/login.php?url=' + encodeURIComponent(loginReturnUrl.pathname + loginReturnUrl.search + loginReturnUrl.hash);
        }

        function buildReturnUrl(target) {
            var loginReturnUrl = new URL(window.location.href);
            loginReturnUrl.searchParams.set('trade_request_login', '1');
            if (target && target.kedcd) {
                loginReturnUrl.searchParams.set('trade_request_target', target.kedcd);
            }
            loginReturnUrl.hash = 'mReviews';
            return loginReturnUrl.pathname + loginReturnUrl.search + loginReturnUrl.hash;
        }

        // 홈 카드 클릭 로그도 상세 페이지와 같은 엔드포인트에 적재
        function recordTradeRequestClickHit(target) {
            if (!target || !target.kedcd || !window.fetch) return;

            var payload = new URLSearchParams();
            payload.set('action', 'trade_request_click_hit');
            payload.set('target_kedcd', target.kedcd);
            payload.set('target_company_name', target.company || '');
            payload.set('target_bzno', target.bzno || '');

            fetch((context && context.endpoint) || '/ko/company_detail.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString(),
                keepalive: true
            }).catch(function () {});
        }

        if (!reviewLinks.length) return;

        reviewLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                var target = parseTarget(link) || {
                    kedcd: '',
                    company: link.getAttribute('data-req-company') || ''
                };
                var loginGuard = window.MacomaLoginGuard;
                var returnUrl = buildReturnUrl(target);
                var loginUrl = buildLoginUrl(target);
                var isLoggedIn = !!(window.MATCH_REQUEST_CONTEXT && window.MATCH_REQUEST_CONTEXT.isLoggedIn);

                event.preventDefault();
                event.stopPropagation();

                if (!isLoggedIn) {
                    if (!loginGuard || typeof loginGuard.show !== 'function') {
                        window.location.href = loginUrl;
                        return;
                    }

                    loginGuard.show({
                        titleId: 'homepageReviewRequestLoginTitle',
                        descriptionText: '거래 요청은 로그인 후 이용하실 수 있습니다.',
                        returnUrl: returnUrl,
                        loginUrl: loginUrl,
                        signupUrl: typeof loginGuard.buildSignupUrl === 'function'
                            ? loginGuard.buildSignupUrl(returnUrl)
                            : '/signup?url=' + encodeURIComponent(returnUrl)
                    });
                    return;
                }

                if (window.setMatchRequesterCompany) {
                    window.setMatchRequesterCompany(context.requesterCompany || null);
                }
                if (window.setMatchRequestTarget) {
                    window.setMatchRequestTarget(target);
                }
                recordTradeRequestClickHit(target);
                if (typeof window.openModal === 'function') {
                    window.openModal('modalRequest');
                }
            });
        });
    })();

    (function initHomepagePartnerCta() {
        var partnerLinks = document.querySelectorAll('.mReviews_card_link[data-partner-cta]');

        if (!partnerLinks.length) return;

        partnerLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                try {
                    sessionStorage.setItem('hkReturnSection', 'mReviews');
                } catch (e) {}

                if (typeof window.gtag === 'function') {
                    window.gtag('event', 'partner_cta_click', {
                        partner: link.getAttribute('data-partner-cta') || '',
                        position: 'review_card'
                    });
                }
            });
        });
    })();

    (function restoreHomepageReturnSection() {
        function restoreSection() {
            var sectionId = '';

            try {
                sectionId = sessionStorage.getItem('hkReturnSection') || '';
            } catch (e) {}

            if (!sectionId) return;

            try {
                sessionStorage.removeItem('hkReturnSection');
            } catch (e) {}

            var section = document.getElementById(sectionId);
            if (!section) return;

            window.setTimeout(function () {
                section.scrollIntoView({ behavior: 'auto', block: 'start' });
            }, 80);
        }

        try {
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'auto';
            }
        } catch (e) {}

        window.addEventListener('pageshow', restoreSection);
    })();

    (function initHomepageRequestLoginGuard() {
        var guardContext = window.__INDEX_LOGIN_GUARD_CONTEXT || {};
        var requestLink = document.querySelector('.mVisual_quick_item--request');
        var returnUrl = guardContext.returnUrl || '/account/trades#received';

        if (!requestLink || guardContext.isLoggedIn) return;

        requestLink.addEventListener('click', function (event) {
            var loginGuard = window.MacomaLoginGuard;
            var loginUrl = guardContext.loginUrl || '/bbs/login.php?url=' + encodeURIComponent(returnUrl);

            event.preventDefault();
            event.stopPropagation();

            if (!loginGuard || typeof loginGuard.show !== 'function') {
                window.location.href = loginUrl;
                return;
            }

            loginGuard.show({
                titleId: 'homepageReceivedRequestLoginTitle',
                descriptionText: '받은 요청 확인은 로그인 후 이용하실 수 있습니다.',
                returnUrl: returnUrl,
                loginUrl: loginUrl,
                signupUrl: typeof loginGuard.buildSignupUrl === 'function'
                    ? loginGuard.buildSignupUrl(returnUrl)
                    : '/signup?url=' + encodeURIComponent(returnUrl)
            });
        });
    })();
