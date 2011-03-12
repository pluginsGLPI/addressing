<?php
/*
 * @version $Id: HEADER 2011-03-12 18:01:26 tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Author of file: CAILLAUD Xavier & COLLET Remi
// Purpose of file: plugin addressing v1.9.0 - GLPI 0.80
// ----------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

if (!isset($_GET["id"])) $_GET["id"] = "";
if (isset($_GET["start"])) $start=$_GET["start"];
else $start=0;

$PluginAddressingAddressing = new PluginAddressingAddressing();

if (isset($_POST["add"])) {

	$PluginAddressingAddressing->check(-1,'w',$_POST);
   if (!empty($_POST["name"]) && !empty($_POST["begin_ip"]) && !empty($_POST["end_ip"]))
      $newID=$PluginAddressingAddressing->add($_POST);
   else
      addMessageAfterRedirect($LANG['plugin_addressing']['setup'][27],false,ERROR);
	glpi_header($_SERVER['HTTP_REFERER']);

} else if (isset($_POST["delete"])) {

	$PluginAddressingAddressing->check($_POST['id'],'w');
	$PluginAddressingAddressing->delete($_POST);
	glpi_header(getItemTypeSearchURL('PluginAddressingAddressing'));

} else if (isset($_POST["restore"])) {

	$PluginAddressingAddressing->check($_POST['id'],'w');
	$PluginAddressingAddressing->restore($_POST);
	glpi_header(getItemTypeSearchURL('PluginAddressingAddressing'));

} else if (isset($_POST["purge"])) {
	$PluginAddressingAddressing->check($_POST['id'],'w');
	$PluginAddressingAddressing->delete($_POST,1);
	glpi_header(getItemTypeSearchURL('PluginAddressingAddressing'));

} else if (isset($_POST["update"])) {

	$PluginAddressingAddressing->check($_POST['id'],'w');
   if (!empty($_POST["name"]) && !empty($_POST["begin_ip"]) && !empty($_POST["end_ip"]))
      $PluginAddressingAddressing->update($_POST);
   else
      addMessageAfterRedirect($LANG['plugin_addressing']['setup'][27],false,ERROR);
	glpi_header($_SERVER['HTTP_REFERER']);

} else {

	$PluginAddressingAddressing->checkGlobal("r");

	commonHeader($LANG['plugin_addressing']['title'][1],'',"plugins","addressing");

	$PluginAddressingAddressing->showForm($_GET["id"]);

	commonFooter();
}

?>