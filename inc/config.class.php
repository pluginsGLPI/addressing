<?php
/*
 * @version $Id: HEADER 2011-03-12 18:01:26 tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Author of file: CAILLAUD Xavier & COLLET Remi
// Purpose of file: plugin addressing v1.9.0 - GLPI 0.80
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginAddressingConfig extends CommonDBTM {

   function showForm() {
      global $LANG;

      $this->getFromDB('1');

      $system = $this->fields["used_system"];

      echo "<div class='center'>";
      echo "<form method='post' action='".$this->getFormURL()."'>";

      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr><th colspan='4'>".$LANG['plugin_addressing']['setup'][19]."</th></tr>";

      echo "<tr class='tab_bg_1'><td colspan='4'><div class='center'><select name='used_system'>";
      echo "<option value='0' ".($system==0?" selected ":"").">".
             $LANG['plugin_addressing']['setup'][20]."</option>";
      echo "<option value='2' ".($system==2?" selected ":"").">".
             $LANG['plugin_addressing']['setup'][25]."</option>";
      echo "<option value='1' ".($system==1?" selected ":"").">".
             $LANG['plugin_addressing']['setup'][21]."</option>";
      echo "<option value='3' ".($system==3?" selected ":"").">".
             $LANG['plugin_addressing']['setup'][28]."</option>";
      echo "<option value='4' ".($system==4?" selected ":"").">".
             $LANG['plugin_addressing']['setup'][29]."</option>";
      echo "</select>";
      echo "</div></td></tr>";

      echo "<tr><th colspan='4'>".$LANG['plugin_addressing']['setup'][10]."</th></tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_addressing']['setup'][11]."</td>";
      echo "<td>";
      echo Dropdown::showYesNo("alloted_ip",$this->fields["alloted_ip"]);
      echo "</td>";

      echo "<td>".$LANG['plugin_addressing']['setup'][12]."</td>";
      echo "<td>";
      echo Dropdown::showYesNo("free_ip",$this->fields["free_ip"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>".$LANG['plugin_addressing']['setup'][13]."</td>";
      echo "<td>";
      echo Dropdown::showYesNo("double_ip",$this->fields["double_ip"]);
      echo "</td>";

      echo "<td>".$LANG['plugin_addressing']['setup'][14]."</td>";
      echo "<td>";
      echo Dropdown::showYesNo("reserved_ip",$this->fields["reserved_ip"]);
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'><td colspan='2'>".$LANG['plugin_addressing']['setup'][22]."</td>";
      echo "<td colspan='2'>";
      echo Dropdown::showYesNo("use_ping",$this->fields["use_ping"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='4'>";
      echo "<input type='hidden' name='id' value='1'>";
      echo "<div class='center'>".
            "<input type='submit' name='update' value=\"".$LANG['buttons'][2]."\" class='submit'>".
           "</div></th></tr>";
      echo "</table>";
      echo "</form>";
      echo "</div>";
   }
}
?>
