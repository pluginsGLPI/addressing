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

ALTER TABLE `glpi_plugin_addressing` RENAME `glpi_plugin_addressing_addressings`;
ALTER TABLE `glpi_plugin_addressing_addressings` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `network` `networks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_networks (id)',
   CHANGE `ipdeb` `begin_ip` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `ipfin` `end_ip` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `ip_alloted` `alloted_ip` tinyint(1) NOT NULL default '0',
   CHANGE `ip_double` `double_ip` tinyint(1) NOT NULL default '0',
   CHANGE `ip_free` `free_ip` tinyint(1) NOT NULL default '0',
   CHANGE `ip_reserved` `reserved_ip` tinyint(1) NOT NULL default '0',
   CHANGE `ping` `use_ping` tinyint(1) NOT NULL default '0',
   DROP `link`,
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`name`),
   ADD INDEX (`entities_id`),
   ADD INDEX (`networks_id`),
   ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_addressing_display` RENAME `glpi_plugin_addressing_configs`;

ALTER TABLE `glpi_plugin_addressing_configs` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `ip_alloted` `alloted_ip` tinyint(1) NOT NULL default '0',
   CHANGE `ip_double` `double_ip` tinyint(1) NOT NULL default '0',
   CHANGE `ip_free` `free_ip` tinyint(1) NOT NULL default '0',
   CHANGE `ip_reserved` `reserved_ip` tinyint(1) NOT NULL default '0',
   CHANGE `ping` `use_ping` tinyint(1) NOT NULL default '0',
   CHANGE `system` `used_system` tinyint(1) NOT NULL default '0';

ALTER TABLE `glpi_plugin_addressing_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `addressing` `addressing` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = 5000 AND `num` = 5;