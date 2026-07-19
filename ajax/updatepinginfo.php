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

// ----------------------------------------------------------------------
// Original Author of file: Alban Lesellier
// ----------------------------------------------------------------------


use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Addressing\Addressing;
use GlpiPlugin\Addressing\PingInfo;

Session::checkRight('plugin_addressing', UPDATE);
header("Content-Type: application/json; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST['addressing_id'])) {
    echo 0;
    return;
}
$addressing_id = (int) $_POST['addressing_id'];

// The global right is not entity-aware: confirm the caller may act on this
// addressing range (entity perimeter) before rewriting its ping information.
$addressing = new Addressing();
if ($addressing_id <= 0 || !$addressing->can($addressing_id, UPDATE)) {
    throw new AccessDeniedHttpException();
}

$old_execution = ini_set("max_execution_time", "0");
$pingInfo = new PingInfo();
$pingInfo->updateAnAddressing($addressing);
ini_set("max_execution_time", $old_execution);

echo 1;
