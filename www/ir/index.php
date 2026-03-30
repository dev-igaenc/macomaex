<?php
include_once(__DIR__.'/_config.php');

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '/ir/';
    if ($host) {
        header('Location: https://' . $host . $uri);
        exit;
    }
}

$error = '';
$has_access = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['ir_password'] ?? '');
    if ($password !== '' && password_verify($password, IR_PASSWORD_HASH)) {
        $has_access = true;
    } else {
        $error = '비밀번호가 올바르지 않습니다.';
    }
}

if (!$has_access) {
    ?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title><?php echo IR_PAGE_TITLE; ?></title>
    <meta name="robots" content="noindex,nofollow">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f4f6fb; color: #1b1f2a; }
        .ir-wrap { max-width: 520px; margin: 10vh auto; background: #fff; padding: 36px; border-radius: 16px; box-shadow: 0 16px 36px rgba(27,31,42,0.12); }
        .ir-title { font-size: 22px; margin: 0 0 10px; letter-spacing: 0.2px; }
        .ir-subtitle { font-size: 13px; color: #6b7380; margin: 0 0 18px; }
        .ir-divider { height: 1px; background: #e6e9ef; margin: 16px 0 18px; }
        .ir-notice { font-size: 12px; color: #4b5563; line-height: 1.6; background: #f9fafb; border: 1px solid #e6e9ef; padding: 12px 14px; border-radius: 10px; }
        .ir-field { width: 100%; padding: 12px 12px; font-size: 14px; border: 1px solid #d8dde6; border-radius: 10px; background: #fff; color: #1b1f2a; }
        .ir-field::placeholder { color: #9aa3b2; }
        .ir-btn { width: 100%; margin-top: 12px; padding: 12px 12px; font-size: 14px; border: 0; border-radius: 10px; background: #1b1f2a; color: #fff; font-weight: 600; cursor: pointer; }
        .ir-error { margin-top: 12px; color: #d93025; font-size: 13px; }
        .ir-links { margin-top: 16px; display: flex; justify-content: center; gap: 12px; }
        .ir-link { width: 42px; height: 42px; border-radius: 50%; background: #eef1f6; color: #1b1f2a; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; text-decoration: none; letter-spacing: 0.5px; }
        .ir-link:hover { background: #1b1f2a; color: #fff; }
        .ir-footer { margin-top: 12px; font-size: 11px; color: #9aa3b2; text-align: center; }
    </style>
</head>
<body>
    <div class="ir-wrap">
        <h1 class="ir-title"><?php echo IR_PAGE_TITLE; ?></h1>
        <p class="ir-subtitle">투자자 대상 자료 열람 페이지</p>
        <div class="ir-notice">
            본 자료는 사전 승인된 대상자만 열람 가능합니다.<br>
            무단 복제·배포·촬영 및 외부 유출을 금지합니다.
        </div>
        <div class="ir-divider"></div>
        <form method="post" autocomplete="off">
            <input class="ir-field" type="password" name="ir_password" placeholder="비밀번호" required>
            <br>
            <button class="ir-btn" type="submit">입장</button>
        </form>
        <br>
        <?php if ($error): ?>
            <div class="ir-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <div class="ir-links">
            <a class="ir-link" href="http://www.igaenc.com" target="_blank" rel="noopener noreferrer" aria-label="이가이엔씨 공식 사이트">IGA</a>
            <a class="ir-link" href="http://www.mnastation.com" target="_blank" rel="noopener noreferrer" aria-label="M&A STATION">MNA</a>
            <a class="ir-link" href="https://macomaex.com" target="_blank" rel="noopener noreferrer" aria-label="MACOMAEX">MAC</a>
        </div>
        <div class="ir-footer">©2026 이가이엔씨 All rights reserved.</div>
    </div>
</body>
</html>
    <?php
    exit;
}

?>
<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <title><?php echo IR_PAGE_TITLE; ?></title>
    <meta name="robots" content="noindex,nofollow">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #fff; color: #222; }
        .ir-content { max-width: 960px; margin: 60px auto; padding: 0 20px; }
        .ir-content h2 { margin: 0 0 16px; }
    </style>
</head>
<body>
    <section class="ir-content">
        <h2><?php echo IR_PAGE_TITLE; ?></h2>
        <?php if (is_file(IR_CONTENT_FILE)): ?>
            <?php echo file_get_contents(IR_CONTENT_FILE); ?>
        <?php else: ?>
            <p>IR 콘텐츠 파일이 없습니다. `<?php echo basename(IR_CONTENT_FILE); ?>`에 내용을 넣어주세요.</p>
        <?php endif; ?>
    </section>
</body>
</html>

