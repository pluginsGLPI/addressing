DROP TABLE IF EXISTS `glpi_plugin_addressing`;
CREATE TABLE `glpi_plugin_addressing` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`network` smallint(6) NOT NULL default '0',
	`ipdebut1` smallint(6) NULL,
	`ipdebut2` smallint(6) NULL,
	`ipdebut3` smallint(6) NULL,
	`ipdebut4` smallint(6) NULL,
	`ipfin4` smallint(6) NULL,
	`ping` smallint(6) NOT NULL default '0',
	`link` smallint(6) NOT NULL default '1',
	`comments` text,
	`deleted` smallint(6) NOT NULL default '0',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_addressing_display`;
CREATE TABLE `glpi_plugin_addressing_display` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`ip_alloted` smallint(6) NOT NULL default '0',
	`ip_double` smallint(6) NOT NULL default '0',
	`ip_free` smallint(6) NOT NULL default '0',
	`ip_reserved` smallint(6) NOT NULL default '0',
	`ping` smallint(6) NOT NULL default '0',
	`system` smallint(6) NOT NULL default '0'
) TYPE = MYISAM ;
	
INSERT INTO `glpi_plugin_addressing_display` ( `ID`, `ip_alloted`,`ip_double`,`ip_free`,`ip_reserved`,`ping`,`system`) VALUES ('1','1','1','1','1','0','0');

DROP TABLE IF EXISTS `glpi_plugin_addressing_profiles`;
CREATE TABLE `glpi_plugin_addressing_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) default NULL,
	`interface` varchar(50) NOT NULL default 'addressing',
	`is_default` enum('0','1') NOT NULL default '0',
	`addressing` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `interface` (`interface`)
) TYPE=MyISAM;

INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` ) VALUES (NULL,'5000','2','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` ) VALUES (NULL,'5000','3','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` ) VALUES (NULL,'5000','4','4','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` ) VALUES (NULL,'5000','5','5','0');