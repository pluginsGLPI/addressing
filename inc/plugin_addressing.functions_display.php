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

function plugin_addressing_displaySearchNewLine($type,$odd=false){
	$out="";
	switch ($type){
		case PDF_OUTPUT_LANDSCAPE : //pdf
		case PDF_OUTPUT_PORTRAIT :
			break;
		case SYLK_OUTPUT : //sylk
//			$out="\n";
			break;
    	case CSV_OUTPUT : //csv
    		//$out="\n";
    		break;
		
		default :
			$class=" class='tab_bg_2' ";
			if ($odd){
				switch ($odd){
					case "double" : //double
						$class=" class='plugin_addressing_ip_double'";
					break;
					case "free" : //free
						$class=" class='plugin_addressing_ip_free'";
					break;
					case "ping_on" : //ping_on
						$class=" class='plugin_addressing_ping_on'";
					break;
					case "ping_off" : //ping_off
						$class=" class='plugin_addressing_ping_off'";
					break;
					default :
						$class=" class='tab_bg_1' ";
				}
			}
		$out="<tr $class>";
		break;
	}
	return $out;
}

function plugin_addressing_display(&$result, $plugin_addressing) {
	
	$network=$plugin_addressing->fields["network"];
	$ping=$plugin_addressing->fields["ping"];
		
	global $DB,$LANG,$CFG_GLPI,$LANGADDRESSING,$INFOFORM_PAGES;
	
	$plugin_addressing_display=new plugin_addressing_display();
	$plugin_addressing_display->getFromDB('1');
	$system=$plugin_addressing_display->fields["system"];

	// Set display type for export if define
	$output_type=HTML_OUTPUT;

	if (isset($_GET["display_type"]))
		$output_type=$_GET["display_type"];

	$ping_response=0;	
		
	$nbcols=6;
	$parameters="ID=";
	
	echo displaySearchHeader($output_type,1,$nbcols);
	echo plugin_addressing_displaySearchNewLine($output_type);
	$header_num=1;

	echo displaySearchHeaderItem($output_type,$LANGADDRESSING["reports"][2],$header_num);
	echo displaySearchHeaderItem($output_type,$LANGADDRESSING["reports"][9],$header_num);
	echo displaySearchHeaderItem($output_type,$LANGADDRESSING["reports"][14],$header_num);
	echo displaySearchHeaderItem($output_type,$LANGADDRESSING["reports"][5],$header_num);
	echo displaySearchHeaderItem($output_type,$LANGADDRESSING["reports"][8],$header_num);
	echo displaySearchHeaderItem($output_type,$LANGADDRESSING["reports"][23],$header_num);
	// End Line for column headers		
	echo displaySearchEndLine($output_type);
	$row_num=1;
		
	$ci=new CommonItem();
	$user = new User();
	
	foreach ($result as $num => $lines) {
		$ip=long2ip(substr($num,2));
		
		if (count($lines)) {
			if (count($lines)>1) {
				$disp = $plugin_addressing->fields["ip_double"];
			} else {
				$disp = $plugin_addressing->fields["ip_alloted"];
			}
			if ($disp) foreach ($lines as $line){
				$row_num++;
				$item_num=1;
				$name=$line["dname"];
				
				// IP
				echo plugin_addressing_displaySearchNewLine($output_type,(count($lines)>1 ? "double" : $row_num%2));				
				echo displaySearchItem($output_type,$ip,$item_num,$row_num);
				
				// Device
				$ci->setType($line["device_type"]);
				if (haveTypeRight($line["device_type"], "r")) {
					$output_iddev = "<a href='".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$line["device_type"]]."?ID=".$line["on_device"]."'>".$name
						.(empty($name) || $CFG_GLPI["view_ID"]?" (".$line["on_device"].")":"")."</a>";
				} else {
					$output_iddev = $name.(empty($name) || $CFG_GLPI["view_ID"]?" (".$line["on_device"].")":"");
				}
				
				echo displaySearchItem($output_type,$output_iddev,$item_num,$row_num);
				
				// User
				if ($line["FK_users"] && $user->getFromDB($line["FK_users"])) {					
					$username=formatUserName($user->fields["ID"],$user->fields["name"],$user->fields["realname"],$user->fields["firstname"]);
					
					if (haveTypeRight(USER_TYPE, "r")) {
						$output_iduser="<a href='".$CFG_GLPI["root_doc"]."/front/user.form.php?ID=".$line["FK_users"]."'>".$username."</a>";
					} else {
						$output_iduser=$username;
					}
					echo displaySearchItem($output_type,$output_iduser,$item_num,$row_num);
				} else {
					echo displaySearchItem($output_type," ",$item_num,$row_num);
				}
				
				// Mac
				if($line["ID"]){
					if (haveTypeRight($line["device_type"], "r")) {
						$output_mac = "<a href='".$CFG_GLPI["root_doc"]."/front/networking.port.php?ID=".$line["ID"]."'>".$line["ifmac"]."</a>";
					} else {
						$output_mac = $line["ifmac"];
					}
					echo displaySearchItem($output_type,$output_mac,$item_num,$row_num);
				} else {
					echo displaySearchItem($output_type," ",$item_num,$row_num);
				}
				// Type
				echo displaySearchItem($output_type,$ci->getType(),$item_num,$row_num);
				
				// Reserved
				if ($plugin_addressing->fields["ip_reserved"] && ereg("reserv",$line["pname"])) {
					echo displaySearchItem($output_type,$LANGADDRESSING["reports"][13],$item_num,$row_num);
				} else {
					echo displaySearchItem($output_type," ",$item_num,$row_num);		
				}
				
				// End
				echo displaySearchEndLine($output_type);
			}
			
		} else if ($plugin_addressing->fields["ip_free"]) {
			$row_num++;
			$item_num=1;
			if (!$ping) {
				echo plugin_addressing_displaySearchNewLine($output_type,"free");
				echo displaySearchItem($output_type,$ip,$item_num,$row_num);
				echo displaySearchItem($output_type," ",$item_num,$row_num);
			} else {
				if ($output_type==HTML_OUTPUT) glpi_flush();
					
				if (plugin_addressing_ping($system,$ip)) {
					$ping_response++;
					echo plugin_addressing_displaySearchNewLine($output_type,"ping_off");
					echo displaySearchItem($output_type,$ip,$item_num,$row_num);
					echo displaySearchItem($output_type,$LANGADDRESSING["reports"][31],$item_num,$row_num);
				} else {
					echo plugin_addressing_displaySearchNewLine($output_type,"ping_on");
					echo displaySearchItem($output_type,$ip,$item_num,$row_num);
					echo displaySearchItem($output_type,$LANGADDRESSING["reports"][32],$item_num,$row_num);				
				}	
			} 
			echo displaySearchItem($output_type," ",$item_num,$row_num);
			echo displaySearchItem($output_type," ",$item_num,$row_num);
			echo displaySearchItem($output_type," ",$item_num,$row_num);
			echo displaySearchItem($output_type," ",$item_num,$row_num);
			echo displaySearchEndLine($output_type);
		}		
	}

	// Display footer
	echo displaySearchFooter($output_type,$plugin_addressing->getTitle());
				
	return $ping_response;
}

?>