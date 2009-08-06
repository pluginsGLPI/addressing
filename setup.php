<?php
/*
 * @version $Id: setup.php,v 1.3 2006/04/02 16:12:23 moyo Exp $
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copynetwork (C) 2003-2006 by the INDEPNET Development Team.

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
// Original Author of file: GRISARD Jean Marc
// Purpose of file:
// ----------------------------------------------------------------------

include_once ("inc/plugin_addressing.functions_auth.php");
include_once ("inc/plugin_addressing.functions_db.php");
include_once ("inc/plugin_addressing.functions_setup.php");
include_once ("inc/plugin_addressing.functions_display.php");
include_once ("inc/plugin_addressing.classes.php");

// Init the hooks of the plugins -Needed
function plugin_init_addressing() {
	
	global $PLUGIN_HOOKS,$CFG_GLPI,$LANGADDRESSING;
	
	$PLUGIN_HOOKS['init_session']['addressing'] = 'plugin_addressing_initSession';
	$PLUGIN_HOOKS['change_profile']['addressing'] = 'plugin_addressing_changeprofile';

	if (isset($_SESSION["glpiID"])){
			
			array_push($CFG_GLPI["deleted_tables"],"glpi_plugin_addressing");
			array_push($CFG_GLPI["specif_entities_tables"],"glpi_plugin_addressing");
		
		if (plugin_addressing_haveRight("addressing","r") && (isset($_SESSION["glpi_plugin_addressing_installed"]) && $_SESSION["glpi_plugin_addressing_installed"]==1) && isset($_SESSION["glpi_plugin_addressing_profile"])){

			$PLUGIN_HOOKS['menu_entry']['addressing'] = true;
			$PLUGIN_HOOKS['submenu_entry']['addressing']['search'] = 'index.php';
		}	
		if (plugin_addressing_haveRight("addressing","w") && (isset($_SESSION["glpi_plugin_addressing_installed"]) && $_SESSION["glpi_plugin_addressing_installed"]==1) && isset($_SESSION["glpi_plugin_addressing_profile"])){
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
		
		// Params : plugin name - string type - number - class - table - form page
		pluginNewType('addressing',"PLUGIN_ADDRESSING_TYPE",5000,"plugin_addressing","glpi_plugin_addressing","front/plugin_addressing.form.php");
		pluginNewType('addressing',"PLUGIN_ADDRESSING_REPORT_TYPE",5001,"plugin_addressing","","");
	}
	
	
}
// Get the name and the version of the plugin - Needed
function plugin_version_addressing(){
	global $LANGADDRESSING;

	return array( 'name'    => $LANGADDRESSING["title"][1],
		'minGlpiVersion' => '0.71', // Optional but recommended
		'version' => '1.6');
}

// Define rights for the plugin types
function plugin_addressing_haveTypeRight($type,$right){
	switch ($type){
		case PLUGIN_ADDRESSING_TYPE :
			// 1 - All rights for all users
			// return true;
			// 2 - Similarity right : same right of computer
			return plugin_addressing_haveRight("addressing",$right);
			break;
	}
}

////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_addressing_getSearchOption(){
	global $LANGADDRESSING,$LANG;
	$sopt=array();

	// Part header
	$sopt[PLUGIN_ADDRESSING_TYPE]['common']=$LANGADDRESSING["title"][1];
	
	$sopt[PLUGIN_ADDRESSING_TYPE][1]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][1]['field']='name';
	$sopt[PLUGIN_ADDRESSING_TYPE][1]['linkfield']='name';
	$sopt[PLUGIN_ADDRESSING_TYPE][1]['name']=$LANG["common"][16];
	
	$sopt[PLUGIN_ADDRESSING_TYPE][2]['table']='glpi_dropdown_network';
	$sopt[PLUGIN_ADDRESSING_TYPE][2]['field']='name';
	$sopt[PLUGIN_ADDRESSING_TYPE][2]['linkfield']='network';
	$sopt[PLUGIN_ADDRESSING_TYPE][2]['name']=$LANGADDRESSING["setup"][24];
	
	$sopt[PLUGIN_ADDRESSING_TYPE][3]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][3]['field']='comments';
	$sopt[PLUGIN_ADDRESSING_TYPE][3]['linkfield']='comments';
	$sopt[PLUGIN_ADDRESSING_TYPE][3]['name']=$LANG["common"][25];
	
	$sopt[PLUGIN_ADDRESSING_TYPE][4]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][4]['field']='ping';
	$sopt[PLUGIN_ADDRESSING_TYPE][4]['linkfield']='ping';
	$sopt[PLUGIN_ADDRESSING_TYPE][4]['name']=$LANGADDRESSING["reports"][30];
	
	$sopt[PLUGIN_ADDRESSING_TYPE][5]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][5]['field']='link';
	$sopt[PLUGIN_ADDRESSING_TYPE][5]['linkfield']='';
	$sopt[PLUGIN_ADDRESSING_TYPE][5]['name']=$LANGADDRESSING["addressing"][3];
	
	$sopt[PLUGIN_ADDRESSING_TYPE][30]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][30]['field']='ID';
	$sopt[PLUGIN_ADDRESSING_TYPE][30]['linkfield']='';
	$sopt[PLUGIN_ADDRESSING_TYPE][30]['name']=$LANG["common"][2];
	
	$sopt[PLUGIN_ADDRESSING_TYPE][80]['table']='glpi_entities';
	$sopt[PLUGIN_ADDRESSING_TYPE][80]['field']='completename';
	$sopt[PLUGIN_ADDRESSING_TYPE][80]['linkfield']='FK_entities';
	$sopt[PLUGIN_ADDRESSING_TYPE][80]['name']=$LANG["entity"][0];

	$sopt[PLUGIN_ADDRESSING_TYPE][1000]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][1000]['field']='ipdeb';
	$sopt[PLUGIN_ADDRESSING_TYPE][1000]['linkfield']='';
	$sopt[PLUGIN_ADDRESSING_TYPE][1000]['name']=$LANGADDRESSING["reports"][38];

	$sopt[PLUGIN_ADDRESSING_TYPE][1001]['table']='glpi_plugin_addressing';
	$sopt[PLUGIN_ADDRESSING_TYPE][1001]['field']='ipfin';
	$sopt[PLUGIN_ADDRESSING_TYPE][1001]['linkfield']='';
	$sopt[PLUGIN_ADDRESSING_TYPE][1001]['name']=$LANGADDRESSING["reports"][39];
	
	return $sopt;
}

function plugin_addressing_giveItem($type,$field,$data,$num,$linkfield=""){
	global $CFG_GLPI,$DB,$INFOFORM_PAGES,$LANGADDRESSING,$LANG;

	switch ($field){
		case "glpi_plugin_addressing.name" :
			$out= "<a href=\"".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$type]."?ID=".$data['ID']."\">";
			$out.= $data["ITEM_$num"];
			if ($CFG_GLPI["view_ID"]||empty($data["ITEM_$num"])) $out.= " (".$data["ID"].")";
			$out.= "</a>";
			return $out;
			break;
		case "glpi_plugin_addressing.comments" :	
				$out= nl2br($data["ITEM_$num"]);
		return $out;
		break;
		case "glpi_dropdown_network.name" :
				$out= $data["ITEM_$num"];
		return $out;
		break;
		case "glpi_plugin_addressing.ping" :
				if ($data["ITEM_$num"]=='1') $out=$LANG["choice"][1];
				else $out=$LANG["choice"][0];
		return $out;
		break;
		case "glpi_plugin_addressing.link" :	
			$out= "<a href=\"front/plugin_addressing.display.php?ID=".$data["ID"]."\">".$LANGADDRESSING["addressing"][4]."</a>";
		return $out;
		break;	
	
	}
	return "";
}


////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function plugin_addressing_MassiveActions($type){
	global $LANG,$LANGADDRESSING;
	switch ($type){
		
		case PLUGIN_ADDRESSING_TYPE:
			return array(
				"plugin_addressing_transfert"=>$LANG["buttons"][48],
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
					dropdownValue("glpi_entities", "FK_entities", '');
				echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"".$LANG["buttons"][2]."\" >";
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
			if ($data['device_type']==PLUGIN_ADDRESSING_TYPE){
				foreach ($data["item"] as $key => $val){
					if ($val==1){
						$plugin_addressing=new plugin_addressing;
						$plugin_addressing->getFromDB($key);
					
						$query="UPDATE `glpi_plugin_addressing` SET `FK_entities` = '".$data['FK_entities']."' WHERE `glpi_plugin_addressing`.`ID` ='$key'";
						$DB->query($query);
					}
				}
			}	
		break;
	}
}

function plugin_addressing_MassiveActionsFieldsDisplay($type,$table,$field,$linkfield){
	global $LINK_ID_TABLE;
	if ($table==$LINK_ID_TABLE[$type]){
		// Table fields
		switch ($table.".".$field){
			case "glpi_plugin_addressing.ping":
				dropdownYesNo("ping");
				echo "<input type='hidden' name='field' value='ping'>";
			return true;
			break;
			
		}

	} 
	// Need to return false on non display item
	return false;
}

// Hook done on delete item case

function plugin_pre_item_delete_addressing($input){
	if (isset($input["_item_type_"]))
		switch ($input["_item_type_"]){
			case PROFILE_TYPE :
				// Manipulate data if needed 
				$plugin_addressing_Profile=new plugin_addressing_Profile;
				$plugin_addressing_Profile->cleanProfiles($input["ID"]);
				break;
		}
	return $input;
}

// Do special actions for dynamic report
function plugin_addressing_dynamicReport($parm){

	$plugin_addressing=new plugin_addressing;
	
	if ($parm["item_type"]==PLUGIN_ADDRESSING_REPORT_TYPE && isset($_GET["ID"]) && isset($_GET["display_type"]) && $plugin_addressing->getFromDB($_GET["ID"])) {

		$result=$plugin_addressing->compute();	
		plugin_addressing_display($result, $plugin_addressing);
		
		return true;
	}
	
	// Return false if no specific display is done, then use standard display
	return false;
}

?>