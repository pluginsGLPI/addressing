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

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Addressing\Addressing;

$Addressing = new Addressing();

// Check the right before anything reaches the output. Html::header() used to run first and
// emitted the whole page skeleton -- title carrying the object name, side menu, breadcrumb
// positioned on the plugin entry -- to callers that were about to be refused. A started
// output stream also stops the HTTP exception handler from producing a clean 403.
if (!$Addressing->canView() && !Session::haveRight("config", UPDATE)) {
    throw new AccessDeniedHttpException();
}

Html::header(Addressing::getTypeName(2), '', "tools", Addressing::class);

Search::show(Addressing::class);

Html::footer();
