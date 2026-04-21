<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_LIB_PATH.'/mailer.lib.php');

if (!function_exists('matching_mail_pick_email')) {
    function matching_mail_pick_email($member_row)
    {
        if (!is_array($member_row)) {
            return '';
        }

        $candidates = array();
        $candidates[] = isset($member_row['mb_5']) ? trim((string)$member_row['mb_5']) : '';
        $candidates[] = isset($member_row['mb_email']) ? trim((string)$member_row['mb_email']) : '';
        $candidates[] = isset($member_row['mb_id']) ? trim((string)$member_row['mb_id']) : '';

        foreach ($candidates as $email) {
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return '';
    }
}

if (!function_exists('matching_mail_pick_login_email')) {
    function matching_mail_pick_login_email($member_row)
    {
        if (!is_array($member_row)) {
            return '';
        }

        $candidates = array();
        $candidates[] = isset($member_row['mb_id']) ? trim((string)$member_row['mb_id']) : '';
        $candidates[] = isset($member_row['mb_email']) ? trim((string)$member_row['mb_email']) : '';

        foreach ($candidates as $email) {
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        return '';
    }
}

if (!function_exists('matching_mail_pick_name')) {
    function matching_mail_pick_name($member_row)
    {
        if (!is_array($member_row)) {
            return '회원';
        }

        $candidates = array();
        $candidates[] = isset($member_row['mb_6']) ? trim((string)$member_row['mb_6']) : '';
        $candidates[] = isset($member_row['mb_name']) ? trim((string)$member_row['mb_name']) : '';
        $candidates[] = isset($member_row['mb_nick']) ? trim((string)$member_row['mb_nick']) : '';

        foreach ($candidates as $name) {
            if ($name !== '') {
                return $name;
            }
        }

        return '회원';
    }
}

if (!function_exists('matching_mail_company_name')) {
    function matching_mail_company_name($company_row, $member_row)
    {
        $candidates = array();
        $candidates[] = isset($company_row['ENP_NM']) ? trim((string)$company_row['ENP_NM']) : '';
        $candidates[] = isset($member_row['mb_name']) ? trim((string)$member_row['mb_name']) : '';

        foreach ($candidates as $name) {
            if ($name !== '') {
                return $name;
            }
        }

        return '';
    }
}

if (!function_exists('matching_mail_log_failure')) {
    function matching_mail_log_failure($mail_type, $related_id, $recipient_email, $subject, $error_message)
    {
        if (!function_exists('mailer_log_write')) {
            return false;
        }

        return mailer_log_write(array(
            'mail_type' => (string) $mail_type,
            'related_id' => (string) $related_id
        ), $recipient_email, $subject, 'FAIL', $error_message);
    }
}

if (!function_exists('send_matching_accept_mail')) {
    function send_matching_accept_mail($matching_no)
    {
        global $config;
        global $g5;

        $matching_no = (int) $matching_no;
        if ($matching_no < 1) {
            return false;
        }

        $matching = sql_fetch(" select * from g5_matching where no = '{$matching_no}' and del != 'D' ");
        if (!$matching || $matching['receive_approval'] !== 'Y') {
            return false;
        }

        $request_log_context = array(
            'mail_type' => 'matching_accept_requester',
            'related_id' => (string) $matching_no
        );
        $receive_log_context = array(
            'mail_type' => 'matching_accept_receiver',
            'related_id' => (string) $matching_no
        );

        if (function_exists('mailer_prepare_tracking_context')) {
            $request_log_context = mailer_prepare_tracking_context(1, $request_log_context);
            $receive_log_context = mailer_prepare_tracking_context(1, $receive_log_context);
        }

        $request_member = sql_fetch(" select * from {$g5['member_table']} where mb_id = '".sql_real_escape_string($matching['request_id'])."' ");
        $receive_member = sql_fetch(" select * from {$g5['member_table']} where mb_id = '".sql_real_escape_string($matching['receive_id'])."' ");

        if (!$request_member || !$receive_member) {
            error_log('[matching_mail] member lookup failed for matching '.$matching_no);
            $fail_subject = '['.$config['cf_title'].'] 매칭 수락 메일 발송 실패';
            matching_mail_log_failure('matching_accept_requester', $matching_no, '', $fail_subject, '요청자 또는 수락자 회원 정보를 찾을 수 없습니다.');
            matching_mail_log_failure('matching_accept_receiver', $matching_no, '', $fail_subject, '요청자 또는 수락자 회원 정보를 찾을 수 없습니다.');
            return false;
        }

        $request_company = array();
        if (!empty($request_member['mb_company'])) {
            $request_company = sql_fetch(" select * from company where KEDCD = '".sql_real_escape_string($request_member['mb_company'])."' ");
        }

        $receive_company = array();
        if (!empty($matching['KEDCD'])) {
            $receive_company = sql_fetch(" select * from company where KEDCD = '".sql_real_escape_string($matching['KEDCD'])."' ");
        }

        $request_name = matching_mail_pick_name($request_member);
        $request_company_name = matching_mail_company_name($request_company, $request_member);
        $receive_company_name = matching_mail_company_name($receive_company, $receive_member);
        $request_contact_name = isset($request_member['mb_6']) && trim($request_member['mb_6']) !== '' ? trim($request_member['mb_6']) : matching_mail_pick_name($request_member);
        $request_contact_position = isset($request_member['mb_7']) ? trim((string)$request_member['mb_7']) : '';
        $request_contact_email = matching_mail_pick_email($request_member);
        $request_contact_phone = isset($request_member['mb_8']) ? trim((string)$request_member['mb_8']) : '';
        $receiver_contact_name = isset($receive_member['mb_6']) && trim($receive_member['mb_6']) !== '' ? trim($receive_member['mb_6']) : matching_mail_pick_name($receive_member);
        $receiver_contact_position = isset($receive_member['mb_7']) ? trim((string)$receive_member['mb_7']) : '';
        $receiver_contact_email = matching_mail_pick_email($receive_member);
        $receiver_contact_phone = isset($receive_member['mb_8']) ? trim((string)$receive_member['mb_8']) : '';
        $matching_request_message = isset($matching['contents']) ? trim((string)$matching['contents']) : '';
        $matching_date = isset($matching['receive_approval_date']) && $matching['receive_approval_date'] ? $matching['receive_approval_date'] : G5_TIME_YMDHIS;
        $request_track_token = isset($request_log_context['track_token']) ? trim((string) $request_log_context['track_token']) : '';
        $receive_track_token = isset($receive_log_context['track_token']) ? trim((string) $receive_log_context['track_token']) : '';
        $request_site_url = G5_URL;
        $receive_site_url = G5_URL;
        $request_matching_url = G5_URL.'/ko/my_matching.php#sent';
        $receive_matching_url = G5_URL.'/ko/my_matching.php#received';

        if (function_exists('mailer_tracking_click_url')) {
            if ($request_track_token !== '') {
                $request_site_url = mailer_tracking_click_url($request_track_token, G5_URL);
                $request_matching_url = mailer_tracking_click_url($request_track_token, $request_matching_url);
            }

            if ($receive_track_token !== '') {
                $receive_site_url = mailer_tracking_click_url($receive_track_token, G5_URL);
                $receive_matching_url = mailer_tracking_click_url($receive_track_token, $receive_matching_url);
            }
        }

        $subject_target = $receive_company_name !== '' ? $receive_company_name : '매칭 요청';
        $request_subject = '['.$config['cf_title'].'] '.$subject_target.' 매칭 요청이 수락되었습니다.';

        ob_start();
        include G5_BBS_PATH.'/matching_accept_mail.php';
        $request_content = ob_get_clean();

        $to_email = matching_mail_pick_login_email($request_member);
        $request_mail_sent = false;
        if ($to_email !== '') {
            $request_mail_sent = mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $to_email, $request_subject, $request_content, 1, "", "", "", $request_log_context);
        } else {
            error_log('[matching_mail] request member login email missing for matching '.$matching_no);
            matching_mail_log_failure('matching_accept_requester', $matching_no, '', $request_subject, '요청자 로그인 이메일이 없습니다.');
        }

        $receive_to_email = matching_mail_pick_login_email($receive_member);
        $receive_mail_sent = false;

        if ($receive_to_email !== '') {
            $receiver_name = $receiver_contact_name;
            $receive_subject_target = $request_company_name !== '' ? $request_company_name : '매칭 요청';
            $receive_subject = '['.$config['cf_title'].'] '.$receive_subject_target.' 매칭 요청을 수락했습니다.';

            ob_start();
            include G5_BBS_PATH.'/matching_accept_mail2.php';
            $receive_content = ob_get_clean();

            $receive_mail_sent = mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $receive_to_email, $receive_subject, $receive_content, 1, "", "", "", $receive_log_context);
        } else {
            error_log('[matching_mail] receive member login email missing for matching '.$matching_no);
            matching_mail_log_failure('matching_accept_receiver', $matching_no, '', '['.$config['cf_title'].'] 매칭 요청을 수락했습니다.', '수락자 로그인 이메일이 없습니다.');
        }

        return ($request_mail_sent || $receive_mail_sent);
    }
}
