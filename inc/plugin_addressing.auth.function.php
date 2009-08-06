<?php
/*
 * @version $Id: auth.function.php 3576 2006-06-12 08:40:44Z moyo $
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
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
// Original Author of file: GRISARD Jean Marc
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

function plugin_addressing_createFirstAccess($ID){

	$PluginAddressingProfile=new PluginAddressingProfile();
	if (!$PluginAddressingProfile->GetfromDB($ID)){

		$Profile=new Profile();
		$Profile->GetfromDB($ID);
		$name=$Profile->fields["name"];

		$PluginAddressingProfile->add(array(
			'id' => $ID,
			'name' => $name,
			'addressing' => 'w'));
	}

}

function plugin_addressing_createAccess($ID){

	$PluginAddressingProfile=new PluginAddressingProfile();
	$Profile=new Profile();
	$Profile->GetfromDB($ID);
	$name=$Profile->fields["name"];

	$PluginAddressingProfile->add(array(
		'id' => $ID,
		'name' => $name));
}

function plugin_addressing_changeProfile()
{
	$prof=new PluginAddressingProfile();
	if($prof->getFromDB($_SESSION['glpiactiveprofile']['id']))
		$_SESSION["glpi_plugin_addressing_profile"]=$prof->fields;
	else
		unset($_SESSION["glpi_plugin_addressing_profile"]);
}

function plugin_addressing_haveRight($module,$right){
	$matches=array(
			""  => array("","r","w"), // ne doit pas arriver normalement
			"r" => array("r","w"),
			"w" => array("w"),
			"1" => array("1"),
			"0" => array("0","1"), // ne doit pas arriver non plus
		      );
	if (isset($_SESSION["glpi_plugin_addressing_profile"][$module])&&in_array($_SESSION["glpi_plugin_addressing_profile"][$module],$matches[$right]))
		return true;
	else return false;
}

function plugin_addressing_checkRight($module, $right) {
	global $CFG_GLPI;

	if (!plugin_addressing_haveRight($module, $right)) {
		// Gestion timeout session
		if (!isset ($_SESSION["glpiID"])) {
			glpi_header($CFG_GLPI["root_doc"] . "/index.php");
			exit ();
		}

		displayRightError();
	}
}
?>