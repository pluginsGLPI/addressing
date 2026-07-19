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

//Options for GLPI 0.71 and newer : need slave db to access the report
use GlpiPlugin\Addressing\Addressing;

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

Session::checkLoginUser();

Html::header(Addressing::getTypeName(2), '', "tools", Addressing::class);

if (!isset($_GET["start"])) {
    $_GET["start"] = 0;
}

if (!isset($_GET["export"])) {
    $_GET["export"] = false;
}

$addressing = new Addressing();
// checkLoginUser() is not authorization on GLPI 11, and showReport() fetches the
// record by an arbitrary id. Enforce the plugin READ right AND entity access on the
// requested record before disclosing its IP report — otherwise any authenticated
// user could read any entity's asset/IP inventory by incrementing the id.
$addressing->check((int) ($_GET['id'] ?? 0), READ);
$addressing->showReport($_GET);

Html::footer();
