<?php
/*
 * @version $Id: report.form.php 179 2014-04-02 08:11:29Z tsmr $
 -------------------------------------------------------------------------
 Addressing plugin for GLPI
 Copyright (C) 2003-2011 by the addressing Development Team.

 https://forge.indepnet.net/projects/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 Addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Addressing. If not, see <http://www.gnu.org/licenses/>.
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
   $reserveip->showForm($_REQUEST["ip"], $_REQUEST["id_addressing"]);
   Html::footer();
}
