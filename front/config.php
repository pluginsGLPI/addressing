<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 addressing plugin for GLPI
 Copyright (C) 2009-2022 by the addressing Development Team.

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

use GlpiPlugin\Addressing\Addressing;
use GlpiPlugin\Addressing\Config;

if (Plugin::isPluginActive("addressing")) {
    $Config = new Config();

    Session::checkRight("config", UPDATE);

    if (isset($_POST["update"])) {
        $Config->update($_POST);
        Html::back();
    } else {
        Html::header(Addressing::getTypeName(2), '', "tools", Addressing::class, "addressing");
        $Config->showForm(1);
        Html::footer();
    }
} else {
    Html::header(__('Setup'), '', "config", "plugin");
    echo "<div class='alert alert-important alert-warning d-flex'>";
    echo "<b>" . __('Please activate the plugin', 'addressing') . "</b></div>";
    Html::footer();
}
