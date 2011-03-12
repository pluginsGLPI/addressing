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

$title="Adresacja IP";

$LANG['plugin_addressing']['title'][1] = "".$title."";

$LANG['plugin_addressing']['reports'][1] = "Raport dla zakresu IP";
$LANG['plugin_addressing']['reports'][2] = "IP";
$LANG['plugin_addressing']['reports'][3] = "Wybierz sieć : ";
$LANG['plugin_addressing']['reports'][5] = "Adres MAC";
$LANG['plugin_addressing']['reports'][8] = "Pozycja typu(ów)";
$LANG['plugin_addressing']['reports'][9] = "Przyłączone urządzenia";
$LANG['plugin_addressing']['reports'][13] = "Zarezerwowany adress";
$LANG['plugin_addressing']['reports'][14] = "Użytkownik";
$LANG['plugin_addressing']['reports'][15] = "Czerwony wiersz";
$LANG['plugin_addressing']['reports'][16] = "Identyczne Ip";
$LANG['plugin_addressing']['reports'][20] = " do ";
$LANG['plugin_addressing']['reports'][23] = "Rezerwacja";
$LANG['plugin_addressing']['reports'][24] = "Niezajęte Ip";
$LANG['plugin_addressing']['reports'][25] = "Niebieski wiersz";
$LANG['plugin_addressing']['reports'][26] = "Liczba niezajętych ip";
$LANG['plugin_addressing']['reports'][27] = "Liczba zarezerwowanych ip";
$LANG['plugin_addressing']['reports'][28] = "Liczba przypisanych numerów IP (bez podwójnych)";
$LANG['plugin_addressing']['reports'][29] = "Podwójne";
$LANG['plugin_addressing']['reports'][30] = "Pinguj niezajęte Ip";
$LANG['plugin_addressing']['reports'][31] = "Ping: otrzymano odpowiedź - używane Ip";
$LANG['plugin_addressing']['reports'][32] = "Ping: brak odpowiedzi - niezajęte Îp";
$LANG['plugin_addressing']['reports'][34] = "Napewno niezajęte Ip (Ping=KO)";
$LANG['plugin_addressing']['reports'][36] = "Wykryta lista podsieci : ";
$LANG['plugin_addressing']['reports'][37] = "Nieprawidłowe dane !!";
$LANG['plugin_addressing']['reports'][38] = "Pierwsze IP";
$LANG['plugin_addressing']['reports'][39] = "Ostatnie IP";

$LANG['plugin_addressing']['profile'][0] = "Zarządzanie uprawnieniami";
$LANG['plugin_addressing']['profile'][3] = "Generowanie raportów";

$LANG['plugin_addressing']['setup'][8] = "Wykryto problem w zakresie IP";
$LANG['plugin_addressing']['setup'][10] = "Display";
$LANG['plugin_addressing']['setup'][11] = "Przydzielone IP";
$LANG['plugin_addressing']['setup'][12] = "Niezajęte Ip";
$LANG['plugin_addressing']['setup'][13] = "Identyczne IP";
$LANG['plugin_addressing']['setup'][14] = "Zarezerwowane IP";
$LANG['plugin_addressing']['setup'][19] = "Rodzaj ping-a";
$LANG['plugin_addressing']['setup'][20] = "Linux ping";
$LANG['plugin_addressing']['setup'][21] = "Windows";
$LANG['plugin_addressing']['setup'][22] = "Użyj Ping";
$LANG['plugin_addressing']['setup'][24] = "Sieć";
$LANG['plugin_addressing']['setup'][25] = "Linux fping";
$LANG['plugin_addressing']['setup'][27] = "Problem z dodaniem, brak wymaganego pola";
$LANG['plugin_addressing']['setup'][28] = "BSD ping";
$LANG['plugin_addressing']['setup'][29] = "MacOSX ping";

?>