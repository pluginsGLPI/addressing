DROP TABLE IF EXISTS `glpi_plugin_addressing`;
CREATE TABLE `glpi_plugin_addressing` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_entities` int(11) NOT NULL default '0',
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`network` smallint(6) NOT NULL default '0',
	`ipdeb` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`ipfin` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
	`ip_alloted` smallint(6) NOT NULL default '0',
	`ip_double` smallint(6) NOT NULL default '0',
	`ip_free` smallint(6) NOT NULL default '0',
	`ip_reserved` smallint(6) NOT NULL default '0',
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
	`addressing` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) TYPE=MyISAM;

INSERT INTO `glpi_display` (`type` , `num` , `rank` , `FK_users` ) VALUES (5000,2,2,0);
INSERT INTO `glpi_display` (`type` , `num` , `rank` , `FK_users` ) VALUES (5000,3,6,0);
INSERT INTO `glpi_display` (`type` , `num` , `rank` , `FK_users` ) VALUES (5000,4,5,0);
INSERT INTO `glpi_display` (`type` , `num` , `rank` , `FK_users` ) VALUES (5000,5,7,0);
INSERT INTO `glpi_display` (`type` , `num` , `rank` , `FK_users` ) VALUES (5000,1000,3,0);
INSERT INTO `glpi_display` (`type` , `num` , `rank` , `FK_users` ) VALUES (5000,1001,4,0);