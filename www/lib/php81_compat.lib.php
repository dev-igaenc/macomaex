<?php
if (!defined('_GNUBOARD_') && !defined('MACOMAEX_PHP81_COMPAT_ALLOW')) {
    // 그누보드 부트스트랩, 레거시 부트스트랩 양쪽 모두 로드
    // 레거시 로드 시 MACOMAEX_PHP81_COMPAT_ALLOW 선행 define 필요
    return;
}

if (!function_exists('get_magic_quotes_gpc')) {
    function get_magic_quotes_gpc()
    {
        return false;
    }
}

if (!function_exists('set_magic_quotes_runtime')) {
    function set_magic_quotes_runtime($new_setting)
    {
        return false;
    }
}

if (function_exists('mysqli_report')) {
    // PHP 7.3 환경과 동일하게 mysqli 예외 리포트 비활성
    @mysqli_report(MYSQLI_REPORT_OFF);
}

if (PHP_VERSION_ID >= 80000 && !function_exists('mysql_connect')) {
    // PHP 8+ 환경용 레거시 mysql_* 호환 레이어
    // mysqli/sql_* 점진 전환 중 기존 코드 경로 유지
    $GLOBALS['__php81_mysql_last_link'] = null;

    function __php81_mysql_resolve_link($link = null)
    {
        if ($link instanceof mysqli) {
            return $link;
        }

        if (isset($GLOBALS['connect']) && $GLOBALS['connect'] instanceof mysqli) {
            return $GLOBALS['connect'];
        }

        if (isset($GLOBALS['g5']['connect_db']) && $GLOBALS['g5']['connect_db'] instanceof mysqli) {
            return $GLOBALS['g5']['connect_db'];
        }

        if (isset($GLOBALS['__php81_mysql_last_link']) && $GLOBALS['__php81_mysql_last_link'] instanceof mysqli) {
            return $GLOBALS['__php81_mysql_last_link'];
        }

        return null;
    }

    function mysql_connect($server = null, $username = null, $password = null, $new_link = false, $client_flags = 0)
    {
        $link = @mysqli_connect((string)$server, (string)$username, (string)$password);
        if ($link instanceof mysqli) {
            $GLOBALS['__php81_mysql_last_link'] = $link;
        }
        return $link;
    }

    function mysql_select_db($database_name, $link_identifier = null)
    {
        $link = __php81_mysql_resolve_link($link_identifier);
        if (!$link) {
            return false;
        }
        return @mysqli_select_db($link, (string)$database_name);
    }

    function mysql_query($query, $link_identifier = null)
    {
        $link = __php81_mysql_resolve_link($link_identifier);
        if (!$link) {
            return false;
        }
        return @mysqli_query($link, $query);
    }

    function mysql_fetch_array($result, $result_type = MYSQLI_BOTH)
    {
        if (!($result instanceof mysqli_result)) {
            return false;
        }
        return @mysqli_fetch_array($result, $result_type);
    }

    function mysql_fetch_assoc($result)
    {
        if (!($result instanceof mysqli_result)) {
            return false;
        }
        return @mysqli_fetch_assoc($result);
    }

    function mysql_num_rows($result)
    {
        if (!($result instanceof mysqli_result)) {
            return 0;
        }
        return mysqli_num_rows($result);
    }

    function mysql_insert_id($link_identifier = null)
    {
        $link = __php81_mysql_resolve_link($link_identifier);
        if (!$link) {
            return 0;
        }
        return mysqli_insert_id($link);
    }

    function mysql_error($link_identifier = null)
    {
        $link = __php81_mysql_resolve_link($link_identifier);
        if (!$link) {
            return 'No mysqli link available';
        }
        return mysqli_error($link);
    }

    function mysql_errno($link_identifier = null)
    {
        $link = __php81_mysql_resolve_link($link_identifier);
        if (!$link) {
            return 0;
        }
        return mysqli_errno($link);
    }

    function mysql_free_result($result)
    {
        if (!($result instanceof mysqli_result)) {
            return false;
        }
        return mysqli_free_result($result);
    }

    function mysql_data_seek($result, $row_number)
    {
        if (!($result instanceof mysqli_result)) {
            return false;
        }
        return mysqli_data_seek($result, (int)$row_number);
    }

    function mysql_num_fields($result)
    {
        if (!($result instanceof mysqli_result)) {
            return 0;
        }
        return mysqli_num_fields($result);
    }

    function mysql_fetch_field($result, $field_offset = 0)
    {
        if (!($result instanceof mysqli_result)) {
            return false;
        }
        if ((int)$field_offset > 0) {
            @mysqli_field_seek($result, (int)$field_offset);
        }
        return mysqli_fetch_field($result);
    }

    function mysql_real_escape_string($unescaped_string, $link_identifier = null)
    {
        $link = __php81_mysql_resolve_link($link_identifier);
        if (!$link) {
            return addslashes((string)$unescaped_string);
        }
        return mysqli_real_escape_string($link, (string)$unescaped_string);
    }
}
