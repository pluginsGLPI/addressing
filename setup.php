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

define('PLUGIN_ADDRESSING_VERSION', '3.0.2');

if (!defined("PLUGIN_ADDRESSING_DIR")) {
    define("PLUGIN_ADDRESSING_DIR", Plugin::getPhpDir("addressing"));
    define("PLUGIN_ADDRESSING_DIR_NOFULL", Plugin::getPhpDir("addressing", false));
    define("PLUGIN_ADDRESSING_WEBDIR", Plugin::getWebDir("addressing"));
}

// Init the hooks of the plugins -Needed
function plugin_init_addressing()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['addressing'] = true;

    $PLUGIN_HOOKS['change_profile']['addressing'] = ['PluginAddressingProfile', 'initProfile'];

    Plugin::registerClass(
        'PluginAddressingProfile',
        ['addtabon' => ['Profile']]
    );

    if (Session::getLoginUserID()) {
        if (Session::haveRight('plugin_addressing', READ)) {
            $PLUGIN_HOOKS["menu_toadd"]['addressing'] = ['tools'  => 'PluginAddressingAddressing'];
        }

        if (Session::haveRight('plugin_addressing', UPDATE)) {
            $PLUGIN_HOOKS['use_massive_action']['addressing']   = 1;
        }

        // Config page
        if (Session::haveRight("config", UPDATE)) {
            $PLUGIN_HOOKS['config_page']['addressing']             = 'front/config.php';
        }

        $PLUGIN_HOOKS['post_item_form']['addressing'] = ['PluginAddressingPinginfo',
           'getPingResponseForItem'];

        // Add specific files to add to the header : javascript or css
        if (isset($_SESSION['glpiactiveprofile']['interface'])
            && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
            $PLUGIN_HOOKS['add_css']['addressing']        = "addressing.css";
            $PLUGIN_HOOKS["javascript"]['addressing']     = [PLUGIN_ADDRESSING_DIR_NOFULL."/addressing.js"];
            $PLUGIN_HOOKS['add_javascript']['addressing'] = 'addressing.js';
        }
    }
}


// Get the name and the version of the plugin - Needed
function plugin_version_addressing()
{
    return [
       'name'           => _n('IP Adressing', 'IP Adressing', 2, 'addressing'),
       'version'        => PLUGIN_ADDRESSING_VERSION,
       'author'         => 'Gilles Portheault, Xavier Caillaud, Remi Collet, Nelly Mahu-Lasson',
       'license'        => 'GPLv2+',
       'homepage'       => 'https://github.com/pluginsGLPI/addressing',
       'requirements'   => [
          'glpi' => [
             'min' => '10.0',
             'max' => '11.0',
          ]
       ]];
}
