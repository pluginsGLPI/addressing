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

function plugin_addressing_install()
{
    global $DB;

    include_once(PLUGIN_ADDRESSING_DIR . "/inc/profile.class.php");

    $update = false;
    if (!$DB->tableExists("glpi_plugin_addressing_display")
        && !$DB->tableExists("glpi_plugin_addressing")
        && !$DB->tableExists("glpi_plugin_addressing_configs")) {
        $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/empty-3.0.1.sql");
    } else {
        if (!$DB->tableExists("glpi_plugin_addressing_profiles")
            && $DB->tableExists("glpi_plugin_addressing_display")
            && !$DB->fieldExists("glpi_plugin_addressing_display", "ipconf1")) {//1.4
            $update = true;
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-1.4.sql");
        }

        if (!$DB->tableExists("glpi_plugin_addressing")
            && $DB->tableExists("glpi_plugin_addressing_display")
            && $DB->fieldExists("glpi_plugin_addressing_display", "ipconf1")) {
            $update = true;
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-1.5.sql");
        }

        if ($DB->tableExists("glpi_plugin_addressing_display")
            && !$DB->fieldExists("glpi_plugin_addressing", "ipdeb")) {
            $update = true;
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-1.6.sql");
        }

        if ($DB->tableExists("glpi_plugin_addressing_profiles")
            && $DB->fieldExists("glpi_plugin_addressing_profiles", "interface")) {
            $update = true;
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-1.7.0.sql");
        }

        if (!$DB->tableExists("glpi_plugin_addressing_configs")) {
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-1.8.0.sql");
            $update = true;
        }

        if ($DB->tableExists("glpi_plugin_addressing_profiles")
            && !$DB->fieldExists("glpi_plugin_addressing_profiles", "use_ping_in_equipment")) {
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-1.9.0.sql");
            $update = true;
        }
        //Version 2.4.0
        if (!$DB->tableExists("glpi_plugin_addressing_filters")) {
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-2.4.0.sql");
        }

        //Version 2.5.0
        if (!$DB->fieldExists("glpi_plugin_addressing_addressings", "locations_id")
            && !$DB->fieldExists("glpi_plugin_addressing_addressings", "fqdns_id")) {
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-2.5.0.sql");
        }
        //Version 2.9.1
        if (!$DB->tableExists("glpi_plugin_addressing_pinginfos")) {
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-2.9.1.sql");
        }
        //Version 3.0.1
        if (!$DB->fieldExists("glpi_plugin_addressing_addressings", "vlans_id")) {
            $DB->runFile(PLUGIN_ADDRESSING_DIR . "/sql/update-3.0.1.sql");
        }
    }

    if ($update) {
        $query_  = "SELECT *
                  FROM `glpi_plugin_addressing_profiles` ";
        $result_ = $DB->query($query_);

        if ($DB->numrows($result_) > 0) {
            while ($data = $DB->fetchArray($result_)) {
                $query  = "UPDATE `glpi_plugin_addressing_profiles`
                      SET `profiles_id` = '" . $data["id"] . "'
                      WHERE `id` = '" . $data["id"] . "'";
                $result = $DB->query($query);
            }
        }

        if ($DB->fieldExists("glpi_plugin_addressing_profiles", "name")) {
            $query  = "ALTER TABLE `glpi_plugin_addressing_profiles`
                    DROP `name` ";
            $result = $DB->query($query);
        }

        Plugin::migrateItemType(
            [5000 => 'PluginAddressingAddressing',
                                 5001 => 'PluginAddressingReport'],
            ["glpi_savedsearches", "glpi_savedsearches_users",
             "glpi_displaypreferences", "glpi_documents_items",
             "glpi_infocoms", "glpi_logs", "glpi_items_tickets"]
        );
    }

    //0.85 : new profile system
    PluginAddressingProfile::migrateProfiles();
    //Add all rights for current user profile
    PluginAddressingProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
    //Drop old profile table : not used anymore
    $migration = new Migration("2.5.0");
    $migration->dropTable('glpi_plugin_addressing_profiles');
    CronTask::Register(PluginAddressingPinginfo::class, 'UpdatePing', DAY_TIMESTAMP);

    return true;
}


/**
 * @return bool
 */
function plugin_addressing_uninstall()
{
    global $DB;

    include_once(PLUGIN_ADDRESSING_DIR . "/inc/profile.class.php");

    $migration = new Migration("2.5.0");
    $tables    = ["glpi_plugin_addressing_addressings",
                  "glpi_plugin_addressing_configs",
                  "glpi_plugin_addressing_filters",
                  "glpi_plugin_addressing_pinginfos",
                  "glpi_plugin_addressing_ipcomments"];

    foreach ($tables as $table) {
        $migration->dropTable($table);
    }

    $itemtypes = ['DisplayPreference', 'SavedSearch'];
    foreach ($itemtypes as $itemtype) {
        $item = new $itemtype;
        $item->deleteByCriteria(['itemtype' => 'PluginAddressingAddressing']);
    }

    //Delete rights associated with the plugin
    $profileRight = new ProfileRight();

    foreach (PluginAddressingProfile::getAllRights() as $right) {
        $profileRight->deleteByCriteria(['name' => $right['field']]);
    }

    //Remove rigth from $_SESSION['glpiactiveprofile'] if exists
    PluginAddressingProfile::removeRightsFromSession();

    PluginAddressingAddressing::removeRightsFromSession();
    CronTask::unregister("addressing");
    return true;
}


/**
 * Define database relations
 *
 * @return array
 */
function plugin_addressing_getDatabaseRelations()
{
    if (Plugin::isPluginActive("addressing")) {
        return ["glpi_networks"  => ["glpi_plugin_addressing_addressings" => "networks_id"],
                "glpi_vlans"     => ["glpi_plugin_addressing_addressings" => "vlans_id"],
                "glpi_fqdns"     => ["glpi_plugin_addressing_addressings" => "fqdns_id"],
                "glpi_locations" => ["glpi_plugin_addressing_addressings" => "locations_id"],
                "glpi_entities"  => ["glpi_plugin_addressing_addressings" => "entities_id"]];
    }
    return [];
}

/**
 * @param $itemtype
 *
 * @return array
 */
function plugin_addressing_getAddSearchOptions($itemtype)
{
    $sopt = [];

    if (in_array($itemtype, PluginAddressingAddressing::getTypes(true))) {
        if (Session::haveRight("plugin_addressing", READ)) {
            $sopt[5000]['table']         = 'glpi_plugin_addressing_pinginfos';
            $sopt[5000]['field']         = 'ping_response';
            $sopt[5000]['name']          = __('Ping result', 'addressing');
            $sopt[5000]['forcegroupby']  = true;
            $sopt[5000]['linkfield']     = 'id';
            $sopt[5000]['massiveaction'] = false;
            $sopt[5000]['joinparams']    = ['beforejoin' => ['table'      => 'glpi_plugin_addressing_pinginfos',
                                                             'joinparams' => ['jointype' => 'itemtype_item']]];
        }
    }
    return $sopt;
}

/**
 * @param $type
 * @param $ID
 * @param $data
 * @param $num
 *
 * @return string
 */
function plugin_addressing_giveItem($type, $ID, $data, $num)
{
    global $DB;

    $dbu = new DbUtils();

    $searchopt =& Search::getOptions($type);
    $table     = $searchopt[$ID]["table"];
    $field     = $searchopt[$ID]["field"];
    $out       = "";
    if (in_array($type, PluginAddressingAddressing::getTypes(true))) {
        switch ($table . '.' . $field) {
            case "glpi_plugin_addressing_pinginfos.ping_response":
                if ($data[$num][0]['name'] == "1") {
                    $out .= "<i class=\"fas fa-check-square fa-2x\" style='color: darkgreen'></i><br>" . __('Last ping OK', 'addressing');
                } elseif ($data[$num][0]['name'] == "0") {
                    $out .= "<i class=\"fas fa-window-close fa-2x\" style='color: darkred'></i><br>" . __('Last ping KO', 'addressing');
                } else {
                    $out .= "<i class=\"fas fa-question fa-2x\" style='color: orange'></i><br>" . __("Ping informations not available", 'addressing');
                }
                return $out;
                break;
        }
    }
    return "";
}

/**
 * Do special actions for dynamic report
 *
 * @param $params
 *
 * @return bool
 */
function plugin_addressing_dynamicReport($params)
{
    $PluginAddressingAddressing = new PluginAddressingAddressing();

    if ($params["item_type"] == 'PluginAddressingReport'
        && isset($params["id"])
        && isset($params["display_type"])
        && $PluginAddressingAddressing->getFromDB($params["id"])) {
        $PluginAddressingReport = new PluginAddressingReport();
        $PluginAddressingAddressing->getFromDB($params['id']);

        $addressingFilter = new PluginAddressingFilter();
        if (isset($params['filter']) && $params['filter'] > 0) {
            if ($addressingFilter->getFromDB($params['filter'])) {
                $ipdeb  = sprintf("%u", ip2long($addressingFilter->fields['begin_ip']));
                $ipfin  = sprintf("%u", ip2long($addressingFilter->fields['end_ip']));
                $result = $PluginAddressingAddressing->compute($params["start"], ['ipdeb'       => $ipdeb,
                                                                                  'ipfin'       => $ipfin,
                                                                                  'entities_id' => $addressingFilter->fields['entities_id'],
                                                                                  'type_filter' => $addressingFilter->fields['type']]);
            }
        } else {
            $ipdeb  = sprintf("%u", ip2long($PluginAddressingAddressing->fields["begin_ip"]));
            $ipfin  = sprintf("%u", ip2long($PluginAddressingAddressing->fields["end_ip"]));
            $result = $PluginAddressingAddressing->compute($params["start"], ['ipdeb' => $ipdeb,
                                                                              'ipfin' => $ipfin]);
        }
        $PluginAddressingReport->displayReport($result, $PluginAddressingAddressing);

        return true;
    }

    // Return false if no specific display is done, then use standard display
    return false;
}

/**
 * @param $itemtype
 * @param $ID
 * @param $order
 * @param $key
 *
 * @return string
 */
function plugin_addressing_addOrderBy($itemtype, $ID, $order, $key)
{
    if ($itemtype == "PluginAddressingAddressing"
        && ($ID == 1000 || $ID == 1001)) {
        return "ORDER BY INET_ATON(ITEM_$key) $order";
    }
}

function plugin_addressing_postinit()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['item_purge']['addressing'] = [];

    foreach (PluginAddressingAddressing::getTypes() as $type) {
        $PLUGIN_HOOKS['item_purge']['addressing'][$type]
           = ['PluginAddressingPinginfo', 'cleanForItem'];
    }
}
