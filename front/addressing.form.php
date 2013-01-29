<?php
/*
 * @version $Id$
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

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

if (isset($_GET["start"])) {
   $start = $_GET["start"];
} else {
   $start = 0;
}

$PluginAddressingAddressing = new PluginAddressingAddressing();

if (isset($_POST["add"])) {
   $PluginAddressingAddressing->check(-1, 'w', $_POST);
   if (!empty($_POST["name"]) && !empty($_POST["begin_ip"]) && !empty($_POST["end_ip"])) {
      $newID = $PluginAddressingAddressing->add($_POST);
   } else {
      Session::addMessageAfterRedirect(__('Problem when adding, required fields are not here','addressing'), false, ERROR);
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $PluginAddressingAddressing->check($_POST['id'], 'w');
   $PluginAddressingAddressing->delete($_POST);
   $PluginAddressingAddressing->redirectToList();

} else if (isset($_POST["restore"])) {
   $PluginAddressingAddressing->check($_POST['id'], 'w');
   $PluginAddressingAddressing->restore($_POST);
   $PluginAddressingAddressing->redirectToList();

} else if (isset($_POST["purge"])) {
   $PluginAddressingAddressing->check($_POST['id'],'w');
   $PluginAddressingAddressing->delete($_POST,1);
   $PluginAddressingAddressing->redirectToList();

} else if (isset($_POST["update"])) {
   $PluginAddressingAddressing->check($_POST['id'], 'w');
   if (!empty($_POST["name"]) && !empty($_POST["begin_ip"]) && !empty($_POST["end_ip"])) {
      $PluginAddressingAddressing->update($_POST);
   } else {
      Session::addMessageAfterRedirect(__('Problem when adding, required fields are not here','addressing'), false, ERROR);
   }
   Html::back();

} else {
   $PluginAddressingAddressing->checkGlobal("r");
   Html::header(PluginAddressingAddressing::getTypeName(2), '', "plugins", "addressing");
   $PluginAddressingAddressing->showForm($_GET["id"]);
   Html::footer();
}
?>