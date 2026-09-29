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

use Glpi\Application\View\TemplateRenderer;
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
    TemplateRenderer::getInstance()->display('@addressing/reserveip_result.html.twig', [
        'reserved' => $reserved,
    ]);
    Html::popFooter();
}
