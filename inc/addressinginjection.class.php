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
 * Class PluginAddressingAddressingInjection
 */
#[AllowDynamicProperties]
class PluginAddressingAddressingInjection extends PluginAddressingAddressing implements PluginDatainjectionInjectionInterface
{
    public static function getTable($classname = null)
    {
        return PluginAddressingAddressing::getTable();
    }

    /**
     * @return bool
     */
    public function isPrimaryType()
    {
        return true;
    }

    /**
     * @return array
     */
    public function connectedTo()
    {
        return [];
    }

    /**
     * @param string $primary_type
     * @return array
     */
    public function getOptions($primary_type = '')
    {

        $tab = Search::getOptions(get_parent_class($this));

        //Specific to location

        $tab[5]['linkfield'] = 'locations_id';
        //$blacklist = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions();
        //Remove some options because some fields cannot be imported
        $notimportable = [8, 14, 30, 80];
        $options['ignore_fields'] = $notimportable;
        $options['displaytype'] = ["dropdown" => [2, 5, 6, 7],
            "multiline_text" => [3],
            "bool" => [4]];

        $tab = PluginDatainjectionCommonInjectionLib::addToSearchOptions($tab, $options, $this);

        return $tab;
    }

    /**
     * Standard method to add an object into glpi
     * WILL BE INTEGRATED INTO THE CORE IN 0.80
     * @param array| $values
     * @param array| $options
     * @return array array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
     * @internal param fields $values to add into glpi
     * @internal param options $options used during creation
     */
    public function addOrUpdateObject($values = [], $options = [])
    {
        $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
        $lib->processAddOrUpdate();
        return $lib->getInjectionResults();
    }

}
