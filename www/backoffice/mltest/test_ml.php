<?php
session_start();

if (!function_exists('mltest_load_env_file')) {
    function mltest_load_env_file($env_path)
    {
        static $loaded = [];
        if (isset($loaded[$env_path])) {
            return;
        }
        $loaded[$env_path] = true;

        if (!is_file($env_path) || !is_readable($env_path)) {
            return;
        }

        $scanner = defined('INI_SCANNER_RAW') ? INI_SCANNER_RAW : INI_SCANNER_NORMAL;
        $values = @parse_ini_file($env_path, false, $scanner);
        if (!is_array($values)) {
            return;
        }

        foreach ($values as $name => $value) {
            if (getenv($name) !== false) {
                continue;
            }
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            @putenv($name . '=' . $value);
        }
    }
}

if (!function_exists('mltest_env')) {
    function mltest_env($name, $default = '')
    {
        $value = getenv($name);
        if ($value !== false && $value !== '') {
            return $value;
        }
        if (isset($_ENV[$name]) && $_ENV[$name] !== '') {
            return $_ENV[$name];
        }
        if (isset($_SERVER[$name]) && $_SERVER[$name] !== '') {
            return $_SERVER[$name];
        }
        return $default;
    }
}

mltest_load_env_file(dirname(dirname(dirname(__DIR__))) . '/.env');
$_PW = (string)mltest_env('MLTEST_PAGE_PASSWORD', '');

if ($_PW === '') {
    http_response_code(500);
    echo 'MLTEST_PAGE_PASSWORD is not configured.';
    exit;
}

if (isset($_POST['pw'])) {
    if ($_POST['pw'] === $_PW) $_SESSION['mltest_auth'] = true;
    else $pwError = true;
}
if (!isset($_SESSION['mltest_auth'])) { ?>
<!DOCTYPE html><html lang="ko"><head><meta charset="UTF-8">
<title>ML 테스트 로그인</title>
<style>body{display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f4f6f9;font-family:'Malgun Gothic',sans-serif;}
.box{background:#fff;padding:36px 32px;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.1);width:320px;text-align:center;}
h2{font-size:17px;margin-bottom:20px;color:#333;}
input{width:100%;padding:10px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:14px;margin-bottom:12px;box-sizing:border-box;}
button{width:100%;padding:11px;background:#452EFF;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;}
.err{color:#dc3545;font-size:13px;margin-bottom:10px;}</style>
</head><body><div class="box">
<h2>🔒 ML 테스트 접근</h2>
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ML 추천 테스트</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Malgun Gothic',sans-serif;background:#f4f6f9;color:#222;padding:24px;}
h1{font-size:20px;font-weight:700;margin-bottom:20px;color:#333;}
.card{background:#fff;border-radius:10px;padding:20px;margin-bottom:16px;box-shadow:0 1px 4px rgba(0,0,0,.08);}
.card-title{font-size:13px;font-weight:700;color:#666;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;}
input[type=text]{width:100%;padding:10px 14px;border:1.5px solid #ddd;border-radius:8px;font-size:15px;outline:none;}
input[type=text]:focus{border-color:#452EFF;}
button{padding:10px 24px;background:#452EFF;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;margin-top:10px;}
button:hover{background:#3520e0;}
button:disabled{background:#aaa;cursor:not-allowed;}
table{width:100%;border-collapse:collapse;font-size:13px;}
th{background:#f0f0f0;padding:7px 10px;text-align:left;font-weight:600;border-bottom:2px solid #ddd;}
td{padding:7px 10px;border-bottom:1px solid #eee;vertical-align:top;}
tr:hover td{background:#fafafa;}
.badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:600;}
.badge-cold{background:#fff3cd;color:#856404;}
.badge-top3{background:#cfe2ff;color:#084298;}
.tabs{display:flex;gap:8px;margin-bottom:12px;}
.tab{padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid #ddd;background:#fff;}
.tab.active{background:#452EFF;color:#fff;border-color:#452EFF;}
.tab-panel{display:none;}
.tab-panel.active{display:block;}
.info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;}
.info-item{background:#f8f9fa;border-radius:7px;padding:8px 12px;}
.info-label{font-size:11px;color:#888;margin-bottom:2px;}
.info-value{font-size:13px;font-weight:600;color:#222;word-break:break-all;}
.radar-mini{font-size:11px;color:#555;}
.elapsed{font-size:12px;color:#888;margin-top:8px;}
.err{color:#dc3545;font-size:13px;padding:8px;background:#fff5f5;border-radius:6px;}
.step-num{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#452EFF;color:#fff;border-radius:50%;font-size:12px;font-weight:700;margin-right:8px;}
.spinner{display:inline-block;width:16px;height:16px;border:2px solid #ddd;border-top-color:#452EFF;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-left:8px;}
@keyframes spin{to{transform:rotate(360deg)}}
.params-list{font-size:12px;line-height:1.8;font-family:monospace;background:#f8f9fa;padding:10px 14px;border-radius:7px;}
.params-list span{color:#452EFF;font-weight:600;}
.stat-chip{font-size:12px;padding:3px 10px;border-radius:12px;background:#f0f0f8;color:#444;font-weight:600;}
.ct-bar{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:8px;}
.ct-chip{font-size:11px;padding:2px 7px;border-radius:10px;font-weight:600;}
.ct-chain2{background:#e8eaf6;color:#3949ab;}
.ct-chain3{background:#f3e5f5;color:#7b1fa2;}
.ct-partner2{background:#e8f5e9;color:#2e7d32;}
.ct-partner3{background:#f1f8e9;color:#558b2f;}
.ct-industry{background:#fff3e0;color:#e65100;}
.ct-neighbor{background:#e3f2fd;color:#1565c0;}
</style>
</head>
<body>
<h1>🧪 ML 추천 테스트</h1>

<div class="card">
  <div class="card-title">사업자번호 입력</div>
  <input type="text" id="bizno" placeholder="예: 407-88-01455" maxlength="12">
  <br>
  <button id="btnRun" onclick="runTest()">테스트 시작</button>
</div>

<div id="step1" class="card" style="display:none">
  <div class="card-title"><span class="step-num">1</span>KED API 기업 정보 <span id="s1Status"></span></div>
  <div id="s1Content"></div>
</div>

<div id="step2" class="card" style="display:none">
  <div class="card-title"><span class="step-num">2</span>EC2 ML 전송 파라미터 <span id="s2Status"></span></div>
  <div id="s2Params"></div>
</div>

<div id="step3" class="card" style="display:none">
  <div class="card-title"><span class="step-num">3</span>추천 결과 비교 <span id="s3Status"></span></div>
  <div id="s3Content"></div>
</div>

<script>
function xmlVal(v){if(v===null||v===undefined)return '';if(typeof v==='object')return '';return String(v).trim();}
function xmlInt(v){var s=xmlVal(v);return s?(parseInt(s,10)||0):0;}

function runTest() {
  var raw = document.getElementById('bizno').value.replace(/[^0-9]/g,'');
  if (raw.length !== 10) { alert('사업자번호 10자리를 입력하세요.'); return; }
  var btn = document.getElementById('btnRun');
  btn.disabled = true; btn.textContent = '조회 중...';
  ['step1','step2','step3'].forEach(function(id){ document.getElementById(id).style.display='none'; });

  document.getElementById('step1').style.display='';
  document.getElementById('s1Status').innerHTML='<span class="spinner"></span>';
  document.getElementById('s1Content').innerHTML='';

  fetch('ked_test.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
    body: new URLSearchParams({comnum:raw}).toString()
  })
  .then(function(r){ if(!r.ok) throw new Error('get_set.php HTTP '+r.status); return r.text(); })
  .then(function(txt){
    var parts=(txt||'').trim().split('|');
    if(parts[0]!=='1000') throw new Error('조회 실패: '+(parts[1]==='02'?'기업 없음':txt.slice(0,100)));
    var info; try{ info=JSON.parse(parts.slice(1).join('|')); }catch(e){ throw new Error('JSON 파싱 오류'); }

    var C=info.CONTENTS||{};
    var enp=C.ENPintr0000||{}, cust=C.ENPcust0001||{}, supp=C.ENPsupp0001||{};
    var fsdt=C.ENPfsdt0001||{}, kbad=C.ENPkbad0000||{};

    var kedcd=xmlVal(enp.kedcd).replace(/\D/g,'').padStart(10,'0');
    var enp_nm=xmlVal(enp.enp_nm_trd)||xmlVal(enp.enp_nm);
    var bzc_cd=xmlVal(enp.bzc_cd), bzc_nm=xmlVal(enp.bzc_nm), enp_typ=xmlVal(enp.enp_typ);
    var sido=xmlVal(enp.loc_addra).slice(0,3), sze=xmlVal(enp.enp_sze)||'3';
    var estb_dt=xmlVal(enp.estb_dt), mpd=xmlVal(enp.mpd);
    var sales=xmlInt(fsdt.fs_val9), prev_sales=xmlInt(fsdt.fs1_val9);
    var asset=xmlInt(fsdt.fs_val3), debt=xmlInt(fsdt.fs_val6);
    var bad_cnt=xmlInt(kbad.kfb_bad_cnt);
    var custNames=[],suppNames=[];
    for(var i=1;i<=10;i++){
      var cn=xmlVal(cust['cust_nm'+i]), sn=xmlVal(supp['supp_nm'+i]);
      if(cn&&cn!=='기타') custNames.push(cn);
      if(sn&&sn!=='기타') suppNames.push(sn);
    }

    document.getElementById('s1Status').innerHTML='<span style="color:#198754;font-weight:600;">✓ '+enp_nm+'</span>';
    var items=[
      ['KEDCD',kedcd],['기업명',enp_nm],['업종코드',bzc_cd],['업종명',bzc_nm],
      ['기업형태',enp_typ==='1'?'법인':enp_typ==='2'?'개인':enp_typ],
      ['지역',sido],['규모(sze)',sze],['설립일',estb_dt],['주력상품',mpd||'-'],
      ['매출(당기)',sales?sales.toLocaleString()+'원':'-'],
      ['매출(전기)',prev_sales?prev_sales.toLocaleString()+'원':'-'],
      ['자산총계',asset?asset.toLocaleString()+'원':'-'],
      ['부채총계',debt?debt.toLocaleString()+'원':'-'],
      ['불량건수',bad_cnt||'0'],
      ['매입거래처',custNames.length?custNames.join(', '):'-'],
      ['매출거래처',suppNames.length?suppNames.join(', '):'-'],
    ];
    document.getElementById('s1Content').innerHTML='<div class="info-grid">'+items.map(function(it){
      return '<div class="info-item"><div class="info-label">'+it[0]+'</div><div class="info-value">'+(it[1]||'-')+'</div></div>';
    }).join('')+'</div>';

    runStep2(kedcd,bzc_cd,sido,sze,estb_dt,mpd,sales,prev_sales,asset,debt,bad_cnt,custNames,suppNames);
  })
  .catch(function(e){
    document.getElementById('s1Status').innerHTML='';
    document.getElementById('s1Content').innerHTML='<div class="err">❌ '+e.message+'</div>';
    document.getElementById('btnRun').disabled=false;
    document.getElementById('btnRun').textContent='테스트 시작';
  });
}

function buildQs(p){ return Object.keys(p).map(function(k){ return encodeURIComponent(k)+'='+encodeURIComponent(p[k]); }).join('&'); }

function ctClass(t){ return 'ct-chip ct-'+(t||'').replace(/[^a-z0-9]/g,''); }

function makeSummary(data, label, cls) {
  var buy=data.buy||{}, sell=data.sell||{};
  var allResults=[].concat(buy.top3||[],buy.list||[],sell.top3||[],sell.list||[]);
  var ctCount={};
  allResults.forEach(function(r){ var t=r.candidate_type||'?'; ctCount[t]=(ctCount[t]||0)+1; });
  var chips=Object.keys(ctCount).map(function(t){
    return '<span class="ct-chip '+ctClass(t)+'">'+t+' × '+ctCount[t]+'</span>';
  }).join(' ');
  var coldBadge=data.coldstart?'<span class="badge badge-cold">coldstart</span>':'<span class="stat-chip">DB 기업</span>';
  return '<div class="cmp-title '+cls+'">'+label+'</div>'
    +'<div class="cmp-stat">'
    +'<span class="stat-chip">매입 후보 '+(buy.total||0)+'개</span>'
    +'<span class="stat-chip">매출 후보 '+(sell.total||0)+'개</span>'
    +coldBadge
    +'</div>'
    +'<div style="font-size:11px;color:#666;margin-bottom:6px;">candidate_type 분포</div>'
    +'<div class="ct-bar">'+chips+'</div>'
    +'<div class="elapsed">⏱ 매입 '+(data.buy_elapsed_ms||0)+'ms / 매출 '+(data.sell_elapsed_ms||0)+'ms</div>';
}

function makeTable(rows, prefix){
  if(!rows.length) return '<p style="color:#888;font-size:13px;padding:8px;">결과 없음</p>';
  var html='<table><tr><th>순위</th><th>기업명</th><th>점수</th><th>유형</th><th>레이더</th></tr>';
  rows.slice(0,45).forEach(function(r,i){
    var radar='';
    if(r.radar){ radar=Object.keys(r.radar).map(function(k){ return '<span class="radar-mini">'+k+':<b>'+r.radar[k]+'</b></span>'; }).join(' '); }
    var ctBadge=i<3?'<span class="badge badge-top3">TOP3</span>':'<span class="ct-chip '+ctClass(r.candidate_type)+'">'+(r.candidate_type||'-')+'</span>';
    html+='<tr><td>'+r.rank+'</td><td><b>'+(r.enp_nm||r.kedcd)+'</b></td>'
      +'<td>'+(r.display_score!=null?r.display_score:r.score||'-')+'</td>'
      +'<td>'+ctBadge+'</td><td>'+(radar||'-')+'</td></tr>';
  });
  return html+'</table>';
}

function runStep2(kedcd,bzc_cd,sido,sze,estb_dt,mpd,sales,prev_sales,asset,debt,bad_cnt,custNames,suppNames){
  document.getElementById('step2').style.display='';
  document.getElementById('step3').style.display='';
  document.getElementById('s3Status').innerHTML='<span class="spinner"></span>';
  document.getElementById('s3Content').innerHTML='';

  var params={action:'recommend',kedcd:kedcd};
  if(bzc_cd)     params.bzc_cd     = bzc_cd;
  if(sido)       params.sido       = sido;
  if(sze)        params.sze        = sze;
  if(estb_dt)    params.estb_dt    = estb_dt;
  if(mpd)        params.mpd        = mpd;
  if(sales)      params.sales      = sales;
  if(prev_sales) params.prev_sales = prev_sales;
  if(asset)      params.asset      = asset;
  if(debt)       params.debt       = debt;
  if(bad_cnt)    params.bad_cnt    = bad_cnt;
  if(custNames.length) params.cust_names=custNames.join('|');
  if(suppNames.length) params.supp_names=suppNames.join('|');

  var badPatterns=['신용카드','현금영수증','수취분','발행분','간이과세','면세사업','소계','합계','직접수출','기타','불명','해당없음','개인','자가공급','내수','수출'];
  function isRealCompany(n){ return badPatterns.every(function(p){ return n.indexOf(p)===-1; }); }
  var removedCust=custNames.filter(function(n){return !isRealCompany(n);}),
      removedSupp=suppNames.filter(function(n){return !isRealCompany(n);});

  var pHtml='<div class="params-list">';
  Object.keys(params).forEach(function(k){ pHtml+='<span>'+k+'</span> = '+params[k]+'<br>'; });
  pHtml+='</div>';
  if(removedCust.length||removedSupp.length){
    pHtml+='<div style="margin-top:8px;font-size:12px;background:#fff3cd;padding:8px 12px;border-radius:6px;color:#856404;">'
      +'⚠️ 비기업 이름 필터링됨<br>';
    if(removedCust.length) pHtml+='매입: <b>'+removedCust.join(', ')+'</b> 제거<br>';
    if(removedSupp.length) pHtml+='매출: <b>'+removedSupp.join(', ')+'</b> 제거';
    pHtml+='</div>';
  }
  document.getElementById('s2Params').innerHTML=pHtml;

  var buyParams=Object.assign({},params,{trade_type:1});
  var sellParams=Object.assign({},params,{trade_type:2});

  // 공통 결과 저장소
  var results={buy:null,sell:null,coldstart:false};

  function renderResults(){
    var buy=results.buy||{}, sell=results.sell||{};
    var buyAll=[].concat(buy.top3||[],buy.list||[]);
    var sellAll=[].concat(sell.top3||[],sell.list||[]);
    var sellLabel=results.sell?'📤 매출 ('+(sell.total||0)+')':'📤 매출 <span class="spinner"></span>';

    document.getElementById('s3Status').innerHTML=
      '<span style="color:#198754;font-weight:600;">✓ 매입 '+(buy.total||0)+'개'
      +(results.sell?' / 매출 '+(sell.total||0)+'개':'')
      +(results.coldstart?' <span class="badge badge-cold">coldstart</span>':'<span class="stat-chip" style="margin-left:6px;">DB 기업</span>')+'</span>';

    var html='<div class="tabs">'
      +'<div class="tab active" onclick="switchTab(this,\'tab-buy\')">📥 매입 ('+(buy.total||0)+')</div>'
      +'<div class="tab" onclick="switchTab(this,\'tab-sell\')">'+sellLabel+'</div>'
      +'</div>'
      +'<div id="tab-buy" class="tab-panel active">'+makeTable(buyAll)+'</div>'
      +'<div id="tab-sell" class="tab-panel">'+(results.sell?makeTable(sellAll):'<p style="color:#888;font-size:13px;padding:8px;">매출 데이터 로딩 중...</p>')+'</div>'
      +'<div class="elapsed">⏱ 매입 '+(results.buy_elapsed_ms||0)+'ms'+(results.sell?' / 매출 '+(results.sell_elapsed_ms||0)+'ms':'')+'</div>';

    document.getElementById('s3Content').innerHTML=html;
  }

  // Step 1: 매입(buy) 먼저 로드
  fetch('recommend_api.php?'+buildQs(buyParams))
  .then(function(r){ return r.json(); })
  .then(function(data){
    if(data.error){
      var diag=data.diag?'<br><pre style="font-size:11px;">'+JSON.stringify(data.diag,null,2)+'</pre>':'';
      document.getElementById('s3Status').innerHTML='';
      document.getElementById('s3Content').innerHTML='<div class="err">❌ '+data.error+diag+'</div>';
      document.getElementById('btnRun').disabled=false; document.getElementById('btnRun').textContent='테스트 시작';
      return;
    }
    results.buy=data.buy||{};
    results.coldstart=data.coldstart||false;
    results.buy_elapsed_ms=data.buy_elapsed_ms||0;
    renderResults();

    // Step 2: 매출(sell) 로드
    return fetch('recommend_api.php?'+buildQs(sellParams));
  })
  .then(function(r){ return r && r.json(); })
  .then(function(data){
    if(!data) return;
    if(data.error) {
      document.getElementById('tab-sell').innerHTML='<div class="err">❌ 매출 로드 실패: '+data.error+'</div>';
      return;
    }
    results.sell=data.sell||{};
    results.sell_elapsed_ms=data.sell_elapsed_ms||0;
    renderResults();
    document.getElementById('btnRun').disabled=false;
    document.getElementById('btnRun').textContent='테스트 시작';
  })
  .catch(function(e){
    document.getElementById('s3Status').innerHTML='';
    document.getElementById('s3Content').innerHTML='<div class="err">❌ '+e.message+'</div>';
    document.getElementById('btnRun').disabled=false;
    document.getElementById('btnRun').textContent='테스트 시작';
  });
}

function switchTab(el,id){
  document.querySelectorAll('.tab').forEach(function(t){t.classList.remove('active');});
  document.querySelectorAll('.tab-panel').forEach(function(p){p.classList.remove('active');});
  el.classList.add('active');
  document.getElementById(id).classList.add('active');
}
function switchTab2(el,showId,hideId){
  var parent=el.closest('.cmp-col');
  parent.querySelectorAll('.tab').forEach(function(t){t.classList.remove('active');});
  el.classList.add('active');
  document.getElementById(showId).className='tab-panel active';
  document.getElementById(hideId).className='tab-panel';
}
</script>
</body>
</html>
