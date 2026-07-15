<?php

if (!function_exists('macomaex_member_only_write_board_tables')) {
    function macomaex_member_only_write_board_tables()
    {
        // 고객문의 회원 전용 작성으로 제한
        return array('Contact');
    }
}

if (!function_exists('macomaex_is_member_only_write_board')) {
    function macomaex_is_member_only_write_board($bo_table)
    {
        return in_array((string) $bo_table, macomaex_member_only_write_board_tables(), true);
    }
}

if (!function_exists('macomaex_require_member_for_board_write')) {
    function macomaex_require_member_for_board_write($bo_table, $is_guest, $target_url = '')
    {
        if (!$is_guest || !macomaex_is_member_only_write_board($bo_table)) {
            return;
        }

        if ($target_url === '') {
            $target_url = G5_BBS_URL . '/write.php?bo_table=' . urlencode($bo_table);
        }

        $login_url = G5_BBS_URL . '/login.php?url=' . urlencode($target_url);

        if (function_exists('login_required_modal')) {
            login_required_modal('', $login_url, 'contact_write');
        }

        alert(
            "글을 쓸 권한이 없습니다.\\n회원이시라면 로그인 후 이용해 보십시오.",
            $login_url
        );
    }
}
