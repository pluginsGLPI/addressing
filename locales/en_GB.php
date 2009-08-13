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

$LANG['plugin_addressing']['title'][1] = "".$title."";

$LANG['plugin_addressing'][1] = "No report found";
$LANG['plugin_addressing'][3] = "Link";
$LANG['plugin_addressing'][4] = "Generate";

$LANG['plugin_addressing']['reports'][1] = "Report for the IP Range";
$LANG['plugin_addressing']['reports'][2] = "IP";
$LANG['plugin_addressing']['reports'][3] = "Select the network : ";
$LANG['plugin_addressing']['reports'][5] = "Mac Address";
$LANG['plugin_addressing']['reports'][8] = "Item type(s)";
$LANG['plugin_addressing']['reports'][9] = "Connected devices";
$LANG['plugin_addressing']['reports'][13] = "Reserved Address";
$LANG['plugin_addressing']['reports'][14] = "User";
$LANG['plugin_addressing']['reports'][15] = "Red row";
$LANG['plugin_addressing']['reports'][16] = "Same Ip";
$LANG['plugin_addressing']['reports'][20] = " to ";
$LANG['plugin_addressing']['reports'][23] = "Reservation";
$LANG['plugin_addressing']['reports'][24] = "Free Ip";
$LANG['plugin_addressing']['reports'][25] = "Blue row";
$LANG['plugin_addressing']['reports'][26] = "Number of free ip";
$LANG['plugin_addressing']['reports'][27] = "Number of reserved ip";
$LANG['plugin_addressing']['reports'][28] = "Number of assigned ip (no doubles)";
$LANG['plugin_addressing']['reports'][29] = "Doubles";
$LANG['plugin_addressing']['reports'][30] = "Ping free Ip";
$LANG['plugin_addressing']['reports'][31] = "Ping: got a response - used Ip";
$LANG['plugin_addressing']['reports'][32] = "Ping: no response - free Îp";
$LANG['plugin_addressing']['reports'][34] = "Real free Ip (Ping=KO)";
$LANG['plugin_addressing']['reports'][36] = "Detected subnet list : ";
$LANG['plugin_addressing']['reports'][37] = "Invalid data !!";
$LANG['plugin_addressing']['reports'][38] = "First IP";
$LANG['plugin_addressing']['reports'][39] = "Last IP";

$LANG['plugin_addressing']['profile'][0] = "Rights management";
$LANG['plugin_addressing']['profile'][3] = "Generate reports";

$LANG['plugin_addressing']['setup'][8] = "Problem detected with the IP Range";
$LANG['plugin_addressing']['setup'][10] = "Display";
$LANG['plugin_addressing']['setup'][11] = "Assigned IP";
$LANG['plugin_addressing']['setup'][12] = "Free Ip";
$LANG['plugin_addressing']['setup'][13] = "Same IP";
$LANG['plugin_addressing']['setup'][14] = "Reserved IP";
$LANG['plugin_addressing']['setup'][15] = "Yes";
$LANG['plugin_addressing']['setup'][16] = "No";
$LANG['plugin_addressing']['setup'][19] = "System for ping";
$LANG['plugin_addressing']['setup'][20] = "Linux ping";
$LANG['plugin_addressing']['setup'][21] = "Windows";
$LANG['plugin_addressing']['setup'][22] = "Use Ping";
$LANG['plugin_addressing']['setup'][24] = "Network";
$LANG['plugin_addressing']['setup'][25] = "Linux fping";
$LANG['plugin_addressing']['setup'][27] = "Problem when adding, required fields are not here";
$LANG['plugin_addressing']['setup'][28] = "BSD ping";
$LANG['plugin_addressing']['setup'][29] = "MacOSX ping";

?>