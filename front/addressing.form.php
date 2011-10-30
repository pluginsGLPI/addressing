<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

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
      Session::addMessageAfterRedirect($LANG['plugin_addressing']['setup'][27], false, ERROR);
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $PluginAddressingAddressing->check($_POST['id'], 'w');
   $PluginAddressingAddressing->delete($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginAddressingAddressing'));

} else if (isset($_POST["restore"])) {
   $PluginAddressingAddressing->check($_POST['id'], 'w');
   $PluginAddressingAddressing->restore($_POST);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginAddressingAddressing'));

} else if (isset($_POST["purge"])) {
   $PluginAddressingAddressing->check($_POST['id'],'w');
   $PluginAddressingAddressing->delete($_POST,1);
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginAddressingAddressing'));

} else if (isset($_POST["update"])) {
   $PluginAddressingAddressing->check($_POST['id'], 'w');
   if (!empty($_POST["name"]) && !empty($_POST["begin_ip"]) && !empty($_POST["end_ip"])) {
      $PluginAddressingAddressing->update($_POST);
   } else {
      Session::addMessageAfterRedirect($LANG['plugin_addressing']['setup'][27], false, ERROR);
   }
   Html::back();

} else {
   $PluginAddressingAddressing->checkGlobal("r");
   Html::header($LANG['plugin_addressing']['title'][1], '', "plugins", "addressing");
   $PluginAddressingAddressing->showForm($_GET["id"]);
   Html::footer();
}
?>