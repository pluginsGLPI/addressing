<?php
/*
 * @version $Id: setup.php 164 2013-09-03 12:39:17Z tsmr $
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
 
class PluginAddressingMenu extends CommonGLPI {
   static $rightname = 'plugin_addressing';

   static function getMenuName() {
      return _n('IP Adressing', 'IP Adressing', 2, 'addressing');
   }

   static function getMenuContent() {
      global $CFG_GLPI;
      $menu          = array();
      $menu['title'] = self::getMenuName();
      $menu['page']  = "/plugins/addressing/front/addressing.php";

      $menu['title']           = PluginAddressingAddressing::getTypeName(2);
      $menu['page']            = PluginAddressingAddressing::getSearchURL(false);
      $menu['links']['search'] = PluginAddressingAddressing::getSearchURL(false);

      if (Session::haveRight('plugin_addressing', UPDATE)) {
         $menu['links']['add'] = PluginAddressingAddressing::getFormURL(false);
      }
      return $menu;
   }
   
   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['tools']['types']['PluginAddressingMenu'])) {
         unset($_SESSION['glpimenu']['tools']['types']['PluginAddressingMenu']); 
      }
      if (isset($_SESSION['glpimenu']['tools']['content']['pluginaddressingmenu'])) {
         unset($_SESSION['glpimenu']['tools']['content']['pluginaddressingmenu']); 
      }
   }
}