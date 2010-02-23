<?php
/*
 * @version $Id: H2009EADER 1 2009-09-21 14:58 Tsmr $
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

class PluginAddressingAddressing extends CommonDBTM {

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_addressing']['title'][1];
   }

   function canCreate() {
      return plugin_addressing_haveRight('addressing', 'w');
   }

   function canView() {
      return plugin_addressing_haveRight('addressing', 'r');
   }

   function getSearchOptions() {
      global $LANG;

      $tab = array();

      $tab['common'] = $LANG['plugin_addressing']['title'][1];

      $tab[1]['table']=$this->getTable();
      $tab[1]['field']='name';
      $tab[1]['linkfield']='name';
      $tab[1]['name']=$LANG['common'][16];
      $tab[1]['datatype']='itemlink';

      $tab[2]['table']='glpi_networks';
      $tab[2]['field']='name';
      $tab[2]['linkfield']='networks_id';
      $tab[2]['name']=$LANG['plugin_addressing']['setup'][24];

      $tab[3]['table']=$this->getTable();
      $tab[3]['field']='comment';
      $tab[3]['linkfield']='comment';
      $tab[3]['name']=$LANG['common'][25];
      $tab[3]['datatype']='text';

      $tab[4]['table']=$this->getTable();
      $tab[4]['field']='use_ping';
      $tab[4]['linkfield']='use_ping';
      $tab[4]['name']=$LANG['plugin_addressing']['reports'][30];
      $tab[4]['datatype']='bool';

      $tab[30]['table']=$this->getTable();
      $tab[30]['field']='id';
      $tab[30]['linkfield']='';
      $tab[30]['name']=$LANG['common'][2];

      $tab[80]['table']='glpi_entities';
      $tab[80]['field']='completename';
      $tab[80]['linkfield']='entities_id';
      $tab[80]['name']=$LANG['entity'][0];

      $tab[1000]['table']=$this->getTable();
      $tab[1000]['field']='begin_ip';
      $tab[1000]['linkfield']='';
      $tab[1000]['name']=$LANG['plugin_addressing']['reports'][38];

      $tab[1001]['table']=$this->getTable();
      $tab[1001]['field']='end_ip';
      $tab[1001]['linkfield']='';
      $tab[1001]['name']=$LANG['plugin_addressing']['reports'][39];

		return $tab;
   }

	function defineTabs($options=array()) {
		global $LANG;

		$ong[1]=$LANG['title'][26];
		return $ong;
	}

	function getTitle() {
		global $LANG;

		return $LANG['plugin_addressing']['reports'][1]." ".$this->fields["begin_ip"]." ".
			  $LANG['plugin_addressing']['reports'][20]." ".$this->fields["end_ip"];
	}

	function dropdownSubnet($entity) {
      global $DB;

      $rand=mt_rand();
      echo "<select name='_subnet' id='plugaddr_subnet' onChange='plugaddr_ChangeList();'>";
      echo "<option value=''>-----</option>";

      $sql="SELECT DISTINCT `subnet`, `netmask`
            FROM `glpi_networkports` " .
            "LEFT JOIN `glpi_computers` ON (`glpi_computers`.`id` = `glpi_networkports`.`items_id`) " .
            "WHERE `itemtype` = 'Computer'
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

	function showForm ($ID, $options=array()) {
		global $CFG_GLPI,$LANG;

		if (!plugin_addressing_haveRight("addressing","r")) return false;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
      // Create item
         $this->check(-1,'r');
         $this->getEmpty();
      }

      $this->showTabs($options);
      $options['formoptions'] = "onSubmit='return plugaddr_Check(\"".$LANG['plugin_addressing']['reports'][37]."\")'";
      $this->showFormHeader($options);

      $PluginAddressingConfig=new PluginAddressingConfig();
      $PluginAddressingConfig->getFromDB('1');

      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['common'][16].": </td>";
      echo "<td>";
      autocompletionTextField($this,"name");
      echo "</td>";

      if ($PluginAddressingConfig->fields["alloted_ip"]) {
         echo "<td>".$LANG['plugin_addressing']['setup'][11]."</td><td>";
         Dropdown::showYesNo('alloted_ip',$this->fields["alloted_ip"]);
         echo "</td>";
      } else {
         echo "<td><input type='hidden' name='alloted_ip' value='0''></td><td></td>";
      }

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['plugin_addressing']['reports'][3]."</td>";
      echo "<td>";
      Dropdown::show('Network', array('name' => "networks_id",'value' => $this->fields["networks_id"]));
      echo "</td>";

      if ($PluginAddressingConfig->fields["free_ip"]) {
         echo "<td>".$LANG['plugin_addressing']['setup'][12]."</td><td>";
         Dropdown::showYesNo('free_ip',$this->fields["free_ip"]);
         echo "</td>";
      } else {
         echo "<td><input type='hidden' name='free_ip' value='0''></td><td></td>";
      }

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['plugin_addressing']['reports'][36]."</td>";
      echo "<td>";
      $this->dropdownSubnet($ID>0 ? $this->fields["entities_id"] : $_SESSION["glpiactive_entity"]);
      echo "</td>";

      if ($PluginAddressingConfig->fields["double_ip"]) {
         echo "<td>".$LANG['plugin_addressing']['setup'][13]."</td><td>";
         Dropdown::showYesNo('double_ip',$this->fields["double_ip"]);
         echo "</td>";
      } else {
         echo "<td><input type='hidden' name='double_ip' value='0''></td><td></td>";
      }

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['plugin_addressing']['reports'][38]." : </td>"; // Subnet
      echo "<td>";
      echo "<input type='text' id='plugaddr_ipdeb0' value='' name='_ipdeb0' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
      echo "<input type='text' id='plugaddr_ipdeb1' value='' name='_ipdeb1' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
      echo "<input type='text' id='plugaddr_ipdeb2' value='' name='_ipdeb2' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
      echo "<input type='text' id='plugaddr_ipdeb3' value='' name='_ipdeb3' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>";
      echo "</td>";

      if ($PluginAddressingConfig->fields["reserved_ip"]) {
         echo "<td>".$LANG['plugin_addressing']['setup'][14]."</td><td>";
         Dropdown::showYesNo('reserved_ip',$this->fields["reserved_ip"]);
         echo "</td>";
      } else {
         echo "<td><input type='hidden' name='reserved_ip' value='0''></td><td></td>";
      }

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['plugin_addressing']['reports'][39]." : </td>"; // Mask
      echo "<td>";
      echo "<input type='text' id='plugaddr_ipfin0' value='' name='_ipfin0' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
      echo "<input type='text' id='plugaddr_ipfin1' value='' name='_ipfin1' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
      echo "<input type='text' id='plugaddr_ipfin2' value='' name='_ipfin2' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>.";
      echo "<input type='text' id='plugaddr_ipfin3' value='' name='_ipfin3' size='3' onChange='plugaddr_ChangeNumber(\"".$LANG['plugin_addressing']['reports'][37]."\");'>";
      echo "</td>";

      if ($PluginAddressingConfig->fields["use_ping"]) {
         echo "<td>".$LANG['plugin_addressing']['reports'][30].": </td><td>";
         Dropdown::showYesNo('use_ping',$this->fields["use_ping"]);
         echo "</td>";
      } else {
         echo "<td><input type='hidden' name='use_ping' value='0''></td><td></td>";
      }

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".$LANG['plugin_addressing']['reports'][1]." : </td>"; // Mask
      echo "<td>";
      echo "<input type='hidden' id='plugaddr_ipdeb' value='".$this->fields["begin_ip"]."' name='begin_ip'>";
      echo "<input type='hidden' id='plugaddr_ipfin' value='".$this->fields["end_ip"]."' name='end_ip'>";
      echo "<div id='plugaddr_range'>-</div>";
      if ($ID>0) {
         echo "<script language='JavaScript' type='text/javascript'>plugaddr_Init(\"".$LANG['plugin_addressing']['reports'][37]."\");</script>";
      }
      echo "</td>";
      echo "<td></td>";
      echo "<td></td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td colspan = '4'>";
      echo "<table cellpadding='2' cellspacing='2' border='0'><tr><td>";
      echo $LANG['common'][25].": </td></tr>";
      echo "<tr><td class='center'><textarea cols='125' rows='3' name='comment'>".$this->fields["comment"]."</textarea>";
      echo "</td></tr></table>";
      echo "</td>";

      $this->showFormButtons($options);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

		return true;
	}

	function linkToExport($ID) {
      global $LANG;

      echo "<div align='center'>";
      echo "<a href='./report.form.php?id=".$ID."&export=true'>".$LANG['buttons'][31]."</a>";
      echo "</div>";
   }

	function compute($start) {
		global $DB, $CFG_GLPI;

		// sprintf to solve 32/64 bits issue
		$ipdeb=sprintf("%u", ip2long($this->fields["begin_ip"]));
		$ipfin=sprintf("%u", ip2long($this->fields["end_ip"]));

		if (!isset($_GET["export_all"])) {
			if (isset($start)) {
				$ipdeb+=$start;
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

		$sql = "SELECT 0 AS id, 'NetworkEquipment' AS itemtype, `id` AS on_device, `dev`.`name` AS dname, '' AS pname, `ip`, `mac`, `users_id`, INET_ATON(`ip`) AS ipnum " .
				"FROM `glpi_networkequipments`  dev " .
				"WHERE INET_ATON(`ip`) >= '$ipdeb' AND INET_ATON(`ip`) <= '$ipfin' AND `is_deleted` = 0 AND `is_template` = 0 " .
				getEntitiesRestrictRequest(" AND ","dev");
		if ($this->fields["networks_id"])
			$sql .= " AND `networks_id` = ".$this->fields["networks_id"];

		foreach ($CFG_GLPI["netport_types"] as $type) {
         $itemtable=getTableForItemType($type);
			$sql .= " UNION SELECT `port`.`id`, `itemtype`, `items_id`, `dev`.`name` AS dname, `port`.`name` AS pname, `port`.`ip`, `port`.`mac`, `users_id`, INET_ATON(`port`.`ip`) AS ipnum " .
					"FROM `glpi_networkports` port, `" . $itemtable . "` dev " .
					"WHERE `itemtype` = '$type' AND `port`.`items_id` = `dev`.`id` AND INET_ATON(`port`.`ip`) >= '$ipdeb' AND INET_ATON(`port`.`ip`) <= '$ipfin' AND `is_deleted` = 0 AND `is_template` = 0 " .
					getEntitiesRestrictRequest(" AND ", "dev");
			if ($this->fields["networks_id"] && $type!='Peripheral' && $type!='Phone')
				$sql .= " AND `networks_id`= ".$this->fields["networks_id"];
		}
		$res=$DB->query($sql);
		if ($res) while ($row=$DB->fetch_assoc($res)) {
			$result["IP".$row["ipnum"]][]=$row;
		}

		return $result;
	}

	function showReport($params) {
      global $CFG_GLPI,$LANG;

      $PluginAddressingReport=new PluginAddressingReport;

      // Default values of parameters
      $default_values["start"]=0;
      $default_values["id"]=0;
      $default_values["export"]=false;

      foreach ($default_values as $key => $val) {
         if (isset($params[$key])) {
            $$key=$params[$key];
         } else {
            $$key=$default_values[$key];
         }
      }

      if ($this->getFromDB($id)) {

         $result=$this->compute($start);
         //echo "<pre>"; print_r($result);	echo "</pre>";

         $nbipf=0;	// ip libres
         $nbipr=0;	// ip r�serv�es
         $nbipt=0;	// ip trouv�es
         $nbipd=0;	// doublons

         foreach ($result as $ip => $lines) {
            if (count($lines)) {
               if (count($lines)>1) {
                  $nbipd++;
               }
               if ((isset($lines[0]['pname']) && strstr($lines[0]['pname'],"reserv")))
                  $nbipr++;
               $nbipt++;
            } else {
               $nbipf++;
            }
         }

         ////title

         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2 left'>";
         echo "<td>";
         if ($this->fields['free_ip']) {
            echo $LANG['plugin_addressing']['reports'][26]." : ".$nbipf."<br>" ;
         }
         if ($this->fields['reserved_ip']) {
            echo $LANG['plugin_addressing']['reports'][27]." : ".$nbipr."<br>" ;
         }
         if ($this->fields['alloted_ip']) {
            echo $LANG['plugin_addressing']['reports'][28]." : ".$nbipt."<br>" ;
         }
         if ($this->fields['double_ip']) {
            echo $LANG['plugin_addressing']['reports'][29]." : ".$nbipd."<br>" ;
         }
         echo "</td>";
         echo "<td>";
         if ($this->fields['double_ip']) {
            echo "<span class='plugin_addressing_ip_double'>".$LANG['plugin_addressing']['reports'][15]."</span> : ".$LANG['plugin_addressing']['reports'][16]."<br>";
         }
         if (isset($this->fields['use_ping']) && $this->fields['use_ping']) {
            echo $LANG['plugin_addressing']['reports'][30]." : <br>";
            echo "<span class='plugin_addressing_ping_off'>".$LANG['plugin_addressing']['reports'][31]."</span><br>";
            echo "<span class='plugin_addressing_ping_on'>".$LANG['plugin_addressing']['reports'][32]."</span>";
         } else {
            echo "<span class='plugin_addressing_ip_free'>".$LANG['plugin_addressing']['reports'][25]."</span> : ".$LANG['plugin_addressing']['reports'][24]."<br>";
         }

         echo "</td></tr>";
         echo "</table>";
         echo "<br>";

         $numrows=1+ip2long($this->fields['end_ip'])-ip2long($this->fields['begin_ip']);
         if (strpos($_SERVER['PHP_SELF'],"report.form.php"))
            printPager($start,$numrows,$_SERVER['PHP_SELF'],"start=$start&amp;id=".$id,'PluginAddressingReport');
         else
            printAjaxPager("",$start,$numrows);
         //////////////////////////liste ips////////////////////////////////////////////////////////////

         $ping_response = $PluginAddressingReport->display($result, $this);

         if ($this->fields['use_ping']) {
            $total_realfreeip=$nbipf-$ping_response;
            echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2 center'>";
            echo "<td>";
            echo $LANG['plugin_addressing']['reports'][34].": ".$total_realfreeip;
            echo "</td></tr>";
            echo "</table>";
         }
         echo "</div>";

      } else {
         echo "<div align='center'><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br><b>";
         echo $LANG['plugin_addressing']['setup'][8];
         echo "</b></div>";
      }
	}
}

?>