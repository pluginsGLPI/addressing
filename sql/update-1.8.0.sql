ALTER TABLE `glpi_plugin_addressing` RENAME `glpi_plugin_addressing_addressing`;
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `ID` `id` int(11) NOT NULL auto_increment;
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `network` `networks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_networks (id)';
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `ipdeb` `begin_ip` varchar(255) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `ipfin` `end_ip` varchar(255) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `ip_alloted` `alloted_ip` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `ip_double` `double_ip` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `ip_free` `free_ip` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `ip_reserved` `reserved_ip` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `ping` `use_ping` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_addressing` DROP `link`;
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `comments` `comment` text collate utf8_unicode_ci;
ALTER TABLE `glpi_plugin_addressing_addressing` CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0';

ALTER TABLE `glpi_plugin_addressing_addressing` ADD INDEX (`name`);
ALTER TABLE `glpi_plugin_addressing_addressing` ADD INDEX (`entities_id`);
ALTER TABLE `glpi_plugin_addressing_addressing` ADD INDEX (`networks_id`);
ALTER TABLE `glpi_plugin_addressing_addressing` ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_addressing_display` RENAME `glpi_plugin_addressing_configs`;

ALTER TABLE `glpi_plugin_addressing_configs` CHANGE `ID` `id` int(11) NOT NULL auto_increment;
ALTER TABLE `glpi_plugin_addressing_configs` CHANGE `ip_alloted` `alloted_ip` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_configs` CHANGE `ip_double` `double_ip` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_configs` CHANGE `ip_free` `free_ip` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_configs` CHANGE `ip_reserved` `reserved_ip` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_configs` CHANGE `ping` `use_ping` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_addressing_configs` CHANGE `system` `used_system` tinyint(1) NOT NULL default '0';

ALTER TABLE `glpi_plugin_addressing_profiles` CHANGE `ID` `id` int(11) NOT NULL auto_increment;
ALTER TABLE `glpi_plugin_addressing_profiles` CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL;
ALTER TABLE `glpi_plugin_addressing_profiles` CHANGE `addressing` `addressing` char(1) collate utf8_unicode_ci default NULL;

DELETE FROM `glpi_displayprefs` WHERE `itemtype` = 5000 AND `num` = 5;