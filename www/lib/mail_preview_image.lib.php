<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('mail_preview_resolve_base_url')) {
    function mail_preview_resolve_base_url($fallback = 'https://macomaex.com')
    {
        if (defined('G5_HTTPS_DOMAIN') && trim((string) G5_HTTPS_DOMAIN) !== '') {
            return rtrim(trim((string) G5_HTTPS_DOMAIN), '/');
        }

        if (defined('G5_DOMAIN') && trim((string) G5_DOMAIN) !== '') {
            return rtrim(trim((string) G5_DOMAIN), '/');
        }

        if (defined('G5_URL') && preg_match('#^https?://#i', (string) G5_URL)) {
            return rtrim(trim((string) G5_URL), '/');
        }

        return rtrim((string) $fallback, '/');
    }
}

if (!function_exists('mail_preview_pick_font_path')) {
    function mail_preview_pick_font_path()
    {
        $candidates = array();

        if (function_exists('macomaex_env')) {
            $envFont = trim((string) macomaex_env('MAIL_PREVIEW_FONT_PATH', ''));
            if ($envFont !== '') {
                $candidates[] = $envFont;
            }
        }

        $candidates = array_merge($candidates, array(
            '/usr/share/fonts/truetype/nanum/NanumGothic.ttf',
            '/usr/share/fonts/truetype/noto/NotoSansCJK-Regular.ttc',
            '/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc',
            '/System/Library/Fonts/AppleSDGothicNeo.ttc',
            '/Library/Fonts/AppleGothic.ttf',
        ));

        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return '';
    }
}

if (!function_exists('mail_preview_apply_blur')) {
    function mail_preview_apply_blur($image, $passes)
    {
        $passes = max(0, (int) $passes);
        for ($i = 0; $i < $passes; $i++) {
            imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
        }
    }
}

if (!function_exists('mail_preview_apply_pixelate')) {
    function mail_preview_apply_pixelate($image, $blockSize)
    {
        $blockSize = max(2, (int) $blockSize);
        if (function_exists('imagefilter') && defined('IMG_FILTER_PIXELATE')) {
            @imagefilter($image, IMG_FILTER_PIXELATE, $blockSize, true);
        }
    }
}

if (!function_exists('mail_preview_generate_trade_request_blur_image')) {
    function mail_preview_generate_trade_request_blur_image($lines, $baseUrl, $options = array())
    {
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor') || !function_exists('imagettftext')) {
            return array('url' => '', 'path' => '', 'error' => 'gd_or_freetype_unavailable');
        }

        $fontPath = mail_preview_pick_font_path();
        if ($fontPath === '') {
            return array('url' => '', 'path' => '', 'error' => 'font_not_found');
        }

        if (!is_array($lines)) {
            $lines = array();
        }

        $cleanLines = array();
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            if (function_exists('mb_strimwidth')) {
                $line = mb_strimwidth($line, 0, 64, '…', 'UTF-8');
            } else {
                $line = substr($line, 0, 64);
            }
            $cleanLines[] = $line;
        }

        if (empty($cleanLines)) {
            return array('url' => '', 'path' => '', 'error' => 'empty_lines');
        }

        if (!is_array($options)) {
            $options = array();
        }

        $style = trim((string) (isset($options['style']) ? $options['style'] : ''));
        if ($style === '') {
            $style = 'default';
        }

        $width = 460;
        $height = 92;
        $fontSize = 13;
        $x = 12;
        $yList = array(24, 52, 80);
        $opacityList = array(0.50, 0.35, 0.25);
        $blurPassList = array(6, 9, 12);
        $pixelateBase = 10;
        $pixelateStep = 2;
        $veilAlpha = 44;
        $gradientFromY = 30;
        $gradientToY = 90;
        $baseColor = array(55, 65, 81);
        $lineCount = min(3, count($cleanLines));
        if ($lineCount < 1) {
            return array('url' => '', 'path' => '', 'error' => 'empty_lines');
        }

        if ($style === 'gradient_reveal') {
            // 이 스타일은 "블러 전용(아래 줄)" 이미지를 그린다.
            $lineCount = min(2, count($cleanLines));
            if ($lineCount < 1) {
                return array('url' => '', 'path' => '', 'error' => 'empty_lines');
            }
            $cleanLines = array_slice($cleanLines, 0, $lineCount);
            $height = 64;
            $yList = array(24, 52);
            $opacityList = array(0.45, 0.28);
            $blurPassList = array(3, 6);
            $pixelateBase = 0;
            $pixelateStep = 0;
            $veilAlpha = 12;
            $gradientFromY = 18;
            $gradientToY = 63;
        } else {
            $cleanLines = array_slice($cleanLines, 0, $lineCount);
            while (count($cleanLines) < 3) {
                $cleanLines[] = end($cleanLines);
            }
            $lineCount = 3;
        }

        $hashSource = json_encode(array(
            'v' => 6,
            'font' => $fontPath,
            'lines' => $cleanLines,
            'style' => $style,
            'w' => $width,
            'h' => $height,
            'fs' => $fontSize,
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $hash = sha1((string) $hashSource);
        $relativeDir = '/mail_preview/trade_request';
        $targetDir = rtrim((string) G5_DATA_PATH, '/') . $relativeDir;
        $targetPath = $targetDir . '/' . $hash . '.png';

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }
        if (!is_dir($targetDir)) {
            return array('url' => '', 'path' => $targetPath, 'error' => 'target_dir_create_failed');
        }
        if (!is_writable($targetDir)) {
            return array('url' => '', 'path' => $targetPath, 'error' => 'target_dir_not_writable');
        }

        if (is_file($targetPath) && is_readable($targetPath)) {
            return array(
                'url' => rtrim((string) $baseUrl, '/') . '/data' . $relativeDir . '/' . $hash . '.png',
                'path' => $targetPath,
                'error' => '',
            );
        }

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);

        imagealphablending($canvas, true);
        for ($i = 0; $i < $lineCount; $i++) {
            $line = $cleanLines[$i];
            $lineLayer = imagecreatetruecolor($width, $height);
            imagealphablending($lineLayer, false);
            imagesavealpha($lineLayer, true);
            $layerTransparent = imagecolorallocatealpha($lineLayer, 255, 255, 255, 127);
            imagefilledrectangle($lineLayer, 0, 0, $width, $height, $layerTransparent);

            imagealphablending($lineLayer, true);
            $alpha = (int) round(127 * (1 - $opacityList[$i]));
            $textColor = imagecolorallocatealpha($lineLayer, $baseColor[0], $baseColor[1], $baseColor[2], $alpha);
            imagettftext($lineLayer, $fontSize, 0, $x, $yList[$i], $textColor, $fontPath, $line);

            if ($blurPassList[$i] > 0) {
                mail_preview_apply_blur($lineLayer, $blurPassList[$i]);
            }
            if ($pixelateBase > 0) {
                // 메일 클라이언트 호환을 위해 CSS blur 의존 대신 이미지 자체를 픽셀화한다.
                mail_preview_apply_pixelate($lineLayer, $pixelateBase + ($i * $pixelateStep));
            }
            imagecopy($canvas, $lineLayer, 0, 0, 0, 0, $width, $height);
            imagedestroy($lineLayer);
        }

        // 최종 베일 오버레이로 원문 판독성을 추가로 낮춘다.
        $veil = imagecolorallocatealpha($canvas, 255, 255, 255, $veilAlpha);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $veil);

        if ($style === 'gradient_reveal') {
            // 하단으로 갈수록 안개가 두꺼워지는 느낌을 준다.
            for ($y = $gradientFromY; $y <= $gradientToY; $y++) {
                $ratio = ($y - $gradientFromY) / max(1, ($gradientToY - $gradientFromY));
                $lineAlpha = (int) round(122 - (46 * $ratio));
                if ($lineAlpha < 0) $lineAlpha = 0;
                if ($lineAlpha > 127) $lineAlpha = 127;
                $mist = imagecolorallocatealpha($canvas, 255, 255, 255, $lineAlpha);
                imageline($canvas, 0, $y, $width, $y, $mist);
            }
        }

        $saved = @imagepng($canvas, $targetPath);
        imagedestroy($canvas);

        if (!$saved) {
            return array('url' => '', 'path' => '', 'error' => 'image_save_failed');
        }

        return array(
            'url' => rtrim((string) $baseUrl, '/') . '/data' . $relativeDir . '/' . $hash . '.png',
            'path' => $targetPath,
            'error' => '',
        );
    }
}
