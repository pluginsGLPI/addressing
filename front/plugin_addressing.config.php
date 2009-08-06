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

$NEEDED_ITEMS=array("setup");
if(!defined('GLPI_ROOT')){
	define('GLPI_ROOT', '../../..'); 
}
include (GLPI_ROOT . "/inc/includes.php");

checkRight("config","w");

	if(!isset($_SESSION["glpi_plugin_addressing_installed"]) || $_SESSION["glpi_plugin_addressing_installed"]!=1) {
		
		commonHeader($LANG["common"][12],$_SERVER['PHP_SELF'],"config","plugins");
		
		if ($_SESSION["glpiactive_entity"]==0){
		
			if (!TableExists("glpi_plugin_addressing") && !TableExists("glpi_plugin_addressing_display")) {
			
				echo "<div align='center'>";
				echo "<table class='tab_cadre' cellpadding='5'>";
				echo "<tr><th>".$LANGADDRESSING["setup"][1];
				echo "</th></tr>";
				echo "<tr class='tab_bg_1'><td>";
				echo "<a href='plugin_addressing.install.php'>".$LANGADDRESSING["setup"][3]."</a></td></tr>";
				echo "</table></div>";
		
	
			} else if (TableExists("glpi_plugin_addressing") && !FieldExists("glpi_plugin_addressing","ipdeb")) {
			
				echo "<div align='center'>";
				echo "<table class='tab_cadre' cellpadding='5'>";
				echo "<tr><th>".$LANGADDRESSING["setup"][1];
				echo "</th></tr>";
				echo "<tr class='tab_bg_1'><td>";
				echo "<a href='plugin_addressing.update.php'>".$LANGADDRESSING["setup"][4]."</a></td></tr>";
				echo "</table></div>";
						
			}
		
		}else{ 
			echo "<div align='center'><br><br><img src=\"".$CFG_GLPI["root_doc"]."/pics/warning.png\" alt=\"warning\"><br><br>"; 
			echo "<b>".$LANGADDRESSING["setup"][26]."</b></div>"; 
		}
	}else {
		
		commonHeader($LANG["common"][12],$_SERVER['PHP_SELF'],"plugins","addressing");
	
		$plugin_addressing_display=new plugin_addressing_display;
		
	if (isset($_POST["update"])) {
		checkRight("config","w");

		$plugin_addressing_display->update($_POST);
		glpi_header($_SERVER['HTTP_REFERER']);

	} else {

		checkRight("config","w");
		
			echo "<div align='center'>";
			
			$plugin_addressing_display=new plugin_addressing_display();
			$plugin_addressing_display->getFromDB('1');
			
			$ip_alloted=$plugin_addressing_display->fields["ip_alloted"];
			$ip_free=$plugin_addressing_display->fields["ip_free"];
			$ip_reserved=$plugin_addressing_display->fields["ip_reserved"];
			$ip_double=$plugin_addressing_display->fields["ip_double"];
			$system=$plugin_addressing_display->fields["system"];
			$ping=$plugin_addressing_display->fields["ping"];
		
			echo "<form method='post' action=\"./plugin_addressing.config.php\">";
					
			echo "<table class='tab_cadre' cellpadding='5'>";
			echo "<tr><th colspan='4'>".$LANGADDRESSING["setup"][19]."</th></tr>";

			echo "<tr class='tab_bg_1'><td colspan='4'><div align='center'><select name=\"system\">";
			echo "<option value='0' ".($system==0?" selected ":"").">".$LANGADDRESSING["setup"][20]."</option>";
			echo "<option value='2' ".($system==2?" selected ":"").">".$LANGADDRESSING["setup"][25]."</option>";
			echo "<option value='1' ".($system==1?" selected ":"").">".$LANGADDRESSING["setup"][21]."</option>";
			echo "</select>";
			echo "</div></td></tr>";
			
			echo "<tr><th colspan='4'>".$LANGADDRESSING["setup"][10]."</th></tr>";
			
			echo "<tr class='tab_bg_1'><td>".$LANGADDRESSING["setup"][11]."</td>";
			echo "<td>";
			echo "<select name=\"ip_alloted\">";
			echo "<option value='1' ".($ip_alloted==1?" selected ":"").">".$LANGADDRESSING["setup"][15]."</option>";
			echo "<option value='0' ".($ip_alloted==0?" selected ":"").">".$LANGADDRESSING["setup"][16]."</option>";
			echo "</select>";
			echo "</td>";
			
			echo "<td>".$LANGADDRESSING["setup"][12]."</td>";
			echo "<td>";
			echo "<select name=\"ip_free\">";
			echo "<option value='1' ".($ip_free==1?" selected ":"").">".$LANGADDRESSING["setup"][15]."</option>";
			echo "<option value='0' ".($ip_free==0?" selected ":"").">".$LANGADDRESSING["setup"][16]."</option>";
			echo "</select>";
			echo "</td>";
			echo "</tr>";
			
			
			
			echo "<tr class='tab_bg_1'><td>".$LANGADDRESSING["setup"][13]."</td>";
			echo "<td>";
			echo "<select name=\"ip_double\">";
			echo "<option value='1' ".($ip_double==1?" selected ":"").">".$LANGADDRESSING["setup"][15]."</option>";
			echo "<option value='0' ".($ip_double==0?" selected ":"").">".$LANGADDRESSING["setup"][16]."</option>";
			echo "</select>";
			echo "</td>";
			
			
			echo "<td>".$LANGADDRESSING["setup"][14]."</td>";
			echo "<td>";
			echo "<select name=\"ip_reserved\">";
			echo "<option value='1' ".($ip_reserved==1?" selected ":"").">".$LANGADDRESSING["setup"][15]."</option>";
			echo "<option value='0' ".($ip_reserved==0?" selected ":"").">".$LANGADDRESSING["setup"][16]."</option>";
			echo "</select>";
			echo "</td>";
						
			echo "</tr>";
			
			echo "<tr class='tab_bg_1'><td colspan='2'>".$LANGADDRESSING["setup"][22]."</td>";
			echo "<td  colspan='2'>";
			echo "<select name=\"ping\">";
			echo "<option value='1' ".($ping==1?" selected ":"").">".$LANGADDRESSING["setup"][15]."</option>";
			echo "<option value='0' ".($ping==0?" selected ":"").">".$LANGADDRESSING["setup"][16]."</option>";
			echo "</select>";
			echo "</td>";
						
			echo "<tr><th colspan='4'>";
			echo "<input type='hidden' name='ID' value=\"1\">";
			echo "<div align='center'><input type='submit' name='update' value=\"".$LANG["buttons"][2]."\" class='submit' ></div></th></tr>";
			echo "</table>";
			echo "</form>";			
				
			
			echo "<table class='tab_cadre' cellpadding='5'>";
			echo "<tr><th>".$LANGADDRESSING["setup"][1];
			echo "</th></tr>";
			if (haveRight("config","w") && haveRight("profile","w")){
			echo "<tr class='tab_bg_1'><td align='center'>";
			echo "<a href=\"./plugin_addressing.profile.php\">".$LANGADDRESSING["profile"][0]."</a>";
			echo "</td></tr>";
			}
			echo "<tr class='tab_bg_1'><td align='center'>";
			echo "<a href='http://glpi-project.org/wiki/doku.php?id=".substr($_SESSION["glpilanguage"],0,2).":plugins:addressing_use' target='_blank'>".$LANGADDRESSING["setup"][17]."&nbsp;</a>";
			echo "/&nbsp;<a href='http://glpi-project.org/wiki/doku.php?id=".substr($_SESSION["glpilanguage"],0,2).":plugins:addressing_faq' target='_blank'>".$LANGADDRESSING["setup"][18]." </a>";
			echo "</td></tr>";
			if ($_SESSION["glpiactive_entity"]==0){
				echo "<tr class='tab_bg_1'><td align='center'>";
				echo "<a href='plugin_addressing.uninstall.php'>".$LANGADDRESSING["setup"][5]."</a>";
				echo " <img src='".$CFG_GLPI["root_doc"]."/pics/aide.png' alt=\"\" onmouseout=\"setdisplay(getElementById('comments'),'none')\" onmouseover=\"setdisplay(getElementById('comments'),'block')\">";
				echo "<span class='over_link' id='comments'>".$LANGADDRESSING["setup"][7]."</span>";
				echo "</td></tr>";
			}
			echo "</table>";
			
		echo "</div>";	
		
	}

}

commonFooter();

?>