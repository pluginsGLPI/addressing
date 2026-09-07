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

use GlpiPlugin\Addressing\Addressing;

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$start = $_GET["start"] ?? 0;

$addressing = new Addressing();

if (isset($_POST["add"])) {
    $addressing->check(-1, CREATE, $_POST);
    if (!empty($_POST["name"])) {
        if ($addressing->checkip($_POST)) {
            $_POST['begin_ip'] = (int) $_POST['begin_ip0'] . "." . (int) $_POST['begin_ip1'] . ".";
            $_POST['begin_ip'] .= (int) $_POST['begin_ip2'] . "." . (int) $_POST['begin_ip3'];
            $_POST['end_ip'] = (int) $_POST['end_ip0'] . "." . (int) $_POST['end_ip1'] . ".";
            $_POST['end_ip'] .= (int) $_POST['end_ip2'] . "." . (int) $_POST['end_ip3'];
            $newID = $addressing->add($_POST);
            if ($_SESSION['glpibackcreated']) {
                Html::redirect($addressing->getFormURL() . "?id=" . $newID);
            }
            Html::back();
        } else {
            Html::back();
        }
    } else {
        Session::addMessageAfterRedirect(
            __('Problem when adding, required fields are not here', 'addressing'),
            false,
            ERROR,
        );
        Html::back();
    }

} elseif (isset($_POST["delete"])) {
    $addressing->check($_POST['id'], DELETE);
    $addressing->delete($_POST);
    $addressing->redirectToList();
} elseif (isset($_POST["restore"])) {
    $addressing->check($_POST['id'], PURGE);
    $addressing->restore($_POST);
    $addressing->redirectToList();
} elseif (isset($_POST["purge"])) {
    $addressing->check($_POST['id'], PURGE);
    $addressing->delete($_POST, 1);
    $addressing->redirectToList();
} elseif (isset($_POST["update"])) {
    $addressing->check($_POST['id'], UPDATE);
    if (!empty($_POST["name"])) {

        if ($addressing->checkip($_POST)) {
            $_POST['begin_ip'] = (int) $_POST['begin_ip0'] . "." . (int) $_POST['begin_ip1'] . ".";
            $_POST['begin_ip'] .= (int) $_POST['begin_ip2'] . "." . (int) $_POST['begin_ip3'];
            $_POST['end_ip'] = (int) $_POST['end_ip0'] . "." . (int) $_POST['end_ip1'] . ".";
            $_POST['end_ip'] .= (int) $_POST['end_ip2'] . "." . (int) $_POST['end_ip3'];
            $addressing->update($_POST);
            Html::back();
        } else {
            Html::back();
        }
    } else {
        Session::addMessageAfterRedirect(
            __('Problem when adding, required fields are not here', 'addressing'),
            false,
            ERROR,
        );
        Html::back();
    }

} elseif (isset($_POST["search"])) {
    // CommonGLPI::display() loads the range from the id of the array it is handed ($_POST
    // here) but only checks READ on $_GET["id"], which is forced to "" above when absent.
    // The per-item check would therefore be skipped and a POSTed id belonging to another
    // entity would be loaded and its name rendered in the navigation header. Check the
    // right on the id that is actually read; checkGlobal() alone only proves the plugin
    // right is held somewhere, not that this range is readable. Without an id the page
    // only renders the search form, for which the global right is enough.
    $search_id = (int) ($_POST['id'] ?? 0);
    if ($search_id > 0) {
        $addressing->check($search_id, READ);
    } else {
        $addressing->checkGlobal(READ);
    }
    Html::header(Addressing::getTypeName(2), '', "tools", Addressing::class);
    $addressing->display($_POST);
    Html::footer();
} else {
    // Same guard on the id display() will read here, applied before any output.
    $display_id = (int) ($_GET['id'] ?? 0);
    if ($display_id > 0) {
        $addressing->check($display_id, READ);
    } else {
        $addressing->checkGlobal(READ);
    }
    Html::header(Addressing::getTypeName(2), '', "tools", Addressing::class);
    $addressing->display($_GET);
    Html::footer();
}
