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

namespace GlpiPlugin\Addressing;

use CommonDBTM;

/**
 * Class IpComment
 */
class IpComment extends CommonDBTM
{
    public static string $rightname = "plugin_addressing";

    public static function getTypeName($nb = 0)
    {

        return _n('IP Addressing', 'IP Addressing', $nb, 'addressing');
    }

    /*
     * The table has no entities_id, so checkEntity() is a no-op and can() would reduce to the
     * global plugin_addressing right: the generic front/ipComment.form.php route of the core
     * would then read, rewrite or purge the comments of any entity's ranges. Comments are only
     * written by ajax/ipcomment.php, which checks the parent range itself and never calls
     * can() on this class, so no generic access is granted at all.
     */
    public static function canView(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canUpdate(): bool
    {
        return false;
    }

    public static function canDelete(): bool
    {
        return false;
    }

    public static function canPurge(): bool
    {
        return false;
    }
}
