<?php
/*
 * @version $Id: HEADER 1 2009-09-21 14:58 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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
// Purpose of file: plugin addressing v1.8.0 - GLPI 0.80
// ----------------------------------------------------------------------
 */

$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

useplugin('addressing',true);

if (isset($_GET)) $tab = $_GET;
if (empty($tab) && isset($_POST)) $tab = $_POST;
if (!isset($tab["id"])) $tab["id"] = "";

if (isset($_GET["start"])) $start=$_GET["start"];
else $start=0;

$PluginAddressing=new PluginAddressing();
$PluginAddressingProfile=new PluginAddressingProfile();

if (isset($_POST["add"]))
{
	if ( plugin_addressing_haveRight("addressing","w"))

		if (!empty($_POST["name"]) && !empty($_POST["begin_ip"]) && !empty($_POST["end_ip"]))
			$newID=$PluginAddressing->add($_POST);
		else
			addMessageAfterRedirect($LANG['plugin_addressing']['setup'][27],false,ERROR);
	glpi_header($_SERVER['HTTP_REFERER']);
	
} else if (isset($_POST["delete"])) {

	if ( plugin_addressing_haveRight("addressing","w"))
		$PluginAddressing->delete($_POST);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/addressing/index.php");
	
} else if (isset($_POST["restore"])) {

	if ( plugin_addressing_haveRight("addressing","w"))
		$PluginAddressing->restore($_POST);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/addressing/index.php");
	
} else if (isset($_POST["purge"])) {
	if ( plugin_addressing_haveRight("addressing","w"))
		$PluginAddressing->delete($_POST,1);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/addressing/index.php");
	
} else if (isset($_POST["update"])) {
	if ( plugin_addressing_haveRight("addressing","w")) {
		if (!empty($_POST["name"]) && !empty($_POST["begin_ip"]) && !empty($_POST["end_ip"]))
			$PluginAddressing->update($_POST);
		else
			addMessageAfterRedirect($LANG['plugin_addressing']['setup'][27],false,ERROR);
	}
	glpi_header($_SERVER['HTTP_REFERER']);
	
} else {

	$PluginAddressingProfile->checkRight("addressing","r");

	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_tab']=$_GET['onglet'];
		//		glpi_header($_SERVER['HTTP_REFERER']);
	}
	commonHeader($LANG['plugin_addressing']['title'][1],$_SERVER["PHP_SELF"],"plugins","addressing");

	$PluginAddressing->showForm($_SERVER["PHP_SELF"],$tab["id"]);

	commonFooter();
}

?>