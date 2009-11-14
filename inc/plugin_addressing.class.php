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

if (!defined('GLPI_ROOT')){
	die("Sorry. You can't access directly to this file");
	}

class PluginAddressing extends CommonDBTM {

	function __construct() {
		$this->table="glpi_plugin_addressing";
		$this->type=PLUGIN_ADDRESSING_TYPE;
	}

  function getSearchOptions() {
    global $LANG;

      $tab = array();

      $tab['common'] = $LANG['plugin_addressing']['title'][1];

      $tab[1]['table']=$this->table;
      $tab[1]['field']='name';
      $tab[1]['linkfield']='name';
      $tab[1]['name']=$LANG['common'][16];
      $tab[1]['datatype']='itemlink';

      $tab[2]['table']='glpi_networks';
      $tab[2]['field']='name';
      $tab[2]['linkfield']='networks_id';
      $tab[2]['name']=$LANG['plugin_addressing']['setup'][24];

      $tab[3]['table']=$this->table;
      $tab[3]['field']='comment';
      $tab[3]['linkfield']='comment';
      $tab[3]['name']=$LANG['common'][25];
      $tab[3]['datatype']='text';

      $tab[4]['table']=$this->table;
      $tab[4]['field']='use_ping';
      $tab[4]['linkfield']='use_ping';
      $tab[4]['name']=$LANG['plugin_addressing']['reports'][30];
      $tab[4]['datatype']='bool';

      $tab[5]['table']=$this->table;
      $tab[5]['field']='generation_link';
      $tab[5]['linkfield']='';
      $tab[5]['name']=$LANG['plugin_addressing'][3];

      $tab[30]['table']=$this->table;
      $tab[30]['field']='id';
      $tab[30]['linkfield']='';
      $tab[30]['name']=$LANG['common'][2];

      $tab[80]['table']='glpi_entities';
      $tab[80]['field']='completename';
      $tab[80]['linkfield']='entities_id';
      $tab[80]['name']=$LANG['entity'][0];

      $tab[1000]['table']=$this->table;
      $tab[1000]['field']='begin_ip';
      $tab[1000]['linkfield']='';
      $tab[1000]['name']=$LANG['plugin_addressing']['reports'][38];

      $tab[1001]['table']=$this->table;
      $tab[1001]['field']='end_ip';
      $tab[1001]['linkfield']='';
      $tab[1001]['name']=$LANG['plugin_addressing']['reports'][39];

		return $tab;
   }

	function defineTabs($ID,$withtemplate) {
		global $LANG;
		
		$ong[1]=$LANG['title'][26];
		return $ong;
	}

	function getTitle() {
		global $LANG;

		return $LANG['plugin_addressing']['reports'][1]." ".$this->fields["begin_ip"]." ".
			  $LANG['plugin_addressing']['reports'][20]." ".$this->fields["end_ip"];
	}

	function compute() {
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

	function dropdownSubnet($entity) {
      global $DB;

      $rand=mt_rand();
      echo "<select name='_subnet' id='plugaddr_subnet' onChange='plugaddr_ChangeList();'>";
      echo "<option value=''>-----</option>";

      $sql="SELECT DISTINCT `subnet`, `netmask`
            FROM `glpi_networkports` " .
            "LEFT JOIN `glpi_computers` ON (`glpi_computers`.`id` = `glpi_networkports`.`items_id`) " .
            "WHERE `itemtype` = '".COMPUTER_TYPE."'
            AND `entities_id` = '".$entity."'
            AND `subnet` NOT IN ('','0.0.0.0','127.0.0.0')
            AND `netmask` NOT IN ('','0.0.0.0','255.255.255.255')" .
            getEntitiesRestrictRequest(" AND ","glpi_computers","entities_id",$entity) .
            "ORDER BY INET_ATON(`subnet`)";
      $result=array();
      $result[0]="-----";
      $res=$DB->query($sql);
      if ($res) while ($row=$DB->fetch_assoc($res)) {
         $val = $row["subnet"]."/".$row["netmask"];
         echo "<option value='$val'>$val</option>";
      }
      echo "</select>\n";
}

	function showForm ($target,$ID,$withtemplate='') {
		global $CFG_GLPI,$LANG;

		if (!plugin_addressing_haveRight("addressing","r")) return false;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
      // Create item
         $this->check(-1,'r');
         $this->getEmpty();
      }

      $this->showTabs($ID, $withtemplate,$_SESSION['glpi_tab']);
      $onsubmit="onSubmit='return plugaddr_Check(\"".$LANG['plugin_addressing']['reports'][37]."\")'";
      $this->showFormHeader($target,$ID,$withtemplate,2,$onsubmit);

      echo "<tr><td class='tab_bg_1 top'>";

      echo "<table cellpadding='2' cellspacing='2' border='0'>\n";

      echo "<tr><td>".$LANG['common'][16].": </td>";
      echo "<td>";
      autocompletionTextField("name",$this->table,"name",$this->fields["name"],20,$this->fields["entities_id"]);
      echo "</td></tr>";

      echo "<tr><td>".$LANG['plugin_addressing']['reports'][3]."</td>";
      echo "<td>";
      dropdownValue("glpi_networks", "networks_id", $this->fields["networks_id"]);
      echo "</td></tr>";

      echo "<tr><td>".$LANG['plugin_addressing']['reports'][36]."</td>";
      echo "<td>";
      $this->dropdownSubnet($ID>0 ? $this->fields["entities_id"] : $_SESSION["glpiactive_entity"]);
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
      echo "<td class='tab_bg_1 top'>";
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

      echo "<td class='tab_bg_1 top'>";

      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo $LANG['common'][25].":	</td></tr>";
      echo "<tr><td class='center'><textarea cols='35' rows='4' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr></table>";

      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($ID,$withtemplate,2);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

		return true;
	}

	function displaySearchNewLine($type,$odd=false) {
		$out="";
		switch ($type) {
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

	function display(&$result) {
		global $DB,$LANG,$CFG_GLPI,$INFOFORM_PAGES;

		$network=$this->fields["networks_id"];
		$ping=$this->fields["use_ping"];

		$PluginAddressingConfig=new PluginAddressingConfig();
		$PluginAddressingConfig->getFromDB('1');
		$system=$PluginAddressingConfig->fields["used_system"];

		// Set display type for export if define
		$output_type=HTML_OUTPUT;

		if (isset($_GET["display_type"]))
			$output_type=$_GET["display_type"];

		$ping_response=0;

		$nbcols=6;
		$parameters="id=";

		echo displaySearchHeader($output_type,1,$nbcols);
		echo $this->displaySearchNewLine($output_type);
		$header_num=1;

		echo displaySearchHeaderItem($output_type,$LANG['plugin_addressing']['reports'][2],$header_num);
		echo displaySearchHeaderItem($output_type,$LANG['plugin_addressing']['reports'][9],$header_num);
		echo displaySearchHeaderItem($output_type,$LANG['plugin_addressing']['reports'][14],$header_num);
		echo displaySearchHeaderItem($output_type,$LANG['plugin_addressing']['reports'][5],$header_num);
		echo displaySearchHeaderItem($output_type,$LANG['plugin_addressing']['reports'][8],$header_num);
		echo displaySearchHeaderItem($output_type,$LANG['plugin_addressing']['reports'][23],$header_num);
		// End Line for column headers
		echo displaySearchEndLine($output_type);
		$row_num=1;

		$ci=new CommonItem();
		$user = new User();

		foreach ($result as $num => $lines) {
			$ip=long2ip(substr($num,2));

			if (count($lines)) {
				if (count($lines)>1) {
					$disp = $this->fields["double_ip"];
				} else {
					$disp = $this->fields["alloted_ip"];
				}
				if ($disp) foreach ($lines as $line) {
					$row_num++;
					$item_num=1;
					$name=$line["dname"];
					$namep=$line["pname"];
					// IP
					echo $this->displaySearchNewLine($output_type,(count($lines)>1 ? "double" : $row_num%2));
					echo displaySearchItem($output_type,$ip,$item_num,$row_num);

					// Device
					$ci->setType($line["itemtype"]);
					if ($line["itemtype"] != NETWORKING_TYPE) {
						if (haveTypeRight($line["itemtype"], "r")) {
							$output_iddev = "<a href='".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$line["itemtype"]]."?id=".$line["on_device"]."'>".$name
								.(empty($name) || $_SESSION["glpiis_ids_visible"]?" (".$line["on_device"].")":"")."</a>";
						} else {
							$output_iddev = $name.(empty($name) || $_SESSION["glpiis_ids_visible"]?" (".$line["on_device"].")":"");
						}
					} else {
						if (haveTypeRight($line["itemtype"], "r")) {
							$output_iddev = "<a href='".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[$line["itemtype"]]."?id=".$line["on_device"]."'>".$namep." - ".$name
								.(empty($name) || $_SESSION["glpiis_ids_visible"]?" (".$line["on_device"].")":"")."</a>";
						} else {
							$output_iddev = $namep." - ".$name.(empty($name) || $_SESSION["glpiis_ids_visible"]?" (".$line["on_device"].")":"");
						}
					}
					echo displaySearchItem($output_type,$output_iddev,$item_num,$row_num);

					// User
					if ($line["users_id"] && $user->getFromDB($line["users_id"])) {
						$username=formatUserName($user->fields["id"],$user->fields["name"],$user->fields["realname"],$user->fields["firstname"]);

						if (haveTypeRight(USER_TYPE, "r")) {
							$output_iduser="<a href='".$CFG_GLPI["root_doc"]."/front/user.form.php?id=".$line["users_id"]."'>".$username."</a>";
						} else {
							$output_iduser=$username;
						}
						echo displaySearchItem($output_type,$output_iduser,$item_num,$row_num);
					} else {
						echo displaySearchItem($output_type," ",$item_num,$row_num);
					}

					// Mac
					if($line["id"]) {
						if (haveTypeRight($line["itemtype"], "r")) {
							$output_mac = "<a href='".$CFG_GLPI["root_doc"]."/front/networking.port.php?id=".$line["id"]."'>".$line["mac"]."</a>";
						} else {
							$output_mac = $line["mac"];
						}
						echo displaySearchItem($output_type,$output_mac,$item_num,$row_num);
					} else {
						echo displaySearchItem($output_type," ",$item_num,$row_num);
					}
					// Type
					echo displaySearchItem($output_type,$ci->getType(),$item_num,$row_num);

					// Reserved
					if ($this->fields["reserved_ip"] && strstr($line["pname"],"reserv")) {
						echo displaySearchItem($output_type,$LANG['plugin_addressing']['reports'][13],$item_num,$row_num);
					} else {
						echo displaySearchItem($output_type," ",$item_num,$row_num);
					}

					// End
					echo displaySearchEndLine($output_type);
				}

			} else if ($this->fields["free_ip"]) {
				$row_num++;
				$item_num=1;
				if (!$ping) {
					echo $this->displaySearchNewLine($output_type,"free");
					echo displaySearchItem($output_type,$ip,$item_num,$row_num);
					echo displaySearchItem($output_type," ",$item_num,$row_num);
				} else {
					if ($output_type==HTML_OUTPUT) glpi_flush();

					if ($this->ping($system,$ip)) {
						$ping_response++;
						echo $this->displaySearchNewLine($output_type,"ping_off");
						echo displaySearchItem($output_type,$ip,$item_num,$row_num);
						echo displaySearchItem($output_type,$LANG['plugin_addressing']['reports'][31],$item_num,$row_num);
					} else {
						echo $this->displaySearchNewLine($output_type,"ping_on");
						echo displaySearchItem($output_type,$ip,$item_num,$row_num);
						echo displaySearchItem($output_type,$LANG['plugin_addressing']['reports'][32],$item_num,$row_num);
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
		echo displaySearchFooter($output_type,$this->getTitle());

		return $ping_response;
	}
	
	function ping($system,$ip) {
    $list ='';
      switch ($system) {

      case 0:
      // linux ping
          exec("ping -c 1 -w 1 ".$ip, $list);
          $nb=count($list);
          if (isset($nb)) {
              for($i=0;$i<$nb;$i++) {
                  if(strpos($list[$i],"ttl=")>0) return true;
              }
          }
      break;

      case 1:
      //windows
          exec("ping.exe -n 1 -w 1 -i 4 ".$ip, $list);
          $nb=count($list);
          if (isset($nb)) {
              for($i=0;$i<$nb;$i++) {
                  if(strpos($list[$i],"TTL")>0) return true;
              }
          }
      break;

      case 2:
      //linux fping
      exec("fping -r1 -c1 -t100 ".$ip, $list);
          $nb=count($list);
          if (isset($nb)) {
              for($i=0;$i<$nb;$i++) {
                  if(strpos($list[$i],"bytes")>0) return true;
              }
          }
      break;

      case 3:
      // *BSD ping
          exec("ping -c 1 -W 1 ".$ip, $list);
          $nb=count($list);
          if (isset($nb)) {
              for($i=0;$i<$nb;$i++) {
                  if(strpos($list[$i],"ttl=")>0) return true;
              }
          }
      break;

      case 4:
      // MacOSX ping
          exec("ping -c 1 -t 1 ".$ip, $list);
          $nb=count($list);
          if (isset($nb)) {
              for($i=0;$i<$nb;$i++) {
                  if(strpos($list[$i],"ttl=")>0) return true;
              }
          }
      break;
      }
  }
}

class PluginAddressingReport extends CommonDBTM {

	function __construct() {
		$this->table="glpi_plugin_addressing";
		$this->type=PLUGIN_ADDRESSING_REPORT_TYPE;
	}
}

class PluginAddressingConfig extends CommonDBTM {

	function __construct() {
		$this->table="glpi_plugin_addressing_configs";
	}
}

?>