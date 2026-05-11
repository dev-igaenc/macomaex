/* ===== Utility Functions ===== */
function escHtml(s) {
	var d = document.createElement('div');
	d.textContent = s;
	return d.innerHTML;
}

function calcYearsInBusiness(foundedStr) {
	var match = foundedStr.match(/(\d{4})/);
	if (!match) return '-';
	var year = parseInt(match[1], 10);
	var currentYear = new Date().getFullYear();
	return (currentYear - year) + '년';
}

/* ===== Main Render ===== */
(function() {
	var co = window.COMPANY_DETAIL_DATA || null;
	if (!co) return;

	function maskedTitle(title) {
		var raw = String(title || '').trim();
		if (!raw) return '기업 상세';
		var m = raw.match(/^(\(주\)|\(유\)|\(사\))?(.*)$/);
		var prefix = m[1] || '';
		var name = m[2] || '';
		var visible = prefix ? name.substring(0, 1) : name.substring(0, 2);
		return prefix + visible + '****';
	}

	// 브라우저 title도 상세 카드와 같은 기준으로 마스킹/해제 상태를 맞춘다.
	var pageTitleText = co.is_unlocked
		? String(co.title_display || co.title || '기업 상세')
		: maskedTitle(co.title);

	// Update page title
	document.title = pageTitleText + ' · 기업 상세 — 매커멕스';

	// 기업 핵심 정보 카드 (공통 모듈)
	if (typeof renderDetailLeftCard === 'function') {
		renderDetailLeftCard(co, '');
	}
	window._matchCompany = { id: co.kedcd || '', name: co.title };

	var verifyCompanyEl = document.getElementById('modal_verify_company');
	if (verifyCompanyEl) verifyCompanyEl.textContent = co.title || '';

	var verifyTextEl = document.querySelector('.modal_verify_text');
	if (verifyTextEl && co.sub && co.corp_type) {
		verifyTextEl.innerHTML = '귀사는 ' + escHtml(co.sub) + '<br>' + escHtml(co.corp_type) + '으로 확인되었습니다.';
	}

	// === RIGHT COLUMN: 5각형 레이더 차트 ===
	var RD_LABELS = ['성장성','수익성','재무안정','업력','고용규모'];
	var RD_NUM = 5, RD_CX = 105, RD_CY = 100, RD_MAX_R = 48, RD_LABEL_R = 68;
	var RD_GRID = [0.2, 0.4, 0.6, 0.8, 1.0];
	function rdPolar(cx, cy, r, i) {
		var a = (Math.PI * 2 * i / RD_NUM) - Math.PI / 2;
		return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
	}

	var growthScore = parseFloat(co.grow) || 0;
	var profitScore = parseFloat(co.profit) || 0;
	var stableScore = parseFloat(co.stable) || 0;
	var ageScore = parseFloat(co.ageScore);
	if (isNaN(ageScore)) {
		var yearsInBiz = calcYearsInBusiness(co.founded);
		var yearsNum = parseFloat(yearsInBiz);
		ageScore = !isNaN(yearsNum) ? Math.min(5, Math.max(1, yearsNum / 5)) : 0;
	}
	var empScore = parseFloat(co.empScore);
	if (isNaN(empScore)) {
		var empNum = parseInt(String(co.employees).replace(/[^\d]/g, ''), 10) || 0;
		empScore = empNum ? Math.min(5, Math.max(1, Math.log2(empNum / 5))) : 0;
	}
	var radarScores = [growthScore, profitScore, stableScore, ageScore, empScore];

	var svgH = '';
	RD_GRID.forEach(function(lv) {
		var pts = [];
		for (var i = 0; i < RD_NUM; i++) { var p = rdPolar(RD_CX, RD_CY, RD_MAX_R * lv, i); pts.push(p.x + ',' + p.y); }
		svgH += '<polygon points="' + pts.join(' ') + '" class="radar-grid"/>';
	});
	for (var i = 0; i < RD_NUM; i++) {
		var p = rdPolar(RD_CX, RD_CY, RD_MAX_R, i);
		svgH += '<line x1="' + RD_CX + '" y1="' + RD_CY + '" x2="' + p.x + '" y2="' + p.y + '" stroke="#E8EBF0" stroke-width="0.5"/>';
	}
	var norm = radarScores.map(function(s) { return s / 5; });
	var dp = [];
	for (var i = 0; i < RD_NUM; i++) { var p = rdPolar(RD_CX, RD_CY, RD_MAX_R * norm[i], i); dp.push(p.x + ',' + p.y); }
	svgH += '<polygon points="' + dp.join(' ') + '" class="radar-shape"/>';
	for (var i = 0; i < RD_NUM; i++) {
		var p = rdPolar(RD_CX, RD_CY, RD_MAX_R * norm[i], i);
		svgH += '<circle cx="' + p.x + '" cy="' + p.y + '" class="radar-dot"/>';
	}
	for (var i = 0; i < RD_NUM; i++) {
		var p = rdPolar(RD_CX, RD_CY, RD_LABEL_R, i);
		var anc = 'middle', dx = 0;
		if (p.x < RD_CX - 5) { anc = 'end'; dx = -2; }
		else if (p.x > RD_CX + 5) { anc = 'start'; dx = 2; }
		var dy = 0;
		if (p.y < RD_CY - 10) dy = -3;
		else if (p.y > RD_CY + 10) dy = 8;
		else dy = 3;
		svgH += '<text x="' + (p.x + dx) + '" y="' + (p.y + dy) + '" text-anchor="' + anc + '" class="radar-label">' + RD_LABELS[i] + '</text>';
	}
	var scoreItems = '';
	var scoreLabels = ['성장성','수익성','재무안정','업력','고용규모'];
	radarScores.forEach(function(v, i) {
		var pct = Math.min(100, Math.max(0, (v / 5) * 100));
		scoreItems += '<div class="radar-score-item"><span class="radar-score-label">' + scoreLabels[i] + '</span><div class="radar-score-bar"><div class="radar-score-bar-fill" style="width:' + Math.round(pct) + '%"></div></div><span class="radar-score-val">' + v.toFixed(1) + '</span></div>';
	});
	// 종합 점수 항목
	var avgScore = (radarScores.reduce(function(a, b) { return a + b; }, 0) / radarScores.length);
	var avgPct = Math.min(100, Math.max(0, (avgScore / 5) * 100));
	scoreItems += '<div class="radar-score-item radar-score-total"><span class="radar-score-label">종합</span><div class="radar-score-bar"><div class="radar-score-bar-fill radar-score-bar-fill--total" style="width:' + Math.round(avgPct) + '%"></div></div><span class="radar-score-val radar-score-val--total">' + avgScore.toFixed(1) + '</span></div>';

	var detailRadarCardEl = document.getElementById('detailRadarCard');
	if (!detailRadarCardEl) return;

	// 우측 카드는 상세 페이지에만 있으므로, 대상이 없으면 조용히 렌더를 중단한다.
	detailRadarCardEl.innerHTML =
		'<div class="radar-chart"><svg viewBox="0 0 210 200" class="radar-svg">' + svgH + '</svg></div>' +
		'<div class="radar-scores">' + scoreItems + '</div>';

	var floatingTitleEl = document.getElementById('floatingTitle');
	if (floatingTitleEl) floatingTitleEl.textContent = co.title;
})();

/* ===== AI Loading ===== */
