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

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0;

$NEEDED_ITEMS=array("networking","computer","printer","peripheral","phone","user","group");
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");

useplugin('addressing',true);

commonHeader($LANG['plugin_addressing']['title'][1],$_SERVER['PHP_SELF'],"plugins","addressing");

$PluginAddressing=new PluginAddressing;

if ($PluginAddressing->getFromDB($_GET["id"])){

	$result=$PluginAddressing->compute();
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
	echo "<div align='center'><b>".$PluginAddressing->getTitle(). "</b><br>";

	echo "<table class='tab_cadre_report'><tr class='tab_bg_2' align='left'>";
	echo "<td>";
	if ($PluginAddressing->fields['free_ip']) {
		echo $LANG['plugin_addressing']['reports'][26]." : ".$nbipf."<br>" ;
	}
	if ($PluginAddressing->fields['reserved_ip']) {
		echo $LANG['plugin_addressing']['reports'][27]." : ".$nbipr."<br>" ;
	}
	if ($PluginAddressing->fields['alloted_ip']) {
		echo $LANG['plugin_addressing']['reports'][28]." : ".$nbipt."<br>" ;
	}
	if ($PluginAddressing->fields['double_ip']) {
		echo $LANG['plugin_addressing']['reports'][29]." : ".$nbipd."<br>" ;
	}
	echo "</td>";
	echo "<td>";
	if ($PluginAddressing->fields['double_ip']) {
		echo "<span class='plugin_addressing_ip_double'>".$LANG['plugin_addressing']['reports'][15]."</span> : ".$LANG['plugin_addressing']['reports'][16]."<br>";
	}
	if (isset($PluginAddressing->fields['use_ping']) && $PluginAddressing->fields['use_ping']){
		echo $LANG['plugin_addressing']['reports'][30]." : <br>";
		echo "<span class='plugin_addressing_ping_off'>".$LANG['plugin_addressing']['reports'][31]."</span><br>";
		echo "<span class='plugin_addressing_ping_on'>".$LANG['plugin_addressing']['reports'][32]."</span>";
	} else {
		echo "<span class='plugin_addressing_ip_free'>".$LANG['plugin_addressing']['reports'][25]."</span> : ".$LANG['plugin_addressing']['reports'][24]."<br>";
	}

	echo "</td></tr>";
	echo "</table>";
	echo "<br>";

	if (isset($_GET["start"])) {
		$start = $_GET["start"];
	} else {
		$start = 0;
	}
	$numrows=1+ip2long($PluginAddressing->fields['end_ip'])-ip2long($PluginAddressing->fields['begin_ip']);
	printPager($start,$numrows,$_SERVER["PHP_SELF"],"start=$start&amp;id=".$_GET["id"],PLUGIN_ADDRESSING_REPORT_TYPE);

	//////////////////////////liste ips////////////////////////////////////////////////////////////

	$ping_response = $PluginAddressing->display($result, $PluginAddressing);

	if ($PluginAddressing->fields['use_ping']){
		$total_realfreeip=$nbipf-$ping_response;
		echo "<table class='tab_cadre_report'><tr class='tab_bg_2' align='center'>";
		echo "<td>";
		echo $LANG['plugin_addressing']['reports'][34].": ".$total_realfreeip;
		echo "</td></tr>";
		echo "</table>";
	}
	echo "</div>";

} else {
	echo "<div align='center'><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br><b>";
	echo $LANG['plugin_addressing']['setup'][8]."<br><br>";
	echo "<a href=\"../index.php\">".$LANG['buttons'][13]."</a>";
	echo "</b></div>";
}

commonFooter();

?>