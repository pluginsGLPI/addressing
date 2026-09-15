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
        // exec() is what turns $list into an array, and it only runs when a case below
        // matches. A $system value outside the switch would otherwise leave a string here
        // and make the implode() on $list fatal under PHP 8.
        $list  = [];
        switch ($system) {
            case 0:
                // linux ping
                if ($return == "true") {
                    exec("ping -n -c 1 -w 1 " . escapeshellarg($ip), $list);
                } else {
                    exec("ping -n -c 1 -w 1 " . escapeshellarg($ip), $list, $error);
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
                    exec("ping -n -c 1 -W 1 " . escapeshellarg($ip), $list);
                } else {
                    exec("ping -n -c 1 -W 1 " . escapeshellarg($ip), $list, $error);
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
                    exec("ping -n -c 1 -t 1 " . escapeshellarg($ip), $list);
                } else {
                    exec("ping -n -c 1 -t 1 " . escapeshellarg($ip), $list, $error);
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
            // Ping output is external, attacker-influenced data (notably the reverse-DNS
            // PTR of the target IP on Unix). It is echoed as text/html by ajax/ping.php,
            // so escape every line before joining with the intended <br /> separators to
            // prevent reflected XSS; the <br /> tags stay as the only markup emitted.
            $list_str = implode('<br />', array_map(
                static fn($line): string => htmlspecialchars((string) $line, ENT_QUOTES, 'UTF-8'),
                $list,
            ));

            return [$list_str, $error];
        } else {
            return false;
        }
    }

    /**
     * Show form
     *
     * @param string     $ip
     * @param Addressing $addressing range the address belongs to
     */
    public function showIPForm($ip, Addressing $addressing)
    {

        // PingInfo::isRangeScanEnabled() is the single decision point of the plugin: it reads
        // both the global switch of the configuration, labelled "Use Ping on IP ranges", and the
        // flag of the range. Only the cron task and the manual scan consulted it. This path is a
        // range path too -- the terminal icon of the report -- and it probed whatever the setting
        // said, so disabling the ping did not stop the server from emitting ICMP.
        if (!PingInfo::isRangeScanEnabled($addressing)) {
            TemplateRenderer::getInstance()->display('@addressing/ping_ip_form.html.twig', [
                'ip'    => $ip,
                'error' => 1,
                'state' => 'disabled',
            ]);
            return;
        }

        $config = new Config();
        $config->getFromDB('1');
        $system = $config->fields["used_system"];

        // The probe blocks for the whole timeout of the command and nothing but the caller
        // decides how often it is asked for. Same two guards as the range scan, sized for a
        // single address: a short cooldown per address, and a lock that refuses a concurrent
        // call instead of queueing it behind the running probe. Both are held by PingInfo, so
        // the four paths that probe cannot drift apart again.
        $result = PingInfo::withProbeGuards($ip, static function () use ($system, $ip) {
            return (new Ping_Equipment())->ping($system, $ip);
        });
        if ($result === null) {
            TemplateRenderer::getInstance()->display('@addressing/ping_ip_form.html.twig', [
                'ip'    => $ip,
                'error' => 1,
                'state' => 'unavailable',
            ]);
            return;
        }
        [$message, $error] = $result;

        TemplateRenderer::getInstance()->display('@addressing/ping_ip_form.html.twig', [
            'ip'    => $ip,
            'error' => $error,
            'state' => 'done',
        ]);
    }
}
