<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 addressing plugin for GLPI
 Copyright (C) 2009-2016 by the addressing Development Team.

 https://github.com/pluginsGLPI/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with addressing. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Alban Lesellier
// ----------------------------------------------------------------------


include('../../../inc/includes.php');

header("Content-Type: application/json; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (!isset($_POST['addressing_id'])) {
   echo json_encode(0);
}
if (!isset($_POST['ipname'])) {
   echo json_encode(0);
}

$addressing_id = $_POST['addressing_id'];
$ipname        = $_POST['ipname'];
$content       = Toolbox::addslashes_deep($_POST['contentC']);

$ipcomment = new PluginAddressingIpcomment();
if ($ipcomment->getFromDBByCrit(['plugin_addressing_addressings_id' => $addressing_id, 'ipname' => $ipname])) {
   $ipcomment->update(['id' => $ipcomment->getID(), 'comments' => $content]);
} else {
   $ipcomment->add(['plugin_addressing_addressings_id' => $addressing_id, 'ipname' => $ipname, 'comments' => $content]);
}


echo json_encode(0);

