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
 * Class PluginAddressingPing_Equipment
 */
class PluginAddressingPing_Equipment extends commonDBTM {

   static $rightname = "plugin_addressing";

   function showPingForm($itemtype, $items_id) {
      global $DB, $CFG_GLPI;

      $obj = new $itemtype();
      $obj->getfromDB($items_id);
      //Html::printCleanArray($obj);
      $dbu      = new DbUtils();
      $itemtype = $dbu->getItemTypeForTable($obj->getTable());

      $list_ip = [];

      $query = "SELECT `glpi_networknames`.`name`, `glpi_ipaddresses`.`name` as ip, `glpi_networkports`.`items_id`
               FROM `glpi_networkports` 
               LEFT JOIN `" . $obj->getTable() . "` ON (`glpi_networkports`.`items_id` = `" . $obj->getTable() . "`.`id`
                              AND `glpi_networkports`.`itemtype` = '" . $itemtype . "')
               LEFT JOIN `glpi_networknames` ON (`glpi_networkports`.`id` =  `glpi_networknames`.`items_id`)
               LEFT JOIN `glpi_ipaddresses` ON (`glpi_ipaddresses`.`items_id` = `glpi_networknames`.`id`)
                WHERE `" . $obj->getTable() . "`.`id` = '" . $obj->fields['id'] . "'";

      $res = $DB->query($query);
      while ($row = $DB->fetchArray($res)) {
         if ($row['ip'] != '') {
            $port = $row['ip'];
            if ($row['name'] != '') {
               $port = $row['name'] . " ($port)";
            }
            $list_ip[$row['ip']] = $port;
         }
      }
      echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2 left'>";
      echo "<tr><th colspan='3'>" . __('IP ping', 'addressing') . "</th>";
      echo "<th>";
      echo "<i class='fas fa-times-circle fa-1x' onclick='$(\"#ping_item\").hide();'></i>";
      echo "</tr>";

      if (count($list_ip) > 0) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('IP') . " : </td>";
         echo "<td colspan='3'>";
         echo "<select style='width:200px' class='form-select' id='ip'>";
         echo "<option>" . Dropdown::EMPTY_VALUE . "</option>";
         foreach ($list_ip as $ip => $name) {
            echo "<option value='$ip'>$name</option>";
         }
         echo "</select>";
         echo "&nbsp;<input class='submit btn btn-primary' type='button' value='" .
              __s('IP ping', 'addressing') . "' id='ping_ip'>";
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Ping result', 'addressing') . " : </td>";
         echo "<td colspan='3'>";
         echo "<div id='ping_response' class='plugin_addressing_ping_equipment'></div>";
         echo "</td></tr>";
      }
      echo "</table>";

      echo Html::scriptBlock("$(document).on('click', '#ping_ip', function(event) {
         $('#ping_response').load('" . PLUGIN_ADDRESSING_WEBDIR . "/ajax/ping.php', {
            'ip': $('#ip').val(),
            'itemtype': '$itemtype',
            'items_id': '$items_id'
         })
      });");

      if (count($list_ip) == 0) {
         echo __('No IP for this equipment', 'addressing');
      }
   }


   /**
    * @param $system
    * @param $ip
    *
    * @return array
    */
   function ping($system, $ip, $return = "list") {
      $error = 1;
      $list  = '';
      switch ($system) {
         case 0 :
            // linux ping
            if ($return == "true") {
               exec("ping -c 1 -w 1 " . $ip, $list);
            } else {
               exec("ping -c 1 -w 1 " . $ip, $list, $error);
            }
            $nb = count($list);
            if (isset($nb) && $return == "true") {
               for ($i = 0; $i < $nb; $i++) {
                  if (strpos($list[$i], "ttl=") > 0) {
                     return true;
                  }
               }
            }
            break;

         case 1 :
            //windows
            if ($return == "true") {
               exec("ping.exe -n 1 -w 100 -i 64 " . $ip, $list);
            } else {
               exec("ping.exe -n 1 -w 100 -i 64 " . $ip, $list, $error);
            }
            $nb = count($list);
            if (isset($nb) && $return == "true") {
               for ($i = 0; $i < $nb; $i++) {
                  if (strpos($list[$i], "TTL") > 0) {
                     return true;
                  }
               }
            }
            break;

         case 2 :
            //linux fping
            if ($return == "true") {
               exec("fping -r1 -c1 -t100 " . $ip, $list);
            } else {
               exec("fping -r1 -c1 -t100 " . $ip, $list, $error);
            }
            $nb = count($list);
            if (isset($nb) && $return == "true") {
               for ($i = 0; $i < $nb; $i++) {
                  if (strpos($list[$i], "bytes") > 0) {
                     return true;
                  }
               }
            }
            break;

         case 3 :
            // BSD ping
            if ($return == "true") {
               exec("ping -c 1 -W 1 " . $ip, $list);
            } else {
               exec("ping -c 1 -W 1 " . $ip, $list, $error);
            }
            $nb = count($list);
            if (isset($nb) && $return == "true") {
               for ($i = 0; $i < $nb; $i++) {
                  if (strpos($list[$i], "ttl=") > 0) {
                     return true;
                  }
               }
            }
            break;

         case 4 :
            // MacOSX ping
            if ($return == "true") {
               exec("ping -c 1 -t 1 " . $ip, $list);
            } else {
               exec("ping -c 1 -t 1 " . $ip, $list, $error);
            }
            $nb = count($list);
            if (isset($nb) && $return == "true") {
               for ($i = 0; $i < $nb; $i++) {
                  if (strpos($list[$i], "ttl=") > 0) {
                     return true;
                  }
               }
            }
            break;
      }
      if ($return == "list") {
         $list_str = implode('<br />', $list);

         return [$list_str, $error];
      } else {
         return false;
      }
   }

   /**
    * @param $system
    * @param $ip
    *
    * @return array
    */
   function getHostnameByPing($system, $ip) {
      $error = 1;
      $list  = '';
      switch ($system) {
         case 0 :
            // linux host
            exec("ping -c 1 -w 1 -a " . $ip, $list, $error);
            break;

         case 1 :
            //windows
            exec("ping.exe -n 1 -w 100 -i 64 -a " . $ip, $list, $error);
            break;
      }
      $list_str = implode('<br />', $list);
      //      return [$list_str, $error];
      return $list[1];
   }

   /**
    * Show form
    *
    * @param type $ip
    * @param type $id_addressing
    */
   function showIPForm($ip) {
      echo Html::script(PLUGIN_ADDRESSING_DIR_NOFULL . "/addressing.js");

      $config = new PluginAddressingConfig();
      $config->getFromDB('1');
      $system = $config->fields["used_system"];

      $ping_equip = new PluginAddressingPing_Equipment();
      list($message, $error) = $ping_equip->ping($system, $ip);

      echo "<div class='alert alert-warning'>";

      echo "<div class='d-flex'>";

      echo "<div class='me-2'>";
      if ($error) {
         echo "<i style='color:forestgreen' class='fas fa-check-circle fa-2x'></i>";
      } else {
         echo "<i style='color:orange' class='fas fa-exclamation-triangle fa-2x'></i>";
      }
      echo "</div>";

      echo "<div>";
      echo "<h4>" . _n("IP address", "IP addresses", 1) . " : " . $ip . "</h4>";
      echo "<div class='text-muted'>";


      if ($error) {
         echo "<span style='color:forestgreen'>&nbsp;";
         echo __('Ping: no response - free IP', 'addressing');
      } else {
         echo "<span style='color:orange'>&nbsp;";
         echo __('Ping: got a response - used IP', 'addressing');
      }
      echo "</span>";

      echo "</div>";
      echo "</div>";
      echo "</div>";
      echo "</div>";
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $tabnum
    * @param int         $withtemplate
    *
    * @return bool
    */
   //   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   //   {
   //
   //      $ping = Session::haveRight('plugin_addressing_use_ping_in_equipment', '1');
   //
   //      if ($ping && in_array($item->getType(), PluginAddressingAddressing::getTypes())) {
   //         if ($item->getField('id')) {
   //            $options = ['obj' => $item];
   //
   //            $pingE = new self();
   //            $pingE->showForm($item->getField('id'), $options);
   //         }
   //      }
   //      return true;
   //   }
   //
   //
   //   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   //   {
   //
   //      $ping = Session::haveRight('plugin_addressing_use_ping_in_equipment', '1');
   //
   //      if ($ping && in_array($item->getType(), PluginAddressingAddressing::getTypes())) {
   //         if ($item->getField('id')) {
   //            return ['1' => __('IP ping', 'addressing')];
   //         }
   //      }
   //      return '';
   //   }
   //
   //

}
