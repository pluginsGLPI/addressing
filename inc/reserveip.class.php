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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginAddressingReserveip extends CommonDBTM
{
   const COMPUTER = 'Computer';
   const NETWORK  = 'NetworkEquipment';
   const PRINTER  = 'Printer';

   static $rightname = 'plugin_addressing';

   static function getTypeName($nb = 0)
   {
      return __("Reserve");
   }

   function getPortName($ip)
   {
      return "reserv-" . $ip;
   }

   /**
    * Show form
    *
    * @param type $input
    * @return type
    */
   function reserveip($input = array())
   {

      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      // Find computer
      $item = new $input['type']();
      $id   = 0;
      if (!$item->getFromDBByQuery("WHERE `name`='" . $input["name_reserveip"] . "' AND `entities_id`=" . $input['entities_id'] . " LIMIT 1")) {
         // Add computer
         $id = $item->add(array("name"        => $input["name_reserveip"],
                                "entities_id" => $input['entities_id'],
                                "locations_id" => $input["locations_id"],
                                "states_id"   => $input["states_id"],
                                "comment"     => $input["comment"]));
      } else {
         $id = $item->getID();
         //update item
         $item->update(array("id"          => $id,
                             "entities_id" => $input['entities_id'],
                             "states_id"   => $input["states_id"],
                             "locations_id" => $input["locations_id"],
                             "comment"     => $input["comment"]));
      }

      // Add a new port
      if ($id) {
         switch ($input['type']) {
            case 'NetworkEquipment' :
               $newinput = array(
                  "itemtype"                 => $input['type'],
                  "items_id"                 => $id,
                  "entities_id"              => $_SESSION["glpiactive_entity"],
                  "name"                     => self::getPortName($input["ip"]),
                  "instantiation_type"       => "NetworkPortAggregate",
                  "_create_children"         => 1,
                  "NetworkName__ipaddresses" => array("-100" => $input["ip"]),
                  "NetworkName_fqdns_id"     => $input["fqdns_id"],
                  "mac"                      => $input["mac"],
               );
               break;
            case 'Computer':
            case 'Printer':
               $newinput = array(
                  "itemtype"                 => $input['type'],
                  "items_id"                 => $id,
                  "entities_id"              => $_SESSION["glpiactive_entity"],
                  "name"                     => self::getPortName($input["ip"]),
                  "instantiation_type"       => "NetworkPortEthernet",
                  "_create_children"         => 1,
                  "NetworkName__ipaddresses" => array("-100" => $input["ip"]),
                  "NetworkName_fqdns_id"     => $input["fqdns_id"],
                  "mac"                      => $input["mac"],
               );
               break;
         }

         $np    = new NetworkPort();
         $newID = $np->add($newinput);

         Event::log($newID, "networkport", 5, "inventory",
            //TRANS: %s is the user login
                    sprintf(__('%s adds an item'), $_SESSION["glpiname"]));
      }
   }

   /**
    * Check mandatory fields
    *
    * @param type $input
    * @return boolean
    */
   function checkMandatoryFields($input)
   {
      $msg     = array();
      $checkKo = false;

      $mandatory_fields = array('name' => __("Object's name", 'addressing'),
                                'ip'   => _n("IP address", "IP addresses", 1));

      foreach ($input as $key => $value) {
         if (isset($mandatory_fields[$key])) {
            if ((isset($value) && empty($value)) || !isset($value)) {
               $msg[$key] = $mandatory_fields[$key];
               $checkKo   = true;
            }
         }
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), false, ERROR);
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
   function showForm($ip, $id_addressing, $randmodal)
   {
      global $CFG_GLPI;

      $addressing = new PluginAddressingAddressing();
      $addressing->getFromDB($id_addressing);

      $this->forceTable(PluginAddressingAddressing::getTable());
      $this->initForm(-1);
      $options['colspan'] = 2;
      $this->showFormHeader($options);

      echo "<input type='hidden' name='ip' value='" . $ip . "' />";
      echo "<input type='hidden' name='id_addressing' value='" . $id_addressing . "' />";
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
         echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/ok.png\" alt=\"ok\">&nbsp;";
         _e('Ping: ip address free', 'addressing');
      } else {
         echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/warning.png\" alt=\"warning\">&nbsp;";
         _e('Ping: not available IP address', 'addressing');
      }

      echo "</td></tr>";
      $strict_entities = Profile_User::getUserEntities($_SESSION['glpiID'],false);
      if (Session::haveAccessToOneOfEntities($strict_entities)
          && Session::isViewAllEntities()) {
         echo "<tr class='tab_bg_1'>
               <td>".__("Entity")."</td>
               <td>";

         $rand = Entity::dropdown(array('name' => 'entities_id',
                                        'entity' => $_SESSION["glpiactiveentities"],
                                        'value' => $addressing->fields['entities_id']));

         $params = array('action' => 'entities_networkip', 'entities_id' => '__VALUE__');
         Ajax::updateItemOnEvent("dropdown_entities_id".$rand, 'entities_networkip', $CFG_GLPI["root_doc"]."/plugins/addressing/ajax/addressing.php", $params);

         $params = array('action' => 'entities_location', 'entities_id' => '__VALUE__', 'value' => $addressing->fields["locations_id"]);
         Ajax::updateItemOnEvent("dropdown_entities_id".$rand, 'entities_location', $CFG_GLPI["root_doc"]."/plugins/addressing/ajax/addressing.php", $params);

         $params = array('action' => 'entities_fqdn', 'entities_id' => '__VALUE__', 'value' => $addressing->fields["fqdns_id"]);
         Ajax::updateItemOnEvent("dropdown_entities_id".$rand, 'entities_fqdn', $CFG_GLPI["root_doc"]."/plugins/addressing/ajax/addressing.php", $params);

         echo "</td><td></td>";
         echo "</tr>";
      }

      echo "</td></tr>";
      echo "<tr class='tab_bg_1'>
               <td>".__("Location")."</td>
               <td><div id='entities_location'>";

      Dropdown::show('Location', array('name'   => "locations_id",
                                       'value'  => $addressing->fields["locations_id"],
                                       'entity' => $addressing->fields['entities_id']));
      echo "</div></td><td></td>";
      echo "</tr>";

      echo "</tr>";
      echo "<tr class='tab_bg_1'>
               <td>".__("Type")."</td>
               <td>";
      Dropdown::showFromArray('type',array(PluginAddressingReserveip::COMPUTER => Computer::getTypeName(),
                                           PluginAddressingReserveip::NETWORK => NetworkEquipment::getTypeName(),
                                           PluginAddressingReserveip::PRINTER => Printer::getTypeName()), array('on_change' => "nameIsThere(\"".$CFG_GLPI['root_doc']."\");"));
      echo "</td><td></td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>
               <td>".__("Name")." : </td><td>";
      $option = array('option' => "onChange=\"javascript:nameIsThere('".$CFG_GLPI['root_doc']."');\"");
      Html::autocompletionTextField($this,"name_reserveip",$option);
      echo "</td><td><div style=\"display: none;\" id='nameItem'>";
      echo "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\">&nbsp;";
      _e('Name already in use', 'addressing');
      echo "</div></td>
            </tr>";

      echo "<tr class='tab_bg_1'>
               <td>".__("Status")." : </td>
               <td>";
      Dropdown::show("State");
      echo "</td>
             <td></td> </tr>";

      echo "<tr class='tab_bg_1'>
               <td>".__("MAC address")." :</td>
               <td><input type='text' name='mac' value='' size='40' /></td>
            <td></td></tr>";

      echo "<tr class='tab_bg_1'>
               <td>".FQDN::getTypeName(1)." :</td>
               <td><div id='entities_fqdn'>";
      Dropdown::show('FQDN', array('name'  => "fqdns_id",
                                   'value' => $addressing->fields["fqdns_id"],
                                   'entity'=> $addressing->fields['entities_id']));
      echo "</div></td><td></td></tr>";

      echo "<tr class='tab_bg_1'>
               <td>".__("Network")." :</td>
               <td><div id='entities_networkip'>";
      IPNetwork::showIPNetworkProperties($addressing->fields['entities_id']);
      echo "</div></td>
            <td></td></tr>";

      echo "<tr class='tab_bg_1'>
               <td>".__("Comments")." :</td>
               <td colspan='2'><textarea cols='75' rows='5' name='comment' ></textarea></td>
            </tr>";

      echo "<tr class='tab_bg_1'>
               <td colspan='4' class='center'>
                  <input type='submit' name='add' class='submit' value='".__("Validate the reservation", 'addressing')."' "
           . "onclick=\"".Html::jsGetElementbyID("reserveip".$randmodal).".dialog('close');window.location.reload();return true;\"/>
               </td>
            </tr>
            </table>";

      Html::closeForm();
   }

}
