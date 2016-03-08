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

      $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/empty-2.0.0.sql");

   } else {
      
      if (!TableExists("glpi_plugin_addressing_profiles")
          && TableExists("glpi_plugin_addressing_display")
          && !FieldExists("glpi_plugin_addressing_display","ipconf1")) {//1.4
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.4.sql");
      }

      if (!TableExists("glpi_plugin_addressing")
          && TableExists("glpi_plugin_addressing_display")
          && FieldExists("glpi_plugin_addressing_display","ipconf1")) {
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.5.sql");

      }

      if (TableExists("glpi_plugin_addressing_display")
          && !FieldExists("glpi_plugin_addressing","ipdeb")) {
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.6.sql");

      }

      if (TableExists("glpi_plugin_addressing_profiles")
          && FieldExists("glpi_plugin_addressing_profiles","interface")) {
         $update = true;
         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.7.0.sql");

      }

      if (!TableExists("glpi_plugin_addressing_configs")) {
         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.8.0.sql");
         $update = true;
      }

      if (TableExists("glpi_plugin_addressing_profiles")
          && !FieldExists("glpi_plugin_addressing_profiles","use_ping_in_equipment")) {
         $DB->runFile(GLPI_ROOT ."/plugins/addressing/sql/update-1.9.0.sql");
         $update = true;
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

      if (FieldExists("glpi_plugin_addressing_profiles", "name")) {
         $query  = "ALTER TABLE `glpi_plugin_addressing_profiles`
                    DROP `name` ";
         $result = $DB->query($query);
      }

      Plugin::migrateItemType(array(5000 => 'PluginAddressingAddressing',
                                    5001 => 'PluginAddressingReport'),
                              array("glpi_bookmarks", "glpi_bookmarks_users",
                                    "glpi_displaypreferences", "glpi_documents_items",
                                    "glpi_infocoms", "glpi_logs", "glpi_items_tickets"));

   }
   
   //0.85 : new profile system
   PluginAddressingProfile::migrateProfiles();
   //Add all rights for current user profile
   PluginAddressingProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   //Drop old profile table : not used anymore
   $migration = new Migration("2.2.0");
   $migration->dropTable('glpi_plugin_addressing_profiles');

   return true;
}


function plugin_addressing_uninstall() {
   global $DB;
   
   include_once (GLPI_ROOT."/plugins/addressing/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/addressing/inc/menu.class.php");
   
   $migration = new Migration("2.3.0");
   $tables = array("glpi_plugin_addressing_addressings",
                   "glpi_plugin_addressing_configs");

   foreach($tables as $table) {
      $migration->dropTable($table);
   }
   
   $itemtypes = array('DisplayPreference', 'Bookmark');
   foreach ($itemtypes as $itemtype) {
      $item = new $itemtype;
      $item->deleteByCriteria(array('itemtype' => 'PluginAddressingAddressing'));
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();

   foreach (PluginAddressingProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(array('name' => $right['field']));
   }
   
   //Remove rigth from $_SESSION['glpiactiveprofile'] if exists
   PluginAddressingProfile::removeRightsFromSession();
   
   PluginAddressingMenu::removeRightsFromSession();

   return true;
}


// Define database relations
function plugin_addressing_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("addressing")) {
      return array("glpi_networks" => array("glpi_plugin_addressing_addressings" => "networks_id"),
                   "glpi_entities" => array("glpi_plugin_addressing_addressings" => "entities_id"));
   }
   return array();
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

/*
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
*/

// Do special actions for dynamic report
function plugin_addressing_dynamicReport($params) {

   $PluginAddressingAddressing = new PluginAddressingAddressing();

   if ($params["item_type"] == 'PluginAddressingReport'
       && isset($params["id"])
       && isset($params["display_type"])
       && $PluginAddressingAddressing->getFromDB($params["id"])) {
      
      $PluginAddressingReport     = new PluginAddressingReport();
      $result = $PluginAddressingAddressing->compute($params["start"]);
      $PluginAddressingReport->displayReport($result, $PluginAddressingAddressing);

      return true;
   }

   // Return false if no specific display is done, then use standard display
   return false;
}

?>