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

use GlpiPlugin\Addressing\Ping_Equipment;

if (strpos($_SERVER['PHP_SELF'], "seePingTab.php")) {
    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

Session::checkRight("plugin_addressing_use_ping_in_equipment", READ);

if (isset($_POST['action']) && $_POST['action'] == "viewPingform") {
    echo Html::scriptBlock("$('#ping_item').show();");

    // itemtype/items_id are caller-supplied and drive which asset's IP/port data gets
    // disclosed below. Cast items_id to an int (it is otherwise reflected verbatim into
    // a JS literal further down the call chain) and let Ping_Equipment::showPingForm()
    // validate the itemtype against a whitelist and check READ on the target item.
    $itemtype = $_POST['itemtype'] ?? '';
    $items_id = (int) ($_POST['items_id'] ?? 0);

    $pingE = new Ping_Equipment();
    $pingE->showPingForm($itemtype, $items_id);
}

$_POST['name'] = "ping_item";
$_POST['rand'] = "";
Ajax::commonDropdownUpdateItem($_POST);
