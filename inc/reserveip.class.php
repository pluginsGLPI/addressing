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
 * Class PluginAddressingReserveip
 */
class PluginAddressingReserveip extends CommonDBTM {

   static $rightname = 'plugin_addressing';

   static function getTypeName($nb = 0) {
      return __("IP reservation", "addressing");
   }

   public static function getTable($classname = null) {
      return "glpi_plugin_addressing_addressings";
   }

   /**
    * @param $ip
    *
    * @return string
    */
   function getPortName($ip) {
      return "reserv-" . $ip;
   }

   /**
    * Show form
    *
    * @param type $input
    *
    * @return type
    */
   function reserveip($input = []) {

      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      // Find computer
      $item = new $input['type']();
      if (!$item->getFromDBByCrit(["name"        => $input["name_reserveip"],
                                   "entities_id" => $input['entities_id']])) {
         // Add computer
         $id = $item->add(["name"         => $input["name_reserveip"],
                           "entities_id"  => $input['entities_id'],
                           "locations_id" => $input["locations_id"],
                           "states_id"    => $input["states_id"],
                           "comment"      => $input["comment"]]);
      } else {
         $id = $item->getID();
         //update item
         $item->update(["id"           => $id,
                        "entities_id"  => $input['entities_id'],
                        "states_id"    => $input["states_id"],
                        "locations_id" => $input["locations_id"],
                        "comment"      => $input["comment"]]);
      }

      // Add a new port
      if ($id) {
         switch ($input['type']) {
            case 'NetworkEquipment' :
               $newinput = [
                  "itemtype"                 => $input['type'],
                  "items_id"                 => $id,
                  "entities_id"              => $_SESSION["glpiactive_entity"],
                  "name"                     => self::getPortName($input["ip"]),
                  "instantiation_type"       => "NetworkPortAggregate",
                  "_create_children"         => 1,
                  "NetworkName__ipaddresses" => ["-100" => $input["ip"]],
                  "NetworkName_fqdns_id"     => $input["fqdns_id"],
                  "mac"                      => $input["mac"],
               ];
               break;
            default :
               $newinput = [
                  "itemtype"                 => $input['type'],
                  "items_id"                 => $id,
                  "entities_id"              => $_SESSION["glpiactive_entity"],
                  "name"                     => self::getPortName($input["ip"]),
                  "instantiation_type"       => "NetworkPortEthernet",
                  "_create_children"         => 1,
                  "NetworkName__ipaddresses" => ["-100" => $input["ip"]],
                  "NetworkName_fqdns_id"     => $input["fqdns_id"],
                  "mac"                      => $input["mac"],
               ];
               break;
         }

         $np    = new NetworkPort();
         $newID = $np->add($newinput);

         \Glpi\Event::log($newID, "networkport", 5, "inventory",
            //TRANS: %s is the user login
                          sprintf(__('%s adds an item'), $_SESSION["glpiname"]));
      }
   }

   /**
    * Check mandatory fields
    *
    * @param type $input
    *
    * @return boolean
    */
   function checkMandatoryFields($input) {
      $msg     = [];
      $checkKo = false;

      $mandatory_fields = ['name_reserveip' => __("Object's name", 'addressing'),
                           'ip'   => _n("IP address", "IP addresses", 1)];

      foreach ($input as $key => $value) {
         if (isset($mandatory_fields[$key])) {
            if ((isset($value) && empty($value)) || !isset($value)) {
               $msg[$key] = $mandatory_fields[$key];
               $checkKo   = true;
            }
         }
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"),
                                                  implode(', ', $msg)), false, ERROR);
         return false;
      }
      return true;
   }

   /**
    * Show form
    *
    * @param type $ip
    * @param type $id_addressing
    */
   function showReservationForm($ip, $id_addressing, $rand) {
      global $CFG_GLPI;

      echo Html::script(PLUGIN_ADDRESSING_DIR_NOFULL."/addressing.js");

      $addressing = new PluginAddressingAddressing();
      $addressing->getFromDB($id_addressing);

      $this->forceTable(PluginAddressingAddressing::getTable());
      $this->initForm(-1);
      $options['colspan'] = 2;
      $this->showFormHeader($options);

      echo Html::hidden('ip', ['value' => $ip]);
      echo Html::hidden('id_addressing', ['value' => $id_addressing]);
      echo "<tr class='tab_bg_1'>
               <td>" . _n("IP address", "IP addresses", 1) . "</td>
               <td>" . $ip . "</td>
               <td>";
      $config = new PluginAddressingConfig();
      $config->getFromDB('1');
      $system = $config->fields["used_system"];

      $ping_equip = new PluginAddressingPing_Equipment();
      list($message, $error) = $ping_equip->ping($system, $ip);
      if ($error) {
         echo "<i class='fas fa-check-circle fa-1x' style='color:forestgreen'></i><span style='color:forestgreen'>&nbsp;";
         echo __('Ping: no response - free IP', 'addressing');
      } else {
         echo "<i class='fas fa-exclamation-triangle fa-1x' style='color:orange'></i><span style='color:orange'>&nbsp;";
         echo __('Ping: got a response - used IP', 'addressing');
      }
      echo "</span>";
      echo "</td></tr>";
      $strict_entities = Profile_User::getUserEntities($_SESSION['glpiID'], false);
      if (Session::haveAccessToOneOfEntities($strict_entities)
          && Session::canViewAllEntities()) {
         echo "<tr class='tab_bg_1'>
               <td>" . __("Entity") . "</td>
               <td>";

         $rand = Entity::dropdown(['name'   => 'entities_id',
                                   'entity' => $_SESSION["glpiactiveentities"],
                                   'value'  => $addressing->fields['entities_id']
                                  ]);

         $params = ['action' => 'entities_networkip', 'entities_id' => '__VALUE__'];
         Ajax::updateItemOnEvent("dropdown_entities_id" . $rand, 'entities_networkip',
                                 PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php",
                                 $params);

         $params = ['action' => 'entities_location', 'entities_id' => '__VALUE__',
                    'value'  => $addressing->fields["locations_id"]];
         Ajax::updateItemOnEvent("dropdown_entities_id" . $rand, 'entities_location',
                                 PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php",
                                 $params);

         $params = ['action' => 'entities_fqdn', 'entities_id' => '__VALUE__',
                    'value'  => $addressing->fields["fqdns_id"]];
         Ajax::updateItemOnEvent("dropdown_entities_id" . $rand, 'entities_fqdn',
                                 PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php",
                                 $params);

         echo "</td><td></td>";
         echo "</tr>";
      }

      echo "</td></tr>";
      echo "<tr class='tab_bg_1'>
                     <td>" . __("Location") . "</td>
                     <td><div id='entities_location'>";

      Dropdown::show('Location', ['name'   => "locations_id",
                                  'value'  => $addressing->fields["locations_id"],
                                  'entity' => $addressing->fields['entities_id']
      ]);
      echo "</div></td><td></td>";
      echo "</tr>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>
               <td>" . __("Type") . "</td>
               <td>";
      $types = PluginAddressingAddressing::dropdownItemtype();
      Dropdown::showFromArray('type', $types,
                              ['on_change' => "nameIsThere(\"" .PLUGIN_ADDRESSING_WEBDIR . "\");"]);
      echo "</td><td></td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>
               <td>" . __("Name") . " : </td><td>";
      $option = ['onChange' => "nameIsThere(\"" . PLUGIN_ADDRESSING_WEBDIR . "\");", 'id' => 'name_reserveip'];
      echo Html::input('name_reserveip', $option);
      echo "</td><td><div style=\"display: none;\" id='nameItem'>";
      echo "<i class='fas fa-exclamation-triangle fa-2x' style='color:orange'></i>&nbsp;";
      echo __('Name already in use', 'addressing');
      echo "</div></td>
            </tr>";

      echo "<tr class='tab_bg_1'>
               <td>" . __("Status") . " : </td>
               <td>";
      State::dropdown(['entity' => $addressing->fields["entities_id"]]);
      echo "</td>
             <td></td> </tr>";

      echo "<tr class='tab_bg_1'>
               <td>" . __("MAC address") . " :</td><td>";
      echo Html::input('mac', ['size' => 40]);
      echo "</td>
            <td></td></tr>";

      echo "<tr class='tab_bg_1'>
               <td>" . FQDN::getTypeName(1) . " :</td>
               <td><div id='entities_fqdn'>";
      Dropdown::show('FQDN', ['name'   => "fqdns_id",
                              'value'  => $addressing->fields["fqdns_id"],
                              'entity' => $addressing->fields['entities_id']]);
      echo "</div></td><td></td></tr>";

      echo "<tr class='tab_bg_1'>
               <td>" . __("Network") . " :</td>
               <td><div id='entities_networkip'>";
      IPNetwork::showIPNetworkProperties($addressing->fields['entities_id']);
      echo "</div></td>
            <td></td></tr>";

      echo "<tr class='tab_bg_1'>
               <td>" . __("Comments") . " :</td>
               <td colspan='2'>";
      Html::textarea(['name'            => 'comment',
                      'cols'       => 75,
                      'rows'       => 5,
                      'enable_richtext' => false]);
      echo "</td>
            </tr>";

      echo "<tr class='tab_bg_1'>
               <td colspan='4' class='center'>";
      echo Html::submit(__("Validate the reservation", 'addressing'), ['name'    => 'add',
                                                                       'class'   => 'btn btn-primary',
                                                                       'onclick' => "$('#reservation$rand').modal('hide');window.location.reload();return true;"]);
      echo "</td>
            </tr>";
      echo " </table>";

      Html::closeForm();
   }

}
