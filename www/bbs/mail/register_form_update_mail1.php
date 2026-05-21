<?php
// 회원가입축하 메일 (회원님께 발송)
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$company_name = '';
$mail_mb_id = isset($mb_id) ? trim((string)$mb_id) : '';
if ($mail_mb_id !== '') {
    $mail_mb_id_sql = sql_escape_string($mail_mb_id);
    $company_row = sql_fetch("
        SELECT C.ENP_NM
        FROM {$g5['member_table']} M
        LEFT JOIN company C ON C.KEDCD = M.mb_company
        WHERE M.mb_id = '{$mail_mb_id_sql}'
        LIMIT 1
    ");
    $company_name = trim((string)($company_row['ENP_NM'] ?? ''));
}
if ($company_name === '') $company_name = isset($mb_name) ? trim((string)$mb_name) : '회원';

$mailBaseUrl = '';
if (defined('G5_HTTPS_DOMAIN') && trim((string)G5_HTTPS_DOMAIN) !== '') {
    $mailBaseUrl = trim((string)G5_HTTPS_DOMAIN);
} else if (defined('G5_DOMAIN') && trim((string)G5_DOMAIN) !== '') {
    $mailBaseUrl = trim((string)G5_DOMAIN);
} else if (defined('G5_URL') && trim((string)G5_URL) !== '') {
    $mailBaseUrl = trim((string)G5_URL);
} else {
    $mailBaseUrl = 'https://macomaex.com';
}
$mailBaseUrl = rtrim($mailBaseUrl, '/');
?>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>가입을 환영합니다 — 매커멕스</title>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="padding:44px 0 60px;">
<tbody><tr><td align="center">

<!-- 메인 래퍼 카드 -->
<table width="560" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:16px;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,0.04),0 8px 24px rgba(0,0,0,0.06);">

  <!-- ── 헤더 ── -->
  <tbody><tr>
    <td style="padding:26px 28px 22px;border-bottom:1px solid #e5e7eb;">
      <div style="font-size:10px;font-weight:700;color:#3b82f6;letter-spacing:0.2em;text-transform:uppercase;margin-bottom:8px;">MACOMAEX · B2B 거래 플랫폼</div>
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tbody><tr>
          <td style="vertical-align:middle;">
            <table cellpadding="0" cellspacing="0" border="0">
              <tbody><tr>
                <td width="22" style="vertical-align:middle;padding-right:8px;">
                  <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/icon-check.png" width="22" height="22" alt="" style="display:block;border:0;outline:none;text-decoration:none;">
                </td>
                <td style="vertical-align:middle;font-size:18px;font-weight:800;color:#111827;letter-spacing:-0.02em;">가입을 환영합니다</td>
              </tr>
            </tbody></table>
          </td>
          <td align="right" style="vertical-align:middle;font-size:11px;color:#9ca3af;white-space:nowrap;"><?php echo date('Y년 m월 d일'); ?></td>
        </tr>
      </tbody></table>
    </td>
  </tr>

  <!-- ── 바디 ── -->
  <tr>
    <td style="padding:14px 14px 18px;background:#f9fafb;">

      <!-- 카드 1 : 환영 메시지 -->
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;border-radius:12px;border:1px solid #e5e7eb;margin-bottom:10px;">
        <tbody><tr>
          <td style="padding:20px 18px 18px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tbody><tr>
                <td width="44" style="vertical-align:top;padding-right:14px;">
                  <table width="44" cellpadding="0" cellspacing="0" border="0">
                    <tbody><tr>
                      <td align="center" width="44" height="44" style="background:#eff6ff;border-radius:50%;">
                        <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/icon-user.png" width="20" height="20" alt="" style="display:block;border:0;outline:none;text-decoration:none;">
                      </td>
                    </tr>
                  </tbody></table>
                </td>
                <td style="vertical-align:top;">
                  <div style="font-size:14px;font-weight:800;color:#111827;margin-bottom:6px;"><?php echo $company_name; ?> 담당자님, 반갑습니다!</div>
                  <div style="font-size:13px;color:#6b7280;line-height:1.8;">
                    매커멕스(MACOMAEX)에 가입해 주셔서 감사합니다.<br />
                    국내 B2B 거래의 새로운 기준, 지금 바로 시작해 보세요.
                  </div>
                </td>
              </tr>
            </tbody></table>
          </td>
        </tr>
      </tbody></table>

      <!-- 카드 2 : 주요 기능 -->
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;border-radius:12px;border:1px solid #e5e7eb;margin-bottom:10px;">
        <tbody><tr>
          <td style="padding:16px 18px 6px;">
            <div style="font-size:12px;font-weight:700;color:#111827;margin-bottom:14px;">매커멕스에서 할 수 있는 것들</div>

            <!-- 기능 1 -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tbody><tr>
                <td style="padding-bottom:12px;border-bottom:1px solid #f3f4f6;">
                  <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tbody><tr>
                      <td width="32" style="vertical-align:top;padding-right:12px;">
                        <table width="32" cellpadding="0" cellspacing="0" border="0">
                          <tbody><tr>
                            <td align="center" width="32" height="32" style="background:#eff6ff;border-radius:8px;">
                              <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/icon-search.png" width="14" height="14" alt="" style="display:block;border:0;outline:none;text-decoration:none;">
                            </td>
                          </tr>
                        </tbody></table>
                      </td>
                      <td style="vertical-align:top;padding-top:4px;">
                        <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:3px;">AI 거래처 탐색</div>
                        <div style="font-size:12px;color:#6b7280;line-height:1.6;">AI가 우리 기업과 딱 맞는 거래처를 분석·추천해드립니다.</div>
                      </td>
                    </tr>
                  </tbody></table>
                </td>
              </tr>
              <!-- 기능 2 -->
              <tr>
                <td style="padding:12px 0;border-bottom:1px solid #f3f4f6;">
                  <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tbody><tr>
                      <td width="32" style="vertical-align:top;padding-right:12px;">
                        <table width="32" cellpadding="0" cellspacing="0" border="0">
                          <tbody><tr>
                            <td align="center" width="32" height="32" style="background:#f3e8ff;border-radius:8px;">
                              <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/icon-chat.png" width="16" height="16" alt="" style="display:block;border:0;outline:none;text-decoration:none;">
                            </td>
                          </tr>
                        </tbody></table>
                      </td>
                      <td style="vertical-align:top;padding-top:4px;">
                        <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:3px;">채팅으로 직접 거래 요청</div>
                        <div style="font-size:12px;color:#6b7280;line-height:1.6;">관심 거래처에 채팅 메시지로 거래 의향을 바로 전달하세요.</div>
                      </td>
                    </tr>
                  </tbody></table>
                </td>
              </tr>
              <!-- 기능 3 -->
              <tr>
                <td style="padding:12px 0 6px;">
                  <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tbody><tr>
                      <td width="32" style="vertical-align:top;padding-right:12px;">
                        <table width="32" cellpadding="0" cellspacing="0" border="0">
                          <tbody><tr>
                            <td align="center" width="32" height="32" style="background:#fef3c7;border-radius:8px;">
                              <img src="<?php echo $mailBaseUrl; ?>/bbs/mail/icon/icon-chart.png" width="14" height="14" alt="" style="display:block;border:0;outline:none;text-decoration:none;">
                            </td>
                          </tr>
                        </tbody></table>
                      </td>
                      <td style="vertical-align:top;padding-top:4px;">
                        <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:3px;">기업 분석 리포트</div>
                        <div style="font-size:12px;color:#6b7280;line-height:1.6;">거래 전 상대 기업의 신용·업황을 한눈에 확인하세요.</div>
                      </td>
                    </tr>
                  </tbody></table>
                </td>
              </tr>
            </tbody></table>
          </td>
        </tr>
      </tbody></table>

      <!-- 카드 3 : CTA -->
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#ffffff;border-radius:12px;border:1px solid #e5e7eb;">
        <tbody><tr>
          <td style="padding:20px 18px;" align="center">
            <div style="font-size:13px;color:#6b7280;margin-bottom:16px;line-height:1.8;">
              지금 바로 거래처를 찾아보세요.<br />
              매커멕스가 최적의 파트너를 연결해 드립니다.
            </div>
            <a href="https://macomaex.com" style="display:inline-block;background:#3b82f6;color:#ffffff;font-size:13px;font-weight:700;padding:12px 40px;border-radius:10px;text-decoration:none;letter-spacing:0.02em;" target="_blank">거래처 탐색 시작하기</a>
            <div style="margin-top:14px;">
              <span style="display:inline-block;background:#eff6ff;color:#3b82f6;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;margin:2px 3px;">AI 추천</span><!--
              --><span style="display:inline-block;background:#eff6ff;color:#3b82f6;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;margin:2px 3px;">실시간 채팅</span><!--
              --><span style="display:inline-block;background:#eff6ff;color:#3b82f6;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;margin:2px 3px;">기업 분석</span>
            </div>
            <div style="font-size:11px;color:#9ca3af;margin-top:10px;">macomaex.com에서 B2B 거래를 시작하세요</div>
          </td>
        </tr>
      </tbody></table>

    </td>
  </tr>

  <!-- ── 푸터 ── -->
  <tr>
    <td style="background:#ffffff;border-top:1px solid #e5e7eb;padding:28px 28px 24px;">

      <div style="text-align:center;">
        <div style="font-size:11px;color:#9ca3af;line-height:1.8;">
          본 메일은 매커멕스(macomaex.com) 회원 가입 시 발송되는 안내 메일입니다.
        </div>
        <div style="font-size:11px;color:#9ca3af;margin-top:6px;">문의: <a href="mailto:admin@macomaex.com" style="color:#3b82f6;text-decoration:none;" target="_blank">admin@macomaex.com</a></div>
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
        <div>서울시 강남구 테헤란로 138 성홍타워 413, 415호</div>
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

</tbody></table>
</td></tr>
</tbody></table><img src="https://webmail-129.cafe24.com/check_receive.php?idx=766976" width="0" height="0" border="0">
</blockquote>
