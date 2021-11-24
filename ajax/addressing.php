<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 addressing plugin for GLPI
 Copyright (C) 2009-2016 by the addressing Development Team.

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

include('../../../inc/includes.php');

Session::checkLoginUser();

Html::header_nocache();
header("Content-Type: text/html; charset=UTF-8");

if (isset($_POST['action']) && $_POST['action'] == 'viewFilter') {
   if (isset($_POST['items_id'])
       && isset($_POST["id"])) {
      $filter = new PluginAddressingFilter();
      $filter->showForm($_POST["id"], ['items_id' => $_POST['items_id']]);
   } else {
      echo __('Access denied');
   }

} else if (isset($_POST['action']) && $_POST['action'] == 'entities_networkip') {
   IPNetwork::showIPNetworkProperties($_POST['entities_id']);

} else if (isset($_POST['action']) && $_POST['action'] == 'entities_location') {
   Dropdown::show('Location', ['name'   => "locations_id",
                               'value'  => $_POST["value"],
                               'entity' => $_POST['entities_id']]);

} else if (isset($_POST['action']) && $_POST['action'] == 'entities_fqdn') {
   Dropdown::show('FQDN', ['name'   => "fqdns_id",
                           'value'  => $_POST["value"],
                           'entity' => $_POST['entities_id']]);

} else {
   Html::popHeader(__s('IP reservation', 'addressing'), $_SERVER['PHP_SELF']);
   $PluginAddressingReserveip = new PluginAddressingReserveip();
   if(filter_var($_GET["ip"], FILTER_VALIDATE_IP)) {
      $PluginAddressingReserveip->showReservationForm($_GET["ip"], $_GET['id_addressing'], $_GET['rand']);
   }

   Html::popFooter();
}
