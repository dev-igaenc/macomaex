/* ===== Demo Company Data — js/mock-data.js 로 이동됨 ===== */
var demoCompanies = window.MOCK_COMPANIES;

/* ===== Utility Functions ===== */
function escHtml(s) {
	var d = document.createElement('div');
	d.textContent = s;
	return d.innerHTML;
}

function getCompanyId() {
	var params = new URLSearchParams(window.location.search);
	var id = (params.get('id') || '').replace(/[^a-z0-9\-_]/gi, '');
	if (!id || !demoCompanies[id]) id = 'beauty-factory';
	return id;
}

function renderStars(score) {
	var full = Math.floor(score);
	var part = score - full;
	var html = '';
	var starPath = 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z';
	for (var i = 0; i < full; i++) {
		html += '<svg viewBox="0 0 24 24" fill="#ffc107"><path d="' + starPath + '"/></svg>';
	}
	if (part > 0.001) {
		var clipPct = Math.round((1 - part) * 100);
		html += '<span class="star-partial">' +
			'<svg viewBox="0 0 24 24" fill="#ffc107" style="clip-path: inset(0 ' + clipPct + '% 0 0);"><path d="' + starPath + '"/></svg>' +
			'<svg viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="1.5"><path d="' + starPath + '"/></svg>' +
			'</span>';
	}
	return html;
}

function calcYearsInBusiness(foundedStr) {
	var match = foundedStr.match(/(\d{4})/);
	if (!match) return '-';
	var year = parseInt(match[1], 10);
	var currentYear = new Date().getFullYear();
	return (currentYear - year) + '년';
}

function demoRandom(min, max) {
	// Seeded by company id hash for consistency per session
	return Math.floor(Math.random() * (max - min + 1)) + min;
}

/* ===== Main Render ===== */
(function() {
	var companyId = getCompanyId();
	var co = demoCompanies[companyId];

	// Update page title
	document.title = co.title + ' · 기업 상세 — 매커멕스';
	var demoNoticeEl = document.getElementById('demoNoticeCode');
	if (demoNoticeEl) demoNoticeEl.textContent = 'company_detail.html?id=' + companyId;

	// 기업 핵심 정보 카드 (공통 모듈)
	renderDetailLeftCard(co, '');
	window._matchCompany = { id: companyId, name: co.title };

	// === RIGHT COLUMN: 5각형 레이더 차트 ===
	var RD_LABELS = ['성장성','수익성','재무안정','업력','고용규모'];
	var RD_NUM = 5, RD_CX = 105, RD_CY = 100, RD_MAX_R = 48, RD_LABEL_R = 68;
	var RD_GRID = [0.2, 0.4, 0.6, 0.8, 1.0];
	function rdPolar(cx, cy, r, i) {
		var a = (Math.PI * 2 * i / RD_NUM) - Math.PI / 2;
		return { x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) };
	}
	// 업력·고용규모 점수 산출
	var yearsInBiz = calcYearsInBusiness(co.founded);
	var yearsNum = parseFloat(yearsInBiz);
	var careerScore = (!isNaN(yearsNum) ? Math.min(5, Math.max(1, yearsNum / 5)) : 3.0).toFixed(1);
	var empNum = parseInt(co.employees) || 50;
	var employScore = Math.min(5, Math.max(1, Math.log2(empNum / 5))).toFixed(1);
	var radarScores = [co.grow, co.profit, co.stable, parseFloat(careerScore), parseFloat(employScore)];

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
	var sc = parseFloat(co.score);
	var starsFull = Math.floor(sc);
	var starsH = '';
	for (var si = 0; si < starsFull; si++) starsH += '<svg viewBox="0 0 24 24" width="16" height="16" fill="#3B5BDB"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
	if (sc - starsFull >= 0.3) starsH += '<svg viewBox="0 0 24 24" width="16" height="16" fill="#3B5BDB"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';

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

	document.getElementById('detailRadarCard').innerHTML =
		'<div class="radar-chart"><svg viewBox="0 0 210 200" class="radar-svg">' + svgH + '</svg></div>' +
		'<div class="radar-scores">' + scoreItems + '</div>';


	// Floating bar title
	document.getElementById('floatingTitle').textContent = co.title;
})();

/* ===== AI Loading ===== */