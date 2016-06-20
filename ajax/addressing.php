<?php
/*
 * @version $Id: addressing.php 155 2013-01-29 10:20:31Z  $
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

include ('../../../inc/includes.php');

Session::checkLoginUser();

Html::header_nocache();
if(isset($_POST['action']) && $_POST['action'] == 'isName'){
   $item = new $_POST['type']();
   $datas = $item->find("`name` = '".$_POST['name']."'");
   if(count($datas) > 0){
      echo json_encode(true);
   }else{
      echo json_encode(false);
   }
}elseif(isset($_POST['action']) && $_POST['action'] == 'viewFilter'){
   if (isset($_POST['items_id'])
       && isset($_POST["id"])) {
      $filter = new PluginAddressingFilter();
      $filter->showForm($_POST["id"], array('items_id' => $_POST['items_id']));
   } else {
      _e('Access denied');
   }
}elseif(isset($_POST['action']) && $_POST['action'] == 'networkip'){
   IPNetwork::showIPNetworkProperties($_POST['entities_id']);
}
