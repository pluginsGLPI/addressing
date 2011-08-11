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

ALTER TABLE `glpi_plugin_addressing_display` DROP `ipconf1`;
ALTER TABLE `glpi_plugin_addressing_display` DROP `ipconf2`;
ALTER TABLE `glpi_plugin_addressing_display` DROP `ipconf3`;
ALTER TABLE `glpi_plugin_addressing_display` DROP `ipconf4`;
ALTER TABLE `glpi_plugin_addressing_display` DROP `netconf`;

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'5000','2','2','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'5000','3','3','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'5000','4','4','0');
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'5000','5','5','0');