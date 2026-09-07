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

// ----------------------------------------------------------------------
// Original Author of file: Alban Lesellier
// ----------------------------------------------------------------------

use Glpi\Exception\Http\AccessDeniedHttpException;
use GlpiPlugin\Addressing\Addressing;
use GlpiPlugin\Addressing\IpComment;
use GlpiPlugin\Addressing\Report;

use function Safe\json_encode;

Session::checkRight('plugin_addressing', UPDATE);

header("Content-Type: application/json; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST['addressing_id'], $_POST['ipname'])) {
    echo json_encode(0);
    return;
}

$addressing_id = (int) $_POST['addressing_id'];
$ipname        = $_POST['ipname'];
$content       = $_POST['contentC'] ?? '';

// The global right is not entity-aware: confirm the caller may act on this
// addressing range (entity perimeter) before writing an IP comment to it.
$addressing = new Addressing();
if ($addressing_id <= 0 || !$addressing->can($addressing_id, UPDATE)) {
    throw new AccessDeniedHttpException();
}

// ipname is the report's row key and is supplied by the caller. The UPDATE right checked
// above only proves this range is writable, not that this key belongs to it. Reject any
// value that is not the "IP<unsigned long>" form built by Addressing::compute(), and any
// address outside the range: otherwise a caller holding UPDATE on one of their own ranges
// can store rows under arbitrary keys, which are never rendered back (the report joins on
// the addresses the range actually contains) and are left behind when the range is deleted.
// The digits are bounded to the unsigned 32-bit range before conversion: string2ip()
// hands its argument to long2ip(), which raises an uncaught TypeError as soon as the
// value no longer fits an int.
if (!preg_match('/^IP(\d{1,10})$/', (string) $ipname, $matches)
    || (int) $matches[1] > 4294967295) {
    throw new AccessDeniedHttpException();
}
$ip = Report::string2ip((int) $matches[1]);
if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || !$addressing->containsIp($ip)) {
    throw new AccessDeniedHttpException();
}
// Rebuild the key from the validated address instead of trusting the posted string.
$ipname = 'IP' . $matches[1];

$ipcomment = new IpComment();
if ($ipcomment->getFromDBByCrit(['plugin_addressing_addressings_id' => $addressing_id, 'ipname' => $ipname])) {
    $ipcomment->update(['id' => $ipcomment->getID(), 'comments' => $content]);
} else {
    $ipcomment->add(['plugin_addressing_addressings_id' => $addressing_id, 'ipname' => $ipname, 'comments' => $content]);
}

echo json_encode(0);
