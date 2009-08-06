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

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

class PluginAddressing extends CommonDBTM {

	function __construct () {
		$this->table="glpi_plugin_addressing";
		$this->type=PLUGIN_ADDRESSING_TYPE;
	}
	
	function defineTabs($ID,$withtemplate){
		global $LANG;
		$ong[1]=$LANG['title'][26];
		return $ong;
	}
	
	function getTitle () {
		global $LANG;
		
		return $LANG['plugin_addressing']['reports'][1]." ".$this->fields["ipdeb"]." ".
			  $LANG['plugin_addressing']['reports'][20]." ".$this->fields["ipfin"];
	}
	
	function compute () {
		global $DB, $CFG_GLPI, $LINK_ID_TABLE;
		
		// sprintf to solve 32/64 bits issue
		$ipdeb=sprintf("%u", ip2long($this->fields["ipdeb"]));
		$ipfin=sprintf("%u", ip2long($this->fields["ipfin"]));
		
		if (!isset($_GET['export_all'])) {
			if (isset($_GET["start"])) {
				$ipdeb+=$_GET["start"];
			}
			if ($ipdeb > $ipfin) {
				$ipdeb = $ipfin;
			}
			if ($ipdeb+$_SESSION["glpilist_limit"]<=$ipfin) {
				$ipfin = $ipdeb+$_SESSION["glpilist_limit"]-1;
			}
		}
		
		$result=array();
		for ($ip=$ipdeb ; $ip<=$ipfin ; $ip++)
			$result["IP".$ip]=array();
	
		$sql = "SELECT 0 AS ID, ".NETWORKING_TYPE." AS device_type, `ID` AS on_device, `dev`.`name` AS dname, '' AS pname, `ifaddr`, `ifmac`, `FK_users`, INET_ATON(`ifaddr`) AS ipnum " .
				"FROM `glpi_networking`  dev " .
				"WHERE INET_ATON(`ifaddr`) >= '$ipdeb' AND INET_ATON(`ifaddr`) <= '$ipfin' AND `deleted` = 0 AND `is_template` = 0 " .
				getEntitiesRestrictRequest(" AND ","dev");
		if ($this->fields["network"]) 
			$sql .= " AND `network` = ".$this->fields["network"];
			
		foreach ($CFG_GLPI["netport_types"] as $type) {
			$sql .= " UNION SELECT `port`.`ID`, `device_type`, `on_device`, `dev`.`name` AS dname, `port`.`name` AS pname, `port`.`ifaddr`, `port`.`ifmac`, `FK_users`, INET_ATON(`port`.`ifaddr`) AS ipnum " .
					"FROM `glpi_networking_ports` port, `" . $LINK_ID_TABLE[$type] . "` dev " .
					"WHERE `device_type` = '$type' AND `port`.`on_device` = `dev`.`ID` AND INET_ATON(`port`.`ifaddr`) >= '$ipdeb' AND INET_ATON(`port`.`ifaddr`) <= '$ipfin' AND `deleted` = 0 AND `is_template` = 0 " .
					getEntitiesRestrictRequest(" AND ", "dev");
			if ($this->fields["network"] && $type!=PERIPHERAL_TYPE && $type!=PHONE_TYPE) 
				$sql .= " AND `network`= ".$this->fields["network"];
		}
		$res=$DB->query($sql);
		if ($res) while ($row=$DB->fetch_assoc($res)) {
			$result["IP".$row["ipnum"]][]=$row;
		}		
		
		return $result;
	}
	
	function showForm ($target,$ID,$withtemplate='') {

		GLOBAL $CFG_GLPI, $LANG;

		if (!plugin_addressing_haveRight("addressing","r")) return false;

		$con_spotted=false;

		if (empty($ID) ||$ID==-1) {

			if($this->getEmpty()) $con_spotted = true;
		} else {
			if($this->getfromDB($ID)&&haveAccessToEntity($this->fields["FK_entities"])) $con_spotted = true;
		}

		if ($con_spotted){
			
			$this->showTabs($ID, $withtemplate,$_SESSION['glpi_tab']);
	
			echo "<form method='post' name=form action='$target' onSubmit='return plugaddr_Check(\"".$LANG['plugin_addressing']['reports'][37]."\")'>";
			echo "<input type='hidden' name='FK_entities' value='".$this->fields["FK_entities"]."'>";
			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			$this->showFormHeader($ID,'',2);

			echo "<tr><td class='tab_bg_1' valign='top'>";

			echo "<table cellpadding='2' cellspacing='2' border='0'>\n";

			echo "<tr><td>".$LANG['common'][16].": </td>";
			echo "<td>";
			autocompletionTextField("name","glpi_plugin_addressing","name",$this->fields["name"],20,$this->fields["FK_entities"]);		
			echo "</td></tr>";
			
			echo "<tr><td>".$LANG['plugin_addressing']['reports'][3]."</td>";
			echo "<td>";
			dropdownValue("glpi_dropdown_network", "network", $this->fields["network"]);
			echo "</td></tr>";
			
			echo "<tr><td>".$LANG['plugin_addressing']['reports'][36]."</td>";
			echo "<td>";
			plugin_addressing_dropdownSubnet($ID>0 ? $this->fields["FK_entities"] : $_SESSION["glpiactive_entity"]);
			echo "</td></tr>";

			echo "<tr><td>".$LANG['plugin_addressing']['reports'][38]." : </td>"; // Subnet
			echo "<td>";
			echo "<input type='text' id='plugaddr_ipdeb0' value='' name='_ipdeb0' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
			echo "<input type='text' id='plugaddr_ipdeb1' value='' name='_ipdeb1' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
			echo "<input type='text' id='plugaddr_ipdeb2' value='' name='_ipdeb2' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
			echo "<input type='text' id='plugaddr_ipdeb3' value='' name='_ipdeb3' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>";
			echo "</td></tr>";

			echo "<tr><td>".$LANG['plugin_addressing']['reports'][39]." : </td>"; // Mask
			echo "<td>";
			echo "<input type='text' id='plugaddr_ipfin0' value='' name='_ipfin0' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
			echo "<input type='text' id='plugaddr_ipfin1' value='' name='_ipfin1' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
			echo "<input type='text' id='plugaddr_ipfin2' value='' name='_ipfin2' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
			echo "<input type='text' id='plugaddr_ipfin3' value='' name='_ipfin3' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>";
			echo "</td></tr>";
			
			echo "<tr><td>".$LANG['plugin_addressing']['reports'][1]." : </td>"; // Mask
			echo "<td>";
			echo "<input type='hidden' id='plugaddr_ipdeb' value='".$this->fields["ipdeb"]."' name='ipdeb'>";
			echo "<input type='hidden' id='plugaddr_ipfin' value='".$this->fields["ipfin"]."' name='ipfin'>";
			echo "<div id='plugaddr_range'>-</div>";
			echo "</td></tr>";

			if ($ID>0) {
				echo "<tr><td><script language='JavaScript' type='text/javascript'>plugaddr_Init(\"".$LANG['plugin_addressing']['reports'][37]."\");</script></td></tr>";
			}
			
			echo "</table>";
			echo "</td>";	
			echo "<td class='tab_bg_1' valign='top'>";
			echo "<table cellpadding='2' cellspacing='2' border='0'>";
			
			$PluginAddressingDisplay=new PluginAddressingDisplay();
			$PluginAddressingDisplay->getFromDB('1');
			
			if ($PluginAddressingDisplay->fields["ip_alloted"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['setup'][11]."</td><td>";
				dropdownYesNo('ip_alloted',$this->fields["ip_alloted"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='ip_alloted' value='0''>\n";
			}
			if ($PluginAddressingDisplay->fields["ip_free"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['setup'][12]."</td><td>";
				dropdownYesNo('ip_free',$this->fields["ip_free"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='ip_free' value='0''>\n";
			}
			if ($PluginAddressingDisplay->fields["ip_double"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['setup'][13]."</td><td>";
				dropdownYesNo('ip_double',$this->fields["ip_double"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='ip_double' value='0''>\n";
			}
			if ($PluginAddressingDisplay->fields["ip_reserved"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['setup'][14]."</td><td>";
				dropdownYesNo('ip_reserved',$this->fields["ip_reserved"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='ip_reserved' value='0''>\n";
			}		
			
			if ($PluginAddressingDisplay->fields["ping"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['reports'][30].": </td><td>";
				dropdownYesNo('ping',$this->fields["ping"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='ping' value=\"0\">\n";
			}
			echo "</table>";

			echo "</td>";	

			echo "<td class='tab_bg_1' valign='top'>";

			echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
			echo $LANG['common'][25].":	</td></tr>";
			echo "<tr><td align='center'><textarea cols='35' rows='4' name='comments' >".$this->fields["comments"]."</textarea>";
			echo "</td></tr></table>";

			echo "</td>";
			echo "</tr>";

			if (plugin_addressing_haveRight("addressing","w")){
				if ($ID=="") {
					
					echo "<tr>";
					echo "<td class='tab_bg_2' valign='top' colspan='3'>";
					echo "<div align='center'><input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" class='submit'></div>";
					echo "</td>";
					echo "</tr>";
	
				} else {
	
					echo "<tr>";
					echo "<td class='tab_bg_2'  colspan='3' valign='top'><div align='center'>";
	
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >";
					if ($this->fields["deleted"]=='0')
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"".$LANG['buttons'][6]."\" class='submit'></div>";
					else {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='restore' value=\"".$LANG['buttons'][21]."\" class='submit'>";

						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"".$LANG['buttons'][22]."\" class='submit'></div>";
					}
					echo "</td>";
					echo "</tr>";
				}
			}
			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";

		} else {
			echo "<div align='center'><b>".$LANG['plugin_addressing'][1]."</b></div>";
			return false;

		}
		return true;
	}
}
class PluginAddressingDisplay extends CommonDBTM {

	function __construct () {
		$this->table="glpi_plugin_addressing_display";
	}	
}

class PluginAddressingProfile extends CommonDBTM {

	function __construct () {
		$this->table="glpi_plugin_addressing_profiles";
		$this->type=-1;
	}
	
	//if profile deleted
	function cleanProfiles($ID) {
	
		$this->delete(array('ID'=>$ID));
	}
	
	//profiles modification
	function showForm($target,$ID){
		global $LANG;

		if (!haveRight("profile","r")) return false;
		$canedit=haveRight("profile","w");
		if ($ID){
			$this->getFromDB($ID);
		}
		echo "<form action='".$target."' method='post'>";
		echo "<table class='tab_cadre_fixe'>";

		echo "<tr><th colspan='2' align='center'><strong>".$LANG['plugin_addressing']['profile'][0]." ".$this->fields["name"]."</strong></th></tr>";

		echo "<tr class='tab_bg_2'>";
		echo "<td>".$LANG['plugin_addressing']['profile'][3].":</td><td>";
		dropdownNoneReadWrite("addressing",$this->fields["addressing"],1,1,1);
		echo "</td>";
		echo "</tr>";

		if ($canedit){
			echo "<tr class='tab_bg_1'>";
			echo "<td align='center' colspan='2'>";
			echo "<input type='hidden' name='ID' value=$ID>";
			echo "<input type='submit' name='update_user_profile' value=\"".$LANG['buttons'][7]."\" class='submit'>";
			echo "</td></tr>";
		}
		echo "</table></form>";

	}
}

?>