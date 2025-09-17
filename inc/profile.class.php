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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginAddressingProfile
 */
class PluginAddressingProfile extends Profile
{
    public static $rightname = "profile";

    public static function getAllRights()
    {
        $rights = [
            ['itemtype'  => 'PluginAddressingAddressing',
                  'label'     => __('Generate reports', 'addressing'),
                  'field'     => 'plugin_addressing'],
            ['itemtype'  => 'PluginAddressingAddressing',
                  'label'     => __('Use ping on equipment form', 'addressing'),
                  'field'     => 'plugin_addressing_use_ping_in_equipment']];
        return $rights;
    }

     /**
     * Show profile form
     *
     * @param $items_id integer id of the profile
     * @param $target value url of target
     *
     * @return nothing
     **/
    public function showForm($profiles_id = 0, $openform = true, $closeform = true)
    {
        echo "<div class='firstbloc'>";
        if (($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]))
            && $openform) {
            $profile = new Profile();
            echo "<form method='post' action='".$profile->getFormURL()."'>";
        }

        $profile = new Profile();
        $profile->getFromDB($profiles_id);

        $rights = [
            ['itemtype'  => 'PluginAddressingAddressing',
                  'label'     => __('Generate reports', 'addressing'),
                  'field'     => 'plugin_addressing']];

        $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                        'default_class' => 'tab_bg_2',
                                                        'title'         => __('General')]);

        echo "<table class='tab_cadre_fixehov'>";

        $effective_rights = ProfileRight::getProfileRights(
            $profiles_id,
            ['plugin_addressing_use_ping_in_equipment']
        );
        echo "<tr class='tab_bg_2'>";
        echo "<td width='20%'>".__('Use ping on equipment form', 'addressing')."</td>";
        echo "<td colspan='5'>";
        Html::showCheckbox(['name'    => '_plugin_addressing_use_ping_in_equipment[1_0]',
                                 'checked' => $effective_rights['plugin_addressing_use_ping_in_equipment']]);
        echo "</td></tr>\n";
        echo "</table>";

        if ($canedit
            && $closeform) {
            echo "<div class='center'>";
            echo Html::hidden('id', ['value' => $profiles_id]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update', 'class' => 'btn btn-primary']);
            echo "</div>\n";
            Html::closeForm();
        }
        echo "</div>";
    }

    public static function getIcon()
    {
        return PluginAddressingAddressing::getIcon();
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


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'Profile') {
            $profile = new self();
            $ID      = $item->getField('id');
            //In case there's no right for this profile, create it
            self::addDefaultProfileInfos(
                $item->getID(),
                ['plugin_addressing' => 0,
                      'plugin_addressing_use_ping_in_equipment' => 0]
            );
            $profile->showForm($ID);
        }
        return true;
    }


    /**
     * @param $profile
    **/
    public static function addDefaultProfileInfos($profiles_id, $rights)
    {
        $profileRight = new ProfileRight();
        $dbu          = new DbUtils();
        foreach ($rights as $right => $value) {
            if (!$dbu->countElementsInTable(
                'glpi_profilerights',
                ["profiles_id" => $profiles_id,
                 "name"        => $right]
            )) {
                $myright['profiles_id'] = $profiles_id;
                $myright['name']        = $right;
                $myright['rights']      = $value;
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
            ['plugin_addressing'                       => ALLSTANDARDRIGHT,
             'plugin_addressing_use_ping_in_equipment' => '1']
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
                'name' => ['LIKE', '%plugin_addressing%']
            ]
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
        $dbu      = new DbUtils();
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
                ['plugin_addressing_use_ping_in_equipment'
                       => $profile['use_ping_in_equipment']]
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
