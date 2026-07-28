<?php

/*
 -------------------------------------------------------------------------
 addressing plugin for GLPI
 Copyright (C) 2016-2026 by the addressing Development Team.

 https://github.com/pluginsGLPI/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with addressing. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Addressing\Addressing;
use GlpiPlugin\Addressing\Filter;
use GlpiPlugin\Addressing\Ping_Equipment;
use GlpiPlugin\Addressing\ReserveIp;
use function Safe\json_encode;

Session::checkRight('plugin_addressing', UPDATE);

Html::header_nocache();
header("Content-Type: text/html; charset=UTF-8");

if (isset($_GET['action']) && $_GET['action'] == 'isName') {
    header("Content-Type: application/json; charset=UTF-8");

    // type/name are attacker-controlled ($_GET). Restrict the itemtype to the plugin's
    // supported network port types, require the caller to hold READ on that itemtype and
    // scope the search to the caller's entities, otherwise this endpoint becomes a
    // cross-itemtype / cross-entity existence oracle for any authenticated plugin user.
    $type = $_GET['type'] ?? '';
    $item = in_array($type, Addressing::getTypes(true), true) ? getItemForItemtype($type) : false;

    if (!($item instanceof CommonDBTM) || !$item->canView()) {
        echo json_encode(false);
    } else {
        $criteria = ['name' => ['LIKE', $_GET['name'] ?? '']];
        if ($item->isEntityAssign()) {
            $criteria = array_merge(
                $criteria,
                Session::getEntitiesRestrictCriteria($item->getTable(), '', '', $item->maybeRecursive())
            );
        }
        $datas = $item->find($criteria);
        echo json_encode(count($datas) > 0);
    }
} else if (isset($_POST['action']) && $_POST['action'] == 'viewFilter') {
    if (isset($_POST['items_id'])
       && isset($_POST["id"])) {
        $filter = new Filter();
        // 'id' is -1 for the "add" form and a real filter id for the "edit" form:
        // require CREATE for a new filter, READ for an existing one, entity-aware.
        $filter_id = (int) $_POST['id'];
        $filter->check($filter_id, $filter_id > 0 ? READ : CREATE, $_POST);
        $filter->showForm($_POST["id"], ['items_id' => $_POST['items_id']]);
    } else {
        throw new AccessDeniedHttpException();
    }
} elseif (isset($_POST['action']) && $_POST['action'] == 'entities_networkip') {
    if (!Session::haveAccessToEntity((int) $_POST['entities_id'])) {
        throw new AccessDeniedHttpException();
    }
    IPNetwork::showIPNetworkProperties($_POST['entities_id']);
} elseif (isset($_POST['action']) && $_POST['action'] == 'entities_location') {
    if (!Session::haveAccessToEntity((int) $_POST['entities_id'])) {
        throw new AccessDeniedHttpException();
    }
    echo __('Location');
    Dropdown::show('Location', ['name'   => "locations_id",
        'value'  => $_POST["value"],
        'entity' => $_POST['entities_id']]);
} elseif (isset($_POST['action']) && $_POST['action'] == 'entities_fqdn') {
    if (!Session::haveAccessToEntity((int) $_POST['entities_id'])) {
        throw new AccessDeniedHttpException();
    }
    echo __('FQDN');
    Dropdown::show('FQDN', ['name'   => "fqdns_id",
        'value'  => $_POST["value"],
        'entity' => $_POST['entities_id']]);
} elseif (isset($_GET['action']) && $_GET['action'] == 'ping') {
    Html::popHeader(__s('IP ping', 'addressing'), $_SERVER['PHP_SELF']);

    $ip = filter_var($_GET['ip'] ?? '', FILTER_VALIDATE_IP);
    if ($ip !== false) {
        $Ping_Equipment = new Ping_Equipment();
        $Ping_Equipment->showIPForm($ip);
    }
    Html::popFooter();
} else {
    $id_addressing = (int) ($_GET['id_addressing'] ?? 0);
    $rand          = (int) ($_GET['rand'] ?? 0);
    $ip            = filter_var($_GET['ip'] ?? '', FILTER_VALIDATE_IP);

    // The global right is not entity-aware: enforce READ on the target range
    // (entity perimeter) before disclosing its reservation form or using it as
    // a server-side ping oracle.
    $addressing = new Addressing();
    if (!$addressing->can($id_addressing, READ)) {
        throw new AccessDeniedHttpException();
    }

    Html::popHeader(ReserveIp::getTypeName());

    if ($ip !== false) {
        $ReserveIp = new ReserveIp();
        $ReserveIp->showReservationForm($ip, $id_addressing, $rand);
    }

    Html::popFooter();
}
