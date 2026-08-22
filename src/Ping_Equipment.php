<?php

/**
 * -------------------------------------------------------------------------
 * addressing plugin for GLPI
 * Copyright (C) 2016-2026 by the addressing Development Team.
 *
 * https://github.com/pluginsGLPI/addressing
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of addressing.
 *
 * addressing is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * addressing is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with addressing. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Addressing;

use CommonDBTM;
use DbUtils;
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Html;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Ping_Equipment
 */
class Ping_Equipment extends CommonDBTM
{
    public static $rightname = "plugin_addressing";

    public function showPingForm($itemtype, $items_id)
    {
        // itemtype/items_id are caller-supplied (ajax/seePingTab.php relays $_POST as-is)
        // and directly drive which asset's network names/IP addresses get disclosed below.
        // Restrict itemtype to the plugin's supported network port types and require READ
        // on the target item before querying/rendering anything about it.
        $items_id = (int) $items_id;
        if (!in_array($itemtype, Addressing::getTypes(true), true)) {
            return;
        }

        $obj = getItemForItemtype($itemtype);
        if (!($obj instanceof CommonDBTM) || !$obj->can($items_id, READ)) {
            return;
        }

        $dbu = new DbUtils();

        TemplateRenderer::getInstance()->display('@addressing/ping_equipment_form.html.twig', [
            'list_ip'     => self::getItemIpList($obj),
            'empty_value' => Dropdown::EMPTY_VALUE,
            'itemtype'    => $dbu->getItemTypeForTable($obj->getTable()),
            'items_id'    => $items_id,
        ]);
    }

    /**
     * Return the IP addresses attached to an asset's network ports.
     *
     * Keyed by IP string (value = display label). Used both to render the ping
     * tab and to bind a caller-supplied ping target to the item whose READ right
     * has been checked: the ping endpoint only accepts an IP present in this list,
     * so it cannot be turned into an arbitrary internal-network scan oracle.
     *
     * @param CommonDBTM $obj An item already loaded and READ-authorized by the caller.
     *
     * @return array<string,string> Map of IP => label.
     */
    public static function getItemIpList(CommonDBTM $obj): array
    {
        global $DB;

        $dbu      = new DbUtils();
        $itemtype = $dbu->getItemTypeForTable($obj->getTable());

        $list_ip = [];

        $request = $DB->request([
            'SELECT' => [
                'glpi_networknames'  => 'name',
                'glpi_ipaddresses'   => 'name AS ip',
                'glpi_networkports'  => 'items_id',
            ],
            'FROM' => 'glpi_networkports',
            'LEFT JOIN' => [
                $obj->getTable() => [
                    'ON' => [
                        'glpi_networkports' => 'items_id',
                        $obj->getTable()    => 'id',
                        ['AND' => [
                            'glpi_networkports.itemtype' => $itemtype,
                        ]],
                    ],
                ],
                'glpi_networknames' => [
                    'ON' => [
                        'glpi_networkports'  => 'id',
                        'glpi_networknames'  => 'items_id',
                    ],
                ],
                'glpi_ipaddresses' => [
                    'ON' => [
                        'glpi_ipaddresses' => 'items_id',
                        'glpi_networknames' => 'id',
                    ],
                ],
            ],
            'WHERE' => [
                $obj->getTable() . '.id' => $obj->fields['id'],
            ],
        ]);

        foreach ($request as $row) {
            if (!empty($row['ip'])) {
                $port = $row['ip'];
                if (!empty($row['name'])) {
                    $port = $row['name'] . " ($port)";
                }
                $list_ip[$row['ip']] = $port;
            }
        }

        return $list_ip;
    }

    /**
    * @param $system
    * @param $ip
    *
    * @return array
    */
    public function ping($system, $ip, $return = "list")
    {
        if (defined('GLPI_INSTALL_MODE') && GLPI_INSTALL_MODE === 'CLOUD') {
            return $return === "true" ? false : [__('Ping unavailable in cloud mode', 'addressing'), 1];
        }

        $error = 1;
        $list  = '';
        switch ($system) {
            case 0:
                // linux ping
                if ($return == "true") {
                    exec("ping -c 1 -w 1 " . escapeshellarg($ip), $list);
                } else {
                    exec("ping -c 1 -w 1 " . escapeshellarg($ip), $list, $error);
                }
                $nb = count($list);
                if (isset($nb) && $return == "true") {
                    for ($i = 0; $i < $nb; $i++) {
                        if (strpos($list[$i], "ttl=") > 0) {
                            return true;
                        }
                    }
                }
                break;

            case 1:
                //windows
                if ($return == "true") {
                    exec("ping.exe -n 1 -w 100 -i 64 " . escapeshellarg($ip), $list);
                } else {
                    exec("ping.exe -n 1 -w 100 -i 64 " . escapeshellarg($ip), $list, $error);
                }
                $nb = count($list);
                if (isset($nb) && $return == "true") {
                    for ($i = 0; $i < $nb; $i++) {
                        if (strpos($list[$i], "TTL") > 0) {
                            return true;
                        }
                    }
                }
                break;

            case 2:
                //linux fping
                if ($return == "true") {
                    exec("fping -r1 -c1 -t100 " . escapeshellarg($ip), $list);
                } else {
                    exec("fping -r1 -c1 -t100 " . escapeshellarg($ip), $list, $error);
                }
                $nb = count($list);
                if (isset($nb) && $return == "true") {
                    for ($i = 0; $i < $nb; $i++) {
                        if (strpos($list[$i], "bytes") > 0) {
                            return true;
                        }
                    }
                }
                break;

            case 3:
                // BSD ping
                if ($return == "true") {
                    exec("ping -c 1 -W 1 " . escapeshellarg($ip), $list);
                } else {
                    exec("ping -c 1 -W 1 " . escapeshellarg($ip), $list, $error);
                }
                $nb = count($list);
                if (isset($nb) && $return == "true") {
                    for ($i = 0; $i < $nb; $i++) {
                        if (strpos($list[$i], "ttl=") > 0) {
                            return true;
                        }
                    }
                }
                break;

            case 4:
                // MacOSX ping
                if ($return == "true") {
                    exec("ping -c 1 -t 1 " . escapeshellarg($ip), $list);
                } else {
                    exec("ping -c 1 -t 1 " . escapeshellarg($ip), $list, $error);
                }
                $nb = count($list);
                if (isset($nb) && $return == "true") {
                    for ($i = 0; $i < $nb; $i++) {
                        if (strpos($list[$i], "ttl=") > 0) {
                            return true;
                        }
                    }
                }
                break;
        }
        if ($return == "list") {
            $list_str = implode('<br />', $list);

            return [$list_str, $error];
        } else {
            return false;
        }
    }

    /**
     * @param $system
     * @param $ip
     *
     * @return array
     */
    public function getHostnameByPing($system, $ip)
    {
        if (defined('GLPI_INSTALL_MODE') && GLPI_INSTALL_MODE === 'CLOUD') {
            return '';
        }

        $error = 1;
        $list  = '';
        switch ($system) {
            case 0:
                // linux host
                exec("ping -c 1 -w 1 -a " . escapeshellarg($ip), $list, $error);
                break;

            case 1:
                //windows
                exec("ping.exe -n 1 -w 100 -i 64 -a " . escapeshellarg($ip), $list, $error);
                break;
        }
        $list_str = implode('<br />', $list);
        //      return [$list_str, $error];
        return $list[1];
    }

    /**
     * Show form
     *
     * @param string $ip
     */
    public function showIPForm($ip)
    {
        echo Html::script(PLUGIN_ADDRESSING_DIR_NOFULL . "/addressing.js");

        $config = new Config();
        $config->getFromDB('1');
        $system = $config->fields["used_system"];

        $ping_equip = new Ping_Equipment();
        [$message, $error] = $ping_equip->ping($system, $ip);

        TemplateRenderer::getInstance()->display('@addressing/ping_ip_form.html.twig', [
            'ip'    => $ip,
            'error' => $error,
        ]);
    }
}
