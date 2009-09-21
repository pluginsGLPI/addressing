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

foreach (glob(GLPI_ROOT . '/plugins/addressing/inc/*.php') as $file)
	include_once ($file);


function plugin_addressing_install(){
	global $DB, $LANG, $CFG_GLPI;

	include_once (GLPI_ROOT."/inc/profile.class.php");

	if (!TableExists("glpi_plugin_addressing_display") &&!TableExists("glpi_plugin_addressing") && !TableExists("glpi_plugin_addressing_configs")) {

		plugin_addressing_Installv180();

	}elseif(!TableExists("glpi_plugin_addressing_profiles") && !FieldExists("glpi_plugin_addressing_display","ipconf1")) {//1.4

    plugin_addressing_updatev14();
    plugin_addressing_updatev15();
		plugin_addressing_updatev16();
		plugin_addressing_updatev170();
		plugin_addressing_updatev180();

	}elseif(!TableExists("glpi_plugin_addressing") && FieldExists("glpi_plugin_addressing_display","ipconf1")) {

    plugin_addressing_updatev15();
		plugin_addressing_updatev16();
		plugin_addressing_updatev170();
		plugin_addressing_updatev180();

	}elseif (TableExists("glpi_plugin_addressing_display") && !FieldExists("glpi_plugin_addressing","ipdeb")) {

		plugin_addressing_updatev16();
		plugin_addressing_updatev170();
		plugin_addressing_updatev180();

	}elseif(TableExists("glpi_plugin_addressing_profiles") && FieldExists("glpi_plugin_addressing_profiles","interface")) {

		plugin_addressing_updatev170();
		plugin_addressing_updatev180();

	}elseif(!TableExists("glpi_plugin_addressing_configs")) {

		plugin_addressing_updatev180();

	}

	plugin_addressing_createFirstAccess($_SESSION['glpiactiveprofile']['id']);
	return true;
}

function plugin_addressing_uninstall(){
	global $DB;

	$tables = array("glpi_plugin_addressing",
					"glpi_plugin_addressing_configs",
					"glpi_plugin_addressing_profiles");

	foreach($tables as $table)
		$DB->query("DROP TABLE `$table`;");

  $tables_glpi = array("glpi_displayprefs",
					"glpi_bookmarks");

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` = '".PLUGIN_ADDRESSING_TYPE."';");

	// TODO check is this is needed...
	plugin_init_addressing();

	return true;
}

////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_addressing_getSearchOption(){
	global $LANG;
	$sopt=array();

	// Part header
	$sopt[PLUGIN_ADDRESSING_TYPE]['common']=$LANG['plugin_addressing']['title'][1];

	$sopt[PLUGIN_ADDRESSING_TYPE][1]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][1]['field']='name';
	$sopt[PLUGIN_ADDRESSING_TYPE][1]['linkfield']='name';
	$sopt[PLUGIN_ADDRESSING_TYPE][1]['name']=$LANG['common'][16];
	$sopt[PLUGIN_ADDRESSING_TYPE][1]['datatype']='itemlink';

	$sopt[PLUGIN_ADDRESSING_TYPE][2]['table']='glpi_networks';
	$sopt[PLUGIN_ADDRESSING_TYPE][2]['field']='name';
	$sopt[PLUGIN_ADDRESSING_TYPE][2]['linkfield']='networks_id';
	$sopt[PLUGIN_ADDRESSING_TYPE][2]['name']=$LANG['plugin_addressing']['setup'][24];

	$sopt[PLUGIN_ADDRESSING_TYPE][3]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][3]['field']='comment';
	$sopt[PLUGIN_ADDRESSING_TYPE][3]['linkfield']='comment';
	$sopt[PLUGIN_ADDRESSING_TYPE][3]['name']=$LANG['common'][25];
	$sopt[PLUGIN_ADDRESSING_TYPE][3]['datatype']='text';

	$sopt[PLUGIN_ADDRESSING_TYPE][4]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][4]['field']='use_ping';
	$sopt[PLUGIN_ADDRESSING_TYPE][4]['linkfield']='use_ping';
	$sopt[PLUGIN_ADDRESSING_TYPE][4]['name']=$LANG['plugin_addressing']['reports'][30];
	$sopt[PLUGIN_ADDRESSING_TYPE][4]['datatype']='bool';

	$sopt[PLUGIN_ADDRESSING_TYPE][5]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][5]['field']='generation_link';
	$sopt[PLUGIN_ADDRESSING_TYPE][5]['linkfield']='';
	$sopt[PLUGIN_ADDRESSING_TYPE][5]['name']=$LANG['plugin_addressing'][3];

	$sopt[PLUGIN_ADDRESSING_TYPE][30]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][30]['field']='id';
	$sopt[PLUGIN_ADDRESSING_TYPE][30]['linkfield']='';
	$sopt[PLUGIN_ADDRESSING_TYPE][30]['name']=$LANG['common'][2];

	$sopt[PLUGIN_ADDRESSING_TYPE][80]['table']='glpi_entities';
	$sopt[PLUGIN_ADDRESSING_TYPE][80]['field']='completename';
	$sopt[PLUGIN_ADDRESSING_TYPE][80]['linkfield']='entities_id';
	$sopt[PLUGIN_ADDRESSING_TYPE][80]['name']=$LANG['entity'][0];

	$sopt[PLUGIN_ADDRESSING_TYPE][1000]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][1000]['field']='begin_ip';
	$sopt[PLUGIN_ADDRESSING_TYPE][1000]['linkfield']='';
	$sopt[PLUGIN_ADDRESSING_TYPE][1000]['name']=$LANG['plugin_addressing']['reports'][38];

	$sopt[PLUGIN_ADDRESSING_TYPE][1001]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][1001]['field']='end_ip';
	$sopt[PLUGIN_ADDRESSING_TYPE][1001]['linkfield']='';
	$sopt[PLUGIN_ADDRESSING_TYPE][1001]['name']=$LANG['plugin_addressing']['reports'][39];

	// Use a plugin type reservation to avoid conflict
	$sopt[PROFILE_TYPE][5000]['table']='glpi_plugin_addressing_profiles';
	$sopt[PROFILE_TYPE][5000]['field']='addressing';
	$sopt[PROFILE_TYPE][5000]['linkfield']='id';
	$sopt[PROFILE_TYPE][5000]['name']=$LANG['plugin_addressing']['title'][1];
	//$sopt[PROFILE_TYPE][5000]['datatype']='bool';

	return $sopt;
}

function plugin_addressing_giveItem($type,$ID,$data,$num){
	global $CFG_GLPI, $INFOFORM_PAGES, $LANG,$SEARCH_OPTION;

	$table=$SEARCH_OPTION[$type][$ID]["table"];
	$field=$SEARCH_OPTION[$type][$ID]["field"];

	switch ($table.'.'.$field){
		case "glpi_plugin_addressing.generation_link" :
			$out= "<a href=\"front/plugin_addressing.display.php?id=".$data["id"]."\">".$LANG['plugin_addressing'][4]."</a>";
			return $out;
			break;
		case "glpi_plugin_addressing_profiles.addressing":
			switch($data["ITEM_$num"]) {
				case 'w':
					return $LANG['profiles'][11];
					break;
				case 'r':
					return $LANG['profiles'][10];
					break;
				default:
					return $LANG['profiles'][12];
			}
			break;
	}
	return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_addressing_MassiveActions($type){
	global $LANG;

	switch ($type){

		case PLUGIN_ADDRESSING_TYPE:
			return array(
				"plugin_addressing_transfert"=>$LANG['buttons'][48],
				);
			break;
		case PROFILE_TYPE:
			return array(
				"plugin_addressing_allow" => $LANG['plugin_addressing']['title'][1] . " - " . $LANG['plugin_addressing']['profile'][3],
				);
			break;

	}
	return array();
}

function plugin_addressing_MassiveActionsDisplay($type,$action){
	global $LANG,$CFG_GLPI;

	switch ($type){

		case PLUGIN_ADDRESSING_TYPE:
			switch ($action){
				case "plugin_addressing_transfert":
					dropdownValue("glpi_entities", "entities_id", '');
					echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
					break;
			}
			break;

		case PROFILE_TYPE:
			switch ($action){
				case 'plugin_addressing_allow':
					dropdownNoneReadWrite('use','');
					echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG['buttons'][2]."\" >";
					break;
			}
			break;
	}

	return "";
}

function plugin_addressing_MassiveActionsProcess($data){
	global $LANG,$DB;

	switch ($data['action']){

		case "plugin_addressing_transfert":
			if ($data['itemtype']==PLUGIN_ADDRESSING_TYPE){
				foreach ($data["item"] as $key => $val){
					if ($val==1){
						$PluginAddressing=new PluginAddressing;
						$PluginAddressing->getFromDB($key);

						$query="UPDATE `glpi_plugin_addressing`
									SET `entities_id` = '".$data['entities_id']."'
									WHERE `glpi_plugin_addressing`.`id` ='$key'";
						$DB->query($query);
					}
				}
			}
			break;

		case 'plugin_addressing_allow':
			if ($data['itemtype']==PROFILE_TYPE) {
				$profglpi = new Profile();
				$prof = new PluginAddressingProfile();
				foreach ($data["item"] as $key => $val){
					if ($profglpi->getFromDB($key) && $profglpi->fields['interface']!='helpdesk') {
						if ($prof->getFromDB($key)) {
							$prof->update(array(
								'id' => $key,
								'addressing' => $data['use']
							));
						} else {
							$prof->add(array(
								'id' => $key,
								'name' => $profglpi->fields['name'],
								'addressing' => $data['use']
							));
						}
					}
				}
			}
			break;
	}
}

// Hook done on delete item case

function plugin_pre_item_delete_addressing($input){
	if (isset($input["_item_type_"]))
		switch ($input["_item_type_"]){
			case PROFILE_TYPE :
				// Manipulate data if needed
				$PluginAddressingProfile=new PluginAddressingProfile;
				$PluginAddressingProfile->cleanProfiles($input["id"]);
				break;
		}
	return $input;
}

// Do special actions for dynamic report
function plugin_addressing_dynamicReport($parm){

	$PluginAddressing=new PluginAddressing;

	if ($parm["item_type"]==PLUGIN_ADDRESSING_REPORT_TYPE && isset($_GET["id"]) && isset($_GET["display_type"]) && $PluginAddressing->getFromDB($_GET["id"])) {

		$result=$PluginAddressing->compute();
		plugin_addressing_display($result, $PluginAddressing);

		return true;
	}

	// Return false if no specific display is done, then use standard display
	return false;
}


// Define headings added by the plugin
function plugin_get_headings_addressing($type,$ID,$withtemplate){

	global $LANG;

	if ($type==PROFILE_TYPE){
		$prof = new Profile();
		if ($ID>0 && $prof->getFromDB($ID) && $prof->fields['interface']!='helpdesk') {
			return array(
				1 => $LANG['plugin_addressing']['title'][1],
				);
		}
	}
	return false;
}

// Define headings actions added by the plugin
function plugin_headings_actions_addressing($type){

	if (in_array($type,array(PROFILE_TYPE))){
		return array(
					1 => "plugin_headings_addressing",
					);
	}
	return false;
}

// action heading
function plugin_headings_addressing($type,$ID,$withtemplate=0){
	global $CFG_GLPI,$LANG;

	switch ($type){
		case PROFILE_TYPE :
			$prof=new PluginAddressingProfile();
			if (!$prof->GetfromDB($ID))
				plugin_addressing_createAccess($ID);
			$prof->showForm($CFG_GLPI["root_doc"]."/plugins/addressing/front/plugin_addressing.profile.php",$ID);
		break;
	}
}

?>