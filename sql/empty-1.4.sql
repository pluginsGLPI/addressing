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

DROP TABLE IF EXISTS `glpi_plugin_addressing_display`;
CREATE TABLE `glpi_plugin_addressing_display` (
	`ID` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`ip_alloted` smallint(6) NOT NULL default '0',
	`ip_double` smallint(6) NOT NULL default '0',
	`ip_free` smallint(6) NOT NULL default '0',
	`ip_reserved` smallint(6) NOT NULL default '0',
	`ipconf1` smallint(6) NULL,
	`ipconf2` smallint(6) NULL,
	`ipconf3` smallint(6) NULL,
	`ipconf4` smallint(6) NULL,
	`netconf` smallint(6) NOT NULL default '0',
	`ping` smallint(6) NOT NULL default '0',
	`system` smallint(6) NOT NULL default '0'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	
INSERT INTO `glpi_plugin_addressing_display` ( `ID`, `ip_alloted`,`ip_double`,`ip_free`,`ip_reserved`,`ping`,`system`) VALUES ('1','1','1','1','1','0','0');

DROP TABLE IF EXISTS `glpi_plugin_addressing_profiles`;
CREATE TABLE `glpi_plugin_addressing_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) default NULL,
	`interface` varchar(50) NOT NULL default 'addressing',
	`is_default` enum('0','1') NOT NULL default '0',
	`addressing` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `interface` (`interface`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;