<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 addressing plugin for GLPI
 Copyright (C) 2009-2016 by the addressing Development Team.

 https://github.com/pluginsGLPI/addressing
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of addressing.

 addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with addressing. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// Init the hooks of the plugins -Needed
function plugin_init_addressing() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['addressing'] = true;

   $PLUGIN_HOOKS['change_profile']['addressing'] = array('PluginAddressingProfile', 'initProfile');

   Plugin::registerClass('PluginAddressingProfile',
                         array('addtabon' => array('Profile')));

   
   
   if (Session::getLoginUserID()) {
      if (Session::haveRight('plugin_addressing', READ)) {
         $PLUGIN_HOOKS["menu_toadd"]['addressing'] = array('tools'  => 'PluginAddressingMenu');
      }

      if (Session::haveRight('plugin_addressing', UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['addressing']   = 1;
      }

      // Config page
      if (Session::haveRight("config", UPDATE)) {
         $PLUGIN_HOOKS['config_page']['addressing']             = 'front/config.form.php';
      }

      // Add specific files to add to the header : javascript or css
      $PLUGIN_HOOKS['add_css']['addressing']        = "addressing.css";
      $PLUGIN_HOOKS['add_javascript']['addressing'] = 'addressing.js';

      $PLUGIN_HOOKS['post_init']['addressing'] = array('PluginAddressingPing_Equipment', 'postinit');
   }
}


// Get the name and the version of the plugin - Needed
function plugin_version_addressing() {

   return array(
      'name'           => _n('IP Adressing', 'IP Adressing', 2, 'addressing'),
      'version'        => '2.5.0',
      'author'         => 'Gilles Portheault, Xavier Caillaud, Remi Collet, Nelly Mahu-Lasson',
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/pluginsGLPI/addressing',
      'minGlpiVersion' => '9.1.1');// For compatibility / no install in version < 9.1.1
}


// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_addressing_check_prerequisites() {

   if (version_compare(GLPI_VERSION, '9.1.1', 'lt') || version_compare(GLPI_VERSION, '9.2', 'ge')) {
      echo "This plugin requires GLPI = 9.1.1";
      return false;
   }
   return true;
}


// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_addressing_check_config() {
   return true;
}

?>
