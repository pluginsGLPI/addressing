ALTER TABLE `glpi_plugin_addressing_addressings`
   ADD `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   ADD `fqdns_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_fqdns (id)';