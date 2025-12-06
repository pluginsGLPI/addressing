<?php

/*
 * @version $Id$
 -------------------------------------------------------------------------
 addressing plugin for GLPI
 Copyright (C) 2009-2022 by the addressing Development Team.

 https://github.com/pluginsGLPI/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 addressing is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with addressing. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Addressing;

use Ajax;
use CommonDBTM;
use DbUtils;
use Glpi\Search\Output\HTMLSearchOutput;
use Glpi\Search\SearchEngine;
use Html;
use Search;
use Session;
use Toolbox;
use User;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

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
    public function displaySearchNewLine($type, $odd = false)
    {
        $out = "";
        switch ($type) {
            case Search::PDF_OUTPUT_LANDSCAPE: //pdf
            case Search::PDF_OUTPUT_PORTRAIT:
                global $PDF_TABLE;
                $style = "";
                if ($odd) {
                    $style = " style=\"background-color:#DDDDDD;\" ";
                }
                $PDF_TABLE .= "<tr nobr=\"true\" $style>";
                break;

            //         case Search::SYLK_OUTPUT : //sylk
            //       $out="\n";
            //            break;

            case Search::CSV_OUTPUT: //csv
                //$out="\n";
                break;

            default:
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
                $out = "<tr $class>";
                break;
        }
        return $out;
    }


    /**
     * @param $result
     * @param $Addressing
     *
     * @return int
     */
    public function displayReport(&$result, $Addressing, $values, $ping_status = [])
    {
        global $CFG_GLPI;

        $ping = $Addressing->fields["use_ping"];

        // Get config
        $Config = new Config();
        $Config->getFromDB('1');
        $system = $Config->fields["used_system"];


        $default_values["start"] = $start = 0;
        $default_values["id"] = $id = 0;
        $default_values["export"] = $export = false;

        foreach ($default_values as $key => $val) {
            if (isset($values[$key])) {
                $$key = $values[$key];
            }
        }
        $itemtype = Addressing::class;
        // Set display type for export if define
        $output_type = $values["display_type"] ?? Search::HTML_OUTPUT;
        $output = SearchEngine::getOutputForLegacyKey($output_type);
        $is_html_output = $output instanceof HTMLSearchOutput;
        $html_output = '';

        if (isset($values["display_type"])) {
            $output_type = $values["display_type"];
        }

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
            $html_output .= $output::showNewLine();
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
                $html_output .= $output::showNewLine($i % 2 === 1);
                $ip = self::string2ip(substr($num, 2));

                if (count($lines)) {
                    if (count($lines) > 1) {
                        $disp = $Addressing->fields["double_ip"];
                    } else {
                        $disp = $Addressing->fields["alloted_ip"];
                    }
                    if ($disp) {
                        foreach ($lines as $line) {
                            $row_num++;
                            $item_num = 1;
                            $name = $line["dname"];
                            $namep = $line["pname"];
                            // IP
                            if ($Addressing->fields["reserved_ip"] && strstr(
                                    $line["pname"],
                                    "reserv"
                                )) {
                                $html_output .= $output::showNewLine("reserved");
                            } else {
                                $html_output .= $output::showNewLine(
                                    (count($lines) > 1 ? "double" : $row_num % 2)
                                );
                            }
                            $rand = mt_rand();
                            $params = [
                                'ip' => trim($ip),
                                'width' => 450,
                                'height' => 300,
                                'dialog_class' => 'modal-sm'
                            ];
                            $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>";
                            $ping_link .= "<i class='fas fa-terminal fa-1x pointer' style='color: orange' title='" . __(
                                    "IP ping",
                                    'addressing'
                                ) . "'></i></a>";

                            if ($is_html_output) {
                                $html_output .= $output::showItem("$ping_link ", $item_num, $row_num, "class='center'");
                            }
                            if (isset($params) && count($params) > 0 && $is_html_output) {
                                echo Ajax::createIframeModalWindow(
                                    'ping' . $rand,
                                    "/plugins/addressing/ajax/addressing.php?action=ping&ip=" . $params['ip'],
                                    [
                                        'title' => __s('IP ping', 'addressing'),
                                        'display' => false
                                    ]
                                );
                            }

                            if ($is_html_output) {
                                $html_output .= $output::showItem($ip, $item_num, $row_num);
                            } else {
                                $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $ip];
                            }
                            // Device
                            $item = new $line["itemtype"]();
                            $link = Toolbox::getItemTypeFormURL($line["itemtype"]);
                            if ($line["itemtype"] != 'NetworkEquipment') {
                                if ($item->canView()) {
                                    $output_iddev = "<a href='" . $link . "?id=" . $line["on_device"] . "'>" . $name
                                        . (empty($name) || $_SESSION["glpiis_ids_visible"] ? " (" . $line["on_device"] . ")" : "") . "</a>";
                                } else {
                                    $output_iddev = $name . (empty($name) || $_SESSION["glpiis_ids_visible"] ? " (" . $line["on_device"] . ")" : "");
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
                                    $output_iddev = $namep . " - " . $name . (empty($name) || $_SESSION["glpiis_ids_visible"] ? " (" . $line["on_device"] . ")" : "");
                                }
                            }
                            if ($is_html_output) {
                                $html_output .= $output::showItem($output_iddev, $item_num, $row_num);
                            } else {
                                $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $output_iddev];
                            }
                            // User
                            if ($line["users_id"] && $user->getFromDB($line["users_id"])) {
                                $dbu = new DbUtils();
                                $username = $dbu->formatUserName(
                                    $user->fields["id"],
                                    $user->fields["name"],
                                    $user->fields["realname"],
                                    $user->fields["firstname"]
                                );

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
                            if ($line["id"]) {
                                if ($item->canView()) {
                                    $output_mac = "<a href='" . $CFG_GLPI["root_doc"] . "/front/networkport.form.php?id="
                                        . $line["id"] . "'>" . $line["mac"] . "</a>";
                                } else {
                                    $output_mac = $line["mac"];
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
                                    'ipname' => $num
                                ])) {
                                    foreach ($pings as $ping) {
                                        $ping_value = $ping['ping_response'];
                                        $ping_date = $ping['ping_date'];
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
                                    $content = "<i class=\"fas fa-question fa-2x\" style='color: orange' title=\"" . __(
                                            "Automatic action has not be launched",
                                            'addressing'
                                        ) . "\"></i>";
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            "$content ",
                                            $item_num,
                                            $row_num,
                                            "style='background-color:#e0e0e0' class='center'"
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
                                        $reserv = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>
<i class='fas fa-clipboard fa-2x pointer' style='color: #d56f15' title='" . __(
                                                "Reserve IP",
                                                'addressing'
                                            ) . "'></i></a>";
                                        $html_output .= $output::showItem(
                                            "$reserv ",
                                            $item_num,
                                            $row_num,
                                            "style='background-color:#e0e0e0' class='center'"
                                        );
                                        if (isset($params) && count(
                                                $params
                                            ) > 0 && $is_html_output) {
                                            echo Ajax::createIframeModalWindow(
                                                'reservation' . $rand,
                                                "/plugins/addressing/ajax/addressing.php?action=showForm&ip=" . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $params['rand'],
                                                [
                                                    'title' => __s('IP reservation', 'addressing'),
                                                    'display' => false,
                                                    'reloadonclose' => true
                                                ]
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
                                                "<i class=\"fas fa-check-square fa-2x\" style='color: darkgreen' title='" . __(
                                                    "Last ping attempt",
                                                    'addressing'
                                                ) . " : "
                                                . Html::convDateTime($ping_date) . "'></i>",
                                                $item_num,
                                                $row_num,
                                                "style='background-color:#e0e0e0' class='center'"
                                            );

                                            if ($Addressing->fields["reserved_ip"] && strstr(
                                                    $line["pname"],
                                                    "reserv"
                                                )) {
                                                $reserv = "<i class='fas fa-clipboard-check fa-2x' style='color: #d56f15' title='" . __(
                                                        'Reserved Address',
                                                        'addressing'
                                                    ) . "'></i>";
                                                $html_output .= $output::showItem(
                                                    $reserv,
                                                    $item_num,
                                                    $row_num,
                                                    "style='background-color:#e0e0e0' class='center'"
                                                );
                                            } else {
                                                $html_output .= $output::showItem(
                                                    " ",
                                                    $item_num,
                                                    $row_num,
                                                    "style='background-color:#e0e0e0' class='center'"
                                                );
                                            }
                                        } else {
                                            $reserv = "";
                                            $content = __('Success', 'addressing');
                                            $html_output .= $output::showItem($content, $item_num, $row_num);
                                            if ($Addressing->fields["reserved_ip"] && strstr(
                                                    $line["pname"],
                                                    "reserv"
                                                )) {
                                                $reserv = __('Reserved', 'addressing');
                                            }
                                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $reserv];
                                        }
                                    } else {
                                        if ($is_html_output) {
                                            $html_output .= $output::showItem(
                                                "<i class=\"fas fa-window-close fa-2x\" style='color: darkred' title='"
                                                . __("Last ping attempt", 'addressing') . " : "
                                                . Html::convDateTime($ping_date) . "'></i>",
                                                $item_num,
                                                $row_num,
                                                "style='background-color:#e0e0e0' class='center'"
                                            );
                                            if ($Addressing->fields["reserved_ip"] && strstr(
                                                    $line["pname"],
                                                    "reserv"
                                                )) {
                                                $html_output .= $output::showItem(
                                                    "<i class='fas fa-clipboard-check fa-2x' style='color: #d56f15' title='"
                                                    . __('Reserved Address', 'addressing') . "'></i>",
                                                    $item_num,
                                                    $row_num,
                                                    "style='background-color:#e0e0e0' class='center'"
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
                                                $reserv = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>
<i class='fas fa-clipboard fa-2x pointer' style='color: #d56f15' title='" . __(
                                                        "Reserve IP",
                                                        'addressing'
                                                    ) . "'></i></a>";
                                                $html_output .= $output::showItem(
                                                    "$reserv ",
                                                    $item_num,
                                                    $row_num,
                                                    "style='background-color:#e0e0e0' class='center'"
                                                );
                                                if (isset($params) && count(
                                                        $params
                                                    ) > 0 && $is_html_output) {
                                                    echo Ajax::createIframeModalWindow(
                                                        'reservation' . $rand,
                                                        "/plugins/addressing/ajax/addressing.php?action=showForm&ip="
                                                        . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $params['rand'],
                                                        [
                                                            'title' => __s('IP reservation', 'addressing'),
                                                            'display' => false,
                                                            'reloadonclose' => true
                                                        ]
                                                    );
                                                }
                                            }
                                        } else {
                                            $content = __('Failed', 'addressing');
                                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $content];

                                            if ($Addressing->fields["reserved_ip"] && strstr(
                                                    $line["pname"],
                                                    "reserv"
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
                                        "style='background-color:#e0e0e0' class='center'"
                                    );
                                } else {
                                    $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => ""];
                                }
                            }

                            $rand = mt_rand();
                            $comment = new IpComment();
                            $comment->getFromDBByCrit(
                                ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()]
                            );
                            $comments = $comment->fields['comments'] ?? '';
                            //                  $html_output .= $output::showItem( '<textarea id="comment'.$num.'"
                            //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                            if ($is_html_output) {
                                $html_output .= $output::showItem(
                                    '<input type="text" id="comment' . $num . '"
                      value="' . $comments . '">',
                                    $item_num,
                                    $row_num,
                                    "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'"
                                );
                            } else {
                                $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $comments];
                            }
                            if ($is_html_output) {
                                $html_output .= $output::showItem(
                                    '<i id="save' . $num . '" class="fas fa-save fa-2x center pointer" style="color:forestgreen"></i>',
                                    $item_num,
                                    $row_num,
                                    "style='background-color:#e0e0e0' class='center' onClick='updateComment$rand()'"
                                );
                                echo "<script>

                                  function updateComment$rand() {

                                      $('#ajax_loader').show();
                                      $.ajax({
                                         url: '" . $CFG_GLPI["root_doc"] . PLUGIN_ADDRESSING_DIR_NOFULL . "/ajax/ipcomment.php',
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
                            $html_output .= $output::showEndLine(false);
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
                        $html_output .= $output::showNewLine("free");
                        $rand = mt_rand();
                        $params = [
                            'ip' => trim($ip),
                            'width' => 450,
                            'height' => 300,
                            'dialog_class' => 'modal-sm'
                        ];
                        $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>";
                        $ping_link .= "<i class='fas fa-terminal fa-1x pointer' style='color: orange' title='" . __(
                                "IP ping",
                                'addressing'
                            ) . "'></i></a>";
                        $html_output .= $output::showItem("$ping_link ", $item_num, $row_num, "class='center'");
                        if (isset($params) && count($params) > 0 && $is_html_output) {
                            echo Ajax::createIframeModalWindow(
                                'ping' . $rand,
                                "/plugins/addressing/ajax/addressing.php?action=ping&ip=" . $params['ip'],
                                [
                                    'title' => __s('IP ping', 'addressing'),
                                    'display' => false
                                ]
                            );
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
                            ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()]
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
                            $reserv .= "<i class='fas fa-clipboard fa-2x pointer' style='color: #d56f15' title='" . __(
                                    "Reserve IP",
                                    'addressing'
                                ) . "'></i></a>";
                            if (isset($params) && count($params) > 0 && $is_html_output) {
                                echo Ajax::createIframeModalWindow(
                                    'reservation' . $rand,
                                    "/plugins/addressing/ajax/addressing.php?action=showForm&ip=" . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $params['rand'],
                                    [
                                        'title' => __s('IP reservation', 'addressing'),
                                        'display' => false,
                                        'reloadonclose' => true
                                    ]
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
                                "style='background-color:#e0e0e0' class='center'"
                            );
                        } else {
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => " "];
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $reserv];
                        }

                        $rand = mt_rand();
                        $comment = new IpComment();
                        $comment->getFromDBByCrit(
                            ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()]
                        );
                        $comments = $comment->fields['comments'] ?? '';
                        //               $html_output .= $output::showItem( '<textarea id="comment'.$num.'"
                        //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                        if ($is_html_output) {
                            $html_output .= $output::showItem(
                                '<input type="text" id="comment' . $num . '"
                      value="' . $comments . '">',
                                $item_num,
                                $row_num,
                                "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'"
                            );
                        } else {
                            $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $comments];
                        }
                        if ($is_html_output) {
                            $html_output .= $output::showItem(
                                '<i id="save' . $num . '" class="fas fa-save fa-2x center pointer" style="color:forestgreen"></i>',
                                $item_num,
                                $row_num,
                                "style='background-color:#e0e0e0' class='center' onClick='updateComment$rand()'"
                            );
                            echo "<script>
                              function updateComment$rand() {

                                  $('#ajax_loader').show();
                                  $.ajax({
                                     url: '" . $CFG_GLPI["root_doc"] . PLUGIN_ADDRESSING_DIR_NOFULL . "/ajax/ipcomment.php',
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
                        $html_output .= $output::showEndLine(false);
                    } else {
                        $ping_action = NOT_AVAILABLE;
                        $plugin_addressing_pinginfo = new PingInfo();
                        if ($plugin_addressing_pinginfo->getFromDBByCrit([
                            'plugin_addressing_addressings_id' => $Addressing->getID(),
                            'ipname' => $num
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
                            'ipname' => $num
                        ]);

                        $content = "";
                        $reserv = "";
                        $see_ping_on = $ping_status[0] ?? 1;
                        $see_ping_off = $ping_status[1] ?? 1;
                        if ($see_ping_on == 1 || $see_ping_off == 1) {
                            if ($ping_value) {
                                if ($see_ping_on == 1) {
                                    $ping_response++;
                                    $html_output .= $output::showNewLine("ping_off");
                                    $rand = mt_rand();
                                    $params = [
                                        'ip' => trim($ip),
                                        'width' => 450,
                                        'height' => 300,
                                        'dialog_class' => 'modal-sm'
                                    ];
                                    $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>";
                                    $ping_link .= "<i class='fas fa-terminal fa-1x pointer' style='color: orange' title='" . __(
                                            "IP ping",
                                            'addressing'
                                        ) . "'></i></a>";
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            "$ping_link ",
                                            $item_num,
                                            $row_num,
                                            "class='center'"
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
                                                'display' => false
                                            ]
                                        );
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem($ip, $item_num, $row_num);
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $ip];
                                    }
                                    $title = __('Ping: got a response - used IP', 'addressing');
                                    //                        $Ping_Equipment = new Ping_Equipment();
                                    //                        $hostname = $Ping_Equipment->getHostnameByPing($system, $ip);
                                    //                        $text = iconv(mb_detect_encoding($hostname, mb_detect_order(), true), "UTF-8", $hostname);
                                    //                        $title .= "<br>".$text;
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            $title,
                                            $item_num,
                                            $row_num
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $title];
                                    }
                                    if ($is_html_output) {
                                        if ($ping_action == NOT_AVAILABLE) {
                                            $content = "<i class=\"fas fa-question fa-2x\" style='color: orange' title=\"" . __(
                                                    "Automatic action has not be launched",
                                                    'addressing'
                                                ) . "\"></i>";
                                        } else {
                                            $content = "<i class=\"fas fa-check-square fa-2x\" style='color: darkgreen' title='" . __(
                                                    "Last ping attempt",
                                                    'addressing'
                                                ) . " : "
                                                . Html::convDateTime(
                                                    $plugin_addressing_pinginfo->fields['ping_date']
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
                                                "style='background-color:#e0e0e0' class='center'"
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
                                            "style='background-color:#e0e0e0' class='center'"
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $reserv];
                                    }
                                    $rand = mt_rand();
                                    $comment = new IpComment();
                                    $comment->getFromDBByCrit(
                                        ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()]
                                    );
                                    $comments = $comment->fields['comments'] ?? '';
                                    //                        $html_output .= $output::showItem( '<textarea id="comment'.$num.'"
                                    //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            '<input type="text" id="comment' . $num . '"
                                 value="' . $comments . '">',
                                            $item_num,
                                            $row_num,
                                            "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'"
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $comments];
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            '<i id="save' . $num . '" class="fas fa-save fa-2x center pointer" style="color:forestgreen"></i>',
                                            $item_num,
                                            $row_num,
                                            "style='background-color:#e0e0e0' class='center' onClick='updateComment$rand()'"
                                        );

                                        echo "<script>
                                          function updateComment$rand() {

                                              $('#ajax_loader').show();
                                              $.ajax({
                                                 url: '" . $CFG_GLPI["root_doc"] . PLUGIN_ADDRESSING_DIR_NOFULL . "/ajax/ipcomment.php',
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
                                    $html_output .= $output::showEndLine(false);
                                }
                            } else {
                                if ($see_ping_off == 1) {
                                    $html_output .= $output::showNewLine("ping_on");
                                    $rand = mt_rand();
                                    $params = [
                                        'id_addressing' => $Addressing->getID(),
                                        'ip' => trim($ip),
                                        'rand' => $rand,
                                        'width' => 450,
                                        'height' => 300,
                                        'dialog_class' => 'modal-sm'
                                    ];
                                    $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>";
                                    $ping_link .= "<i class='fas fa-terminal fa-1x pointer' style='color: orange' title='" . __(
                                            "IP ping",
                                            'addressing'
                                        ) . "'></i></a>";
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            "$ping_link ",
                                            $item_num,
                                            $row_num,
                                            "class='center'"
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
                                                'display' => false
                                            ]
                                        );
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem($ip, $item_num, $row_num);
                                        $html_output .= $output::showItem(
                                            __('Ping: no response - free IP', 'addressing'),
                                            $item_num,
                                            $row_num
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $ip];
                                        $current_row[$itemtype . '_' . (++$colnum)] = [
                                            'displayname' => __(
                                                'Ping: no response - free IP',
                                                'addressing'
                                            )
                                        ];
                                    }
                                    $content = " ";
                                    if ($is_html_output) {
                                        if ($ping_action == NOT_AVAILABLE) {
                                            $content = "<i class=\"fas fa-question fa-2x\" style='color: orange' title=\"" . __(
                                                    "Automatic action has not be launched",
                                                    'addressing'
                                                ) . "\"></i>";
                                        } else {
                                            $content = "<i class=\"fas fa-window-close fa-2x\" style='color: darkred' title='" . __(
                                                    "Last ping attempt",
                                                    'addressing'
                                                ) . " : "
                                                . Html::convDateTime(
                                                    $plugin_addressing_pinginfo->fields['ping_date']
                                                ) . "'></i>";
                                            $rand = mt_rand();
                                            $reserv = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>";
                                            $reserv .= "<i class='fas fa-clipboard fa-2x pointer' style='color: #d56f15' title='" . __(
                                                    "Reserve IP",
                                                    'addressing'
                                                ) . "'></i></a>";
                                            if (isset($params) && count(
                                                    $params
                                                ) > 0 && $is_html_output) {
                                                echo Ajax::createIframeModalWindow(
                                                    'reservation' . $rand,
                                                    "/plugins/addressing/ajax/addressing.php?action=showForm&ip=" . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $rand,
                                                    [
                                                        'title' => __s('IP reservation', 'addressing'),
                                                        'display' => false,
                                                        'reloadonclose' => true
                                                    ]
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
                                                "style='background-color:#e0e0e0' class='center'"
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
                                            "style='background-color:#e0e0e0' class='center'"
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $reserv];
                                    }
                                    $rand = mt_rand();
                                    $comment = new IpComment();
                                    $comment->getFromDBByCrit(
                                        ['ipname' => $num, 'plugin_addressing_addressings_id' => $Addressing->getID()]
                                    );
                                    $comments = $comment->fields['comments'] ?? '';

                                    //                        $html_output .= $output::showItem( '<textarea id="comment'.$num.'"
                                    //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            '<input type="text" id="comment' . $num . '"
                      value="' . $comments . '">',
                                            $item_num,
                                            $row_num,
                                            "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'"
                                        );
                                    } else {
                                        $current_row[$itemtype . '_' . (++$colnum)] = ['displayname' => $comments];
                                    }
                                    if ($is_html_output) {
                                        $html_output .= $output::showItem(
                                            '<i id="save' . $num . '" class="fas fa-save fa-2x center pointer" style="color:forestgreen"></i>',
                                            $item_num,
                                            $row_num,
                                            "style='background-color:#e0e0e0' class='center' onClick='updateComment$rand()'"
                                        );

                                        echo "<script>
                                              function updateComment$rand() {

                                                  $('#ajax_loader').show();
                                                  $.ajax({
                                                     url: '" . $CFG_GLPI["root_doc"] . PLUGIN_ADDRESSING_DIR_NOFULL . "/ajax/ipcomment.php',
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

                                    $html_output .= $output::showEndLine(false);
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
                    'rows' => $rows,
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
