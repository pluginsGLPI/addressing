<?php
/*

   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/

   ----------------------------------------------------------------------
   LICENSE

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License (GPL)
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   To read the license please visit http://www.gnu.org/copyleft/gpl.html
   ----------------------------------------------------------------------
// Original Author of file: GRISARD Jean Marc & CAILLAUD Xavier
Purpose of file:
----------------------------------------------------------------------
 */
/**************** Plugin Addressing **************/

$title="IP Adressing";

$LANGADDRESSING["title"][1]="".$title."";
$LANGADDRESSING["title"][2]="Report";

$LANGADDRESSING["addressing"][1]= "No report found";
$LANGADDRESSING["addressing"][2]= "IP Report";
$LANGADDRESSING["addressing"][3]= "Link";
$LANGADDRESSING["addressing"][4]= "Generate";

$LANGADDRESSING["reports"][1]="Report for the IP Range";
$LANGADDRESSING["reports"][2]="IP";
$LANGADDRESSING["reports"][3]="Select the network : ";
$LANGADDRESSING["reports"][5]="Mac Address";
$LANGADDRESSING["reports"][8]="Item type(s)";
$LANGADDRESSING["reports"][9]="Connected devices";
$LANGADDRESSING["reports"][13]="Reserved Address";
$LANGADDRESSING["reports"][14]="User";
$LANGADDRESSING["reports"][15]="Red row";
$LANGADDRESSING["reports"][16]="Same Ip";
$LANGADDRESSING["reports"][20]=" to ";
$LANGADDRESSING["reports"][23]="Reservation";
$LANGADDRESSING["reports"][24]="Free Ip";
$LANGADDRESSING["reports"][25]="Blue row";
$LANGADDRESSING["reports"][26]="Number of free ip";
$LANGADDRESSING["reports"][27]="Number of reserved ip";
$LANGADDRESSING["reports"][28]="Number of assigned ip (no doubles)";
$LANGADDRESSING["reports"][29]="Doubles";
$LANGADDRESSING["reports"][30]="Ping free Ip";
$LANGADDRESSING["reports"][31]="Ping: got a response - used Ip";
$LANGADDRESSING["reports"][32]="Ping: no response - free Îp";
$LANGADDRESSING["reports"][34]="Real free Ip (Ping=KO)";
$LANGADDRESSING["reports"][36]="Detected subnet list : ";
$LANGADDRESSING["reports"][37]="Invalid data !!";
$LANGADDRESSING["reports"][38]="First IP";
$LANGADDRESSING["reports"][39]="Last IP";

$LANGADDRESSING["profile"][0] = "Rights management";
$LANGADDRESSING["profile"][1] = "$title";
$LANGADDRESSING["profile"][2] = "Setup";
$LANGADDRESSING["profile"][3] = "Generate reports";
$LANGADDRESSING["profile"][4] = "List of profiles already configured";

$LANGADDRESSING["setup"][1] = "Setup of plugin ".$title."";
$LANGADDRESSING["setup"][3] = "Install $title plugin";
$LANGADDRESSING["setup"][4] = "Update $title plugin to new version";
$LANGADDRESSING["setup"][5] = "Uninstall $title plugin";
$LANGADDRESSING["setup"][6] = "Warning, the update is irreversible.";
$LANGADDRESSING["setup"][7] = "Warning, the uninstallation of the plugin is irreversible.<br> You will loose all the data.";
$LANGADDRESSING["setup"][8]="Problem detected with the IP Range";
$LANGADDRESSING["setup"][10]="Display";
$LANGADDRESSING["setup"][11]="Assigned IP";
$LANGADDRESSING["setup"][12]="Free Ip";
$LANGADDRESSING["setup"][13]="Same IP";
$LANGADDRESSING["setup"][14]="Reserved IP";
$LANGADDRESSING["setup"][15]="Yes";
$LANGADDRESSING["setup"][16]="No";
$LANGADDRESSING["setup"][17] = "Instructions";
$LANGADDRESSING["setup"][18] = "FAQ";
$LANGADDRESSING["setup"][19] = "System for ping";
$LANGADDRESSING["setup"][20] = "Linux ping";
$LANGADDRESSING["setup"][21] = "Windows";
$LANGADDRESSING["setup"][22]="Use Ping";
$LANGADDRESSING["setup"][24]="Network";
$LANGADDRESSING["setup"][25] = "Linux fping";
$LANGADDRESSING["setup"][26] = "Merci de vous placer sur l'entité racine (voir tous)";
$LANGADDRESSING["setup"][27] = "Problem when adding, required fields are not here";

?>