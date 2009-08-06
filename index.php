<?php
/*
 * @version $Id: setup.php,v 1.2 2006/04/02 14:45:27 moyo Exp $
 ---------------------------------------------------------------------- 
 GLPI - Gestionnaire Libre de Parc Informatique 
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
// Original Author of file: Gilles PORTHEAULT
// Purpose of file:
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array("search");
define('GLPI_ROOT', '../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

commonHeader($LANGADDRESSING["title"][1],$_SERVER['PHP_SELF'],"plugins","addressing");

if(plugin_addressing_haveRight("addressing","r") || haveRight("config","w")){

	if(!isset($_SESSION["glpi_plugin_addressing_installed"]) || $_SESSION["glpi_plugin_addressing_installed"]!=1) {
		glpi_header("./front/plugin_addressing.config.php");
	} 
	else {
	
	manageGetValuesInSearch(PLUGIN_ADDRESSING_TYPE);
		
	searchForm(PLUGIN_ADDRESSING_TYPE,$_SERVER['PHP_SELF'],$_GET["field"],$_GET["contains"],$_GET["sort"],$_GET["deleted"],$_GET["link"],$_GET["distinct"]);

	showList(PLUGIN_ADDRESSING_TYPE,$_SERVER['PHP_SELF'],$_GET["field"],$_GET["contains"],$_GET["sort"],$_GET["order"],$_GET["start"],$_GET["deleted"],$_GET["link"],$_GET["distinct"]);

	}

}else{
	echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
		echo "<b>".$LANG["login"][5]."</b></div>";
}

commonFooter();

?>