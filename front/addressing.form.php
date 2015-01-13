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

$addressing = new PluginAddressingAddressing();

if (isset($_POST["add"])) {
   $addressing->check(-1, CREATE, $_POST);
   if (!empty($_POST["name"]) 
      && !empty($_POST["begin_ip"]) 
         && !empty($_POST["end_ip"])) {
      $newID = $addressing->add($_POST);
      
   } else {
      Session::addMessageAfterRedirect(__('Problem when adding, required fields are not here','addressing'), 
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
   $addressing->delete($_POST,1);
   $addressing->redirectToList();

} else if (isset($_POST["update"])) {
   $addressing->check($_POST['id'], UPDATE);
   if (!empty($_POST["name"]) 
      && !empty($_POST["begin_ip"]) 
         && !empty($_POST["end_ip"])) {
      $addressing->update($_POST);
   } else {
      Session::addMessageAfterRedirect(__('Problem when adding, required fields are not here','addressing'), 
                                       false, ERROR);
   }
   Html::back();

} else {
   $addressing->checkGlobal(READ);
   Html::header(PluginAddressingAddressing::getTypeName(2), '', "tools", "pluginaddressingmenu");
   $addressing->display($_GET);
   Html::footer();
}
?>