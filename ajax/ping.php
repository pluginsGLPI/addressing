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


include('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!isset($_POST['ip']) || !filter_var($_POST["ip"], FILTER_VALIDATE_IP)) {
   exit();
}
$ip = $_POST['ip'];
$itemtype = $_POST['itemtype'];
$items_id = $_POST['items_id'];

$config = new PluginAddressingConfig();
$config->getFromDB('1');
$system = $config->fields["used_system"];

$ping_equip = new PluginAddressingPing_Equipment();
list($message, $error) = $ping_equip->ping($system, $ip);

$plugin_addressing_pinginfo = new PluginAddressingPinginfo();

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

      $num = "IP".PluginAddressingReport::ip2string($ip);
      $plugin_addressing_pinginfo->add(['ping_response' => $ping_value,
         'ping_date' => $ping_date, 'itemtype' => $itemtype,
         'items_id' => $items_id, 'ipname' => $num]);
   }
}

echo $ping_response = $message;

