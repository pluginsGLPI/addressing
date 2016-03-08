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

class PluginAddressingAddressing extends CommonDBTM {

   static $rightname = "plugin_addressing";

   static function getTypeName($nb=0) {

      return _n('IP Adressing', 'IP Adressing', $nb, 'addressing');
   }

   function getSearchOptions() {

      $tab = array();

      $tab['common']             = self::getTypeName(2);

      $tab[1]['table']           = $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = $this->getType();

      $tab[2]['table']           = 'glpi_networks';
      $tab[2]['field']           = 'name';
      $tab[2]['name']            = _n('Network', 'Networks', 2);
      $tab[2]['datatype']        = 'dropdown';

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'comment';
      $tab[3]['name']            = __('Comments');
      $tab[3]['datatype']        = 'text';

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'use_ping';
      $tab[4]['name']            = __('Ping free Ip', 'addressing');
      $tab[4]['datatype']        = 'bool';

      $tab[30]['table']          = $this->getTable();
      $tab[30]['field']          = 'id';
      $tab[30]['name']           = __('ID');
      $tab[30]['datatype']       = 'number';

      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'dropdown';

      $tab[1000]['table']         = $this->getTable();
      $tab[1000]['field']         = 'begin_ip';
      $tab[1000]['name']          = __('First IP', 'addressing');
      $tab[1000]['nosearch']      = true;
      $tab[1000]['massiveaction'] = false;

      $tab[1001]['table']         = $this->getTable();
      $tab[1001]['field']         = 'end_ip';
      $tab[1001]['name']          = __('Last IP', 'addressing');
      $tab[1001]['nosearch']      = true;
      $tab[1001]['massiveaction'] = false;

      return $tab;
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(__CLASS__, $ong, $options);
      return $ong;
   }


   function getTitle() {

      return __('Report for the IP Range', 'addressing')." ".$this->fields["begin_ip"]." ".
             __('to')." ".$this->fields["end_ip"];
   }


   function dropdownSubnet($entity) {
      global $DB;

      $rand = mt_rand();
      echo "<select name='_subnet' id='plugaddr_subnet' onChange='plugaddr_ChangeList();'>";

      $sql = "SELECT DISTINCT `completename`
              FROM `glpi_ipnetworks`" .
              getEntitiesRestrictRequest(" WHERE ","glpi_ipnetworks","entities_id",$entity) ."";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>";
      foreach ($DB->request($sql) as $network) {
         echo "<option value='".$network["completename"]."'>".$network["completename"]."</option>";
      }
      echo "</select>\n";
   }


   function showForm ($ID, $options=array()) {

      $this->initForm($ID, $options);

      $options['formoptions']
            = "onSubmit='return plugaddr_Check(\"".__('Invalid data !!', 'addressing')."\")'";
      $this->showFormHeader($options);

      $PluginAddressingConfig = new PluginAddressingConfig();
      $PluginAddressingConfig->getFromDB('1');

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,"name");
      echo "</td>";

      if ($PluginAddressingConfig->fields["alloted_ip"]) {
         echo "<td>".__('Assigned IP', 'addressing')."</td><td>";
         Dropdown::showYesNo('alloted_ip',$this->fields["alloted_ip"]);
         echo "</td>";
      } else {
         echo "<td>"; 
         echo Html::hidden('alloted_ip', array('value' => 0));
         //echo "<input type='hidden' name='alloted_ip' value='0'></td><td></td>";
         echo "</td><td></td>";
      }

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Select the network', 'addressing')."</td>";
      echo "<td>";
      Dropdown::show('Network', array('name'  => "networks_id",
                                      'value' => $this->fields["networks_id"]));
      echo "</td>";

      if ($PluginAddressingConfig->fields["free_ip"]) {
         echo "<td>".__('Free Ip', 'addressing')."</td><td>";
         Dropdown::showYesNo('free_ip', $this->fields["free_ip"]);
         echo "</td>";
      } else {
         echo "<td>"; 
         echo Html::hidden('free_ip', array('value' => 0));
         echo "</td><td></td>";
         //echo "<td><input type='hidden' name='free_ip' value='0'></td><td></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Detected subnet list', 'addressing')."</td>";
      echo "<td>";
      $this->dropdownSubnet($ID>0 ? $this->fields["entities_id"] : $_SESSION["glpiactive_entity"]);
      echo "</td>";

      if ($PluginAddressingConfig->fields["double_ip"]) {
         echo "<td>".__('Same IP', 'addressing')."</td><td>";
         Dropdown::showYesNo('double_ip', $this->fields["double_ip"]);
         echo "</td>";
      } else {
         //echo "<td><input type='hidden' name='double_ip' value='0'></td><td></td>";
         echo "<td>"; 
         echo Html::hidden('double_ip', array('value' => 0));
         echo "</td><td></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('First IP', 'addressing')."</td>"; // Subnet
      echo "<td>";
      echo "<input type='text' id='plugaddr_ipdeb0' value='' name='_ipdeb0' size='3' ".
             "onChange='plugaddr_ChangeNumber(\"".__('Invalid data !!', 'addressing')."\");'>.";
      echo "<input type='text' id='plugaddr_ipdeb1' value='' name='_ipdeb1' size='3' ".
             "onChange='plugaddr_ChangeNumber(\"".__('Invalid data !!', 'addressing')."\");'>.";
      echo "<input type='text' id='plugaddr_ipdeb2' value='' name='_ipdeb2' size='3' ".
             "onChange='plugaddr_ChangeNumber(\"".__('Invalid data !!', 'addressing')."\");'>.";
      echo "<input type='text' id='plugaddr_ipdeb3' value='' name='_ipdeb3' size='3' ".
             "onChange='plugaddr_ChangeNumber(\"".__('Invalid data !!', 'addressing')."\");'>";
      echo "</td>";

      if ($PluginAddressingConfig->fields["reserved_ip"]) {
         echo "<td>".__('Reserved IP', 'addressing')."</td><td>";
         Dropdown::showYesNo('reserved_ip',$this->fields["reserved_ip"]);
         echo "</td>";
      } else {
         echo "<td>"; 
         echo Html::hidden('reserved_ip', array('value' => 0));
         echo "</td><td></td>";
         //echo "<td><input type='hidden' name='reserved_ip' value='0'></td><td></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Last IP', 'addressing')."</td>"; // Mask
      echo "<td>";
      echo "<input type='text' id='plugaddr_ipfin0' value='' name='_ipfin0' size='3' ".
             "onChange='plugaddr_ChangeNumber(\"".__('Invalid data !!', 'addressing')."\");'>.";
      echo "<input type='text' id='plugaddr_ipfin1' value='' name='_ipfin1' size='3' ".
             "onChange='plugaddr_ChangeNumber(\"".__('Invalid data !!', 'addressing')."\");'>.";
      echo "<input type='text' id='plugaddr_ipfin2' value='' name='_ipfin2' size='3' ".
             "onChange='plugaddr_ChangeNumber(\"".__('Invalid data !!', 'addressing')."\");'>.";
      echo "<input type='text' id='plugaddr_ipfin3' value='' name='_ipfin3' size='3' ".
             "onChange='plugaddr_ChangeNumber(\"".__('Invalid data !!', 'addressing')."\");'>";
      echo "</td>";

      if ($PluginAddressingConfig->fields["use_ping"]) {
         echo "<td>".__('Ping free Ip', 'addressing')."</td><td>";
         Dropdown::showYesNo('use_ping', $this->fields["use_ping"]);
         echo "</td>";
      } else {
         echo "<td>"; 
         echo Html::hidden('use_ping', array('value' => 0));
         echo "</td><td></td>";
         //echo "<td><input type='hidden' name='use_ping' value='0'></td><td></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Report for the IP Range', 'addressing')."</td>"; // Mask
      echo "<td>";
      echo "<input type='hidden' id='plugaddr_ipdeb' value='".$this->fields["begin_ip"]."' name='begin_ip'>";
      echo "<input type='hidden' id='plugaddr_ipfin' value='".$this->fields["end_ip"]."' name='end_ip'>";
      echo "<div id='plugaddr_range'>-</div>";
      if ($ID > 0) {
         $js = "plugaddr_Init(\"".__('Invalid data !!', 'addressing')."\");";
         echo Html::scriptBlock($js);
      }
      echo "</td>";
      echo "<td></td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td colspan = '4'>";
      echo "<table cellpadding='2' cellspacing='2'><tr><td>";
      echo __('Comments')."</td></tr>";
      echo "<tr><td class='center'>".
            "<textarea cols='125' rows='3' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td></tr></table>";
      echo "</td>";

      $this->showFormButtons($options);

      return true;
   }


   /*
   function linkToExport($ID) {

      echo "<div class='center'>";
      echo "<a href='./report.form.php?id=".$ID."&export=true'>".__('Export')."</a>";
      echo "</div>";
   }*/


   function compute($start) {
      global $DB, $CFG_GLPI;

      // sprintf to solve 32/64 bits issue
      $ipdeb = sprintf("%u", ip2long($this->fields["begin_ip"]));
      $ipfin = sprintf("%u", ip2long($this->fields["end_ip"]));

      if (!isset($_GET["export_all"])) {
         if (isset($start)) {
            $ipdeb += $start;
         }
         if ($ipdeb > $ipfin) {
            $ipdeb = $ipfin;
         }
         if ($ipdeb+$_SESSION["glpilist_limit"] <= $ipfin) {
            $ipfin = $ipdeb+$_SESSION["glpilist_limit"]-1;
         }
      }

      $result = array();
      for ($ip=$ipdeb ; $ip<=$ipfin ; $ip++) {
         $result["IP".$ip] = array();
      }

      $sql = "SELECT `port`.`id`,
                     'NetworkEquipment' AS itemtype,
                     `dev`.`id` AS on_device,
                     `dev`.`name` AS dname,
                     '' AS pname,
                     `glpi_ipaddresses`.`name` as ip,
                     `port`.`mac`,
                     `dev`.`users_id`,
                     INET_ATON(`glpi_ipaddresses`.`name`) AS ipnum
               FROM `glpi_networkports` port
               LEFT JOIN `glpi_networkequipments` dev ON (`port`.`items_id` = `dev`.`id`
                     AND `port`.`itemtype` = 'NetworkEquipment')
               LEFT JOIN `glpi_networknames` ON (`port`.`id` =  `glpi_networknames`.`items_id`)
               LEFT JOIN `glpi_ipaddresses` ON (`glpi_ipaddresses`.`items_id` = `glpi_networknames`.`id`)
               WHERE INET_ATON(`glpi_ipaddresses`.`name`) >= '$ipdeb'
                     AND INET_ATON(`glpi_ipaddresses`.`name`) <= '$ipfin'
                     AND `dev`.`is_deleted` = 0
                     AND `dev`.`is_template` = 0 " .
                     getEntitiesRestrictRequest(" AND ","dev");
      

      if ($this->fields["networks_id"]) {
         $sql .= " AND `dev`.`networks_id` = ".$this->fields["networks_id"];
      }
      
      //$ntypes = $CFG_GLPI["networkport_types"];
      //foreach ($ntypes as $k => $v) {
      //   if ($v == 'PluginFusioninventoryUnknownDevice') {
      //      unset($ntypes[$k]);
      //   }
      //}
      
      foreach ($CFG_GLPI["networkport_types"] as $type) {
         $itemtable = getTableForItemType($type);
         if (!($item = getItemForItemtype($type))) {
            continue;
         }
         $sql .= " UNION SELECT `port`.`id`,
                                 '" . $type . "' AS `itemtype`,
                                 `port`.`items_id`,
                                `dev`.`name` AS dname,
                                `port`.`name` AS pname,
                                `glpi_ipaddresses`.`name` as ip,
                                `port`.`mac`";
         
         if ($type == 'PluginFusioninventoryUnknownDevice') {
            $sql .= " ,0 AS `users_id` ";
         } else {
            $sql .= " ,`dev`.`users_id` ";
         }
         $sql .= " , INET_ATON(`glpi_ipaddresses`.`name`) AS ipnum ";
         $sql .= " FROM `glpi_networkports` port
                        LEFT JOIN `" . $itemtable . "` dev ON (`port`.`items_id` = `dev`.`id`
                              AND `port`.`itemtype` = '" . $type . "')
                        LEFT JOIN `glpi_networknames` ON (`port`.`id` =  `glpi_networknames`.`items_id`)
                        LEFT JOIN `glpi_ipaddresses` ON (`glpi_ipaddresses`.`items_id` = `glpi_networknames`.`id`)
                        WHERE INET_ATON(`glpi_ipaddresses`.`name`) >= '$ipdeb'
                              AND INET_ATON(`glpi_ipaddresses`.`name`) <= '$ipfin'" .
                              getEntitiesRestrictRequest(" AND ", "dev");
         
         if ($item->maybeDeleted()) {
            $sql.=" AND `dev`.`is_deleted` = '0'";
         }
         
         if ($item->maybeTemplate()) {
            $sql.=" AND `dev`.`is_template` = '0'";
         }
      
         if ($this->fields["networks_id"] 
               && $type != 'Peripheral' 
                  && $type != 'Phone' 
                     && $type != 'PluginFusioninventoryUnknownDevice') {
            $sql .= " AND `dev`.`networks_id`= ".$this->fields["networks_id"];
         }
      }
      $res = $DB->query($sql);
      if ($res) {
         while ($row=$DB->fetch_assoc($res)) {
            $result["IP".$row["ipnum"]][]=$row;
         }
      }

      return $result;
   }


   function showReport($params) {
      global $CFG_GLPI;

      $PluginAddressingReport = new PluginAddressingReport();

      // Default values of parameters
      $default_values["start"]  = $start  = 0;
      $default_values["id"]     = $id     = 0;
      $default_values["export"] = $export = false;

      foreach ($default_values as $key => $val) {
         if (isset($params[$key])) {
            $$key=$params[$key];
         }
      }

      if ($this->getFromDB($id)) {
         $result = $this->compute($start);
         $nbipf = 0; // ip libres
         $nbipr = 0; // ip reservees
         $nbipt = 0; // ip trouvees
         $nbipd = 0; // doublons

         foreach ($result as $ip => $lines) {
            if (count($lines)) {
               if (count($lines)>1) {
                  $nbipd++;
               }
               if ((isset($lines[0]['pname']) && strstr($lines[0]['pname'],"reserv")))
                  $nbipr++;
               $nbipt++;
            } else {
               $nbipf++;
            }
         }

         ////title
         echo "<div class='spaced'>";
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2 left'>";
         echo "<td>";
         if ($this->fields['free_ip']) {
            echo __('Number of free ip', 'addressing')." ".$nbipf."<br>" ;
         }
         if ($this->fields['reserved_ip']) {
            echo __('Number of reserved ip', 'addressing')." ".$nbipr."<br>" ;
         }
         if ($this->fields['alloted_ip']) {
            echo __('Number of assigned ip (no doubles)', 'addressing')." ".$nbipt."<br>" ;
         }
         if ($this->fields['double_ip']) {
            echo __('Doubles', 'addressing')." ".$nbipd."<br>" ;
         }
         echo "</td>";
         echo "<td>";
         if ($this->fields['double_ip']) {
            echo "<span class='plugin_addressing_ip_double'>".
               __('Red row', 'addressing')."</span> - ".__('Same Ip', 'addressing')."<br>";
         }
         if (isset($this->fields['use_ping']) && $this->fields['use_ping']) {
            echo __('Ping free Ip', 'addressing')."<br>";
            echo "<span class='plugin_addressing_ping_off'>".
               __('Ping: got a response - used Ip', 'addressing').
                 "</span><br>";
            echo "<span class='plugin_addressing_ping_on'>".
               __('Ping: no response - free Ip', 'addressing').
                 "</span>";
         } else {
            echo "<span class='plugin_addressing_ip_free'>".
               __('Blue row', 'addressing')."</span> - ".__('Free Ip', 'addressing')."<br>";
         }
         if ($this->fields['reserved_ip']) {
            echo "<span class='plugin_addressing_ip_reserved'>".
               __('Green row', 'addressing')."</span> - ".__('Reserved Ip', 'addressing')."<br>";
         }

         echo "</td></tr>";
         
         echo "<tr><td colspan='2' align='center'>";
         echo "<a href='./report.form.php?id=".$this->getID()."&export=true'>".__('Export')."</a>";
         echo "</td></tr>";
         
         echo "</table>";
         echo "</div>";

         $numrows = 1+ip2long($this->fields['end_ip'])-ip2long($this->fields['begin_ip']);
         if (strpos($_SERVER['PHP_SELF'],"report.form.php")) {
            Html::printPager($start, $numrows, $_SERVER['PHP_SELF'], "start=$start&amp;id=".$id,
                             'PluginAddressingReport');
         } else {
            Html::printAjaxPager("", $start, $numrows);
         }

         //////////////////////////liste ips////////////////////////////////////////////////////////////

         $ping_response = $PluginAddressingReport->displayReport($result, $this);

         if ($this->fields['use_ping']) {
            $total_realfreeip=$nbipf-$ping_response;
            echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2 center'>";
            echo "<td>";
            echo __('Real free Ip (Ping=KO)', 'addressing')." ".$total_realfreeip;
            echo "</td></tr>";
            echo "</table>";
         }
         echo "</div>";

      } else {
         echo "<div class='center'>".
               "<img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt='warning'><br><br><b>".
                 __('Problem detected with the IP Range', 'addressing')."</b></div>";
      }
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         if ($tabnum == 0) {
            $item->showReport($_GET);
         }
      }
      return true;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      return array(Report::getTypeName(1));
   }

   //Massive Action
   function getSpecificMassiveActions($checkitem=NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);
      
      if (Session::haveRight('transfer', READ)
            && Session::isMultiEntitiesMode()
            && $isadmin) {
         $actions['PluginAddressingAddressing'.MassiveAction::CLASS_ACTION_SEPARATOR.'transfer'] = __('Transfer');
      }
      return $actions;
   }  


   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "transfer" :
            Dropdown::show('Entity');
            echo Html::submit(_x('button','Post'), array('name' => 'massiveaction'));
            return true;
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }
   
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      switch ($ma->getAction()) {
          case "transfer" :
            $input = $ma->getInput();

            if ($item->getType() == 'PluginAddressingAddressing') {
               foreach ($ids as $key) {
                  $values["id"] = $key;
                  $values["entities_id"] = $input['entities_id'];

                  if ($item->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                      $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            break;
      }
   }
}

?>