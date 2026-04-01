<?php
if (!defined('_GNUBOARD_')) exit;

include_once(G5_PHPMAILER_PATH.'/PHPMailerAutoload.php');

// 메일 보내기 (파일 여러개 첨부 가능)
// type : text=0, html=1, text+html=2
function mailer($fname, $fmail, $to, $subject, $content, $type=0, $file="", $cc="", $bcc="")
{
    global $config;
    global $g5;

    // 메일발송 사용을 하지 않는다면
    if (!$config['cf_email_use']) return;

    if ($type != 1)
        $content = nl2br($content);

    $result = run_replace('mailer', $fname, $fmail, $to, $subject, $content, $type, $file, $cc, $bcc);
    
    if( is_array($result) && isset($result['return']) ){
        return $result['return'];
    }

    $mail_send_result = false;
    $mail = null;

    try {
        $get_mailer_env = function($name, $default = '') {
            if (function_exists('macomaex_env')) {
                return macomaex_env($name, $default);
            }

            $value = getenv($name);
            if ($value !== false) {
                return $value;
            }

            return $default;
        };

        $smtp_host = '';
        $smtp_port = '';
        $smtp_user = '';
        $smtp_password = '';
        $smtp_secure = '';
        $smtp_authtype = '';
        $smtp_useauth = false;
        $smtp_auto_tls = true;
        $from_mail = $fmail;
        $from_name = $fname;

        $env_smtp_host = trim($get_mailer_env('SMTP_HOST', ''));
        if ($env_smtp_host !== '') {
            $smtp_host = $env_smtp_host;
        } else if (defined('SMTP') && SMTP) {
            $smtp_host = SMTP;
        } else if (defined('G5_SMTP') && G5_SMTP) {
            $smtp_host = G5_SMTP;
        }

        $env_smtp_port = trim($get_mailer_env('SMTP_PORT', ''));
        if ($env_smtp_port !== '') {
            $smtp_port = $env_smtp_port;
        } else if (defined('SMTP_PORT') && SMTP_PORT) {
            $smtp_port = SMTP_PORT;
        } else if (defined('G5_SMTP_PORT') && G5_SMTP_PORT) {
            $smtp_port = G5_SMTP_PORT;
        }

        $env_smtp_user = trim($get_mailer_env('SMTP_USER', ''));
        if ($env_smtp_user !== '') {
            $smtp_user = $env_smtp_user;
        } else if (defined('SMTP_USER') && SMTP_USER) {
            $smtp_user = SMTP_USER;
        } else if (defined('G5_Username') && G5_Username) {
            $smtp_user = G5_Username;
        }

        $env_smtp_password = $get_mailer_env('SMTP_PW', '');
        if ($env_smtp_password !== '') {
            $smtp_password = $env_smtp_password;
        } else if (defined('SMTP_PW') && SMTP_PW) {
            $smtp_password = SMTP_PW;
        } else if (defined('G5_Password') && G5_Password) {
            $smtp_password = G5_Password;
        }

        $env_smtp_secure = trim($get_mailer_env('SMTP_SECURE', ''));
        if ($env_smtp_secure !== '') {
            $smtp_secure = strtolower($env_smtp_secure);
        } else if (defined('SMTP_SECURE') && SMTP_SECURE) {
            $smtp_secure = strtolower(trim(SMTP_SECURE));
        }

        $env_smtp_auto_tls = strtolower(trim($get_mailer_env('SMTP_AUTO_TLS', '')));
        if ($env_smtp_auto_tls !== '') {
            $smtp_auto_tls = !in_array($env_smtp_auto_tls, array('0', 'false', 'off', 'no'), true);
        }

        $env_smtp_authtype = trim($get_mailer_env('SMTP_AUTHTYPE', ''));
        if ($env_smtp_authtype !== '') {
            $smtp_authtype = $env_smtp_authtype;
        } else if (defined('SMTP_AUTHTYPE') && SMTP_AUTHTYPE) {
            $smtp_authtype = SMTP_AUTHTYPE;
        }

        if (defined('SMTP_USEAUTH')) {
            $smtp_useauth = (bool) SMTP_USEAUTH;
        } else if ($smtp_user !== '' || $smtp_password !== '') {
            $smtp_useauth = true;
        }

        $env_from_mail = trim($get_mailer_env('SMTP_FROM_EMAIL', ''));
        if ($env_from_mail !== '') {
            $from_mail = $env_from_mail;
        }

        $env_from_name = trim($get_mailer_env('SMTP_FROM_NAME', ''));
        if ($env_from_name !== '') {
            $from_name = $env_from_name;
        }

        $mail = new PHPMailer(); // defaults to using php "mail()"
        if ($smtp_host) {
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->Host = $smtp_host; // SMTP server

            if ($smtp_port) {
                $mail->Port = (int) $smtp_port;
            }

            if (in_array($smtp_secure, array('none', 'off', 'false', 'disable', 'disabled'), true)) {
                $smtp_secure = '';
                $smtp_auto_tls = false;
            }

            if ($smtp_secure && $smtp_secure !== 'auto') {
                $mail->SMTPSecure = $smtp_secure;
            }

            if (property_exists($mail, 'SMTPAutoTLS')) {
                $mail->SMTPAutoTLS = $smtp_auto_tls;
            }

            if ($smtp_useauth && $smtp_user !== '' && $smtp_password !== '') {
                $mail->SMTPAuth = true; // SMTP Auth
                if ($smtp_authtype) {
                    $mail->AuthType = $smtp_authtype;
                }
                $mail->Username = $smtp_user; // SMTP Auth id
                $mail->Password = $smtp_password; // SMTP Auth password
            }
        }
        $mail->CharSet = 'UTF-8';
        $mail->From = $from_mail;
        $mail->FromName = $from_name;
        $mail->Subject = $subject;
        $mail->AltBody = ""; // optional, comment out and test
        $mail->msgHTML($content);
        $mail->addAddress($to);
        if ($cc)
            $mail->addCC($cc);
        if ($bcc)
            $mail->addBCC($bcc);
        //print_r2($file); exit;
        if ($file != "") {
            foreach ($file as $f) {
                $mail->addAttachment($f['path'], $f['name']);
            }
        }

        $mail = run_replace('mail_options', $mail, $fname, $fmail, $to, $subject, $content, $type, $file, $cc, $bcc);

        $mail_send_result = $mail->send();
        if (!$mail_send_result) {
            error_log('[mailer] send failed to '.$to.': '.$mail->ErrorInfo);
        }

    } catch (Exception $e) {
        error_log('[mailer] exception while sending to '.$to.': '.$e->getMessage());
    }

    run_event('mail_send_result', $mail_send_result, $mail, $to, $cc, $bcc);

    return $mail_send_result;
}

// 파일을 첨부함
function attach_file($filename, $tmp_name)
{
    // 서버에 업로드 되는 파일은 확장자를 주지 않는다. (보안 취약점)
    $dest_file = G5_DATA_PATH.'/tmp/'.str_replace('/', '_', $tmp_name);
    move_uploaded_file($tmp_name, $dest_file);
    $tmpfile = array("name" => $filename, "path" => $dest_file);
    return $tmpfile;
}
