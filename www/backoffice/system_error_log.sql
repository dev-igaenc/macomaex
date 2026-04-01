-- 시스템 에러 로그 테이블 (수동 적용)
-- 기본 테이블 접두사가 g5_ 인 경우: g5_system_error_log

CREATE TABLE `g5_system_error_log` (
  `el_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `el_datetime` datetime NOT NULL,
  `el_type` varchar(30) NOT NULL DEFAULT '',
  `el_level` varchar(20) NOT NULL DEFAULT '',
  `el_message` varchar(500) NOT NULL DEFAULT '',
  `el_file` varchar(255) NOT NULL DEFAULT '',
  `el_line` int(11) NOT NULL DEFAULT '0',
  `el_request_method` varchar(10) NOT NULL DEFAULT '',
  `el_request_uri` varchar(255) NOT NULL DEFAULT '',
  `el_mb_id` varchar(20) NOT NULL DEFAULT '',
  `el_ip` varchar(45) NOT NULL DEFAULT '',
  `el_fingerprint` char(40) NOT NULL DEFAULT '',
  `el_payload` text NOT NULL,
  `el_resolved` tinyint(4) NOT NULL DEFAULT '0',
  `el_resolved_datetime` datetime DEFAULT NULL,
  PRIMARY KEY (`el_id`),
  KEY `idx_el_datetime` (`el_datetime`),
  KEY `idx_el_type` (`el_type`),
  KEY `idx_el_level` (`el_level`),
  KEY `idx_el_fingerprint` (`el_fingerprint`),
  KEY `idx_el_resolved` (`el_resolved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
