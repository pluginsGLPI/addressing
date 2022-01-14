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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginAddressingConfig
 */
class PluginAddressingConfig extends CommonDBTM {

   static $rightname = "plugin_addressing";

   function showForm($ID, $options = []) {

      $this->getFromDB($ID);

      $system = $this->fields["used_system"];

      echo "<div class='center'>";
      echo "<form method='post' action='".$this->getFormURL()."'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>".__('System for ping', 'addressing')."</th></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'><div class='center'>";
      $array = [0 => __('Linux ping', 'addressing'),
                1 => __('Windows', 'addressing'),
                2 => __('Linux fping', 'addressing'),
                3 => __('BSD ping', 'addressing'),
                4 => __('MacOSX ping', 'addressing')];
      Dropdown::ShowFromArray("used_system", $array, ['value' => $system]);
      echo "</div></td></tr>";

      echo "<tr><th colspan='4'>".__('Display', 'addressing')."</th></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Assigned IP', 'addressing')."</td>";
      echo "<td>";
      Dropdown::showYesNo("alloted_ip", $this->fields["alloted_ip"]);
      echo "</td>";

      echo "<td>".__('Free IP', 'addressing')."</td>";
      echo "<td>";
      Dropdown::showYesNo("free_ip", $this->fields["free_ip"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>".__('Same IP', 'addressing')."</td>";
      echo "<td>";
      Dropdown::showYesNo("double_ip", $this->fields["double_ip"]);
      echo "</td>";

      echo "<td>".__('Reserved IP', 'addressing')."</td>";
      echo "<td>";
      Dropdown::showYesNo("reserved_ip", $this->fields["reserved_ip"]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'><td colspan='2'>".__('Use Ping', 'addressing')."</td>";
      echo "<td colspan='2'>";
      Dropdown::showYesNo("use_ping", $this->fields["use_ping"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>";
      echo Html::hidden('id', ['value' => 1]);
      echo "<div class='center'>";
      echo Html::submit(_sx('button', 'Post'), ['name' => 'update', 'class' => 'btn btn-primary me-2']);
      echo "</div></th></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }
}

