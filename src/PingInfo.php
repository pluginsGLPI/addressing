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

use Ajax;
use CommonDBTM;
use CronTask;
use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Addressing\Config;
use GlpiPlugin\Addressing\Report;
use Html;
use Session;

/**
 * Class PingInfo
 */
class PingInfo extends CommonDBTM
{
    public static $rightname = "plugin_addressing";

    /**
     * Largest number of ICMP probes a single ping run may send for one range.
     *
     * updatePingInfos() sends one synchronous probe per address of the range, each
     * waiting up to a second for an answer. On a /16 that is 65536 sequential probes,
     * i.e. roughly 18 hours holding a PHP worker (or a cron slot) and as many outgoing
     * packets. The budget bounds a single run; the remaining addresses are simply left
     * without ping information rather than letting the caller decide how long the scan
     * lasts.
     */
    public const MAX_PING_TARGETS = 1024;

    /**
     * Wall clock budget, in seconds, of a scan started from the interactive endpoint.
     *
     * MAX_PING_TARGETS bounds the number of probes but not their duration: 1024 probes
     * waiting up to a second each still hold a PHP worker and an HTTP slot for roughly
     * seventeen minutes. The full sweep of a range is the job of the UpdatePing cron
     * task, which is the only path allowed to run unbounded.
     */
    public const MANUAL_SCAN_TIME_BUDGET = 60;

    /**
     * Minimum delay, in seconds, between two scans of the same range asked for by hand.
     *
     * Without it the endpoint may be replayed in a loop, which multiplies the outgoing
     * ICMP traffic and the number of workers held at the sole discretion of the caller.
     */
    public const MANUAL_SCAN_COOLDOWN = 300;

    /**
     * Minimum delay, in seconds, between two probes of the same address by the same user.
     *
     * The range scan is bounded by a cooldown and an exclusive lock, but the unit probes had
     * nothing: each call runs a blocking command whose timeout is about a second, and the
     * caller alone decides how often it is replayed. The delay is short enough to stay
     * unnoticed when the report is browsed by hand, and long enough to make a loop pointless.
     */
    public const UNIT_PROBE_COOLDOWN = 5;

    /**
     * Number of seconds still to wait before this user may probe this address again.
     *
     * The stamps live in the session: the quota is per user, which is the granularity that
     * matters here, and it costs neither a table nor a file.
     *
     * @param string $ip address the caller wants to probe
     **/
    public static function getRemainingProbeCooldown(string $ip): int
    {
        $last = $_SESSION['plugin_addressing_last_probes'][$ip] ?? 0;

        return (int) max(0, self::UNIT_PROBE_COOLDOWN - (time() - (int) $last));
    }


    /**
     * Record that this user has just probed this address.
     *
     * @param string $ip address that has been probed
     **/
    public static function registerProbe(string $ip): void
    {
        $now    = time();
        $probes = $_SESSION['plugin_addressing_last_probes'] ?? [];
        if (!is_array($probes)) {
            $probes = [];
        }

        // A stamp older than the cooldown carries no information any more, and the session must
        // not grow by one entry per address of a report that has just been browsed.
        foreach ($probes as $probed_ip => $stamp) {
            if ($now - (int) $stamp >= self::UNIT_PROBE_COOLDOWN) {
                unset($probes[$probed_ip]);
            }
        }
        $probes[$ip] = $now;

        $_SESSION['plugin_addressing_last_probes'] = $probes;
    }


    /**
     * Take the exclusive probe lock of the current user.
     *
     * The cooldown is computed from the last finished probe, so it does not stop N calls fired
     * at the same second. This lock does: a concurrent caller is turned away instead of holding
     * a worker of its own for the whole timeout of the command.
     *
     * @return resource|null the lock handle, or null when a probe is already running
     **/
    public static function acquireProbeLock()
    {
        $handle = fopen(GLPI_TMP_DIR . '/addressing_probe_' . (int) Session::getLoginUserID() . '.lock', 'c');
        if ($handle === false) {
            return null;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return null;
        }

        return $handle;
    }


    /**
     * Reason why withProbeGuards() refused to run the probe.
     */
    public const PROBE_REFUSED_COOLDOWN = 'cooldown';
    public const PROBE_REFUSED_BUSY     = 'busy';

    /**
     * Run a unit probe under the guards every probe path has to apply.
     *
     * Four paths of the plugin trigger a server side probe, and each of them used to carry its
     * own copy of the cooldown and of the exclusive lock. That is exactly how one of them -- the
     * reservation form, reachable by a plain GET -- ended up with neither: nothing tied the
     * guards to the probe itself. They live here now, so a new caller gets them by construction
     * instead of having to remember them.
     *
     * @param string      $ip      address the caller wants to probe
     * @param callable    $probe   run while the lock is held; its return value is passed back
     * @param string|null $refusal set to PROBE_REFUSED_COOLDOWN or PROBE_REFUSED_BUSY when the
     *                             probe is refused, so each caller can word its own answer
     *
     * @return mixed what $probe returned, or null when the probe was refused
     **/
    public static function withProbeGuards(string $ip, callable $probe, ?string &$refusal = null)
    {
        $refusal = null;

        if (self::getRemainingProbeCooldown($ip) > 0) {
            $refusal = self::PROBE_REFUSED_COOLDOWN;

            return null;
        }

        $lock = self::acquireProbeLock();
        if ($lock === null) {
            $refusal = self::PROBE_REFUSED_BUSY;

            return null;
        }
        self::registerProbe($ip);

        try {
            return $probe();
        } finally {
            self::releaseScanLock($lock);
        }
    }


    /**
     * Number of seconds still to wait before this range may be scanned by hand again.
     *
     * updateAnAddressing() rewrites every ping information row of the range, so the most
     * recent ping_date is the date of the last complete run.
     *
     * @param Addressing $addressing range the caller wants to scan
     **/
    public static function getRemainingScanCooldown(Addressing $addressing): int
    {
        global $DB;

        $last = $DB->request([
            'SELECT' => ['ping_date'],
            'FROM'   => self::getTable(),
            'WHERE'  => ['plugin_addressing_addressings_id' => $addressing->getID()],
            'ORDER'  => ['ping_date DESC'],
            'LIMIT'  => 1,
        ])->current();

        if (!is_array($last) || empty($last['ping_date'])) {
            return 0;
        }

        $elapsed = time() - strtotime($last['ping_date']);

        return (int) max(0, self::MANUAL_SCAN_COOLDOWN - $elapsed);
    }


    /**
     * Take the exclusive scan lock of a range.
     *
     * The cooldown is computed from the last finished run, so it does not stop N calls
     * started at the same second. This lock does: concurrent callers are turned away
     * instead of each holding a worker and sending their own copy of the ICMP traffic.
     * The handle must stay referenced until the scan is over.
     *
     * @param Addressing $addressing range to lock
     *
     * @return resource|null the lock handle, or null when the range is already scanned
     **/
    public static function acquireScanLock(Addressing $addressing)
    {
        $handle = fopen(GLPI_TMP_DIR . '/addressing_scan_' . $addressing->getID() . '.lock', 'c');
        if ($handle === false) {
            return null;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return null;
        }

        return $handle;
    }


    /**
     * Release a lock taken by acquireScanLock() or acquireProbeLock().
     *
     * @param resource|null $handle handle returned by acquireScanLock() or acquireProbeLock()
     **/
    public static function releaseScanLock($handle): void
    {
        if (is_resource($handle)) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }


    public static function getTypeName($nb = 0)
    {

        return _n('IP Addressing', 'IP Addressing', $nb, 'addressing');
    }

    /**
     * @param $name
     **/
    public static function cronInfo($name)
    {

        switch ($name) {
            case 'UpdatePing':
                return [
                    'description' => __('Launch ping for each ip report', 'addressing'),
                ];
        }
        return [];
    }

    /**
     * Cron action on addressing : auto ping
     *
     * @param $task for log, if NULL display
     *
     **/
    public static function cronUpdatePing(CronTask $task)
    {

        // The default value advertised a call without a task, which addVolume() would then
        // dereference on null and turn into a fatal error. CronTask::launch() always hands the
        // task over, so the contract is simply declared as it really is.
        $cron_status = 1;
        $self        = new self();
        $vol         = $self->updateAllAddressing();
        $task->addVolume($vol);
        //      $task->log(Dropdown::getDropdownName("glpi_entities",
        //                                           $entity) . ":  $message\n");

        return $cron_status;
    }


    /**
     * Tell whether a range may be scanned with ICMP probes.
     *
     * Two settings gate a scan: the global "use_ping" switch of the plugin configuration and
     * the "use_ping" flag of the range itself. The cron task honoured the second one through
     * its find() criteria, the manual endpoint honoured neither, and nothing read the first
     * one at all: turning the master switch off stopped no probe. Both are read here, at the
     * single point every scan goes through.
     *
     * @param Addressing $addressing Range about to be scanned
     *
     * @return bool
     */
    public static function isRangeScanEnabled(Addressing $addressing): bool
    {
        if (empty($addressing->fields['use_ping'])) {
            return false;
        }

        $config = new Config();
        if (!$config->getFromDB(1)) {
            return false;
        }

        return !empty($config->fields['use_ping']);
    }

    public function updateAllAddressing()
    {
        // memory_limit is deliberately NOT lifted here: a cron task must fail cleanly
        // on an oversized range instead of growing until the kernel OOM killer takes
        // down an arbitrary process of the host (typically MySQL). The range size is
        // now bounded by Addressing::MAX_RANGE_SIZE and the scan by MAX_PING_TARGETS.
        $old_execution        = ini_set("max_execution_time", "0");
        $addressing           = new Addressing();
        $addressings          = $addressing->find(['is_deleted' => 0,
            'use_ping'   => 1]);
        $total_ping_responses = 0;
        foreach ($addressings as $addressing_array) {
            $addressing->getFromDB($addressing_array['id']);
            // No deadline: the cron task is the path that may legitimately run long.
            $ping_responses       = $this->updateAnAddressing($addressing);
            $total_ping_responses += $ping_responses;
        }
        ini_set("max_execution_time", $old_execution);
        return $total_ping_responses;
    }

    /**
     * Refresh the ping information of a range.
     *
     * @param Addressing  $addressing range to scan
     * @param float|null  $deadline   microtime(true) value past which the scan stops,
     *                                null for an unbounded run (cron task only)
     **/
    public function updateAnAddressing(Addressing $addressing, ?float $deadline = null)
    {
        // Single point of entry of the cron task and of the manual launch: emit nothing when
        // ping is disabled, be it globally or for this range.
        if (!self::isRangeScanEnabled($addressing)) {
            return 0;
        }

        $ipdeb = sprintf("%u", ip2long($addressing->fields["begin_ip"]));
        $ipfin = sprintf("%u", ip2long($addressing->fields["end_ip"]));

        $result                     = $addressing->compute(0, ['ipdeb'    => $ipdeb,
            'ipfin'    => $ipfin,
            'entities' => $addressing->fields['entities_id']]);
        $plugin_addressing_pinginfo = new PingInfo();
        $plugin_addressing_pinginfo->deleteByCriteria(['plugin_addressing_addressings_id' => $addressing->getID()]);

        $ping_responses = $this->updatePingInfos($result, $addressing, $deadline);

        return $ping_responses;
    }

    /**
     * @param array       $result   addresses of the range, as computed by Addressing::compute()
     * @param Addressing  $Addressing range being scanned
     * @param float|null  $deadline microtime(true) value past which the scan stops
     **/
    private function updatePingInfos($result, Addressing $Addressing, ?float $deadline = null)
    {

        // Get config
        $Config         = new Config();
        $Ping_Equipment = new Ping_Equipment();
        $Config->getFromDB('1');
        $system = $Config->fields["used_system"];

        $ping_response = 0;
        $probes        = 0;

        $plugin_addressing_pinginfo = new PingInfo();

        foreach ($result as $num => $lines) {
            // Bound the scan: each iteration spawns a blocking ICMP probe, so the cost
            // of this loop is linear in the size of the range and entirely driven by
            // caller-supplied boundaries. Stop once the budget is spent.
            if ($probes >= self::MAX_PING_TARGETS) {
                if (!isCommandLine()) {
                    Session::addMessageAfterRedirect(
                        sprintf(
                            __('Ping stopped after %d addresses', 'addressing'),
                            self::MAX_PING_TARGETS,
                        ),
                        false,
                        WARNING,
                    );
                }
                break;
            }
            // Second bound, on the wall clock this time: a probe against a filtered
            // address costs the full timeout, so the number of probes alone says
            // nothing about how long the caller holds the worker.
            if ($deadline !== null && microtime(true) >= $deadline) {
                if (!isCommandLine()) {
                    Session::addMessageAfterRedirect(
                        sprintf(
                            __('Ping stopped after %d seconds', 'addressing'),
                            self::MANUAL_SCAN_TIME_BUDGET,
                        ),
                        false,
                        WARNING,
                    );
                }
                break;
            }
            $probes++;

            $ip = Report::string2ip(substr($num, 2));

            $ping_value                               = $Ping_Equipment->ping($system, $ip, "true");
            $data                                     = [];
            $data['plugin_addressing_addressings_id'] = $Addressing->getID();
            $data['ipname']                           = $num;

            $data['itemtype']      = isset($lines['0']['itemtype']) ? $lines['0']['itemtype'] : "";
            $data['items_id']      = isset($lines['0']['on_device']) ? $lines['0']['on_device'] : "0";
            $data['ping_response'] = $ping_value ?? 0;
            $data['ping_date']     = date('Y-m-d H:i:s');

            $plugin_addressing_pinginfo->add($data);

            if (!is_null($ping_value)) {
                $ping_response++;
            }
        }
        return $ping_response;
    }

    public static function getPingResponseForItem($params)
    {

        $ping_right = Session::haveRight('plugin_addressing_use_ping_in_equipment', READ);
        $item       = $params['item'];

        if ($ping_right
          && in_array($item->getType(), Addressing::getTypes()) && $item->getID() > 0) {
            $items_id                   = $item->getID();
            $itemtype                   = $item->getType();
            $plugin_addressing_pinginfo = new PingInfo();

            $ping_action = 0;
            $ping_value  = 0;
            if ($pings = $plugin_addressing_pinginfo->find(['itemtype' => $itemtype,
                'items_id' => $items_id])) {
                foreach ($pings as $ping) {
                    $ping_value = $ping['ping_response'];
                    $ping_date  = $ping['ping_date'];
                    $ipname     = $ping['ipname'];
                }
                $ping_action = 1;
            }

            if ($ping_action == 0) {
                $content = "<i class=\"ti ti-question\" style='color: orange;font-size: 2em;' title=\"" . __(
                    "Automatic action has not be launched",
                    'addressing',
                ) . "\">
                    </i><br>" . __("Ping informations not available", 'addressing');
            } else {
                if ($ping_value == 1) {
                    $content = "<i class=\"ti ti-square-check\" style='color: darkgreen;font-size: 2em;' title='" . __(
                        "Last ping attempt",
                        'addressing',
                    ) . " : "
                          . Html::convDateTime($ping_date) . "'></i><br>" . __(
                              "Last ping attempt",
                              'addressing',
                          ) . " : "
                          . Html::convDateTime($ping_date);
                    $content .= "<br>" . __('IP') . "&nbsp;" . $ip = Report::string2ip(
                        substr($ipname, 2),
                    );
                } else {
                    $content = "<i class=\"ti ti-square-x\" style='color: darkred;font-size: 2em;' title='" . __(
                        "Last ping attempt",
                        'addressing',
                    ) . " : "
                          . Html::convDateTime($ping_date) . "'></i><br>" . __(
                              "Last ping attempt",
                              'addressing',
                          ) . " : "
                          . Html::convDateTime($ping_date);
                    $content .= "<br>" . __('IP') . "&nbsp;" . $ip = Report::string2ip(substr(
                        $ipname,
                        2,
                    ));
                }
            }

            $rand = mt_rand();

            TemplateRenderer::getInstance()->display('@addressing/pinginfo.html.twig', [
                'content' => $content,
                'items_id' => $items_id,
                'itemtype' => $itemtype,
                'rand' => $rand,
                'root_dir' => PLUGIN_ADDRESSING_WEBDIR,
            ]);
        }
    }


    /**
     * @param \CommonDBTM $item
     */
    public static function cleanForItem(CommonDBTM $item)
    {

        $temp = new self();
        $temp->deleteByCriteria(
            ['itemtype' => $item->getType(),
                'items_id' => $item->getField('id')],
        );
    }
}
