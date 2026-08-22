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

DROP TABLE IF EXISTS `glpi_plugin_addressing_addressings`;
CREATE TABLE `glpi_plugin_addressing_addressings` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `networks_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_networks (id)',
   `begin_ip` varchar(255) collate utf8_unicode_ci default NULL,
   `end_ip` varchar(255) collate utf8_unicode_ci default NULL,
   `alloted_ip` tinyint(1) NOT NULL default '0',
   `double_ip` tinyint(1) NOT NULL default '0',
   `free_ip` tinyint(1) NOT NULL default '0',
   `reserved_ip` tinyint(1) NOT NULL default '0',
   `use_ping` tinyint(1) NOT NULL default '0',
   `comment` text collate utf8_unicode_ci,
   `is_deleted` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `networks_id` (`networks_id`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_addressing_configs`;
CREATE TABLE `glpi_plugin_addressing_configs` (
   `id` int(11) NOT NULL auto_increment,
   `alloted_ip` tinyint(1) NOT NULL default '0',
   `double_ip` tinyint(1) NOT NULL default '0',
   `free_ip` tinyint(1) NOT NULL default '0',
   `reserved_ip` tinyint(1) NOT NULL default '0',
   `use_ping` tinyint(1) NOT NULL default '0',
   `used_system` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_addressing_configs` VALUES ('1','1','1','1','1','0','0');

INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginAddressingAddressing',2,2,0);
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginAddressingAddressing',3,6,0);
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginAddressingAddressing',4,5,0);
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginAddressingAddressing',1000,3,0);
INSERT INTO `glpi_displaypreferences` VALUES (NULL,'PluginAddressingAddressing',1001,4,0);
