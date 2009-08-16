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

		return $LANG['plugin_addressing']['reports'][1]." ".$this->fields["begin_ip"]." ".
			  $LANG['plugin_addressing']['reports'][20]." ".$this->fields["end_ip"];
	}

	function compute () {
		global $DB, $CFG_GLPI, $LINK_ID_TABLE;

		// sprintf to solve 32/64 bits issue
		$ipdeb=sprintf("%u", ip2long($this->fields["begin_ip"]));
		$ipfin=sprintf("%u", ip2long($this->fields["end_ip"]));

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

		$sql = "SELECT 0 AS id, ".NETWORKING_TYPE." AS itemtype, `id` AS on_device, `dev`.`name` AS dname, '' AS pname, `ip`, `mac`, `users_id`, INET_ATON(`ip`) AS ipnum " .
				"FROM `glpi_networkequipments`  dev " .
				"WHERE INET_ATON(`ip`) >= '$ipdeb' AND INET_ATON(`ip`) <= '$ipfin' AND `is_deleted` = 0 AND `is_template` = 0 " .
				getEntitiesRestrictRequest(" AND ","dev");
		if ($this->fields["networks_id"])
			$sql .= " AND `networks_id` = ".$this->fields["networks_id"];

		foreach ($CFG_GLPI["netport_types"] as $type) {
			$sql .= " UNION SELECT `port`.`id`, `itemtype`, `items_id`, `dev`.`name` AS dname, `port`.`name` AS pname, `port`.`ip`, `port`.`mac`, `users_id`, INET_ATON(`port`.`ip`) AS ipnum " .
					"FROM `glpi_networkports` port, `" . $LINK_ID_TABLE[$type] . "` dev " .
					"WHERE `itemtype` = '$type' AND `port`.`items_id` = `dev`.`id` AND INET_ATON(`port`.`ip`) >= '$ipdeb' AND INET_ATON(`port`.`ip`) <= '$ipfin' AND `is_deleted` = 0 AND `is_template` = 0 " .
					getEntitiesRestrictRequest(" AND ", "dev");
			if ($this->fields["networks_id"] && $type!=PERIPHERAL_TYPE && $type!=PHONE_TYPE)
				$sql .= " AND `networks_id`= ".$this->fields["networks_id"];
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
			if($this->getfromDB($ID)&&haveAccessToEntity($this->fields["entities_id"])) $con_spotted = true;
		}

		if ($con_spotted){

			$this->showTabs($ID, $withtemplate,$_SESSION['glpi_tab']);
			$onsubmit="onSubmit='return plugaddr_Check(\"".$LANG['plugin_addressing']['reports'][37]."\")'";
			$this->showFormHeader($target,$ID,$withtemplate,2,$onsubmit);

			echo "<tr><td class='tab_bg_1' valign='top'>";

			echo "<table cellpadding='2' cellspacing='2' border='0'>\n";

			echo "<tr><td>".$LANG['common'][16].": </td>";
			echo "<td>";
			autocompletionTextField("name","glpi_plugin_addressing","name",$this->fields["name"],20,$this->fields["entities_id"]);
			echo "</td></tr>";

			echo "<tr><td>".$LANG['plugin_addressing']['reports'][3]."</td>";
			echo "<td>";
			dropdownValue("glpi_networks", "networks_id", $this->fields["networks_id"]);
			echo "</td></tr>";

			echo "<tr><td>".$LANG['plugin_addressing']['reports'][36]."</td>";
			echo "<td>";
			plugin_addressing_dropdownSubnet($ID>0 ? $this->fields["entities_id"] : $_SESSION["glpiactive_entity"]);
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
			echo "<input type='hidden' id='plugaddr_ipdeb' value='".$this->fields["begin_ip"]."' name='begin_ip'>";
			echo "<input type='hidden' id='plugaddr_ipfin' value='".$this->fields["end_ip"]."' name='end_ip'>";
			echo "<div id='plugaddr_range'>-</div>";
			echo "</td></tr>";

			if ($ID>0) {
				echo "<tr><td><script language='JavaScript' type='text/javascript'>plugaddr_Init(\"".$LANG['plugin_addressing']['reports'][37]."\");</script></td></tr>";
			}

			echo "</table>";
			echo "</td>";
			echo "<td class='tab_bg_1' valign='top'>";
			echo "<table cellpadding='2' cellspacing='2' border='0'>";

			$PluginAddressingConfig=new PluginAddressingConfig();
			$PluginAddressingConfig->getFromDB('1');

			if ($PluginAddressingConfig->fields["alloted_ip"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['setup'][11]."</td><td>";
				dropdownYesNo('alloted_ip',$this->fields["alloted_ip"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='alloted_ip' value='0''>\n";
			}
			if ($PluginAddressingConfig->fields["free_ip"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['setup'][12]."</td><td>";
				dropdownYesNo('free_ip',$this->fields["free_ip"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='free_ip' value='0''>\n";
			}
			if ($PluginAddressingConfig->fields["double_ip"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['setup'][13]."</td><td>";
				dropdownYesNo('double_ip',$this->fields["double_ip"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='double_ip' value='0''>\n";
			}
			if ($PluginAddressingConfig->fields["reserved_ip"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['setup'][14]."</td><td>";
				dropdownYesNo('reserved_ip',$this->fields["reserved_ip"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='reserved_ip' value='0''>\n";
			}

			if ($PluginAddressingConfig->fields["use_ping"]) {
				echo "<tr><td>".$LANG['plugin_addressing']['reports'][30].": </td><td>";
				dropdownYesNo('use_ping',$this->fields["use_ping"]);
				echo "</td></tr>";
			} else {
				echo "<input type='hidden' name='use_ping' value=\"0\">\n";
			}
			echo "</table>";

			echo "</td>";

			echo "<td class='tab_bg_1' valign='top'>";

			echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
			echo $LANG['common'][25].":	</td></tr>";
			echo "<tr><td align='center'><textarea cols='35' rows='4' name='comment' >".$this->fields["comment"]."</textarea>";
			echo "</td></tr></table>";

			echo "</td>";
			echo "</tr>";

			$this->showFormButtons($ID,$withtemplate,2);
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";

		} else {
			echo "<div align='center'><b>".$LANG['plugin_addressing'][1]."</b></div>";
			return false;

		}
		return true;
	}
}
class PluginAddressingConfig extends CommonDBTM {

	function __construct () {
		$this->table="glpi_plugin_addressing_configs";
	}
}

class PluginAddressingProfile extends CommonDBTM {

	function __construct () {
		$this->table="glpi_plugin_addressing_profiles";
		$this->type=-1;
	}

	//if profile deleted
	function cleanProfiles($ID) {

		$this->delete(array('id'=>$ID));
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
			echo "<input type='hidden' name='id' value=$ID>";
			echo "<input type='submit' name='update_user_profile' value=\"".$LANG['buttons'][7]."\" class='submit'>";
			echo "</td></tr>";
		}
		echo "</table></form>";

	}
}

?>