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

include ('../../../inc/includes.php');

$reserveip = new PluginAddressingReserveip();

if (isset($_POST['add'])) {
   $reserveip->check(-1, CREATE, $_POST);
   $reserveip->reserveip($_POST);
   Html::back();
} else {
   Html::header(PluginAddressingReserveip::getTypeName(), '', "tools", "pluginaddressingmenu");
   $reserveip->showReservationForm($_REQUEST["ip"], $_REQUEST["id_addressing"], $_REQUEST['rand']);
   Html::footer();
}
