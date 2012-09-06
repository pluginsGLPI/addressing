<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 Addressing plugin for GLPI
 Copyright (C) 2003-2011 by the addressing Development Team.

 https://forge.indepnet.net/projects/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 Addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Addressing. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_addressing() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['addressing'] = true;

   $PLUGIN_HOOKS['change_profile']['addressing'] = array('PluginAddressingProfile', 'changeProfile');
   $PLUGIN_HOOKS['pre_item_purge']['addressing'] = array('Profile' => array('PluginAddressingProfile',
                                                                            'purgeProfiles'));
   Plugin::registerClass('PluginAddressingProfile',
                         array('addtabon' => array('Profile')));

   if (Session::getLoginUserID()) {
      if (plugin_addressing_haveRight("addressing","r")) {
         $PLUGIN_HOOKS['menu_entry']['addressing']              = 'front/addressing.php';
         $PLUGIN_HOOKS['submenu_entry']['addressing']['search'] = 'front/addressing.php';
      }

      if (plugin_addressing_haveRight("addressing","w")) {
         $PLUGIN_HOOKS['submenu_entry']['addressing']['add'] = 'front/addressing.form.php?new=1';
         $PLUGIN_HOOKS['use_massive_action']['addressing']   = 1;
      }

      // Config page
      if (Session::haveRight("config","w")) {
         $PLUGIN_HOOKS['submenu_entry']['addressing']['config'] = 'front/config.form.php';
         $PLUGIN_HOOKS['config_page']['addressing']             = 'front/config.form.php';
      }

      // Add specific files to add to the header : javascript or css
      //$PLUGIN_HOOKS['add_javascript']['example']="example.js";
      $PLUGIN_HOOKS['add_css']['addressing']        = "addressing.css";
      $PLUGIN_HOOKS['add_javascript']['addressing'] = 'addressing.js';

      $PLUGIN_HOOKS['post_init']['addressing'] = array('PluginAddressingPing_Equipment', 'postinit');
   }
}


// Get the name and the version of the plugin - Needed
function plugin_version_addressing() {
   global $LANG;

   return array('name'           => $LANG['plugin_addressing']['title'][1],
                'version'        => '2.0.1',
                'author'         => 'Gilles Portheault, Xavier Caillaud, Remi Collet, Nelly Mahu-Lasson',
                'license'        => 'GPLv2+',
                'homepage'       => 'https://forge.indepnet.net/projects/addressing',
                'minGlpiVersion' => '0.83.3');// For compatibility / no install in version < 0.80
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_addressing_check_prerequisites() {

   if (version_compare(GLPI_VERSION,'0.83.3','lt') || version_compare(GLPI_VERSION,'0.84','ge')) {
      echo "This plugin requires GLPI >= 0.83.3 and GLPI < 0.84";
      return false;
   }
   return true;
}


// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_addressing_check_config() {
   return true;
}


function plugin_addressing_haveRight($module,$right) {

   $matches = array(""  => array("","r","w"), // ne doit pas arriver normalement
                    "r" => array("r","w"),
                    "w" => array("w"),
                    "1" => array("1"),
                    "0" => array("0","1")); // ne doit pas arriver non plus

   if (isset($_SESSION["glpi_plugin_addressing_profile"][$module])
       && in_array($_SESSION["glpi_plugin_addressing_profile"][$module],$matches[$right])) {
      return true;
   }
   return false;
}
?>
