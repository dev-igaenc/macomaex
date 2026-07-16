<?php
if (!defined('_GNUBOARD_')) exit;

$privacy = sql_fetch("select co_content from g5_content where co_id='privacy_consent'");
echo isset($privacy['co_content']) ? $privacy['co_content'] : '';
