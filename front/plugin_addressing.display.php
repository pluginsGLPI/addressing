<?php
/*
 * @version $Id: setup.php,v 1.2 2006/04/02 14:45:27 moyo Exp $
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
//
// ----------------------------------------------------------------------
// Original Author of file: Gilles PORTHEAULT
// Purpose of file:
// ----------------------------------------------------------------------

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0;

$NEEDED_ITEMS=array("networking","computer","printer","peripheral","phone","user","group");
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

commonHeader($LANGADDRESSING["title"][1],$_SERVER['PHP_SELF'],"plugins","addressing");

$plugin_addressing=new plugin_addressing;

if ($plugin_addressing->getFromDB($_GET["ID"])){

	$result=$plugin_addressing->compute();	
	//echo "<pre>"; print_r($result);	echo "</pre>";

	$nbipf=0;	// ip libres
	$nbipr=0;	// ip réservées
	$nbipt=0;	// ip trouvées
	$nbipd=0;	// doublons

	foreach ($result as $ip => $lines) {
		if (count($lines)) {
			if (count($lines)>1) {
				$nbipd++;
			}	
			$nbipt++;
		} else {
			$nbipf++;
		}
	}

	////title
	echo "<div align='center'><b>".$plugin_addressing->getTitle(). "</b><br>";

	echo "<table class='tab_cadre_report'><tr class='tab_bg_2' align='left'>";
	echo "<td>";
	if ($plugin_addressing->fields['ip_free']) {
		echo $LANGADDRESSING["reports"][26]." : ".$nbipf."<br>" ;
	}
	if ($plugin_addressing->fields['ip_reserved']) {
		echo $LANGADDRESSING["reports"][27]." : ".$nbipr."<br>" ;
	}    
	if ($plugin_addressing->fields['ip_alloted']) {
		echo $LANGADDRESSING["reports"][28]." : ".$nbipt."<br>" ;
	} 
	if ($plugin_addressing->fields['ip_double']) {
		echo $LANGADDRESSING["reports"][29]." : ".$nbipd."<br>" ;
	}
	echo "</td>";
	echo "<td>";
	if ($plugin_addressing->fields['ip_double']) {
		echo "<span class='plugin_addressing_ip_double'>".$LANGADDRESSING["reports"][15]."</span> : ".$LANGADDRESSING["reports"][16]."<br>";
	}
	if (isset($plugin_addressing->fields['ping']) && $plugin_addressing->fields['ping']){
		echo $LANGADDRESSING["reports"][30]." : <br>";
		echo "<span class='plugin_addressing_ping_off'>".$LANGADDRESSING["reports"][31]."</span><br>";
		echo "<span class='plugin_addressing_ping_on'>".$LANGADDRESSING["reports"][32]."</span>";	
	} else {
		echo "<span class='plugin_addressing_ip_free'>".$LANGADDRESSING["reports"][25]."</span> : ".$LANGADDRESSING["reports"][24]."<br>";
	}
	
	echo "</td></tr>";
	echo "</table>";
	echo "<br>";
		
	if (isset($_GET["start"])) {
		$start = $_GET["start"];
	} else {
		$start = 0;
	}
	$numrows=1+ip2long($plugin_addressing->fields['ipfin'])-ip2long($plugin_addressing->fields['ipdeb']);
	printPager($start,$numrows,$_SERVER["PHP_SELF"],"start=$start&amp;ID=".$_GET["ID"],PLUGIN_ADDRESSING_REPORT_TYPE);
	//plugin_addressing_printPager("ID=".$_GET["ID"]);
	
	//////////////////////////liste ips////////////////////////////////////////////////////////////

	$ping_response = plugin_addressing_display($result, $plugin_addressing);

	if ($plugin_addressing->fields['ping']){	
		$total_realfreeip=$nbipf-$ping_response;
		echo "<table class='tab_cadre_report'><tr class='tab_bg_2' align='center'>";
		echo "<td>";
		echo $LANGADDRESSING["reports"][34].": ".$total_realfreeip;
		echo "</td></tr>";
		echo "</table>";
	}
	echo "</div>";
		
} else {
	echo "<div align='center'><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br><b>";
	echo $LANGADDRESSING["setup"][8]."<br><br>";
	echo "<a href=\"../index.php\">".$LANG["buttons"][13]."</a>";
	echo "</b></div>";
}

commonFooter();

?>