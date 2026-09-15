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

//Options for GLPI 0.71 and newer : need slave db to access the report
use GlpiPlugin\Addressing\Addressing;
use GlpiPlugin\Addressing\Report;

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

if (!isset($_GET["start"])) {
    $_GET["start"] = 0;
}

if (!isset($_GET["export"])) {
    $_GET["export"] = false;
}

// Check the right BEFORE emitting any output: check() throws when access is denied,
// and once Html::header() has written the page head and navigation bar GLPI can no
// longer render a proper error page - the client would get a truncated document
// instead, which also makes range ids easier to enumerate.
$addressing = new Addressing();
$addressing->check((int) ($_GET['id'] ?? 0), READ);

// A non HTML display type makes the report answer with a downloadable file (CSV, ODS, PDF).
// The page head was written first, so the whole GLPI layout was captured inside that file and
// the export was unusable. Decide before producing any output; an unsupported value throws
// here, while nothing has been sent yet and a proper error page can still be rendered.
$is_html_output = Report::isHtmlOutput($_GET['display_type'] ?? Search::HTML_OUTPUT);

if ($is_html_output) {
    Html::header(Addressing::getTypeName(2), '', "tools", Addressing::class);
}

$addressing->showReport($_GET);

if ($is_html_output) {
    Html::footer();
}
