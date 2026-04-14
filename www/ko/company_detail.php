<?php
include_once("./_common.php");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>기업 상세 — 매커멕스</title>
	<script>try { if (window.self !== window.top) document.documentElement.classList.add('in-iframe'); } catch(e) {}</script>
	<link href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="css/design-tokens.css" />
	<link rel="stylesheet" href="css/badges.css" />
	<link rel="stylesheet" href="css/favorite.css">
    <!-- <link rel="stylesheet" href="css/auth-toggle.css"> -->
	<link rel="stylesheet" href="css/confirm-modal.css">
	<link rel="stylesheet" href="css/share.css">
	<link rel="stylesheet" href="css/verify-modal.css" />
	<link rel="stylesheet" href="css/ai-loading.css" />
	<link rel="stylesheet" href="css/company-detail-left.css" />
	<link rel="stylesheet" href="css/company-detail.css" />
	<link rel="stylesheet" href="css/match-request-modal.css" />
	<link rel="stylesheet" href="css/top-nav.css" />
</head>
<body>
<div id="detailWrap">

	<!-- Header -->
	<div id="site-header" data-variant="search"></div>
	<script>
	// In iframe mode, prevent header.js from injecting header and setting body paddingTop
	if (document.documentElement.classList.contains('in-iframe')) {
		var _sh = document.getElementById('site-header');
		if (_sh) _sh.removeAttribute('id');
	}
	</script>
	<script src="js/header.js"></script>
	<script>
	if (document.documentElement.classList.contains('in-iframe')) {
		document.body.style.paddingTop = '0';
		document.documentElement.style.setProperty('--header-h', '0px');
		if (new URLSearchParams(window.location.search).get('nocta') === '1') {
			document.documentElement.classList.add('no-cta');
		}
	}
	</script>


	<!-- Verify modal -->
	<div class="modal_verify_overlay" id="modal_verify_overlay" aria-hidden="true">
		<div class="modal_verify" role="dialog" aria-labelledby="modal_verify_company" aria-modal="true">
			<button type="button" class="modal_verify_close" id="modal_verify_btn_close" aria-label="닫기">&times;</button>
			<div class="modal_verify_icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
			</div>
			<h2 class="modal_verify_company" id="modal_verify_company">(주)이가이엔씨</h2>
			<p class="modal_verify_text">귀사는 데이터베이스 및 온라인 정보 제공업<br>법인기업으로 확인되었습니다.</p>
			<p class="modal_verify_text2">본 서비스는 귀사와 동일·유사 업종의 실제 거래 데이터를 분석해 적합한 거래처를 추천해드립니다.</p>
			<hr class="modal_verify_hr">
			<p class="modal_verify_guide">추천 결과를 확인해보세요.</p>
			<button type="button" class="modal_verify_btn" id="modal_verify_btn_ai">
				<span class="sparkle" aria-hidden="true"><svg viewBox="0 0 24 24"><use href="#icon-magic-wand"/></svg></span>
				거래처 확인하기
			</button>
		</div>
	</div>
	<!-- AI guide modal -->
	<div class="modal_verify_overlay" id="modal_ai_guide_overlay" aria-hidden="true">
		<div class="modal_verify" role="dialog" aria-labelledby="modal_ai_guide_title" aria-modal="true">
			<button type="button" class="modal_verify_close" id="modal_ai_guide_btn_close" aria-label="닫기">&times;</button>
			<div class="modal_verify_icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M12 17v-5M12 8h.01M4 12a8 8 0 1 0 16 0 8 8 0 1 0-16 0z"/></svg>
			</div>
			<h2 class="modal_verify_company" id="modal_ai_guide_title">AI 추천 안내</h2>
			<p class="modal_verify_text" id="modal_ai_guide_text">사업자 번호를 입력하고 AI 추천받기를 눌러주세요.</p>
			<p class="modal_verify_text2" id="modal_ai_guide_text2">AI 분석을 통해 업종/거래 데이터를 기반으로 적합한 거래처 판매처를 추천해드립니다.</p>
			<button type="button" class="modal_verify_btn" id="modal_ai_guide_btn_ok">확인했어요</button>
		</div>
	</div>

	<div class="page-wrap">
		<nav class="top-nav">
			<a class="btn-back" href="javascript:history.back();"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg> 돌아가기</a>
			<div class="top-nav-actions">
				<button type="button" class="btn-match-cta-top" id="openTypeModal2">거래 요청하기</button>
				<button type="button" class="btn-favorite" data-favorite title="관심기업 등록" aria-label="관심기업 등록">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
				</button>
				<button type="button" class="btn-topnav-icon" data-share title="공유하기" aria-label="공유하기">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.59 13.51l6.83 3.98M15.41 6.51l-6.82 3.98"/></svg>
				</button>
			</div>
		</nav>

		<div class="layout">
			<!-- Left column: 기업 핵심 정보 -->
			<div class="card" id="leftCard"></div>

			<!-- Right column: 5각형 레이더 차트 -->
			<div class="right-stack" id="rightStack">
				<div class="right-section-card">
					<div class="right-section-title">기업 분석</div>
					<div class="detail-radar-card" id="detailRadarCard"></div>
				</div>
			</div>
		</div>
	</div>

	<!-- 1) 거래 타입 선택 -->
	<div class="modal-overlay" id="modalType" aria-hidden="true">
		<div class="modal modal-sm" role="dialog" aria-labelledby="typeTitle">
			<div class="modal-header">
				<h2 id="typeTitle">거래 타입 선택</h2>
				<button type="button" class="modal-close" data-close="modalType" aria-label="닫기">&times;</button>
			</div>
			<div class="modal-body">
				<div class="type-cards">
					<div class="type-card selected" data-type="normal" role="button" tabindex="0">
						<div class="type-card-head">
							<input type="radio" name="match_type" value="normal" checked>
							<div>
								<div class="type-title">일반 거래</div>
								<div class="type-cost">10 크레딧 차감</div>
							</div>
						</div>
						<p class="type-desc">피거래기업의 응답을 기다리는 방식으로, 회신 시점이 불확실할 수 있습니다.</p>
						<ul class="type-features">
							<li>&#10003; 거래 수락 시 채팅방 오픈</li>
							<li>&#9888; 수락/거절 응답 대기 필요</li>
						</ul>
					</div>
					<div class="type-card" data-type="tm" role="button" tabindex="0">
						<span class="badge-rec">추천</span>
						<div class="type-card-head">
							<input type="radio" name="match_type" value="tm">
							<div>
								<div class="type-title">TM 서포트 거래</div>
								<div class="type-cost">30 크레딧 차감</div>
							</div>
						</div>
						<p class="type-desc">24시간 무응답 시 전문 TM이 피거래기업을 직접 컨택하여 회신을 유도합니다.</p>
						<ul class="type-features">
							<li>&#10003; TM 거래 중계</li>
							<li>&#10003; 무응답 시 직접 컨택</li>
							<li>&#10003; 체계적 진행 보장</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn-gray" id="backToRequest">&#8592; 뒤로</button>
				<button type="button" class="btn-primary" id="confirmType" style="display:inline-flex;align-items:center;gap:6px;"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>거래요청하기</button>
			</div>
		</div>
	</div>

	<!-- 2) 거래 요청서 작성 -->
	<div class="modal-overlay" id="modalRequest" aria-hidden="true">
		<div class="modal" role="dialog" aria-labelledby="reqTitle">
			<div class="modal-header">
				<h2 id="reqTitle">거래 요청서 작성</h2>
				<button type="button" class="modal-close" data-close="modalRequest" aria-label="닫기">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-grid">
					<div>
						<div class="form-section-title">1. 거래요청기업 정보</div>
						<table class="read-table">
							<tr><th>회사명</th><td>(주)해움인터내셔널</td></tr>
							<tr><th>사업자번호</th><td>123-45-67890</td></tr>
							<tr><th>설립</th><td>2008년</td></tr>
							<tr><th>고용인원</th><td>48명</td></tr>
							<tr><th>기업유형</th><td>법인기업</td></tr>
							<tr><th>업종</th><td>화장품 제조업</td></tr>
							<tr><th>주요제품</th><td>산업용 케미컬·정밀첨가제·맞춤 배합·납기·친환경 소재 컨설팅</td></tr>
						</table>
					</div>
					<!-- 거래 요청사항 -->
					<div id="reqSection">
						<div class="form-section-title">2. 요청사항</div>

						<div class="req2-step">
							<div class="req2-step-label">거래 방향</div>
							<div class="req2-chips" id="reqDirChips">
								<label class="req2-chip active"><input type="radio" name="req_dir" value="buy" checked><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg> 구매</label>
								<label class="req2-chip"><input type="radio" name="req_dir" value="supply"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg> 판매</label>
								<label class="req2-chip"><input type="radio" name="req_dir" value="collab"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg> 협업</label>
							</div>
						</div>

						<div class="req2-step">
							<div class="req2-step-label">거래 목적 선택 <span style="font-weight:400;color:#888">(복수 선택 가능)</span></div>
							<div class="req2-chips" id="reqTypeChips"></div>
						</div>

						<div class="req2-step">
							<div class="req2-step-label">거래 상세 정보</div>
							<div class="req2-dynamic">
								<label class="req2-required">거래 희망 품목</label>
								<input type="text" id="reqItemField" name="req_item" placeholder="거래 희망 품목을 입력해 주세요.">
							</div>
						</div>

						<div class="req2-step">
							<div class="req2-step-label">추가 문의 사항</div>
							<textarea class="extra" id="reqExtraNote" maxlength="500">담당자님, 안녕하세요. 귀사를 확인하고 연락드립니다. 신규 거래처를 검토 중이며, 귀사와 적합한 경우 거래 진행을 희망합니다. 상세 조건은 협의를 통해 논의하고자 합니다.</textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn-gray" data-close="modalRequest">&#10005; 취소</button>
				<button type="button" class="btn-primary" id="goToTypeStep">다음 &#10145;</button>
			</div>
		</div>
	</div>

	<!-- floating bar removed -->

	<!-- AI loading overlay (inline from ai_loading.php) -->
	<div class="ai-loading_overlay" id="aiLoadingOverlay">
		<div class="ai-loading_card">
			<div class="ai-loading_spinner"></div>
			<h3 class="ai-loading_title">AI가 거래처를 분석하고 있습니다</h3>
			<div class="ai-loading_steps">
				<div class="ai-loading_step" data-step="0">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>업종 연관성 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="1">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>거래 네트워크 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="2">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>지역 근접성 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="3">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>규모 적합도 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="4">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>재무 건전성 분석 중...</span>
				</div>
				<div class="ai-loading_step" data-step="5">
					<span class="ai-loading_step_icon"><svg viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg></span>
					<span>기업 신뢰도 분석 중...</span>
				</div>
			</div>
			<div class="ai-loading_bar"><div class="ai-loading_bar_fill" id="aiLoadingBarFill"></div></div>
		</div>
	</div>
</div>

<!-- SVG icon definitions -->
<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
	<symbol id="icon-magic-wand" viewBox="0 0 24 24"><path fill="currentColor" d="M16.8 2.2l1.1 2.3 2.3 1.1-2.3 1.1-1.1 2.3-1.1-2.3-2.3-1.1 2.3-1.1 1.1-2.3zm3.2 2.8.6 1.1 1.1.6-1.1.6-.6 1.1-.6-1.1-1.1-.6 1.1-.6.6-1.1zM7.4 17.4 16.1 8.7l2.6 2.6-8.7 8.7-2.6-2.6zm-1 2.4 1.3 1.3-3.1 1 .8-2.9z"/></symbol>
</svg>

<script src="js/mock-data.js"></script>
<script src="js/badges.js"></script>
<script src="js/company-detail-left.js"></script>
<script src="js/company-detail.js"></script>
<script src="js/ai-loading.js"></script>
<script src="js/match-request-modal.js"></script>
<script src="js/favorite.js"></script>
<script src="js/share.js"></script>
<script src="js/confirm-modal.js"></script>
<script src="js/verify-modal.js"></script>
<!-- <script src="inc/auth-toggle.js"></script> -->
</body>
</html>
