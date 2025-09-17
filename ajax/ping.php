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

// ----------------------------------------------------------------------
// Original Author of file: Alexandre DELAUNAY
// Purpose of file:
// ----------------------------------------------------------------------

use Glpi\Exception\Http\NotFoundHttpException;
use GlpiPlugin\Addressing\Config;
use GlpiPlugin\Addressing\Ping_Equipment;
use GlpiPlugin\Addressing\Pinginfo;
use GlpiPlugin\Addressing\Report;

Session::checkRight('plugin_addressing', UPDATE);
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!isset($_POST['ip']) || !filter_var($_POST["ip"], FILTER_VALIDATE_IP)) {
        throw new NotFoundHttpException();
}
$ip = $_POST['ip'];
$itemtype = $_POST['itemtype'];
$items_id = $_POST['items_id'];

$config = new Config();
$config->getFromDB('1');
$system = $config->fields["used_system"];

$ping_equip = new Ping_Equipment();
list($message, $error) = $ping_equip->ping($system, $ip);

$plugin_addressing_pinginfo = new Pinginfo();

$ping_value = $ping_equip->ping($system, $ip, "true");

$id = 0;
$ping_date = 0;
if ($ping_value == false || $ping_value == true) {
    $ping_date = $_SESSION['glpi_currenttime'];
    if ($pings = $plugin_addressing_pinginfo->find(['itemtype' => $itemtype,
      'items_id' => $items_id])) {
        foreach ($pings as $ping) {
            $id = $ping['id'];

            $plugin_addressing_pinginfo->update(['id' => $id,
            'ping_response' => $ping_value,
            'ping_date' => $ping_date]);
        }
    } else {
        $num = "IP".Report::ip2string($ip);
        $plugin_addressing_pinginfo->add(['ping_response' => $ping_value,
         'ping_date' => $ping_date, 'itemtype' => $itemtype,
         'items_id' => $items_id, 'ipname' => $num]);
    }
}

echo $ping_response = $message;
