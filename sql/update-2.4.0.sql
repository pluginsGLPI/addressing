DROP TABLE IF EXISTS `glpi_plugin_addressing_filters`;
CREATE TABLE `glpi_plugin_addressing_filters` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `plugin_addressing_addressings_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `begin_ip` varchar(255) collate utf8_unicode_ci default NULL,
   `end_ip` varchar(255) collate utf8_unicode_ci default NULL,
   `type` varchar(255) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_addressing_addressings_id` (`plugin_addressing_addressings_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;