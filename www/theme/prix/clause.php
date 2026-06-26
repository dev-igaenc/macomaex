<?php
if (!defined('_GNUBOARD_')) exit;

$provision = sql_fetch("select co_content from g5_content where co_id='provision'");
echo isset($provision['co_content']) ? $provision['co_content'] : '';
