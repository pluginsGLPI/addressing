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
use Dropdown;
use Glpi\Application\View\TemplateRenderer;
use Html;

/**
 * Class Config
 */
class Config extends CommonDBTM
{
    // The web controllers (front/config.php, front/config.form.php) and the menu entry
    // in setup.php all gate this screen on the "config" right, but $rightname is what the
    // generic core paths (legacy REST API, massive actions, data injection) actually
    // enforce. Keeping "plugin_addressing" here would let any technician holding the
    // plugin right rewrite the instance wide settings, ping command included.
    public static $rightname = "config";

    public function showForm($ID, $options = [])
    {
        $this->initForm($ID, $options);
        $is_cloud = defined('GLPI_INSTALL_MODE') && GLPI_INSTALL_MODE === 'CLOUD';
        TemplateRenderer::getInstance()->display(
            '@addressing/config.html.twig',
            [
                'id'       => 1,
                'item'     => $this,
                'config'   => $this->fields,
                'is_cloud' => $is_cloud,
            ],
        );
    }

    public function prepareInputForUpdate($input)
    {
        if (defined('GLPI_INSTALL_MODE') && GLPI_INSTALL_MODE === 'CLOUD') {
            $input['use_ping'] = 0;
        }
        return $input;
    }
}
