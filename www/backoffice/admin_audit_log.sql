-- 감사로그 테이블 (수동 적용)
-- 기본 테이블 접두사가 g5_ 인 경우: g5_admin_audit_log

CREATE TABLE `g5_admin_audit_log` (
  `al_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `al_datetime` datetime NOT NULL,
  `mb_id` varchar(20) NOT NULL DEFAULT '',
  `mb_name` varchar(100) NOT NULL DEFAULT '',
  `mb_level` int(11) NOT NULL DEFAULT '0',
  `al_ip` varchar(45) NOT NULL DEFAULT '',
  `al_method` varchar(10) NOT NULL DEFAULT '',
  `al_uri` varchar(255) NOT NULL DEFAULT '',
  `al_action` varchar(120) NOT NULL DEFAULT '',
  `al_target` varchar(120) NOT NULL DEFAULT '',
  `al_result` varchar(10) NOT NULL DEFAULT '',
  `al_message` varchar(255) NOT NULL DEFAULT '',
  `al_payload` text NOT NULL,
  PRIMARY KEY (`al_id`),
  KEY `idx_al_datetime` (`al_datetime`),
  KEY `idx_mb_id` (`mb_id`),
  KEY `idx_al_action` (`al_action`),
  KEY `idx_al_result` (`al_result`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
