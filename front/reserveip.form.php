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

use GlpiPlugin\Addressing\ReserveIp;

$reserveip = new ReserveIp();

if (isset($_POST['add'])) {
    $reserveip->check(-1, CREATE, $_POST);
    // reserveip() returns false on each of its refusal paths, four of which are the
    // authorisation and entity boundary checks. Announcing a success regardless let a
    // technician record an address as reserved while it actually stayed free, and hid
    // every refused attempt from the operator.
    $reserved = $reserveip->reserveip($_POST);
    Html::popHeader(ReserveIp::getTypeName());
    if ($reserved) {
        echo "<div class='alert alert-important alert-info d-flex'>";
        echo __("The address has been reserved", "addressing");
        echo "</div>";
    } else {
        echo "<div class='alert alert-important alert-danger d-flex'>";
        echo __("The address could not be reserved", "addressing");
        echo "</div>";
    }
    Html::popFooter();
}
