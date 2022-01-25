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

include ('../../../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

if (isset($_GET["start"])) {
   $start = $_GET["start"];
} else {
   $start = 0;
}

$addressing = new PluginAddressingAddressing();

if (isset($_POST["add"])) {
   $addressing->check(-1, CREATE, $_POST);
   if (!empty($_POST["name"])
      && !empty($_POST["begin_ip"])
         && !empty($_POST["end_ip"])) {
      $newID = $addressing->add($_POST);

   } else {
      Session::addMessageAfterRedirect(__('Problem when adding, required fields are not here', 'addressing'),
                                       false, ERROR);
   }
   if ($_SESSION['glpibackcreated']) {
      Html::redirect($addressing->getFormURL()."?id=".$newID);
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $addressing->check($_POST['id'], DELETE);
   $addressing->delete($_POST);
   $addressing->redirectToList();

} else if (isset($_POST["restore"])) {
   $addressing->check($_POST['id'], PURGE);
   $addressing->restore($_POST);
   $addressing->redirectToList();

} else if (isset($_POST["purge"])) {
   $addressing->check($_POST['id'], PURGE);
   $addressing->delete($_POST, 1);
   $addressing->redirectToList();

} else if (isset($_POST["update"])) {
   $addressing->check($_POST['id'], UPDATE);
   if (!empty($_POST["name"])
      && !empty($_POST["begin_ip"])
         && !empty($_POST["end_ip"])) {
      $addressing->update($_POST);
   } else {
      Session::addMessageAfterRedirect(__('Problem when adding, required fields are not here', 'addressing'),
                                       false, ERROR);
   }
   Html::back();

} else if (isset($_POST["search"])) {

   $addressing->checkGlobal(READ);
   Html::header(PluginAddressingAddressing::getTypeName(2), '', "tools", "pluginaddressingaddressing");
   $addressing->display($_POST);
   Html::footer();
} else {
   $addressing->checkGlobal(READ);
   Html::header(PluginAddressingAddressing::getTypeName(2), '', "tools", "pluginaddressingaddressing");
   $addressing->display($_GET);
   Html::footer();
}
