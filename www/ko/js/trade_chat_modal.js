(function () {
	'use strict';

	function initTradeChatModal() {
		var overlay = document.getElementById('chatDemoOverlay');
		if (!overlay) return;
		if (window.TradeChatModal && window.TradeChatModal.__initialized) return;

		var modal = overlay.querySelector('.chat-demo-modal');
		var closeBtn = document.getElementById('closeChatDemo');
		var closeSidebarBtn = document.getElementById('closeChatSidebar');
		var backBtn = document.getElementById('chatBackBtn');
		var openDemoBtn = document.getElementById('openChatDemo');
		var roomListEl = document.getElementById('chatRoomList');
		var searchInputEl = document.getElementById('tradeChatRoomSearch');
		var messagesEl = document.getElementById('chatMessages');
		var mainNameEl = document.getElementById('chatMainName');
		var mainStatusEl = document.getElementById('chatMainStatus');
		var mainAvatarEl = document.getElementById('chatMainAvatar');
		var inputEl = document.getElementById('chatInput');
		var sendBtn = document.getElementById('chatSendBtn');
		var formEl = document.getElementById('tradeChatModalForm');
		var context = window.__TRADE_CHAT_MODAL_CONTEXT || {};
		var state = {
			bootstrap: null,
			client: null,
			channel: null,
			channelListener: null,
			activeRoomId: 0,
			filterKeyword: '',
			bootPromise: null
		};

		function ensureLoginGuardModal() {
			var guard = document.getElementById('tradeChatLoginGuard');
			if (guard) return guard;

			guard = document.createElement('div');
			guard.id = 'tradeChatLoginGuard';
			guard.className = 'login-guard-modal';
			guard.innerHTML = '' +
				'<div class="login-guard">' +
					'<div class="login-guard__overlay" aria-hidden="true"></div>' +
					'<div class="login-guard__modal" role="dialog" aria-modal="true" aria-labelledby="tradeChatLoginGuardTitle">' +
						'<div class="login-guard__icon" aria-hidden="true">' +
							'<svg viewBox="0 0 24 24">' +
								'<path d="M12 12a4 4 0 1 0 0-8a4 4 0 0 0 0 8Z"></path>' +
								'<path d="M4 20a8 8 0 0 1 16 0"></path>' +
							'</svg>' +
						'</div>' +
						'<h1 class="login-guard__title" id="tradeChatLoginGuardTitle">로그인 후 이용 가능합니다</h1>' +
						'<p class="login-guard__desc" id="tradeChatLoginGuardDesc">로그인 후 이용하세요.<br>로그인 페이지로 이동해 주세요.</p>' +
						'<div class="login-guard__actions">' +
							'<button type="button" class="login-guard__btn login-guard__btn--ghost" id="tradeChatLoginGuardBack">이전으로</button>' +
							'<a class="login-guard__btn login-guard__btn--primary" id="tradeChatLoginGuardLink" href="/bbs/login.php">로그인</a>' +
						'</div>' +
					'</div>' +
				'</div>';

			document.body.appendChild(guard);

			guard.querySelector('#tradeChatLoginGuardBack').addEventListener('click', function () {
				document.documentElement.classList.remove('hide-scroll');
				guard.classList.remove('is-open');
			});

			return guard;
		}

		function redirectToLogin() {
			var loginUrl = context.loginUrl || '/bbs/login.php?url=' + encodeURIComponent(window.location.pathname + window.location.search);
			var guard = ensureLoginGuardModal();
			closeModal();
			guard.querySelector('#tradeChatLoginGuardLink').setAttribute('href', loginUrl);
			guard.querySelector('#tradeChatLoginGuardDesc').innerHTML = '로그인 후 이용하세요.<br>로그인 페이지로 이동해 주세요.';
			document.documentElement.classList.add('hide-scroll');
			guard.classList.add('is-open');
		}

		function isMobileViewport() {
			return window.innerWidth <= 600;
		}

		function escapeHtml(value) {
			return String(value || '')
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;')
				.replace(/'/g, '&#039;');
		}

		function formatTime(value) {
			if (!value) return '';
			var date = new Date(value);
			if (Number.isNaN(date.getTime())) return '';

			return date.toLocaleString('ko-KR', {
				year: 'numeric',
				month: '2-digit',
				day: '2-digit',
				hour: '2-digit',
				minute: '2-digit'
			});
		}

		function setMainHeader(room, statusText, statusCode) {
			var title = room && room.title ? room.title : '채팅방을 선택해 주세요';
			var initial = room && room.initial ? room.initial : '채';
			mainNameEl.textContent = title;
			mainAvatarEl.textContent = initial;
			mainStatusEl.setAttribute('data-room-status', statusCode || 'waiting');
			mainStatusEl.innerHTML =
				'<span class="chat-main-status__dot" aria-hidden="true"></span>' +
				'<span class="chat-main-status__text">' + escapeHtml(statusText || '연결 대기 중') + '</span>';
		}

		function setComposerEnabled(enabled, placeholder) {
			inputEl.disabled = !enabled;
			sendBtn.disabled = !enabled;
			inputEl.placeholder = placeholder || '메시지를 입력하세요…';
		}

		function renderEmptyMessages(message) {
			messagesEl.innerHTML = '<div class="trade-chat-empty-state trade-chat-empty-state--messages">' + escapeHtml(message) + '</div>';
		}

		function renderStatusBadge(text) {
			messagesEl.innerHTML = '<span class="chat-demo-badge">' + escapeHtml(text) + '</span>';
		}

		function renderRoomStateNotice(room, meta, detail) {
			var title = room && room.title ? room.title : '채팅방';
			messagesEl.innerHTML = '' +
				'<div class="trade-chat-state-card is-' + escapeHtml(meta.code) + '">' +
					'<strong class="trade-chat-state-card__title">' + escapeHtml(title) + '</strong>' +
					'<p class="trade-chat-state-card__body">' + escapeHtml(detail) + '</p>' +
				'</div>';
		}

		function cleanupActiveChannel() {
			if (!state.channel) return;

			try {
				if (state.channelListener && typeof state.channel.off === 'function') {
					state.channel.off('message.new', state.channelListener);
				}
			} catch (error) {
			}

			try {
				if (typeof state.channel.stopWatching === 'function') {
					state.channel.stopWatching();
				}
			} catch (error) {
			}

			state.channel = null;
			state.channelListener = null;
		}

		async function fetchRoomPayload(roomId) {
			var bootstrapUrl = context.bootstrapUrl || '/ko/trade_chat_modal_data.php';
			var url = bootstrapUrl + (bootstrapUrl.indexOf('?') > -1 ? '&' : '?') + 'mode=room&room_id=' + encodeURIComponent(roomId);
			var response = await fetch(url, {
				method: 'GET',
				credentials: 'same-origin',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			});

			if (!response.ok) {
				throw new Error('채팅방 정보를 불러오지 못했습니다.');
			}

			return response.json();
		}

		function renderMessages(messages, currentUserId, room) {
			if (!Array.isArray(messages) || messages.length < 1) {
				renderEmptyMessages('아직 메시지가 없습니다.');
				return;
			}

			messagesEl.innerHTML = messages.map(function (message) {
				var mine = !!(message.user && message.user.id === currentUserId);
				var sender = mine
					? String((room && room.myCompanyName) || '우리 회사')
					: String((room && room.counterpartyCompanyName) || (room && room.title) || '상대 기업');
				return '<div class="chat-msg chat-msg--' + (mine ? 'out' : 'in') + '">' +
					(mine ? '' : '<span class="chat-msg-sender">' + escapeHtml(sender) + '</span>') +
					'<div class="chat-msg-bubble">' + escapeHtml(message.text || '') + '</div>' +
					'<span class="chat-msg-time">' + escapeHtml(formatTime(message.created_at)) + '</span>' +
				'</div>';
			}).join('');

			messagesEl.scrollTop = messagesEl.scrollHeight;
		}

		function findRoomById(roomId) {
			if (!state.bootstrap || !Array.isArray(state.bootstrap.rooms)) return null;
			for (var i = 0; i < state.bootstrap.rooms.length; i++) {
				if (Number(state.bootstrap.rooms[i].roomId) === Number(roomId)) {
					return state.bootstrap.rooms[i];
				}
			}
			return null;
		}

		function getRoomStatusMeta(roomStatus) {
			var status = String(roomStatus || '').toLowerCase();
			if (status === 'active') {
				return {
					code: 'active',
					label: '채팅 가능',
					header: '온라인',
					placeholder: '메시지를 입력하세요…'
				};
			}

			if (status === 'pending') {
				return {
					code: 'pending',
					label: '준비중',
					header: '준비 중',
					placeholder: '채팅 준비가 완료되면 메시지를 보낼 수 있습니다.'
				};
			}

			if (status === 'blocked') {
				return {
					code: 'blocked',
					label: '차단됨',
					header: '오프라인',
					placeholder: '현재 이 채팅방에는 메시지를 보낼 수 없습니다.'
				};
			}

			if (status === 'closed') {
				return {
					code: 'closed',
					label: '종료됨',
					header: '오프라인',
					placeholder: '종료된 채팅방에는 메시지를 보낼 수 없습니다.'
				};
			}

			return {
				code: 'unknown',
				label: '확인중',
				header: '준비 중',
				placeholder: '채팅 정보를 불러오는 중입니다.'
			};
		}

		function getFilteredRooms() {
			var rooms = state.bootstrap && Array.isArray(state.bootstrap.rooms) ? state.bootstrap.rooms : [];
			var keyword = String(state.filterKeyword || '').trim().toLowerCase();
			if (keyword === '') return rooms;

			return rooms.filter(function (room) {
				return String(room.title || '').toLowerCase().indexOf(keyword) > -1
					|| String(room.preview || '').toLowerCase().indexOf(keyword) > -1
					|| String(room.companyPairKey || '').toLowerCase().indexOf(keyword) > -1;
			});
		}

		function renderRoomList() {
			var rooms = getFilteredRooms();
			if (!rooms.length) {
				var message = state.filterKeyword
					? '검색 조건과 일치하는 채팅방이 없습니다.'
					: '연결된 채팅방이 없습니다.';
				roomListEl.innerHTML = '<div class="trade-chat-empty-state trade-chat-empty-state--list">' + escapeHtml(message) + '</div>';
				return;
			}

			roomListEl.innerHTML = rooms.map(function (room) {
				var active = Number(room.roomId) === Number(state.activeRoomId);
				var timeText = room.updatedAt ? formatTime(room.updatedAt) : '';
				return '<button type="button" class="chat-room-item' + (active ? ' active' : '') + '" data-room-id="' + room.roomId + '">' +
					'<div class="chat-room-avatar">' + escapeHtml(room.initial || '채') + '</div>' +
					'<div class="chat-room-info">' +
						'<div class="chat-room-name">' + escapeHtml(room.title || '채팅방') + '</div>' +
						'<div class="chat-room-preview">' + escapeHtml(room.preview || '최근 대화가 없습니다.') + '</div>' +
					'</div>' +
					'<div class="chat-room-meta">' +
						'<span class="chat-room-time">' + escapeHtml(timeText) + '</span>' +
					'</div>' +
				'</button>';
			}).join('');
		}

		async function ensureClient(roomPayload) {
			var bootstrap = roomPayload || state.bootstrap || {};
			var StreamChatCtor = window.StreamChat && (window.StreamChat.StreamChat || window.StreamChat);

			if (!StreamChatCtor) {
				throw new Error('Stream JS SDK를 불러오지 못했습니다.');
			}

			if (!bootstrap.configured || !bootstrap.apiKey || !bootstrap.token || !bootstrap.user || !bootstrap.user.id) {
				throw new Error('채팅 연결 정보가 부족합니다.');
			}

			if (!state.client) {
				state.client = StreamChatCtor.getInstance
					? StreamChatCtor.getInstance(bootstrap.apiKey)
					: new StreamChatCtor(bootstrap.apiKey);
				await state.client.connectUser(bootstrap.user, bootstrap.token);
			}

			return state.client;
		}

		async function bindRoom(roomId) {
			var room = findRoomById(roomId);
			var roomMeta = room ? getRoomStatusMeta(room.roomStatus) : getRoomStatusMeta('');
			state.activeRoomId = room ? Number(room.roomId) : 0;
			cleanupActiveChannel();
			renderRoomList();

			if (!room) {
				setMainHeader(null, '연결 대기 중', 'waiting');
				renderEmptyMessages('채팅방을 선택하면 대화 내용을 불러옵니다.');
				setComposerEnabled(false, '채팅방을 먼저 선택해 주세요.');
				return;
			}

			setMainHeader(room, roomMeta.header, roomMeta.code);

			if (isMobileViewport()) {
				modal.classList.add('chat-demo-modal--mobile-chat');
			}

			if (room.roomStatus !== 'active') {
				var roomNotice = roomMeta.code === 'pending'
					? '거래 승인 이후 채팅 채널 동기화를 기다리는 중입니다. 잠시 후 다시 확인해 주세요.'
					: roomMeta.code === 'blocked'
						? '현재 이 채팅방은 차단 상태입니다. 운영팀 확인 후 다시 이용할 수 있습니다.'
						: roomMeta.code === 'closed'
							? '종료된 채팅방입니다. 기존 메시지는 확인만 가능하며 새 메시지는 보낼 수 없습니다.'
							: '현재 채팅방 상태를 확인하는 중입니다.';
				renderRoomStateNotice(room, roomMeta, roomNotice);
				setComposerEnabled(false, roomMeta.placeholder);
				return;
			}

			if (!room.channelType || !room.channelId) {
				renderEmptyMessages('채팅 채널 정보가 아직 준비되지 않았습니다.');
				setComposerEnabled(false, '채널 정보가 준비되면 메시지를 보낼 수 있습니다.');
				return;
			}

			try {
				renderStatusBadge('채팅 서버에 연결 중입니다.');
				var roomPayload = await fetchRoomPayload(room.roomId);
				if (!roomPayload || roomPayload.success === false) {
					throw new Error((roomPayload && roomPayload.message) || '채팅방 정보를 불러오지 못했습니다.');
				}
				var client = await ensureClient(roomPayload);
				var channel = client.channel(roomPayload.channelType || room.channelType, roomPayload.channelId || room.channelId);
				await channel.watch();
				state.channel = channel;
				state.channelListener = function () {
					renderMessages(channel.state && channel.state.messages ? channel.state.messages : [], state.bootstrap.user.id, room);
				};
				if (typeof channel.on === 'function') {
					channel.on('message.new', state.channelListener);
				}
				setMainHeader(room, roomMeta.header, roomMeta.code);
				renderMessages(channel.state && channel.state.messages ? channel.state.messages : [], state.bootstrap.user.id, room);
				setComposerEnabled(true, roomMeta.placeholder);
			} catch (error) {
				setMainHeader(room, '준비 중', 'error');
				renderEmptyMessages(error && error.message ? error.message : '채팅 연결에 실패했습니다.');
				setComposerEnabled(false, '채팅 연결 후 메시지를 입력할 수 있습니다.');
			}
		}

		async function fetchBootstrap(initialRoomId) {
			var bootstrapUrl = context.bootstrapUrl || '/ko/trade_chat_modal_data.php';
			var url = bootstrapUrl;
			if (initialRoomId) {
				url += (url.indexOf('?') > -1 ? '&' : '?') + 'initial_room_id=' + encodeURIComponent(initialRoomId);
			}

			var response = await fetch(url, {
				method: 'GET',
				credentials: 'same-origin',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			});

			if (!response.ok) {
				throw new Error('채팅방 목록을 불러오지 못했습니다.');
			}

			return response.json();
		}

		async function boot(options) {
			options = options || {};
			var initialRoomId = Number(options.roomId || 0);

			if (context.isLoggedIn === false) {
				redirectToLogin();
				return false;
			}

			try {
				state.bootPromise = fetchBootstrap(initialRoomId);
				state.bootstrap = await state.bootPromise;
				state.bootPromise = null;
			} catch (error) {
				state.bootPromise = null;
				state.bootstrap = null;
				renderEmptyMessages(error && error.message ? error.message : '채팅 정보를 불러오지 못했습니다.');
				setComposerEnabled(false, '채팅 정보를 불러오는 중입니다.');
				return false;
			}

			if (!state.bootstrap || state.bootstrap.login === false) {
				redirectToLogin();
				return false;
			}

			renderRoomList();

			if (!state.bootstrap.rooms || !state.bootstrap.rooms.length) {
				setMainHeader(null, '연결 대기 중', 'waiting');
				renderEmptyMessages('아직 연결된 채팅방이 없습니다.');
				setComposerEnabled(false, '연결된 채팅방이 생기면 메시지를 보낼 수 있습니다.');
				return true;
			}

			await bindRoom(state.bootstrap.initialRoomId || initialRoomId || state.bootstrap.rooms[0].roomId);
			return true;
		}

		async function openModal(options) {
			options = options || {};

			if (context.isLoggedIn === false) {
				redirectToLogin();
				return false;
			}

			overlay.classList.add('open');
			overlay.setAttribute('aria-hidden', 'false');
			modal.classList.remove('chat-demo-modal--locked');

			if (isMobileViewport()) {
				if (options.startInChat === true) {
					modal.classList.add('chat-demo-modal--mobile-chat');
				} else {
					modal.classList.remove('chat-demo-modal--mobile-chat');
				}
			} else {
				modal.classList.remove('chat-demo-modal--mobile-chat');
			}

			document.documentElement.classList.add('hide-scroll');

			if (roomListEl && (!state.bootstrap || options.reload === true || options.roomId)) {
				roomListEl.innerHTML = '<div class="trade-chat-empty-state trade-chat-empty-state--list">연결 가능한 채팅방을 불러오는 중입니다.</div>';
				renderEmptyMessages('채팅방을 불러오는 중입니다.');
				setComposerEnabled(false, '채팅 정보를 불러오는 중입니다.');
				try {
					await boot(options);
				} catch (error) {
					roomListEl.innerHTML = '<div class="trade-chat-empty-state trade-chat-empty-state--list">' + escapeHtml(error && error.message ? error.message : '채팅방 목록을 불러오지 못했습니다.') + '</div>';
					renderEmptyMessages(error && error.message ? error.message : '채팅 정보를 불러오지 못했습니다.');
					setComposerEnabled(false, '채팅 정보를 다시 확인해 주세요.');
				}
			}
		}

		function closeModal() {
			cleanupActiveChannel();
			overlay.classList.remove('open');
			overlay.setAttribute('aria-hidden', 'true');
			modal.classList.remove('chat-demo-modal--mobile-chat');
			modal.classList.remove('chat-demo-modal--locked');
			document.documentElement.classList.remove('hide-scroll');
		}

		function backToList() {
			modal.classList.remove('chat-demo-modal--mobile-chat');
		}

		if (openDemoBtn) {
			openDemoBtn.addEventListener('click', function () {
				openModal({
					startInChat: false
				});
			});
		}

		if (closeBtn) {
			closeBtn.addEventListener('click', closeModal);
		}

		if (closeSidebarBtn) {
			closeSidebarBtn.addEventListener('click', closeModal);
		}

		if (backBtn) {
			backBtn.addEventListener('click', backToList);
		}

		if (searchInputEl) {
			searchInputEl.addEventListener('input', function () {
				state.filterKeyword = searchInputEl.value || '';
				renderRoomList();
			});
		}

		if (roomListEl) {
			roomListEl.addEventListener('click', function (event) {
				var trigger = event.target.closest('[data-room-id]');
				if (!trigger) return;
				bindRoom(Number(trigger.getAttribute('data-room-id') || 0));
			});
		}

		if (formEl) {
			formEl.addEventListener('submit', async function (event) {
				event.preventDefault();
				if (!state.channel || inputEl.disabled) return;

				var text = String(inputEl.value || '').trim();
				if (!text) return;

				setComposerEnabled(false, '메시지 전송 중입니다.');
				try {
					await state.channel.sendMessage({ text: text });
					inputEl.value = '';
					renderMessages(state.channel.state && state.channel.state.messages ? state.channel.state.messages : [], state.bootstrap.user.id, findRoomById(state.activeRoomId));
				} catch (error) {
					renderEmptyMessages(error && error.message ? error.message : '메시지 전송에 실패했습니다.');
				} finally {
					setComposerEnabled(true, '메시지를 입력하세요…');
					inputEl.focus();
				}
			});
		}

		overlay.addEventListener('click', function (event) {
			if (event.target === overlay) {
				closeModal();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && overlay.classList.contains('open')) {
				closeModal();
			}
		});

		window.TradeChatModal = {
			__initialized: true,
			open: openModal,
			close: closeModal,
			reload: function (options) {
				options = options || {};
				options.reload = true;
				return openModal(options);
			},
			showChatPane: function () {
				if (isMobileViewport()) {
					modal.classList.add('chat-demo-modal--mobile-chat');
				}
			},
			showRoomList: backToList,
			selectRoom: bindRoom,
			getOverlay: function () {
				return overlay;
			}
		};
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initTradeChatModal);
	} else {
		initTradeChatModal();
	}
})();
