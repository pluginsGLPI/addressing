CREATE TABLE `glpi_plugin_addressing_pinginfos`
(
    `id`                               int(11) NOT NULL auto_increment,
    `plugin_addressing_addressings_id` int(11) NOT NULL default '0',
    `ipname`                           varchar(255) collate utf8_unicode_ci default NULL,
    `ping_response`                    tinyint(1) NOT NULL default '0',
    `ping_date`                        datetime                             DEFAULT NULL,
    `items_id`                         int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
    `itemtype`                         varchar(100) collate utf8_unicode_ci COMMENT 'see .class.php file',
    PRIMARY KEY (`id`),
    KEY  `plugin_addressing_addressings_id` (`plugin_addressing_addressings_id`),
    KEY  `ipname` (`ipname`),
    KEY  `ping_response` (`ping_response`),
    KEY  `ping_date` (`ping_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
