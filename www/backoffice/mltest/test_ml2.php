<?php
session_start();
require_once __DIR__ . '/api_config.php';
$_PW = MLTEST_PAGE_PASSWORD;
if (isset($_POST['pw'])) {
    if ($_POST['pw'] === $_PW) $_SESSION['mltest_auth'] = true;
    else $pwError = true;
}
$fixedModel = GEMINI_MODEL;
if (!isset($_SESSION['mltest_auth'])) { ?>
<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ML 테스트 v2 로그인</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f4f6f9;font-family:'Malgun Gothic',sans-serif;padding:20px;}
.box{background:#fff;padding:36px 28px;border-radius:14px;box-shadow:0 4px 20px rgba(0,0,0,.1);width:100%;max-width:360px;text-align:center;}
h2{font-size:17px;margin-bottom:22px;color:#333;}
input{width:100%;padding:13px 16px;border:1.5px solid #ddd;border-radius:10px;font-size:16px;margin-bottom:12px;outline:none;}
input:focus{border-color:#452EFF;}
button{width:100%;padding:14px;background:#452EFF;color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;}
.err{color:#dc3545;font-size:13px;margin-bottom:10px;}
</style>
</head><body><div class="box">
<h2>🔒 ML 테스트 v2</h2>
<?php if (isset($pwError)) echo '<div class="err">비밀번호가 틀렸습니다.</div>'; ?>
<form method="post">
<input type="password" name="pw" placeholder="비밀번호" autofocus>
<button type="submit">확인</button>
</form></div></body></html>
<?php exit; } ?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>ML 추천 테스트 v2</title>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<style>
/* ── Reset & Base ──────────────────────────────────────── */
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Malgun Gothic',sans-serif;background:#f4f6f9;color:#222;padding-bottom:20px;}
/* ── Header ────────────────────────────────────────────── */
.top-bar{background:#452EFF;color:#fff;padding:14px 16px;font-size:16px;font-weight:700;position:sticky;top:0;z-index:100;display:flex;align-items:center;gap:10px;}
.top-bar small{font-size:12px;font-weight:400;opacity:.8;}
/* ── Cards ─────────────────────────────────────────────── */
.wrap{padding:12px;}
@media(min-width:640px){.wrap{padding:24px;max-width:980px;margin:0 auto;}}
.card{background:#fff;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 1px 6px rgba(0,0,0,.07);}
.card-title{font-size:12px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;display:flex;align-items:center;gap:6px;}
.step-num{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;background:#452EFF;color:#fff;border-radius:50%;font-size:11px;font-weight:700;flex-shrink:0;}
.step-num.gray{background:#aaa;}
/* ── Input & Button ─────────────────────────────────────── */
.search-row{display:flex;gap:8px;}
input[type=text]{flex:1;padding:12px 14px;border:1.5px solid #ddd;border-radius:10px;font-size:16px;outline:none;min-width:0;}
input[type=text]:focus{border-color:#452EFF;}
.btn-primary{padding:12px 20px;background:#452EFF;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;white-space:nowrap;min-height:48px;}
.btn-primary:hover{background:#3520e0;}
.btn-primary:disabled{background:#aaa;cursor:not-allowed;}
.btn-sm{padding:7px 14px;border:1.5px solid #ddd;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;background:#fff;color:#555;white-space:nowrap;}
.btn-sm:hover{background:#f0f0f0;}
/* ── Info Grid ──────────────────────────────────────────── */
.info-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;}
@media(min-width:540px){.info-grid{grid-template-columns:repeat(3,1fr);}}
@media(min-width:768px){.info-grid{grid-template-columns:repeat(4,1fr);}}
.info-item{background:#f8f9fa;border-radius:8px;padding:8px 10px;}
.info-label{font-size:10px;color:#999;margin-bottom:2px;}
.info-value{font-size:13px;font-weight:600;color:#222;word-break:break-all;}
/* ── Keyword Chips ─────────────────────────────────────── */
.kw-tabs{display:flex;gap:8px;margin-bottom:12px;}
.kw-tab{padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid #ddd;background:#fff;color:#666;}
.kw-tab.active{background:#452EFF;color:#fff;border-color:#452EFF;}
.chip-wrap{display:flex;flex-wrap:wrap;gap:8px;min-height:36px;}
.kw-chip{padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid #ddd;background:#fff;color:#555;user-select:none;transition:all .15s;}
.kw-chip:hover{border-color:#452EFF;color:#452EFF;}
.kw-chip.selected{background:#452EFF;color:#fff;border-color:#452EFF;}
.kw-sel-count{font-size:12px;color:#888;align-self:center;}
.kw-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:12px;}
/* ── Collapsible ────────────────────────────────────────── */
.collapse-btn{display:flex;align-items:center;justify-content:space-between;cursor:pointer;width:100%;background:none;border:none;padding:0;color:inherit;}
.collapse-arrow{font-size:12px;transition:transform .2s;}
.collapse-arrow.open{transform:rotate(180deg);}
.collapse-body{overflow:hidden;max-height:0;transition:max-height .3s ease;}
.collapse-body.open{max-height:2000px;}
/* ── Result Tabs ────────────────────────────────────────── */
.tabs{display:flex;gap:8px;margin-bottom:12px;}
.tab{padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid #ddd;background:#fff;color:#666;}
.tab.active{background:#452EFF;color:#fff;border-color:#452EFF;}
.tab-panel{display:none;}
.tab-panel.active{display:block;}
@media(max-width:639px){
  .tabs{gap:0;border-bottom:2px solid #eee;margin-bottom:12px;}
  .tab{border:none;border-radius:0;border-bottom:2px solid transparent;margin-bottom:-2px;flex:1;text-align:center;background:none;padding:10px 8px;}
  .tab.active{background:none;color:#452EFF;border-bottom-color:#452EFF;}
}
/* ── Result Cards ───────────────────────────────────────── */
.result-list{display:flex;flex-direction:column;gap:8px;}
.result-card{border:1px solid #eee;border-radius:10px;padding:12px;background:#fafafa;}
.result-card.top1{border-color:#452EFF;background:#f5f4ff;}
.result-card.top3{border-color:#84d0ff;background:#f0f8ff;}
.rc-header{display:flex;align-items:center;gap:8px;margin-bottom:8px;}
.rc-rank{font-size:11px;font-weight:700;color:#fff;background:#452EFF;border-radius:20px;padding:2px 8px;}
.rc-rank.r2,.rc-rank.r3{background:#1976d2;}
.rc-name{font-size:14px;font-weight:700;color:#111;flex:1;}
.rc-score{font-size:13px;font-weight:600;color:#452EFF;}
.rc-meta{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:4px;}
.rc-radar{font-size:11px;color:#666;line-height:1.8;}
@media(min-width:640px){
  .result-list{display:none!important;}
  .result-table-wrap{display:block!important;}
}
.result-table-wrap{display:none;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{background:#f0f0f0;padding:8px 10px;text-align:left;font-weight:600;border-bottom:2px solid #ddd;}
td{padding:8px 10px;border-bottom:1px solid #eee;vertical-align:top;}
tr:hover td{background:#fafafa;}
.pagination{display:flex;align-items:center;gap:8px;margin-top:12px;justify-content:center;}
.pg-btn{padding:8px 18px;border:1.5px solid #ddd;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;background:#fff;color:#452EFF;min-height:40px;}
.pg-btn:hover{background:#f0f0ff;}
.pg-btn:disabled{color:#ccc;border-color:#eee;cursor:default;}
.pg-info{font-size:12px;color:#888;text-align:center;min-width:80px;}
/* ── Badges ─────────────────────────────────────────────── */
.badge-cold{background:#fff3cd;color:#856404;display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;}
.stat-chip{font-size:12px;padding:4px 10px;border-radius:20px;background:#f0f0f8;color:#444;font-weight:600;display:inline-block;}
.ct-chip{font-size:11px;padding:2px 8px;border-radius:10px;font-weight:600;}
.ct-chain2{background:#e8eaf6;color:#3949ab;}
.ct-chain3{background:#f3e5f5;color:#7b1fa2;}
.ct-partner2{background:#e8f5e9;color:#2e7d32;}
.ct-partner3{background:#f1f8e9;color:#558b2f;}
.ct-industry{background:#fff3e0;color:#e65100;}
.ct-neighbor{background:#e3f2fd;color:#1565c0;}
/* ── Spinner ────────────────────────────────────────────── */
.spinner{display:inline-block;width:16px;height:16px;border:2px solid #ddd;border-top-color:#452EFF;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;}
@keyframes spin{to{transform:rotate(360deg)}}
.err{color:#dc3545;font-size:13px;padding:10px 12px;background:#fff5f5;border-radius:8px;}
/* ── Params ─────────────────────────────────────────────── */
.params-list{font-size:12px;line-height:1.8;font-family:monospace;background:#f8f9fa;padding:10px 14px;border-radius:8px;overflow-x:auto;}
.params-list span{color:#452EFF;font-weight:600;}
/* ── LLM A/B ────────────────────────────────────────────── */
.llm-section{margin-bottom:16px;}
.llm-section:last-child{margin-bottom:0;}
.llm-header{display:flex;align-items:center;gap:10px;margin-bottom:10px;flex-wrap:wrap;}
.llm-label{font-size:12px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap;}
.llm-top-info{font-size:13px;font-weight:600;color:#111;background:#f8f9fa;border:1px solid #eee;border-radius:8px;padding:6px 10px;flex:1;min-width:0;}
.ab-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
@media(max-width:639px){.ab-grid{grid-template-columns:1fr;}}
.ab-panel{border:1px solid #eee;border-radius:10px;padding:12px;background:#fafafa;}
.ab-panel.panel-a{border-color:#c5cae9;background:#f5f4ff;}
.ab-panel.panel-b{border-color:#c8e6c9;background:#f1f8f2;}
.ab-label{font-size:11px;font-weight:700;color:#555;background:#fff;border:1px solid #ddd;border-radius:6px;padding:3px 10px;display:inline-block;margin-bottom:8px;}
.btn-generate{padding:10px 20px;background:#0d6efd;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;}
.btn-generate.sell{background:#198754;}
.btn-generate:disabled{background:#aaa;cursor:not-allowed;}
.report-body{font-size:13px;line-height:1.8;margin-top:10px;}
.report-body h1{font-size:16px;margin:14px 0 7px;font-weight:700;}
.report-body h2{font-size:14px;margin:12px 0 5px;font-weight:700;border-bottom:1px solid #eee;padding-bottom:3px;}
.report-body h3{font-size:13px;margin:9px 0 4px;font-weight:700;}
.report-body table{width:100%;border-collapse:collapse;margin:8px 0;font-size:12px;display:block;overflow-x:auto;}
.report-body th{background:#f0f0f0;padding:5px 8px;text-align:left;border:1px solid #ddd;}
.report-body td{padding:4px 8px;border:1px solid #ddd;}
.report-body blockquote{border-left:3px solid #452EFF;padding:7px 12px;background:#f8f8ff;margin:8px 0;border-radius:0 8px 8px 0;}
.report-body p{margin:5px 0;}
.report-body ul,.report-body ol{padding-left:18px;margin:5px 0;}
.report-body hr{border:none;border-top:1px solid #eee;margin:14px 0;}
</style>
</head>
<body>

<div class="top-bar">
  <span>🧪 ML 추천 테스트 v2</span>
  <small id="topCompany"></small>
</div>

<div class="wrap">

<!-- 검색 -->
<div class="card">
  <div class="card-title">사업자번호 입력</div>
  <div class="search-row">
    <input type="text" id="bizno" placeholder="예: 407-88-01455" maxlength="12"
      onkeydown="if(event.key==='Enter')runTest()">
    <button class="btn-primary" id="btnRun" onclick="runTest()">조회</button>
  </div>
</div>

<!-- Step 1: 기업정보 -->
<div id="step1" class="card" style="display:none">
  <div class="card-title"><span class="step-num">1</span>기업 정보 <span id="s1Status" style="margin-left:auto;"></span></div>
  <div id="s1Content"></div>
</div>

<!-- Step 2: 업종 키워드 선택 -->
<div id="step2kw" class="card" style="display:none">
  <div class="card-title"><span class="step-num">2</span>업종 키워드 선택 <span id="kwLoadStatus" style="margin-left:auto;font-size:11px;color:#999;font-weight:400;"></span></div>
  <div class="kw-tabs" id="kwTabRow">
    <div class="kw-tab active" onclick="switchKwTab(1,this)">📥 구매처 업종</div>
    <div class="kw-tab" onclick="switchKwTab(2,this)">📤 판매처 업종</div>
  </div>
  <div id="kwChips"><p style="color:#bbb;font-size:13px;">로딩 중...</p></div>
  <div class="kw-actions">
    <button class="btn-sm" onclick="selectAllChips()">전체선택</button>
    <button class="btn-sm" onclick="clearChips()">선택해제</button>
    <span class="kw-sel-count" id="kwSelCount"></span>
    <button class="btn-primary" id="btnRecommend" onclick="runStep3(false)" style="margin-left:auto;padding:10px 20px;font-size:14px;">선택 업종 추천 →</button>
  </div>
  <div style="margin-top:8px;text-align:right;">
    <button class="btn-sm" id="btnRecommendAll" onclick="runStep3(true)" style="font-size:12px;color:#888;">기존 추천 (전체 업종 대상) →</button>
  </div>
</div>

<!-- ML 파라미터 (접기) -->
<div id="stepParams" class="card" style="display:none">
  <button class="collapse-btn" onclick="toggleParams()">
    <div class="card-title" style="margin:0;"><span class="step-num gray">P</span>ML 전송 파라미터</div>
    <span class="collapse-arrow" id="paramsArrow">▼</span>
  </button>
  <div class="collapse-body" id="paramsBody">
    <div style="height:10px;"></div>
    <div id="sParams"></div>
  </div>
</div>

<!-- Step 3: 추천 결과 -->
<div id="step3" class="card" style="display:none">
  <div class="card-title"><span class="step-num">3</span>추천 결과 <span id="s3Status" style="margin-left:auto;"></span></div>
  <div id="s3Content"></div>
</div>

<!-- Step 4: LLM 검증 -->
<div id="step4" class="card" style="display:none">
  <div class="card-title"><span class="step-num">4</span>LLM 검증 <small style="font-weight:400;color:#aaa;font-size:11px;">모델: <?= htmlspecialchars($fixedModel) ?></small></div>

  <!-- 구매처 1위 -->
  <div class="llm-section">
    <div class="llm-header">
      <span class="llm-label">📥 구매처 추천 1위</span>
      <span class="llm-top-info" id="llmBuyInfo">추천 결과 대기 중</span>
      <button class="btn-generate" id="btnBuy" onclick="generateReport(1)">A·B 분석 ▶</button>
    </div>
    <div class="ab-grid">
      <div class="ab-panel panel-a">
        <span class="ab-label">A — 전략형</span>
        <div id="s4buy-a"></div>
      </div>
      <div class="ab-panel panel-b">
        <span class="ab-label">B — 현장형</span>
        <div id="s4buy-b"></div>
      </div>
    </div>
  </div>

  <hr style="border:none;border-top:1px solid #eee;margin:16px 0;">

  <!-- 판매처 1위 -->
  <div class="llm-section">
    <div class="llm-header">
      <span class="llm-label">📤 판매처 추천 1위</span>
      <span class="llm-top-info" id="llmSellInfo">추천 결과 대기 중</span>
      <button class="btn-generate sell" id="btnSell" onclick="generateReport(2)">A·B 분석 ▶</button>
    </div>
    <div class="ab-grid">
      <div class="ab-panel panel-a">
        <span class="ab-label">A — 전략형</span>
        <div id="s4sell-a"></div>
      </div>
      <div class="ab-panel panel-b">
        <span class="ab-label">B — 현장형</span>
        <div id="s4sell-b"></div>
      </div>
    </div>
  </div>
</div>

</div><!-- /wrap -->

<script>
// ── 전역 상태 ──────────────────────────────────────────────
var FIXED_MODEL = '<?= htmlspecialchars($fixedModel) ?>';
var _company = null, _mlBuy = [], _mlSell = [];
var _buyPage = 0, _sellPage = 0;
var _srcKeywords = [];
var _kwTab = 1;
var _kwData = {1: [], 2: []};
var _kwLoaded = {1: false, 2: false};
// Map(bzc → Set(kw)) — 탭별 독립 선택, 키워드 수 = 가중치
var _kwSelected = {1: new Map(), 2: new Map()};
var PER_PAGE = 10;

// ── 유틸 ────────────────────────────────────────────────────
function xmlVal(v){if(v===null||v===undefined)return '';if(typeof v==='object')return '';return String(v).trim();}
function xmlInt(v){var s=xmlVal(v);return s?(parseInt(s,10)||0):0;}
function buildQs(p){return Object.keys(p).filter(function(k){return p[k]!==''&&p[k]!==null&&p[k]!==undefined;}).map(function(k){return encodeURIComponent(k)+'='+encodeURIComponent(p[k]);}).join('&');}
function ctClass(t){return 'ct-chip ct-'+(t||'').replace(/[^a-z0-9]/g,'');}
function isMobile(){return window.innerWidth<640;}
function renderMd(txt){try{return marked.parse(txt||'');}catch(e){return '<pre style="white-space:pre-wrap;font-size:12px;">'+txt+'</pre>';}}
function escHtml(txt){
  return String(txt==null?'':txt)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;');
}
function arrOrEmpty(v){
  return Array.isArray(v)
    ? v.filter(function(x){
        var s=String(x||'').trim();
        return s!=='' && s!=='미확인' && s!=='없음';
      })
    : [];
}
function parseTradeJson(raw){
  if(!raw||typeof raw!=='string') return null;
  var cleaned=raw.trim();
  cleaned=cleaned.replace(/^```json\s*/i,'').replace(/^```\s*/,'').replace(/\s*```$/,'').trim();
  try{return JSON.parse(cleaned);}catch(e){}
  var start=cleaned.indexOf('{');
  var end=cleaned.lastIndexOf('}');
  if(start===-1||end===-1||end<=start) return null;
  try{return JSON.parse(cleaned.slice(start,end+1));}catch(e){}
  return null;
}
function renderTradeJsonReport(raw){
  var data=parseTradeJson(raw);
  if(!data||typeof data!=='object') return null;
  var buyItems=arrOrEmpty(data.buy_items);
  var sellItems=arrOrEmpty(data.sell_items);
  var reasons=arrOrEmpty(data.reasons);
  var scenarios=arrOrEmpty(data.trade_scenarios);
  var actions=arrOrEmpty(data.first_touch_actions);
  var considerations=arrOrEmpty(data.considerations);
  var html='';
  html+='<h1>'+escHtml(data.title||'거래 가능성 분석')+'</h1>';
  html+='<blockquote>추천 유형: '+escHtml(data.trade_label||'')+' | 점수: '+escHtml(data.score||'')+' | 연결: '+escHtml(data.connection_type||'')+'</blockquote>';
  if(buyItems.length||sellItems.length){
    html+='<h2>거래 품목 요약</h2>';
    if(buyItems.length){
      html+='<h3>구매 가능 품목</h3>';
      html+='<p>'+escHtml(buyItems.map(function(item,idx){return String.fromCharCode(9312+idx)+' '+item;}).join('  '))+'</p>';
    }
    if(sellItems.length){
      html+='<h3>판매 가능 품목</h3>';
      html+='<p>'+escHtml(sellItems.map(function(item,idx){return String.fromCharCode(9312+idx)+' '+item;}).join('  '))+'</p>';
    }
  }
  html+='<h2>핵심 추천 사유</h2>';
  html+='<ul>'+(reasons.length?reasons:['미확인']).map(function(item){return '<li>'+escHtml(item)+'</li>';}).join('')+'</ul>';
  html+='<h2>1. 어떤 거래처인가</h2>';
  html+='<h3>1-1. 거래 언어로 본 기업 포지션</h3><p>'+escHtml(data.positioning||'')+'</p>';
  html+='<h3>1-2. 거래 가능 품목 상세</h3><p>'+escHtml(data.tradable_items_detail||'')+'</p>';
  html+='<h2>2. 왜 우리 회사와 연결되는가</h2>';
  html+='<h3>2-1. 거래 연결 배경</h3><p>'+escHtml(data.connection_background||'')+'</p>';
  html+='<h3>2-2. 공급망·공정 연결성</h3><p>'+escHtml(data.supply_chain_fit||'')+'</p>';
  html+='<h3>2-3. 거래 시나리오</h3><ul>'+(scenarios.length?scenarios:['미확인']).map(function(item){return '<li>'+escHtml(item)+'</li>';}).join('')+'</ul>';
  html+='<h2>3. 거래 신뢰도 분석</h2>';
  html+='<h3>3-1. 거래 안정성</h3><p>'+escHtml(data.stability_analysis||'')+'</p>';
  html+='<h3>3-2. 재무 건전성</h3><p>'+escHtml(data.financial_analysis||'')+'</p>';
  html+='<h2>4. 실행 제안 및 감안할 사항</h2>';
  html+='<h3>4-1. 첫 거래 접점</h3><ul>'+(actions.length?actions:['미확인']).map(function(item){return '<li>'+escHtml(item)+'</li>';}).join('')+'</ul>';
  html+='<h3>4-2. 감안할 사항</h3><ul>'+(considerations.length?considerations:['미확인']).map(function(item){return '<li>'+escHtml(item)+'</li>';}).join('')+'</ul>';
  return html;
}
function renderTradeJsonFailure(raw){
  var preview=String(raw==null?'':raw).trim();
  if(preview.length>3000) preview=preview.slice(0,3000)+'\n...';
  return ''
    +'<div class="err">❌ B-현장형 응답이 JSON 스키마를 지키지 않았습니다. 아래는 원본 응답입니다.</div>'
    +'<div class="report-body">'
    +'<h2>거래 품목 요약</h2>'
    +'<h3>구매 가능 품목</h3><p>파싱 실패</p>'
    +'<h3>판매 가능 품목</h3><p>파싱 실패</p>'
    +'<h3>원본 응답</h3><pre style="white-space:pre-wrap;font-size:12px;background:#f8f9fa;border:1px solid #eee;border-radius:8px;padding:10px;">'+escHtml(preview||'(빈 응답)')+'</pre>'
    +'</div>';
}

// ── 접기/펼치기 ─────────────────────────────────────────────
function toggleParams(){
  var body=document.getElementById('paramsBody');
  var arrow=document.getElementById('paramsArrow');
  var open=body.classList.toggle('open');
  arrow.classList.toggle('open',open);
}

// ── Step 1: KED 조회 ─────────────────────────────────────────
function runTest(){
  var raw=document.getElementById('bizno').value.replace(/[^0-9]/g,'');
  if(raw.length!==10){alert('사업자번호 10자리를 입력하세요.');return;}
  var btn=document.getElementById('btnRun');
  btn.disabled=true; btn.textContent='조회 중...';
  ['step1','step2kw','stepParams','step3','step4'].forEach(function(id){document.getElementById(id).style.display='none';});
  document.getElementById('topCompany').textContent='';
  _mlBuy=[]; _mlSell=[];
  _srcKeywords=[];
  _kwData={1:[],2:[]}; _kwLoaded={1:false,2:false};
  _kwSelected={1:new Map(),2:new Map()};

  document.getElementById('step1').style.display='';
  document.getElementById('s1Status').innerHTML='<span class="spinner"></span>';
  document.getElementById('s1Content').innerHTML='';

  fetch('ked_test.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:new URLSearchParams({comnum:raw}).toString()})
  .then(function(r){if(!r.ok)throw new Error('HTTP '+r.status);return r.text();})
  .then(function(txt){
    var parts=(txt||'').trim().split('|');
    if(parts[0]!=='1000') throw new Error('조회 실패: '+(parts[1]==='02'?'기업 없음':txt.slice(0,100)));
    var info; try{info=JSON.parse(parts.slice(1).join('|'));}catch(e){throw new Error('JSON 파싱 오류');}
    var C=info.CONTENTS||{};
    var enp=C.ENPintr0000||{},cust=C.ENPcust0001||{},supp=C.ENPsupp0001||{};
    var fsdt=C.ENPfsdt0001||{},kbad=C.ENPkbad0000||{};
    var kedcd=xmlVal(enp.kedcd).replace(/\D/g,'').padStart(10,'0');
    var enp_nm=xmlVal(enp.enp_nm_trd)||xmlVal(enp.enp_nm);
    var bzc_cd=xmlVal(enp.bzc_cd),bzc_nm=xmlVal(enp.bzc_nm),enp_typ=xmlVal(enp.enp_typ);
    var sido=xmlVal(enp.loc_addra).slice(0,3),sze=xmlVal(enp.enp_sze)||'3';
    var estb_dt=xmlVal(enp.estb_dt),mpd=xmlVal(enp.mpd);
    var sales=xmlInt(fsdt.fs_val9),prev_sales=xmlInt(fsdt.fs1_val9);
    var asset=xmlInt(fsdt.fs_val3),debt=xmlInt(fsdt.fs_val6);
    var bad_cnt=xmlInt(kbad.kfb_bad_cnt);
    var custNames=[],suppNames=[];
    for(var i=1;i<=10;i++){
      var cn=xmlVal(cust['cust_nm'+i]),sn=xmlVal(supp['supp_nm'+i]);
      if(cn&&cn!=='기타') custNames.push(cn);
      if(sn&&sn!=='기타') suppNames.push(sn);
    }
    _company={
      kedcd:kedcd,enp_nm:enp_nm,bzc_cd:bzc_cd,bzc_nm:bzc_nm,
      enp_typ:enp_typ==='1'?'법인':enp_typ==='2'?'개인':'기타',
      sido:sido,sze:sze,estb_dt:estb_dt,mpd:mpd||'-',
      sales:sales,prev_sales:prev_sales,asset:asset,debt:debt,bad_cnt:bad_cnt,
      cust_names:custNames,supp_names:suppNames,coldstart:false,
      bizno:raw
    };
    document.getElementById('topCompany').textContent=enp_nm;
    document.getElementById('s1Status').innerHTML='<span style="color:#7ef7b0;font-weight:600;font-size:12px;">✓</span>';
    var enpTypLabel=enp_typ==='1'?'법인':enp_typ==='2'?'개인':enp_typ;
    document.getElementById('s1Content').innerHTML='<div class="info-grid">'+[
      ['기업명',enp_nm],['업종',bzc_cd+' '+bzc_nm],['형태',enpTypLabel],
      ['지역',sido],['규모',sze],['설립일',estb_dt],['주력상품',mpd||'-'],
      ['매출(당기)',sales?sales.toLocaleString()+'원':'-'],
      ['자산',asset?asset.toLocaleString()+'원':'-'],['부채',debt?debt.toLocaleString()+'원':'-'],
      ['불량건수',bad_cnt||'0'],
      ['매입처',custNames.length?custNames.join(', '):'-'],
      ['매출처',suppNames.length?suppNames.join(', '):'-'],
    ].map(function(it){
      return '<div class="info-item"><div class="info-label">'+it[0]+'</div><div class="info-value">'+(it[1]||'-')+'</div></div>';
    }).join('')+'</div>';

    // 키워드 로드 (두 탭 병렬)
    document.getElementById('step2kw').style.display='';
    _kwTab=1;
    document.querySelectorAll('.kw-tab').forEach(function(t,i){t.classList.toggle('active',i===0);});
    document.getElementById('kwChips').innerHTML='<p style="color:#bbb;font-size:13px;">로딩 중...</p>';
    document.getElementById('kwLoadStatus').textContent='';
    loadBzcKw(1);
    loadBzcKw(2);

    // ML 파라미터 섹션 준비
    document.getElementById('stepParams').style.display='';
    buildParamsHtml(raw);

    btn.disabled=false; btn.textContent='조회';
  })
  .catch(function(e){
    document.getElementById('s1Status').innerHTML='';
    document.getElementById('s1Content').innerHTML='<div class="err">❌ '+e.message+'</div>';
    btn.disabled=false; btn.textContent='조회';
  });
}

// ── 파라미터 HTML ──────────────────────────────────────────
function buildParamsHtml(bizno){
  if(!_company) return;
  var C=_company;
  var params={action:'recommend',kedcd:C.kedcd};
  if(bizno&&bizno!==C.kedcd) params.bizno=bizno;
  if(C.bzc_cd)     params.bzc_cd=C.bzc_cd;
  if(C.sido)       params.sido=C.sido;
  if(C.sze)        params.sze=C.sze;
  if(C.estb_dt)    params.estb_dt=C.estb_dt;
  if(C.mpd&&C.mpd!=='-') params.mpd=C.mpd;
  if(C.sales)      params.sales=C.sales;
  if(C.prev_sales) params.prev_sales=C.prev_sales;
  if(C.asset)      params.asset=C.asset;
  if(C.debt)       params.debt=C.debt;
  if(C.bad_cnt)    params.bad_cnt=C.bad_cnt;
  if(C.cust_names&&C.cust_names.length) params.cust_names=C.cust_names.join('|');
  if(C.supp_names&&C.supp_names.length) params.supp_names=C.supp_names.join('|');

  var pHtml='<div class="params-list">';
  Object.keys(params).forEach(function(k){pHtml+='<span>'+k+'</span> = '+params[k]+'<br>';});
  pHtml+='<span style="color:#198754;">filter_bzc_cds</span> = <em id="paramFilterPreview">(칩 선택 후 표시)</em><br>';
  pHtml+='</div>';
  document.getElementById('sParams').innerHTML=pHtml;
}

// ── Step 2: 키워드 로드 ─────────────────────────────────────
function loadBzcKw(tradeType){
  if(!_company||!_company.bzc_cd) return;
  document.getElementById('kwLoadStatus').textContent='로딩...';
  fetch('bzc_kw_api.php?bzc_cd='+encodeURIComponent(_company.bzc_cd)+'&trade_type='+tradeType)
  .then(function(r){return r.json();})
  .then(function(data){
    _kwLoaded[tradeType]=true;
    _kwData[tradeType]=data.ok ? (data.keywords||[]) : [];
    if(tradeType===_kwTab) renderChips();
    if(_kwLoaded[1]&&_kwLoaded[2]){
      document.getElementById('kwLoadStatus').textContent='';
    }
  })
  .catch(function(){
    _kwLoaded[tradeType]=true;
    _kwData[tradeType]=[];
    if(tradeType===_kwTab) renderChips();
  });
}

function switchKwTab(tab,el){
  if(_kwTab===tab) return;
  _kwTab=tab;
  // 탭 전환 시 새 탭의 선택 초기화 (이전 탭 선택은 유지)
  _kwSelected[tab]=new Map();
  document.querySelectorAll('.kw-tab').forEach(function(t){t.classList.remove('active');});
  el.classList.add('active');
  renderChips();
  updateSelCount();
  updateFilterPreview();
}

function parseKw(kw){
  if(!kw) return [];
  var s=String(kw).trim();
  if(s.charAt(0)==='['){
    try{
      var arr=JSON.parse(s);
      if(Array.isArray(arr)) return arr.map(function(v){return String(v).trim();}).filter(Boolean);
    }catch(e){}
  }
  return s.split(',').map(function(v){return v.trim().replace(/^["'\[\]\s]+|["'\[\]\s]+$/g,'');}).filter(Boolean);
}

function getBzcWeightPairs(tradeType){
  var pairs=[];
  _kwSelected[tradeType].forEach(function(kwSet,bzc){
    if(kwSet.size>0) pairs.push({bzc:bzc,weight:kwSet.size});
  });
  return pairs;
}

function renderChips(){
  var chips=_kwData[_kwTab]||[];
  var m=_kwSelected[_kwTab];
  if(!chips.length){
    document.getElementById('kwChips').innerHTML='<p style="color:#999;font-size:13px;">해당 업종의 거래 데이터가 없습니다. 전체 대상으로 추천합니다.</p>';
    updateSelCount();
    return;
  }
  var html='<div class="chip-wrap">';
  chips.forEach(function(c){
    var keywords=parseKw(c.kw);
    var kwSet=m.get(c.bzc)||new Set();
    var totalKws=keywords.length;
    var allSel=(kwSet.size===totalKws&&totalKws>1);
    keywords.forEach(function(kw){
      var active=kwSet.has(kw)?'selected':'';
      var badge=allSel?'<sup style="font-size:9px;opacity:.85;margin-left:2px;">×2</sup>':'';
      html+='<span class="kw-chip '+active+'" data-bzc="'+c.bzc+'" data-kw="'+encodeURIComponent(kw)+'" onclick="toggleChip(this)">'+kw+badge+'</span>';
    });
  });
  html+='</div>';
  document.getElementById('kwChips').innerHTML=html;
  updateSelCount();
}

function toggleChip(el){
  var bzc=el.dataset.bzc;
  var kw=decodeURIComponent(el.dataset.kw||'');
  var m=_kwSelected[_kwTab];
  if(!m.has(bzc)) m.set(bzc,new Set());
  var kwSet=m.get(bzc);
  if(kwSet.has(kw)){
    kwSet.delete(kw);
    if(kwSet.size===0) m.delete(bzc);
  }else{
    kwSet.add(kw);
  }
  renderChips(); // ×2 배지 업데이트를 위해 재렌더
  updateFilterPreview();
}

function selectAllChips(){
  var m=_kwSelected[_kwTab];
  (_kwData[_kwTab]||[]).forEach(function(c){
    var kws=parseKw(c.kw);
    if(!m.has(c.bzc)) m.set(c.bzc,new Set());
    kws.forEach(function(kw){m.get(c.bzc).add(kw);});
  });
  renderChips();
  updateFilterPreview();
}

function clearChips(){
  _kwSelected[_kwTab].clear();
  renderChips();
  updateFilterPreview();
}

function updateSelCount(){
  var bPairs=getBzcWeightPairs(1);
  var sPairs=getBzcWeightPairs(2);
  var bKw=0; _kwSelected[1].forEach(function(s){bKw+=s.size;});
  var sKw=0; _kwSelected[2].forEach(function(s){sKw+=s.size;});
  var parts=[];
  if(bPairs.length) parts.push('구매처 '+bPairs.length+'개 업종 (키워드 '+bKw+'개)');
  if(sPairs.length) parts.push('판매처 '+sPairs.length+'개 업종 (키워드 '+sKw+'개)');
  document.getElementById('kwSelCount').textContent=parts.length?parts.join(' / ')+' 선택됨':'(선택 없으면 전체 대상)';
}

function updateFilterPreview(){
  var el=document.getElementById('paramFilterPreview');
  if(!el) return;
  var buy=getBzcWeightPairs(1);
  var sell=getBzcWeightPairs(2);
  var txt='';
  if(buy.length) txt+='구매처:['+buy.map(function(p){return p.bzc+'(w'+p.weight+')';}).join(',')+'] ';
  if(sell.length) txt+='판매처:['+sell.map(function(p){return p.bzc+'(w'+p.weight+')';}).join(',') +']';
  el.textContent=txt||'(없음 — 전체 대상)';
}

// ── Step 3: EC2 추천 ────────────────────────────────────────
function runStep3(noFilter){
  if(!_company){alert('먼저 기업을 조회하세요.');return;}
  var btn=document.getElementById('btnRecommend');
  var btnAll=document.getElementById('btnRecommendAll');
  btn.disabled=true; btnAll.disabled=true;
  btn.textContent='추천 중...';
  document.getElementById('step3').style.display='';
  document.getElementById('step4').style.display='none';
  document.getElementById('s3Status').innerHTML='<span class="spinner"></span>';
  document.getElementById('s3Content').innerHTML='';
  _mlBuy=[]; _mlSell=[];
  // 이전 Gemini 결과 초기화 — 새 추천 실행 시 이전 보고서가 남아 자동 조회처럼 보이는 문제 방지
  ['s4buy-a','s4buy-b','s4sell-a','s4sell-b'].forEach(function(id){
    var el=document.getElementById(id); if(el) el.innerHTML='';
  });
  document.getElementById('btnBuy').disabled=false;
  document.getElementById('btnBuy').textContent='A·B 분석 ▶';
  document.getElementById('btnSell').disabled=false;
  document.getElementById('btnSell').textContent='A·B 분석 ▶';

  var C=_company;
  var base={action:'recommend',kedcd:C.kedcd};
  if(C.bizno&&C.bizno!==C.kedcd) base.bizno=C.bizno;
  if(C.bzc_cd)     base.bzc_cd=C.bzc_cd;
  if(C.sido)       base.sido=C.sido;
  if(C.sze)        base.sze=C.sze;
  if(C.estb_dt)    base.estb_dt=C.estb_dt;
  if(C.mpd&&C.mpd!=='-') base.mpd=C.mpd;
  if(C.sales)      base.sales=C.sales;
  if(C.prev_sales) base.prev_sales=C.prev_sales;
  if(C.asset)      base.asset=C.asset;
  if(C.debt)       base.debt=C.debt;
  if(C.bad_cnt)    base.bad_cnt=C.bad_cnt;
  if(C.cust_names&&C.cust_names.length) base.cust_names=C.cust_names.join('|');
  if(C.supp_names&&C.supp_names.length) base.supp_names=C.supp_names.join('|');

  var buyParams=Object.assign({},base,{trade_type:1});
  var sellParams=Object.assign({},base,{trade_type:2});
  var buyPairs=[], sellPairs=[];
  if(!noFilter){
    buyPairs=getBzcWeightPairs(1);
    sellPairs=getBzcWeightPairs(2);
    if(buyPairs.length){
      buyParams.filter_bzc_cds=buyPairs.map(function(p){return p.bzc;}).join(',');
      buyParams.filter_bzc_weights=buyPairs.map(function(p){return p.weight;}).join(',');
    }
    if(sellPairs.length){
      sellParams.filter_bzc_cds=sellPairs.map(function(p){return p.bzc;}).join(',');
      sellParams.filter_bzc_weights=sellPairs.map(function(p){return p.weight;}).join(',');
    }
  }

  // 한쪽에만 필터가 있으면 해당 타입만 호출, 나머지는 빈 결과
  var hasAnyFilter = buyPairs.length > 0 || sellPairs.length > 0;
  var doBuy  = !hasAnyFilter || buyPairs.length > 0;
  var doSell = !hasAnyFilter || sellPairs.length > 0;
  var emptyResp = {buy:{top3:[],list:[],total:0},sell:{top3:[],list:[],total:0},buy_elapsed_ms:0,sell_elapsed_ms:0,coldstart:false,src_keywords:[]};

  Promise.all([
    doBuy  ? fetch('recommend_api.php?'+buildQs(buyParams)).then(function(r){return r.json();})  : Promise.resolve(emptyResp),
    doSell ? fetch('recommend_api.php?'+buildQs(sellParams)).then(function(r){return r.json();}) : Promise.resolve(emptyResp)
  ]).then(function(results){
    var buyData=results[0], sellData=results[1];
    if(buyData.error){
      document.getElementById('s3Status').innerHTML='';
      document.getElementById('s3Content').innerHTML='<div class="err">❌ 구매처 오류: '+buyData.error+'</div>';
      btn.disabled=false; btnAll.disabled=false; btn.textContent='선택 업종 추천 →';
      return;
    }
    var buyAll=(buyData.buy&&buyData.buy.top3)?[].concat(buyData.buy.top3,buyData.buy.list||[]):[];
    var sellAll=(sellData.sell&&sellData.sell.top3)?[].concat(sellData.sell.top3,sellData.sell.list||[]):[];
    if(sellData.error) sellAll=[];
    _mlBuy=buyAll; _mlSell=sellAll;
    _srcKeywords=(buyData.src_keywords||sellData.src_keywords||[]);
    if(_company) _company.coldstart=buyData.coldstart||false;
    _buyPage=0; _sellPage=0;

    var buyTotal=buyData.buy?buyData.buy.total||0:0;
    var sellTotal=sellData.sell?sellData.sell.total||0:0;

    document.getElementById('s3Status').innerHTML=
      '<span style="color:#198754;font-weight:600;font-size:12px;">✓ 구매처 '+(buyAll.length)
      +' / 판매처 '+(sellAll.length)+'</span>'
      +(buyData.coldstart?' <span class="badge-cold">coldstart</span>':'');

    var s3html='<div class="tabs">'
      +'<div class="tab active" onclick="switchTab(this,\'tb-buy\')">📥 구매처 ('+buyTotal+')</div>'
      +'<div class="tab" onclick="switchTab(this,\'tb-sell\')">📤 판매처 ('+(sellData.error?'오류':sellTotal)+')</div>'
      +'</div>'
      +'<div id="tb-buy" class="tab-panel active"><div id="res-buy"></div></div>'
      +'<div id="tb-sell" class="tab-panel"><div id="res-sell">'+(sellData.error?'<div class="err">❌ 판매처 오류: '+sellData.error+'</div>':'')+'</div></div>'
      +'<div style="font-size:11px;color:#bbb;text-align:center;margin-top:4px;">⏱ '+
      (buyData.buy_elapsed_ms||0)+'ms / '+(sellData.sell_elapsed_ms||0)+'ms</div>';
    document.getElementById('s3Content').innerHTML=s3html;

    renderPage('buy',0);
    if(!sellData.error) renderPage('sell',0);

    // Step 4 LLM 섹션 표시
    document.getElementById('step4').style.display='';
    updateLlmTopInfo();
    btn.disabled=false; btnAll.disabled=false; btn.textContent='선택 업종 추천 →';
  })
  .catch(function(e){
    document.getElementById('s3Status').innerHTML='';
    document.getElementById('s3Content').innerHTML='<div class="err">❌ '+e.message+'</div>';
    btn.disabled=false; btnAll.disabled=false; btn.textContent='선택 업종 추천 →';
  });
}

// ── 결과 렌더 ────────────────────────────────────────────────
function renderPage(type,page){
  var all=type==='buy'?_mlBuy:_mlSell;
  var total=all.length,pages=Math.ceil(total/PER_PAGE)||1;
  page=Math.max(0,Math.min(page,pages-1));
  if(type==='buy') _buyPage=page; else _sellPage=page;
  var rows=all.slice(page*PER_PAGE,(page+1)*PER_PAGE);
  var offset=page*PER_PAGE;
  var html=isMobile()?makeCards(rows,offset):makeTable(rows,offset);
  if(total>PER_PAGE){
    html+='<div class="pagination">'
      +'<button class="pg-btn"'+(page===0?' disabled':'')+' onclick="renderPage(\''+type+'\','+(page-1)+')">← 이전</button>'
      +'<span class="pg-info">'+(page+1)+'/'+pages+' · '+total+'개</span>'
      +'<button class="pg-btn"'+(page===pages-1?' disabled':'')+' onclick="renderPage(\''+type+'\','+(page+1)+')">다음 →</button>'
      +'</div>';
  }
  var el=document.getElementById('res-'+type);
  if(el) el.innerHTML=html;
}

function makeCards(rows,offset){
  offset=offset||0;
  if(!rows||!rows.length) return '<p style="color:#888;font-size:13px;padding:12px;">결과 없음</p>';
  var html='<div class="result-list">';
  rows.forEach(function(r,i){
    var absIdx=offset+i;
    var cls=absIdx===0?'top1':absIdx<=2?'top3':'';
    var rankCls=absIdx===0?'':'r'+(absIdx+1<=3?absIdx+1:'');
    var radar='';
    if(r.radar){var rk=Object.keys(r.radar).slice(0,4);radar='<div class="rc-radar">'+rk.map(function(k){return '<b>'+k+'</b>:'+r.radar[k];}).join(' · ')+'</div>';}
    var ctBadge='<span class="'+ctClass(r.candidate_type)+'">'+(r.candidate_type||'-')+'</span>';
    html+='<div class="result-card '+cls+'">'
      +'<div class="rc-header"><span class="rc-rank '+rankCls+'">'+r.rank+'위</span>'
      +'<span class="rc-name">'+(r.enp_nm||r.kedcd)+'</span>'
      +'<span class="rc-score">'+(r.display_score!=null?r.display_score:r.score||'-')+'</span></div>'
      +'<div class="rc-meta">'+ctBadge+'</div>'+radar+'</div>';
  });
  return html+'</div>';
}

function makeTable(rows,offset){
  offset=offset||0;
  if(!rows||!rows.length) return '<p style="color:#888;font-size:13px;padding:8px;">결과 없음</p>';
  var html='<div style="overflow-x:auto;"><table><tr><th>순위</th><th>기업명</th><th>사업자번호</th><th>점수</th><th>유형</th><th>레이더</th></tr>';
  rows.forEach(function(r,i){
    var absIdx=offset+i;
    var radar='';
    if(r.radar){radar=Object.keys(r.radar).map(function(k){return '<span style="font-size:11px;color:#555;">'+k+':<b>'+r.radar[k]+'</b></span>';}).join(' ');}
    var ctBadge='<span class="'+ctClass(r.candidate_type)+'">'+(r.candidate_type||'-')+'</span>';
    html+='<tr><td>'+r.rank+'</td><td><b>'+(r.enp_nm||r.kedcd)+'</b></td>'
      +'<td style="font-size:12px;color:#888;">'+(r.bzno||'-')+'</td>'
      +'<td>'+(r.display_score!=null?r.display_score:r.score||'-')+'</td>'
      +'<td>'+ctBadge+'</td><td>'+(radar||'-')+'</td></tr>';
  });
  return html+'</table></div>';
}

function switchTab(el,id){
  document.querySelectorAll('.tab').forEach(function(t){t.classList.remove('active');});
  document.querySelectorAll('.tab-panel').forEach(function(p){p.classList.remove('active');});
  el.classList.add('active');
  document.getElementById(id).classList.add('active');
}

// ── Step 4: LLM A/B 검증 ────────────────────────────────────
function updateLlmTopInfo(){
  var b=_mlBuy[0];
  var s=_mlSell[0];
  document.getElementById('llmBuyInfo').textContent=b?(b.rank+'위 '+(b.enp_nm||b.kedcd)+' · 점수:'+(b.display_score!=null?b.display_score:b.score||'-')):'추천 결과 없음';
  document.getElementById('llmSellInfo').textContent=s?(s.rank+'위 '+(s.enp_nm||s.kedcd)+' · 점수:'+(s.display_score!=null?s.display_score:s.score||'-')):'추천 결과 없음';
}

function buildReportData(tradeType){
  var isBuy=(tradeType===1);
  var C=_company;
  return {
    buy_top1:  isBuy ? (_mlBuy[0]||null)  : null,
    sell_top1: isBuy ? null : (_mlSell[0]||null),
    trade_type: tradeType,
    source_enp_nm:    C.enp_nm,
    source_kedcd:     C.kedcd,
    source_bzc_cd:    C.bzc_cd,
    source_bzc_nm:    C.bzc_nm,
    source_sido:      C.sido,
    source_sze:       C.sze,
    source_enp_typ:   C.enp_typ,
    source_estb_dt:   C.estb_dt,
    source_mpd:       C.mpd,
    src_keywords:     _srcKeywords,
    source_sales:     C.sales,
    source_prev_sales:C.prev_sales,
    source_asset:     C.asset,
    source_debt:      C.debt,
    source_bad_cnt:   C.bad_cnt,
    source_cust_names:C.cust_names,
    source_supp_names:C.supp_names,
    source_coldstart: C.coldstart
  };
}

function callGemini(data, tier){
  return fetch('gemini_api.php',{
    method:'POST',
    headers:{'Content-Type':'application/json; charset=utf-8'},
    body:JSON.stringify({model:FIXED_MODEL, tier:tier, data:data})
  }).then(function(r){
    return r.text().then(function(txt){
      if(!txt||!txt.trim()) throw new Error('빈 응답');
      try{return JSON.parse(txt);}catch(e){throw new Error('파싱 실패: '+txt.slice(0,100));}
    });
  });
}

function renderResult(elId, res){
  var el=document.getElementById(elId);
  if(!el) return;
  if(res.error){el.innerHTML='<div class="err">❌ '+res.error+'</div>';return;}
  var ms=res.elapsed_ms,ts=ms>=1000?(ms/1000).toFixed(1)+'s':ms+'ms';
  var chips='<div style="display:flex;gap:5px;flex-wrap:wrap;margin:8px 0 10px;">'
    +'<span class="stat-chip" style="background:#eef;font-size:11px;">'+res.model_label+'</span>'
    +(res.thinking?'<span class="stat-chip" style="background:#f3e5f5;color:#7b1fa2;font-size:11px;">💭</span>':'')
    +'<span class="stat-chip" style="font-size:11px;">⏱ '+ts+'</span>'
    +'<span class="stat-chip" style="font-size:11px;">in '+res.input_tokens.toLocaleString()+'</span>'
    +'<span class="stat-chip" style="font-size:11px;">out '+res.output_tokens.toLocaleString()+'</span>'
    +(res.cost_confirmed
      ?'<span class="stat-chip" style="font-size:11px;">₩'+res.cost_krw.toLocaleString()+'</span>'
      :'')
    +'</div>';
  var bodyHtml=null;
  if(res.prompt_tier==='trade') bodyHtml=renderTradeJsonReport(res.report);
  if(res.prompt_tier==='trade'&&!bodyHtml) bodyHtml=renderTradeJsonFailure(res.report);
  if(!bodyHtml) bodyHtml=renderMd(res.report);
  el.innerHTML=chips+'<div class="report-body">'+bodyHtml+'</div>';
}

function generateReport(tradeType){
  if(!_company){alert('기업 조회 후 추천을 먼저 실행하세요.');return;}
  var isBuy=(tradeType===1);
  var top1=isBuy?_mlBuy[0]:_mlSell[0];
  if(!top1){alert((isBuy?'구매처':'판매처')+' 추천 결과가 없습니다.');return;}

  var btn=document.getElementById(isBuy?'btnBuy':'btnSell');
  var aId=isBuy?'s4buy-a':'s4sell-a';
  var bId=isBuy?'s4buy-b':'s4sell-b';
  var spinner='<div style="color:#888;font-size:12px;padding:20px 0;text-align:center;"><span class="spinner" style="width:20px;height:20px;border-width:2px;"></span><div style="margin-top:10px;">생성 중...</div></div>';

  btn.disabled=true; btn.textContent='분석 중...';
  document.getElementById(aId).innerHTML=spinner;
  document.getElementById(bId).innerHTML=spinner;

  var data=buildReportData(tradeType);

  Promise.allSettled([
    callGemini(data,'premium'),
    callGemini(data,'trade')
  ]).then(function(results){
    var resA=results[0].status==='fulfilled'?results[0].value:{error:results[0].reason.message};
    var resB=results[1].status==='fulfilled'?results[1].value:{error:results[1].reason.message};
    renderResult(aId, resA);
    renderResult(bId, resB);
    btn.disabled=false;
    btn.textContent='A·B 재분석 ▶';
  });
}

window.addEventListener('resize',function(){
  if(_mlBuy.length) renderPage('buy',_buyPage);
  if(_mlSell.length) renderPage('sell',_sellPage);
});
</script>
</body>
</html>
