CREATE TABLE `glpi_plugin_addressing_ipcomments`
(
    `id`                               int(11) NOT NULL auto_increment,
    `plugin_addressing_addressings_id` int(11) NOT NULL default '0',
    `ipname`                           varchar(255) collate utf8_unicode_ci default NULL,
    `comments`                         LONGTEXT collate utf8_unicode_ci,
    PRIMARY KEY (`id`),
    KEY  `plugin_addressing_addressings_id` (`plugin_addressing_addressings_id`),
    KEY  `ipname` (`ipname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;