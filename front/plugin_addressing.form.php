<?php
/*
   ----------------------------------------------------------------------
   GLPI - financialnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

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
   ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------


$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");
if(isset($_GET)) $tab = $_GET;
if(empty($tab) && isset($_POST)) $tab = $_POST;
if(!isset($tab["ID"])) $tab["ID"] = "";

if (isset($_GET["start"])) $start=$_GET["start"];
else $start=0;

$plugin_addressing=new plugin_addressing();

if (isset($_POST["add"]))
{
	if( plugin_addressing_HaveRight("addressing","w"))
		
		if (!empty($_POST["name"]) && !empty($_POST["ipdeb"]) && !empty($_POST["ipfin"]))
			$newID=$plugin_addressing->add($_POST);
		else
			addMessageAfterRedirect($LANGADDRESSING["setup"][27]);
	glpi_header($_SERVER['HTTP_REFERER']);
} 
else if (isset($_POST["delete"]))
{

	if( plugin_addressing_HaveRight("addressing","w"))
		$plugin_addressing->delete($_POST);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/addressing/index.php");
}
else if (isset($_POST["restore"]))
{

	if( plugin_addressing_HaveRight("addressing","w"))
		$plugin_addressing->restore($_POST);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/addressing/index.php");
}
else if (isset($_POST["purge"]))
{
	if( plugin_addressing_HaveRight("addressing","w"))
		$plugin_addressing->delete($_POST,1);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/addressing/index.php");
}
else if (isset($_POST["update"]))
{
	if( plugin_addressing_HaveRight("addressing","w")) {
		if (!empty($_POST["name"]) && !empty($_POST["ipdeb"]) && !empty($_POST["ipfin"]))
			$plugin_addressing->update($_POST);
		else
			addMessageAfterRedirect($LANGADDRESSING["setup"][27]);		
	}
	glpi_header($_SERVER['HTTP_REFERER']);
} 
else
{

	plugin_addressing_checkRight("addressing","r");


	if (!isset($_SESSION['glpi_onglet'])) $_SESSION['glpi_onglet']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_onglet']=$_GET['onglet'];

	}

	commonHeader($LANGADDRESSING["title"][1],$_SERVER["PHP_SELF"],"plugins","addressing");

	if ($plugin_addressing->showForm($_SERVER["PHP_SELF"],$tab["ID"])) {
		if (!empty($tab['ID']))
			switch($_SESSION['glpi_onglet']){
				case -1 :
					break;
				default :
					
					break;
			}
	}	

	commonFooter();
}

?>
