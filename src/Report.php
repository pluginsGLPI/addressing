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
use DbUtils;
use Glpi\Exception\Http\BadRequestHttpException;
use Glpi\Search\Output\HTMLSearchOutput;
use Glpi\Search\SearchEngine;
use Html;
use Search;
use Session;
use Toolbox;
use User;

/**
 * Class Report
 */
class Report extends CommonDBTM
{
    public static $rightname = "plugin_addressing";

    public static function getTypeName($nb = 0)
    {
        return __('Report');
    }

    /**
     * @param      $type
     * @param bool $odd
     *
     * @return string
     */
    //    public function displaySearchNewLine($type, $odd = false)
    //    {
    //        $out = "";
    //        switch ($type) {
    //            case Search::PDF_OUTPUT_LANDSCAPE: //pdf
    //            case Search::PDF_OUTPUT_PORTRAIT:
    //                global $PDF_TABLE;
    //                $style = "";
    //                if ($odd) {
    //                    $style = " style=\"background-color:#DDDDDD;\" ";
    //                }
    //                $PDF_TABLE .= "<tr nobr=\"true\" $style>";
    //                break;
    //
    //            //         case Search::SYLK_OUTPUT : //sylk
    //            //       $out="\n";
    //            //            break;
    //
    //            case Search::CSV_OUTPUT: //csv
    //                //$out="\n";
    //                break;
    //
    //            default:
    //                $class = " class='tab_bg_2' ";
    //                if ($odd) {
    //                    switch ($odd) {
    //                        case "double": //double
    //                            $class = " class='plugin_addressing_ip_double'";
    //                            break;
    //
    //                        case "free": //free
    //                            $class = " class='plugin_addressing_ip_free'";
    //                            break;
    //
    //                        case "reserved": //free
    //                            $class = " class='plugin_addressing_ip_reserved'";
    //                            break;
    //
    //                        case "ping_on": //ping_on
    //                            $class = " class='plugin_addressing_ping_on'";
    //                            break;
    //
    //                        case "ping_off": //ping_off
    //                            $class = " class='plugin_addressing_ping_off'";
    //                            break;
    //
    //                        default:
    //                            $class = " class='tab_bg_1' ";
    //                    }
    //                }
    //                $out = "<tr $class>";
    //                break;
    //        }
    //        return $out;
    //    }

    public static function showNewLine($odd = false, $is_deleted = false): string
    {
        $class = " class='tab_bg_2' ";
        if ($odd) {
            switch ($odd) {
                case "double": //double
                    $class = " class='plugin_addressing_ip_double'";
                    break;

                case "free": //free
                    $class = " class='plugin_addressing_ip_free'";
                    break;

                case "reserved": //free
                    $class = " class='plugin_addressing_ip_reserved'";
                    break;

                case "ping_on": //ping_on
                    $class = " class='plugin_addressing_ping_on'";
                    break;

                case "ping_off": //ping_off
                    $class = " class='plugin_addressing_ping_off'";
                    break;

                default:
                    $class = " class='tab_bg_1' ";
            }
        }
        return "<tr $class>";
    }


    /**
     * @param $result
     * @param $Addressing
     *
     * @return int
     */
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
        global $CFG_GLPI;

        // The flag of the range is only half of the decision: the configuration of the plugin
        // carries a global switch, labelled "Use Ping on IP ranges", which this report ignored.
        // Ask the single decision point instead, so the report, the cron task and the manual
        // scan all answer the same question.
        $ping = PingInfo::isRangeScanEnabled($Addressing) ? 1 : 0;

        // Get config
        $Config = new Config();
        $Config->getFromDB('1');
        $system = $Config->fields["used_system"];


        // These used to be assigned through variable variables from the request, so $start kept
        // whatever type the caller sent. It is immediately used in pagination arithmetic
        // ($start + glpilist_limit), which raises a TypeError on an array and lets a negative
        // offset walk outside the result set. Read each parameter explicitly, with its type.
        $start  = max(0, (int) ($values['start'] ?? 0));
        $id     = (int) ($values['id'] ?? 0);
        $export = (bool) ($values['export'] ?? false);

        $itemtype = Addressing::class;
        // Set display type for export if define. $values now carries the request parameters
        // (it used to receive the ping status array by mistake, see Addressing::showReport()),
        // so display_type is caller controlled here and has to be validated before it reaches
        // the core: getOutputForLegacyKey(int $output_type) raises a TypeError on a non numeric
        // value and a RuntimeException on a key outside the enumeration, neither of which is
        // caught, turning an arbitrary parameter into a 500 with a full stack trace.
        $output_type = self::validateOutputType($values["display_type"] ?? Search::HTML_OUTPUT);
        $output = SearchEngine::getOutputForLegacyKey($output_type);
        $is_html_output = $output instanceof HTMLSearchOutput;
        $html_output = '';

        $headers = [];
        $rows = [];
        $numrows = count($result);
        $end_display = $start + $_SESSION['glpilist_limit'];
        if (isset($_GET['export_all'])) {
            $start = 0;
            $end_display = $numrows;
        }

        $nbcols = 4;
        if (!$is_html_output) {
            $nbcols--;
        }

        // Set display type for export if define
        //        $output_type = Search::HTML_OUTPUT;
        //
        //        if (isset($_GET["display_type"])) {
        //            $output_type = $_GET["display_type"];
        //        }

        //        $header_num    = 1;
        //        $nbcols        = 8;
        $ping_response = 0;
        //        $row_num       = 1;

        // Column headers
        if ($is_html_output) {
            $html_output .= $output::showHeader($end_display - $start + 1, $nbcols);
        }
        if (!$is_html_output) {
            $headers[] = __('IP');
            $headers[] = __('Connected to');
            $headers[] = _n('User', 'Users', 1);
            $headers[] = __('MAC address');
            $headers[] = __('Item type');
            if ($ping == 1) {
                $headers[] = __('Ping result', 'addressing');
            }
            $headers[] = __('Reservation', 'addressing');
            $headers[] = __('Comments');
        } else {
            $header_num = 1;
            $html_output .= self::showNewLine();
            $html_output .= $output::showHeaderItem("", $header_num);
            $html_output .= $output::showHeaderItem(__('IP'), $header_num);
            $html_output .= $output::showHeaderItem(__('Connected to'), $header_num);
            $html_output .= $output::showHeaderItem(_n('User', 'Users', 1), $header_num);
            $html_output .= $output::showHeaderItem(__('MAC address'), $header_num);
            $html_output .= $output::showHeaderItem(__('Item type'), $header_num);
            if ($ping == 1) {
                $html_output .= $output::showHeaderItem(__('Ping result', 'addressing'), $header_num);
            }
            $html_output .= $output::showHeaderItem(__('Reservation', 'addressing'), $header_num);
            $html_output .= $output::showHeaderItem(__('Comments'), $header_num);
            $html_output .= $output::showHeaderItem("", $header_num);
            $html_output .= $output::showEndLine($output_type);
        }

        $user = new User();
        $row_num = 0;
        if (!empty($result)) {
            $i = 0;
            //            for ($i = $start; ($i < $numrows) && ($i < $end_display); $i++) {
            foreach ($result as $num => $lines) {
                $row_num++;
                $current_row = [];
                $item_num = 1;
                $colnum = 0;
                $i++;
                if ($is_html_output) {
                    $html_output .= self::showNewLine($i % 2 === 1);
                }
                $ip = self::string2ip(substr($num, 2));

                if (count($lines)) {
                    if (count($lines) > 1) {
                        $disp = $Addressing->fields["double_ip"];
                    } else {
                        $disp = $Addressing->fields["alloted_ip"];
                    }
                    if ($disp) {
                        foreach ($lines as $line) {
                            // itemtype comes from the glpi_networkports rows: a plugin
                            // uninstalled without cleaning its ports leaves a class name
                            // that no longer exists, and instantiating it below would raise
                            // an uncaught Error making the whole report unreachable. Skip
                            // the row instead, as every other dynamic instantiation in this
                            // plugin already does.
                            $line_item = getItemForItemtype($line["itemtype"]);
                            if (!($line_item instanceof CommonDBTM)) {
                                continue;
                            }
                            $row_num++;
                            $item_num = 1;
                            // Asset/port names are stored raw in DB and rendered through the
                            // legacy HTMLSearchOutput::showItem(), which emits its value verbatim.
                            // Escape here so a hostile asset/port name cannot inject markup into
                            // the report (stored XSS across the entity/right boundary).
                            $name = htmlspecialchars((string) $line["dname"], ENT_QUOTES, 'UTF-8');
                            $namep = htmlspecialchars((string) $line["pname"], ENT_QUOTES, 'UTF-8');
                            // IP
                            if ($is_html_output) {
                                if ($Addressing->fields["reserved_ip"] && strstr(
                                    $line["pname"],
                                    "reserv",
                                )) {
                                    $html_output .= self::showNewLine("reserved");
                                } else {
                                    $html_output .= self::showNewLine(
                                        (count($lines) > 1 ? "double" : $row_num % 2),
                                    );
                                }
                            }
                            $rand = mt_rand();
                            $params = [
                                'ip' => trim($ip),
                                'width' => 450,
                                'height' => 300,
                                'dialog_class' => 'modal-sm',
                            ];
                            // The icon opens an iframe that makes the server probe the address.
                            // It was offered whatever the ping setting said, so neither the global
                            // switch nor the flag of the range had any effect on this column --
                            // report.html.twig already hides the manual launch button that way,
                            // through can_scan. The cell itself stays, so the row keeps the number
                            // of columns its header announces.
                            $ping_link = "";
                            if ($ping == 1) {
                                $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>";
                                $ping_link .= "<i class='ti ti-terminal-2 pointer' style='color: orange' title='" . __(
                                    "IP ping",
                                    'addressing',
                                ) . "'></i></a>";
                            }

                            if ($is_html_output) {
                                $html_output .= $output::showItem("$ping_link ", $item_num, $row_num, "class='center'");
                            }
                            if ($ping == 1 && isset($params) && count($params) > 0 && $is_html_output) {
                                echo Ajax::createIframeModalWindow(
                                    'ping' . $rand,
                                    "/plugins/addressing/ajax/addressing.php?action=ping&ip=" . $params['ip'],
                                    [
                                        'title' => __s('IP ping', 'addressing'),
                                        'display' => false,
                                    ],
                                );
                            }

                            if ($is_html_output) {
                                $html_output .= $output::showItem($ip, $item_num, $row_num);
                            } else {
                                $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $ip];
                            }
                            // Device
                            $item = $line_item;
                            // Defence in depth on top of the itemtype filtering done in
                            // Addressing::compute(): when the profile cannot read the asset, degrade
                            // every detail it carries to a neutral label instead of only dropping the
                            // link to its form. The address itself stays listed as used.
                            $can_view_device = $item->canView();
                            $restricted_label = htmlspecialchars(
                                __("Restricted access", "addressing"),
                                ENT_QUOTES,
                                "UTF-8",
                            );
                            $link = Toolbox::getItemTypeFormURL($line["itemtype"]);
                            if ($line["itemtype"] != 'NetworkEquipment') {
                                if ($item->canView()) {
                                    $output_iddev = "<a href='" . $link . "?id=" . $line["on_device"] . "'>" . $name
                                        . (empty($name) || $_SESSION["glpiis_ids_visible"] ? " (" . $line["on_device"] . ")" : "") . "</a>";
                                } else {
                                    $output_iddev = $restricted_label;
                                }
                            } else {
                                if ($item->canView()) {
                                    if (empty($namep)) {
                                        $linkp = '';
                                    } else {
                                        $linkp = $namep . " - ";
                                    }
                                    $output_iddev = "<a href='" . $link . "?id=" . $line["on_device"] . "'>" . $linkp . $name
                                        . (empty($name) || $_SESSION["glpiis_ids_visible"] ? " (" . $line["on_device"] . ")" : "") . "</a>";
                                } else {
                                    $output_iddev = $restricted_label;
                                }
                            }
                            if ($is_html_output) {
                                $html_output .= $output::showItem($output_iddev, $item_num, $row_num);
                            } else {
                                $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $output_iddev];
                            }
                            // User
                            if ($line["users_id"] && $can_view_device && $user->getFromDB($line["users_id"])) {
                                $dbu = new DbUtils();
                                $username = $dbu->formatUserName(
                                    $user->fields["id"],
                                    $user->fields["name"],
                                    $user->fields["realname"],
                                    $user->fields["firstname"],
                                );

                                $username = htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8');
                                if ($user->canView()) {
                                    $output_iduser = "<a href='" . $CFG_GLPI["root_doc"] . "/front/user.form.php?id="
                                        . $line["users_id"] . "'>" . $username . "</a>";
                                } else {
                                    $output_iduser = $username;
                                }
                                if ($is_html_output) {
                                    $html_output .= $output::showItem($output_iduser, $item_num, $row_num);
                                } else {
                                    $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $output_iduser];
                                }
                            } else {
                                if ($is_html_output) {
                                    $html_output .= $output::showItem(" ", $item_num, $row_num);
                                } else {
                                    $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => ""];
                                }
                            }

                            // Mac
                            if ($line["id"] && $can_view_device) {
                                $mac = htmlspecialchars((string) $line["mac"], ENT_QUOTES, 'UTF-8');
                                if ($item->canView()) {
                                    $output_mac = "<a href='" . $CFG_GLPI["root_doc"] . "/front/networkport.form.php?id="
                                        . $line["id"] . "'>" . $mac . "</a>";
                                } else {
                                    $output_mac = $mac;
                                }
                                if ($is_html_output) {
                                    $html_output .= $output::showItem($output_mac, $item_num, $row_num);
                                } else {
                                    $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $output_mac];
                                }
                            } else {
                                if ($is_html_output) {
                                    $html_output .= $output::showItem(" ", $item_num, $row_num);
                                } else {
                                    $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => ""];
                                }
                            }
                            // Type
                            if ($is_html_output) {
                                $html_output .= $output::showItem($item::getTypeName(), $item_num, $row_num);
                            } else {
                                $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $item::getTypeName()];
                            }
                            // Ping
                            $ping_action = NOT_AVAILABLE;
                            if ($Addressing->fields["free_ip"] && $ping) {
                                $plugin_addressing_pinginfo = new PingInfo();
                                if ($pings = $plugin_addressing_pinginfo->find([
                                    'plugin_addressing_addressings_id' => $Addressing->getID(),
                                    'ipname' => $num,
                                ])) {
                                    // Reusing $ping as the loop variable replaced the ping setting
                                    // of the range with a row of the ping information table for
                                    // the whole remainder of the rendering, so every later test of
                                    // $ping read an array instead of the flag.
                                    foreach ($pings as $ping_info) {
                                        $ping_value = $ping_info['ping_response'];
                                        $ping_date = $ping_info['ping_date'];
                                    }
                                    $ping_action = 1;
                                } else {
                                    $ping_value = 0;
                                    //                        $ping_value = $this->ping($system, $ip);
                                    //                        $data = [];
                                    //                        $data['plugin_addressing_addressings_id'] = $Addressing->getID();
                                    //                        $data['ipname'] = $num;
                                    //                        $data['ping_response'] = $ping_value ?? 0;
                                    //                        $data['ping_date'] = date('Y-m-d H:i:s');;
                                    //                        $plugin_addressing_pinginfo->add($data);
                                }
                                //                     $plugin_addressing_pinginfo->getFromDBByCrit(['plugin_addressing_addressings_id' => $Addressing->getID(),
                                //                        'ipname' => $num]);

                                if ($ping_action == NOT_AVAILABLE) {
                                    $content = "<i class=\"ti ti-question\" style='color: orange;font-size: 2em;' title=\"" . __(
                                        "Automatic action has not be launched",
                                        'addressing',
                                    ) . "\"></i>";
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            "$content ",
                                            $item_num,
                                            $row_num,
                                            "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                        );
                                        $rand = mt_rand();
                                        $params = [
                                            'id_addressing' => $Addressing->getID(),
                                            'ip' => trim($ip),
                                            //                           'root_doc' => $CFG_GLPI['root_doc'],
                                            'rand' => $rand,
                                            //                           'width' => 1000,
                                            //                           'height' => 550
                                        ];
                                        $reserv = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>";
                                        $reserv .= "<i class='ti ti-clipboard pointer' style='color: #d56f15;font-size: 2em;' title='" . __(
                                            "Reserve IP",
                                            'addressing',
                                        ) . "'></i></a>";
                                        $html_output .= $output::showItem(
                                            "$reserv ",
                                            $item_num,
                                            $row_num,
                                            "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                        );
                                        if (isset($params) && count(
                                            $params,
                                        ) > 0 && $is_html_output) {
                                            echo Ajax::createIframeModalWindow(
                                                'reservation' . $rand,
                                                "/plugins/addressing/ajax/addressing.php?action=showForm&ip=" . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $params['rand'],
                                                [
                                                    'title' => __s('IP reservation', 'addressing'),
                                                    'display' => false,
                                                    'reloadonclose' => true,
                                                ],
                                            );
                                        }
                                    } else {
                                        $content = __('Unknown');
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $content];
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => ""];
                                    }
                                } else {
                                    if ($ping_value) {
                                        if ($is_html_output) {
                                            $html_output .= $output::showItem(
                                                "<i class=\"ti ti-square-check\" style='color: var(--add-state-ok, darkgreen);font-size: 2em;' title='" . __(
                                                    "Last ping attempt",
                                                    'addressing',
                                                ) . " : "
                                                . Html::convDateTime($ping_date) . "'></i>",
                                                $item_num,
                                                $row_num,
                                                "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                            );

                                            if ($Addressing->fields["reserved_ip"] && strstr(
                                                $line["pname"],
                                                "reserv",
                                            )) {
                                                $reserv = "<i class='ti ti-clipboard-check' style='color: #d56f15;font-size: 2em;' title='" . __(
                                                    'Reserved Address',
                                                    'addressing',
                                                ) . "'></i>";
                                                $html_output .= $output::showItem(
                                                    $reserv,
                                                    $item_num,
                                                    $row_num,
                                                    "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                                );
                                            } else {
                                                $html_output .= $output::showItem(
                                                    " ",
                                                    $item_num,
                                                    $row_num,
                                                    "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                                );
                                            }
                                        } else {
                                            $reserv = "";
                                            $content = __('Success', 'addressing');
                                            if ($is_html_output) {
                                                $html_output .= $output::showItem($content, $item_num, $row_num);
                                            }
                                            if ($Addressing->fields["reserved_ip"] && strstr(
                                                $line["pname"],
                                                "reserv",
                                            )) {
                                                $reserv = __('Reserved', 'addressing');
                                            }
                                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $reserv];
                                        }
                                    } else {
                                        if ($is_html_output) {
                                            $html_output .= $output::showItem(
                                                "<i class=\"ti ti-square-x\" style='color: var(--add-state-ko, darkred);font-size: 2em;' title='"
                                                . __("Last ping attempt", 'addressing') . " : "
                                                . Html::convDateTime($ping_date) . "'></i>",
                                                $item_num,
                                                $row_num,
                                                "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                            );
                                            if ($Addressing->fields["reserved_ip"] && strstr(
                                                $line["pname"],
                                                "reserv",
                                            )) {
                                                $html_output .= $output::showItem(
                                                    "<i class='ti ti-clipboard-check' style='color: #d56f15;font-size: 2em;' title='"
                                                    . __('Reserved Address', 'addressing') . "'></i>",
                                                    $item_num,
                                                    $row_num,
                                                    "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                                );
                                            } else {
                                                $rand = mt_rand();
                                                $params = [
                                                    'id_addressing' => $Addressing->getID(),
                                                    'ip' => trim($ip),
                                                    //                                 'root_doc' => $CFG_GLPI['root_doc'],
                                                    'rand' => $rand,
                                                    //                                 'width' => 1000,
                                                    //                                 'height' => 550
                                                ];
                                                $reserv = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>";
                                                $reserv .= "<i class='ti ti-clipboard pointer' style='color: #d56f15;font-size: 2em;' title='" . __(
                                                    "Reserve IP",
                                                    'addressing',
                                                ) . "'></i></a>";
                                                $html_output .= $output::showItem(
                                                    "$reserv ",
                                                    $item_num,
                                                    $row_num,
                                                    "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                                );
                                                if (isset($params) && count(
                                                    $params,
                                                ) > 0 && $is_html_output) {
                                                    echo Ajax::createIframeModalWindow(
                                                        'reservation' . $rand,
                                                        "/plugins/addressing/ajax/addressing.php?action=showForm&ip="
                                                        . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $params['rand'],
                                                        [
                                                            'title' => __s('IP reservation', 'addressing'),
                                                            'display' => false,
                                                            'reloadonclose' => true,
                                                        ],
                                                    );
                                                }
                                            }
                                        } else {
                                            $content = __('Failed', 'addressing');
                                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $content];

                                            if ($Addressing->fields["reserved_ip"] && strstr(
                                                $line["pname"],
                                                "reserv",
                                            )) {
                                                $reserv = __('Reserved', 'addressing');
                                            } else {
                                                $reserv = "";
                                            }
                                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $reserv];
                                        }
                                    }
                                }
                            } else {
                                if ($is_html_output) {
                                    $html_output .= $output::showItem(
                                        " ",
                                        $item_num,
                                        $row_num,
                                        "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                    );
                                } else {
                                    $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => ""];
                                }
                            }

                            $rand = mt_rand();
                            $comment = new IpComment();
                            $comment->getFromDBByCrit(
                                ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()],
                            );
                            $comments = $comment->fields['comments'] ?? '';
                            //                  $html_output .= $output::showItem( '<textarea id="comment'.$num.'"
                            //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                            if ($is_html_output) {
                                $html_output .= $output::showItem(
                                    '<input type="text" id="comment' . $num . '"
                      value="' . htmlspecialchars($comments, ENT_QUOTES, 'UTF-8') . '">',
                                    $item_num,
                                    $row_num,
                                    "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center' onChange='updateFA$rand()'",
                                );
                            } else {
                                $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $comments];
                            }
                            if ($is_html_output) {
                                $html_output .= $output::showItem(
                                    '<i id="save' . $num . '" class="ti ti-device-floppy center pointer" style="color:forestgreen;font-size: 2em;"></i>',
                                    $item_num,
                                    $row_num,
                                    "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center' onClick='updateComment$rand()'",
                                );
                                echo "<script>

                                  function updateComment$rand() {

                                      $('#ajax_loader').show();
                                      $.ajax({
                                         url: '" . PLUGIN_ADDRESSING_WEBDIR . "/ajax/ipcomment.php',
                                            type: 'POST',
                                            data:
                                              {
                                                addressing_id:" . $Addressing->getID() . ",
                                                ipname: \"" . $num . "\",
                                                contentC: $('#comment" . $num . "').val(),

                                              },
                                            success: function(response){
                                                $('#save" . $num . "').css('color','');
                                                $('#save" . $num . "').css('color','forestgreen');
                                                $('#ajax_loader').hide();

                                             },
                                            error: function(xhr, status, error) {
                                               console.log(xhr);
                                               console.log(status);
                                               console.log(error);
                                             }
                                         });
                                   };

                                  function updateFA$rand() {
                                      $('#save" . $num . "').css('color','');
                                      $('#save" . $num . "').css('color','orange');

                                   };
                                 </script>";
                            }
                            //                  echo '<td><textarea id="tre" name="story"
                            //          rows="5" cols="33"></textarea></td>';
                            // End
                            $rows[$row_num] = $current_row;
                            if ($is_html_output) {
                                $html_output .= $output::showEndLine(false);
                            }
                        }
                    }
                } elseif ($Addressing->fields["free_ip"]) {
                    $row_num++;
                    $item_num = 1;
                    $content = "";

                    $rand = mt_rand();
                    $params = [
                        'id_addressing' => $Addressing->getID(),
                        'ip' => trim($ip),
                        //               'root_doc' => $CFG_GLPI['root_doc'],
                        'rand' => $rand,
                        //               'width' => 1000,
                        //               'height' => 550
                    ];

                    if (!$ping) {
                        if ($is_html_output) {
                            $html_output .= self::showNewLine("free");
                        }
                        $rand = mt_rand();
                        $params = [
                            'ip' => trim($ip),
                            'width' => 450,
                            'height' => 300,
                            'dialog_class' => 'modal-sm',
                        ];
                        // This whole branch is the one taken when the ping is off, yet it offered
                        // the icon that makes the server probe the address, and the iframe behind
                        // it ran the probe. Leave the cell empty: the column is still emitted, so
                        // the row keeps the number of cells its header announces.
                        if ($is_html_output) {
                            $html_output .= $output::showItem(" ", $item_num, $row_num, "class='center'");
                        }
                        if ($is_html_output) {
                            $html_output .= $output::showItem($ip, $item_num, $row_num);
                        } else {
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $ip];
                        }
                        if ($is_html_output) {
                            $html_output .= $output::showItem(" ", $item_num, $row_num);
                            $html_output .= $output::showItem(" ", $item_num, $row_num);
                        } else {
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                        }
                        $rand = mt_rand();
                        $comment = new IpComment();
                        $comment->getFromDBByCrit(
                            ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()],
                        );
                        $comments = $comment->fields['comments'] ?? '';

                        if ($is_html_output) {
                            $content = "";
                            $params = [
                                'id_addressing' => $Addressing->getID(),
                                'ip' => trim($ip),
                                //                                 'root_doc' => $CFG_GLPI['root_doc'],
                                'rand' => $rand,
                                //                                 'width' => 1000,
                                //                                 'height' => 550
                            ];
                            $reserv = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>";
                            $reserv .= "<i class='ti ti-clipboard pointer' style='color: #d56f15;font-size: 2em;' title='" . __(
                                "Reserve IP",
                                'addressing',
                            ) . "'></i></a>";
                            if (isset($params) && count($params) > 0 && $is_html_output) {
                                echo Ajax::createIframeModalWindow(
                                    'reservation' . $rand,
                                    "/plugins/addressing/ajax/addressing.php?action=showForm&ip=" . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $params['rand'],
                                    [
                                        'title' => __s('IP reservation', 'addressing'),
                                        'display' => false,
                                        'reloadonclose' => true,
                                    ],
                                );
                            }
                        } else {
                            $content = "";
                            $reserv = "";
                        }
                        if ($is_html_output) {
                            $html_output .= $output::showItem(" ", $item_num, $row_num);
                            $html_output .= $output::showItem(" ", $item_num, $row_num);
                            $html_output .= $output::showItem(
                                "$reserv ",
                                $item_num,
                                $row_num,
                                "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                            );
                        } else {
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $reserv];
                        }

                        $rand = mt_rand();
                        $comment = new IpComment();
                        $comment->getFromDBByCrit(
                            ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()],
                        );
                        $comments = $comment->fields['comments'] ?? '';
                        //               $html_output .= $output::showItem( '<textarea id="comment'.$num.'"
                        //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                        if ($is_html_output) {
                            $html_output .= $output::showItem(
                                '<input type="text" id="comment' . $num . '"
                      value="' . htmlspecialchars($comments, ENT_QUOTES, 'UTF-8') . '">',
                                $item_num,
                                $row_num,
                                "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center' onChange='updateFA$rand()'",
                            );
                        } else {
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $comments];
                        }
                        if ($is_html_output) {
                            $html_output .= $output::showItem(
                                '<i id="save' . $num . '" class="ti ti-device-floppy center pointer" style="color:forestgreen;font-size: 2em;"></i>',
                                $item_num,
                                $row_num,
                                "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center' onClick='updateComment$rand()'",
                            );
                            echo "<script>
                              function updateComment$rand() {

                                  $('#ajax_loader').show();
                                  $.ajax({
                                     url: '" . PLUGIN_ADDRESSING_WEBDIR . "/ajax/ipcomment.php',
                                        type: 'POST',
                                        data:
                                          {
                                            addressing_id:" . $Addressing->getID() . ",
                                            ipname: \"" . $num . "\",
                                            contentC: $('#comment" . $num . "').val(),

                                          },
                                        success: function(response){
                                            $('#save" . $num . "').css('color','');
                                            $('#save" . $num . "').css('color','forestgreen');
                                            $('#ajax_loader').hide();

                                         },
                                        error: function(xhr, status, error) {
                                           console.log(xhr);
                                           console.log(status);
                                           console.log(error);
                                         }
                                     });
                               };

                              function updateFA$rand() {
                                  $('#save" . $num . "').css('color','');
                                  $('#save" . $num . "').css('color','orange');

                               };
                             </script>";
                        } else {
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => ""];
                        }
                        $rows[$row_num] = $current_row;
                        if ($is_html_output) {
                            $html_output .= $output::showEndLine(false);
                        }
                    } else {
                        $ping_action = NOT_AVAILABLE;
                        $plugin_addressing_pinginfo = new PingInfo();
                        if ($plugin_addressing_pinginfo->getFromDBByCrit([
                            'plugin_addressing_addressings_id' => $Addressing->getID(),
                            'ipname' => $num,
                        ])) {
                            $ping_value = $plugin_addressing_pinginfo->fields['ping_response'];
                            $ping_action = 1;
                        } else {
                            $ping_value = 0;
                            //                  $ping_value = $this->ping($system, $ip);
                            //                  $data = [];
                            //                  $data['plugin_addressing_addressings_id'] = $Addressing->getID();
                            //                  $data['ipname'] = $num;
                            //                  $data['ping_response'] = $ping_value ?? 0;
                            //                  $data['ping_date'] = date('Y-m-d H:i:s');;
                            //                  $plugin_addressing_pinginfo->add($data);
                        }

                        $plugin_addressing_pinginfo->getFromDBByCrit([
                            'plugin_addressing_addressings_id' => $Addressing->getID(),
                            'ipname' => $num,
                        ]);

                        $content = "";
                        $reserv = "";
                        $see_ping_on = $ping_status[0] ?? 1;
                        $see_ping_off = $ping_status[1] ?? 1;
                        if ($see_ping_on == 1 || $see_ping_off == 1) {
                            if ($ping_value) {
                                if ($see_ping_on == 1) {
                                    $ping_response++;
                                    if ($is_html_output) {
                                        $html_output .= self::showNewLine("ping_off");
                                    }
                                    $rand = mt_rand();
                                    $params = [
                                        'ip' => trim($ip),
                                        'width' => 450,
                                        'height' => 300,
                                        'dialog_class' => 'modal-sm',
                                    ];
                                    $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>";
                                    $ping_link .= "<i class='ti ti-terminal-2 pointer' style='color: orange' title='" . __(
                                        "IP ping",
                                        'addressing',
                                    ) . "'></i></a>";
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            "$ping_link ",
                                            $item_num,
                                            $row_num,
                                            "class='center'",
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $ping_link];
                                    }
                                    if (isset($params) && count($params) > 0 && $is_html_output) {
                                        echo Ajax::createIframeModalWindow(
                                            'ping' . $rand,
                                            "/plugins/addressing/ajax/addressing.php?action=ping&ip=" . $params['ip'],
                                            [
                                                'title' => __s('IP ping', 'addressing'),
                                                'display' => false,
                                            ],
                                        );
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem($ip, $item_num, $row_num);
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $ip];
                                    }
                                    $title = __('Ping: got a response - used IP', 'addressing');
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            $title,
                                            $item_num,
                                            $row_num,
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $title];
                                    }
                                    if ($is_html_output) {
                                        if ($ping_action == NOT_AVAILABLE) {
                                            $content = "<i class=\"ti ti-question\" style='color: orange;font-size: 2em;' title=\"" . __(
                                                "Automatic action has not be launched",
                                                'addressing',
                                            ) . "\"></i>";
                                        } else {
                                            $content = "<i class=\"ti ti-square-check\" style='color: var(--add-state-ok, darkgreen);font-size: 2em;' title='" . __(
                                                "Last ping attempt",
                                                'addressing',
                                            ) . " : "
                                                . Html::convDateTime(
                                                    $plugin_addressing_pinginfo->fields['ping_date'],
                                                ) . "'></i>";
                                        }
                                    } else {
                                        $content = __('Success', 'addressing');
                                    }

                                    $reserv = "";
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(" ", $item_num, $row_num);
                                        $html_output .= $output::showItem(" ", $item_num, $row_num);
                                        $html_output .= $output::showItem(" ", $item_num, $row_num);
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                                    }
                                    if ($ping) {
                                        if ($is_html_output) {
                                            $html_output .= $output::showItem(
                                                "$content ",
                                                $item_num,
                                                $row_num,
                                                "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                            );
                                        } else {
                                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $content];
                                        }
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            "$reserv ",
                                            $item_num,
                                            $row_num,
                                            "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $reserv];
                                    }
                                    $rand = mt_rand();
                                    $comment = new IpComment();
                                    $comment->getFromDBByCrit(
                                        ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()],
                                    );
                                    $comments = $comment->fields['comments'] ?? '';
                                    //                        $html_output .= $output::showItem( '<textarea id="comment'.$num.'"
                                    //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            '<input type="text" id="comment' . $num . '"
                                 value="' . htmlspecialchars($comments, ENT_QUOTES, 'UTF-8') . '">',
                                            $item_num,
                                            $row_num,
                                            "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center' onChange='updateFA$rand()'",
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $comments];
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            '<i id="save' . $num . '" class="ti ti-device-floppy center pointer" style="color:forestgreen;font-size: 2em;"></i>',
                                            $item_num,
                                            $row_num,
                                            "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center' onClick='updateComment$rand()'",
                                        );

                                        echo "<script>
                                          function updateComment$rand() {

                                              $('#ajax_loader').show();
                                              $.ajax({
                                                 url: '" . PLUGIN_ADDRESSING_WEBDIR . "/ajax/ipcomment.php',
                                                    type: 'POST',
                                                    data:
                                                      {
                                                        addressing_id:" . $Addressing->getID() . ",
                                                        ipname: \"" . $num . "\",
                                                        contentC: $('#comment" . $num . "').val(),

                                                      },
                                                    success: function(response){
                                                        $('#save" . $num . "').css('color','');
                                                        $('#save" . $num . "').css('color','forestgreen');
                                                        $('#ajax_loader').hide();

                                                     },
                                                    error: function(xhr, status, error) {
                                                       console.log(xhr);
                                                       console.log(status);
                                                       console.log(error);
                                                     }
                                                 });
                                           };

                                          function updateFA$rand() {
                                              $('#save" . $num . "').css('color','');
                                              $('#save" . $num . "').css('color','orange');

                                           };
                                         </script>";
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showEndLine(false);
                                    }
                                }
                            } else {
                                if ($see_ping_off == 1) {
                                    if ($is_html_output) {
                                        $html_output .= self::showNewLine("ping_on");
                                    }
                                    $rand = mt_rand();
                                    $params = [
                                        'id_addressing' => $Addressing->getID(),
                                        'ip' => trim($ip),
                                        'rand' => $rand,
                                        'width' => 450,
                                        'height' => 300,
                                        'dialog_class' => 'modal-sm',
                                    ];
                                    $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>";
                                    $ping_link .= "<i class='ti ti-terminal-2 pointer' style='color: orange' title='" . __(
                                        "IP ping",
                                        'addressing',
                                    ) . "'></i></a>";
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            "$ping_link ",
                                            $item_num,
                                            $row_num,
                                            "class='center'",
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $ping_link];
                                    }
                                    if (isset($params) && count($params) > 0 && $is_html_output) {
                                        echo Ajax::createIframeModalWindow(
                                            'ping' . $rand,
                                            "/plugins/addressing/ajax/addressing.php?action=ping&ip=" . $params['ip'],
                                            [
                                                'title' => __s('IP ping', 'addressing'),
                                                'display' => false,
                                            ],
                                        );
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem($ip, $item_num, $row_num);
                                        $html_output .= $output::showItem(
                                            __('Ping: no response - free IP', 'addressing'),
                                            $item_num,
                                            $row_num,
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $ip];
                                        $current_row[$itemtype . '_' . (++$colnum)] = [
                                            'displayname' => __(
                                                'Ping: no response - free IP',
                                                'addressing',
                                            ),
                                        ];
                                    }
                                    $content = " ";
                                    if ($is_html_output) {
                                        if ($ping_action == NOT_AVAILABLE) {
                                            $content = "<i class=\"ti ti-question\" style='color: orange;font-size: 2em;' title=\"" . __(
                                                "Automatic action has not be launched",
                                                'addressing',
                                            ) . "\"></i>";
                                        } else {
                                            $content = "<i class=\"ti ti-square-x\" style='color: var(--add-state-ko, darkred);font-size: 2em;' title='" . __(
                                                "Last ping attempt",
                                                'addressing',
                                            ) . " : "
                                                . Html::convDateTime(
                                                    $plugin_addressing_pinginfo->fields['ping_date'],
                                                ) . "'></i>";
                                            $rand = mt_rand();
                                            $reserv = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>";
                                            $reserv .= "<i class='ti ti-clipboard pointer' style='color: #d56f15;font-size: 2em;' title='" . __(
                                                "Reserve IP",
                                                'addressing',
                                            ) . "'></i></a>";
                                            if (isset($params) && count(
                                                $params,
                                            ) > 0 && $is_html_output) {
                                                echo Ajax::createIframeModalWindow(
                                                    'reservation' . $rand,
                                                    "/plugins/addressing/ajax/addressing.php?action=showForm&ip=" . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $rand,
                                                    [
                                                        'title' => __s('IP reservation', 'addressing'),
                                                        'display' => false,
                                                        'reloadonclose' => true,
                                                    ],
                                                );
                                            }
                                        }
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(" ", $item_num, $row_num);
                                        $html_output .= $output::showItem(" ", $item_num, $row_num);
                                        $html_output .= $output::showItem(" ", $item_num, $row_num);
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                                    }
                                    if ($ping) {
                                        if ($is_html_output) {
                                            $html_output .= $output::showItem(
                                                "$content ",
                                                $item_num,
                                                $row_num,
                                                "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                            );
                                        } else {
                                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $content];
                                        }
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            "$reserv ",
                                            $item_num,
                                            $row_num,
                                            "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center'",
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $reserv];
                                    }
                                    $rand = mt_rand();
                                    $comment = new IpComment();
                                    $comment->getFromDBByCrit(
                                        ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()],
                                    );
                                    $comments = $comment->fields['comments'] ?? '';

                                    //                        $html_output .= $output::showItem( '<textarea id="comment'.$num.'"
                                    //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            '<input type="text" id="comment' . $num . '"
                      value="' . htmlspecialchars($comments, ENT_QUOTES, 'UTF-8') . '">',
                                            $item_num,
                                            $row_num,
                                            "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center' onChange='updateFA$rand()'",
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $comments];
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            '<i id="save' . $num . '" class="ti ti-device-floppy center pointer" style="color:forestgreen;font-size: 2em;"></i>',
                                            $item_num,
                                            $row_num,
                                            "style='background-color:var(--add-cell-bg, #e0e0e0)' class='center' onClick='updateComment$rand()'",
                                        );

                                        echo "<script>
                                              function updateComment$rand() {

                                                  $('#ajax_loader').show();
                                                  $.ajax({
                                                     url: '" . PLUGIN_ADDRESSING_WEBDIR . "/ajax/ipcomment.php',
                                                        type: 'POST',
                                                        data:
                                                          {
                                                            addressing_id:" . $Addressing->getID() . ",
                                                            ipname: \"" . $num . "\",
                                                            contentC: $('#comment" . $num . "').val(),

                                                          },
                                                        success: function(response){
                                                            $('#save" . $num . "').css('color','');
                                                            $('#save" . $num . "').css('color','forestgreen');
                                                            $('#ajax_loader').hide();

                                                         },
                                                        error: function(xhr, status, error) {
                                                           console.log(xhr);
                                                           console.log(status);
                                                           console.log(error);
                                                         }
                                                     });
                                               };

                                              function updateFA$rand() {
                                                  $('#save" . $num . "').css('color','');
                                                  $('#save" . $num . "').css('color','orange');

                                               };
                                             </script>";
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showEndLine(false);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if ($is_html_output) {
            //div for the modal
            echo "<div id=\"plugaddr_form\"  style=\"display:none;text-align:center\"></div>";
            $output::showFooter($Addressing->getTitle(), $numrows);
        }
        if ($is_html_output) {
            echo $html_output;
        } else {
            $params = [
                'start' => 0,
                'is_deleted' => 0,
                'as_map' => 0,
                'browse' => 0,
                'unpublished' => 1,
                'criteria' => [],
                'metacriteria' => [],
                'display_type' => 0,
                'hide_controls' => true,
            ];

            $addressing_data = SearchEngine::prepareDataForSearch($itemtype, $params);
            $addressing_data = array_merge($addressing_data, [
                'itemtype' => $itemtype,
                'data' => [
                    'totalcount' => $numrows,
                    'count' => $numrows,
                    'search' => '',
                    'cols' => [],
                    'rows' => self::escapeExportFormulas($rows),
                ],
            ]);

            $colid = 0;
            foreach ($headers as $header) {
                $addressing_data['data']['cols'][] = [
                    'name' => $header,
                    'itemtype' => $itemtype,
                    'id' => ++$colid,
                ];
            }

            $output->displayData($addressing_data, []);
        }
        return $ping_response;
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
