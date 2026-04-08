<?php
if (!defined('_GNUBOARD_')) exit;
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>매칭 수락 안내 메일</title>
</head>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
    <div style="border:1px solid #dedede">
        <h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">
            매칭 요청이 수락되었습니다.
        </h1>
        <span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">
            <a href="<?php echo isset($request_site_url) && $request_site_url ? $request_site_url : G5_URL ?>" target="_blank"><?php echo $config['cf_title'] ?></a>
        </span>

        <div style="padding:30px;border-bottom:1px solid #eee;color:#333;line-height:1.7">
            <p style="margin:0 0 18px;">
                안녕하세요<?php if ($request_company_name !== '') { ?>, <b><?php echo htmlspecialchars($request_company_name, ENT_QUOTES, 'UTF-8'); ?></b><?php } ?> 담당자 <?php echo htmlspecialchars($request_name, ENT_QUOTES, 'UTF-8'); ?>님.
            </p>
            <p style="margin:0 0 18px;">
                <?php if ($receive_company_name !== '') { ?><b><?php echo htmlspecialchars($receive_company_name, ENT_QUOTES, 'UTF-8'); ?></b>에서<?php } else { ?>상대 기업에서<?php } ?>
                매칭 요청을 수락했습니다.
            </p>
            <p style="margin:0;">
                아래 내용을 확인하신 뒤 사이트 내 매칭 화면에서 상세 정보를 확인해 주세요.
            </p>
        </div>

        <div style="padding:30px;border-bottom:1px solid #eee;color:#333;">
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;">
                <tr>
                    <th align="left" style="padding:0 0 12px;width:140px;color:#777;font-weight:normal;">수락 기업</th>
                    <td style="padding:0 0 12px;"><b><?php echo htmlspecialchars($receive_company_name !== '' ? $receive_company_name : '-', ENT_QUOTES, 'UTF-8'); ?></b></td>
                </tr>
                <tr>
                    <th align="left" style="padding:0 0 12px;color:#777;font-weight:normal;">수락 일시</th>
                    <td style="padding:0 0 12px;"><?php echo htmlspecialchars($matching_date, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <tr>
                    <th align="left" style="padding:0 0 12px;color:#777;font-weight:normal;">담당자 이름</th>
                    <td style="padding:0 0 12px;"><?php echo htmlspecialchars($receiver_contact_name !== '' ? $receiver_contact_name : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php if ($receiver_contact_position !== '') { ?>
                <tr>
                    <th align="left" style="padding:0 0 12px;color:#777;font-weight:normal;">담당자 직위</th>
                    <td style="padding:0 0 12px;"><?php echo htmlspecialchars($receiver_contact_position, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php } ?>
                <?php if ($receiver_contact_email !== '') { ?>
                <tr>
                    <th align="left" style="padding:0 0 12px;color:#777;font-weight:normal;">담당자 이메일</th>
                    <td style="padding:0 0 12px;"><a href="mailto:<?php echo htmlspecialchars($receiver_contact_email, ENT_QUOTES, 'UTF-8'); ?>" style="color:#333;text-decoration:none;"><?php echo htmlspecialchars($receiver_contact_email, ENT_QUOTES, 'UTF-8'); ?></a></td>
                </tr>
                <?php } ?>
                <?php if ($receiver_contact_phone !== '') { ?>
                <tr>
                    <th align="left" style="padding:0;color:#777;font-weight:normal;">담당자 연락처</th>
                    <td style="padding:0;"><?php echo htmlspecialchars($receiver_contact_phone, ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php } ?>
            </table>
        </div>

        <?php if ($matching_request_message !== '') { ?>
        <div style="padding:30px;border-bottom:1px solid #eee;color:#333;">
            <p style="margin:0 0 12px;color:#777;">보낸 매칭 메시지</p>
            <div style="padding:20px;background:#f7f7f7;"><?php echo nl2br(htmlspecialchars($matching_request_message, ENT_QUOTES, 'UTF-8')); ?></div>
        </div>
        <?php } ?>

        <a href="<?php echo $request_matching_url ?>" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">보낸 매칭 내역 확인하기</a>
    </div>
</div>

</body>
</html>
