<?php
if (!defined('_GNUBOARD_')) exit;
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>거래 요청 도착 안내</title>
</head>
<body>
<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
	<div style="border:1px solid #dedede">
		<h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">
			거래 요청이 도착했습니다.
		</h1>
		<span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">
			<a href="<?php echo G5_URL; ?>" target="_blank"><?php echo $config['cf_title']; ?></a>
		</span>

		<div style="padding:30px;border-bottom:1px solid #eee;color:#333;line-height:1.7">
			<p style="margin:0 0 14px;">
				안녕하세요, <b><?php echo htmlspecialchars($receiverName, ENT_QUOTES, 'UTF-8'); ?></b>님.
			</p>
			<p style="margin:0;">
				<b><?php echo htmlspecialchars($requestCompanyName, ENT_QUOTES, 'UTF-8'); ?></b> 기업에서 거래 요청을 보냈습니다.
			</p>
		</div>

		<div style="padding:30px;border-bottom:1px solid #eee;color:#333;">
			<table cellpadding="0" cellspacing="0" border="0" style="width:100%;border-collapse:collapse;">
				<tr>
					<th align="left" style="padding:0 0 12px;width:140px;color:#777;font-weight:normal;">요청 기업</th>
					<td style="padding:0 0 12px;"><b><?php echo htmlspecialchars($requestCompanyName, ENT_QUOTES, 'UTF-8'); ?></b></td>
				</tr>
				<tr>
					<th align="left" style="padding:0 0 12px;color:#777;font-weight:normal;">거래 방향</th>
					<td style="padding:0 0 12px;"><?php echo htmlspecialchars($tradeDirectionLabel, ENT_QUOTES, 'UTF-8'); ?></td>
				</tr>
				<tr>
					<th align="left" style="padding:0 0 12px;color:#777;font-weight:normal;">거래 희망 품목</th>
					<td style="padding:0 0 12px;"><?php echo nl2br(htmlspecialchars($desiredItem, ENT_QUOTES, 'UTF-8')); ?></td>
				</tr>
				<tr>
					<th align="left" style="padding:0;color:#777;font-weight:normal;">거래 타입</th>
					<td style="padding:0;"><?php echo htmlspecialchars($tradeTypeLabel, ENT_QUOTES, 'UTF-8'); ?></td>
				</tr>
			</table>
		</div>

		<?php if ($messageContent !== '') { ?>
		<div style="padding:30px;border-bottom:1px solid #eee;color:#333;">
			<p style="margin:0 0 12px;color:#777;">추가 문의 사항</p>
			<div style="padding:20px;background:#f7f7f7;"><?php echo nl2br(htmlspecialchars($messageContent, ENT_QUOTES, 'UTF-8')); ?></div>
		</div>
		<?php } ?>

		<a href="<?php echo $tradeRequestActionUrl; ?>" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">
			마코마엑스 접속하기
		</a>
	</div>
</div>
</body>
</html>
