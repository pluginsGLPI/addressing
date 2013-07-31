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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginAddressingConfig extends CommonDBTM {

   function showForm() {

      $this->getFromDB('1');

      $system = $this->fields["used_system"];

      echo "<div class='center'>";
      echo "<form method='post' action='".$this->getFormURL()."'>";

      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr><th colspan='4'>".__('System for ping', 'addressing')."</th></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'><div class='center'><select name='used_system'>";
      echo "<option value='0' ".($system==0?" selected ":"").">".
            __('Linux ping', 'addressing')."</option>";
      echo "<option value='2' ".($system==2?" selected ":"").">".
            __('Linux fping', 'addressing')."</option>";
      echo "<option value='1' ".($system==1?" selected ":"").">".
            __('Windows', 'addressing')."</option>";
      echo "<option value='3' ".($system==3?" selected ":"").">".
            __('BSD ping', 'addressing')."</option>";
      echo "<option value='4' ".($system==4?" selected ":"").">".
            __('MacOSX ping', 'addressing')."</option>";
      echo "</select>";
      echo "</div></td></tr>";

      echo "<tr><th colspan='4'>".__('Display', 'addressing')."</th></tr>";

      echo "<tr class='tab_bg_1'><td>".__('Assigned IP', 'addressing')."</td>";
      echo "<td>";
      Dropdown::showYesNo("alloted_ip",$this->fields["alloted_ip"]);
      echo "</td>";

      echo "<td>".__('Free IP', 'addressing')."</td>";
      echo "<td>";
      Dropdown::showYesNo("free_ip",$this->fields["free_ip"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>".__('Same IP', 'addressing')."</td>";
      echo "<td>";
      Dropdown::showYesNo("double_ip",$this->fields["double_ip"]);
      echo "</td>";

      echo "<td>".__('Reserved IP', 'addressing')."</td>";
      echo "<td>";
      Dropdown::showYesNo("reserved_ip",$this->fields["reserved_ip"]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'><td colspan='2'>".__('Use Ping', 'addressing')."</td>";
      echo "<td colspan='2'>";
      Dropdown::showYesNo("use_ping",$this->fields["use_ping"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>";
      echo "<input type='hidden' name='id' value='1'>";
      echo "<div class='center'>".
            "<input type='submit' name='update' value='"._sx('button','Post')."' class='submit'>".
           "</div></th></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }
}

?>