<?php
$G5_PATH = "../..";
include_once("$G5_PATH/common.php");
?>

<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8" />
  <title>전환사채(CB) 투자에 따른 환급액 계산기</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; line-height: 1.6; }
    h1 { margin-bottom: 0.5rem; }
    .card { border: 1px solid #ddd; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: 600; }
    input[type="number"] { width: 100%; padding: 8px; box-sizing: border-box; margin-bottom: 12px; }
    button { padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; }
    button.primary { background: #0052cc; color: #fff; font-weight: 600; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: right; }
    th { background: #f5f5f5; }
    td.label { text-align: left; }
  </style>
</head>
<body>
  <h1>전환사채(CB) 투자에 따른 환급액 계산기_(주)이가이엔씨</h1>

  <div class="card">
    <label>총수입금액 (원)</label>
    <input id="income" type="number" value="100000000" />

    <label>투자금액 (원)</label>
    <input id="investment" type="number" value="50000000" />

    <label>연수익률 (%)</label>
    <input id="rate" type="number" step="0.1" value="4" />

    <button class="primary" onclick="calculate()">계산하기</button>
  </div>

  <div id="result" class="card" style="display:none;">
    <h2>결과</h2>

    <h3>1. 소득공제액</h3>
    <table>
      <tr>
        <th>공제구간</th>
        <th>적용 투자금액</th>
        <th>공제율</th>
        <th>소득공제액</th>
      </tr>
      <tr>
        <td class="label">100% (0~30M)</td>
        <td id="tier1-amount"></td>
        <td>100%</td>
        <td id="tier1-ded"></td>
      </tr>
      <tr>
        <td class="label">70% (30~50M)</td>
        <td id="tier2-amount"></td>
        <td>70%</td>
        <td id="tier2-ded"></td>
      </tr>
      <tr>
        <td class="label">30% (50M 초과)</td>
        <td id="tier3-amount"></td>
        <td>30%</td>
        <td id="tier3-ded"></td>
      </tr>
      <tr>
        <td class="label">소득공제 합계</td>
        <td colspan="2"></td>
        <td id="ded-sum"></td>
      </tr>
      <tr>
        <td class="label">공제한도 (총수입 50%)</td>
        <td colspan="2"></td>
        <td id="ded-limit"></td>
      </tr>
      <tr>
        <td class="label"><b>최종 소득공제액</b></td>
        <td colspan="2"></td>
        <td id="ded-final"></td>
      </tr>
    </table>

    <h3>2. 소득세 환급액</h3>
    <table>
      <tr>
        <th>구분</th>
        <th>과세표준</th>
        <th>국세</th>
        <th>지방소득세(10%)</th>
        <th>총 납부세액</th>
      </tr>
      <tr>
        <td class="label">투자 전</td>
        <td id="tax-base-before"></td>
        <td id="tax-income-before"></td>
        <td id="tax-local-before"></td>
        <td id="tax-total-before"></td>
      </tr>
      <tr>
        <td class="label">투자 후</td>
        <td id="tax-base-after"></td>
        <td id="tax-income-after"></td>
        <td id="tax-local-after"></td>
        <td id="tax-total-after"></td>
      </tr>
      <tr>
        <td class="label"><b>환급액</b></td>
        <td colspan="3"></td>
        <td id="refund"></td>
      </tr>
    </table>

    <h3>3. 이자수익 및 총수익</h3>
    <table>
      <tr>
        <td class="label">월 이자수익</td>
        <td id="interest-monthly"></td>
      </tr>
      <tr>
        <td class="label">3년(36개월) 총 이자수익</td>
        <td id="interest-total"></td>
      </tr>
      <tr>
        <td class="label"><b>총수익 (환급액 + 이자)</b></td>
        <td id="total-return"></td>
      </tr>
      <tr>
        <td class="label"><b>원금대비 수익률</b></td>
        <td id="roi"></td>
      </tr>
    </table>
  </div>

  <script>
    function fmt(n) {
      return n.toLocaleString('ko-KR', { maximumFractionDigits: 0 });
    }
    function fmt1(n) {
      return n.toLocaleString('ko-KR', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
    }

    // 종합소득세(국세) 계산: 한국 세율 + 누진공제식
    function incomeTax(base) {
      let tax;
      if (base <= 12000000) {
        tax = base * 0.06;
      } else if (base <= 46000000) {
        tax = base * 0.15 - 1080000;
      } else if (base <= 88000000) {
        tax = base * 0.24 - 5220000;
      } else if (base <= 150000000) {
        tax = base * 0.35 - 14900000;
      } else if (base <= 300000000) {
        tax = base * 0.38 - 19400000;
      } else if (base <= 500000000) {
        tax = base * 0.40 - 25400000;
      } else if (base <= 1000000000) {
        tax = base * 0.42 - 35400000;
      } else {
        tax = base * 0.45 - 65400000;
      }
      return tax;
    }

    function calculate() {
      const income = Number(document.getElementById('income').value || 0);
      const invest = Number(document.getElementById('investment').value || 0);
      const rate = Number(document.getElementById('rate').value || 0);

      // 1) 소득공제 구간
      const tier1Amount = Math.min(invest, 30000000);
      const tier2Amount = Math.max(Math.min(invest - 30000000, 20000000), 0);
      const tier3Amount = Math.max(invest - 50000000, 0);

      const tier1Ded = tier1Amount * 1.0;
      const tier2Ded = tier2Amount * 0.7;
      const tier3Ded = tier3Amount * 0.3;

      const dedSum = tier1Ded + tier2Ded + tier3Ded;
      const dedLimit = income * 0.5;
      const dedFinal = Math.min(dedSum, dedLimit);

      // 2) 소득세 전/후
      const baseBefore = income;
      const baseAfter = Math.max(income - dedFinal, 0);

      const incomeBefore = incomeTax(baseBefore);
      const localBefore = incomeBefore * 0.1;
      const totalBefore = incomeBefore + localBefore;

      const incomeAfter = incomeTax(baseAfter);
      const localAfter = incomeAfter * 0.1;
      const totalAfter = incomeAfter + localAfter;

      const refund = totalBefore - totalAfter;

      // 3) 이자 및 수익
      const monthlyInterest = invest * (rate / 100) / 12;
      const totalInterest = monthlyInterest * 36;
      const totalReturn = refund + totalInterest;
      const roi = invest > 0 ? (totalReturn / invest * 100) : 0;

      // 결과 표시
      document.getElementById('tier1-amount').textContent = fmt(tier1Amount);
      document.getElementById('tier1-ded').textContent = fmt(tier1Ded);
      document.getElementById('tier2-amount').textContent = fmt(tier2Amount);
      document.getElementById('tier2-ded').textContent = fmt(tier2Ded);
      document.getElementById('tier3-amount').textContent = fmt(tier3Amount);
      document.getElementById('tier3-ded').textContent = fmt(tier3Ded);
      document.getElementById('ded-sum').textContent = fmt(dedSum);
      document.getElementById('ded-limit').textContent = fmt(dedLimit);
      document.getElementById('ded-final').textContent = fmt(dedFinal);

      document.getElementById('tax-base-before').textContent = fmt(baseBefore);
      document.getElementById('tax-income-before').textContent = fmt(incomeBefore);
      document.getElementById('tax-local-before').textContent = fmt(localBefore);
      document.getElementById('tax-total-before').textContent = fmt(totalBefore);

      document.getElementById('tax-base-after').textContent = fmt(baseAfter);
      document.getElementById('tax-income-after').textContent = fmt(incomeAfter);
      document.getElementById('tax-local-after').textContent = fmt(localAfter);
      document.getElementById('tax-total-after').textContent = fmt(totalAfter);

      document.getElementById('refund').textContent = fmt(refund);

      document.getElementById('interest-monthly').textContent = fmt(Math.round(monthlyInterest));
      document.getElementById('interest-total').textContent = fmt(Math.round(totalInterest));
      document.getElementById('total-return').textContent = fmt(Math.round(totalReturn));
      document.getElementById('roi').textContent = fmt1(roi) + " %";

      document.getElementById('result').style.display = 'block';
    }

    // 초기 자동 계산
    calculate();
  </script>
</body>
</html>
