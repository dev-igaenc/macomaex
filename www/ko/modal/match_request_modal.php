<?php if (!defined('_GNUBOARD_')) exit; ?>

<!-- 1) 거래 요청서 작성 -->
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
						<tr><th>회사명</th><td id="matchTargetCompanyName">정보 준비중</td></tr>
						<tr><th>사업자번호</th><td id="matchTargetBzno">정보 준비중</td></tr>
						<tr><th>설립</th><td id="matchTargetFounded">정보 준비중</td></tr>
						<tr><th>고용인원</th><td id="matchTargetEmployees">정보 준비중</td></tr>
						<tr><th>기업유형</th><td id="matchTargetCorpType">정보 준비중</td></tr>
						<tr><th>업종</th><td id="matchTargetIndustry">정보 준비중</td></tr>
						<tr><th>주요제품</th><td id="matchTargetProduct">정보 준비중</td></tr>
					</table>
				</div>
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
						<textarea class="extra" id="reqExtraNote" maxlength="500"></textarea>
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

<!-- 2) 거래 타입 선택 -->
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
