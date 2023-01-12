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
 * Class PluginAddressingAddressing
 */
class PluginAddressingAddressing extends CommonDBTM {

   static $rightname = "plugin_addressing";

   static $types     = ['Computer', 'NetworkEquipment', 'Peripheral', 'Phone', 'Printer', 'Enclosure', 'PDU', 'Cluster'];
   public $dohistory = true;

   static function getTypeName($nb = 0) {

      return _n('IP Adressing', 'IP Adressing', $nb, 'addressing');
   }

   static function getIcon() {
      return "ti ti-map-pin";
   }

   /**
    * Actions done when item is deleted from the database
    *
    * @return nothing
    **/
   function cleanDBonPurge() {
      $temp1 = new PluginAddressingPinginfo();
      $temp1->deleteByCriteria(array('plugin_addressing_addressings_id' => $this->fields['id']));
      $temp2 = new PluginAddressingFilter();
      $temp2->deleteByCriteria(array('plugin_addressing_addressings_id' => $this->fields['id']));
   }

   public function rawSearchOptions() {

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];

      $tab[] = [
         'id'       => '2',
         'table'    => 'glpi_networks',
         'field'    => 'name',
         'name'     => _n('Network', 'Networks', 2),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '3',
         'table'    => $this->getTable(),
         'field'    => 'comment',
         'name'     => __('Comments'),
         'datatype' => 'text'
      ];

      $tab[] = [
         'id'       => '4',
         'table'    => $this->getTable(),
         'field'    => 'use_ping',
         'name'     => __('Ping free IP', 'addressing'),
         'datatype' => 'bool'
      ];

      $tab[] = [
         'id'       => '5',
         'table'    => 'glpi_locations',
         'field'    => 'name',
         'name'     => __('Location'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '6',
         'table'    => 'glpi_fqdns',
         'field'    => 'name',
         'name'     => FQDN::getTypeName(1),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '7',
         'table'    => 'glpi_vlans',
         'field'    => 'name',
         'name'     => Vlan::getTypeName(1),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '30',
         'table'    => $this->getTable(),
         'field'    => 'id',
         'name'     => __('ID'),
         'datatype' => 'number'
      ];

      $tab[] = [
         'id'       => '80',
         'table'    => 'glpi_entities',
         'field'    => 'completename',
         'name'     => __('Entity'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'            => '1000',
         'table'         => $this->getTable(),
         'field'         => 'begin_ip',
         'name'          => __('First IP', 'addressing'),
//         'nosearch'      => true,
         'massiveaction' => false
      ];

      $tab[] = [
         'id'            => '1001',
         'table'         => $this->getTable(),
         'field'         => 'end_ip',
         'name'          => __('Last IP', 'addressing'),
//         'nosearch'      => true,
         'massiveaction' => false
      ];

      $tab[] = [
         'id'            => '1',
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
         'massiveaction' => false
      ];

      return $tab;
   }


   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('PluginAddressingFilter', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }


   /**
    * @return string
    */
   function getTitle() {

      return __('Report for the IP Range', 'addressing') . " " . $this->fields["begin_ip"] . " " .
             __('to') . " " . $this->fields["end_ip"];
   }


   /**
    * @param $entity
    */
   //   function dropdownSubnet($entity) {
   //      global $DB;
   //
   //      $dbu         = new DbUtils();
   //      $sql         = "SELECT DISTINCT `completename`
   //              FROM `glpi_ipnetworks`" .
   //                     $dbu->getEntitiesRestrictRequest(" WHERE ", "glpi_ipnetworks", "entities_id", $entity);
   //      $networkList = [0 => Dropdown::EMPTY_VALUE];
   //      foreach ($DB->request($sql) as $network) {
   //         $networkList += [$network["completename"] => $network["completename"]];
   //      }
   //      $rand = mt_rand();
   //      $name = "_subnet";
   //      Dropdown::ShowFromArray($name, $networkList, ['rand'      => $rand,
   //                                                    'on_change' => 'plugaddr_ChangeList("dropdown_' . $name . $rand . '");']);
   //   }


   function post_getEmpty() {
      $this->fields['alloted_ip']  = 1;
      $this->fields['free_ip']     = 1;
      $this->fields['reserved_ip'] = 1;
      $this->fields['double_ip']   = 1;
   }

   function showForm($ID, $options = []) {

      Html::requireJs("addressing");
      $this->initForm($ID, $options);

      $options['formoptions']
         = "onSubmit='return plugaddr_Check(\"" . __('Invalid data !!', 'addressing') . "\")'";
      $this->showFormHeader($options);

      $PluginAddressingConfig = new PluginAddressingConfig();
      $PluginAddressingConfig->getFromDB('1');

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      echo Html::input('name', ['value' => $this->fields['name'], 'size' => 40]);
      echo "</td>";

      if ($PluginAddressingConfig->fields["alloted_ip"]) {
         echo "<td>" . __('Assigned IP', 'addressing') . "</td><td>";
         Dropdown::showYesNo('alloted_ip', $this->fields["alloted_ip"]);
         echo "</td>";
      } else {
         echo "<td>";
         echo Html::hidden('alloted_ip', ['value' => 0]);
         echo "</td><td></td>";
      }

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      //      echo "<td>" . __('Detected subnet list', 'addressing') . "</td>";
      //      echo "<td>";
      //      $this->dropdownSubnet($ID > 0 ? $this->fields["entities_id"] : $_SESSION["glpiactive_entity"]);
      echo "<td>" . __('First IP', 'addressing') . "</td>"; // Subnet
      echo "<td>";
      if (empty($this->fields["begin_ip"])) {
         $this->fields["begin_ip"] = "...";
      }
      $ipexploded = explode(".", $this->fields["begin_ip"]);
      $i          = 0;
      foreach ($ipexploded as $ipnum) {
         if ($ipnum > 255) {
            $ipexploded[$i] = '';
         }
         $i++;
      }
      echo Html::input('_ipdeb0', ['value'     => $ipexploded[0],
                                   'id'        => 'plugaddr_ipdeb0',
                                   'size'      => 3,
                                   'maxlength' => 3,
                                   'class'     => 'form-inline']);
      echo Html::input('_ipdeb1', ['value'     => $ipexploded[0],
                                   'id'        => 'plugaddr_ipdeb1',
                                   'size'      => 3,
                                   'maxlength' => 3,
                                   'class'     => 'form-inline']);
      echo Html::input('_ipdeb2', ['value'     => $ipexploded[0],
                                   'id'        => 'plugaddr_ipdeb2',
                                   'size'      => 3,
                                   'maxlength' => 3,
                                   'class'     => 'form-inline']);
      echo Html::input('_ipdeb3', ['value'     => $ipexploded[0],
                                   'id'        => 'plugaddr_ipdeb3',
                                   'size'      => 3,
                                   'maxlength' => 3,
                                   'class'     => 'form-inline']);

      echo "</td>";

      if ($PluginAddressingConfig->fields["free_ip"]) {
         echo "<td>" . __('Free IP', 'addressing') . "</td><td>";
         Dropdown::showYesNo('free_ip', $this->fields["free_ip"]);
         echo "</td>";
      } else {
         echo "<td>";
         echo Html::hidden('free_ip', ['value' => 0]);
         echo "</td><td></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Last IP', 'addressing') . "</td>"; // Mask
      echo "<td>";

      unset($ipexploded);
      if (empty($this->fields["end_ip"])) {
         $this->fields["end_ip"] = "...";
      }
      $ipexploded = explode(".", $this->fields["end_ip"]);
      $j          = 0;
      foreach ($ipexploded as $ipnum) {
         if ($ipnum > 255) {
            $ipexploded[$j] = '';
         }
         $j++;
      }

      echo "<script type='text/javascript'>
      function test(id) {
         if (document.getElementById('plugaddr_ipfin' + id).value == '') {
            if (id == 3) {
               document.getElementById('plugaddr_ipfin' + id).value = '254';
            } else {
               document.getElementById('plugaddr_ipfin' + id).value = " .
           "document.getElementById('plugaddr_ipdeb' + id).value;
            }
         }
      }
      </script>";

      echo Html::input('_ipfin0', ['value'     => $ipexploded[0],
                                   'id'        => 'plugaddr_ipfin0',
                                   'size'      => 3,
                                   'maxlength' => 3,
                                   'class'     => 'form-inline',
                                   'onfocus'   => 'test(0)']);
      echo Html::input('_ipfin1', ['value'     => $ipexploded[0],
                                   'id'        => 'plugaddr_ipfin1',
                                   'size'      => 3,
                                   'maxlength' => 3,
                                   'class'     => 'form-inline',
                                   'onfocus'   => 'test(1)']);
      echo Html::input('_ipfin2', ['value'     => $ipexploded[0],
                                   'id'        => 'plugaddr_ipfin2',
                                   'size'      => 3,
                                   'maxlength' => 3,
                                   'class'     => 'form-inline',
                                   'onfocus'   => 'test(2)']);
      echo Html::input('_ipfin3', ['value'     => $ipexploded[0],
                                   'id'        => 'plugaddr_ipfin3',
                                   'size'      => 3,
                                   'maxlength' => 3,
                                   'class'     => 'form-inline',
                                   'onfocus'   => 'test(3)']);
      echo "</td>";

      if ($PluginAddressingConfig->fields["double_ip"]) {
         echo "<td>" . __('Same IP', 'addressing') . "</td><td>";
         Dropdown::showYesNo('double_ip', $this->fields["double_ip"]);
         echo "</td>";
      } else {
         echo "<td>";
         echo Html::hidden('double_ip', ['value' => 0]);
         echo "</td><td></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . _n('VLAN', 'VLANs', 1). "</td>";
      echo "<td>";
      Dropdown::show('Vlan', ['name'   => "vlans_id",
                                  'value'  => $this->fields["vlans_id"],
                                  'entity' => $this->fields['entities_id']]);
      echo "</td>";

      if ($PluginAddressingConfig->fields["reserved_ip"]) {
         echo "<td>" . __('Reserved IP', 'addressing') . "</td><td>";
         Dropdown::showYesNo('reserved_ip', $this->fields["reserved_ip"]);
         echo "</td>";
      } else {
         echo "<td>";
         echo Html::hidden('reserved_ip', ['value' => 0]);
         echo "</td><td></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>" . __('Report for the IP Range', 'addressing') . "</td>"; // Mask
      echo "<td>";
      echo Html::hidden('begin_ip', ['value' => $this->fields["begin_ip"],
                                     'id'    => 'plugaddr_ipdeb']);
      echo Html::hidden('end_ip', ['value' => $this->fields["end_ip"],
                                   'id'    => 'plugaddr_ipfin']);
      echo "<div id='plugaddr_range'>-</div>";
      if ($ID > 0) {
         $js = "plugaddr_Init(\"" . __('Invalid data !!', 'addressing') . "\");";
         echo Html::scriptBlock('$(document).ready(function() {' . $js . '});');
      }
      echo "</td>";
      if ($PluginAddressingConfig->fields["use_ping"]) {
         echo "<td>" . __('Ping free IP', 'addressing') . "</td><td>";
         Dropdown::showYesNo('use_ping', $this->fields["use_ping"]);
         echo "</td>";
      } else {
         echo "<td>";
         echo Html::hidden('use_ping', ['value' => 0]);
         echo "</td><td></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Comments') . "</td>";
      echo "<td class='center' colspan='3'>";
      Html::textarea(['name'            => 'comment',
                      'value'           => $this->fields["comment"],
                      'cols'            => 125,
                      'rows'            => 3,
                      'enable_richtext' => false]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><th colspan='4'>" . _n('Filter', 'Filters', 1, 'addressing');
      echo "</th>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Network') . "</td>";
      echo "<td>";
      Dropdown::show('Network', ['name'  => "networks_id",
                                 'value' => $this->fields["networks_id"]]);
      echo "</td>";
       echo "<td>" . __('Use the networks as filter', 'addressing') . "</td>";
       echo "<td>";
       Dropdown::showYesNo("use_as_filter", $this->fields["use_as_filter"]);
       Html::showToolTip(nl2br(__('The display of items depends on these criterias', 'addressing')));
      echo "</td>";
      echo "</tr>";

       echo "<tr class='tab_bg_1'><th colspan='4'>" . __('Default fields for reservation', 'addressing');
       echo "</th>";

       echo "<tr class='tab_bg_1'>";
       echo "<td>" . __('Location') . "</td>";
       echo "<td>";
       Dropdown::show('Location', ['name'   => "locations_id",
                                   'value'  => $this->fields["locations_id"],
                                   'entity' => $this->fields['entities_id']]);
       echo "</td>";

       echo "<td>" . FQDN::getTypeName(1) . "</td>";
       echo "<td>";
       Dropdown::show('FQDN', ['name'   => "fqdns_id",
                               'value'  => $this->fields["fqdns_id"],
                               'entity' => $this->fields['entities_id']]);
       echo "</td>";
       echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }


   /*
   function linkToExport($ID) {

      echo "<div class='center'>";
      echo "<a href='./report.form.php?id=".$ID."&export=true'>".__('Export')."</a>";
      echo "</div>";
   }*/


   /**
    * @param       $start
    * @param array $params
    *
    * @return array
    * @throws \GlpitestSQLError
    */
   function compute($start, $params = []) {
      global $DB;

      $ipdeb = 0;
      $ipfin = 0;
      foreach ($params as $key => $val) {
         if (isset($params[$key])) {
            $$key = $params[$key];
         }
      }

      if (isset($_GET["export"])) {
         if (isset($start)) {
            $ipdeb += $start;
         }
         if ($ipdeb > $ipfin) {
            $ipdeb = $ipfin;
         }
         if ($ipdeb + $_SESSION["glpilist_limit"] <= $ipfin) {
            $ipfin = $ipdeb + $_SESSION["glpilist_limit"] - 1;
         }
      }

      $result = [];
      for ($ip = $ipdeb; $ip <= $ipfin; $ip++) {
         $result["IP" . $ip] = [];
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
               WHERE (`glpi_ipaddresses`.`name` IS NOT NULL AND `glpi_ipaddresses`.`name` != '') AND `glpi_ipaddresses`.`version` LIKE 4
                 AND (INET_ATON(`glpi_ipaddresses`.`name`) BETWEEN '$ipdeb' AND '$ipfin')
                     AND `dev`.`is_deleted` = 0
                     AND `dev`.`is_template` = 0 ";
      $dbu = new DbUtils();
      if (isset($entities)) {
         $sql .= $dbu->getEntitiesRestrictRequest(" AND ", "dev", "entities_id", $entities);
      } else {
         $sql .= $dbu->getEntitiesRestrictRequest(" AND ", "dev", "entities_id", $this->fields['entities_id']);
      }
      if (isset($type_filter)) {
         $sql .= " AND `glpi_ipaddresses`.`mainitemtype` = '" . $type_filter . "'";
      }

      if ($this->fields["use_as_filter"] == 1 && $this->fields["networks_id"]) {
         $sql .= " AND `dev`.`networks_id` = " . $this->fields["networks_id"];
      }

      //$ntypes = $CFG_GLPI["networkport_types"];
      //foreach ($ntypes as $k => $v) {
      //   if ($v == 'PluginFusioninventoryUnknownDevice') {
      //      unset($ntypes[$k]);
      //   }
      //}
      if (isset($type_filter)) {
         $types = [$type_filter];
      } else {
         $types = self::getTypes(true);
      }

      $dbu = new DbUtils();

      foreach ($types as $type) {

         if (!($item = $dbu->getItemForItemtype($type))) {
            continue;
         }
         $itemtable = $dbu->getTableForItemType($type);
         $sql       .= " UNION (SELECT `port`.`id`,
                                    '" . $type . "' AS `itemtype`,
                                    `port`.`items_id`,
                                   `dev`.`name` AS dname,
                                   `port`.`name` AS pname,
                                   `glpi_ipaddresses`.`name` as ip,
                                   `port`.`mac`";

         if ($type == 'PluginFusioninventoryUnknownDevice'
             || $type == 'Enclosure'
             || $type == 'PDU'
             || $type == 'Cluster'
             || $type == 'Unmanaged') {
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
                           WHERE (`glpi_ipaddresses`.`name` IS NOT NULL AND `glpi_ipaddresses`.`name` != '') AND `glpi_ipaddresses`.`version` LIKE 4
                           AND (INET_ATON(`glpi_ipaddresses`.`name`) BETWEEN '$ipdeb' AND '$ipfin')";
         $dbu = new DbUtils();
         if (isset($entities)) {
            $sql .= $dbu->getEntitiesRestrictRequest(" AND ", "dev", "entities_id", $entities);
         } else {
            $sql .= $dbu->getEntitiesRestrictRequest(" AND ", "dev", "entities_id", $this->fields['entities_id']);
         }

         if (isset($type_filter)) {
            $sql .= " AND `glpi_ipaddresses`.`mainitemtype` = '" . $type_filter . "'";
         }

         if ($item->maybeDeleted()) {
            $sql .= " AND `dev`.`is_deleted` = '0'";
         }

         if ($item->maybeTemplate()) {
            $sql .= " AND `dev`.`is_template` = '0'";
         }

         if ($this->fields["use_as_filter"] == 1 && $this->fields["networks_id"]
             && $DB->fieldExists($type::getTable(), 'networks_id')) {
            $sql .= " AND `dev`.`networks_id`= " . $this->fields["networks_id"];
         }
         $sql .= " GROUP BY `ip`, `port`.`mac` ORDER BY ipnum)";
      }
      $res = $DB->query($sql);
      if ($res) {
         while ($row = $DB->fetchAssoc($res)) {
            $result["IP" . $row["ipnum"]][] = $row;
         }
      }
      foreach ($result as $key => $data) {
         if (count($data) > 1) {
            foreach ($data as $keyip => $ip) {
               if (empty($ip['pname'])) {
                  unset($result[$key][$keyip]);
               }

            }
         }
      }
      if (isset($type_filter)) {
         foreach ($result as $key => $data) {
            if (empty($data)) {
               unset($result[$key]);
            }
         }
      }
      return $result;
   }

   /**
    * @param $params
    */
   function showReport($params) {
      global $CFG_GLPI;

      $PluginAddressingReport = new PluginAddressingReport();

      // Default values of parameters
      $default_values["start"]  = $start = 0;
      $default_values["id"]     = $id = 0;
      $default_values["export"] = $export = false;
      $default_values['filter'] = $filter = 0;

      foreach ($default_values as $key => $val) {
         if (isset($params[$key])) {
            $$key = $params[$key];
         }
      }

      if ($this->getFromDB($id)) {
         $addressingFilter = new PluginAddressingFilter();
         if ($filter > 0) {
            if ($addressingFilter->getFromDB($filter)) {
               $ipdeb = sprintf("%u", ip2long($addressingFilter->fields['begin_ip']));
               $ipfin = sprintf("%u", ip2long($addressingFilter->fields['end_ip']));

               $result = $this->compute($start, ['ipdeb'       => $ipdeb,
                                                 'ipfin'       => $ipfin,
                                                 'entities'    => $addressingFilter->fields['entities_id'],
                                                 'type_filter' => $addressingFilter->fields['type']]);
            }
         } else {
            $ipdeb  = sprintf("%u", ip2long($this->fields["begin_ip"]));
            $ipfin  = sprintf("%u", ip2long($this->fields["end_ip"]));
            $result = $this->compute($start, ['ipdeb' => $ipdeb,
                                              'ipfin' => $ipfin]);
         }

         $nbipf = 0; // ip libres
         $nbipr = 0; // ip reservees
         $nbipt = 0; // ip trouvees
         $nbipd = 0; // doublons

         foreach ($result as $ip => $lines) {
            if (count($lines)) {
               if (count($lines) > 1) {
                  $nbipd++;
                  if (!$this->fields['double_ip'] || (isset($params['seedoubleip']) && $params['seedoubleip'] == 0)) {
                     unset($result[$ip]);
                  }
               }
               if ((isset($lines[0]['pname']) && strstr($lines[0]['pname'], "reserv"))) {
                  $nbipr++;
                  if (!$this->fields['alloted_ip'] || (isset($params['seereservedip']) && $params['seereservedip'] == 0)) {
                     unset($result[$ip]);
                  }
               }
               $nbipt++;
               if (!$this->fields['alloted_ip'] || (isset($params['seeallotedip']) && $params['seeallotedip'] == 0)) {
                  unset($result[$ip]);
               }
            } else {
               $nbipf++;
               if (!$this->fields['free_ip'] || (isset($params['seefreeip']) && $params['seefreeip'] == 0)) {
                  unset($result[$ip]);
               }
            }
         }

         ////title
         echo "<div class='spaced'>";
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2 left'>";
         echo "<td class='alert alert-info'>";
         $free = isset($params['seefreeip']) ? $params['seefreeip'] : $this->fields['free_ip'];
         if ($free == 1) {
            echo __('Number of free IP', 'addressing') . " " . $nbipf . "<br>";
         }
         $reserved = isset($params['seereservedip']) ? $params['seereservedip'] : $this->fields['reserved_ip'];
         if ($reserved == 1) {
            echo __('Number of reserved IP', 'addressing') . " " . $nbipr . "<br>";
         }
         $alloted = isset($params['seeallotedip']) ? $params['seeallotedip'] : $this->fields['alloted_ip'];
         if ($alloted == 1) {
            echo __('Number of assigned IP (no doubles)', 'addressing') . " " . $nbipt . "<br>";
         }
         $doubles = isset($params['seedoubleip']) ? $params['seedoubleip'] : $this->fields['double_ip'];
         if ($doubles == 1) {
            echo __('Number of doubles IP', 'addressing') . " " . $nbipd . "<br>";
         }
         echo "</td>";
         echo "<td style='padding: 10px;margin: 10px;'>";

         echo "<table class='netport-legend'>";
         echo "<tr><th colspan='4'>" . __('Caption') . "</th></tr>";

         echo "<tr>";
         if ($doubles == 1) {
            echo "<td class='legend_addressing plugin_addressing_ip_double'>" . __('Same IP', 'addressing') . "</td>&nbsp;";
         }
         $ping_off = 1;
         $ping_on  = 1;
         if (isset($this->fields['use_ping']) && $this->fields['use_ping']) {
            $ping_off = isset($params['ping_off']) ? $params['ping_off'] : $ping_off;
            if ($ping_off == 1) {
               echo "<td class='legend_addressing plugin_addressing_ping_off'>" .
                    __('Ping: got a response - used IP', 'addressing') .
                    "</td>&nbsp;";
            }
            $ping_on = isset($params['ping_on']) ? $params['ping_on'] : $ping_on;
            if ($ping_on == 1) {
               echo "<td class='legend_addressing plugin_addressing_ping_on'>" .
                    __('Ping: no response - free IP', 'addressing') .
                    "</td>&nbsp;";
            }
         } else {
            echo "<td class='legend_addressing plugin_addressing_ip_free'>" . __('Free IP', 'addressing') . "</td>&nbsp;";
         }
         if ($reserved == 1) {
            echo "<td class='legend_addressing plugin_addressing_ip_reserved'>" . __('Reserved IP', 'addressing') . "</td>&nbsp;";
         }
         echo "</td></tr>";
         echo "</table>";

         echo "</td></tr>";
         echo "</table>";
         echo "</div>";

         ////////////////////////// research ////////////////////////////////////////////////////////////
         echo "<form method='post' name='filtering_form' id='filtering_form' action='" . Toolbox::getItemTypeFormURL("PluginAddressingAddressing") . "?id=$id'>";
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2 center'>";

         echo Html::hidden('id', ['value' => $id]);

         echo "<tr class='tab_bg_1 center'>";
         echo "<td>" . __('Assigned IP', 'addressing') . "</td><td>";
         self::showSwitchField('seeallotedip', $alloted);
         echo "</td>";

         echo "<td>" . __('Same IP', 'addressing') . "</td><td>";
         self::showSwitchField('seedoubleip', $doubles);
         echo "</td>";

         echo "<td>" . __('Reserved IP', 'addressing') . "</td><td>";
         self::showSwitchField('seereservedip', $reserved);
         echo "</td>";

         echo "<td>" . __('Free IP', 'addressing') . "</td><td>";
         self::showSwitchField('seefreeip', $free);
         echo "</td>";

         echo "</tr>";

         if (isset($this->fields['use_ping']) && $this->fields['use_ping']) {
            echo "<tr class='tab_bg_1 center'>";
            echo "<td>" . __('Ping: no response - free IP', 'addressing') . "</td><td>";
            self::showSwitchField('ping_on', $ping_on);
            echo "</td>";

            echo "<td>" . __('Ping: got a response - used IP', 'addressing') . "</td><td>";
            self::showSwitchField('ping_off', $ping_off);
            echo "</td>";

            echo "<td class='center' colspan='4'>";
            echo "<button form='' type='submit' id='updatePingInfo' class='submit btn btn-primary me-2 center' name='updatePingInfo' title='" . _sx('button', 'Manual launch of ping', 'addressing') . "'>";
            echo "<i class='fas fa-spinner' data-hasqtip='0' aria-hidden='true'></i>&nbsp;";
            echo _sx('button', 'Manual launch of ping', 'addressing');
            echo "</button>";
            echo "</td>";

            echo "</tr>";
         }
         $filter_list = new PluginAddressingFilter();
         $datas       = $filter_list->find(['plugin_addressing_addressings_id' => $id]);
         if (count($datas) > 0) {
            echo "<tr class='tab_bg_1 center'>";
            echo "<td colspan='4'>";
            echo _n('Filter', 'Filters', 2, 'addressing');
            echo "</td>";
            echo "<td colspan='4'>";
            PluginAddressingFilter::dropdownFilters($params['id'], $filter);
            echo "</td>";
         }
         echo "<tr class='tab_bg_1 center'>";
         echo "<td colspan='8'>";
         echo Html::submit(_sx('button', 'Search'), ['name' => 'search', 'class' => 'btn btn-primary me-2']);
         echo "</td>";
         echo "</td></tr>";
         echo "</table>";

         Html::closeForm();


         echo "<script>
                          $('#updatePingInfo').click(function() {
                             var addressing_id = {$this->getID()};
                             
                            
                          
                             $('#ajax_loader').show();
                             $.ajax({
                                url: '" . PLUGIN_ADDRESSING_WEBDIR . "/ajax/updatepinginfo.php',
                                   type: 'POST',
                                   data: {'addressing_id' : addressing_id},
                                   success: function(response){
                                       $('#ajax_loader').hide();
                                       if (response == 1) {
                                          document.location.reload();
                                       }
                                    },
                                   error: function(xhr, status, error) {
                                      console.log(xhr);
                                      console.log(status);
                                      console.log(error);
                                    } 
                                });
                          });
                        </script>";

         echo "<div id='ajax_loader' class=\"ajax_loader hidden\">";
         echo "</div>";

         $numrows = count($result);
         //         $numrows = 1 + ip2long($this->fields['end_ip']) - ip2long($this->fields['begin_ip']);
         $result = array_slice($result, $start, $_SESSION["glpilist_limit"]);
         $parameters = "id=$id&amp;ping_on=$ping_on&amp;ping_off=$ping_off&amp;filter=$filter&amp;seeallotedip=$alloted&amp;seedoubleip=$doubles&amp;seereservedip=$reserved&amp;seefreeip=$free";
         Html::printPager($start, $numrows, self::getFormURL(), $parameters,
                          'PluginAddressingReport');


         //////////////////////////liste ips////////////////////////////////////////////////////////////
         $ping_status   = [$ping_off, $ping_on];
         $ping_response = $PluginAddressingReport->displayReport($result, $this, $ping_status);

         if ($this->fields['use_ping']) {
            $total_realfreeip = $nbipf - $ping_response;
            echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2 center'>";
            echo "<td>";
            echo __('Real free IP (Ping=KO)', 'addressing') . " " . $total_realfreeip;
            echo "</td></tr>";
            echo "</table>";
         }
         echo "</div>";

      } else {
         echo "<div class='alert alert-important alert-warning d-flex'>";
         echo " <b>" .
              __('Problem detected with the IP Range', 'addressing') . "</b></div>";
      }
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == __CLASS__) {
         if ($tabnum == 0) {
            $item->showReport($_GET);
         }
      }
      return true;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      return [Report::getTypeName(1)];
   }

   //Massive Action
   function getSpecificMassiveActions($checkitem = null) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if (Session::haveRight('transfer', READ)
          && Session::isMultiEntitiesMode()
          && $isadmin) {
         $actions['PluginAddressingAddressing' . MassiveAction::CLASS_ACTION_SEPARATOR . 'transfer'] = __('Transfer');
      }
      return $actions;
   }


   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "transfer" :
            Dropdown::show('Entity');
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction', 'class' => 'btn btn-primary']);
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
                                                       array         $ids) {

      switch ($ma->getAction()) {
         case "transfer" :
            $input = $ma->getInput();

            if ($item->getType() == 'PluginAddressingAddressing') {
               foreach ($ids as $key) {
                  $values["id"]          = $key;
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

   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    **/
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   /**
    * Display a dropdown which contains all the available itemtypes
    *
    * @param $typocrit_id the field widget item id
    * @param value the selected value
    *
    * @return nothing
    **/
   static function dropdownItemtype() {

      //Add definition : display dropdown
      $types = self::getTypes();

      //      $options[0] = Dropdown::EMPTY_VALUE;

      foreach ($types as $itemtype) {
         $item               = new $itemtype();
         $options[$itemtype] = $item->getTypeName($itemtype);
      }

//      asort($options);
      return $options;
   }

   /**
    * @param $name
    * @param $value
    */
   static function showSwitchField($name, $value) {

      echo Html::hidden($name, ['id'    => $name,
                                'value' => $value]);
      echo Html::scriptBlock("(function(){
                             var toggleButton = $('.$name');
                             toggleButton.click(function() {
                             if ($(this).hasClass('fa-toggle-on')) {
                                   toggleButton.removeClass('fa-toggle-on');
                                   toggleButton.addClass('fa-toggle-off');
                                   toggleButton.removeClass('enabled');
                                   toggleButton.addClass('disabled');
                                   document.getElementById('$name').value = '0';
                                 } else {
                                   toggleButton.removeClass('fa-toggle-off');
                                   toggleButton.addClass('fa-toggle-on');
                                   toggleButton.removeClass('disabled');
                                   toggleButton.addClass('enabled');
                                   document.getElementById('$name').value = '1';
                                 }
                             });
                           })();");
      if ($value == 1) {
         echo "<a class=\"button\"><i class=\"$name fa-fw fas fa-2x fa-toggle-on enabled\"></i></a>";
      } else {
         echo "<a class=\"button\"><i class=\"$name fa-fw fas fa-2x fa-toggle-off disabled\"></i></a>";
      }
   }

   static function getMenuContent() {

      $menu                    = [];
      $menu['title']           = self::getMenuName();
      $menu['title']           = self::getTypeName(2);
      $menu['page']            = self::getSearchURL(false);
      $menu['links']['search'] = self::getSearchURL(false);
      $menu['links']['lists']  = "";
      if (Session::haveRight('plugin_addressing', UPDATE)) {
         $menu['links']['add'] = self::getFormURL(false);
      }

      if (Session::haveRight(static::$rightname, UPDATE)
          || Session::haveRight("config", UPDATE)) {
         //Entry icon in breadcrumb
         $menu['links']['config'] = PluginAddressingConfig::getSearchURL(false);
         //Link to config page in admin plugins list
         $menu['config_page'] = PluginAddressingConfig::getSearchURL(false);

         //Add a fourth level in breadcrumb for configuration page
         $menu['options']['config']['title']           = __('Setup');
         $menu['options']['config']['page']            = PluginAddressingConfig::getSearchURL(false);
         $menu['options']['config']['links']['search'] = self::getSearchURL(false);
         $menu['options']['config']['links']['add']    = self::getFormURL(false);
      }

      $menu['icon'] = self::getIcon();

      return $menu;
   }

   static function removeRightsFromSession() {
      if (isset($_SESSION['glpimenu']['tools']['types']['PluginAddressingAddressing'])) {
         unset($_SESSION['glpimenu']['tools']['types']['PluginAddressingAddressing']);
      }
      if (isset($_SESSION['glpimenu']['tools']['content']['pluginaddressingaddressing'])) {
         unset($_SESSION['glpimenu']['tools']['content']['pluginaddressingaddressing']);
      }
   }
}

