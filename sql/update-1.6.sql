ALTER TABLE `glpi_plugin_addressing`
	ADD `ipdeb` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL default '' AFTER `ipfin4`,
	ADD `ipfin` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL default '' AFTER `ipdeb`;

UPDATE `glpi_plugin_addressing` SET
	ipdeb=CONCAT_WS('.',ipdebut1,ipdebut2,ipdebut3,ipdebut4),
	ipfin=CONCAT_WS('.',ipdebut1,ipdebut2,ipdebut3,ipfin4);

ALTER TABLE `glpi_plugin_addressing`
	ADD `ip_alloted` SMALLINT NOT NULL DEFAULT '1' AFTER `ipfin` ,
	ADD `ip_double` SMALLINT NOT NULL DEFAULT '1' AFTER `ip_alloted` ,
	ADD `ip_free` SMALLINT NOT NULL DEFAULT '1' AFTER `ip_double` ,
	ADD `ip_reserved` SMALLINT NOT NULL DEFAULT '1' AFTER `ip_free` ;

ALTER TABLE `glpi_plugin_addressing`
	DROP `ipdebut1`,
	DROP `ipdebut2`,
	DROP `ipdebut3`,
	DROP `ipdebut4`,
	DROP `ipfin4`;

DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = 5000;
INSERT INTO `glpi_displaypreferences` VALUES (NULL ,5000,2,2,0);
INSERT INTO `glpi_displaypreferences` VALUES (NULL ,5000,3,6,0);
INSERT INTO `glpi_displaypreferences` VALUES (NULL ,5000,4,5,0);
INSERT INTO `glpi_displaypreferences` VALUES (NULL ,5000,5,7,0);
INSERT INTO `glpi_displaypreferences` VALUES (NULL ,5000,1000,3,0);
INSERT INTO `glpi_displaypreferences` VALUES (NULL ,5000,1001,4,0);