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

namespace GlpiPlugin\Addressing;

use CommonGLPI;
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use ProfileRight;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Profile
 */
class Profile extends \Profile
{
    public static $rightname = "profile";

    public static function getAllRights()
    {
        $rights = [
            [
                'itemtype' => Addressing::class,
                'label' => __('Generate reports', 'addressing'),
                'field' => Addressing::$rightname,
                'rights' => \Profile::getRightsFor(Addressing::class)
            ],
            [
                'itemtype' => Addressing::class,
                'label' => __('Use ping on equipment form', 'addressing'),
                'field' => PingInfo::$rightname,
                'rights' => [
                    READ => __s('Read'),
                ]
            ]
        ];
        return $rights;
    }


    public static function getIcon()
    {
        return Addressing::getIcon();
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            if ($item->getField('interface') == 'central') {
                return self::createTabEntry(_n('IP Addressing', 'IP Addressing', 2, 'addressing'));
            }
            return '';
        }
        return '';
    }


    /**
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     *
     * @return bool
     */
    public static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {
        if (!$item instanceof \Profile || !self::canView()) {
            return false;
        }

        $profile = new \Profile();
        $profile->getFromDB($item->getID());

        $rights = self::getAllRights();

        $twig = TemplateRenderer::getInstance();
        $twig->display('@addressing/profile.html.twig', [
            'id' => $item->getID(),
            'profile' => $profile,
            'title' => self::getTypeName(Session::getPluralNumber()),
            'rights' => $rights,
        ]);

        return true;
    }


    /**
     * @param $profile
     **/
    public static function addDefaultProfileInfos($profiles_id, $rights)
    {
        $profileRight = new ProfileRight();
        $dbu = new DbUtils();
        foreach ($rights as $right => $value) {
            if (!$dbu->countElementsInTable(
                'glpi_profilerights',
                [
                    "profiles_id" => $profiles_id,
                    "name" => $right
                ]
            )) {
                $myright['profiles_id'] = $profiles_id;
                $myright['name'] = $right;
                $myright['rights'] = $value;
                $profileRight->add($myright);

                //Add right to the current session
                $_SESSION['glpiactiveprofile'][$right] = $value;
            }
        }
    }

    /**
     * @param $ID  integer
     */
    public static function createFirstAccess($profiles_id)
    {
        self::addDefaultProfileInfos(
            $profiles_id,
            [
                'plugin_addressing' => ALLSTANDARDRIGHT,
                'plugin_addressing_use_ping_in_equipment' => '1'
            ]
        );
    }


    /**
     * Initialize profiles
     */
    public static function initProfile()
    {
        global $DB;

        $it = $DB->request([
            'FROM' => 'glpi_profilerights',
            'WHERE' => [
                'profiles_id' => $_SESSION['glpiactiveprofile']['id'],
                'name' => ['LIKE', '%plugin_addressing%'],
            ],
        ]);
        foreach ($it as $prof) {
            if (isset($_SESSION['glpiactiveprofile'])) {
                $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
            }
        }
    }

    public static function migrateProfiles()
    {
        global $DB;

        if (!$DB->tableExists('glpi_plugin_addressing_profiles')) {
            return true;
        }
        $dbu = new DbUtils();
        $profiles = $dbu->getAllDataFromTable('glpi_plugin_addressing_profiles');
        foreach ($profiles as $id => $profile) {
            switch ($profile['addressing']) {
                case 'r':
                    $value = READ;
                    break;
                case 'w':
                    $value = ALLSTANDARDRIGHT;
                    break;
                case 0:
                default:
                    $value = 0;
                    break;
            }
            self::addDefaultProfileInfos($profile['profiles_id'], ['plugin_addressing' => $value]);
            self::addDefaultProfileInfos(
                $profile['profiles_id'],
                [
                    'plugin_addressing_use_ping_in_equipment'
                    => $profile['use_ping_in_equipment']
                ]
            );
        }
    }


    public static function removeRightsFromSession()
    {
        foreach (self::getAllRights() as $right) {
            if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
                unset($_SESSION['glpiactiveprofile'][$right['field']]);
            }
        }
    }
}
