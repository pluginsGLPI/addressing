<?php

/*
 * @version $Id: report.class.php 153 2012-12-17 14:59:00Z tsmr $
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

class PluginAddressingReserveip extends CommonDBTM {

   static $rightname = 'plugin_addressing';

   static function getTypeName($nb = 0) {
      return __("Reserve");
   }

   function getPortName($ip) {
      return "reserv-".$ip;
   }

   /**
    * Show form
    * 
    * @param type $input
    * @return type
    */
   function reserveip($input = array()) {

      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      // Find computer
      $computer    = new Computer();
      $id_computer = 0;
      if (!$computer->getFromDBByQuery("WHERE `name`='".$input["computername"]."' AND `entities_id`=".$_SESSION["glpiactive_entity"]. " LIMIT 1")) {
         // Add computer
         $id_computer = $computer->add(array("name"        => $input["computername"],
                                             "entities_id" => $_SESSION["glpiactive_entity"],
                                             'states_id'   => $input["states_id"]));
      } else {
         $id_computer = $computer->getID();
      }

      // Add a new port
      if ($id_computer) {
         $newinput = array(
            "itemtype"                 => "Computer",
            "items_id"                 => $id_computer,
            "entities_id"              => $_SESSION["glpiactive_entity"],
            "name"                     => self::getPortName($input["ip"]),
            "instantiation_type"       => "NetworkPortEthernet",
            "mac"                      => $input["mac"],
            "NetworkName__ipaddresses" => array("-100" => $input["ip"]),
            "speed"                    => "0",
            "speed_other_value"        => "",
            "add"                      => __("Add"),
         );

         $np = new NetworkPort();
         $np->splitInputForElements($newinput);
         $newID = $np->add($newinput);
         $np->updateDependencies(1);
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
   function checkMandatoryFields($input) {
      $msg     = array();
      $checkKo = false;

      $mandatory_fields = array('computername' => __("Computer's name"),
                                'ip'           => _n("IP address", "IP addresses", 1));

      foreach ($input as $key => $value) {
         if (isset($mandatory_fields[$key])) {
            if ((isset($value) && empty($value)) || !isset($value)) {
               $msg[$key]   = $mandatory_fields[$key];
               $checkKo = true;
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
   function showForm($ip, $id_addressing) {

      $this->forceTable(PluginAddressingAddressing::getTable());
      $this->initForm(-1);
      $options['colspan']= 1;
      $this->showFormHeader($options);

      echo "<input type='hidden' name='ip' value='".$ip."' />";
      echo "<input type='hidden' name='id_addressing' value='".$id_addressing."' />";
      echo "<tr class='tab_bg_1'>
               <td>"._n("IP address", "IP addresses", 1)."</td>
               <td>".$ip."</td>
            <tr class='tab_bg_1'>
               <td>".__("Computer's name")." : </td>
               <td><input type='text' name='computername' value='' size='40'></td>
            </tr>
            <tr class='tab_bg_1'>
               <td>".__("Status")." : </td>
               <td>";
         Dropdown::show("State");
         echo "</td>
            </tr>
            <tr class='tab_bg_1'>
               <td>".__("MAC address")." :</td>
               <td><input type='text' name='mac' value='' size='40' /></td>
            </tr>
            <tr class='tab_bg_1'>
               <td colspan='4' class='center'>
                  <input type='submit' name='add' class='submit' value='".__("Validate the reservation", 'adressing')."' />
               </td>
            </tr>
            </table>";
      Html::closeForm();
   }

}
