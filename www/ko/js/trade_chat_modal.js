(function () {
	var overlay = document.getElementById('chatDemoOverlay');
	if (!overlay) return;

	var modal = overlay.querySelector('.chat-demo-modal');
	var closeBtn = document.getElementById('closeChatDemo');
	var closeSidebarBtn = document.getElementById('closeChatSidebar');
	var backBtn = document.getElementById('chatBackBtn');
	var openDemoBtn = document.getElementById('openChatDemo');

	function isMobileViewport() {
		return window.innerWidth <= 600;
	}

	function openModal(options) {
		options = options || {};

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
	}

	function closeModal() {
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
			openModal();
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
		open: openModal,
		close: closeModal,
		showChatPane: function () {
			if (isMobileViewport()) {
				modal.classList.add('chat-demo-modal--mobile-chat');
			}
		},
		showRoomList: backToList,
		getOverlay: function () {
			return overlay;
		}
	};
})();
