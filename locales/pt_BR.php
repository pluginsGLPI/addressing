<?php
/*

   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2005 by the INDEPNET Development Team.

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
$LANGADDRESSING["title"][2]="Relatórios";

$LANGADDRESSING["addressing"][1]= "No report found";
$LANGADDRESSING["addressing"][2]= "IP Report";
$LANGADDRESSING["addressing"][3]= "Link";
$LANGADDRESSING["addressing"][4]= "Generate";

$LANGADDRESSING["reports"][1]="Relatório de Range e IPs";
$LANGADDRESSING["reports"][2]="IP";
$LANGADDRESSING["reports"][3]="Selecione o Range de IP e a Rede: ";
$LANGADDRESSING["reports"][5]="Mac Address";
$LANGADDRESSING["reports"][8]="Tipo(s) de Item(s)";
$LANGADDRESSING["reports"][9]="Dispositivos Conectados";
$LANGADDRESSING["reports"][13]="Endereço Reservado";
$LANGADDRESSING["reports"][14]="Usuario";
$LANGADDRESSING["reports"][15]="Fila Vermelha";
$LANGADDRESSING["reports"][16]="Mesmo IP";
$LANGADDRESSING["reports"][20]=" para ";
$LANGADDRESSING["reports"][23]="Reservation";
$LANGADDRESSING["reports"][24]="IP(s) Liberado";
$LANGADDRESSING["reports"][25]="Fila Azul";
$LANGADDRESSING["reports"][26]="Numero de IP(s) Livre(s)";
$LANGADDRESSING["reports"][27]="Numero de IP(s) Reservado(s)";
$LANGADDRESSING["reports"][28]="Número de endereços IP atribuídos (Não duplicados)";
$LANGADDRESSING["reports"][29]="Duplicados";
$LANGADDRESSING["reports"][30]="Ping IP Liberado";
$LANGADDRESSING["reports"][31]="Ping OK";
$LANGADDRESSING["reports"][32]="Ping KO";
$LANGADDRESSING["reports"][34]="IP Real Liberado (Ping=OK)";
$LANGADDRESSING["reports"][36]="Detected subnet list : ";
$LANGADDRESSING["reports"][37]="Invalid data !!";
$LANGADDRESSING["reports"][38]="First IP";
$LANGADDRESSING["reports"][39]="Last IP";

$LANGADDRESSING["profile"][0] = "Gerenciar Permissões";
$LANGADDRESSING["profile"][1] = "$title";
$LANGADDRESSING["profile"][2] = "Setup";
$LANGADDRESSING["profile"][3] = "Gerar Tabela";
$LANGADDRESSING["profile"][4] = "Lista de Perfis que estão configurados";

$LANGADDRESSING["setup"][1] = "Setup do Plugin ".$title."";
$LANGADDRESSING["setup"][3] = "Instalar $title plugin ";
$LANGADDRESSING["setup"][4] = "Atualizar $title plugin para versão";
$LANGADDRESSING["setup"][5] = "Desinstalar $title plugin";
$LANGADDRESSING["setup"][7] = "Aviso, se você desinstalar o plugin será irreversivel.<br> Você irá perder todos os dados.";
$LANGADDRESSING["setup"][8]="Detectado um problema com a Range de IP";
$LANGADDRESSING["setup"][10]="Mostrar";
$LANGADDRESSING["setup"][11]="Atribuir IP";
$LANGADDRESSING["setup"][12]="Ip Livre";
$LANGADDRESSING["setup"][13]="Mesmo IP";
$LANGADDRESSING["setup"][14]="IP Reservado";
$LANGADDRESSING["setup"][15]="Sim";
$LANGADDRESSING["setup"][16]="Não";
$LANGADDRESSING["setup"][17] = "Instruções";
$LANGADDRESSING["setup"][18] = "FAQ";
$LANGADDRESSING["setup"][19] = "Sistem";
$LANGADDRESSING["setup"][20] = "Linux ping";
$LANGADDRESSING["setup"][21] = "Windows";
$LANGADDRESSING["setup"][22]="Usar Ping";
$LANGADDRESSING["setup"][24]="Rede Padrão";
$LANGADDRESSING["setup"][25] = "Linux fping";
$LANGADDRESSING["setup"][26] = "Merci de vous placer sur l'entité racine (voir tous)";
$LANGADDRESSING["setup"][27] = "Problem when adding, required fields are not here";

?>