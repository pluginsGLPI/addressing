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
 * Class PluginAddressingPinginfo
 */
class PluginAddressingPinginfo extends CommonDBTM {
   static $rightname = "plugin_addressing";

   static function getTypeName($nb = 0) {

      return _n('IP Adressing', 'IP Adressing', $nb, 'addressing');
   }

   /**
    * @param $name
    **/
   static function cronInfo($name) {

      switch ($name) {
         case 'UpdatePing' :
            return [
               'description' => __('Launch ping for each ip report', 'addressing'),
            ];

      }
      return [];
   }

   /**
    * Cron action on addressing : auto ping
    *
    * @param $task for log, if NULL display
    *
    **/
   static function cronUpdatePing($task = null) {

      $cron_status = 1;
      $self        = new self();
      $vol         = $self->updateAllAddressing();
      $task->addVolume($vol);
      //      $task->log(Dropdown::getDropdownName("glpi_entities",
      //                                           $entity) . ":  $message\n");

      return $cron_status;
   }


   public function updateAllAddressing() {
      $old_memory           = ini_set("memory_limit", "-1");
      $old_execution        = ini_set("max_execution_time", "0");
      $addressing           = new PluginAddressingAddressing();
      $addressings          = $addressing->find(['is_deleted' => 0,
                                                 'use_ping'   => 1]);
      $total_ping_responses = 0;
      foreach ($addressings as $addressing_array) {
         $addressing->getFromDB($addressing_array['id']);
         $ping_responses       = $this->updateAnAddressing($addressing);
         $total_ping_responses += $ping_responses;
      }
      ini_set("memory_limit", $old_memory);
      ini_set("max_execution_time", $old_execution);
      return $total_ping_responses;
   }

   public function updateAnAddressing(PluginAddressingAddressing $addressing) {

      $ipdeb = sprintf("%u", ip2long($addressing->fields["begin_ip"]));
      $ipfin = sprintf("%u", ip2long($addressing->fields["end_ip"]));

      $result                     = $addressing->compute(0, ['ipdeb'    => $ipdeb,
                                                             'ipfin'    => $ipfin,
                                                             'entities' => $addressing->fields['entities_id']]);
      $plugin_addressing_pinginfo = new PluginAddressingPinginfo();
       $save = $plugin_addressing_pinginfo->find(['plugin_addressing_addressings_id' => $addressing->getID()]);
      $plugin_addressing_pinginfo->deleteByCriteria(['plugin_addressing_addressings_id' => $addressing->getID()]);

      $ping_responses = $this->updatePingInfos($result, $addressing);

      return $ping_responses;
   }

   private function updatePingInfos($result, PluginAddressingAddressing $PluginAddressingAddressing) {

      // Get config
      $PluginAddressingConfig         = new PluginAddressingConfig();
      $PluginAddressingPing_Equipment = new PluginAddressingPing_Equipment();
      $PluginAddressingConfig->getFromDB('1');
      $system = $PluginAddressingConfig->fields["used_system"];

      $ping_response = 0;

      $plugin_addressing_pinginfo = new PluginAddressingPinginfo();

      foreach ($result as $num => $lines) {
         $ip = PluginAddressingReport::string2ip(substr($num, 2));

         $ping_value                               = $PluginAddressingPing_Equipment->ping($system, $ip, "true");
         $data                                     = [];
         $data['plugin_addressing_addressings_id'] = $PluginAddressingAddressing->getID();
         $data['ipname']                           = $num;

         $data['itemtype']      = isset($lines['0']['itemtype']) ? $lines['0']['itemtype'] : "";
         $data['items_id']      = isset($lines['0']['on_device']) ? $lines['0']['on_device'] : "0";
         $data['ping_response'] = $ping_value ?? 0;
         $data['ping_date']     = date('Y-m-d H:i:s');

         $plugin_addressing_pinginfo->add($data);

         if (!is_null($ping_value)) {
            $ping_response++;
         }
      }
      return $ping_response;
   }

   static function getPingResponseForItem($params) {
      global $CFG_GLPI;

      $ping_right = Session::haveRight('plugin_addressing_use_ping_in_equipment', '1');
      $item       = $params['item'];

      if ($ping_right
          && in_array($item->getType(), PluginAddressingAddressing::getTypes()) && $item->getID() > 0) {

         $items_id                   = $item->getID();
         $itemtype                   = $item->getType();
         $plugin_addressing_pinginfo = new PluginAddressingPinginfo();

         $ping_action = 0;
         $ping_value  = 0;
         if ($pings = $plugin_addressing_pinginfo->find(['itemtype' => $itemtype,
                                                         'items_id' => $items_id])) {
            foreach ($pings as $ping) {
               $ping_value = $ping['ping_response'];
               $ping_date  = $ping['ping_date'];
               $ipname     = $ping['ipname'];
            }
            $ping_action = 1;
         }

         if ($ping_action == 0) {
            $content = "<i class=\"fas fa-question fa-2x\" style='color: orange' title=\"" . __("Automatic action has not be launched", 'addressing') . "\">
                    </i><br>" . __("Ping informations not available", 'addressing');
         } else {
            if ($ping_value == 1) {
               $content = "<i class=\"fas fa-check-square fa-2x\" style='color: darkgreen' title='" . __("Last ping attempt", 'addressing') . " : "
                          . Html::convDateTime($ping_date) . "'></i><br>" . __("Last ping attempt", 'addressing') . " : "
                          . Html::convDateTime($ping_date);
               $content .= "<br>" . __('IP') . "&nbsp;" . $ip = PluginAddressingReport::string2ip(substr($ipname, 2));
            } else {
               $content = "<i class=\"fas fa-window-close fa-2x\" style='color: darkred' title='" . __("Last ping attempt", 'addressing') . " : "
                          . Html::convDateTime($ping_date) . "'></i><br>" . __("Last ping attempt", 'addressing') . " : "
                          . Html::convDateTime($ping_date);
               $content .= "<br>" . __('IP') . "&nbsp;" . $ip = PluginAddressingReport::string2ip(substr($ipname, 2));
            }
         }
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th colspan='4'>";
         echo __('Ping result', 'addressing');
         echo "</th></tr>";

         //$('#ping_item').hide();
         echo "<tr class='tab_bg_1 center'><td colspan='2'>";
         echo $content;
         echo "</td><td colspan='2'>";

         $rand = mt_rand();
         echo "<button form='' class='submit btn btn-warning' onclick='javascript:viewPingform" . $items_id . "$rand();'>";
         echo "<i class='fas fa-terminal fa-2x' style='color: orange' title='" . _sx('button', 'Manual launch of ping', 'addressing') . "'></i>";
         echo "</button>";

         echo "<script type='text/javascript' >\n";
         echo "function viewPingform" . $items_id . "$rand() {\n";
         $params = ['action'   => 'viewPingform',
                    'items_id' => $items_id,
                    'itemtype' => $itemtype];
         Ajax::updateItemJsCode("ping_item",
                                PLUGIN_ADDRESSING_WEBDIR . "/ajax/seePingTab.php",
                                $params);
         echo "};";
         echo "</script>\n";


         echo "</td></tr>";
         echo "<tr class='tab_bg_1 center'><td colspan='4'>";
         echo "<div id='ping_item'>";
         include(PLUGIN_ADDRESSING_DIR . "/ajax/seePingTab.php");
         echo "</div>";
         echo "</td></tr>";
         echo "</table>";
      }
   }


   /**
    * @param \CommonDBTM $item
    */
   public static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         ['itemtype' => $item->getType(),
          'items_id' => $item->getField('id')]
      );
   }
}
