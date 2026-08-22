--
-- -------------------------------------------------------------------------
-- addressing plugin for GLPI
-- Copyright (C) 2016-2026 by the addressing Development Team.
--
-- https://github.com/pluginsGLPI/addressing
-- -------------------------------------------------------------------------
--
-- LICENSE
--
-- This file is part of addressing.
--
-- addressing is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- addressing is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with addressing. If not, see <http://www.gnu.org/licenses/>.
-- --------------------------------------------------------------------------
--

CREATE TABLE `glpi_plugin_addressing_pinginfos`
(
    `id`                               int unsigned NOT NULL auto_increment,
    `plugin_addressing_addressings_id` int unsigned NOT NULL default '0',
    `ipname`                           varchar(255) collate utf8mb4_unicode_ci default NULL,
    `ping_response`                    tinyint NOT NULL default '0',
    `ping_date`                        timestamp NULL DEFAULT NULL,
    `items_id`                         int unsigned NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
    `itemtype`                         varchar(100) collate utf8mb4_unicode_ci COMMENT 'see .class.php file',
    PRIMARY KEY (`id`),
    KEY  `plugin_addressing_addressings_id` (`plugin_addressing_addressings_id`),
    KEY  `ipname` (`ipname`),
    KEY  `ping_response` (`ping_response`),
    KEY  `ping_date` (`ping_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
