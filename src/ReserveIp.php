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
use Glpi\Application\View\TemplateRenderer;
use Glpi\Event;
use NetworkPort;
use Profile_User;
use Session;

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

        // Normalise the asset name once it is known to be present, like entities_id below:
        // it is used as a lookup criterion and then written onto the asset itself.
        $input['name_reserveip'] = trim((string) $input['name_reserveip']);

        // $input['ip'] is posted straight to this endpoint and, unlike the AJAX form
        // that produced it (ajax/addressing.php), nothing here re-validates it: it ends
        // up in the port name ("reserv-<value>") and in NetworkName__ipaddresses.
        // Replay the two checks the form does: a syntactically valid IPv4, contained in
        // a range the caller may READ, so a reservation cannot be tied to an address
        // outside the caller's addressing plan.
        $ip = filter_var($input['ip'] ?? '', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        if ($ip === false || !Addressing::isIpInReadableRange($ip)) {
            Session::addMessageAfterRedirect(__('Invalid data !!', 'addressing'), false, ERROR);
            return false;
        }
        $input['ip'] = $ip;

        // Dropdown ids are posted values: the UI restricts the choice to the target
        // entity but a crafted request can carry the id of a value owned by another
        // entity, which would then be written on the asset and its label read back.
        foreach (['locations_id' => 'Location', 'states_id' => 'State', 'fqdns_id' => 'FQDN'] as $field => $itemtype) {
            // Normalise while validating: checkMandatoryFields() above only covers the
            // required fields, so a minimal POST omitting these three reached the
            // add()/update() arrays and the NetworkPort payloads below as undefined keys --
            // one PHP 8 warning per read, then an uncontrolled value written on the asset.
            $input[$field] = (int) ($input[$field] ?? 0);
            if (!$this->isUsableDropdownValue($itemtype, $input[$field])) {
                Session::addMessageAfterRedirect(__('Invalid data !!', 'addressing'), false, ERROR);
                return false;
            }
        }

        // Same reasoning for the free text comment, which has no dropdown to validate it.
        $input['comment'] = (string) ($input['comment'] ?? '');

        // Every other posted field is validated above; mac was the exception and went straight
        // into glpi_networkports.mac. This is not an injection -- the report escapes it and the
        // exports go through PhpSpreadsheet -- but arbitrary text in a normalised inventory
        // field silently defeats every later correlation (automatic inventory, DHCP, rules).
        $mac = strtolower(trim((string) ($input['mac'] ?? '')));
        if ($mac !== '' && !preg_match('/^([0-9a-f]{2}:){5}[0-9a-f]{2}$/', $mac)) {
            Session::addMessageAfterRedirect(__('Invalid data !!', 'addressing'), false, ERROR);
            return false;
        }
        $input['mac'] = $mac;

        // $input['type'] is fully attacker-controlled ($_POST['type']). Restrict it to the
        // plugin's supported network port types and enforce the target itemtype's own
        // CREATE/UPDATE right (and entity access) before writing an asset/NetworkPort on
        // the caller's behalf. The plugin right checked in front/reserveip.form.php only
        // gates access to the reservation feature itself, not to the underlying itemtype.
        // Each refusal below used to return silently: no message for the legitimate user and
        // no trace for the operator, while the controller announced a success anyway.
        if (!in_array($input['type'] ?? '', Addressing::getTypes(true), true)) {
            Session::addMessageAfterRedirect(__('Invalid data !!', 'addressing'), false, ERROR);
            return false;
        }

        $item = getItemForItemtype($input['type']);
        if (!($item instanceof CommonDBTM)) {
            Session::addMessageAfterRedirect(__('Invalid data !!', 'addressing'), false, ERROR);
            return false;
        }

        // entities_id drives both the asset lookup below and the entity the asset and its
        // NetworkPort are written into. Normalise it once, like the other posted fields, so
        // a request that omits it falls back to the root entity -- which can(-1, CREATE)
        // then rejects unless the caller may reach it -- instead of raising a warning and
        // reaching the query as null.
        $input['entities_id'] = (int) ($input['entities_id'] ?? 0);

        // Find computer
        if (!$item->getFromDBByCrit(["name"        => $input["name_reserveip"],
            "entities_id" => $input['entities_id']])) {
            // Add computer
            // can() takes its third argument by reference (?array &$input), so it has
            // to be handed a variable: an inline array literal makes PHP raise
            // "Argument #3 ($input) could not be passed by reference" at call time,
            // before any right is even evaluated -- which broke every reservation that
            // had to create the asset, whatever the caller's profile (GitHub issue #113).
            $create_input = [
                "name"        => $input["name_reserveip"],
                "entities_id" => $input['entities_id'],
            ];
            if (!$item->can(-1, CREATE, $create_input)) {
                Session::addMessageAfterRedirect(__('You are not allowed to do this action'), false, ERROR);
                return false;
            }
            $id = $item->add(["name"         => $input["name_reserveip"],
                "entities_id"  => $input['entities_id'],
                "locations_id" => $input["locations_id"],
                "states_id"    => $input["states_id"],
                "comment"      => $input["comment"]]);
        } else {
            $id = $item->getID();
            if (!$item->can($id, UPDATE)) {
                Session::addMessageAfterRedirect(__('You are not allowed to do this action'), false, ERROR);
                return false;
            }
            //update item
            $item->update(["id"           => $id,
                "entities_id"  => $input['entities_id'],
                "states_id"    => $input["states_id"],
                "locations_id" => $input["locations_id"],
                "comment"      => $input["comment"]]);
        }

        // Add a new port
        if ($id) {
            // Bind the port to the entity of the asset carrying it rather than to the
            // active session entity: the two can differ, which would leave the port
            // misaligned with its own asset.
            $port_entities_id = (int) ($item->fields['entities_id'] ?? $input['entities_id']);

            switch ($input['type']) {
                case 'NetworkEquipment':
                    $newinput = [
                        "itemtype"                 => $input['type'],
                        "items_id"                 => $id,
                        "entities_id"              => $port_entities_id,
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
                        "entities_id"              => $port_entities_id,
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
                sprintf(__('%s adds an item'), $_SESSION["glpiname"]),
            );
        }

        return true;
    }

    /**
     * Whether a posted dropdown value lies within the caller's entity perimeter.
     *
     * Only the entity boundary is enforced, not the "dropdown" right: a technician
     * legitimately reserving an IP does not necessarily hold it.
     *
     * @param string $itemtype dropdown class name
     * @param mixed  $items_id posted id (0/empty means "no value", which is fine)
     */
    private function isUsableDropdownValue(string $itemtype, $items_id): bool
    {
        $items_id = (int) $items_id;
        if ($items_id <= 0) {
            return true;
        }

        $item = getItemForItemtype($itemtype);
        if (!($item instanceof CommonDBTM) || !$item->getFromDB($items_id)) {
            return false;
        }

        if (!$item->isEntityAssign()) {
            // Global dropdown: no entity boundary to enforce.
            return true;
        }

        return Session::haveAccessToEntity(
            (int) $item->fields['entities_id'],
            (bool) ($item->fields['is_recursive'] ?? false),
        );
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

        // Walk the reference list, not the submitted data: a field entirely omitted from the
        // request never entered the loop, so the check passed. A missing name_reserveip then
        // reached getFromDBByCrit() and add() as null, which either overwrote an existing
        // nameless asset of the entity or created a new one.
        foreach ($mandatory_fields as $key => $label) {
            if (!isset($input[$key])
                || !is_scalar($input[$key])
                || trim((string) $input[$key]) === '') {
                $msg[$key] = $label;
                $checkKo   = true;
            }
        }

        if ($checkKo) {
            Session::addMessageAfterRedirect(sprintf(
                __("Mandatory fields are not filled. Please correct: %s"),
                implode(', ', $msg),
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
        // The flag of the range is only half of the decision: the configuration of the plugin
        // carries a global switch, labelled "Use Ping on IP ranges", which this form ignored, so
        // opening it made the server probe the address even with the ping turned off. Ask the
        // single decision point, as the cron task and the manual scan already do.
        $ping = PingInfo::isRangeScanEnabled($addressing) ? 1 : 0;
        $msg = "";
        if ($ping == 1) {
            // This form is reached by a plain GET and was the only probe path left with neither
            // cooldown nor lock: it ran the blocking command as often as it was asked to, which
            // made it the one an authenticated user would loop on to hold the whole worker pool
            // and pour ICMP towards an internal host. Go through the same guards as the three
            // other paths, and say the probe is unavailable rather than run it.
            $refusal = null;
            $result  = PingInfo::withProbeGuards($ip, static function () use ($system, $ip) {
                return (new Ping_Equipment())->ping($system, $ip);
            }, $refusal);
            if ($result === null) {
                $msg = "<div class='alert alert-info'>";
                $msg .= "<i class='ti ti-clock'></i>";
                $msg .= "<span>&nbsp;";
                $msg .= htmlescape(
                    $refusal === PingInfo::PROBE_REFUSED_COOLDOWN
                        ? __(
                            'A ping has just been run for this address, please retry in a few seconds',
                            'addressing',
                        )
                        : __('A ping is already running, please retry in a few seconds', 'addressing'),
                );
                $msg .= "</span>";
                $msg .= "</div>";
            } elseif ($result[1]) {
                $msg = "<div class='alert alert-success'>";
                $msg .= "<i class='ti ti-circle-check' style='color:forestgreen'></i>";
                $msg .= "<span style='color:forestgreen'>&nbsp;";
                $msg .= __('Ping: no response - free IP', 'addressing');
                $msg .= "</span>";
                $msg .= "</div>";
            } else {
                $msg = "<div class='alert alert-warning'>";
                $msg .= "<i class='ti ti-alert-triangle' style='color:orange'></i>";
                $msg .= "<span style='color:orange'>&nbsp;";
                $msg .= __('Ping: got a response - used IP', 'addressing');
                $msg .= "</span>";
                $msg .= "</div>";
            }
        }
        $options['types'] = Addressing::dropdownItemtype();
        $strict_entities = Profile_User::getUserEntities($_SESSION['glpiID'], false);
        // Only offer the target-entity selector to users who may act across entities.
        // reserveip() stays authoritative (can(-1, CREATE) / can($id, UPDATE)); this just
        // keeps the UI from exposing an entity choice a single-entity user cannot use.
        $entities_rights = Session::haveAccessToOneOfEntities($strict_entities)
            && Session::canViewAllEntities();

        TemplateRenderer::getInstance()->display('@addressing/reserveip.html.twig', [
            'item' => $this,
            'rand' =>  $rand,
            'msg' => $msg,
            'params' => $options,
            'entities_rights' => $entities_rights,
            'root_addressing' => PLUGIN_ADDRESSING_WEBDIR,
            // The type dropdown used to read a "config" variable this method never passed,
            // which raises a Twig RuntimeError under GLPI_STRICT_ENV and silently drops the
            // preselection everywhere else. The legacy form it was migrated from called
            // Dropdown::showFromArray() without any value, so the first supported itemtype
            // is the intended default: an empty string keeps that behaviour while making
            // the contract of the template explicit.
            'default_type' => '',
        ]);
    }
}
