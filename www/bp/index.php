<?php
include_once(__DIR__ . '/_common.php');

$content_file = __DIR__ . '/content.html';
?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title>BP 페이지</title>
    <meta name="robots" content="noindex,nofollow">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #fff; color: #222; }
        .bp-content { max-width: 960px; margin: 60px auto; padding: 0 20px; }
    </style>
</head>
<body>
    <section class="bp-content">
        <?php if (is_file($content_file)): ?>
            <?php echo file_get_contents($content_file); ?>
        <?php else: ?>
            <h1>BP 페이지</h1>
            <p>`content.html` 파일을 추가하면 이 위치에 내용이 표시됩니다.</p>
        <?php endif; ?>
    </section>
</body>
</html>
