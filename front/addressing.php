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


use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Addressing\Addressing;

Session::checkLoginUser();

$Addressing = new Addressing();

if (isset($_GET['action']) && $_GET['action'] == 'isName') {
    if ($Addressing->canView() || Session::haveRight("config", UPDATE)) {
        $item = new $_GET['type']();
        $datas = $item->find(['name' => ['LIKE', $_GET['name']]]);
        if (count($datas) > 0) {
            echo json_encode(true);
        } else {
            echo json_encode(false);
        }
    }
} else {
    Html::header(Addressing::getTypeName(2), '', "tools", Addressing::class);

    if ($Addressing->canView() || Session::haveRight("config", UPDATE)) {
        Search::show(Addressing::class);
    } else {
        throw new AccessDeniedHttpException();
    }

    Html::footer();
}
