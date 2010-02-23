<?php
/*
 * @version $Id: HEADER 1 2010-02-24 00:12 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

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
// Purpose of file: plugin addressing v1.8.0 - GLPI 0.78
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginAddressingReport extends CommonDBTM {

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
				if ($odd) {
					switch ($odd) {
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

   function display(&$result, $PluginAddressingAddressing) {
		global $DB,$LANG,$CFG_GLPI;

		$network=$PluginAddressingAddressing->fields["networks_id"];
		$ping=$PluginAddressingAddressing->fields["use_ping"];


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

		echo Search::showHeader($output_type,1,$nbcols,1);
		echo $this->displaySearchNewLine($output_type);
		$header_num=1;

		echo Search::showHeaderItem($output_type,$LANG['plugin_addressing']['reports'][2],$header_num);
		echo Search::showHeaderItem($output_type,$LANG['plugin_addressing']['reports'][9],$header_num);
		echo Search::showHeaderItem($output_type,$LANG['plugin_addressing']['reports'][14],$header_num);
		echo Search::showHeaderItem($output_type,$LANG['plugin_addressing']['reports'][5],$header_num);
		echo Search::showHeaderItem($output_type,$LANG['plugin_addressing']['reports'][8],$header_num);
		echo Search::showHeaderItem($output_type,$LANG['plugin_addressing']['reports'][23],$header_num);
		// End Line for column headers
		echo Search::showEndLine($output_type);
		$row_num=1;

		$user = new User();

		foreach ($result as $num => $lines) {
			$ip=long2ip(substr($num,2));

			if (count($lines)) {
				if (count($lines)>1) {
					$disp = $PluginAddressingAddressing->fields["double_ip"];
				} else {
					$disp = $PluginAddressingAddressing->fields["alloted_ip"];
				}
				if ($disp) foreach ($lines as $line) {
					$row_num++;
					$item_num=1;
					$name=$line["dname"];
					$namep=$line["pname"];
					// IP
					echo $this->displaySearchNewLine($output_type,(count($lines)>1 ? "double" : $row_num%2));
					echo Search::showItem($output_type,$ip,$item_num,$row_num);

					// Device
					$item = new $line["itemtype"]();
					$link=getItemTypeFormURL($line["itemtype"]);
					if ($line["itemtype"] != 'NetworkEquipment') {
						if ($item->canView()) {
							$output_iddev = "<a href='".$link."?id=".$line["on_device"]."'>".$name
								.(empty($name) || $_SESSION["glpiis_ids_visible"]?" (".$line["on_device"].")":"")."</a>";
						} else {
							$output_iddev = $name.(empty($name) || $_SESSION["glpiis_ids_visible"]?" (".$line["on_device"].")":"");
						}
					} else {
						if ($item->canView()) {
                     if (empty($namep)) $linkp=''; else $linkp=$namep." - ";
							$output_iddev = "<a href='".$link."?id=".$line["on_device"]."'>".$linkp.$name
								.(empty($name) || $_SESSION["glpiis_ids_visible"]?" (".$line["on_device"].")":"")."</a>";
						} else {
							$output_iddev = $namep." - ".$name.(empty($name) || $_SESSION["glpiis_ids_visible"]?" (".$line["on_device"].")":"");
						}
					}
					echo Search::showItem($output_type,$output_iddev,$item_num,$row_num);

					// User
					if ($line["users_id"] && $user->getFromDB($line["users_id"])) {
						$username=formatUserName($user->fields["id"],$user->fields["name"],$user->fields["realname"],$user->fields["firstname"]);

						if ($user->canView()) {
							$output_iduser="<a href='".$CFG_GLPI["root_doc"]."/front/user.form.php?id=".$line["users_id"]."'>".$username."</a>";
						} else {
							$output_iduser=$username;
						}
						echo Search::showItem($output_type,$output_iduser,$item_num,$row_num);
					} else {
						echo Search::showItem($output_type," ",$item_num,$row_num);
					}

					// Mac
					if ($line["id"]) {
						if ($item->canView()) {
							$output_mac = "<a href='".$CFG_GLPI["root_doc"]."/front/networkport.form.php?id=".$line["id"]."'>".$line["mac"]."</a>";
						} else {
							$output_mac = $line["mac"];
						}
						echo Search::showItem($output_type,$output_mac,$item_num,$row_num);
					} else {
						echo Search::showItem($output_type," ",$item_num,$row_num);
					}
					// Type
					echo Search::showItem($output_type,$item->getTypeName(),$item_num,$row_num);

					// Reserved
					if ($PluginAddressingAddressing->fields["reserved_ip"] && strstr($line["pname"],"reserv")) {
						echo Search::showItem($output_type,$LANG['plugin_addressing']['reports'][13],$item_num,$row_num);
					} else {
						echo Search::showItem($output_type," ",$item_num,$row_num);
					}

					// End
					echo Search::showEndLine($output_type);
				}

			} else if ($PluginAddressingAddressing->fields["free_ip"]) {
				$row_num++;
				$item_num=1;
				if (!$ping) {
					echo $this->displaySearchNewLine($output_type,"free");
					echo Search::showItem($output_type,$ip,$item_num,$row_num);
					echo Search::showItem($output_type," ",$item_num,$row_num);
				} else {
					if ($output_type==HTML_OUTPUT) glpi_flush();

					if ($this->ping($system,$ip)) {
						$ping_response++;
						echo $this->displaySearchNewLine($output_type,"ping_off");
						echo Search::showItem($output_type,$ip,$item_num,$row_num);
						echo Search::showItem($output_type,$LANG['plugin_addressing']['reports'][31],$item_num,$row_num);
					} else {
						echo $this->displaySearchNewLine($output_type,"ping_on");
						echo Search::showItem($output_type,$ip,$item_num,$row_num);
						echo Search::showItem($output_type,$LANG['plugin_addressing']['reports'][32],$item_num,$row_num);
					}
				}
				echo Search::showItem($output_type," ",$item_num,$row_num);
				echo Search::showItem($output_type," ",$item_num,$row_num);
				echo Search::showItem($output_type," ",$item_num,$row_num);
				echo Search::showItem($output_type," ",$item_num,$row_num);
				echo Search::showEndLine($output_type);
			}
		}

		// Display footer
		echo Search::showFooter($output_type,$PluginAddressingAddressing->getTitle());

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
               if (strpos($list[$i],"ttl=")>0) return true;
            }
         }
         break;
      case 1:
         //windows
         exec("ping.exe -n 1 -w 1 -i 4 ".$ip, $list);
         $nb=count($list);
         if (isset($nb)) {
            for($i=0;$i<$nb;$i++) {
               if (strpos($list[$i],"TTL")>0) return true;
            }
         }
         break;
      case 2:
         //linux fping
         exec("fping -r1 -c1 -t100 ".$ip, $list);
         $nb=count($list);
         if (isset($nb)) {
            for($i=0;$i<$nb;$i++) {
               if (strpos($list[$i],"bytes")>0) return true;
            }
         }
         break;
      case 3:
         // *BSD ping
         exec("ping -c 1 -W 1 ".$ip, $list);
         $nb=count($list);
         if (isset($nb)) {
            for($i=0;$i<$nb;$i++) {
               if (strpos($list[$i],"ttl=")>0) return true;
            }
         }
         break;
      case 4:
         // MacOSX ping
         exec("ping -c 1 -t 1 ".$ip, $list);
         $nb=count($list);
         if (isset($nb)) {
            for($i=0;$i<$nb;$i++) {
               if (strpos($list[$i],"ttl=")>0) return true;
            }
         }
         break;
      }
   }
}

?>