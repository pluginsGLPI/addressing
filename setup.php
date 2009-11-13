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

include_once ("inc/plugin_addressing.profile.class.php");

// Init the hooks of the plugins -Needed
function plugin_init_addressing() {

	global $PLUGIN_HOOKS,$CFG_GLPI,$LANG;

	$PLUGIN_HOOKS['change_profile']['addressing'] = 'plugin_addressing_changeProfile';

	// Params : plugin name - string type - number - class - table - form page
	registerPluginType('addressing', 'PLUGIN_ADDRESSING_TYPE', 5000, array(
		'classname'  => 'PluginAddressing',
		'tablename'  => 'glpi_plugin_addressing',
		'formpage'   => 'front/plugin_addressing.form.php',
		'searchpage' => 'index.php',
		'deleted_tables' => true,
		'specif_entities_tables' => true
		));

	registerPluginType('addressing', 'PLUGIN_ADDRESSING_REPORT_TYPE', 5001, array(
		'classname' => 'PluginAddressingReport',
		'tablename'  => 'glpi_plugin_addressing'
		));

	if (isset($_SESSION["glpiID"])) {

		if (plugin_addressing_haveRight("addressing","r")) {

			$PLUGIN_HOOKS['menu_entry']['addressing'] = true;
			$PLUGIN_HOOKS['submenu_entry']['addressing']['search'] = 'index.php';
			$PLUGIN_HOOKS['headings']['addressing'] = 'plugin_get_headings_addressing';
			$PLUGIN_HOOKS['headings_action']['addressing'] = 'plugin_headings_actions_addressing';
		}
		if (plugin_addressing_haveRight("addressing","w")) {
			$PLUGIN_HOOKS['submenu_entry']['addressing']['add'] = 'front/plugin_addressing.form.php?new=1';
			$PLUGIN_HOOKS['use_massive_action']['addressing']=1;
			$PLUGIN_HOOKS['pre_item_delete']['addressing'] = 'plugin_pre_item_delete_addressing';
		}
		// Config page
		if (haveRight("config","w")) {
			$PLUGIN_HOOKS['submenu_entry']['addressing']['config'] = 'front/plugin_addressing.config.php';
			$PLUGIN_HOOKS['config_page']['addressing'] = 'front/plugin_addressing.config.php';
		}

		// Add specific files to add to the header : javascript or css
		//$PLUGIN_HOOKS['add_javascript']['example']="example.js";
		$PLUGIN_HOOKS['add_css']['addressing']="addressing.css";
		$PLUGIN_HOOKS['add_javascript']['addressing']='addressing.js';

	}


}
// Get the name and the version of the plugin - Needed
function plugin_version_addressing() {
	global $LANG;

	return array (
		'name' => $LANG['plugin_addressing']['title'][1],
		'version' => '1.8.0',
		'author'=>'Gilles Portheault, Xavier Caillaud, Remi Collet',
		'homepage'=>'https://forge.indepnet.net/projects/show/addressing',
		'minGlpiVersion' => '0.80',// For compatibility / no install in version < 0.72
	);
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_addressing_check_prerequisites() {
	if (GLPI_VERSION>=0.80) {
		return true;
	} else {
		echo "GLPI version not compatible need 0.80";
	}
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_addressing_check_config() {
	return true;
}

function plugin_addressing_changeProfile() {
	$PluginAddressingProfile=new PluginAddressingProfile();
	$PluginAddressingProfile->changeProfile();
}

function plugin_addressing_haveRight($module,$right) {
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

// Define rights for the plugin types
function plugin_addressing_haveTypeRight($type,$right) {
	switch ($type){
		case PLUGIN_ADDRESSING_TYPE :
			// 1 - All rights for all users
			// return true;
			// 2 - Similarity right : same right of computer
			return plugin_addressing_haveRight("addressing",$right);
			break;
	}
}

?>