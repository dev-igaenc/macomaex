-- --------------------------------------------------------

--
-- Table structure for table `g5_nc_alimtalk_config`
--

DROP TABLE IF EXISTS `g5_nc_alimtalk_config`;
CREATE TABLE `g5_nc_alimtalk_config` (
	`nc_plus_friend_id` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_biz_msg_service_id` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_sub_access_key_id` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_sub_secret_key` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_biz_msg_admin_hp` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_sms` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_sms_service_id` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_sms_admin_hp` VARCHAR(20) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_nc_alimtalk_history`
--

DROP TABLE IF EXISTS `g5_nc_alimtalk_history`;
CREATE TABLE `g5_nc_alimtalk_history` (
	`nc_id` INT(11) NOT NULL AUTO_INCREMENT,
	`nc_to` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_template_code` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_request_id` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_message_id` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_reserved` TINYINT(4) NOT NULL DEFAULT '0',
	`nc_reserved_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`nc_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`nc_id`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_nc_friendtalk_history`
--

DROP TABLE IF EXISTS `g5_nc_friendtalk_history`;
CREATE TABLE `g5_nc_friendtalk_history` (
	`nc_id` INT(11) NOT NULL AUTO_INCREMENT,
	`nc_to` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_request_id` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_message_id` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_reserved` TINYINT(4) NOT NULL DEFAULT '0',
	`nc_reserved_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`nc_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`nc_id`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `g5_nc_friendtalk_image`
--

DROP TABLE IF EXISTS `g5_nc_friendtalk_image`;
CREATE TABLE `g5_nc_friendtalk_image` (
	`nc_id` INT(11) NOT NULL AUTO_INCREMENT,
	`nc_plus_friend_id` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_image_id` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_image_name` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_image_url` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`nc_image_is_wide` TINYINT(4) NOT NULL DEFAULT '0',
	`nc_datetime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`nc_id`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
