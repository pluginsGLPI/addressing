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

use Glpi\Plugin\Hooks;
use GlpiPlugin\Addressing\Addressing;
use GlpiPlugin\Addressing\PingInfo;
use GlpiPlugin\Addressing\Profile;

define('PLUGIN_ADDRESSING_VERSION', '3.1.1');

if (!defined("PLUGIN_ADDRESSING_DIR")) {
    define("PLUGIN_ADDRESSING_DIR", Plugin::getPhpDir("addressing"));
    define("PLUGIN_ADDRESSING_DIR_NOFULL", Plugin::getPhpDir("addressing", false));
}

// Init the hooks of the plugins -Needed
function plugin_init_addressing()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['addressing'] = true;

    $PLUGIN_HOOKS['change_profile']['addressing'] = [Profile::class, 'initProfile'];

    Plugin::registerClass(
        Profile::class,
        ['addtabon' => ['Profile']]
    );

    if (Session::getLoginUserID()) {
        if (Session::haveRight('plugin_addressing', READ)) {
            $PLUGIN_HOOKS["menu_toadd"]['addressing'] = ['tools'  => Addressing::class];
        }

        if (Session::haveRight('plugin_addressing', UPDATE)) {
            $PLUGIN_HOOKS['use_massive_action']['addressing']   = 1;
        }

        // Config page
        if (Session::haveRight("config", UPDATE)) {
            $PLUGIN_HOOKS['config_page']['addressing']             = 'front/config.php';
        }

        $PLUGIN_HOOKS['post_item_form']['addressing'] = [PingInfo::class,
            'getPingResponseForItem'];

        // Add specific files to add to the header : javascript or css
        if (isset($_SESSION['glpiactiveprofile']['interface'])
            && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
            $PLUGIN_HOOKS[Hooks::ADD_CSS]['addressing']        = "addressing.css";
            $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['addressing'] = 'addressing.js';
        }
    }
}


// Get the name and the version of the plugin - Needed
function plugin_version_addressing()
{
    return [
        'name'           => _n('IP Addressing', 'IP Addressing', 2, 'addressing'),
        'version'        => PLUGIN_ADDRESSING_VERSION,
        'author'         => 'Gilles Portheault, Xavier Caillaud, Remi Collet, Nelly Mahu-Lasson',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://github.com/pluginsGLPI/addressing',
        'requirements'   => [
            'glpi' => [
                'min' => '11.0',
                'max' => '12.0',
            ],
        ]];
}
