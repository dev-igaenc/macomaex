var matchingPageData = window.__MATCHING_PAGE_DATA || {};
var receivedData = Array.isArray(matchingPageData.received) ? matchingPageData.received : [];
var sentData = Array.isArray(matchingPageData.sent) ? matchingPageData.sent : [];
var interestedData = Array.isArray(matchingPageData.interested) ? matchingPageData.interested : [];
var matchingCsrfToken = matchingPageData.csrfToken || '';

/* ======================================================
   Rendering Functions
   ====================================================== */

function renderMsgHtml(msg) {
    if (msg && typeof msg === 'object') {
        var tags = '<div class="req-msg-tags">';
        if (msg.direction) tags += '<span class="req-tag dir">' + msg.direction + '</span>';
        (msg.types || []).forEach(function (t) { tags += '<span class="req-tag type">' + t + '</span>'; });
        if (msg.matchType) tags += '<span class="req-tag mtype">' + msg.matchType + ' 거래</span>';
        tags += '</div>';

        var itemHtml = '';
        if (msg.item) {
            itemHtml = '<div class="req-detail"><div class="req-detail-section"><div class="req-detail-row"><span class="req-detail-label">거래 희망 품목</span><span class="req-detail-value">' + msg.item + '</span></div></div></div>';
        }

        var extra = msg.extra ? '<p class="req-msg-extra">' + msg.extra + '</p>' : '';
        return tags + itemHtml + extra;
    }
    return msg || '';
}

/* ======================================================
   공통 SVG / 헬퍼
   ====================================================== */
function maskName(s) {
    var m = s.match(/^(\(주\)|\(유\)|\(사\))?(.*)$/);
    var prefix = m[1] || '';
    var name   = m[2];
    var visible = prefix ? name.substring(0, 1) : name.substring(0, 2);
    return prefix + visible + '****';
}

function maskAddress(s) {
    var address = String(s || '').trim();
    if (!address) return '주소 비공개';
    return '****';
}

function favBtnHtml(active, kedcd) {
    var kedcdAttr = kedcd ? ' data-kedcd="' + kedcd + '"' : '';
    return '<button type="button" class="btn-favorite' + (active ? ' is-active' : '') + '" data-favorite' + kedcdAttr + ' title="관심기업" aria-label="관심기업"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></button>';
}
function arrowSvg() {
    return '<svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M2 4l3 3 3-3"/></svg>';
}
function chatSvg() {
    return '<svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>';
}
function openChat(id) {
    var allItems = receivedData.concat(sentData, interestedData);
    var item = allItems.find(function(candidate) {
        return String(candidate.id) === String(id);
    });

    if (!item || !item.chatEnabled || !item.chatRoomId) {
        if (window._showToast) {
            window._showToast('!', '채팅방 준비가 아직 완료되지 않았습니다', 'default');
        } else {
            alert('채팅방 준비가 아직 완료되지 않았습니다.');
        }
        return;
    }

    if (window.TradeChatModal && typeof window.TradeChatModal.open === 'function') {
        window.TradeChatModal.open({
            roomId: item.chatRoomId,
            startInChat: true
        });
        return;
    }

    if (window._showToast) {
        window._showToast('!', '채팅 모달을 불러오지 못했습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.', 'default');
    } else {
        alert('채팅 모달을 불러오지 못했습니다. 페이지를 새로고침한 뒤 다시 시도해 주세요.');
    }
}

function retryChat(id) {
    var allItems = receivedData.concat(sentData, interestedData);
    var item = allItems.find(function(candidate) {
        return String(candidate.id) === String(id);
    });

    if (!item || !item.chatRoomId) {
        if (window._showToast) window._showToast('!', '재시도할 채팅방 정보가 없습니다', 'default');
        else alert('재시도할 채팅방 정보가 없습니다.');
        return;
    }

    var body = new URLSearchParams();
    body.set('action', 'trade_chat_retry');
    body.set('room_id', item.chatRoomId);
    body.set('csrf_token', matchingCsrfToken);

    fetch('/ko/my_matching.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: body.toString()
    }).then(function(res) {
        return res.json();
    }).then(function(payload) {
        if (payload && payload.success) {
            item.chatEnabled = true;
            item.chatRoomStatus = 'active';
            if (window._showToast) window._showToast('✓', '채팅방 준비가 완료되었습니다', 'success');
            openChat(id);
            return;
        }

        var message = payload && payload.message ? payload.message : '채팅방 준비 재시도에 실패했습니다';
        if (window._showToast) window._showToast('!', message, 'default');
        else alert(message);
    }).catch(function() {
        if (window._showToast) window._showToast('!', '채팅방 준비 재시도에 실패했습니다', 'default');
        else alert('채팅방 준비 재시도에 실패했습니다.');
    });
}
function statusBadgeHtml(status) {
    if (status === 'N') return '<span class="status-badge pending">연결중</span>';
    if (status === 'Y') return '<span class="status-badge accepted">수락됨</span>';
    if (status === 'R') return '<span class="status-badge rejected">거래거절</span>';
    return '';
}

/* ======================================================
   공통 카드 빌더
   ====================================================== */
/* dt 라벨 상수 — 자간 조정용 <i> 포함 */
var DT = {
    addr:    '주<i></i>소',
    industry:'종<i></i>목',
    manager: '담<i></i>당<i></i>자',
    message: '메<i></i>시<i></i>지',
    score:   '거래점수'
};

function infoRow(dt, dd) {
    return '<dl><dt>' + dt + '</dt><dd>' + dd + '</dd></dl>';
}

function addrRow(address) {
    return '<dl><dt>' + DT.addr + '</dt><dd compact-row="address">' +
        '<div class="compact-row-inline">' +
            '<span class="compact-row-text">' + address + '</span>' +
            '<button type="button" class="compact-row-toggle" compact-more onclick="toggleCompactRow(this)"><span>더보기</span></button>' +
        '</div>' +
    '</dd></dl>';
}

function msgRow(msg) {
    return '<dl><dt>' + DT.message + '</dt><dd message><div>' + renderMsgHtml(msg) + '</div>' +
        '<button type="button" message-more onclick="toggleMessage(this)"><i>' + arrowSvg() + '</i><span>요청 내용 펼치기</span></button></dd></dl>';
}

/**
 * renderBaseCard(item, opts)
 * opts: {
 *   status    : 'N'|'Y'|'R'|undefined  — data-status 속성 및 name행 배지
 *   badge     : html string             — 커스텀 배지 (status 배지 대신 사용 시)
 *   alwaysFav : bool                    — starred 값 무시하고 항상 활성
 *   rows      : html string             — info-area 내부 행 전체
 *   btnHtml   : html string             — btn-option 내부
 * }
 */
function renderBaseCard(item, opts) {
    var statusAttr  = opts.status ? ' data-status="' + opts.status + '"' : '';
    var badge       = opts.badge !== undefined ? opts.badge : '';
    var fav         = favBtnHtml(opts.alwaysFav ? true : !!item.starred, item.kedcd);
    var displayName = opts.name !== undefined ? opts.name : item.company;
    var detailTarget = item.detailUrl || getDetailId(item.company);
    var detailExtra = !item.detailUrl && opts.nocta ? '&nocta=1' : '';
    var detailBtn   = '<button type="button" class="btn-detail" onclick="openDetailPopup(\'' + detailTarget + detailExtra + '\',\'' + item.company.replace(/'/g,'\\\'') + '\')">기업정보보기</button>';
    return '<div class="matching-item" data-id="' + item.id + '"' + statusAttr + '>' +
        '<p class="name"><strong>' + displayName + '</strong>' + badge + '<span class="name-actions">' + detailBtn + fav + '</span></p>' +
        '<div class="info-area">' + (opts.rows || '') + '</div>' +
        '<div class="btn-option">' + (opts.btnHtml || '') + '</div>' +
    '</div>';
}

/* ======================================================
   카드 렌더 함수
   ====================================================== */
function renderReceivedItem(item) {
    var managerHtml = item.status === 'Y'
        ? item.manager
        : '<span class="pending-text">수락 후 공개</span>';
    var btnHtml = item.status === 'N'
        ? '<button class="btn-accept" onclick="handleAccept(' + item.id + ')">수락하기</button>' +
          '<button class="btn-reject" onclick="handleReject(' + item.id + ')">거절하기</button>'
        : item.status === 'Y'
            ? (item.chatEnabled
                ? '<button class="btn-chat" onclick="openChat(' + item.id + ')">' + chatSvg() + '채팅창 열기</button>'
                : '<button class="btn-chat" onclick="retryChat(' + item.id + ')">' + chatSvg() + '채팅 준비중</button>')
            : '<span class="btn-disabled">거래거절</span>';
    return renderBaseCard(item, {
        status:  item.status,
        nocta:   true,
        rows:    addrRow(item.address) + infoRow(DT.industry, item.industry) +
                 infoRow(DT.manager, managerHtml) + msgRow(item.message),
        btnHtml: btnHtml
    });
}

function renderSentItem(item) {
    var managerHtml = item.status === 'Y'
        ? item.manager
        : '<span class="pending-text">거래 성공시 공개</span>';
    var btnHtml = item.status === 'N'
        ? '<span class="btn-disabled">연결중</span>'
        : item.status === 'Y'
            ? (item.chatEnabled
                ? '<button class="btn-chat" onclick="openChat(' + item.id + ')">' + chatSvg() + '채팅창 열기</button>'
                : '<button class="btn-chat" onclick="retryChat(' + item.id + ')">' + chatSvg() + '채팅 준비중</button>')
            : '<span class="btn-disabled">거래거절</span>';
    return renderBaseCard(item, {
        status:  item.status,
        nocta:   true,
        rows:    addrRow(item.address) + infoRow(DT.industry, item.industry) +
                 infoRow(DT.manager, managerHtml) + msgRow(item.message),
        btnHtml: btnHtml
    });
}

function renderInterestedItem(item) {
    var isCreditUsed = Number(item.creditUsed || 0) === 1;
    var btnHtml = '';
    if (!item.matchStatus) {
        btnHtml = '<button class="btn-match-request" onclick="handleMatchRequest(\'' + item.company.replace(/'/g,'\\\'') + '\', \'' + (item.kedcd || '') + '\')">거래요청하기</button>';
    } else {
        var parts     = item.matchStatus.split('-');
        var direction = parts[0]; // 'received' or 'sent'
        var status    = parts[1]; // 'N', 'Y', or 'R'
        if (direction === 'received') {
            if (status === 'N') {
                btnHtml = '<button class="btn-accept" onclick="handleAccept(' + item.id + ')">수락하기</button>' +
                          '<button class="btn-reject" onclick="handleReject(' + item.id + ')">거절하기</button>';
            } else if (status === 'Y') {
                btnHtml = item.chatEnabled
                    ? '<button class="btn-chat" onclick="openChat(\'' + item.id + '\')">' + chatSvg() + '채팅창 열기</button>'
                    : '<button class="btn-chat" onclick="retryChat(\'' + item.id + '\')">' + chatSvg() + '채팅 준비중</button>';
            } else {
                btnHtml = '<span class="btn-disabled">거래거절</span>';
            }
        } else { // sent
            if (status === 'N') {
                btnHtml = '<span class="btn-disabled">연결중</span>';
            } else if (status === 'Y') {
                btnHtml = item.chatEnabled
                    ? '<button class="btn-chat" onclick="openChat(\'' + item.id + '\')">' + chatSvg() + '채팅창 열기</button>'
                    : '<button class="btn-chat" onclick="retryChat(\'' + item.id + '\')">' + chatSvg() + '채팅 준비중</button>';
            } else {
                btnHtml = '<span class="btn-disabled">거래거절</span>';
            }
        }
    }
    return renderBaseCard(item, {
        name:      isCreditUsed ? item.company : maskName(item.company),
        masked:    !isCreditUsed,
        alwaysFav: true,
        rows:      infoRow(DT.industry, item.industry) + addrRow(isCreditUsed ? item.address : maskAddress(item.address)),
        btnHtml:   btnHtml
    });
}

function renderSelectedItem(item) {
    return renderBaseCard(item, {
        rows:    infoRow(DT.industry, item.industry) + infoRow(DT.addr, item.address) +
                 infoRow(DT.score, '<strong style="color:#2563eb;">' + item.matchScore + '점</strong> / 100점'),
        btnHtml: '<button class="btn-match-request" onclick="handleMatchRequest(\'' + item.company.replace(/'/g,'\\\'') + '\', \'' + (item.kedcd || '') + '\')">거래요청</button>'
    });
}

/* ======================================================
   Render Lists
   ====================================================== */
function rejectedSectionHtml(items, renderFn) {
    var xSvg   = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="8" y1="8" x2="16" y2="16"/><line x1="16" y1="8" x2="8" y2="16"/></svg>';
    var arrSvg = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>';
    return '<div class="rejected-section">' +
        '<button class="btn-rejected-toggle" onclick="toggleRejected(this)">' +
            xSvg + '거절된 연결 ' + items.length + '건 보기' +
            '<span class="rj-arrow">' + arrSvg + '</span>' +
        '</button>' +
        '<div class="rejected-list">' + items.map(renderFn).join('') + '</div>' +
    '</div>';
}

function toggleRejected(btn) {
    var rejList = btn.nextElementSibling;
    var isOpen  = rejList.classList.toggle('open');
    btn.classList.toggle('open', isOpen);
    if (isOpen) {
        if (window._bindFavorites) window._bindFavorites();
        hideShortMessages(rejList);
    }
}

function renderReceived(filter) {
    var list     = document.getElementById('receivedList');
    var allItems = filter === 'all' ? receivedData : receivedData.filter(function(d) { return d.status === filter; });
    var items    = allItems.filter(function(d) { return d.status !== 'R'; });
    var rejected = allItems.filter(function(d) { return d.status === 'R'; });
    if (items.length === 0 && rejected.length === 0) {
        list.innerHTML = '<div class="NoData">거래 자료가 없습니다.</div>';
    } else {
        list.innerHTML = (items.length ? items.map(renderReceivedItem).join('') : '') +
                         (rejected.length ? rejectedSectionHtml(rejected, renderReceivedItem) : '');
    }
    if (window._bindFavorites) window._bindFavorites();
    hideShortMessages(list);
}

function renderSent(filter) {
    var list     = document.getElementById('sentList');
    var allItems = filter === 'all' ? sentData : sentData.filter(function(d) { return d.status === filter; });
    var items    = allItems.filter(function(d) { return d.status !== 'R'; });
    var rejected = allItems.filter(function(d) { return d.status === 'R'; });
    if (items.length === 0 && rejected.length === 0) {
        list.innerHTML = '<div class="NoData">거래 자료가 없습니다.</div>';
    } else {
        list.innerHTML = (items.length ? items.map(renderSentItem).join('') : '') +
                         (rejected.length ? rejectedSectionHtml(rejected, renderSentItem) : '');
    }
    if (window._bindFavorites) window._bindFavorites();
    hideShortMessages(list);
}

function renderInterested() {
    var list = document.getElementById('interestedList');
    if (!interestedData.length) {
        list.innerHTML = '<div class="NoData">관심기업 자료가 없습니다.</div>';
    } else {
        list.innerHTML = interestedData.map(renderInterestedItem).join('');
    }
    if (window._bindFavorites) window._bindFavorites();
}


function updateCounts() {
    // Received counts
    document.getElementById('rcvCountAll').textContent = receivedData.length;
    document.getElementById('rcvCountN').textContent = receivedData.filter(function(d){return d.status==='N';}).length;
    document.getElementById('rcvCountY').textContent = receivedData.filter(function(d){return d.status==='Y';}).length;
    // Sent counts
    document.getElementById('sntCountAll').textContent = sentData.length;
    document.getElementById('sntCountN').textContent = sentData.filter(function(d){return d.status==='N';}).length;
    document.getElementById('sntCountY').textContent = sentData.filter(function(d){return d.status==='Y';}).length;
}

function setFavoriteButtonsState(kedcd, active) {
    if (!kedcd) return;
    document.querySelectorAll('[data-favorite][data-kedcd="' + kedcd + '"]').forEach(function(btn) {
        btn.classList.toggle('is-active', !!active);
    });
}

function findItemByKedcd(kedcd) {
    var receivedItem = receivedData.find(function(item) { return item.kedcd === kedcd; });
    if (receivedItem) {
        return {
            source: 'received',
            item: receivedItem
        };
    }

    var sentItem = sentData.find(function(item) { return item.kedcd === kedcd; });
    if (sentItem) {
        return {
            source: 'sent',
            item: sentItem
        };
    }

    var interestedItem = interestedData.find(function(item) { return item.kedcd === kedcd; });
    if (interestedItem) {
        return {
            source: 'interested',
            item: interestedItem
        };
    }

    return null;
}

function buildInterestedItemFromSource(source, item) {
    var matchStatus = null;
    if (source === 'received') {
        matchStatus = 'received-' + item.status;
    } else if (source === 'sent') {
        matchStatus = 'sent-' + item.status;
    }

    return {
        id: 'fav_' + item.kedcd,
        company: item.company,
        address: item.address,
        industry: item.industry,
        creditUsed: Number(item.creditUsed || 0),
        matchStatus: matchStatus,
        starred: true,
        kedcd: item.kedcd,
        detailUrl: item.detailUrl || '',
        chatRoomId: item.chatRoomId || 0,
        chatEnabled: !!item.chatEnabled,
        chatRoomStatus: item.chatRoomStatus || ''
    };
}

function syncFavoriteState(kedcd, active) {
    if (!kedcd) return;

    receivedData.forEach(function(item) {
        if (item.kedcd === kedcd) item.starred = !!active;
    });

    sentData.forEach(function(item) {
        if (item.kedcd === kedcd) item.starred = !!active;
    });

    interestedData.forEach(function(item) {
        if (item.kedcd === kedcd) item.starred = !!active;
    });

    if (active) {
        var exists = interestedData.some(function(item) { return item.kedcd === kedcd; });
        if (!exists) {
            var sourceItem = findItemByKedcd(kedcd);
            if (sourceItem && sourceItem.item) {
                interestedData.unshift(buildInterestedItemFromSource(sourceItem.source, sourceItem.item));
            }
        }
    } else {
        interestedData = interestedData.filter(function(item) { return item.kedcd !== kedcd; });
    }

    setFavoriteButtonsState(kedcd, active);

    if (currentMainTab === 'received') {
        renderReceived(currentSubFilter.received);
    } else if (currentMainTab === 'sent') {
        renderSent(currentSubFilter.sent);
    } else if (currentMainTab === 'interested') {
        renderInterested();
    }
}

/* ======================================================
   Tab Switching
   ====================================================== */
var currentMainTab = 'received';
var currentSubFilter = { received: 'all', sent: 'all' };
var tradeRequestActionUrls = ['/ko/my_matching.php'];
var sentStatusRequestUrls = ['/ko/my_matching.php'];
var sentStatusPollTimer = null;

function moveTabIndicator(tab) {
    var activeEl = document.querySelector('#mainTabs a[data-tab="' + tab + '"]');
    var indicator = document.getElementById('tabIndicator');
    if (!activeEl || !indicator) return;
    indicator.style.left  = activeEl.offsetLeft + 'px';
    indicator.style.width = activeEl.offsetWidth + 'px';
}

function switchMainTab(tab) {
    currentMainTab = tab;

    // Update main tab styles
    document.querySelectorAll('#mainTabs a').forEach(function(a) {
        a.classList.toggle('active', a.dataset.tab === tab);
    });

    // Move sliding indicator
    moveTabIndicator(tab);

    // Show/hide panels
    document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
    document.getElementById('panel-' + tab).classList.add('active');

    // Show/hide sub-filter tabs
    document.getElementById('subTabsReceived').style.display = (tab === 'received') ? '' : 'none';
    document.getElementById('subTabsSent').style.display = (tab === 'sent') ? '' : 'none';

    // Render content
    if (tab === 'received') renderReceived(currentSubFilter.received);
    else if (tab === 'sent') renderSent(currentSubFilter.sent);
    else if (tab === 'interested') renderInterested();

    if (tab === 'sent') {
        refreshSentStatuses();
        startSentStatusPolling();
    } else {
        stopSentStatusPolling();
    }
}

/* ======================================================
   Interactions
   ====================================================== */
function toggleMessage(btn) {
    var dd = btn.closest('dd');
    if (!dd.classList.contains('active')) {
        dd.classList.add('active');
        btn.querySelector('span').textContent = '요청 내용 접기';
    } else {
        dd.classList.remove('active');
        btn.querySelector('span').textContent = '요청 내용 펼치기';
    }
}

function toggleCompactRow(btn) {
    var dd = btn.closest('dd');
    if (!dd) return;

    if (!dd.classList.contains('active')) {
        dd.classList.add('active');
        btn.querySelector('span').textContent = '접기';
    } else {
        dd.classList.remove('active');
        btn.querySelector('span').textContent = '더보기';
    }
}

function refreshCompactRows(root) {
    (root || document).querySelectorAll('dd[compact-row]').forEach(function(dd) {
        var inner = dd.querySelector('.compact-row-text');
        var btn   = dd.querySelector('button[compact-more]');
        if (!inner || !btn) return;

        var overflowWidth = Math.ceil(inner.scrollWidth) - Math.ceil(inner.clientWidth);
        var isOverflow = overflowWidth > 10;

        btn.style.display = isOverflow ? '' : 'none';
        dd.classList.toggle('is-overflow', isOverflow);

        if (!isOverflow) {
            dd.classList.remove('active');
            btn.querySelector('span').textContent = '더보기';
        }
    });
}

function hideShortMessages(root) {
    (root || document).querySelectorAll('dd[message]').forEach(function(dd) {
        var inner = dd.querySelector('div');
        var btn   = dd.querySelector('button[message-more]');
        if (!inner || !btn) return;
        btn.style.display = inner.scrollHeight <= inner.offsetHeight + 2 ? 'none' : '';
    });

    refreshCompactRows(root);
}

function splitResponseParts(text) {
    return String(text || '').split('|');
}

function approvalStatusToCode(status) {
    if (Number(status) === 1) return 'Y';
    if (Number(status) === 2) return 'R';
    return 'N';
}

function requestTradeApproval(url, id, action) {
    var body = new URLSearchParams();
    body.set('action', 'trade_request_approval');
    body.set('idx', id);
    body.set('typ', action);
    body.set('csrf_token', matchingCsrfToken);

    return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: body.toString()
    }).then(function(res) {
        return res.text();
    });
}

function submitTradeApproval(id, action) {
    var index = 0;

    function tryNext() {
        if (index >= tradeRequestActionUrls.length) {
            return Promise.resolve('500');
        }

        var url = tradeRequestActionUrls[index++];
        return requestTradeApproval(url, id, action)
            .then(function(res) {
                var code = splitResponseParts(res)[0];
                if (code === '1000' || code === '501') return res;
                return tryNext();
            })
            .catch(function() {
                return tryNext();
            });
    }

    return tryNext();
}

function syncInterestedMatchStatusByKedcd(kedcd, status, direction) {
    if (!kedcd) return;

    interestedData.forEach(function(item) {
        if (item.kedcd !== kedcd) return;
        item.matchStatus = direction + '-' + status;
    });
}

function updateReceivedItemStatus(id, nextStatus) {
    var item = receivedData.find(function(d) { return d.id === id; });
    if (!item) return false;

    item.status = nextStatus;
    item.approvalStatus = nextStatus === 'Y' ? 1 : nextStatus === 'R' ? 2 : 0;
    syncInterestedMatchStatusByKedcd(item.kedcd, nextStatus, 'received');
    updateCounts();
    renderReceived(currentSubFilter.received);
    return true;
}

function requestSentStatuses(url) {
    var body = new URLSearchParams();
    body.set('action', 'my_matching_sent_status');

    return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
        },
        body: body.toString()
    }).then(function(res) {
        return res.json();
    });
}

function fetchSentStatuses() {
    var index = 0;

    function tryNext() {
        if (index >= sentStatusRequestUrls.length) {
            return Promise.reject(new Error('sent status request failed'));
        }

        var url = sentStatusRequestUrls[index++];
        return requestSentStatuses(url)
            .then(function(payload) {
                if (payload && (payload.success || payload.login === false)) return payload;
                return tryNext();
            })
            .catch(function() {
                return tryNext();
            });
    }

    return tryNext();
}

function applySentStatuses(items) {
    if (!Array.isArray(items)) return false;

    var changed = false;
    var statusMap = {};

    items.forEach(function(item) {
        statusMap[String(item.id)] = approvalStatusToCode(item.approval_status);
    });

    sentData.forEach(function(item) {
        var nextStatus = statusMap[String(item.id)];
        if (!nextStatus || item.status === nextStatus) return;

        item.status = nextStatus;
        item.approvalStatus = nextStatus === 'Y' ? 1 : nextStatus === 'R' ? 2 : 0;
        syncInterestedMatchStatusByKedcd(item.kedcd, nextStatus, 'sent');
        changed = true;
    });

    if (changed) {
        updateCounts();

        if (currentMainTab === 'sent') {
            renderSent(currentSubFilter.sent);
        } else if (currentMainTab === 'interested') {
            renderInterested();
        }
    }

    return changed;
}

function refreshSentStatuses() {
    return fetchSentStatuses()
        .then(function(payload) {
            if (!payload || payload.login === false) return false;
            return applySentStatuses(payload.items || []);
        })
        .catch(function() {
            return false;
        });
}

function startSentStatusPolling() {
    if (sentStatusPollTimer) return;
    sentStatusPollTimer = window.setInterval(function() {
        refreshSentStatuses();
    }, 15000);
}

function stopSentStatusPolling() {
    if (!sentStatusPollTimer) return;
    window.clearInterval(sentStatusPollTimer);
    sentStatusPollTimer = null;
}

// Confirm dialog system
var confirmCallback = null;
function showConfirm(title, msg, cb) {
    document.getElementById('confirmTitle').textContent = title;
    document.getElementById('confirmMsg').textContent = msg;
    document.getElementById('confirmOverlay').classList.add('is_open');
    document.documentElement.classList.add('hide-scroll');
    confirmCallback = cb;
}

function handleAccept(id) {
    showConfirm('요청 수락', '정말 수락하시겠습니까?', function(ok) {
        if (!ok) return;

        submitTradeApproval(id, 'accept').then(function(res) {
            var parts = splitResponseParts(res);
            var code = parts[0];

            if (code === '1000') {
                updateReceivedItemStatus(id, 'Y');
                if (window._showToast) {
                    window._showToast('✓', '요청을 수락했습니다', 'success');
                }
                window.setTimeout(function() {
                    window.location.reload();
                }, 500);
                return;
            }

            if (code === '501') {
                window.location.href = '/bbs/login.php?url=' + encodeURIComponent(window.location.pathname + window.location.search + window.location.hash);
                return;
            }

            if (window._showToast) {
                window._showToast('!', '요청 수락 처리 중 오류가 발생했습니다', 'default');
            } else {
                alert('요청 수락 처리 중 오류가 발생했습니다.');
            }
        }).catch(function() {
            if (window._showToast) {
                window._showToast('!', '요청 수락 처리 중 오류가 발생했습니다', 'default');
            } else {
                alert('요청 수락 처리 중 오류가 발생했습니다.');
            }
        });
    });
}

function handleReject(id) {
    showConfirm('요청 거절', '정말 거절하시겠습니까?', function(ok) {
        if (!ok) return;

        submitTradeApproval(id, 'reject').then(function(res) {
            var parts = splitResponseParts(res);
            var code = parts[0];

            if (code === '1000') {
                updateReceivedItemStatus(id, 'R');
                if (window._showToast) {
                    window._showToast('!', '요청을 거절했습니다', 'default');
                }
                return;
            }

            if (code === '501') {
                window.location.href = '/bbs/login.php?url=' + encodeURIComponent(window.location.pathname + window.location.search + window.location.hash);
                return;
            }

            if (window._showToast) {
                window._showToast('!', '요청 거절 처리 중 오류가 발생했습니다', 'default');
            } else {
                alert('요청 거절 처리 중 오류가 발생했습니다.');
            }
        }).catch(function() {
            if (window._showToast) {
                window._showToast('!', '요청 거절 처리 중 오류가 발생했습니다', 'default');
            } else {
                alert('요청 거절 처리 중 오류가 발생했습니다.');
            }
        });
    });
}

function handleCancel(id) {
    showConfirm('거래 취소', '정말 취소하시겠습니까?', function(ok) {
        if (!ok) return;
        sentData = sentData.filter(function(d) { return d.id !== id; });
        updateCounts();
        renderSent(currentSubFilter.sent);
    });
}

function buildMatchRequestTargetByKedcd(kedcd, companyName) {
    var item = findItemByKedcd(kedcd);
    var sourceItem = item && item.item ? item.item : null;

    return {
        kedcd: kedcd || '',
        company: sourceItem && sourceItem.company ? sourceItem.company : companyName,
        bzno: sourceItem && sourceItem.bzno ? sourceItem.bzno : '',
        detailUrl: sourceItem && sourceItem.detailUrl ? sourceItem.detailUrl : '',
        address: sourceItem && sourceItem.address ? sourceItem.address : '',
        founded: sourceItem && sourceItem.founded ? sourceItem.founded : '',
        employees: sourceItem && sourceItem.employees ? sourceItem.employees : '',
        corpType: sourceItem && sourceItem.corpType ? sourceItem.corpType : '',
        industry: sourceItem && sourceItem.industry ? sourceItem.industry : '',
        product: sourceItem && sourceItem.product ? sourceItem.product : ''
    };
}

window.onTradeRequestCreated = function(data, target) {
    if (!target || !target.kedcd) return;

    var newItem = {
        id: data && data.tr_no ? Number(data.tr_no) : Date.now(),
        tradeNo: data && data.tr_no ? Number(data.tr_no) : 0,
        company: target.company || '정보 준비중',
        bzno: target.bzno || '',
        address: target.address || '정보 준비중',
        industry: target.industry || '정보 준비중',
        manager: '',
        phone: '',
        email: '',
        status: 'N',
        starred: true,
        kedcd: target.kedcd,
        detailUrl: target.detailUrl || '',
        requestBzno: '',
        targetBzno: '',
        approvalStatus: 0,
        message: {
            matchType: '일반',
            direction: '',
            types: [],
            item: '',
            extra: ''
        }
    };

    var existingSent = sentData.find(function(item) { return item.kedcd === target.kedcd && item.status === 'N'; });
    if (!existingSent) {
        sentData.unshift(newItem);
    }

    interestedData.forEach(function(item) {
        if (item.kedcd === target.kedcd) {
            item.matchStatus = 'sent-N';
        }
    });

    updateCounts();
    if (currentMainTab === 'interested') {
        renderInterested();
    } else if (currentMainTab === 'sent') {
        renderSent(currentSubFilter.sent);
    }

    if (window._showToast) {
        window._showToast('✓', '거래 요청서가 저장되었습니다', 'success');
    }
};

function handleMatchRequest(companyName, kedcd) {
    var target = buildMatchRequestTargetByKedcd(kedcd, companyName);
    if (window.setMatchRequestTarget) {
        window.setMatchRequestTarget(target);
    }
    window._matchCompany = { id: target.kedcd || getDetailId(companyName), name: companyName };
    window.openModal('modalRequest');
}

/* AI Loading – now loaded from external file */

/* ======================================================
   Init
   ====================================================== */
function initMyMatchingPage() {
    document.querySelectorAll('#mainTabs a').forEach(function(a) {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            var tab = this.dataset.tab;
            window.location.hash = tab === 'received' ? '#received' : '#' + tab;
            switchMainTab(tab);
        });
    });

    document.querySelectorAll('#subTabsReceived a').forEach(function(a) {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            var filter = this.dataset.filter;
            currentSubFilter.received = filter;
            document.querySelectorAll('#subTabsReceived a').forEach(function(x) { x.classList.remove('active'); });
            this.classList.add('active');
            renderReceived(filter);
        });
    });

    document.querySelectorAll('#subTabsSent a').forEach(function(a) {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            var filter = this.dataset.filter;
            currentSubFilter.sent = filter;
            document.querySelectorAll('#subTabsSent a').forEach(function(x) { x.classList.remove('active'); });
            this.classList.add('active');
            renderSent(filter);
        });
    });

    document.getElementById('confirmOk').addEventListener('click', function() {
        document.getElementById('confirmOverlay').classList.remove('is_open');
        document.documentElement.classList.remove('hide-scroll');
        if (confirmCallback) confirmCallback(true);
        confirmCallback = null;
    });

    document.getElementById('confirmCancel').addEventListener('click', function() {
        document.getElementById('confirmOverlay').classList.remove('is_open');
        document.documentElement.classList.remove('hide-scroll');
        if (confirmCallback) confirmCallback(false);
        confirmCallback = null;
    });

    updateCounts();

    // Check hash for initial tab
    var hash = window.location.hash.replace('#', '') || 'received';
    var validTabs = ['interested', 'received', 'sent', 'selected'];
    if (validTabs.indexOf(hash) === -1) hash = 'received';
    switchMainTab(hash);
    // 초기 인디케이터 위치 (레이아웃 완료 후)
    requestAnimationFrame(function() { moveTabIndicator(hash); });

    // Handle browser back/forward
    window.addEventListener('hashchange', function() {
        var h = window.location.hash.replace('#', '') || 'received';
        if (validTabs.indexOf(h) !== -1) switchMainTab(h);
    });

    window.addEventListener('message', function(event) {
        if (event.origin !== window.location.origin) return;
        if (!event.data || event.data.type !== 'favorite-changed') return;
        syncFavoriteState(event.data.kedcd, !!event.data.active);
    });

    window.addEventListener('favorite:changed', function(event) {
        if (!event.detail || !event.detail.kedcd) return;
        syncFavoriteState(event.detail.kedcd, !!event.detail.active);
    });

    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible' && currentMainTab === 'sent') {
            refreshSentStatuses();
        }
    });

    window.addEventListener('load', function() {
        hideShortMessages(document);
    });

    if (document.fonts && typeof document.fonts.ready === 'object' && typeof document.fonts.ready.then === 'function') {
        document.fonts.ready.then(function() {
            hideShortMessages(document);
        });
    }

    var compactResizeTimer = null;
    window.addEventListener('resize', function() {
        clearTimeout(compactResizeTimer);
        compactResizeTimer = setTimeout(function() {
            hideShortMessages(document);
        }, 120);
    });
}

/* ===== 상세보기 팝업 ===== */
function initDetailPopup() {
    var overlay = document.getElementById('detailPopupOverlay');
    var iframe  = document.getElementById('detailPopupIframe');
    var title   = document.getElementById('detailPopupTitle');
    var closeBtn = document.getElementById('detailPopupClose');

    window.openDetailPopup = function(detailId, companyName) {
        var bust = '_popup_ts=' + Date.now();
        iframe.src = detailId + (detailId.indexOf('?') === -1 ? '?' : '&') + bust;
        title.textContent = companyName || '기업 상세보기';
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    function closePopup() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        setTimeout(function() { iframe.src = ''; }, 300);
    }

    closeBtn.addEventListener('click', closePopup);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closePopup();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) closePopup();
    });
}

function bootMyMatchingPage() {
    if (!document.getElementById('matchingContent')) return;
    initMyMatchingPage();
    initDetailPopup();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootMyMatchingPage);
} else {
    bootMyMatchingPage();
}
