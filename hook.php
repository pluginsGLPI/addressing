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

function plugin_addressing_install() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/addressing/inc/profile.class.php");

   $update = false;
   if (!TableExists("glpi_plugin_addressing_display")
       &&!TableExists("glpi_plugin_addressing")
       && !TableExists("glpi_plugin_addressing_configs")) {

      $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/empty-1.9.0.sql");

   } else {
      $update = true;
      if (!TableExists("glpi_plugin_addressing_profiles")
          && !FieldExists("glpi_plugin_addressing_display","ipconf1")) {//1.4

         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.4.sql");
      }

      if (!TableExists("glpi_plugin_addressing")
          && TableExists("glpi_plugin_addressing_display")
          && FieldExists("glpi_plugin_addressing_display","ipconf1")) {

         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.5.sql");

      }

      if (TableExists("glpi_plugin_addressing_display")
          && !FieldExists("glpi_plugin_addressing","ipdeb")) {

         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.6.sql");

      }

      if (TableExists("glpi_plugin_addressing_profiles")
          && FieldExists("glpi_plugin_addressing_profiles","interface")) {

         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.7.0.sql");

      }

      if (!TableExists("glpi_plugin_addressing_configs")) {
         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.8.0.sql");

      }

      if (!fieldExists("glpi_plugin_addressing_profiles","use_ping_in_equipment")) {
         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.9.0.sql");
      }
   }

   if ($update) {
      $query_  = "SELECT *
                  FROM `glpi_plugin_addressing_profiles` ";
      $result_ = $DB->query($query_);

      if ($DB->numrows($result_)>0) {
         while ($data=$DB->fetch_array($result_)) {
            $query = "UPDATE `glpi_plugin_addressing_profiles`
                      SET `profiles_id` = '".$data["id"]."'
                      WHERE `id` = '".$data["id"]."'";
            $result=$DB->query($query);
         }
      }

      if (FieldExists("glpi_plugin_addressing_profiles","name")) {
         $query  = "ALTER TABLE `glpi_plugin_addressing_profiles`
                    DROP `name` ";
         $result = $DB->query($query);
      }

      Plugin::migrateItemType(array(5000 => 'PluginAddressingAddressing',
                                    5001 => 'PluginAddressingReport'),
                              array("glpi_bookmarks", "glpi_bookmarks_users",
                                    "glpi_displaypreferences", "glpi_documents_items",
                                    "glpi_infocoms", "glpi_logs", "glpi_tickets"));
   }

   PluginAddressingProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}


function plugin_addressing_uninstall() {
   global $DB;

   $tables = array("glpi_plugin_addressing_addressings",
                   "glpi_plugin_addressing_configs",
                   "glpi_plugin_addressing_profiles");

   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   //old versions
   $tables = array("glpi_plugin_addressing_display",
                   "glpi_plugin_addressing");

   foreach($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   $tables_glpi = array("glpi_displaypreferences",
                        "glpi_bookmarks");

   foreach($tables_glpi as $table_glpi)
      $DB->query("DELETE
                  FROM `$table_glpi`
                  WHERE `itemtype` = '".'PluginAddressingAddressing'."';");

   return true;
}


// Define database relations
function plugin_addressing_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("addressing")) {
      return array("glpi_networks" => array("glpi_plugin_addressing_addressings" => "networks_id"),
                   "glpi_entities" => array("glpi_plugin_addressing_addressings" => "entities_id"),
                   "glpi_profiles" => array("glpi_plugin_addressing_profiles"    => "profiles_id"));
   }
   return array ();
}


function plugin_addressing_getAddSearchOptions($itemtype) {

   $sopt = array();

   if ($itemtype == 'Profile') {
      if (plugin_addressing_haveRight("addressing","r")) {
         // Use a plugin type reservation to avoid conflict
         $sopt[5000]['table'] = 'glpi_plugin_addressing_profiles';
         $sopt[5000]['field'] = 'addressing';
         $sopt[5000]['name']  = PluginAddressingAddressing::getTypeName(2);
         //$sopt[5000]['datatype']='bool';
      }
   }
   return $sopt;
}


function plugin_addressing_giveItem($type,$ID,$data,$num) {

  $searchopt = &Search::getOptions($type);

   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];

   switch ($table.'.'.$field) {
      case "glpi_plugin_addressing_profiles.addressing":
         switch($data["ITEM_$num"]) {
            case 'w':
               return __('Writing ability');

            case 'r':
               return __('Read');

            default:
               return __('No access');
         }
         break;
   }
   return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_addressing_MassiveActions($type) {

   switch ($type) {
      case 'Profile' :
         return array("plugin_addressing_allow" => PluginAddressingAddressing::getTypeName(2) . " - " .
                                                   __('Generate reports', 'addressing'));
   }
   return array();
}


function plugin_addressing_MassiveActionsDisplay($options=array()) {

   switch ($options['itemtype']) {
      case 'Profile' :
         switch ($options['action']) {
            case "plugin_addressing_allow" :
               Profile::dropdownNoneReadWrite('use','');
               echo "&nbsp;<input type='submit' name='massiveaction' class='submit' value=\"".
                            _sx('button','Post')."\" >";
               break;
         }
         break;
   }
   return "";
}


function plugin_addressing_MassiveActionsProcess($data) {

   $res = array('ok' => 0,
            'ko' => 0,
            'noright' => 0);

   switch ($data['action']) {
      case 'plugin_addressing_allow' :
         if ($data['itemtype'] == 'Profile') {
            $profglpi = new Profile();
            $prof     = new PluginAddressingProfile();
            foreach ($data["item"] as $key => $val) {
               if ($profglpi->getFromDB($key)
                     && $profglpi->fields['interface']!='helpdesk') {
                  if ($prof->getFromDBByProfile($key)) {
                     if ($prof->update(array('id'          => $prof->fields['id'],
                                         'profiles_id' => $key,
                                         'addressing'  => $data['use']))) {
                        $res['ok']++;
                     } else {
                        $res['ko']++;
                     }
                  } else {
                     if ($prof->add(array('id'             => $prof->fields['id'],
                                      'profiles_id'    => $key,
                                      'addressing'     => $data['use']))) {
                        $res['ok']++;
                     } else {
                        $res['ko']++;
                     }
                  }
               } else {
                  $res['ko']++;
               }
            }
         }
         break;
   }
   return $res;
}


// Do special actions for dynamic report
function plugin_addressing_dynamicReport($parm) {

   $PluginAddressingAddressing = new PluginAddressingAddressing();
   $PluginAddressingReport     = new PluginAddressingReport();

   if ($parm["item_type"]=='PluginAddressingReport'
       && isset($parm["id"])
       && isset($parm["display_type"])
       && $PluginAddressingAddressing->getFromDB($parm["id"])) {

      $result = $PluginAddressingAddressing->compute($parm["start"]);
      $PluginAddressingReport->display($result, $PluginAddressingAddressing);

      return true;
   }

   // Return false if no specific display is done, then use standard display
   return false;
}

?>