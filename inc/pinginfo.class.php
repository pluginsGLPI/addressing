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

/**
 * Class PluginAddressingPinginfo
 */
class PluginAddressingPinginfo extends CommonDBTM {
   static $rightname = "plugin_addressing";

   function showForm() {

   }

   /**
    * @param $name
    **/
   static function cronInfo($name) {

      switch ($name) {
         case 'UpdatePing' :
            return [
               'description' => __('Update last ping on free ip'),
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
      $addressings          = $addressing->find(['is_deleted' => 0]);
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

      $ipdeb  = sprintf("%u", ip2long($addressing->fields["begin_ip"]));
      $ipfin  = sprintf("%u", ip2long($addressing->fields["end_ip"]));
      $result = $addressing->compute(0, ['ipdeb' => $ipdeb,
                                         'ipfin' => $ipfin]);


      $ping_responses = $this->updatePingInfos($result, $addressing);


      return $ping_responses;
   }

   private function updatePingInfos($result, PluginAddressingAddressing $PluginAddressingAddressing) {
      global $CFG_GLPI;

      $ping = $PluginAddressingAddressing->fields["use_ping"];

      // Get config
      $PluginAddressingConfig = new PluginAddressingConfig();
      $PluginAddressingReport = new PluginAddressingReport();
      $PluginAddressingConfig->getFromDB('1');
      $system = $PluginAddressingConfig->fields["used_system"];


      $ping_response = 0;


      $plugin_addressing_pinginfo = new PluginAddressingPinginfo();
      $plugin_addressing_pinginfo->deleteByCriteria(['plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID()]);
      foreach ($result as $num => $lines) {
         $ip = PluginAddressingReport::string2ip(substr($num, 2));

         if (!count($lines) && $PluginAddressingAddressing->fields["free_ip"]) {


            if ($ping) {

               $ping_value                               = $PluginAddressingReport->ping($system, $ip);
               $data                                     = [];
               $data['plugin_addressing_addressings_id'] = $PluginAddressingAddressing->getID();
               $data['ipname']                           = $num;
               $data['ping_response']                    = $ping_value ?? 0;
               $data['ping_date']                        = date('Y-m-d H:i:s');;
               $plugin_addressing_pinginfo->add($data);
               if (!is_null($ping_value)) {
                  $ping_response++;
               }

            }

         }
      }


      return $ping_response;
   }


}
