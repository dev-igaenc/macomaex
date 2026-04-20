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
            if (viewBox && !svg.getAttribute('viewBox')) {
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

// 프리뷰 배너 2초 자동 전환 + 높이 동기화
(function(){
    var wrap = document.getElementById('previewBanner');
    if (!wrap) return;
    var slides = wrap.querySelectorAll('.mVisual_preview_slide');
    var dots = wrap.querySelectorAll('.mVisual_preview_dot');
    if (!slides.length || !dots.length) return;
    var current = 0;

    function syncHeight() {
        var base = slides[0];
        if (!base) return;
        var baseH = base.offsetHeight;
        for (var i = 1; i < slides.length; i++) {
            slides[i].style.height = baseH + 'px';
        }
    }

    function show(idx) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = idx % slides.length;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
    }

    syncHeight();
    window.addEventListener('resize', syncHeight);
    dots.forEach(function(dot, i) {
        dot.addEventListener('click', function() { show(i); });
    });
    setInterval(function() { show(current + 1); }, 2000);
})();

// 채팅 모달
(function () {
    var overlay = document.getElementById('chatDemoOverlay');
    if (!overlay) return;

    var openBtn = document.getElementById('openChatDemo');
    var closeBtn = document.getElementById('closeChatDemo');
    var closeSidebarBtn = document.getElementById('closeChatSidebar');
    var backBtn = document.getElementById('chatBackBtn');
    var chatRoomList = document.getElementById('chatRoomList');
    var chatMessages = document.getElementById('chatMessages');
    var chatMainName = document.getElementById('chatMainName');
    var chatMainAvatar = document.getElementById('chatMainAvatar');
    var chatInput = document.getElementById('chatInput');
    var chatSendBtn = document.getElementById('chatSendBtn');

    if (!openBtn || !chatRoomList || !chatMessages || !chatMainName || !chatMainAvatar || !chatInput || !chatSendBtn) return;

    var rooms = [
        {
            id: 0,
            name: '대원스틸 담당자',
            initial: '대',
            time: '10:22',
            unread: 0,
            messages: [
                { type: 'badge', text: '오늘 · 거래 수락됨' },
                { type: 'in', sender: '대원스틸', text: '안녕하세요! 거래 요청 검토했습니다. 긍정적으로 진행해보고 싶습니다.', time: '오전 10:12' },
                { type: 'out', text: '감사합니다! 구체적인 납기 조건과 단가 논의를 해보면 좋겠습니다.', time: '오전 10:15' },
                { type: 'in', sender: '대원스틸', text: '네, 이번 주 중으로 견적서 보내드리겠습니다.', time: '오전 10:22' }
            ]
        },
        {
            id: 1,
            name: '(주)클라우드브릿지',
            initial: '클',
            time: '어제',
            unread: 2,
            messages: [
                { type: 'badge', text: '어제 · 거래 연결됨' },
                { type: 'in', sender: '클라우드브릿지', text: '협력 관련해서 논의해보고 싶습니다.', time: '오후 3:40' },
                { type: 'in', sender: '클라우드브릿지', text: '편하실 때 미팅 일정 잡아볼 수 있을까요?', time: '오후 3:41' }
            ]
        }
    ];

    var activeRoomId = 0;

    function renderRooms() {
        chatRoomList.innerHTML = '';
        rooms.forEach(function (room) {
            var lastMsg = room.messages[room.messages.length - 1];
            var preview = lastMsg ? lastMsg.text : '';
            if (preview.length > 20) preview = preview.slice(0, 20) + '…';
            var item = document.createElement('div');
            item.className = 'chat-room-item' + (room.id === activeRoomId ? ' active' : '');
            item.innerHTML =
                '<div class="chat-room-avatar">' + room.initial + '</div>' +
                '<div class="chat-room-info">' +
                    '<div class="chat-room-name">' + room.name + '</div>' +
                    '<div class="chat-room-preview">' + preview + '</div>' +
                '</div>' +
                '<div class="chat-room-meta">' +
                    '<span class="chat-room-time">' + room.time + '</span>' +
                    (room.unread > 0 ? '<span class="chat-room-unread">' + room.unread + '</span>' : '') +
                '</div>';
            item.addEventListener('click', function () {
                activeRoomId = room.id;
                room.unread = 0;
                renderRooms();
                renderMessages(room);
                if (window.innerWidth <= 600) {
                    overlay.querySelector('.chat-demo-modal').classList.add('chat-demo-modal--mobile-chat');
                }
            });
            chatRoomList.appendChild(item);
        });
    }

    function renderMessages(room) {
        chatMainName.textContent = room.name;
        chatMainAvatar.textContent = room.initial;
        chatMessages.innerHTML = '';
        room.messages.forEach(function (msg) {
            if (msg.type === 'badge') {
                var b = document.createElement('span');
                b.className = 'chat-demo-badge';
                b.textContent = msg.text;
                chatMessages.appendChild(b);
                return;
            }
            var wrap = document.createElement('div');
            wrap.className = 'chat-msg chat-msg--' + msg.type;
            var html = '';
            if (msg.type === 'in') html += '<span class="chat-msg-sender">' + msg.sender + '</span>';
            html += '<div class="chat-msg-bubble">' + msg.text + '</div>';
            html += '<span class="chat-msg-time">' + msg.time + '</span>';
            wrap.innerHTML = html;
            chatMessages.appendChild(wrap);
        });
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function sendMessage() {
        var text = (chatInput.value || '').trim();
        if (!text) return;
        var now = new Date();
        var hh = now.getHours();
        var mm = now.getMinutes();
        var ampm = hh < 12 ? '오전' : '오후';
        hh = hh % 12 || 12;
        var timeStr = ampm + ' ' + hh + ':' + (mm < 10 ? '0' : '') + mm;
        var room = rooms[activeRoomId];
        room.messages.push({ type: 'out', text: text, time: timeStr });
        room.time = timeStr;
        chatInput.value = '';
        renderMessages(room);
        renderRooms();
    }

    chatSendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') sendMessage();
    });

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            overlay.querySelector('.chat-demo-modal').classList.remove('chat-demo-modal--mobile-chat');
        });
    }
    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener('click', function () {
            overlay.classList.remove('open');
            overlay.querySelector('.chat-demo-modal').classList.remove('chat-demo-modal--mobile-chat');
        });
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            overlay.classList.remove('open');
            overlay.querySelector('.chat-demo-modal').classList.remove('chat-demo-modal--mobile-chat');
        });
    }

    openBtn.addEventListener('click', function () {
        var loggedIn = window._isLoggedIn ? window._isLoggedIn() : true;
        if (!loggedIn && window._requireLogin) {
            window._requireLogin('채팅', '담당자와 채팅하시려면<br>로그인이 필요합니다.');
            return;
        }
        overlay.classList.add('open');
        overlay.querySelector('.chat-demo-modal').classList.remove('chat-demo-modal--locked');
        renderRooms();
        renderMessages(rooms[activeRoomId]);
    });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.classList.remove('open');
    });
})();

// 모바일 전용: 카테고리 2줄 스와이프 캐러셀 (대분류 8개)
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

// 비주얼 배너 캐러셀: 3장 겹침, 3초 자동 로테이션, 1/3> 페이지네이션
(function() {
    var wrap = document.querySelector('.mVisual_banner_wrap');
    if (!wrap) return;
    var container = wrap.querySelector('.mVisual_banner_swiper');
    var paginationEl = wrap.querySelector('.mVisual_banner_pagination');
    var nextBtn = wrap.querySelector('.mVisual_banner_next');
    var slides = container ? container.querySelectorAll('.swiper-slide') : [];
    if (!slides.length) return;
    var total = slides.length;
    var current = 0;
    var timer;

    function applyClasses() {
        var nextIdx = (current + 1) % total;
        var prevIdx = (current - 1 + total) % total;
        slides.forEach(function(s, i) {
            s.classList.remove('slide-active', 'slide-next', 'slide-prev');
            if (i === current) s.classList.add('slide-active');
            else if (i === nextIdx) s.classList.add('slide-next');
            else if (i === prevIdx) s.classList.add('slide-prev');
        });
        if (paginationEl) {
            var cur = paginationEl.querySelector('.mVisual_banner_page_current');
            var tot = paginationEl.querySelector('.mVisual_banner_page_total');
            if (cur) cur.textContent = current + 1;
            if (tot) tot.textContent = total;
        }
    }

    function goNext() {
        current = (current + 1) % total;
        applyClasses();
    }

    function startAutoplay() {
        clearInterval(timer);
        timer = setInterval(goNext, 3000);
    }

    function equalizeBannerHeight() {
        var maxH = 0;
        slides.forEach(function(s) {
            s.style.position = 'relative';
            s.style.height = 'auto';
        });
        slides.forEach(function(s) {
            var h = s.offsetHeight;
            if (h > maxH) maxH = h;
        });
        if (container) container.style.height = maxH + 'px';
        slides.forEach(function(s) {
            s.style.position = '';
            s.style.height = '';
        });
    }

    applyClasses();
    equalizeBannerHeight();
    startAutoplay();

    window.addEventListener('resize', equalizeBannerHeight);
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            goNext();
            startAutoplay();
        });
    }
})();

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
            detailOpenMode: 'page',
            popupSelector: '.popup_submit',
            requestTypeSelector: '#request_type',
            receiverNameSelector: '#recv',
            planUrl: '/ko/plan.php',
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
