DROP TABLE IF EXISTS `glpi_plugin_addressing_profiles`;
CREATE TABLE `glpi_plugin_addressing_profiles` (
`ID` int(11) NOT NULL auto_increment,
`name` varchar(255) default NULL,
`interface` varchar(50) NOT NULL default 'addressing',
`is_default` smallint(6) NOT NULL default '0',
`addressing` char(1) default NULL,
PRIMARY KEY  (`ID`),
KEY `interface` (`interface`)
) TYPE=MyISAM;

ALTER TABLE `glpi_plugin_addressing_display` ADD `ping` smallint(6) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_display` ADD `system` smallint(6) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_display` ADD `ipconf1` smallint(6) NULL;
ALTER TABLE `glpi_plugin_addressing_display` ADD `ipconf2` smallint(6) NULL;
ALTER TABLE `glpi_plugin_addressing_display` ADD `ipconf3` smallint(6) NULL;
ALTER TABLE `glpi_plugin_addressing_display` ADD `ipconf4` smallint(6) NULL;
ALTER TABLE `glpi_plugin_addressing_display` ADD `netconf` smallint(6) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_display` cHANGE `ip_alloted` `ip_alloted` smallint(6) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_display` cHANGE `ip_double` `ip_double` smallint(6) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_display` cHANGE `ip_free` `ip_free` smallint(6) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_display` cHANGE `ip_reserved` `ip_reserved` smallint(6) NOT NULL default '0';
UPDATE `glpi_plugin_addressing_display` SET `ip_alloted` = '0' WHERE `ip_alloted` = '2';
UPDATE `glpi_plugin_addressing_display` SET `ip_double` = '0' WHERE `ip_double` = '2';
UPDATE `glpi_plugin_addressing_display` SET `ip_free` = '0' WHERE `ip_free` = '2';
UPDATE `glpi_plugin_addressing_display` SET `ip_reserved` = '0' WHERE `ip_reserved` = '2';