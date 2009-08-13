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

$title="Direccionamiento IP";

$LANG['plugin_addressing']['title'][1] = "".$title."";

$LANG['plugin_addressing'][1] = "Ningún informe encontrado";
$LANG['plugin_addressing'][3] = "Enlace";
$LANG['plugin_addressing'][4] = "Generar";

$LANG['plugin_addressing']['reports'][1] = "Informe para el rango IP";
$LANG['plugin_addressing']['reports'][2] = "IP";
$LANG['plugin_addressing']['reports'][3] = "Selecciona la Red : ";
$LANG['plugin_addressing']['reports'][5] = "Dirección Mac";
$LANG['plugin_addressing']['reports'][8] = "Tipo(s) de material";
$LANG['plugin_addressing']['reports'][9] = "Equipos conectados";
$LANG['plugin_addressing']['reports'][13] = "Direcciones reservadas";
$LANG['plugin_addressing']['reports'][14] = "Usuario";
$LANG['plugin_addressing']['reports'][15] = "Línea en Rojo";
$LANG['plugin_addressing']['reports'][16] = "Misma Ip";
$LANG['plugin_addressing']['reports'][20] = " a ";
$LANG['plugin_addressing']['reports'][23] = "Reservada";
$LANG['plugin_addressing']['reports'][24] = "Ip libre";
$LANG['plugin_addressing']['reports'][25] = "Línea en Azul";
$LANG['plugin_addressing']['reports'][26] = "Número de ip libres";
$LANG['plugin_addressing']['reports'][27] = "Número de ip reservadas";
$LANG['plugin_addressing']['reports'][28] = "Número de ip asignadas (no duplicadas)";
$LANG['plugin_addressing']['reports'][29] = "Duplicadas";
$LANG['plugin_addressing']['reports'][30] = "Ping Ip libre";
$LANG['plugin_addressing']['reports'][31] = "Ping: responde - Ip usada";
$LANG['plugin_addressing']['reports'][32] = "Ping: no response - Ip libre";
$LANG['plugin_addressing']['reports'][34] = "Ip libre real (Ping=KO)";
$LANG['plugin_addressing']['reports'][36] = "Lista de subred detectada : ";
$LANG['plugin_addressing']['reports'][37] = "dato inválido!!";
$LANG['plugin_addressing']['reports'][38] = "Primera IP";
$LANG['plugin_addressing']['reports'][39] = "Última IP";

$LANG['plugin_addressing']['profile'][0] = "Rights management";
$LANG['plugin_addressing']['profile'][3] = "Genera Informes";

$LANG['plugin_addressing']['setup'][8] = "Problema detectado con el rango IP";
$LANG['plugin_addressing']['setup'][10] = "Muestra";
$LANG['plugin_addressing']['setup'][11] = "IP Asignada";
$LANG['plugin_addressing']['setup'][12] = "Ip Libre";
$LANG['plugin_addressing']['setup'][13] = "Misma IP";
$LANG['plugin_addressing']['setup'][14] = "IP Reservada";
$LANG['plugin_addressing']['setup'][15] = "Si";
$LANG['plugin_addressing']['setup'][16] = "No";
$LANG['plugin_addressing']['setup'][19] = "Sistema para el ping";
$LANG['plugin_addressing']['setup'][20] = "Ping Linux";
$LANG['plugin_addressing']['setup'][21] = "Windows";
$LANG['plugin_addressing']['setup'][22] = "Uso Ping?";
$LANG['plugin_addressing']['setup'][24] = "Red";
$LANG['plugin_addressing']['setup'][25] = "Linux fping";
$LANG['plugin_addressing']['setup'][27] = "Problema al añadir, campos requeridos vacíos";
$LANG['plugin_addressing']['setup'][28] = "Ping BSD";
$LANG['plugin_addressing']['setup'][29] = "Ping MacOSX";

?>