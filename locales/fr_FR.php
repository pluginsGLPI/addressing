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

$title="Adressage IP";

$LANGADDRESSING["title"][1]="".$title."";
$LANGADDRESSING["title"][2]="Rapport";

$LANGADDRESSING["addressing"][1]= "Pas de rapport trouvé";
$LANGADDRESSING["addressing"][2]= "Rapport IP";
$LANGADDRESSING["addressing"][3]= "Lien";
$LANGADDRESSING["addressing"][4]= "Génération";

$LANGADDRESSING["reports"][1]="Rapport pour la plage IP";
$LANGADDRESSING["reports"][2]="IP";
$LANGADDRESSING["reports"][3]="Sélectionnez le réseau : ";
$LANGADDRESSING["reports"][5]="Adresse Mac";
$LANGADDRESSING["reports"][8]="Type(s) de matériel";
$LANGADDRESSING["reports"][9]="Périphériques connectés";
$LANGADDRESSING["reports"][13]="Adresse réservée";
$LANGADDRESSING["reports"][14]="Utilisateur";
$LANGADDRESSING["reports"][15]="Ligne en rouge";
$LANGADDRESSING["reports"][16]="Ip en double";
$LANGADDRESSING["reports"][20]=" à ";
$LANGADDRESSING["reports"][23]="Réservation";
$LANGADDRESSING["reports"][24]="Ip libres";
$LANGADDRESSING["reports"][25]="Ligne en bleu";
$LANGADDRESSING["reports"][26]="Nombre d'ip libres";
$LANGADDRESSING["reports"][27]="Nombre d'ip réservées";
$LANGADDRESSING["reports"][28]="Nombre d'ip affectées (hors doublons)";
$LANGADDRESSING["reports"][29]="Doublons";
$LANGADDRESSING["reports"][30]="Pinger les Ip libres";
$LANGADDRESSING["reports"][31]="Réponse au ping - adresse utilisée";
$LANGADDRESSING["reports"][32]="Pas de réponse au ping - adresse libre";
$LANGADDRESSING["reports"][34]="ip libres réelles (Ping=KO)";
$LANGADDRESSING["reports"][36]="Liste des sous-réseaux détectés : ";
$LANGADDRESSING["reports"][37]="Données saisies incorrectes !!";
$LANGADDRESSING["reports"][38]="Première IP";
$LANGADDRESSING["reports"][39]="Dernière IP";

$LANGADDRESSING["profile"][0] = "Gestion des droits";
$LANGADDRESSING["profile"][1] = "$title";
$LANGADDRESSING["profile"][2] = "Configuration";
$LANGADDRESSING["profile"][3] = "Générer des rapports";
$LANGADDRESSING["profile"][4] = "Listes des profils déjà configurés";

$LANGADDRESSING["setup"][1] = "Configuration du plugin ".$title."";
$LANGADDRESSING["setup"][3] = "Installer le plugin $title";
$LANGADDRESSING["setup"][4] = "Mettre à jour le plugin $title vers la nouvelle version";
$LANGADDRESSING["setup"][5] = "Désinstaller le plugin $title";
$LANGADDRESSING["setup"][7] = "Attention, la désinstallation du plugin est irréversible.<br> Vous perdrez toutes les données.";
$LANGADDRESSING["setup"][8]="Problème dans le choix de la plage IP";
$LANGADDRESSING["setup"][10]="Affichage";
$LANGADDRESSING["setup"][11]="Ip attribuées";
$LANGADDRESSING["setup"][12]="Ip libres";
$LANGADDRESSING["setup"][13]="IP en double";
$LANGADDRESSING["setup"][14]="IP réservées";
$LANGADDRESSING["setup"][15]="Oui";
$LANGADDRESSING["setup"][16]="Non";
$LANGADDRESSING["setup"][17] = "Mode d'emploi";
$LANGADDRESSING["setup"][18] = "FAQ";
$LANGADDRESSING["setup"][19] = "Système pour le ping";
$LANGADDRESSING["setup"][20] = "Linux ping";
$LANGADDRESSING["setup"][21] = "Windows";
$LANGADDRESSING["setup"][22]="Utiliser le Ping";
$LANGADDRESSING["setup"][24]="Réseau";
$LANGADDRESSING["setup"][25] = "Linux fping";
$LANGADDRESSING["setup"][26] = "Merci de vous placer sur l'entité racine (voir tous)";
$LANGADDRESSING["setup"][27] = "Problème lors de l'ajout, des champs requis sont manquant";

?>