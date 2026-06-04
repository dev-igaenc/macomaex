<?php
session_start();
require_once __DIR__ . '/api_config.php';  // .env → GEMINI_API_KEY 상수 로드
$_PW = MLTEST_PAGE_PASSWORD;

// ── 로그인 처리 ───────────────────────────────────────────
if (isset($_POST['pw'])) {
    if ($_POST['pw'] === $_PW) $_SESSION['mltest_auth'] = true;
    else $pwError = true;
}

// ── 로그인 화면 ───────────────────────────────────────────
if (!isset($_SESSION['mltest_auth'])) { ?>
<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8">
<title>LLM 보고서 로그인</title>
<style>
body{display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f4f6f9;font-family:'Malgun Gothic',sans-serif;}
.box{background:#fff;padding:36px 32px;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.1);width:320px;text-align:center;}
h2{font-size:17px;margin-bottom:20px;color:#333;}
input{width:100%;padding:10px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;margin-bottom:12px;box-sizing:border-box;}
button{width:100%;padding:11px;background:#452EFF;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;}
.err{color:#dc3545;font-size:13px;margin-bottom:10px;}
</style></head><body><div class="box">
<h2>🔒 LLM 보고서 접근</h2>
<?php if (isset($pwError)) echo '<div class="err">비밀번호가 틀렸습니다.</div>'; ?>
<form method="post"><input type="password" name="pw" placeholder="비밀번호" autofocus>
<button type="submit">확인</button></form>
</div></body></html>
<?php exit; }

// ── 키 상태 (서버 .env에서 로드된 상수) ──────────────────
$hasKey    = defined('GEMINI_API_KEY') && strlen(GEMINI_API_KEY) >= 22;
$maskedKey = $hasKey ? substr(GEMINI_API_KEY, 0, 4) . '...' . substr(GEMINI_API_KEY, -4) : '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Gemini 모델 비교 보고서</title>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Malgun Gothic',sans-serif;background:#f4f6f9;color:#222;padding:20px;}
h1{font-size:20px;font-weight:700;margin-bottom:16px;color:#333;}
a{color:#452EFF;text-decoration:none;}

/* ─ 카드 ─ */
.card{background:#fff;border-radius:10px;padding:18px 20px;margin-bottom:14px;box-shadow:0 1px 4px rgba(0,0,0,.08);}
.card-title{font-size:12px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;}

/* ─ 키 패널 ─ */
.key-bar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.key-status{font-size:13px;font-weight:600;color:#198754;background:#d1e7dd;padding:4px 12px;border-radius:20px;}
.key-status.missing{color:#842029;background:#f8d7da;}
.key-form{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:10px;}
.key-form input{flex:1;min-width:260px;padding:9px 13px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;font-family:monospace;}
.key-form input:focus{outline:none;border-color:#452EFF;}
.key-hint{font-size:11px;color:#888;margin-top:6px;}
.msg-ok{color:#198754;font-size:13px;font-weight:600;}
.msg-err{color:#dc3545;font-size:13px;}

/* ─ 입력/버튼 ─ */
input[type=text]{width:100%;padding:10px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:15px;outline:none;}
input[type=text]:focus{border-color:#452EFF;}
.btn{padding:10px 22px;background:#452EFF;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;}
.btn:hover{background:#3520e0;}
.btn:disabled{background:#aaa;cursor:not-allowed;}
.btn-sm{padding:6px 14px;font-size:13px;}
.btn-outline{background:#fff;color:#452EFF;border:1.5px solid #452EFF;}
.btn-outline:hover{background:#f0f0ff;}
.btn-danger{background:#dc3545;}
.btn-danger:hover{background:#b02a37;}

/* ─ 스텝 ─ */
.step-num{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#452EFF;color:#fff;border-radius:50%;font-size:12px;font-weight:700;margin-right:7px;}
.spinner{display:inline-block;width:14px;height:14px;border:2px solid #ddd;border-top-color:#452EFF;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-left:6px;}
@keyframes spin{to{transform:rotate(360deg)}}
.info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:8px;}
.info-item{background:#f8f9fa;border-radius:7px;padding:8px 12px;}
.info-label{font-size:11px;color:#888;margin-bottom:2px;}
.info-value{font-size:13px;font-weight:600;color:#222;word-break:break-all;}
.err-box{color:#dc3545;font-size:13px;background:#fff5f5;border-radius:6px;padding:10px 14px;}

/* ─ 모드 탭 ─ */
.mode-tabs{display:flex;gap:8px;margin-bottom:14px;}
.mode-tab{padding:8px 20px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid #ddd;background:#fff;color:#666;}
.mode-tab.active{background:#452EFF;color:#fff;border-color:#452EFF;}

/* ─ 모델 체크리스트 ─ */
.model-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px;margin-bottom:14px;}
.model-check{display:flex;align-items:center;gap:8px;padding:9px 14px;border-radius:8px;background:#f8f9fa;cursor:pointer;border:1.5px solid transparent;}
.model-check:hover{border-color:#ccc;}
.model-check.selected{border-color:#452EFF;background:#f0f0ff;}
.model-check input[type=checkbox]{width:16px;height:16px;accent-color:#452EFF;cursor:pointer;}
.model-name{font-size:13px;font-weight:600;}
.model-badge{font-size:10px;padding:2px 7px;border-radius:10px;font-weight:700;}
.badge-flagship{background:#e8eaf6;color:#3949ab;}
.badge-balanced{background:#e8f5e9;color:#2e7d32;}
.badge-fast{background:#fff3e0;color:#e65100;}
.badge-legacy{background:#f5f5f5;color:#616161;}
.badge-thinking{background:#f3e5f5;color:#7b1fa2;}

/* ─ 프롬프트 티어 선택 ─ */
.tier-btns{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;}
.tier-btn{padding:8px 18px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid #ddd;background:#fff;color:#666;}
.tier-btn.active{background:#0d6efd;color:#fff;border-color:#0d6efd;}

/* ─ 결과 그리드 ─ */
.result-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(480px,1fr));gap:16px;margin-top:16px;}
.result-card{background:#fff;border-radius:10px;box-shadow:0 1px 6px rgba(0,0,0,.1);overflow:hidden;}
.result-header{padding:12px 16px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px;}
.result-title{font-size:14px;font-weight:700;color:#222;}
.thinking-badge{font-size:10px;padding:2px 8px;border-radius:10px;background:#f3e5f5;color:#7b1fa2;font-weight:700;}
.tier-tag{font-size:10px;padding:2px 8px;border-radius:10px;font-weight:700;}
.tier-basic{background:#fff3cd;color:#856404;}
.tier-enhanced{background:#cff4fc;color:#055160;}
.tier-premium{background:#d1e7dd;color:#0a3622;}
.metrics-row{display:flex;gap:6px;padding:10px 16px;background:#fafafa;border-bottom:1px solid #eee;flex-wrap:wrap;}
.metric-chip{font-size:11px;padding:3px 9px;border-radius:10px;background:#f0f0f8;color:#444;font-weight:600;}
.metric-chip.time{background:#fff3cd;color:#856404;}
.metric-chip.tokens{background:#cff4fc;color:#055160;}
.metric-chip.cost{background:#d1e7dd;color:#0a3622;}
.metric-chip.thinking{background:#f3e5f5;color:#7b1fa2;}
.result-body{padding:16px;max-height:600px;overflow-y:auto;font-size:13px;line-height:1.7;}
.result-body h1{font-size:16px;margin:14px 0 8px;}
.result-body h2{font-size:14px;margin:12px 0 6px;color:#333;}
.result-body h3{font-size:13px;margin:10px 0 5px;color:#555;}
.result-body table{width:100%;border-collapse:collapse;margin:8px 0;font-size:12px;}
.result-body th{background:#f0f0f0;padding:6px 8px;text-align:left;border:1px solid #ddd;font-weight:600;}
.result-body td{padding:5px 8px;border:1px solid #ddd;vertical-align:top;}
.result-body blockquote{border-left:3px solid #452EFF;padding:6px 12px;background:#f8f8ff;margin:8px 0;color:#333;}
.result-body p{margin:6px 0;}
.result-body ul,.result-body ol{padding-left:18px;margin:4px 0;}
.result-body strong{color:#111;}
.result-loading{display:flex;align-items:center;justify-content:center;height:120px;color:#888;font-size:13px;}
.result-error{padding:16px;color:#dc3545;font-size:13px;background:#fff5f5;}
.result-footer{padding:10px 16px;border-top:1px solid #eee;display:flex;gap:8px;}

/* ─ 비교 요약 테이블 ─ */
.summary-table{width:100%;border-collapse:collapse;font-size:13px;}
.summary-table th{background:#f0f0f0;padding:8px 12px;text-align:left;border-bottom:2px solid #ddd;font-weight:600;}
.summary-table td{padding:8px 12px;border-bottom:1px solid #eee;}
.summary-table tr:hover td{background:#fafafa;}
.rank-1{color:#d4a000;font-weight:700;}
.rank-2{color:#777;}
.rank-3{color:#c07830;}

@media print{
  body{background:#fff;padding:0;}
  .no-print{display:none!important;}
  .result-body{max-height:none;}
}
@media(max-width:600px){
  .result-grid{grid-template-columns:1fr;}
  .model-grid{grid-template-columns:1fr 1fr;}
}
</style>
</head>
<body>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
  <h1>📊 Gemini 모델 비교 보고서</h1>
  <div style="display:flex;gap:8px;" class="no-print">
    <a href="test_ml.php" style="font-size:13px;color:#666;">← ML 테스트</a>
    <a href="?logout=1" style="font-size:13px;color:#dc3545;">로그아웃</a>
  </div>
</div>

<?php if (isset($_GET['logout'])) { session_destroy(); header('Location: llm_report.php'); exit; } ?>

<!-- ── API 키 상태 (서버 .env 관리) ──────────────────────── -->
<div class="card no-print">
  <div class="card-title">🔑 Gemini API 키</div>
  <div class="key-bar">
    <?php if ($hasKey): ?>
      <span class="key-status">✓ 서버 .env 설정됨: <?= htmlspecialchars($maskedKey) ?></span>
    <?php else: ?>
      <span class="key-status missing">✗ 미설정 — 서버 .env에 GEMINI_API_KEY를 추가하세요</span>
    <?php endif; ?>
    <span style="font-size:12px;color:#888;">키 변경은 서버 .env 파일에서 직접 수정</span>
  </div>
</div>

<!-- ── Step 1: 사업자번호 ─────────────────────────────── -->
<div class="card no-print">
  <div class="card-title"><span class="step-num">1</span>사업자번호 입력</div>
  <div style="display:flex;gap:10px;align-items:flex-start;flex-wrap:wrap;">
    <input type="text" id="bizno" placeholder="예: 407-88-01455" maxlength="12" style="max-width:280px;">
    <button class="btn" id="btnRun" onclick="runAll()" <?= $hasKey ? '' : 'disabled title="서버 .env에 GEMINI_API_KEY를 설정하세요"' ?>>분석 시작</button>
  </div>
</div>

<!-- ── Step 2: 기업 정보 ──────────────────────────────── -->
<div id="step2" class="card" style="display:none;">
  <div class="card-title"><span class="step-num">2</span>기업 정보 &amp; ML 추천 결과 <span id="s2Status"></span></div>
  <div id="s2Content"></div>
</div>

<!-- ── Step 3: 보고서 생성 ────────────────────────────── -->
<div id="step3" class="card no-print" style="display:none;">
  <div class="card-title"><span class="step-num">3</span>보고서 생성 옵션</div>

  <!-- 비교 모드 -->
  <div style="font-size:12px;color:#888;margin-bottom:8px;font-weight:600;">비교 모드</div>
  <div class="mode-tabs" style="margin-bottom:18px;">
    <div class="mode-tab active" id="modeModelTab" onclick="switchMode('model')">🤖 모델 비교</div>
    <div class="mode-tab" id="modePromptTab" onclick="switchMode('prompt')">✏️ 프롬프트 비교</div>
  </div>

  <!-- 모델 비교 모드 -->
  <div id="modeModelPanel">
    <div style="font-size:12px;color:#888;margin-bottom:8px;font-weight:600;">사용할 모델 선택</div>
    <div class="model-grid">
      <?php
      $models = [
          ['key'=>'gemini-3.1-pro-preview',        'label'=>'Gemini 3.1 Pro Preview',       'badge'=>'flagship', 'thinking'=>false, 'default'=>false],
          ['key'=>'gemini-3-flash-preview',        'label'=>'Gemini 3 Flash Preview',        'badge'=>'fast',     'thinking'=>false, 'default'=>false],
          ['key'=>'gemini-3.1-flash-lite-preview', 'label'=>'Gemini 3.1 Flash Lite Preview', 'badge'=>'fast',     'thinking'=>false, 'default'=>false],
          ['key'=>'gemini-2.5-pro',                'label'=>'Gemini 2.5 Pro',               'badge'=>'flagship', 'thinking'=>false, 'default'=>true],
          ['key'=>'gemini-2.5-pro-thinking',       'label'=>'Gemini 2.5 Pro',               'badge'=>'flagship', 'thinking'=>true,  'default'=>true],
          ['key'=>'gemini-2.5-flash',              'label'=>'Gemini 2.5 Flash',             'badge'=>'balanced', 'thinking'=>false, 'default'=>true],
          ['key'=>'gemini-2.5-flash-thinking',     'label'=>'Gemini 2.5 Flash',             'badge'=>'balanced', 'thinking'=>true,  'default'=>false],
      ];
      foreach ($models as $m): ?>
      <label class="model-check <?= $m['default'] ? 'selected' : '' ?>" onclick="toggleModel(this)">
        <input type="checkbox" name="models[]" value="<?= $m['key'] ?>" <?= $m['default'] ? 'checked' : '' ?>>
        <div>
          <div class="model-name"><?= $m['label'] ?></div>
          <div style="margin-top:3px;display:flex;gap:4px;flex-wrap:wrap;">
            <span class="model-badge badge-<?= $m['badge'] ?>"><?= $m['badge'] ?></span>
            <?php if ($m['thinking']): ?><span class="model-badge badge-thinking">thinking</span><?php endif; ?>
          </div>
        </div>
      </label>
      <?php endforeach; ?>
    </div>

    <div style="font-size:12px;color:#888;margin-bottom:8px;font-weight:600;">프롬프트 티어</div>
    <div class="tier-btns" id="modelModeTiers">
      <div class="tier-btn" onclick="selectTier(this,'modelMode')" data-tier="basic">Basic</div>
      <div class="tier-btn" onclick="selectTier(this,'modelMode')" data-tier="enhanced">Enhanced</div>
      <div class="tier-btn active" onclick="selectTier(this,'modelMode')" data-tier="premium">Premium</div>
    </div>
  </div>

  <!-- 프롬프트 비교 모드 -->
  <div id="modePromptPanel" style="display:none;">
    <div style="font-size:12px;color:#888;margin-bottom:8px;font-weight:600;">기준 모델 선택</div>
    <div class="tier-btns" id="promptModeModels">
      <div class="tier-btn active" onclick="selectTier(this,'promptModel')" data-tier="gemini-2.5-pro">Gemini 2.5 Pro</div>
      <div class="tier-btn" onclick="selectTier(this,'promptModel')" data-tier="gemini-2.5-flash">Gemini 2.5 Flash</div>
      <div class="tier-btn" onclick="selectTier(this,'promptModel')" data-tier="gemini-2.0-flash">Gemini 2.0 Flash</div>
    </div>
    <div style="font-size:12px;color:#888;margin-top:12px;">3단계 프롬프트 모두 실행하여 품질을 비교합니다.</div>
    <div style="margin-top:8px;font-size:12px;background:#fff3e0;padding:10px 14px;border-radius:7px;color:#e65100;">
      ⚠️ Basic = Flask 빠른 상황의 품질 기준선 / Premium = 최고 품질 목표치
    </div>
  </div>

  <!-- 거래 유형 선택 -->
  <div style="font-size:12px;color:#888;margin-bottom:8px;font-weight:600;margin-top:16px;">거래 유형</div>
  <div style="display:flex;gap:8px;margin-bottom:16px;" id="tradeTypeRow">
    <label id="tradeLbl1" style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:13px;font-weight:600;padding:7px 14px;border-radius:20px;border:1.5px solid #452EFF;background:#f0f0ff;color:#452EFF;">
      <input type="radio" name="tradeType" value="1" checked onchange="selectedTradeType=1;updateTradeLabel();" style="accent-color:#452EFF;"> 📥 구매처
    </label>
    <label id="tradeLbl2" style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:13px;font-weight:600;padding:7px 14px;border-radius:20px;border:1.5px solid #ddd;background:#fff;color:#666;">
      <input type="radio" name="tradeType" value="2" onchange="selectedTradeType=2;updateTradeLabel();" style="accent-color:#198754;"> 📤 판매처
    </label>
  </div>

  <div style="margin-top:16px;">
    <button class="btn" id="btnGenerate" onclick="generateReports()">🚀 보고서 생성</button>
    <span style="font-size:12px;color:#888;margin-left:10px;">병렬 실행 — 모델별 독립 호출</span>
  </div>
</div>

<!-- ── Step 4: 결과 ───────────────────────────────────── -->
<div id="step4" style="display:none;">

  <!-- 비교 요약 -->
  <div class="card" id="summaryCard" style="display:none;">
    <div class="card-title" style="display:flex;align-items:center;justify-content:space-between;">
      <span>📈 비교 요약</span>
      <button class="btn btn-sm btn-outline no-print" onclick="window.print()">🖨️ 인쇄 / PDF 저장</button>
    </div>
    <div style="overflow-x:auto;"><table class="summary-table" id="summaryTable">
      <thead><tr>
        <th>모델 / 프롬프트</th><th>응답시간</th><th>입력토큰</th>
        <th>출력토큰</th><th>Thinking</th><th>비용($)</th><th>글자수</th>
      </tr></thead>
      <tbody id="summaryBody"></tbody>
    </table></div>
  </div>

  <!-- 보고서 카드 그리드 -->
  <div class="result-grid" id="resultGrid"></div>
</div>

<script>
// ─ 상태 ───────────────────────────────────────────────────
var appData = null;   // { company: {...}, ml: {...} }
var currentMode = 'model';
var modelModeTier = 'premium';
var promptModeModel = 'gemini-2.5-pro';
var summaryRows = [];
var selectedTradeType = 1;

// ─ 모드 전환 ──────────────────────────────────────────────
function switchMode(m) {
    currentMode = m;
    document.getElementById('modeModelTab').classList.toggle('active', m === 'model');
    document.getElementById('modePromptTab').classList.toggle('active', m === 'prompt');
    document.getElementById('modeModelPanel').style.display = m === 'model' ? '' : 'none';
    document.getElementById('modePromptPanel').style.display = m === 'prompt' ? '' : 'none';
}

function toggleModel(el) {
    el.classList.toggle('selected', el.querySelector('input').checked);
}

function selectTier(el, group) {
    var parent = el.closest('.tier-btns') || el.parentElement;
    parent.querySelectorAll('.tier-btn').forEach(function(b){ b.classList.remove('active'); });
    el.classList.add('active');
    if (group === 'modelMode') modelModeTier = el.dataset.tier;
    if (group === 'promptModel') promptModeModel = el.dataset.tier;
}

function updateTradeLabel(){
    var s=(selectedTradeType===2);
    document.getElementById('tradeLbl1').style.cssText='display:flex;align-items:center;gap:5px;cursor:pointer;font-size:13px;font-weight:600;padding:7px 14px;border-radius:20px;border:1.5px solid '+(s?'#ddd':'#452EFF')+';background:'+(s?'#fff':'#f0f0ff')+';color:'+(s?'#666':'#452EFF')+';';
    document.getElementById('tradeLbl2').style.cssText='display:flex;align-items:center;gap:5px;cursor:pointer;font-size:13px;font-weight:600;padding:7px 14px;border-radius:20px;border:1.5px solid '+(s?'#198754':'#ddd')+';background:'+(s?'#e8f5e9':'#fff')+';color:'+(s?'#198754':'#666')+';';
}

// ─ 유틸 ───────────────────────────────────────────────────
function xmlVal(v){ if(v===null||v===undefined) return ''; if(typeof v==='object') return ''; return String(v).trim(); }
function xmlInt(v){ var s=xmlVal(v); return s?(parseInt(s,10)||0):0; }
function qs(p){ return Object.keys(p).map(function(k){ return encodeURIComponent(k)+'='+encodeURIComponent(p[k]); }).join('&'); }
function renderMd(txt) {
    try { return marked.parse(txt || ''); } catch(e) { return '<pre>'+txt+'</pre>'; }
}
function fmtMs(ms){ return ms >= 1000 ? (ms/1000).toFixed(1)+'s' : ms+'ms'; }

// ─ Step 1+2: KED + ML 조회 ───────────────────────────────
function runAll() {
    var raw = document.getElementById('bizno').value.replace(/[^0-9]/g,'');
    if (raw.length !== 10) { alert('사업자번호 10자리를 입력하세요.'); return; }
    document.getElementById('btnRun').disabled = true;
    document.getElementById('btnRun').textContent = '조회 중...';
    document.getElementById('step2').style.display = '';
    document.getElementById('step3').style.display = 'none';
    document.getElementById('step4').style.display = 'none';
    document.getElementById('s2Status').innerHTML = '<span class="spinner"></span>';
    document.getElementById('s2Content').innerHTML = '';

    fetch('ked_test.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
        body: new URLSearchParams({comnum: raw}).toString()
    })
    .then(function(r){ return r.text(); })
    .then(function(txt){
        var parts=(txt||'').trim().split('|');
        if(parts[0]!=='1000') throw new Error('KED 조회 실패: '+(parts[1]==='02'?'기업 없음':txt.slice(0,120)));
        var info; try{ info=JSON.parse(parts.slice(1).join('|')); }catch(e){ throw new Error('JSON 파싱 오류'); }

        var C=info.CONTENTS||{};
        var enp=C.ENPintr0000||{}, cust=C.ENPcust0001||{}, supp=C.ENPsupp0001||{};
        var fsdt=C.ENPfsdt0001||{}, kbad=C.ENPkbad0000||{};

        var company = {
            kedcd:    xmlVal(enp.kedcd).replace(/\D/g,'').padStart(10,'0'),
            enp_nm:   xmlVal(enp.enp_nm_trd)||xmlVal(enp.enp_nm),
            bzc_cd:   xmlVal(enp.bzc_cd),
            bzc_nm:   xmlVal(enp.bzc_nm),
            enp_typ:  xmlVal(enp.enp_typ)==='1'?'법인':xmlVal(enp.enp_typ)==='2'?'개인':'기타',
            sido:     xmlVal(enp.loc_addra).slice(0,3),
            sze:      xmlVal(enp.enp_sze)||'3',
            estb_dt:  xmlVal(enp.estb_dt),
            mpd:      xmlVal(enp.mpd)||'-',
            sales:    xmlInt(fsdt.fs_val9),
            prev_sales: xmlInt(fsdt.fs1_val9),
            asset:    xmlInt(fsdt.fs_val3),
            debt:     xmlInt(fsdt.fs_val6),
            bad_cnt:  xmlInt(kbad.kfb_bad_cnt),
            cust_names: [],
            supp_names: [],
            coldstart: false,
        };
        for(var i=1;i<=10;i++){
            var cn=xmlVal(cust['cust_nm'+i]);
            var sn=xmlVal(supp['supp_nm'+i]);
            if(cn&&cn!=='기타') company.cust_names.push(cn);
            if(sn&&sn!=='기타') company.supp_names.push(sn);
        }

        // 기업 정보 표시
        document.getElementById('s2Status').innerHTML='<span style="color:#198754;font-weight:600;">✓ '+company.enp_nm+'</span>';
        var items=[
            ['기업명',company.enp_nm],['업종코드',company.bzc_cd],['업종명',company.bzc_nm],
            ['기업형태',company.enp_typ],['지역',company.sido],['규모',company.sze],
            ['설립일',company.estb_dt],['주력상품',company.mpd],
            ['매출(당기)',company.sales?company.sales.toLocaleString()+'원':'-'],
            ['매출(전기)',company.prev_sales?company.prev_sales.toLocaleString()+'원':'-'],
            ['자산총계',company.asset?company.asset.toLocaleString()+'원':'-'],
            ['부채총계',company.debt?company.debt.toLocaleString()+'원':'-'],
            ['불량건수',company.bad_cnt||'0'],
        ];
        var html='<div class="info-grid">'+items.map(function(it){
            return '<div class="info-item"><div class="info-label">'+it[0]+'</div><div class="info-value">'+(it[1]||'-')+'</div></div>';
        }).join('')+'</div>';
        html+='<div style="margin-top:12px;font-size:12px;color:#888;font-weight:600;">ML 결과 조회 중...<span class="spinner"></span></div>';
        document.getElementById('s2Content').innerHTML=html;

        // ML 결과 조회
        var mlParams={kedcd:company.kedcd};
        if(company.bzc_cd) mlParams.bzc_cd=company.bzc_cd;
        if(company.sido)   mlParams.sido=company.sido;
        if(company.sze)    mlParams.sze=company.sze;
        if(company.estb_dt) mlParams.estb_dt=company.estb_dt;
        if(company.mpd&&company.mpd!=='-') mlParams.mpd=company.mpd;
        if(company.sales)  mlParams.sales=company.sales;
        if(company.prev_sales) mlParams.prev_sales=company.prev_sales;
        if(company.asset)  mlParams.asset=company.asset;
        if(company.debt)   mlParams.debt=company.debt;
        if(company.bad_cnt) mlParams.bad_cnt=company.bad_cnt;
        if(company.cust_names.length) mlParams.cust_names=company.cust_names.join('|');
        if(company.supp_names.length) mlParams.supp_names=company.supp_names.join('|');

        return Promise.all([
            fetch('recommend_api.php?'+qs(Object.assign({},mlParams,{trade_type:1}))).then(function(r){return r.json();}),
            fetch('recommend_api.php?'+qs(Object.assign({},mlParams,{trade_type:2}))).then(function(r){return r.json();})
        ]).then(function(results){
            var buyData=results[0], sellData=results[1];
            company.coldstart = buyData.coldstart || false;
            var mlResult = {
                buy_top3:  (buyData.buy||{}).top3||[],
                sell_top3: (sellData.sell||{}).sell ? [] : (sellData.sell||{}).top3||[],
                buy_total: (buyData.buy||{}).total||0,
                sell_total:(sellData.sell||{}).total||0,
                buy_elapsed_ms:  buyData.buy_elapsed_ms||0,
                sell_elapsed_ms: sellData.sell_elapsed_ms||0,
                src_keywords: buyData.src_keywords || sellData.src_keywords || [],
            };
            // sell 데이터 구조 수정
            if(sellData.sell && sellData.sell.top3) mlResult.sell_top3=sellData.sell.top3;

            appData = Object.assign({}, company, mlResult);

            // ML 결과 요약 추가
            var mlHtml='<div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">';
            mlHtml+='<span style="font-size:12px;font-weight:600;color:#452EFF;">ML 매입 '+mlResult.buy_total+'개</span>';
            mlHtml+='<span style="font-size:12px;font-weight:600;color:#198754;">ML 매출 '+mlResult.sell_total+'개</span>';
            if(company.coldstart) mlHtml+='<span style="font-size:11px;background:#fff3cd;color:#856404;padding:2px 8px;border-radius:10px;">coldstart</span>';
            mlHtml+='<span style="font-size:11px;color:#888;">매입Top3: '+(mlResult.buy_top3.map(function(r){return r.enp_nm;}).join(', ')||'-')+'</span>';
            mlHtml+='</div>';

            document.getElementById('s2Content').innerHTML=html+mlHtml;
            document.getElementById('step3').style.display='';
            document.getElementById('btnRun').disabled=false;
            document.getElementById('btnRun').textContent='다시 조회';
        });
    })
    .catch(function(e){
        document.getElementById('s2Status').innerHTML='';
        document.getElementById('s2Content').innerHTML='<div class="err-box">❌ '+e.message+'</div>';
        document.getElementById('btnRun').disabled=false;
        document.getElementById('btnRun').textContent='분석 시작';
    });
}

// ─ 공통 후처리 ────────────────────────────────────────────
function escapeRe(s){return s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');}

function postProcessReport(text, buyNmReal){
    // 기업명 마스킹
    if(buyNmReal){
        text=text.replace(new RegExp(escapeRe(buyNmReal),'g'),'\x00NM\x00');
    }
    // **** 보호 → ** 제거 → 복원
    text=text.replace(/\*{4}/g,'\x00ST\x00');
    text=text.replace(/\*\*/g,'');
    text=text.replace(/\x00ST\x00/g,'****');
    if(buyNmReal){
        var mask=Array.from(buyNmReal).slice(0,2).join('')+'****';
        text=text.replace(/\x00NM\x00/g,mask);
    }
    // (추론) 제거
    text=text.replace(/\s*\(추론\)/g,'');
    // 면책 문구 이탤릭 강제
    text=text.replace(/\*?(구글 검색 기반 기업 분석이며, 실거래 시 민감한 부분은 해당사를 통해 확인 바랍니다\.)\*?/g,'*$1*');
    return text;
}

// ─ Step 3: 보고서 생성 ───────────────────────────────────
function generateReports() {
    if (!appData) { alert('먼저 사업자번호를 조회하세요.'); return; }

    var jobs = [];
    if (currentMode === 'model') {
        var checked = document.querySelectorAll('input[name="models[]"]:checked');
        if (!checked.length) { alert('모델을 하나 이상 선택하세요.'); return; }
        checked.forEach(function(cb){ jobs.push({model: cb.value, tier: modelModeTier}); });
    } else {
        ['basic','enhanced','premium'].forEach(function(t){
            jobs.push({model: promptModeModel, tier: t});
        });
    }

    document.getElementById('step4').style.display='';
    document.getElementById('summaryCard').style.display='none';
    document.getElementById('summaryBody').innerHTML='';
    document.getElementById('resultGrid').innerHTML='';
    summaryRows = [];

    // 카드 초기화
    jobs.forEach(function(job){
        var cardId = 'card-' + job.model + '-' + job.tier;
        var card = document.createElement('div');
        card.className='result-card';
        card.id=cardId;
        card.innerHTML=
          '<div class="result-header">'
            +'<span class="result-title">'+getLabelForJob(job)+'</span>'
            +'<div style="display:flex;gap:4px;">'
              +(job.model.includes('thinking')?'<span class="thinking-badge">💭 Thinking</span>':'')
              +'<span class="tier-tag tier-'+job.tier+'">'+job.tier+'</span>'
            +'</div>'
          +'</div>'
          +'<div class="result-loading" id="ld-'+cardId+'"><span class="spinner" style="width:20px;height:20px;border-width:3px;margin-right:8px;"></span>생성 중...</div>'
          +'<div id="st-'+cardId+'" style="white-space:pre-wrap;font-size:12px;line-height:1.7;color:#555;padding:8px 12px;"></div>';
        document.getElementById('resultGrid').appendChild(card);
    });

    document.getElementById('step4').scrollIntoView({behavior:'smooth',block:'start'});

    // 병렬 JSON 호출
    var finished = 0;
    jobs.forEach(function(job){
        var cardId = 'card-' + job.model + '-' + job.tier;
        var payload = Object.assign({}, appData, {
            trade_type:         selectedTradeType,
            source_enp_nm:      appData.enp_nm,
            source_bzc_cd:      appData.bzc_cd,
            source_bzc_nm:      appData.bzc_nm,
            source_sido:        appData.sido,
            source_sze:         appData.sze,
            source_mpd:         appData.mpd,
            source_sales:       appData.sales,
            source_cust_names:  appData.cust_names,
            source_supp_names:  appData.supp_names,
            buy_top1:  appData.buy_top3[0]  || null,
            sell_top1: appData.sell_top3[0] || null,
        });
        fetch('gemini_api.php', {
            method:'POST',
            headers:{'Content-Type':'application/json; charset=utf-8'},
            body: JSON.stringify({model: job.model, tier: job.tier, data: payload})
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            var card = document.getElementById(cardId);
            if(res.error){
                document.getElementById('ld-'+cardId).remove();
                document.getElementById('st-'+cardId).remove();
                card.insertAdjacentHTML('beforeend','<div class="result-error">❌ '+res.error+'</div>');
                return;
            }
            var reportText = res.report || '';
            var metricsHtml=
              '<div class="metrics-row">'
              +'<span class="metric-chip time">⏱ '+fmtMs(res.elapsed_ms)+'</span>'
              +'<span class="metric-chip tokens">in '+res.input_tokens.toLocaleString()+'</span>'
              +'<span class="metric-chip tokens">out '+res.output_tokens.toLocaleString()+'</span>'
              +(res.think_tokens?'<span class="metric-chip thinking">💭 '+res.think_tokens.toLocaleString()+'</span>':'')
              +'<span class="metric-chip cost">₩'+res.cost_krw.toLocaleString()+'원</span>'
              +'<span class="metric-chip">'+(res.report_chars||reportText.length).toLocaleString()+'자</span>'
              +'</div>';
            var bodyHtml='<div class="result-body">'+renderMd(reportText)+'</div>';
            var footerHtml='<div class="result-footer no-print">'
              +'<button class="btn btn-sm btn-outline" onclick="copyReport(this, \''+cardId+'\')">📋 복사</button>'
              +'</div>';
            document.getElementById('ld-'+cardId).remove();
            document.getElementById('st-'+cardId).remove();
            card.innerHTML=
              card.querySelector('.result-header').outerHTML
              +metricsHtml+bodyHtml+footerHtml;
            summaryRows.push({
                label:         getLabelForJob(job),
                tier:          job.tier,
                elapsed_ms:    res.elapsed_ms,
                input_tokens:  res.input_tokens,
                output_tokens: res.output_tokens,
                think_tokens:  res.think_tokens,
                cost_usd:      res.cost_usd,
                cost_krw:      res.cost_krw,
                report_chars:  res.report_chars||reportText.length,
            });
        })
        .catch(function(e){
            var card = document.getElementById(cardId);
            document.getElementById('ld-'+cardId).remove();
            document.getElementById('st-'+cardId).remove();
            card.insertAdjacentHTML('beforeend','<div class="result-error">❌ 네트워크 오류: '+e.message+'</div>');
        })
        .finally(function(){ finished++; if(finished===jobs.length) renderSummary(); });
    });
}

function getLabelForJob(job) {
    var modelLabels = {
        'gemini-2.5-pro':           'Gemini 2.5 Pro',
        'gemini-2.5-pro-thinking':  'Gemini 2.5 Pro',
        'gemini-2.5-flash':         'Gemini 2.5 Flash',
        'gemini-2.5-flash-thinking':'Gemini 2.5 Flash',
        'gemini-2.0-flash':         'Gemini 2.0 Flash',
        'gemini-1.5-flash':         'Gemini 1.5 Flash',
    };
    return (modelLabels[job.model]||job.model);
}

function renderSummary() {
    if (!summaryRows.length) return;
    document.getElementById('summaryCard').style.display='';
    // 응답시간 기준 정렬
    var sorted = summaryRows.slice().sort(function(a,b){ return a.elapsed_ms-b.elapsed_ms; });
    var html='';
    sorted.forEach(function(r, i){
        var rankCls = i===0?'rank-1':i===1?'rank-2':i===2?'rank-3':'';
        html+='<tr>'
          +'<td class="'+rankCls+'"><b>'+r.label+'</b><span class="tier-tag tier-'+r.tier+'" style="margin-left:6px;">'+r.tier+'</span></td>'
          +'<td class="'+rankCls+'">'+fmtMs(r.elapsed_ms)+'</td>'
          +'<td>'+r.input_tokens.toLocaleString()+'</td>'
          +'<td>'+r.output_tokens.toLocaleString()+'</td>'
          +'<td>'+(r.think_tokens?r.think_tokens.toLocaleString():'-')+'</td>'
          +'<td>₩'+r.cost_krw.toLocaleString()+'원</td>'
          +'<td>'+r.report_chars.toLocaleString()+'자</td>'
          +'</tr>';
    });
    document.getElementById('summaryBody').innerHTML=html;
}

function copyReport(btn, cardId) {
    var body = document.querySelector('#'+cardId+' .result-body');
    if (!body) return;
    navigator.clipboard.writeText(body.innerText).then(function(){
        btn.textContent='✓ 복사됨';
        setTimeout(function(){ btn.textContent='📋 복사'; }, 2000);
    });
}
</script>
</body>
</html>
