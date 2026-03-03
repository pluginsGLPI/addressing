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

namespace GlpiPlugin\Addressing;

use CommonDBTM;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Event;
use NetworkPort;
use Profile_User;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class ReserveIp
 */
class ReserveIp extends CommonDBTM
{
    public static $rightname = 'plugin_addressing';

    public static function getTypeName($nb = 0)
    {
        return __("IP reservation", "addressing");
    }

    public static function getTable($classname = null)
    {
        return "glpi_plugin_addressing_addressings";
    }

    /**
     * @param $ip
     *
     * @return string
     */
    public function getPortName($ip)
    {
        return "reserv-" . $ip;
    }

    /**
     * Show form
     *
     * @param  $input
     *
     * @return
     */
    public function reserveip($input = [])
    {

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
                case 'NetworkEquipment':
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
                default:
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

            Event::log(
                $newID,
                "networkport",
                5,
                "inventory",
                //TRANS: %s is the user login
                sprintf(__('%s adds an item'), $_SESSION["glpiname"])
            );
        }
    }

    /**
     * Check mandatory fields
     *
     * @param  $input
     *
     * @return bool
     */
    public function checkMandatoryFields($input)
    {
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
            Session::addMessageAfterRedirect(sprintf(
                __("Mandatory fields are not filled. Please correct: %s"),
                implode(', ', $msg)
            ), false, ERROR);
            return false;
        }
        return true;
    }

    /**
     * Show form
     *
     * @param  $ip
     * @param  $id_addressing
     */
    public function showReservationForm($ip, $id_addressing, $rand)
    {

        $addressing = new Addressing();
        $addressing->getFromDB($id_addressing);

        $this->forceTable(Addressing::getTable());
        $this->initForm(-1);
        $options['colspan'] = 2;
        $options['no_header'] = true;
        $options['id_addressing'] = $id_addressing;
        $options['ip'] = $ip;

        $config = new Config();
        $config->getFromDB('1');
        $system = $config->fields["used_system"];
        $ping_equip = new Ping_Equipment();

        $msg = "";
        [$message, $error] = $ping_equip->ping($system, $ip);
        if ($error) {
            $msg = "<div class='alert alert-success'>";
            $msg .= "<i class='ti ti-circle-check' style='color:forestgreen'></i>";
            $msg .= "<span style='color:forestgreen'>&nbsp;";
            $msg .=  __('Ping: no response - free IP', 'addressing');
            $msg .= "</span>";
            $msg .= "</div>";
        } else {
            $msg = "<div class='alert alert-warning'>";
            $msg .=  "<i class='ti ti-alert-triangle' style='color:orange'></i>";
            $msg .= "<span style='color:orange'>&nbsp;";
            $msg .=  __('Ping: got a response - used IP', 'addressing');
            $msg .= "</span>";
            $msg .= "</div>";
        }

        $options['types'] = Addressing::dropdownItemtype();
        $strict_entities = Profile_User::getUserEntities($_SESSION['glpiID'], false);
        $entities_rights = Session::haveAccessToOneOfEntities($strict_entities)
            && Session::canViewAllEntities();

        $entities_rights = true;
        TemplateRenderer::getInstance()->display('@addressing/reserveip.html.twig', [
            'item' => $this,
            'rand' =>  $rand,
            'msg' => $msg,
            'params' => $options,
            'entities_rights' => $entities_rights,
            'root_addressing' => PLUGIN_ADDRESSING_WEBDIR
        ]);
    }
}
