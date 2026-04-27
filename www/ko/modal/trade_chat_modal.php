<?php if (!defined('_GNUBOARD_')) exit; ?>

<div id="chatDemoOverlay" class="trade-chat-modal-overlay" aria-hidden="true">
	<div class="chat-demo-modal" role="dialog" aria-modal="true" aria-labelledby="chatMainName">
		<div class="chat-sidebar">
			<div class="chat-sidebar-head">
				<span class="chat-sidebar-title">메시지</span>
				<div class="chat-sidebar-head-actions">
					<button class="chat-sidebar-new" id="tradeChatSidebarToggle" type="button" title="목록 접기" aria-label="목록 접기" aria-pressed="false">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="15 18 9 12 15 6"></polyline>
						</svg>
					</button>
				</div>
			</div>
			<div class="chat-sidebar-search">
				<input id="tradeChatRoomSearch" type="text" placeholder="기업명 검색" autocomplete="off">
			</div>
			<div class="chat-room-list" id="chatRoomList">
				<div class="trade-chat-empty-state trade-chat-empty-state--list">
					연결 가능한 채팅방을 불러오는 중입니다.
				</div>
			</div>
		</div>

		<div class="chat-main">
			<div class="chat-main-header">
				<button class="chat-back-btn" id="chatBackBtn" type="button" aria-label="목록으로">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<polyline points="15 18 9 12 15 6"></polyline>
					</svg>
				</button>
				<div class="chat-main-avatar" id="chatMainAvatar">채</div>
				<div class="chat-main-info">
					<div class="chat-main-name" id="chatMainName">채팅방을 선택해 주세요</div>
					<div class="chat-main-status" id="chatMainStatus" data-room-status="waiting">
						<span class="chat-main-status__dot" aria-hidden="true"></span>
						<span class="chat-main-status__text">연결 대기 중</span>
					</div>
				</div>
				<button class="chat-demo-close" id="closeChatDemo" type="button" aria-label="닫기">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>
			</div>

			<div class="chat-demo-messages" id="chatMessages">
				<div class="trade-chat-empty-state trade-chat-empty-state--messages">
					채팅방을 선택하면 대화 내용을 불러옵니다.
				</div>
			</div>

			<form class="chat-demo-input-row" id="tradeChatModalForm" autocomplete="off">
				<input class="chat-demo-input" id="chatInput" type="text" placeholder="메시지를 입력하세요…" disabled>
				<button class="chat-demo-send" id="chatSendBtn" type="submit" aria-label="전송" disabled>
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
						<line x1="22" y1="2" x2="11" y2="13"></line>
						<polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
					</svg>
				</button>
			</form>
		</div>
	</div>
</div>
