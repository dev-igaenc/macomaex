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
    $hasRequestCompanyHomepage = trim($requestCompanyHomepage) !== '';
    $tradeDirectionLabel = isset($tradeDirectionLabel) ? (string)$tradeDirectionLabel : '';
    $tradePurposeLabels = (isset($tradePurposeLabels) && is_array($tradePurposeLabels)) ? $tradePurposeLabels : array();
    $messagePreviewLines = (isset($messagePreviewLines) && is_array($messagePreviewLines)) ? $messagePreviewLines : array();
    $messagePreviewImageUrl = isset($messagePreviewImageUrl) ? trim((string)$messagePreviewImageUrl) : '';
    $mailDateLabel = isset($mailDateLabel) ? (string)$mailDateLabel : date('Y년 m월 d일 H:i');

    // Email clients require absolute image URLs. G5_URL can be relative ('') depending on deployment config.
    $mailBaseUrl = '';
    if (defined('G5_HTTPS_DOMAIN') && trim((string)G5_HTTPS_DOMAIN) !== '') {
        $mailBaseUrl = trim((string)G5_HTTPS_DOMAIN);
    } elseif (defined('G5_DOMAIN') && trim((string)G5_DOMAIN) !== '') {
        $mailBaseUrl = trim((string)G5_DOMAIN);
    } elseif (defined('G5_URL') && preg_match('#^https?://#i', (string)G5_URL)) {
        $mailBaseUrl = trim((string)G5_URL);
    } else {
        $mailBaseUrl = 'https://macomaex.com';
    }
    $mailBaseUrl = rtrim($mailBaseUrl, '/');
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
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
      <tr>
        <td valign="middle" style="font-size:18px;font-weight:800;color:#111827;letter-spacing:-0.02em;">거래 요청이 도착했습니다</td>
        <td valign="middle" align="right" style="font-size:11px;color:#9ca3af;white-space:nowrap;padding-left:12px;"><?php echo $e($mailDateLabel); ?></td>
      </tr>
    </table>
  </td></tr>

  <tr><td style="padding:14px 14px 18px;background:#f9fafb;">

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:10px;">
      <tr>
        <td style="width:47%;vertical-align:top;">
          <div style="background:#ffffff;border-radius:12px;padding:16px;border:1px solid #e5e7eb;">
            <table role="presentation" cellpadding="0" cellspacing="0" style="width:36px;height:36px;background:#eff6ff;border-radius:18px;margin:0 0 10px 0;">
              <tr>
                <td align="center" valign="middle" style="width:36px;height:36px;line-height:0;font-size:0;">
                  <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/organization-t.png" width="16" height="16" alt="" style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;">
                </td>
              </tr>
            </table>
            <div style="font-size:13px;font-weight:800;color:#111827;margin-bottom:3px;"><?php echo $e($requestCompanyProfileName); ?></div>
            <div style="font-size:11px;color:#6b7280;">발신 기업</div>
          </div>
        </td>

        <td align="center" style="width:6%;padding:0 4px;vertical-align:middle;">
            <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/long-arrow-right.png" width="16" height="16" alt="" style="display:block;border:0;outline:none;text-decoration:none;">
        </td>

        <td style="width:47%;vertical-align:top;">
          <div style="background:#ffffff;border-radius:12px;padding:16px;border:1px solid #e5e7eb;">
            <table role="presentation" cellpadding="0" cellspacing="0" style="width:36px;height:36px;background:#ecfdf5;border-radius:18px;margin:0 0 10px 0;">
              <tr>
                <td align="center" valign="middle" style="width:36px;height:36px;line-height:0;font-size:0;">
                  <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/organization-r.png" width="16" height="16" alt="" style="display:block;margin:0 auto;border:0;outline:none;text-decoration:none;">
                </td>
              </tr>
            </table>
            <div style="font-size:13px;font-weight:800;color:#111827;margin-bottom:3px;"><?php echo $e($targetCompanyName); ?></div>
            <div style="font-size:11px;color:#6b7280;">수신 기업</div>
          </div>
        </td>
      </tr>
    </table>

    <div style="background:#ffffff;border-radius:12px;padding:16px 18px;border:1px solid #e5e7eb;margin-bottom:10px;">
      <div style="font-size:12px;font-weight:700;color:#111827;margin-bottom:14px;">발신 기업 정보</div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/briefcase.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">회사명</span>
        <span style="font-size:13px;color:#374151;font-weight:700;"><?php echo $e($requestCompanyProfileName); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/identification-documents.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">사업자번호</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyBizNo !== '' ? $requestCompanyBizNo : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/calendar--v1.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">설립</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyFounded !== '' ? $requestCompanyFounded : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/conference-call.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">고용인원</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyEmployees !== '' ? $requestCompanyEmployees : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/layers.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">기업유형</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyCorpType !== '' ? $requestCompanyCorpType : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/price-tag.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">업종</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyIndustry !== '' ? $requestCompanyIndustry : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/package.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">주요제품</span>
        <span style="font-size:13px;color:#374151;"><?php echo $e($requestCompanyProduct !== '' ? $requestCompanyProduct : '—'); ?></span>
      </div>
      <div style="display:flex;align-items:center;gap:8px;padding:9px 0;">
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/globe--v1.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:80px;flex-shrink:0;">홈페이지</span>
        <?php if ($hasRequestCompanyHomepage) { ?>
        <a href="<?php echo $e($requestCompanyHomepage); ?>" style="font-size:13px;color:#3b82f6;text-decoration:none;"><?php echo $e(preg_replace('#^https?://#', '', $requestCompanyHomepage)); ?></a>
        <?php } else { ?>
        <span style="font-size:13px;color:#374151;">정보 준비중</span>
        <?php } ?>
      </div>
    </div>

    <div style="background:#ffffff;border-radius:12px;padding:16px 18px;border:1px solid #e5e7eb;">
      <div style="font-size:12px;font-weight:700;color:#111827;margin-bottom:14px;">거래 요청 내용</div>

      <div style="display:flex;align-items:center;gap:8px;padding:10px 0;border-bottom:1px solid #f3f4f6;">
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/long-arrow-right.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:120px;flex-shrink:0;">거래 방향</span>
        <span style="font-size:13px;color:#374151;font-weight:700;"><?php echo $e($tradeDirectionLabel); ?></span>
      </div>

      <div style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
          <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/clock--v1.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
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
        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/monitor--v1.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
        <span style="font-size:11px;color:#9ca3af;font-weight:600;width:120px;flex-shrink:0;">거래 희망 품목</span>
        <span style="font-size:13px;color:#374151;font-weight:700;"><?php echo $e($desiredItem); ?></span>
      </div>

      <div style="padding:10px 0 4px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
          <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/topic.png" width="13" height="13" alt="" style="display:inline-block;vertical-align:middle;margin-right:8px;border:0;outline:none;text-decoration:none;">
          <span style="font-size:11px;color:#9ca3af;font-weight:600;">추가 문의 사항</span>
        </div>
        <div style="background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb;padding:12px 14px;margin-bottom:16px;">
          <?php if ($messagePreviewImageUrl !== '') { ?>
          <img src="<?php echo $e($messagePreviewImageUrl); ?>" width="460" alt="문의 내용 미리보기" style="display:block;width:100%;max-width:460px;height:auto;border:0;outline:none;text-decoration:none;">
          <?php } else { ?>
          <div style="line-height:1.8;font-size:13px;color:#9ca3af;">
            <div style="height:14px;background:#e5e7eb;border-radius:7px;margin:2px 0 8px;max-width:92%;"></div>
            <div style="height:14px;background:#e5e7eb;border-radius:7px;margin:2px 0 8px;max-width:82%;"></div>
            <div style="height:14px;background:#e5e7eb;border-radius:7px;margin:2px 0;max-width:76%;"></div>
          </div>
          <?php } ?>
        </div>
      </div>

      <div style="text-align:center;">
        <a href="<?php echo $e($tradeRequestActionUrl); ?>" style="display:inline-block;background:#3b82f6;color:#ffffff;font-size:13px;font-weight:700;padding:12px 40px;border-radius:10px;text-decoration:none;letter-spacing:0.02em;">채팅으로 대화하기</a>
        <div style="font-size:11px;color:#9ca3af;margin-top:10px;">macomaex.com에서 담당자와 직접 소통하세요</div>
      </div>
    </div>

  </td></tr>

    <tr>
        <td style="background:#ffffff;border-top:1px solid #e5e7eb;padding:28px 28px 24px;">
            <div style="text-align:center;">
                <div style="font-size:11px;color:#9ca3af;line-height:1.8;">
                    (주)이가이엔씨가 운영하는 매커멕스(macomaex.com)에서<br>
                    회원사의 거래 요청을 대신 전달한 메일입니다.
                </div>
                <div style="margin-top:14px;">
                    <a href="https://macomaex.com" style="font-size:11px;color:#6b7280;text-decoration:none;padding:0 10px;" target="_blank">매커멕스 홈페이지</a>
                    <span style="display:inline-block;width:1px;height:10px;background:#e5e7eb;vertical-align:middle;"></span>
                    <a href="https://macomaex.com/unsubscribe?email=sample@company.com" style="font-size:11px;color:#6b7280;text-decoration:none;padding:0 10px;" target="_blank">수신거부</a>
                </div>
            </div>
            <div style="height:24px;"></div>
            <div style="text-align:center;font-size:10px;color:#b0b6bf;line-height:1.9;letter-spacing:0.01em;">
                <div>
                    <span style="color:#9ca3af;font-size:10px;letter-spacing:0.08em;margin-right:6px;">운영사</span>&nbsp;<span style="color:#374151;font-weight:600;font-size:11px;">(주)이가이엔씨</span>
                </div>
                <div style="margin-top:6px;">
                사업자등록번호 186-81-01689
                <span style="color:#e5e7eb;margin:0 6px;">·</span>
                통신판매업 제2026-서울강남-02512호
                </div>
                <div>
                서울시 강남구 테헤란로 138 성홍타워 413, 415호
                </div>
                <div>
                02-573-8777
                <span style="color:#e5e7eb;margin:0 6px;">·</span>
                <a href="http://www.igaenc.com" style="color:#b0b6bf;text-decoration:none;" target="_blank">www.igaenc.com</a>
                </div>
            </div>
            <div style="text-align:center;margin-top:20px;">
                <div style="font-size:10px;color:#d1d5db;letter-spacing:0.04em;">© 2026 IGAENC Co., Ltd.</div>
            </div>
        </td>
    </tr>

</table>
</td></tr>
</table>

</body>
</html>
