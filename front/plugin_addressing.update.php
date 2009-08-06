<?php
/*
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
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array("profile");
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");
if (haveRight("config","w") && haveRight("profile","w")){

	if (!TableExists("glpi_plugin_addressing_profiles")) {
		cleanCache("GLPI_HEADER_".$_SESSION["glpiID"]);
		plugin_addressing_updatev14();
		plugin_addressing_updatev15();
		plugin_addressing_updatev16();
		plugin_addressing_createfirstaccess($_SESSION['glpiactiveprofile']['ID']);
		plugin_addressing_initSession();
	} else if (!TableExists("glpi_plugin_addressing")) {
		cleanCache("GLPI_HEADER_".$_SESSION["glpiID"]);
		plugin_addressing_updatev15();
		plugin_addressing_updatev16();
		plugin_addressing_createfirstaccess($_SESSION['glpiactiveprofile']['ID']);
		plugin_addressing_initSession();
	} else if (!FieldExists("glpi_plugin_addressing","ipdeb")) {
		plugin_addressing_updatev16();
		plugin_addressing_initSession();
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}else{

	commonHeader($LANG["login"][5],$_SERVER['PHP_SELF'],"plugins","addressing");
	echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>";
	echo "<b>".$LANG["login"][5]."</b></div>";
	commonFooter();
}

?>