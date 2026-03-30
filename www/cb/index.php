<?php
include_once("./_common.php");
?>
<!DOCTYPE html>
<html lang="ko" data-theme="light" style=""><head>
    <meta charset="UTF-8">
    <title>벤처인증기업 CB투자 시뮬레이션</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+KR:wght@400;700&amp;display=swap" rel="stylesheet">
    <style>
      /* Document-style Reset & Typography */
      body {
        max-width: 880px;
        margin: 0 auto;
        padding: 32px 80px;
        position: relative;
        box-sizing: border-box;
        background-color: white;
        color: #000;
        font-family: 'Noto Serif KR', 'Times New Roman', serif;
        line-height: 1.6;
      }

      /* Heading Styles */
      h1 {
        font-size: 24pt;
        font-weight: 700;
        text-align: center;
        margin-bottom: 32px;
        border-bottom: 2px solid #000;
        padding-bottom: 16px;
      }

      h2 {
        font-size: 16pt;
        font-weight: 700;
        margin-top: 28px;
        margin-bottom: 12px;
        color: #333;
        border-left: 4px solid #444;
        padding-left: 12px;
      }

      h3 {
        font-size: 13pt;
        font-weight: 700;
        margin-top: 20px;
        margin-bottom: 8px;
        color: #444;
      }

      /* Paragraph Styles */
      p {
        font-size: 11pt;
        margin-bottom: 12px;
        text-align: justify;
        text-justify: inter-word;
      }

      /* Table Styles - Professional Document Look */
      table {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0 24px 0;
        font-size: 10pt;
        border: 1px solid #000;
      }

      th {
        background-color: #f2f2f2;
        color: #000;
        font-weight: 700;
        text-align: center;
        padding: 8px;
        border: 1px solid #000;
        vertical-align: middle;
      }

      td {
        padding: 8px;
        border: 1px solid #000;
        text-align: right;
        color: #000;
      }

      td.label-col {
        text-align: left;
        font-weight: bold;
        background-color: #fafafa;
      }

      /* List Styles */
      ul {
        margin-left: 20px;
        margin-bottom: 16px;
        list-style-type: disc;
      }

      li {
        font-size: 11pt;
        margin-bottom: 6px;
      }

      /* Assumption Box */
      .assumption-box {
        border: 1px solid #666;
        padding: 16px;
        margin-bottom: 24px;
        background-color: #f9f9f9;
        font-size: 10.5pt;
      }

      .assumption-title {
        font-weight: bold;
        margin-bottom: 8px;
        text-decoration: underline;
      }

      /* Footer Disclaimer */
      .disclaimer {
        margin-top: 40px;
        padding-top: 16px;
        border-top: 1px solid #ccc;
        font-size: 9pt;
        color: #555;
        font-style: italic;
      }

      /* Utility */
      .highlight-text {
        color: #d32f2f;
        font-weight: bold;
      }
      .text-center {
        text-align: center;
      }
    </style>
  </head>
  <body style="">
    <h1>TIPS 투자전 프라이빗 라운드(CB투자)</h1>
    <p class="text-center" style="font-size: 12pt; margin-top: -20px; margin-bottom: 32px; color: #555;">
      (2월말까지 모금액 총 2억, 최대 7명)
    </p>

    <h2>1. 개요</h2>
    <p>
      본 문서는 근로소득자가 벤처기업인증을 받은 기업에 전환사채(Convertible Bond, CB) 형태로 투자할 경우 기대할 수 있는
      수익과 세제 혜택을 분석하기 위해 작성되었습니다. 벤처투자 소득공제 제도는 조세특례제한법 제16조에 의거, 벤처기업
      등에 투자 시 투자 금액의 일정 비율을 종합소득금액에서 공제해주는 강력한 절세 수단입니다.
    </p>

    <div class="assumption-box">
      <div class="assumption-title">※ 시뮬레이션 가정 및 기준</div>
      <ul>
        <li><strong>이자율:</strong> 연 5% (단리 기준 가정)</li>
        <li><strong>투자 기간:</strong> 3년 만기 보유 가정</li>
        <li><strong>주식 전환시점:</strong> 투자 2년 후 선택 가능</li>
        <li><strong>원금 상환시점:</strong> 투자 2년 후 선택 가능</li>
        <li>
          <strong>소득공제율:</strong>
          <ul>
            <li>3,000만 원 이하 구간: 100%</li>
            <li>3,000만 원 초과 ~ 5,000만 원 이하 구간: 70%</li>
          </ul>
        </li>
        <li><strong>소득공제 한도:</strong> 종합소득금액의 50%</li>
        <li><strong>적용 세율:</strong> 2024년 귀속 소득세 과세표준 구간 기준 (지방소득세 10% 별도 포함 계산)</li>
      </ul>
    </div>

    <h2>2. 소득 구간별 투자 시뮬레이션</h2>

    <h3>(1) 근로소득 1억 원일 경우 (과세표준 8,800만 원 초과 ~ 1.5억 원 이하 구간 가정)</h3>
    <p>
      연봉 1억 원인 근로자의 경우, 기본공제 등을 제외한 과세표준이 대략 35% 세율 구간(지방세 포함 38.5%)에 해당한다고
      가정하여 계산하였습니다.
    </p>
    <table>
      <thead>
        <tr>
          <th>구분</th>
          <th>투자금 3,000만 원</th>
          <th>투자금 5,000만 원</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="label-col">연간 이자수익 (5%)</td>
          <td>1,500,000 원</td>
          <td>2,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">소득공제 대상 금액</td>
          <td>30,000,000 원<br>(100% 공제)</td>
          <td>44,000,000 원<br>(3천만 100% + 2천만 70%)</td>
        </tr>
        <tr>
          <td class="label-col">적용 한계세율(지방세 포함)</td>
          <td>38.5%</td>
          <td>38.5%</td>
        </tr>
        <tr>
          <td class="label-col">절감되는 세금 (예상 환급액)</td>
          <td>11,550,000 원</td>
          <td>16,940,000 원</td>
        </tr>
        <tr>
          <td class="label-col">투자 원금 대비 절세 효과</td>
          <td>38.5%</td>
          <td>약 33.8%</td>
        </tr>
        <tr>
          <td class="label-col">총 혜택 (이자 + 절세액)</td>
          <td class="highlight-text">13,050,000 원</td>
          <td class="highlight-text">19,440,000 원</td>
        </tr>
      </tbody>
    </table>

    <h3>(2) 근로소득 1.5억 원일 경우 (과세표준 1.5억 원 초과 구간 일부 적용 가정)</h3>
    <p>
      연봉 1.5억 원인 근로자의 경우, 과세표준 8,800만 원 초과 구간(35%)과 일부 1.5억 원 초과 구간(38%)에 걸쳐 있을 수
      있으나, 보수적으로 35%(지방세 포함 38.5%) 구간을 주 적용 세율로 계산합니다. (공제 후 과표 하락 효과 고려)
    </p>
    <table>
      <thead>
        <tr>
          <th>구분</th>
          <th>투자금 3,000만 원</th>
          <th>투자금 5,000만 원</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="label-col">연간 이자수익 (5%)</td>
          <td>1,500,000 원</td>
          <td>2,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">소득공제 대상 금액</td>
          <td>30,000,000 원</td>
          <td>44,000,000 원</td>
        </tr>
        <tr>
          <td class="label-col">적용 한계세율(지방세 포함)</td>
          <td>약 38.5% ~ 41.8%<br>(평균 40% 가정)</td>
          <td>약 38.5% ~ 41.8%<br>(평균 40% 가정)</td>
        </tr>
        <tr>
          <td class="label-col">절감되는 세금 (예상 환급액)</td>
          <td>12,000,000 원</td>
          <td>17,600,000 원</td>
        </tr>
        <tr>
          <td class="label-col">투자 원금 대비 절세 효과</td>
          <td>40.0%</td>
          <td>약 35.2%</td>
        </tr>
        <tr>
          <td class="label-col">총 혜택 (이자 + 절세액)</td>
          <td class="highlight-text">13,500,000 원</td>
          <td class="highlight-text">20,100,000 원</td>
        </tr>
      </tbody>
    </table>

    <h3>(3) 근로소득 2억 원일 경우 (최고세율 구간 근접)</h3>
    <p>
      연봉 2억 원인 근로자는 과세표준 1.5억 원 초과 구간(38%, 지방세 포함 41.8%) 및 3억 원 이하 구간에 안정적으로
      위치합니다. 높은 세율이 적용되므로 절세 효과가 극대화됩니다.
    </p>
    <table>
      <thead>
        <tr>
          <th>구분</th>
          <th>투자금 3,000만 원</th>
          <th>투자금 5,000만 원</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="label-col">연간 이자수익 (5%)</td>
          <td>1,500,000 원</td>
          <td>2,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">소득공제 대상 금액</td>
          <td>30,000,000 원</td>
          <td>44,000,000 원</td>
        </tr>
        <tr>
          <td class="label-col">적용 한계세율(지방세 포함)</td>
          <td>41.8%</td>
          <td>41.8%</td>
        </tr>
        <tr>
          <td class="label-col">절감되는 세금 (예상 환급액)</td>
          <td>12,540,000 원</td>
          <td>18,392,000 원</td>
        </tr>
        <tr>
          <td class="label-col">투자 원금 대비 절세 효과</td>
          <td>41.8%</td>
          <td>약 36.8%</td>
        </tr>
        <tr>
          <td class="label-col">총 혜택 (이자 + 절세액)</td>
          <td class="highlight-text">14,040,000 원</td>
          <td class="highlight-text">20,892,000 원</td>
        </tr>
      </tbody>
    </table>

    <h2>3. 연도별 수익 변화 분석</h2>
    <p>
      벤처투자 소득공제는 투자 첫 해에만 적용되며, 이후 연도에는 이자 수익만 발생합니다. 
      3년 만기를 가정하여 연도별 누적 수익을 분석하였습니다.
    </p>

    <h3>(1) 투자금 3,000만 원 - 연도별 누적 수익</h3>
    <table>
      <thead>
        <tr>
          <th>구분</th>
          <th>1년차</th>
          <th>2년차</th>
          <th>3년차 (만기)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="label-col" colspan="4" style="text-align: center; background-color: #e8e8e8;">근로소득 1억 원 (세율 38.5%)</td>
        </tr>
        <tr>
          <td class="label-col">이자수익 (누적)</td>
          <td>1,500,000 원</td>
          <td>3,000,000 원</td>
          <td>4,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">세금 환급액</td>
          <td>11,550,000 원</td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr>
          <td class="label-col">누적 총 수익</td>
          <td class="highlight-text">13,050,000 원</td>
          <td class="highlight-text">15,550,000 원</td>
          <td class="highlight-text">16,050,000 원</td>
        </tr>
        <tr>
          <td class="label-col">수익률 (원금 대비)</td>
          <td class="highlight-text">43.5%</td>
          <td class="highlight-text">51.8%</td>
          <td class="highlight-text">53.5%</td>
        </tr>
        <tr>
          <td class="label-col" colspan="4" style="text-align: center; background-color: #e8e8e8;">근로소득 1.5억 원 (세율 40%)</td>
        </tr>
        <tr>
          <td class="label-col">이자수익 (누적)</td>
          <td>1,500,000 원</td>
          <td>3,000,000 원</td>
          <td>4,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">세금 환급액</td>
          <td>12,000,000 원</td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr>
          <td class="label-col">누적 총 수익</td>
          <td class="highlight-text">13,500,000 원</td>
          <td class="highlight-text">16,000,000 원</td>
          <td class="highlight-text">16,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">수익률 (원금 대비)</td>
          <td class="highlight-text">45.0%</td>
          <td class="highlight-text">53.3%</td>
          <td class="highlight-text">55.0%</td>
        </tr>
        <tr>
          <td class="label-col" colspan="4" style="text-align: center; background-color: #e8e8e8;">근로소득 2억 원 (세율 41.8%)</td>
        </tr>
        <tr>
          <td class="label-col">이자수익 (누적)</td>
          <td>1,500,000 원</td>
          <td>3,000,000 원</td>
          <td>4,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">세금 환급액</td>
          <td>12,540,000 원</td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr>
          <td class="label-col">누적 총 수익</td>
          <td class="highlight-text">14,040,000 원</td>
          <td class="highlight-text">16,540,000 원</td>
          <td class="highlight-text">17,040,000 원</td>
        </tr>
        <tr>
          <td class="label-col">수익률 (원금 대비)</td>
          <td class="highlight-text">46.8%</td>
          <td class="highlight-text">55.1%</td>
          <td class="highlight-text">56.8%</td>
        </tr>
      </tbody>
    </table>

    <h3>(2) 투자금 5,000만 원 - 연도별 누적 수익</h3>
    <table>
      <thead>
        <tr>
          <th>구분</th>
          <th>1년차</th>
          <th>2년차</th>
          <th>3년차 (만기)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="label-col" colspan="4" style="text-align: center; background-color: #e8e8e8;">근로소득 1억 원 (세율 38.5%)</td>
        </tr>
        <tr>
          <td class="label-col">이자수익 (누적)</td>
          <td>2,500,000 원</td>
          <td>5,000,000 원</td>
          <td>7,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">세금 환급액</td>
          <td>16,940,000 원</td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr>
          <td class="label-col">누적 총 수익</td>
          <td class="highlight-text">19,440,000 원</td>
          <td class="highlight-text">24,440,000 원</td>
          <td class="highlight-text">24,440,000 원</td>
        </tr>
        <tr>
          <td class="label-col">수익률 (원금 대비)</td>
          <td class="highlight-text">38.9%</td>
          <td class="highlight-text">48.9%</td>
          <td class="highlight-text">48.9%</td>
        </tr>
        <tr>
          <td class="label-col" colspan="4" style="text-align: center; background-color: #e8e8e8;">근로소득 1.5억 원 (세율 40%)</td>
        </tr>
        <tr>
          <td class="label-col">이자수익 (누적)</td>
          <td>2,500,000 원</td>
          <td>5,000,000 원</td>
          <td>7,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">세금 환급액</td>
          <td>17,600,000 원</td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr>
          <td class="label-col">누적 총 수익</td>
          <td class="highlight-text">20,100,000 원</td>
          <td class="highlight-text">25,100,000 원</td>
          <td class="highlight-text">25,100,000 원</td>
        </tr>
        <tr>
          <td class="label-col">수익률 (원금 대비)</td>
          <td class="highlight-text">40.2%</td>
          <td class="highlight-text">50.2%</td>
          <td class="highlight-text">50.2%</td>
        </tr>
        <tr>
          <td class="label-col" colspan="4" style="text-align: center; background-color: #e8e8e8;">근로소득 2억 원 (세율 41.8%)</td>
        </tr>
        <tr>
          <td class="label-col">이자수익 (누적)</td>
          <td>2,500,000 원</td>
          <td>5,000,000 원</td>
          <td>7,500,000 원</td>
        </tr>
        <tr>
          <td class="label-col">세금 환급액</td>
          <td>18,392,000 원</td>
          <td>-</td>
          <td>-</td>
        </tr>
        <tr>
          <td class="label-col">누적 총 수익</td>
          <td class="highlight-text">20,892,000 원</td>
          <td class="highlight-text">25,892,000 원</td>
          <td class="highlight-text">25,892,000 원</td>
        </tr>
        <tr>
          <td class="label-col">수익률 (원금 대비)</td>
          <td class="highlight-text">41.8%</td>
          <td class="highlight-text">51.8%</td>
          <td class="highlight-text">51.8%</td>
        </tr>
      </tbody>
    </table>

    <div class="assumption-box">
      <div class="assumption-title">※ 연도별 수익 특징</div>
      <ul>
        <li><strong>1년차:</strong> 소득공제에 따른 세금 환급액이 포함되어 가장 높은 수익률 실현 (38~47%)</li>
        <li><strong>2년차:</strong> 이자 수익만 누적되며, 누적 수익률은 지속 상승 (49~55%)</li>
        <li><strong>3년차 (만기):</strong> 3년간 이자 수익 누적 완료 후 원금 + 총 수익 회수 (49~57%)</li>
        <li><strong>핵심 포인트:</strong> 투자 첫 해 연말정산 시 세금 환급을 받아 실질적인 투자 원금 부담이 크게 감소하며, 이후 안정적인 이자 수익이 계속 발생합니다.</li>
      </ul>
    </div>

    <h2>4. 요약 및 결론</h2>
    <p>시뮬레이션 결과, 고소득자일수록 벤처투자 소득공제를 통한 절세 효과가 매우 큰 것으로 나타났습니다.</p>
    <ul>
      <li>
        <strong>소득공제 효율:</strong> 투자금 3,000만 원까지는 100% 소득공제가 적용되므로, 이 구간까지의 투자가 세후
        수익률 측면에서 가장 효율적입니다.
      </li>
      <li>
        <strong>실질 수익률 상승:</strong> 단순 이자 수익(5%) 외에, 연말정산을 통해 돌려받는 세금 환급액을 수익으로
        환산할 경우, 투자 원금 대비 <strong>30% ~ 40% 이상의 확정적 절세 수익</strong>을 투자 첫 해에 확보할 수
        있습니다.
      </li>
      <li>
        <strong>전략적 제언:</strong> 근로소득 1억 원 이상의 대상자는 가급적 3,000만 원 한도를 채워 투자하는 것이
        유리하며, 여유 자금이 있을 경우 5,000만 원까지 증액하여 70% 공제 구간까지 활용하는 것을 권장합니다.
      </li>
    </ul>

    <div class="disclaimer">
      <strong>※ 유의사항 및 면책조항</strong><br>
      1. 본 시뮬레이션은 2024년 세법 기준으로 작성되었으며, 실제 결정세액은 개인의 인적공제, 타 소득공제 항목 등에 따라
      달라질 수 있습니다.<br>
      2. 벤처투자 소득공제는 투자일로부터 3년간 투자를 유지해야 추징되지 않습니다.<br>
      3. CB 투자는 원금 손실 위험이 있는 투자 상품입니다. 기업의 부도 등으로 인한 원금 손실 위험은 본 시뮬레이션의 절세
      이익 계산에 포함되지 않았습니다.<br>
      4. 본 자료는 정보 제공을 목적으로 하며, 법적 효력을 갖지 않습니다. 정확한 세무 상담은 세무 전문가와 상의하시기
      바랍니다.
    </div>
  

</body></html>