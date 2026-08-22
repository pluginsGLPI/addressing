<?php

/**
 * -------------------------------------------------------------------------
 * addressing plugin for GLPI
 * Copyright (C) 2016-2026 by the addressing Development Team.
 *
 * https://github.com/pluginsGLPI/addressing
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of addressing.
 *
 * addressing is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * addressing is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with addressing. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

/**
 * PHPStan-only stub for the optional `datainjection` plugin dependency.
 *
 * This plugin integrates with datainjection only when that plugin is installed.
 * The stub lets static analysis resolve the referenced symbols without relying
 * on the datainjection plugin being present on disk, so the analysis stays
 * independent of the deployment layout (marketplace/, plugins/, or absent).
 * It is never loaded at runtime and is stripped from the release archive (tools/).
 *
 * Signatures and @return phpdocs mirror the real datainjection classes so the
 * existing PHPStan baseline keeps matching.
 */

interface PluginDatainjectionInjectionInterface
{
    /**
     * @return boolean a boolean
     */
    public function isPrimaryType();

    /**
     * @return array of GLPI types
     */
    public function connectedTo();

    /**
     * @param string $primary_type    (default '')
     *
     * @return array of search options, as defined in each commondbtm object
     */
    public function getOptions($primary_type = '');

    /**
     * @param array $values    array fields to add into glpi
     * @param array $options   array options used during creation
     *
     * @return array of IDs of newly created objects
     */
    public function addOrUpdateObject($values = [], $options = []);
}

class PluginDatainjectionCommonInjectionLib
{
    public function __construct($injectionClass, $values, $options = []) {}

    public static function getBlacklistedOptions($itemtype): array
    {
        return [];
    }

    public static function addToSearchOptions($tab, $options, $injectionClass): array
    {
        return [];
    }

    public function processAddOrUpdate(): void {}

    public function getInjectionResults(): array
    {
        return [];
    }
}

class PluginDatainjectionModel
{
    public static function clean($input): void {}
}
