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
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Exception\Http\BadRequestHttpException;
use Glpi\Search\Output\AbstractSearchOutput;
use Glpi\Search\Output\HTMLSearchOutput;
use Glpi\Search\SearchEngine;
use Html;
use NetworkEquipment;
use NetworkPort;
use Search;
use Toolbox;
use User;

/**
 * Class Report
 */
class Report extends CommonDBTM
{
    public static string $rightname = "plugin_addressing";

    public static function getTypeName($nb = 0)
    {
        return __('Report');
    }

    /**
     * Confront a requested display type with the output modes the core actually supports.
     *
     * @param mixed $output_type
     */
    private static function validateOutputType($output_type): int
    {
        $allowed_output_types = [
            Search::GLOBAL_SEARCH,
            Search::HTML_OUTPUT,
            Search::PDF_OUTPUT_LANDSCAPE,
            Search::PDF_OUTPUT_PORTRAIT,
            Search::CSV_OUTPUT,
            Search::ODS_OUTPUT,
            Search::XLSX_OUTPUT,
            Search::NAMES_OUTPUT,
        ];

        if (!is_numeric($output_type) || !in_array((int) $output_type, $allowed_output_types, true)) {
            throw new BadRequestHttpException();
        }

        return (int) $output_type;
    }

    /**
     * Tell whether a requested display type renders the page, or a downloadable file.
     *
     * The entry point has to know this before emitting anything: the CSV, ODS and PDF writers
     * send their own headers and body, so a page head written beforehand ends up captured
     * inside the exported file.
     *
     * @param mixed $display_type
     */
    public static function isHtmlOutput($display_type): bool
    {
        $output = SearchEngine::getOutputForLegacyKey(self::validateOutputType($display_type));

        return $output instanceof HTMLSearchOutput;
    }

    public function displayReport(&$result, Addressing $Addressing, array $values, array $ping_status = [])
    {
        // $values carries the request parameters, so display_type is caller controlled and has to
        // be validated before it reaches the core: getOutputForLegacyKey(int $output_type) raises a
        // TypeError on a non numeric value and a RuntimeException on a key outside the enumeration,
        // neither of which is caught, turning an arbitrary parameter into a 500 with a stack trace.
        $output = SearchEngine::getOutputForLegacyKey(
            self::validateOutputType($values['display_type'] ?? Search::HTML_OUTPUT),
        );

        $report = $this->getReportData($result, $Addressing, $ping_status);

        if ($output instanceof HTMLSearchOutput) {
            TemplateRenderer::getInstance()->display('@addressing/report_ip_list.html.twig', $report);
        } else {
            self::displayExport($output, $report);
        }

        return $report['ping_response'];
    }

    /**
     * Build the rows of the IP report once, for the page and for the exports alike.
     *
     * The page and the exports used to be written side by side in every branch, each with its
     * own idea of the columns: some export rows had one cell too many or too few for their
     * header, and the addresses answering the ping were never exported at all. Both now read
     * the same rows.
     *
     * @param array<int|string, array<int, array<string, mixed>>> $result      compute() output, keyed by "IP<long>"
     * @param array<int, mixed>                                   $ping_status [show the "no response" rows, show the "got a response" rows]
     *
     * @return array{addressing_id: int, use_ping: bool, comment_url: string, ping_response: int, rows: list<array<string, mixed>>}
     */
    public function getReportData(array $result, Addressing $Addressing, array $ping_status = []): array
    {
        // The flag of the range is only half of the decision: the configuration of the plugin
        // carries a global switch, labelled "Use Ping on IP ranges", which this report ignored.
        // Ask the single decision point instead, so the report, the cron task and the manual
        // scan all answer the same question.
        $use_ping      = PingInfo::isRangeScanEnabled($Addressing);
        $see_ping_on   = ($ping_status[0] ?? 1) == 1;
        $see_ping_off  = ($ping_status[1] ?? 1) == 1;
        $addressing_id = $Addressing->getID();
        $ping_response = 0;
        $rows          = [];

        $user = new User();
        $dbu  = new DbUtils();

        foreach ($result as $num => $lines) {
            $num = (string) $num;
            $ip  = self::string2ip(substr($num, 2));

            if (count($lines)) {
                $displayed = count($lines) > 1 ? $Addressing->fields['double_ip'] : $Addressing->fields['alloted_ip'];
                if (!$displayed) {
                    continue;
                }

                foreach ($lines as $line) {
                    // itemtype comes from the glpi_networkports rows: a plugin uninstalled
                    // without cleaning its ports leaves a class name that no longer exists, and
                    // instantiating it would raise an uncaught Error making the whole report
                    // unreachable. Skip the row instead, as every other dynamic instantiation in
                    // this plugin already does.
                    $item = getItemForItemtype($line['itemtype']);
                    if (!($item instanceof CommonDBTM)) {
                        continue;
                    }

                    $is_reserved = $Addressing->fields['reserved_ip']
                        && str_contains((string) $line['pname'], 'reserv');

                    $row = self::newRow($Addressing, $num, $ip);
                    if ($is_reserved) {
                        $row['class'] = 'plugin_addressing_ip_reserved';
                    } elseif (count($lines) > 1) {
                        $row['class'] = 'plugin_addressing_ip_double';
                    } else {
                        $row['class'] = count($rows) % 2 === 0 ? 'tab_bg_1' : 'tab_bg_2';
                    }

                    // The icon opens an iframe that makes the server probe the address, so it
                    // follows the ping setting, as the manual launch button of the report does.
                    if ($use_ping) {
                        $row['ping_url'] = self::getPingUrl($ip);
                    }

                    // Defence in depth on top of the itemtype filtering done in
                    // Addressing::compute(): when the profile cannot read the asset, degrade
                    // every detail it carries to a neutral label instead of only dropping the
                    // link to its form. The address itself stays listed as used.
                    $can_view_device = $item->canView();
                    if ($can_view_device) {
                        $label = (string) $line['dname'];
                        if ($line['itemtype'] === NetworkEquipment::class && !empty($line['pname'])) {
                            $label = $line['pname'] . ' - ' . $label;
                        }
                        if (empty($line['dname']) || $_SESSION['glpiis_ids_visible']) {
                            $label .= ' (' . $line['on_device'] . ')';
                        }
                        $row['device'] = self::newCell(
                            $label,
                            Toolbox::getItemTypeFormURL($line['itemtype']) . '?id=' . (int) $line['on_device'],
                        );
                    } else {
                        $row['device'] = self::newCell(__('Restricted access', 'addressing'));
                    }

                    if ($line['users_id'] && $can_view_device && $user->getFromDB($line['users_id'])) {
                        $row['user'] = self::newCell(
                            $dbu->formatUserName(
                                $user->fields['id'],
                                $user->fields['name'],
                                $user->fields['realname'],
                                $user->fields['firstname'],
                            ),
                            $user->canView() ? User::getFormURLWithID((int) $line['users_id']) : null,
                        );
                    }

                    if ($line['id'] && $can_view_device) {
                        $row['mac'] = self::newCell(
                            (string) $line['mac'],
                            NetworkPort::getFormURLWithID((int) $line['id']),
                        );
                    }

                    $row['type'] = $item::getTypeName();

                    if ($Addressing->fields['free_ip'] && $use_ping) {
                        $ping_info = self::findPingInfo($addressing_id, $num);
                        if ($ping_info === null) {
                            $row['ping_state']      = 'unknown';
                            $row['reservation_url'] = self::getReservationUrl($addressing_id, $ip);
                        } else {
                            $row['ping_state'] = $ping_info['ping_response'] ? 'ok' : 'ko';
                            $row['ping_date']  = (string) Html::convDateTime($ping_info['ping_date']);
                            if ($is_reserved) {
                                $row['reservation'] = 'reserved';
                            } elseif (!$ping_info['ping_response']) {
                                $row['reservation_url'] = self::getReservationUrl($addressing_id, $ip);
                            }
                        }
                    }

                    $rows[] = $row;
                }
            } elseif ($Addressing->fields['free_ip']) {
                $row = self::newRow($Addressing, $num, $ip);

                if (!$use_ping) {
                    // No ping icon here: this is the branch taken when the ping is off.
                    $row['class']           = 'plugin_addressing_ip_free';
                    $row['reservation_url'] = self::getReservationUrl($addressing_id, $ip);
                } else {
                    $ping_info = self::findPingInfo($addressing_id, $num);
                    if ($ping_info !== null && $ping_info['ping_response']) {
                        if (!$see_ping_on) {
                            continue;
                        }
                        $ping_response++;
                        $row['class']      = 'plugin_addressing_ping_off';
                        $row['device']     = self::newCell(__('Ping: got a response - used IP', 'addressing'));
                        $row['ping_state'] = 'ok';
                        $row['ping_date']  = (string) Html::convDateTime($ping_info['ping_date']);
                    } else {
                        if (!$see_ping_off) {
                            continue;
                        }
                        $row['class']  = 'plugin_addressing_ping_on';
                        $row['device'] = self::newCell(__('Ping: no response - free IP', 'addressing'));
                        if ($ping_info === null) {
                            $row['ping_state'] = 'unknown';
                        } else {
                            $row['ping_state']      = 'ko';
                            $row['ping_date']       = (string) Html::convDateTime($ping_info['ping_date']);
                            $row['reservation_url'] = self::getReservationUrl($addressing_id, $ip);
                        }
                    }
                    $row['ping_url'] = self::getPingUrl($ip);
                }

                $rows[] = $row;
            }
        }

        return [
            'addressing_id' => $addressing_id,
            'use_ping'      => $use_ping,
            'comment_url'   => PLUGIN_ADDRESSING_WEBDIR . '/ajax/ipcomment.php',
            'ping_response' => $ping_response,
            'rows'          => $rows,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function newRow(Addressing $Addressing, string $num, string $ip): array
    {
        $comment = new IpComment();
        $comment->getFromDBByCrit(['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()]);

        return [
            'class'           => '',
            'ip'              => $ip,
            'ipname'          => $num,
            'ping_url'        => null,
            'device'          => self::newCell(''),
            'user'            => self::newCell(''),
            'mac'             => self::newCell(''),
            'type'            => '',
            'ping_state'      => null,
            'ping_date'       => '',
            'reservation'     => null,
            'reservation_url' => null,
            'comment'         => (string) ($comment->fields['comments'] ?? ''),
        ];
    }

    /**
     * @return array{label: string, url: ?string}
     */
    private static function newCell(string $label, ?string $url = null): array
    {
        return ['label' => $label, 'url' => $url];
    }

    /**
     * @return ?array<string, mixed>
     */
    private static function findPingInfo(int $addressing_id, string $ipname): ?array
    {
        $ping_info = null;
        $rows = (new PingInfo())->find([
            'plugin_addressing_addressings_id' => $addressing_id,
            'ipname'                           => $ipname,
        ]);
        foreach ($rows as $data) {
            $ping_info = $data;
        }

        return $ping_info;
    }

    private static function getPingUrl(string $ip): string
    {
        return PLUGIN_ADDRESSING_WEBDIR . '/ajax/addressing.php?' . http_build_query([
            'action'    => 'ping',
            'ip'        => trim($ip),
            '_in_modal' => 1,
        ]);
    }

    private static function getReservationUrl(int $addressing_id, string $ip): string
    {
        return PLUGIN_ADDRESSING_WEBDIR . '/ajax/addressing.php?' . http_build_query([
            'action'        => 'showForm',
            'ip'            => trim($ip),
            'id_addressing' => $addressing_id,
            // Used by the reservation form as a DOM identifier.
            'rand'          => mt_rand(),
            '_in_modal'     => 1,
        ]);
    }

    /**
     * Hand the rows of the report to a CSV, ODS or PDF writer.
     *
     * @param array{use_ping: bool, rows: list<array<string, mixed>>} $report
     */
    private static function displayExport(AbstractSearchOutput $output, array $report): void
    {
        $itemtype = Addressing::class;

        $headers = [
            __('IP'),
            __('Connected to'),
            _n('User', 'Users', 1),
            __('MAC address'),
            __('Item type'),
        ];
        if ($report['use_ping']) {
            $headers[] = __('Ping result', 'addressing');
        }
        $headers[] = __('Reservation', 'addressing');
        $headers[] = __('Comments');

        $ping_labels = [
            'unknown' => __('Unknown'),
            'ok'      => __('Success', 'addressing'),
            'ko'      => __('Failed', 'addressing'),
        ];

        $rows = [];
        foreach ($report['rows'] as $row) {
            $cells = [
                $row['ip'],
                $row['device']['label'],
                $row['user']['label'],
                $row['mac']['label'],
                $row['type'],
            ];
            if ($report['use_ping']) {
                $cells[] = $ping_labels[$row['ping_state']] ?? '';
            }
            $cells[] = $row['reservation'] === 'reserved' ? __('Reserved', 'addressing') : '';
            $cells[] = $row['comment'];

            // The writers expect `displayname` to be safe HTML, as giveItem() provides it, and
            // decode it back to text themselves.
            $export_row = [];
            foreach ($cells as $index => $cell) {
                $export_row[$itemtype . '_' . ($index + 1)] = ['displayname' => htmlescape((string) $cell)];
            }
            $rows[] = $export_row;
        }

        $addressing_data = SearchEngine::prepareDataForSearch($itemtype, [
            'start'         => 0,
            'is_deleted'    => 0,
            'as_map'        => 0,
            'browse'        => 0,
            'unpublished'   => 1,
            'criteria'      => [],
            'metacriteria'  => [],
            'display_type'  => 0,
            'hide_controls' => true,
        ]);
        $addressing_data = array_merge($addressing_data, [
            'itemtype' => $itemtype,
            'data'     => [
                'totalcount' => count($rows),
                'count'      => count($rows),
                'search'     => '',
                'cols'       => [],
                'rows'       => self::escapeExportFormulas($rows),
            ],
        ]);

        $colid = 0;
        foreach ($headers as $header) {
            $addressing_data['data']['cols'][] = [
                'name'     => $header,
                'itemtype' => $itemtype,
                'id'       => ++$colid,
            ];
        }

        $output->displayData($addressing_data, []);
    }

    /**
     * Neutralise spreadsheet formulas in the cells handed to the non HTML renderers.
     *
     * displayData() passes these rows to the CSV, ODS and PDF writers as they are, and a
     * cell whose first character is one of = + - @ tab or carriage return is evaluated as
     * a formula by Excel and LibreOffice as soon as the file is opened. Asset names, port
     * names, user names and IP comments are free text written by any profile allowed to
     * edit them, so the export turns the report into a stored code execution vector
     * against whoever opens it -- typically the operator who asked for the export.
     *
     * The neutralisation is reversible on purpose: a single leading apostrophe is
     * prepended, which spreadsheets strip on display and which a re-import can remove
     * unambiguously, instead of mangling the exported value itself.
     *
     * @param array<int|string, array<int|string, mixed>> $rows
     *
     * @return array<int|string, array<int|string, mixed>>
     */
    private static function escapeExportFormulas(array $rows): array
    {
        foreach ($rows as $row_key => $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($row as $cell_key => $cell) {
                if (
                    !is_array($cell)
                    || !isset($cell['displayname'])
                    || !is_string($cell['displayname'])
                    || $cell['displayname'] === ''
                ) {
                    continue;
                }

                $value = $cell['displayname'];

                // The report composes its cells as markup -- links to the asset, to the user,
                // input fields for the comments -- and the very same array feeds the CSV, ODS
                // and PDF writers, which dump it verbatim. Reduce a cell to the text a reader
                // expects before anything else, and before the formula test below, so that a
                // payload cannot hide its leading character behind a tag.
                if (str_contains($value, '<')) {
                    $value = html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8');
                    $value = trim((string) preg_replace('/\s+/', ' ', $value));
                }

                if ($value !== '' && str_contains("=+-@\t\r", $value[0])) {
                    $value = "'" . $value;
                }

                $rows[$row_key][$cell_key]['displayname'] = $value;
            }
        }

        return $rows;
    }

    /**
     * Converts an (IPv4) Internet network address into a string in Internet standard dotted format
     * @link http://php.net/manual/en/function.long2ip.php
     * problem with 32-bit architectures: https://bugs.php.net/bug.php?id=74417&edit=1
     *
     * @param $s
     *
     * @return string
     */
    public static function string2ip($s)
    {
        if ($s > PHP_INT_MAX) {
            $s = 2 * PHP_INT_MIN + $s;
        }
        return long2ip($s);
    }

    public static function ip2string($s)
    {
        if ($s > PHP_INT_MAX) {
            $s = 2 * PHP_INT_MIN + $s;
        }
        return ip2long($s);
    }
}
