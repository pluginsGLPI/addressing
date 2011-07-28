<?php
/*
 * @version $Id: HEADER 2011-03-12 18:01:26 tsmr $
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
// Purpose of file: plugin addressing v1.9.0 - GLPI 0.80
// ----------------------------------------------------------------------
 */

$title="IP Adressing";

$LANG['plugin_addressing']['title'][1] = "".$title."";

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
$LANG['plugin_addressing']['profile'][4] = "Use ping on equipment form";

$LANG['plugin_addressing']['setup'][8] = "Problem detected with the IP Range";
$LANG['plugin_addressing']['setup'][10] = "Display";
$LANG['plugin_addressing']['setup'][11] = "Assigned IP";
$LANG['plugin_addressing']['setup'][12] = "Free Ip";
$LANG['plugin_addressing']['setup'][13] = "Same IP";
$LANG['plugin_addressing']['setup'][14] = "Reserved IP";
$LANG['plugin_addressing']['setup'][19] = "System for ping";
$LANG['plugin_addressing']['setup'][20] = "Linux ping";
$LANG['plugin_addressing']['setup'][21] = "Windows";
$LANG['plugin_addressing']['setup'][22] = "Use Ping";
$LANG['plugin_addressing']['setup'][24] = "Network";
$LANG['plugin_addressing']['setup'][25] = "Linux fping";
$LANG['plugin_addressing']['setup'][27] = "Problem when adding, required fields are not here";
$LANG['plugin_addressing']['setup'][28] = "BSD ping";
$LANG['plugin_addressing']['setup'][29] = "MacOSX ping";

$LANG['plugin_addressing']['equipment'][0] = "Ping";
$LANG['plugin_addressing']['equipment'][1] = "Result";
$LANG['plugin_addressing']['equipment'][2] = "Ping: got a response";
$LANG['plugin_addressing']['equipment'][3] = "Ping: no response";
$LANG['plugin_addressing']['equipment'][4] = "IP ping";
$LANG['plugin_addressing']['equipment'][5] = "no IP for this equipment";

?>
