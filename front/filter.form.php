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



Session::checkLoginUser();

$filter = new PluginAddressingFilter();

if (isset($_POST['add'])) {
    $filter->check(-1, CREATE, $_POST);
    unset($_POST['id']);
    $filter->add($_POST);
    Html::back();
} elseif (isset($_POST['update'])) {
    $filter->check($_POST['id'], UPDATE);
    $filter->update($_POST);
    Html::back();
} elseif (isset($_POST["purge"])) {
    $filter->check($_POST['id'], PURGE);
    $filter->delete($_POST, 1);
    Html::back();
}
