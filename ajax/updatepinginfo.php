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
use GlpiPlugin\Addressing\PingInfo;

Session::checkRight('plugin_addressing', UPDATE);
header("Content-Type: application/json; charset=UTF-8");
Html::header_nocache();

if (!isset($_POST['addressing_id'])) {
    echo 0;
    return;
}
$addressing_id = (int) $_POST['addressing_id'];

// The global right is not entity-aware: confirm the caller may act on this
// addressing range (entity perimeter) before rewriting its ping information.
$addressing = new Addressing();
if ($addressing_id <= 0 || !$addressing->can($addressing_id, UPDATE)) {
    throw new AccessDeniedHttpException();
}

// A scan holds a PHP worker and an HTTP slot for its whole duration while sending one
// blocking ICMP probe per address of the range. Lifting max_execution_time to zero here
// handed that duration to the caller: any profile with the plugin UPDATE right could
// replay this endpoint on a large range and exhaust the worker pool of the instance,
// with the outgoing traffic aimed at a network of its choosing. The full sweep belongs
// to the UpdatePing cron task; what is done here is bounded in time, exclusive per
// range and rate limited.
// The cron task skips the ranges whose ping is disabled, globally or individually. The manual
// launch honoured neither switch, so it was a way around the setting; refuse it here too.
if (!PingInfo::isRangeScanEnabled($addressing)) {
    Session::addMessageAfterRedirect(
        __s('Ping is disabled for this IP range', 'addressing'),
        false,
        WARNING,
    );
    echo 1;
    return;
}

$cooldown = PingInfo::getRemainingScanCooldown($addressing);
if ($cooldown > 0) {
    Session::addMessageAfterRedirect(
        sprintf(
            __s('The ping of this range has just been run, please wait %d seconds', 'addressing'),
            $cooldown,
        ),
        false,
        WARNING,
    );
    // Answer 1 all the same: the caller reloads the page, which is where the message
    // queued above is displayed.
    echo 1;
    return;
}

$lock = PingInfo::acquireScanLock($addressing);
if ($lock === null) {
    Session::addMessageAfterRedirect(
        __s('A ping of this range is already running', 'addressing'),
        false,
        WARNING,
    );
    echo 1;
    return;
}

// A margin over the budget of the scan itself so that the loop stops on its own
// deadline, and reports it, rather than being cut down mid write by the engine.
$old_execution = ini_set("max_execution_time", (string) (PingInfo::MANUAL_SCAN_TIME_BUDGET + 30));
$pingInfo = new PingInfo();
$pingInfo->updateAnAddressing($addressing, microtime(true) + PingInfo::MANUAL_SCAN_TIME_BUDGET);
ini_set("max_execution_time", $old_execution);
PingInfo::releaseScanLock($lock);

echo 1;
