<?php
include_once(__DIR__ . '/_common.php');

$content_files = array(
    __DIR__ . '/content.html',
    __DIR__ . '/index.html',
);

foreach ($content_files as $content_file) {
    if (is_file($content_file)) {
        readfile($content_file);
        exit;
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>CB2 페이지</title>
    <meta name="robots" content="noindex,nofollow">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #fff; color: #222; }
        .cb2-content { max-width: 960px; margin: 60px auto; padding: 0 20px; }
    </style>
</head>
<body>
    <section class="cb2-content">
        <!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title>벤처기업 CB 투자 혜택 모바일 랜딩페이지</title>
  <style>
    :root{
      --bg1:#eef4ff;
      --bg2:#f8fbff;
      --glass:rgba(255,255,255,.78);
      --glass-strong:rgba(255,255,255,.94);
      --line:rgba(255,255,255,.80);
      --text:#101828;
      --sub:#667085;
      --navy:#102a56;
      --blue:#2f6fde;
      --blue2:#73b2ff;
      --green:#0f8c67;
      --green-soft:#eefbf6;
      --red:#b42318;
      --red-deep:#7a0f0f;
      --shadow:0 20px 50px rgba(17,24,39,.10);
      --shadow-soft:0 8px 24px rgba(17,24,39,.06);
      --radius-xl:30px;
      --radius-lg:24px;
      --radius-md:18px;
    }

    *{box-sizing:border-box}

    html,body{
      margin:0;
      padding:0;
      min-height:100%;
      font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","SF Pro Text","Apple SD Gothic Neo","Malgun Gothic","Segoe UI",sans-serif;
      color:var(--text);
      background:
        radial-gradient(circle at top left, #dbeaff 0%, transparent 35%),
        radial-gradient(circle at top right, #e8deff 0%, transparent 28%),
        linear-gradient(180deg, var(--bg1) 0%, var(--bg2) 100%);
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      -webkit-text-size-adjust:100%;
    }

    body{
      padding:env(safe-area-inset-top) 0 env(safe-area-inset-bottom);
    }

    .app{
      max-width:520px;
      margin:0 auto;
      min-height:100dvh;
      padding:14px 14px 32px;
      display:flex;
      flex-direction:column;
    }

    .topbar{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-bottom:12px;
      padding:4px 2px;
    }

    .logo{
      width:164px;
      height:auto;
      display:block;
      filter:drop-shadow(0 6px 12px rgba(0,0,0,.08));
    }

    .progress{
      display:flex;
      align-items:center;
      gap:6px;
      flex-shrink:0;
    }

    .dot{
      width:8px;
      height:8px;
      border-radius:999px;
      background:rgba(20,54,111,.16);
      transition:.25s ease;
    }

    .dot.active{
      width:26px;
      background:linear-gradient(90deg,#1f4b99,#2f6fde);
    }

    .screen{
      display:none;
      flex:1;
      animation:fadeUp .35s ease;
    }

    .screen.active{
      display:flex;
      flex-direction:column;
    }

    @keyframes fadeUp{
      from{opacity:0; transform:translateY(12px)}
      to{opacity:1; transform:translateY(0)}
    }

    .hero{
      position:relative;
      overflow:hidden;
      border-radius:32px;
      padding:22px 18px 20px;
      color:#fff;
      background:
        radial-gradient(circle at 20% 20%, rgba(255,255,255,.22), transparent 24%),
        radial-gradient(circle at 80% 10%, rgba(255,255,255,.15), transparent 20%),
        linear-gradient(135deg,#102a56 0%, #1f4b99 42%, #2f6fde 76%, #73b2ff 100%);
      box-shadow:0 24px 54px rgba(23,58,122,.28);
      margin-bottom:14px;
    }

    .hero::after{
      content:"";
      position:absolute;
      right:-40px;
      bottom:-46px;
      width:160px;
      height:160px;
      border-radius:50%;
      background:rgba(255,255,255,.10);
      filter:blur(6px);
    }

    .pill{
      position:relative;
      z-index:1;
      display:inline-flex;
      padding:7px 11px;
      border-radius:999px;
      font-size:11px;
      font-weight:800;
      letter-spacing:.04em;
      background:rgba(255,255,255,.15);
      border:1px solid rgba(255,255,255,.18);
      margin-bottom:12px;
    }

    .hero-title{
      position:relative;
      z-index:1;
      margin:0 0 10px;
      font-size:30px;
      line-height:1.16;
      letter-spacing:-0.04em;
      font-weight:900;
      word-break:keep-all;
    }

    .hero-desc{
      position:relative;
      z-index:1;
      margin:0;
      font-size:13px;
      line-height:1.72;
      color:rgba(255,255,255,.93);
      word-break:keep-all;
    }

    .card{
      background:var(--glass);
      border:1px solid var(--line);
      backdrop-filter:blur(18px) saturate(1.15);
      -webkit-backdrop-filter:blur(18px) saturate(1.15);
      box-shadow:var(--shadow);
      border-radius:var(--radius-xl);
      padding:18px 16px;
    }

    .title{
      margin:0 0 6px;
      font-size:22px;
      line-height:1.25;
      letter-spacing:-0.03em;
      font-weight:900;
      color:#15366f;
      word-break:keep-all;
    }

    .desc{
      margin:0 0 16px;
      font-size:13px;
      color:var(--sub);
      line-height:1.7;
      word-break:keep-all;
    }

    .summary-chip{
      display:inline-flex;
      align-items:center;
      gap:6px;
      min-height:34px;
      padding:8px 12px;
      border-radius:999px;
      background:rgba(255,255,255,.82);
      box-shadow:var(--shadow-soft);
      font-size:12px;
      font-weight:800;
      color:#214991;
      margin-bottom:14px;
    }

    .label{
      display:block;
      margin-bottom:10px;
      font-size:14px;
      font-weight:800;
      color:#243041;
      letter-spacing:-0.01em;
    }

    .input{
      width:100%;
      height:60px;
      border:none;
      outline:none;
      border-radius:20px;
      padding:0 18px;
      background:var(--glass-strong);
      color:#0f172a;
      font-size:22px;
      font-weight:900;
      letter-spacing:-0.03em;
      box-shadow:
        inset 0 0 0 1px rgba(152,162,179,.18),
        0 8px 18px rgba(17,24,39,.05);
      transition:.2s ease;
    }

    .input:focus{
      box-shadow:
        inset 0 0 0 1.5px rgba(47,111,222,.34),
        0 0 0 5px rgba(47,111,222,.10);
      background:#fff;
    }

    .hint{
      margin-top:8px;
      font-size:12px;
      color:var(--sub);
      line-height:1.6;
      word-break:keep-all;
    }

    .chips{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-top:12px;
    }

    .chip{
      border:none;
      min-height:40px;
      padding:10px 13px;
      border-radius:999px;
      background:#fff;
      color:#234a90;
      font-size:12px;
      font-weight:900;
      box-shadow:var(--shadow-soft);
      cursor:pointer;
    }

    .spacer{
      flex:1;
      min-height:16px;
    }

    .action-row{
      display:flex;
      gap:10px;
      margin-top:18px;
    }

    .btn{
      flex:1;
      height:52px;
      border:none;
      border-radius:18px;
      font-size:15px;
      font-weight:900;
      letter-spacing:-0.02em;
      cursor:pointer;
      transition:transform .08s ease, opacity .2s ease;
    }

    .btn:active{
      transform:scale(.985);
    }

    .btn-primary{
      color:#fff;
      background:linear-gradient(135deg,#1f4b99 0%, #2f6fde 60%, #5ba0ff 100%);
      box-shadow:0 16px 26px rgba(47,111,222,.25);
    }

    .btn-secondary{
      color:#214991;
      background:rgba(255,255,255,.84);
      box-shadow:var(--shadow-soft);
    }

    .btn:disabled{
      opacity:.45;
      cursor:not-allowed;
      transform:none;
    }

    .result-stack{
      display:flex;
      flex-direction:column;
      gap:12px;
      margin-top:10px;
    }

    .result-hero-total{
      position:relative;
      overflow:hidden;
      border-radius:26px;
      padding:20px 18px 18px;
      background:
        radial-gradient(circle at 15% 20%, rgba(255,255,255,.18), transparent 26%),
        radial-gradient(circle at 85% 15%, rgba(255,255,255,.10), transparent 22%),
        linear-gradient(135deg,#ff5a4f 0%, #d92d20 45%, #b42318 72%, #7a0f0f 100%);
      color:#fff;
      box-shadow:
        0 18px 38px rgba(180,35,24,.28),
        inset 0 1px 0 rgba(255,255,255,.18);
      border:1px solid rgba(255,255,255,.14);
    }

    .result-hero-total::after{
      content:"";
      position:absolute;
      right:-22px;
      top:-18px;
      width:96px;
      height:96px;
      border-radius:50%;
      background:rgba(255,255,255,.10);
      filter:blur(2px);
    }

    .result-hero-label{
      position:relative;
      z-index:1;
      display:inline-flex;
      align-items:center;
      gap:6px;
      min-height:28px;
      padding:6px 10px;
      border-radius:999px;
      background:rgba(255,255,255,.14);
      border:1px solid rgba(255,255,255,.12);
      font-size:11px;
      font-weight:800;
      letter-spacing:.02em;
      color:rgba(255,255,255,.94);
      margin-bottom:10px;
    }

    .result-hero-title{
      position:relative;
      z-index:1;
      margin:0 0 6px;
      font-size:14px;
      font-weight:800;
      color:rgba(255,255,255,.86);
      letter-spacing:-0.01em;
      word-break:keep-all;
    }

    .result-hero-value{
      position:relative;
      z-index:1;
      margin:0;
      font-size:34px;
      line-height:1.08;
      letter-spacing:-0.05em;
      font-weight:950;
      color:#fff;
      word-break:keep-all;
    }

    .result-hero-desc{
      position:relative;
      z-index:1;
      margin-top:8px;
      font-size:13px;
      line-height:1.65;
      color:rgba(255,255,255,.92);
      word-break:keep-all;
    }

    .benefit-grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
    }

    .mini-grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
    }

    .result-card{
      border-radius:22px;
      padding:15px 15px;
      background:rgba(255,255,255,.88);
      border:1px solid rgba(255,255,255,.85);
      box-shadow:var(--shadow-soft);
    }

    .result-card.benefit-soft{
      background:linear-gradient(180deg,#f3fcf8 0%, #edf9f3 100%);
      border:1px solid rgba(210,241,226,.95);
    }

    .result-card.benefit-soft .result-label{
      color:#0f8c67;
    }

    .result-card.benefit-soft .result-value{
      color:#0d3f32;
    }

    .result-card.benefit-soft .result-text{
      color:#3a5f56;
    }

    .result-card.compact{
      padding:11px 12px;
      border-radius:18px;
      min-height:88px;
      display:flex;
      flex-direction:column;
      justify-content:center;
    }

    .result-card.compact.wide{
      grid-column:1 / -1;
      min-height:92px;
    }

    .result-label{
      font-size:12px;
      color:#667085;
      margin-bottom:6px;
      font-weight:800;
      letter-spacing:-0.01em;
    }

    .result-value{
      font-size:28px;
      line-height:1.16;
      letter-spacing:-0.04em;
      font-weight:900;
      word-break:keep-all;
    }

    .result-text{
      margin-top:8px;
      font-size:13px;
      color:#475467;
      line-height:1.65;
      word-break:keep-all;
    }

    .result-card.compact .result-label{
      font-size:11px;
      margin-bottom:4px;
    }

    .result-card.compact .result-value{
      font-size:21px;
      line-height:1.14;
    }

    .result-card.compact .result-text{
      margin-top:5px;
      font-size:11px;
      line-height:1.45;
      color:#667085;
    }

    .narrative{
      margin-top:10px;
      padding:16px 16px;
      border-radius:22px;
      background:linear-gradient(180deg,#ffffff 0%, #f9fbff 100%);
      border:1px solid rgba(255,255,255,.86);
      box-shadow:var(--shadow-soft);
    }

    .narrative p{
      margin:0 0 10px;
      font-size:15px;
      line-height:1.75;
      color:#1f2937;
      word-break:keep-all;
    }

    .narrative p:last-child{
      margin-bottom:0;
    }

    .contact-box{
      margin-top:12px;
      border-radius:24px;
      padding:18px 16px;
      background:linear-gradient(135deg,#102a56 0%, #1f4b99 50%, #2f6fde 100%);
      color:#fff;
      box-shadow:0 18px 34px rgba(20,54,111,.24);
    }

    .contact-title{
      font-size:18px;
      font-weight:900;
      margin:0 0 8px;
      letter-spacing:-0.02em;
      word-break:keep-all;
    }

    .contact-text{
      margin:0 0 14px;
      font-size:13px;
      line-height:1.75;
      color:rgba(255,255,255,.92);
      word-break:keep-all;
    }

    .contact-btn{
      display:flex;
      align-items:center;
      justify-content:center;
      width:100%;
      min-height:52px;
      border-radius:18px;
      text-decoration:none;
      font-size:16px;
      font-weight:900;
      letter-spacing:-0.02em;
      margin-top:10px;
      text-align:center;
      padding:12px 16px;
    }

    .call-btn{
      background:rgba(255,255,255,.16);
      border:1px solid rgba(255,255,255,.20);
      color:#fff;
      backdrop-filter:blur(8px);
    }

    .macomaex-btn{
      background:rgba(255,255,255,.96);
      border:1px solid rgba(255,255,255,.55);
      color:#12305f;
      box-shadow:0 10px 24px rgba(0,0,0,.10);
      line-height:1.5;
    }

    .note{
      margin-top:12px;
      font-size:11px;
      color:#6b7280;
      line-height:1.75;
      word-break:keep-all;
    }

    .note a{
      color:#1f4b99;
      text-decoration:none;
    }

    @media (max-width:420px){
      .app{
        padding:12px 12px 28px;
      }

      .logo{
        width:144px;
      }

      .hero{
        border-radius:28px;
        padding:20px 16px 18px;
      }

      .hero-title{
        font-size:27px;
      }

      .card{
        padding:16px 14px;
        border-radius:26px;
      }

      .input{
        height:56px;
        font-size:20px;
      }

      .result-hero-total{
        padding:18px 16px 16px;
        border-radius:24px;
      }

      .result-hero-value{
        font-size:30px;
      }

      .benefit-grid{
        grid-template-columns:1fr;
      }

      .mini-grid{
        gap:9px;
      }

      .result-card.compact{
        min-height:84px;
        padding:10px 11px;
      }

      .result-card.compact .result-value{
        font-size:19px;
      }

      .narrative p{
        font-size:14px;
      }
    }
  </style>
</head>
<body>
  <div class="app">
    <div class="topbar">
      <img class="logo" src="https://macomaex.com//cb/img/igalogo.png" alt="이가이엔씨 로고" />
      <div class="progress">
        <div class="dot active" id="dot1"></div>
        <div class="dot" id="dot2"></div>
        <div class="dot" id="dot3"></div>
      </div>
    </div>

    <!-- STEP 1 -->
    <section class="screen active" id="screen1">
      <div class="hero">
        <div class="pill">STEP 1</div>
        <h1 class="hero-title">투자금을 입력해주세요</h1>
        <p class="hero-desc">
          벤처인증기업 CB 투자 기준으로 예상 혜택을 계산합니다.<br />
          최소 투자금은 3,000만원부터 반영됩니다.
        </p>
      </div>

      <div class="card">
        <label class="label" for="investment">CB 투자금</label>
        <input class="input" id="investment" type="text" inputmode="numeric" placeholder="예: 30,000,000" />
        <div class="hint">예: 3,000만원 = 30,000,000원</div>

        <div class="chips">
          <button class="chip preset-investment" type="button" data-value="30000000">3,000만원</button>
          <button class="chip preset-investment" type="button" data-value="50000000">5,000만원</button>
          <button class="chip preset-investment" type="button" data-value="100000000">1억원</button>
        </div>

        <div class="spacer"></div>

        <div class="action-row">
          <button class="btn btn-primary" id="next1" disabled>다음</button>
        </div>
      </div>
    </section>

    <!-- STEP 2 -->
    <section class="screen" id="screen2">
      <div class="hero">
        <div class="pill">STEP 2</div>
        <h1 class="hero-title">연봉을 입력해주세요</h1>
        <p class="hero-desc">
          입력하신 투자금 기준으로 소득공제 가능액, 절세금액,<br />
          3년 유지 시 이자수익을 계산합니다.
        </p>
      </div>

      <div class="card">
        <div class="summary-chip" id="investmentPreview">투자금 -</div>

        <label class="label" for="salary">연봉</label>
        <input class="input" id="salary" type="text" inputmode="numeric" placeholder="예: 100,000,000" />
        <div class="hint">예: 1억원 = 100,000,000원</div>

        <div class="chips">
          <button class="chip preset-salary" type="button" data-value="80000000">8,000만원</button>
          <button class="chip preset-salary" type="button" data-value="100000000">1억원</button>
          <button class="chip preset-salary" type="button" data-value="150000000">1.5억원</button>
        </div>

        <div class="spacer"></div>

        <div class="action-row">
          <button class="btn btn-secondary" id="back2">이전</button>
          <button class="btn btn-primary" id="next2" disabled>결과 보기</button>
        </div>
      </div>
    </section>

    <!-- STEP 3 -->
    <section class="screen" id="screen3">
      <div class="hero">
        <div class="pill">RESULT</div>
        <h1 class="hero-title">입력이 완료되었습니다</h1>
        <p class="hero-desc">
          입력하신 투자금과 연봉을 기준으로<br />
          벤처기업 CB 투자 시 기대할 수 있는 혜택을 정리했습니다.
        </p>
      </div>

      <div class="card">
        <h2 class="title">핵심 결과</h2>
        <p class="desc">
          이자수익은 연 5.0% 기준, 세후 금액은 15.4% 원천징수를 반영한 간이 계산입니다.
        </p>

        <div class="result-stack">
          <div class="result-hero-total">
            <div class="result-hero-label">TOTAL BENEFIT</div>
            <div class="result-hero-title">절세금액 + 3년 세후 이자수익</div>
            <div class="result-hero-value" id="resultTotalBenefit">-</div>
            <div class="result-hero-desc" id="resultTotalBenefitText">
              절세금액 + 이자수익 = -
            </div>
          </div>

          <div class="benefit-grid">
            <div class="result-card benefit-soft">
              <div class="result-label">절세금액</div>
              <div class="result-value" id="resultTaxSaving">-</div>
              <div class="result-text" id="resultTaxSavingText">-</div>
            </div>

            <div class="result-card benefit-soft">
              <div class="result-label">3년 유지 시 이자수익</div>
              <div class="result-value" id="resultInterestGross">-</div>
              <div class="result-text" id="resultInterestNet">-</div>
            </div>
          </div>

          <div class="mini-grid">
            <div class="result-card compact">
              <div class="result-label">투자금</div>
              <div class="result-value" id="resultInvestment">-</div>
            </div>

            <div class="result-card compact">
              <div class="result-label">연봉</div>
              <div class="result-value" id="resultSalary">-</div>
            </div>

            <div class="result-card compact wide">
              <div class="result-label">소득공제 가능액</div>
              <div class="result-value" id="resultDeduction">-</div>
              <div class="result-text" id="resultDeductionText">
                벤처투자 소득공제 구간 및 한도를 반영한 금액입니다.
              </div>
            </div>
          </div>
        </div>

        <div class="narrative">
          <p id="line1">-</p>
          <p id="line2">-</p>
          <p id="line3">-</p>
          <p id="line4">-</p>
        </div>

        <div class="contact-box">
          <p class="contact-text">
            실제 벤처기업에 투자를 하시려면 <strong>02-573-8677</strong>로 연락을 주세요.
          </p>

          <a class="contact-btn call-btn" href="tel:02-573-8677">02-573-8677 전화하기</a>

          <a
            class="contact-btn macomaex-btn"
            href="https://www.macomaex.com"
            target="_blank"
            rel="noopener noreferrer"
          >
            벤처인증기업 (주)이가이엔씨<br> AI기반 거래처 발굴 플랫폼<br>'매커멕스' 둘러보기 클릭!!
          </a>
        </div>

        <div class="action-row" style="margin-top:14px;">
          <button class="btn btn-secondary" id="restart">처음부터 다시 계산하기</button>
        </div>

        <div class="note">
          참고:
          <a href="https://www.law.go.kr/LSW/lsLawLinkInfo.do?lsJoLnkSeq=1001022337&lsId=001584&chrClsCd=010202&print=print" target="_blank" rel="noopener noreferrer">조세특례제한법 제16조</a>
        </div>
      </div>
    </section>
  </div>

  <script>
    const ASSUMED_COUPON_RATE = 5.0; // 연 이자율 가정
    const INTEREST_NET_FACTOR = 0.846; // 15.4% 원천징수 반영

    const screens = {
      1: document.getElementById('screen1'),
      2: document.getElementById('screen2'),
      3: document.getElementById('screen3')
    };

    const dots = {
      1: document.getElementById('dot1'),
      2: document.getElementById('dot2'),
      3: document.getElementById('dot3')
    };

    const investmentInput = document.getElementById('investment');
    const salaryInput = document.getElementById('salary');

    const next1 = document.getElementById('next1');
    const next2 = document.getElementById('next2');
    const back2 = document.getElementById('back2');
    const restart = document.getElementById('restart');

    const investmentPreview = document.getElementById('investmentPreview');

    const resultInvestment = document.getElementById('resultInvestment');
    const resultSalary = document.getElementById('resultSalary');
    const resultDeduction = document.getElementById('resultDeduction');
    const resultDeductionText = document.getElementById('resultDeductionText');

    const resultTaxSaving = document.getElementById('resultTaxSaving');
    const resultTaxSavingText = document.getElementById('resultTaxSavingText');

    const resultInterestGross = document.getElementById('resultInterestGross');
    const resultInterestNet = document.getElementById('resultInterestNet');

    const resultTotalBenefit = document.getElementById('resultTotalBenefit');
    const resultTotalBenefitText = document.getElementById('resultTotalBenefitText');

    const line1 = document.getElementById('line1');
    const line2 = document.getElementById('line2');
    const line3 = document.getElementById('line3');
    const line4 = document.getElementById('line4');

    function showStep(step){
      Object.keys(screens).forEach(key=>{
        screens[key].classList.remove('active');
        dots[key].classList.remove('active');
      });
      screens[step].classList.add('active');
      dots[step].classList.add('active');
      window.scrollTo({top:0,behavior:'smooth'});
    }

    function toInt(value){
      return Number(String(value).replace(/[^0-9]/g,'')) || 0;
    }

    function formatComma(value){
      const num = toInt(value);
      return num ? num.toLocaleString('ko-KR') : '';
    }

    function formatInput(el){
      el.value = formatComma(el.value);
    }

    function krw(n){
      return Math.round(n).toLocaleString('ko-KR') + '원';
    }

    function earnedIncomeDeduction(salary){
      if(salary <= 5000000) return salary * 0.7;
      if(salary <= 15000000) return 3500000 + (salary - 5000000) * 0.4;
      if(salary <= 45000000) return 7500000 + (salary - 15000000) * 0.15;
      if(salary <= 100000000) return 12000000 + (salary - 45000000) * 0.05;
      return 14750000 + (salary - 100000000) * 0.02;
    }

    function incomeTax(base){
      const b = Math.max(0, base);
      if(b <= 14000000) return b * 0.06;
      if(b <= 50000000) return 840000 + (b - 14000000) * 0.15;
      if(b <= 88000000) return 6240000 + (b - 50000000) * 0.24;
      if(b <= 150000000) return 15360000 + (b - 88000000) * 0.35;
      if(b <= 300000000) return 37060000 + (b - 150000000) * 0.38;
      if(b <= 500000000) return 94060000 + (b - 300000000) * 0.40;
      if(b <= 1000000000) return 174060000 + (b - 500000000) * 0.42;
      return 384060000 + (b - 1000000000) * 0.45;
    }

    function ventureDeductionBreakdown(investment){
      const tier1Base = Math.min(investment, 30000000);
      const tier2Base = Math.min(Math.max(investment - 30000000, 0), 20000000);
      const tier3Base = Math.max(investment - 50000000, 0);

      const tier1Ded = tier1Base * 1.0;
      const tier2Ded = tier2Base * 0.7;
      const tier3Ded = tier3Base * 0.3;

      return {
        tier1Base,
        tier2Base,
        tier3Base,
        tier1Ded,
        tier2Ded,
        tier3Ded,
        total: tier1Ded + tier2Ded + tier3Ded
      };
    }

    function updateButtons(){
      const investment = toInt(investmentInput.value);
      const salary = toInt(salaryInput.value);

      next1.disabled = investment <= 0;
      next2.disabled = salary <= 0;
    }

    function calculateAndRender(){
      let investment = toInt(investmentInput.value);
      let salary = toInt(salaryInput.value);

      if(investment < 30000000) investment = 30000000;
      if(salary < 0) salary = 0;

      const earnedDed = earnedIncomeDeduction(salary);
      const compositeIncome = Math.max(0, salary - earnedDed);
      const deductionCap = compositeIncome * 0.5;

      const breakdown = ventureDeductionBreakdown(investment);
      const rawDeduction = breakdown.total;
      const appliedDeduction = Math.min(rawDeduction, deductionCap);

      const taxBefore = incomeTax(compositeIncome) * 1.1; // 지방소득세 포함
      const taxAfter = incomeTax(Math.max(0, compositeIncome - appliedDeduction)) * 1.1;
      const taxSaving = Math.max(0, taxBefore - taxAfter);

      const grossInterest = investment * (ASSUMED_COUPON_RATE / 100) * 3;
      const netInterest = grossInterest * INTEREST_NET_FACTOR;

      const totalBenefit = taxSaving + netInterest;

      resultInvestment.textContent = krw(investment);
      resultSalary.textContent = krw(salary);
      resultDeduction.textContent = krw(appliedDeduction);
      resultDeductionText.textContent = `구간별 공제율과 종합소득금액 50% 한도를 반영한 소득공제 가능액입니다.`;

      resultTaxSaving.textContent = krw(taxSaving);
      resultTaxSavingText.textContent = `입력하신 연봉 기준으로 예상되는 절세금액은 ${krw(taxSaving)}입니다.`;

      resultInterestGross.textContent = `세전 ${krw(grossInterest)}`;
      resultInterestNet.textContent = `3년 유지 시 세후 예상 이자수익은 ${krw(netInterest)}입니다.`;

      resultTotalBenefit.textContent = krw(totalBenefit);
      resultTotalBenefitText.textContent = `절세금액 ${krw(taxSaving)} + 세후 이자수익 ${krw(netInterest)} 기준 총 혜택입니다.`;

      line1.textContent = `투자금은 ${krw(investment)}이고, 연봉은 ${krw(salary)}입니다.`;
      line2.textContent = `벤처인증기업에 투자하여 받을 수 있는 소득공제 가능액은 ${krw(appliedDeduction)}입니다.`;
      line3.textContent = `예상 절세금액은 ${krw(taxSaving)}이며, 3년간 유지하여 받을 수 있는 이자수익은 세전 ${krw(grossInterest)}, 세후 ${krw(netInterest)}입니다.`;
      line4.textContent = `결론적으로 예상 Total Benefit은 ${krw(totalBenefit)}입니다.`;
    }

    investmentInput.addEventListener('input', () => {
      formatInput(investmentInput);
      updateButtons();
    });

    salaryInput.addEventListener('input', () => {
      formatInput(salaryInput);
      updateButtons();
    });

    document.querySelectorAll('.preset-investment').forEach(btn => {
      btn.addEventListener('click', () => {
        investmentInput.value = Number(btn.dataset.value).toLocaleString('ko-KR');
        updateButtons();
      });
    });

    document.querySelectorAll('.preset-salary').forEach(btn => {
      btn.addEventListener('click', () => {
        salaryInput.value = Number(btn.dataset.value).toLocaleString('ko-KR');
        updateButtons();
      });
    });

    next1.addEventListener('click', () => {
      let investment = toInt(investmentInput.value);
      if(investment < 30000000){
        investment = 30000000;
        investmentInput.value = investment.toLocaleString('ko-KR');
      }
      investmentPreview.textContent = `투자금 ${krw(investment)}`;
      showStep(2);
      setTimeout(() => salaryInput.focus(), 120);
    });

    back2.addEventListener('click', () => {
      showStep(1);
      setTimeout(() => investmentInput.focus(), 120);
    });

    next2.addEventListener('click', () => {
      calculateAndRender();
      showStep(3);
    });

    restart.addEventListener('click', () => {
      investmentInput.value = '';
      salaryInput.value = '';
      investmentPreview.textContent = '투자금 -';
      updateButtons();
      showStep(1);
      setTimeout(() => investmentInput.focus(), 120);
    });

    investmentInput.addEventListener('keydown', (e) => {
      if(e.key === 'Enter' && !next1.disabled) next1.click();
    });

    salaryInput.addEventListener('keydown', (e) => {
      if(e.key === 'Enter' && !next2.disabled) next2.click();
    });

    updateButtons();
  </script>
</body>
</html>

    </section>
</body>
</html>
