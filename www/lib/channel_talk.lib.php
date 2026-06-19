<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('channel_talk_env')) {
    function channel_talk_env($key, $default = '')
    {
        $key = trim((string)$key);
        if ($key === '') return (string)$default;

        if (function_exists('macomaex_env')) {
            return trim((string)macomaex_env($key, $default));
        }

        $value = getenv($key);
        if ($value === false || $value === null || $value === '') {
            return (string)$default;
        }

        return trim((string)$value);
    }
}

if (!function_exists('channel_talk_plugin_key')) {
    function channel_talk_plugin_key()
    {
        $pluginKey = channel_talk_env('CHANNEL_TALK_PLUGIN_KEY', '');
        if ($pluginKey !== '') return $pluginKey;

        return '296accc8-9450-4f33-a7e5-8a61d905164d';
    }
}

if (!function_exists('channel_talk_guest_member_id')) {
    function channel_talk_guest_member_id()
    {
        $sessionId = session_id();
        if ($sessionId === '') {
            @session_start();
            $sessionId = session_id();
        }

        $sessionId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$sessionId);
        if ($sessionId === '') {
            $sessionId = substr(hash('sha256', uniqid('guest_', true)), 0, 12);
        }

        return 'guest_' . $sessionId;
    }
}

if (!function_exists('channel_talk_member_id')) {
    function channel_talk_member_id()
    {
        global $member, $is_member;

        if (!empty($is_member)) {
            $mbId = trim((string)($member['mb_id'] ?? ''));
            if ($mbId !== '') {
                return $mbId;
            }
        }

        return channel_talk_guest_member_id();
    }
}

if (!function_exists('channel_talk_boot_payload')) {
    function channel_talk_boot_payload($extra = array())
    {
        $memberId = channel_talk_member_id();
        $payload = array(
            'pluginKey' => channel_talk_plugin_key(),
            'memberId' => $memberId,
            'profile' => array(
                'name' => $memberId,
            ),
        );

        if (is_array($extra) && !empty($extra)) {
            $payload = array_replace_recursive($payload, $extra);
        }

        return $payload;
    }
}

if (!function_exists('channel_talk_json_encode')) {
    function channel_talk_json_encode($value)
    {
        $flags = 0;
        if (defined('JSON_UNESCAPED_UNICODE')) $flags |= JSON_UNESCAPED_UNICODE;
        if (defined('JSON_UNESCAPED_SLASHES')) $flags |= JSON_UNESCAPED_SLASHES;

        $json = json_encode($value, $flags);
        if ($json === false) {
            return '{}';
        }

        return $json;
    }
}

if (!function_exists('channel_talk_boot_script')) {
    function channel_talk_boot_script($extra = array())
    {
        $payload = channel_talk_json_encode(channel_talk_boot_payload($extra));

        return <<<HTML
<script>
(function(){var w=window;if(w.ChannelIO){return w.console.error("ChannelIO script included twice.");}var ch=function(){ch.c(arguments);};ch.q=[];ch.c=function(args){ch.q.push(args);};w.ChannelIO=ch;function l(){if(w.ChannelIOInitialized){return;}w.ChannelIOInitialized=true;var s=document.createElement("script");s.type="text/javascript";s.async=true;s.src="https://cdn.channel.io/plugin/ch-plugin-web.js";var x=document.getElementsByTagName("script")[0];if(x.parentNode){x.parentNode.insertBefore(s,x);}}if(document.readyState==="complete"){l();}else{w.addEventListener("DOMContentLoaded",l);w.addEventListener("load",l);}})();
ChannelIO('boot', {$payload});
</script>
HTML;
    }
}
