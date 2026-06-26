<?php
if (!defined('_GNUBOARD_')) exit;

$privacy_consent = sql_fetch("select co_content from g5_content where co_id='privacy_consent'");
$content = isset($privacy_consent['co_content']) ? trim((string)$privacy_consent['co_content']) : '';

if ($content !== '') {
    // Align custom consent content with the existing clause page typography.
    $content = preg_replace('/<h3\b[^>]*>(.*?)<\/h3>/is', '<p class="title">$1</p>', $content, 1);
    $content = preg_replace('/<table\b([^>]*)>(.*?)<\/table>/is', '<div class="nTable"><table$1>$2</table></div>', $content);
}

echo $content;
