<?php
/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-addressing.org/
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

// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
// Purpose of file:
// ----------------------------------------------------------------------

if(!defined('GLPI_ROOT')){
	define('GLPI_ROOT', '../../..');
	$NEEDED_ITEMS=array("setup");
	include (GLPI_ROOT . "/inc/includes.php");
}

useplugin('addressing',true);

$PluginAddressingDisplay=new PluginAddressingDisplay();

checkRight("config","w");

if (isset($_POST["update"])) {
	checkRight("config","w");

	$PluginAddressingDisplay->update($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);

} else {
	
	$plugin = new Plugin();
	if ($plugin->isActivated("addressing"))
		commonHeader($LANG['plugin_addressing']['title'][1],$_SERVER["PHP_SELF"],"plugins","addressing");
	else
		commonHeader($LANG['common'][12],$_SERVER['PHP_SELF'],"config","plugins");
		
	echo "<div align='center'>";
	
	$PluginAddressingDisplay->getFromDB('1');
	
	$ip_alloted=$PluginAddressingDisplay->fields["ip_alloted"];
	$ip_free=$PluginAddressingDisplay->fields["ip_free"];
	$ip_reserved=$PluginAddressingDisplay->fields["ip_reserved"];
	$ip_double=$PluginAddressingDisplay->fields["ip_double"];
	$system=$PluginAddressingDisplay->fields["system"];
	$ping=$PluginAddressingDisplay->fields["ping"];
	
	echo "<form method='post' action=\"./plugin_addressing.config.php\">";
			
	echo "<table class='tab_cadre' cellpadding='5'>";
	echo "<tr><th colspan='4'>".$LANG['plugin_addressing']['setup'][19]."</th></tr>";
	
	echo "<tr class='tab_bg_1'><td colspan='4'><div align='center'><select name=\"system\">";
	echo "<option value='0' ".($system==0?" selected ":"").">".$LANG['plugin_addressing']['setup'][20]."</option>";
	echo "<option value='2' ".($system==2?" selected ":"").">".$LANG['plugin_addressing']['setup'][25]."</option>";
	echo "<option value='1' ".($system==1?" selected ":"").">".$LANG['plugin_addressing']['setup'][21]."</option>";
	echo "<option value='3' ".($system==3?" selected ":"").">".$LANG['plugin_addressing']['setup'][28]."</option>";
	echo "<option value='4' ".($system==4?" selected ":"").">".$LANG['plugin_addressing']['setup'][29]."</option>";
	echo "</select>";
	echo "</div></td></tr>";
	
	echo "<tr><th colspan='4'>".$LANG['plugin_addressing']['setup'][10]."</th></tr>";
	
	echo "<tr class='tab_bg_1'><td>".$LANG['plugin_addressing']['setup'][11]."</td>";
	echo "<td>";
	echo "<select name=\"ip_alloted\">";
	echo "<option value='1' ".($ip_alloted==1?" selected ":"").">".$LANG['plugin_addressing']['setup'][15]."</option>";
	echo "<option value='0' ".($ip_alloted==0?" selected ":"").">".$LANG['plugin_addressing']['setup'][16]."</option>";
	echo "</select>";
	echo "</td>";
	
	echo "<td>".$LANG['plugin_addressing']['setup'][12]."</td>";
	echo "<td>";
	echo "<select name=\"ip_free\">";
	echo "<option value='1' ".($ip_free==1?" selected ":"").">".$LANG['plugin_addressing']['setup'][15]."</option>";
	echo "<option value='0' ".($ip_free==0?" selected ":"").">".$LANG['plugin_addressing']['setup'][16]."</option>";
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	
	echo "<tr class='tab_bg_1'><td>".$LANG['plugin_addressing']['setup'][13]."</td>";
	echo "<td>";
	echo "<select name=\"ip_double\">";
	echo "<option value='1' ".($ip_double==1?" selected ":"").">".$LANG['plugin_addressing']['setup'][15]."</option>";
	echo "<option value='0' ".($ip_double==0?" selected ":"").">".$LANG['plugin_addressing']['setup'][16]."</option>";
	echo "</select>";
	echo "</td>";
	
	echo "<td>".$LANG['plugin_addressing']['setup'][14]."</td>";
	echo "<td>";
	echo "<select name=\"ip_reserved\">";
	echo "<option value='1' ".($ip_reserved==1?" selected ":"").">".$LANG['plugin_addressing']['setup'][15]."</option>";
	echo "<option value='0' ".($ip_reserved==0?" selected ":"").">".$LANG['plugin_addressing']['setup'][16]."</option>";
	echo "</select>";
	echo "</td>";
				
	echo "</tr>";
	
	echo "<tr class='tab_bg_1'><td colspan='2'>".$LANG['plugin_addressing']['setup'][22]."</td>";
	echo "<td  colspan='2'>";
	echo "<select name=\"ping\">";
	echo "<option value='1' ".($ping==1?" selected ":"").">".$LANG['plugin_addressing']['setup'][15]."</option>";
	echo "<option value='0' ".($ping==0?" selected ":"").">".$LANG['plugin_addressing']['setup'][16]."</option>";
	echo "</select>";
	echo "</td>";
				
	echo "<tr><th colspan='4'>";
	echo "<input type='hidden' name='ID' value=\"1\">";
	echo "<div align='center'><input type='submit' name='update' value=\"".$LANG['buttons'][2]."\" class='submit' ></div></th></tr>";
	echo "</table>";
	echo "</form>";
	echo "</div>";
	
	commonFooter();
}
?>