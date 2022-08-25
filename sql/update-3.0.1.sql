CREATE TABLE `glpi_plugin_addressing_ipcomments`
(
    `id`                               int unsigned NOT NULL auto_increment,
    `plugin_addressing_addressings_id` int unsigned NOT NULL default '0',
    `ipname`                           varchar(255) collate utf8mb4_unicode_ci default NULL,
    `comments`                         LONGTEXT collate utf8mb4_unicode_ci,
    PRIMARY KEY (`id`),
    KEY  `plugin_addressing_addressings_id` (`plugin_addressing_addressings_id`),
    KEY  `ipname` (`ipname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

ALTER TABLE `glpi_plugin_addressing_addressings`
    ADD `vlans_id` int unsigned NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_addressing_addressings`
    ADD `use_as_filter` tinyint NOT NULL default '0';
