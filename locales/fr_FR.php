<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Addressing plugin for GLPI
 Copyright (C) 2003-2011 by the addressing Development Team.

 https://forge.indepnet.net/projects/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 Addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Addressing. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

$title="Adressage IP";

$LANG['plugin_addressing']['title'][1] = "".$title."";

$LANG['plugin_addressing']['reports'][1] = "Rapport pour la plage IP";
$LANG['plugin_addressing']['reports'][2] = "IP";
$LANG['plugin_addressing']['reports'][3] = "Sélectionnez le réseau : ";
$LANG['plugin_addressing']['reports'][5] = "Adresse Mac";
$LANG['plugin_addressing']['reports'][8] = "Type(s) de matériel";
$LANG['plugin_addressing']['reports'][9] = "Périphériques connectés";
$LANG['plugin_addressing']['reports'][13] = "Adresse réservée";
$LANG['plugin_addressing']['reports'][14] = "Utilisateur";
$LANG['plugin_addressing']['reports'][15] = "Ligne en rouge";
$LANG['plugin_addressing']['reports'][16] = "Ip en double";
$LANG['plugin_addressing']['reports'][20] = " à ";
$LANG['plugin_addressing']['reports'][23] = "Réservation";
$LANG['plugin_addressing']['reports'][24] = "Ip libres";
$LANG['plugin_addressing']['reports'][25] = "Ligne en bleu";
$LANG['plugin_addressing']['reports'][26] = "Nombre d'ip libres";
$LANG['plugin_addressing']['reports'][27] = "Nombre d'ip réservées";
$LANG['plugin_addressing']['reports'][28] = "Nombre d'ip affectées (hors doublons)";
$LANG['plugin_addressing']['reports'][29] = "Doublons";
$LANG['plugin_addressing']['reports'][30] = "Pinger les Ip libres";
$LANG['plugin_addressing']['reports'][31] = "Réponse au ping - adresse utilisée";
$LANG['plugin_addressing']['reports'][32] = "Pas de réponse au ping - adresse libre";
$LANG['plugin_addressing']['reports'][34] = "ip libres réelles (Ping=KO)";
$LANG['plugin_addressing']['reports'][36] = "Liste des sous-réseaux détectés : ";
$LANG['plugin_addressing']['reports'][37] = "Données saisies incorrectes !!";
$LANG['plugin_addressing']['reports'][38] = "Première IP";
$LANG['plugin_addressing']['reports'][39] = "Dernière IP";

$LANG['plugin_addressing']['profile'][0] = "Gestion des droits";
$LANG['plugin_addressing']['profile'][3] = "Générer des rapports";
$LANG['plugin_addressing']['profile'][4] = "Utiliser le Ping sur la fiche des equipements";

$LANG['plugin_addressing']['setup'][8] = "Problème dans le choix de la plage IP";
$LANG['plugin_addressing']['setup'][10] = "Affichage";
$LANG['plugin_addressing']['setup'][11] = "Ip attribuées";
$LANG['plugin_addressing']['setup'][12] = "Ip libres";
$LANG['plugin_addressing']['setup'][13] = "IP en double";
$LANG['plugin_addressing']['setup'][14] = "IP réservées";
$LANG['plugin_addressing']['setup'][19] = "Système pour le ping";
$LANG['plugin_addressing']['setup'][20] = "Linux ping";
$LANG['plugin_addressing']['setup'][21] = "Windows";
$LANG['plugin_addressing']['setup'][22] = "Utiliser le Ping";
$LANG['plugin_addressing']['setup'][24] = "Réseau";
$LANG['plugin_addressing']['setup'][25] = "Linux fping";
$LANG['plugin_addressing']['setup'][27] = "Problème lors de l'ajout, des champs requis sont manquant";
$LANG['plugin_addressing']['setup'][28] = "BSD ping";
$LANG['plugin_addressing']['setup'][29] = "MacOSX ping";



$LANG['plugin_addressing']['equipment'][0] = "Ping";
$LANG['plugin_addressing']['equipment'][1] = "Résultat";
$LANG['plugin_addressing']['equipment'][2] = "Réponse au ping";
$LANG['plugin_addressing']['equipment'][3] = "Pas de réponse au ping";
$LANG['plugin_addressing']['equipment'][4] = "Ping IP";
$LANG['plugin_addressing']['equipment'][5] = "Aucune ip pour cet épuipement";

?>
