<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 addressing plugin for GLPI
 Copyright (C) 2009-2022 by the addressing Development Team.

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

include ('../../../inc/includes.php');

if (Plugin::isPluginActive("addressing")) {
   $PluginAddressingConfig = new PluginAddressingConfig();

   Session::checkRight("config", UPDATE);

   if (isset($_POST["update"])) {
      $PluginAddressingConfig->update($_POST);
      Html::back();

   } else {
      Html::header(PluginAddressingAddressing::getTypeName(2), '', "tools", "pluginaddressingaddressing", "addressing");
      $PluginAddressingConfig->showForm(1);
      Html::footer();
   }

} else {
   Html::header(__('Setup'), '', "config", "plugins");
   echo "<div class='alert alert-important alert-warning d-flex'>";
   echo "<b>".__('Please activate the plugin', 'addressing')."</b></div>";
   Html::footer();
}
