<?php
if (!defined('_GNUBOARD_')) exit;

$e = static function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$requestCompanyProfileName = isset($requestCompanyProfileName) ? (string)$requestCompanyProfileName : (string)$requestCompanyName;
$requestCompanyBizNo = isset($requestCompanyBizNo) ? (string)$requestCompanyBizNo : '';
$requestCompanyFounded = isset($requestCompanyFounded) ? (string)$requestCompanyFounded : '';
$requestCompanyEmployees = isset($requestCompanyEmployees) ? (string)$requestCompanyEmployees : '';
$requestCompanyCorpType = isset($requestCompanyCorpType) ? (string)$requestCompanyCorpType : '';
$requestCompanyIndustry = isset($requestCompanyIndustry) ? (string)$requestCompanyIndustry : '';
$requestCompanyProduct = isset($requestCompanyProduct) ? (string)$requestCompanyProduct : '';
$requestCompanyHomepage = isset($requestCompanyHomepage) ? (string)$requestCompanyHomepage : '';
$tradeDirectionLabel = isset($tradeDirectionLabel) ? (string)$tradeDirectionLabel : '';
$tradePurposeLabels = (isset($tradePurposeLabels) && is_array($tradePurposeLabels)) ? $tradePurposeLabels : array();
$messagePreviewLines = (isset($messagePreviewLines) && is_array($messagePreviewLines)) ? $messagePreviewLines : array();
$mailDateLabel = isset($mailDateLabel) ? (string)$mailDateLabel : date('Y년 m월 d일 H:i');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>거래 요청서 — 매커멕스</title>
<style>
  *, *::before, *::after { box-sizing: border-box; }
  body {
    margin: 0; padding: 0;
    background: #ffffff;
    min-height: 100vh;
    font-family: 'Apple SD Gothic Neo', 'Malgun Gothic', -apple-system, sans-serif;
  }
</style>
</head>
<body>

<table width="100%" cellpadding="0" cellspacing="0" style="padding:44px 0 60px;">
<tr><td align="center">

<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,0.04),0 8px 24px rgba(0,0,0,0.06);overflow:hidden;">

  <tr><td style="padding:26px 28px 22px;border-bottom:1px solid #e5e7eb;">
    <div style="font-size:10px;font-weight:700;color:#3b82f6;letter-spacing:0.2em;text-transform:uppercase;margin-bottom:6px;">MACOMAEX · B2B 거래 플랫폼</div>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
      <div style="font-size:18px;font-weight:800;color:#111827;letter-spacing:-0.02em;">거래 요청이 도착했습니다</div>
      <div style="font-size:11px;color:#9ca3af;"><?php echo $e($mailDateLabel); ?></div>
    </div>
  </td></tr>

  <tr><td style="padding:14px 14px 18px;background:#f9fafb;">

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
      <tr>
        <td style="width:47%;vertical-align:top;">
          <div style="background:#ffffff;border-radius:12px;padding:16px;border:1px solid #e5e7eb;">
            <div style="width:36px;height:36px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
              <img src="cid:trade-icon-sender" alt="" width="16" height="16" style="display:block;border:0;outline:none;text-decoration:none;">
            </div>
            <div style="font-size:13px;font-weight:800;color:#111827;margin-bottom:3px;"><?php echo $e($requestCompanyProfileName); ?></div>
            <div style="font-size:11px;color:#6b7280;">발신 기업</div>
          </div>
        </td>

        <td align="center" style="width:6%;padding:0 4px;vertical-align:middle;">
          <img src="cid:trade-icon-arrow" alt="" width="16" height="16" style="display:block;border:0;outline:none;text-decoration:none;">
        </td>

        <td style="width:47%;vertical-align:top;">
          <div style="background:#ffffff;border-radius:12px;padding:16px;border:1px solid #e5e7eb;">
            <div style="width:36px;height:36px;background:#ecfdf5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
              <img src="cid:trade-icon-receiver" alt="" width="16" height="16" style="display:block;border:0;outline:none;text-decoration:none;">
            </div>
            <div style="font-size:13px;font-weight:800;color:#111827;margin-bottom:3px;"><?php echo $e($targetCompanyName); ?></div>
            <div style="font-size:11px;color:#6b7280;">수신 기업</div>
          </div>
        </td>
      </tr>
    </table>

    <div style="background:#ffffff;border-radius:12px;padding:16px 18px;border:1px solid #e5e7eb;margin-bottom:10px;">
      <div style="font-size:12px;font-weight:700;color:#111827;margin-bottom:14px;">발신 기업 정보</div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8a2 2 0 0 0-2 2v2h12V5a2 2 0 0 0-2-2z"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">회사명</span>
        <span style="font-size:13px;color:#374151;font-weight:700;"><?php echo $e($requestCompanyProfileName); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="4" x2="8" y2="9"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">사업자번호</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyBizNo !== '' ? $requestCompanyBizNo : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">설립</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyFounded !== '' ? $requestCompanyFounded : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">고용인원</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyEmployees !== '' ? $requestCompanyEmployees : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">기업유형</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyCorpType !== '' ? $requestCompanyCorpType : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">업종</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyIndustry !== '' ? $requestCompanyIndustry : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:<?php echo $requestCompanyHomepage !== '' ? '1px solid #f3f4f6' : '0'; ?>;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">주요제품</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyProduct !== '' ? $requestCompanyProduct : '—'); ?></span>
      </div>
      <?php if ($requestCompanyHomepage !== '') { ?>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">홈페이지</span>
        <a href="<?php echo $e($requestCompanyHomepage); ?>" style="font-size:13px;color:#3b82f6;text-decoration:none;"><?php echo $e(preg_replace('#^https?://#', '', $requestCompanyHomepage)); ?></a>
      </div>
      <?php } ?>
    </div>

    <div style="background:#ffffff;border-radius:12px;padding:16px 18px;border:1px solid #e5e7eb;">
      <div style="font-size:12px;font-weight:700;color:#111827;margin-bottom:14px;">거래 요청 내용</div>

      <div style="display:flex;align-items:center;gap:8px;padding:10px 0;border-bottom:1px solid #f3f4f6;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:120px;flex-shrink:0;">거래 방향</span>
        <span style="font-size:13px;color:#374151;font-weight:700;"><?php echo $e($tradeDirectionLabel); ?></span>
      </div>

      <div style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          <span style="font-size:11px;color:#9ca3af;font-weight:600;">거래 목적 선택</span>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:6px;padding-left:21px;">
          <?php if (!empty($tradePurposeLabels)) { ?>
            <?php foreach ($tradePurposeLabels as $purposeLabel) { ?>
              <span style="display:inline-block;background:#eff6ff;color:#3b82f6;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;"><?php echo $e($purposeLabel); ?></span>
            <?php } ?>
          <?php } else { ?>
            <span style="display:inline-block;background:#f3f4f6;color:#6b7280;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;">선택 정보 없음</span>
          <?php } ?>
        </div>
      </div>

      <div style="display:flex;align-items:center;gap:8px;padding:10px 0;border-bottom:1px solid #f3f4f6;">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:120px;flex-shrink:0;">거래 희망 품목</span>
        <span style="font-size:13px;color:#374151;font-weight:700;"><?php echo $e($desiredItem); ?></span>
      </div>

      <div style="padding:10px 0 4px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span style="font-size:11px;color:#9ca3af;font-weight:600;">추가 문의 사항</span>
        </div>
        <div style="background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb;padding:12px 14px;margin-bottom:16px;">
          <div style="line-height:1.8;font-size:13px;color:#374151;">
            <div style="filter:blur(1px);opacity:0.85;user-select:none;"><?php echo $e(isset($messagePreviewLines[0]) ? $messagePreviewLines[0] : ''); ?></div>
            <div style="filter:blur(2.5px);opacity:0.6;user-select:none;"><?php echo $e(isset($messagePreviewLines[1]) ? $messagePreviewLines[1] : ''); ?></div>
            <div style="filter:blur(4px);opacity:0.35;user-select:none;"><?php echo $e(isset($messagePreviewLines[2]) ? $messagePreviewLines[2] : ''); ?></div>
          </div>
        </div>
      </div>

      <div style="text-align:center;">
        <a href="<?php echo $e($tradeRequestActionUrl); ?>" style="display:inline-block;background:#3b82f6;color:#ffffff;font-size:13px;font-weight:700;padding:12px 40px;border-radius:10px;text-decoration:none;letter-spacing:0.02em;">채팅하기</a>
        <div style="font-size:11px;color:#6b7280;margin-top:10px;">채팅 연결은 로그인/회원가입 후 가능합니다.</div>
        <div style="font-size:11px;color:#9ca3af;margin-top:10px;">macomaex.com에서 담당자와 직접 소통하세요</div>
      </div>
    </div>

  </td></tr>

  <tr><td style="background:#ffffff;border-top:1px solid #e5e7eb;padding:18px 28px;text-align:center;">
    <div style="font-size:11px;color:#9ca3af;line-height:1.9;">
      본 메일은 귀사와 관련된 기업 간 거래 요청에 따른 안내이며, 광고 목적이 아닙니다.<br>
      해당 요청은 로그인 / 회원가입 후 채팅을 통해 상세 협의가 가능합니다.
    </div>
    <div style="font-size:11px;color:#9ca3af;margin-top:6px;">문의: <a href="mailto:admin@macomaex.com" style="color:#3b82f6;text-decoration:none;">admin@macomaex.com</a></div>
  </td></tr>

</table>
</td></tr>
</table>

</body>
</html>
