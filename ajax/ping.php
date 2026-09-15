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

// ----------------------------------------------------------------------
// Original Author of file: Alexandre DELAUNAY
// Purpose of file:
// ----------------------------------------------------------------------

use Glpi\Exception\Http\NotFoundHttpException;
use GlpiPlugin\Addressing\Addressing;
use GlpiPlugin\Addressing\Config;
use GlpiPlugin\Addressing\Ping_Equipment;
use GlpiPlugin\Addressing\PingInfo;
use GlpiPlugin\Addressing\Report;

// This endpoint pings a single equipment; the tab offering it is gated by seePingTab.php and
// by PingInfo::showPingButton() on the dedicated right. Requiring the plugin UPDATE right here
// instead both denied the feature to the profiles it was granted to and opened it to profiles
// that were never granted it: use the same right as the two other entry points.
Session::checkRight('plugin_addressing_use_ping_in_equipment', READ);

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST['ip']) || !filter_var($_POST["ip"], FILTER_VALIDATE_IP)) {
    throw new NotFoundHttpException();
}

$ip = $_POST['ip'];
$itemtype = $_POST['itemtype'] ?? '';
$items_id = (int) ($_POST['items_id'] ?? 0);

// itemtype/items_id are caller-supplied and drive the PingInfo row written below.
// Reject unknown classes and any item outside the caller's entity perimeter so the
// endpoint cannot be used to enumerate or pollute ping data across the entity boundary.
// getItemForItemtype() resolves any CommonDBTM of the instance, well beyond what this plugin
// deals with. Confront the posted value with the itemtypes the plugin actually supports before
// instantiating it, so the endpoint cannot be pointed at an unrelated table.
if (!is_string($itemtype) || !in_array($itemtype, Addressing::getTypes(true), true)) {
    throw new NotFoundHttpException();
}

$item = getItemForItemtype($itemtype);
if (!($item instanceof CommonDBTM) || !$item->can($items_id, READ)) {
    throw new NotFoundHttpException();
}

// The ping target is caller-supplied and only format-validated above. Bind it to
// the READ-authorized item by requiring it to be one of that item's own network
// port IPs, otherwise this endpoint would let an authenticated user run arbitrary
// server-side ICMP probes against internal hosts (network scan oracle).
$item_ips = Ping_Equipment::getItemIpList($item);
if (!isset($item_ips[$ip])) {
    throw new NotFoundHttpException();
}

$config = new Config();
$config->getFromDB('1');
$system = $config->fields["used_system"];

// Each call runs a blocking probe whose timeout is about a second, and nothing but the caller
// decides how often it is replayed: a few dozen concurrent requests hold as many workers, and
// the instance sends a sustained ICMP stream towards a host chosen among the addresses of the
// assets it may read. The range scan is already bounded by a cooldown and an exclusive lock;
// PingInfo::withProbeGuards() applies the same two, sized for a single address. The lock is per
// user and non blocking, so a concurrent call is refused instead of queueing behind the running
// probe. Both calls to ping() run inside it: they are the two halves of one answer, the human
// readable output and the boolean stored in the ping information row, so they share one lock
// and one cooldown stamp rather than counting as two probes.
$refusal = null;
$result  = PingInfo::withProbeGuards($ip, static function () use ($system, $ip) {
    $ping_equip = new Ping_Equipment();

    return [$ping_equip->ping($system, $ip), $ping_equip->ping($system, $ip, "true")];
}, $refusal);

if ($result === null) {
    echo htmlescape(
        $refusal === PingInfo::PROBE_REFUSED_COOLDOWN
            ? __('A ping has just been run for this address, please retry in a few seconds', 'addressing')
            : __('A ping is already running, please retry in a few seconds', 'addressing'),
    );
    return;
}

[[$message, $error], $ping_value] = $result;

$plugin_addressing_pinginfo = new PingInfo();

$id = 0;
$ping_date = 0;
if ($ping_value == false || $ping_value == true) {
    $ping_date = $_SESSION['glpi_currenttime'];
    if ($pings = $plugin_addressing_pinginfo->find(['itemtype' => $itemtype,
        'items_id' => $items_id])) {
        foreach ($pings as $ping) {
            $id = $ping['id'];
            $num = "IP" . Report::ip2string($ip);
            $plugin_addressing_pinginfo->update(['id' => $id,
                'ping_response' => $ping_value,
                'ping_date' => $ping_date, 'ipname' => $num]);
        }
    } else {
        $num = "IP" . Report::ip2string($ip);
        $plugin_addressing_pinginfo->add(['ping_response' => $ping_value,
            'ping_date' => $ping_date, 'itemtype' => $itemtype,
            'items_id' => $items_id, 'ipname' => $num]);
    }
}

// The message carries the output of the probe. Escape it: the response is injected in the page
// by the caller, and nothing guarantees the underlying command only ever returns plain text.
echo htmlescape($message);
