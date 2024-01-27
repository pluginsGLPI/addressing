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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginAddressingReport
 */
class PluginAddressingReport extends CommonDBTM {
   static $rightname = "plugin_addressing";

   /**
    * @param      $type
    * @param bool $odd
    *
    * @return string
    */
   function displaySearchNewLine($type, $odd = false) {

      $out = "";
      switch ($type) {
         case Search::PDF_OUTPUT_LANDSCAPE : //pdf
         case Search::PDF_OUTPUT_PORTRAIT :
            global $PDF_TABLE;
            $style = "";
            if ($odd) {
               $style = " style=\"background-color:#DDDDDD;\" ";
            }
            $PDF_TABLE .= "<tr nobr=\"true\" $style>";
            break;

         case Search::SYLK_OUTPUT : //sylk
            //       $out="\n";
            break;

         case Search::CSV_OUTPUT : //csv
            //$out="\n";
            break;

         default :
            $class = " class='tab_bg_2' ";
            if ($odd) {
               switch ($odd) {
                  case "double" : //double
                     $class = " class='plugin_addressing_ip_double'";
                     break;

                  case "free" : //free
                     $class = " class='plugin_addressing_ip_free'";
                     break;

                  case "reserved" : //free
                     $class = " class='plugin_addressing_ip_reserved'";
                     break;

                  case "ping_on" : //ping_on
                     $class = " class='plugin_addressing_ping_on'";
                     break;

                  case "ping_off" : //ping_off
                     $class = " class='plugin_addressing_ping_off'";
                     break;

                  default :
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
    * @param $PluginAddressingAddressing
    *
    * @return int
    */
   function displayReport(&$result, $PluginAddressingAddressing, $ping_status = []) {
      global $CFG_GLPI;

      $ping = $PluginAddressingAddressing->fields["use_ping"];

      // Get config
      $PluginAddressingConfig = new PluginAddressingConfig();
      $PluginAddressingConfig->getFromDB('1');
      $system = $PluginAddressingConfig->fields["used_system"];

      // Set display type for export if define
      $output_type = Search::HTML_OUTPUT;

      if (isset($_GET["display_type"])) {
         $output_type = $_GET["display_type"];
      }

      $header_num    = 1;
      $nbcols        = 8;
      $ping_response = 0;
      $row_num       = 1;

      // Column headers
      echo Search::showHeader($output_type, 1, $nbcols, 1);
      echo $this->displaySearchNewLine($output_type);
      echo Search::showHeaderItem($output_type, "", $header_num);
      echo Search::showHeaderItem($output_type, __('IP'), $header_num);
      echo Search::showHeaderItem($output_type, __('Connected to'), $header_num);
      echo Search::showHeaderItem($output_type, _n('User', 'Users', 1), $header_num);
      echo Search::showHeaderItem($output_type, __('MAC address'), $header_num);
      echo Search::showHeaderItem($output_type, __('Item type'), $header_num);
      if ($ping == 1) {
         echo Search::showHeaderItem($output_type, __('Ping result', 'addressing'), $header_num);
      }
      echo Search::showHeaderItem($output_type, __('Reservation', 'addressing'), $header_num);
      echo Search::showHeaderItem($output_type, __('Comments'), $header_num);
      echo Search::showHeaderItem($output_type, "", $header_num);
      echo Search::showEndLine($output_type);

      $user = new User();

      foreach ($result as $num => $lines) {
         $ip = self::string2ip(substr($num, 2));

         if (count($lines)) {
            if (count($lines) > 1) {
               $disp = $PluginAddressingAddressing->fields["double_ip"];
            } else {
               $disp = $PluginAddressingAddressing->fields["alloted_ip"];
            }
            if ($disp) {
               foreach ($lines as $line) {
                  $row_num++;
                  $item_num = 1;
                  $name     = $line["dname"];
                  $namep    = $line["pname"];
                  // IP
                  if ($PluginAddressingAddressing->fields["reserved_ip"] && strstr($line["pname"],
                                                                                   "reserv")) {
                     echo $this->displaySearchNewLine($output_type, "reserved");
                  } else {
                     echo $this->displaySearchNewLine($output_type,
                        (count($lines) > 1 ? "double" : $row_num % 2));
                  }
                  $rand = mt_rand();
                  $params = ['ip' => trim($ip),
                             'width'         => 450,
                             'height'        => 300,
                             'dialog_class'  => 'modal-sm'];
                  $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>
<i class='fas fa-terminal fa-1x pointer' style='color: orange' title='" . __("IP ping", 'addressing') . "'></i></a>";
                  echo Search::showItem($output_type, "$ping_link ", $item_num, $row_num, "class='center'");
                  if (isset($params) && count($params) > 0 && $output_type == Search::HTML_OUTPUT) {
                     echo Ajax::createIframeModalWindow('ping' . $rand,
                                                        PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php?action=ping&ip=" . $params['ip'],
                                                        ['title' => __s('IP ping', 'addressing'),
                                                         'display' => false]);
                  }
                  echo Search::showItem($output_type, $ip, $item_num, $row_num);

                  // Device
                  $item = new $line["itemtype"]();
                  $link = Toolbox::getItemTypeFormURL($line["itemtype"]);
                  if ($line["itemtype"] != 'NetworkEquipment') {
                     if ($item->canView()) {
                        $output_iddev = "<a href='" . $link . "?id=" . $line["on_device"] . "'>" . $name .
                                        (empty($name) || $_SESSION["glpiis_ids_visible"] ? " (" . $line["on_device"] . ")" : "") . "</a>";
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
                        $output_iddev = "<a href='" . $link . "?id=" . $line["on_device"] . "'>" . $linkp . $name .
                                        (empty($name) || $_SESSION["glpiis_ids_visible"] ? " (" . $line["on_device"] . ")" : "") . "</a>";
                     } else {
                        $output_iddev = $namep . " - " . $name . (empty($name) || $_SESSION["glpiis_ids_visible"] ? " (" . $line["on_device"] . ")" : "");
                     }
                  }
                  echo Search::showItem($output_type, $output_iddev, $item_num, $row_num);

                  // User
                  if ($line["users_id"] && $user->getFromDB($line["users_id"])) {
                     $dbu      = new DbUtils();
                     $username = $dbu->formatUserName($user->fields["id"], $user->fields["name"],
                                                      $user->fields["realname"], $user->fields["firstname"]);

                     if ($user->canView()) {
                        $output_iduser = "<a href='" . $CFG_GLPI["root_doc"] . "/front/user.form.php?id=" .
                                         $line["users_id"] . "'>" . $username . "</a>";
                     } else {
                        $output_iduser = $username;
                     }
                     echo Search::showItem($output_type, $output_iduser, $item_num, $row_num);
                  } else {
                     echo Search::showItem($output_type, " ", $item_num, $row_num);
                  }

                  // Mac
                  if ($line["id"]) {
                     if ($item->canView()) {
                        $output_mac = "<a href='" . $CFG_GLPI["root_doc"] . "/front/networkport.form.php?id=" .
                                      $line["id"] . "'>" . $line["mac"] . "</a>";
                     } else {
                        $output_mac = $line["mac"];
                     }
                     echo Search::showItem($output_type, $output_mac, $item_num, $row_num);
                  } else {
                     echo Search::showItem($output_type, " ", $item_num, $row_num);
                  }
                  // Type
                  echo Search::showItem($output_type, $item::getTypeName(), $item_num, $row_num);

                  // Ping
                  $ping_action = NOT_AVAILABLE;
                  if ($PluginAddressingAddressing->fields["free_ip"] && $ping) {
                     $plugin_addressing_pinginfo = new PluginAddressingPinginfo();
                     if ($pings = $plugin_addressing_pinginfo->find(['plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID(),
                                                                     'ipname'                           => $num])) {
                        foreach ($pings as $ping) {
                           $ping_value = $ping['ping_response'];
                           $ping_date  = $ping['ping_date'];
                        }
                        $ping_action = 1;
                     } else {
                        $ping_value = 0;
                        //                        $ping_value = $this->ping($system, $ip);
                        //                        $data = [];
                        //                        $data['plugin_addressing_addressings_id'] = $PluginAddressingAddressing->getID();
                        //                        $data['ipname'] = $num;
                        //                        $data['ping_response'] = $ping_value ?? 0;
                        //                        $data['ping_date'] = date('Y-m-d H:i:s');;
                        //                        $plugin_addressing_pinginfo->add($data);
                     }
                     //                     $plugin_addressing_pinginfo->getFromDBByCrit(['plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID(),
                     //                        'ipname' => $num]);

                     if ($ping_action == NOT_AVAILABLE) {

                        $content = "<i class=\"fas fa-question fa-2x\" style='color: orange' title=\"" . __("Automatic action has not be launched", 'addressing') . "\"></i>";
                         if ($output_type == Search::HTML_OUTPUT) {
                             echo Search::showItem($output_type, "$content ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                             $rand = mt_rand();
                             $params = ['id_addressing' => $PluginAddressingAddressing->getID(),
                                 'ip' => trim($ip),
                                 //                           'root_doc' => $CFG_GLPI['root_doc'],
                                 'rand' => $rand,
                                 //                           'width' => 1000,
                                 //                           'height' => 550
                             ];
                             $reserv = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>
<i class='fas fa-clipboard fa-2x pointer' style='color: #d56f15' title='" . __("Reserve IP", 'addressing') . "'></i></a>";
                             echo Search::showItem($output_type, "$reserv ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                             if (isset($params) && count($params) > 0 && $output_type == Search::HTML_OUTPUT) {
                                 echo Ajax::createIframeModalWindow('reservation' . $rand,
                                     PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php?action=showForm&ip=" . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $params['rand'],
                                     ['title' => __s('IP reservation', 'addressing'),
                                         'display' => false, 'reloadonclose' => true]);
                             }
                         } else {
                             $content = __('Unknown');
                             echo Search::showItem($output_type, "$content ", $item_num, $row_num);
                             echo Search::showItem($output_type, " ", $item_num, $row_num);
                         }

                     } else {
                        if ($ping_value) {
                            if ($output_type == Search::HTML_OUTPUT) {

                                echo Search::showItem($output_type, "<i class=\"fas fa-check-square fa-2x\" style='color: darkgreen' title='" . __("Last ping attempt", 'addressing') . " : "
                                    . Html::convDateTime($ping_date) . "'></i>", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");

                                if ($PluginAddressingAddressing->fields["reserved_ip"] && strstr($line["pname"], "reserv")) {
                                    $reserv = "<i class='fas fa-clipboard-check fa-2x' style='color: #d56f15' title='" . __('Reserved Address', 'addressing') . "'></i>";
                                    echo Search::showItem($output_type, "$reserv ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                                } else {
                                    echo Search::showItem($output_type, " ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                                }
                            } else {
                               $reserv = "";
                                $content = __('Success', 'addressing');
                                echo Search::showItem($output_type, "$content ", $item_num, $row_num);
                                if ($PluginAddressingAddressing->fields["reserved_ip"] && strstr($line["pname"], "reserv")) {
                                    $reserv = __('Reserved', 'addressing');
                                }
                                echo Search::showItem($output_type, $reserv, $item_num, $row_num);
                            }
                        } else {
                            if ($output_type == Search::HTML_OUTPUT) {
                                echo Search::showItem($output_type, "<i class=\"fas fa-window-close fa-2x\" style='color: darkred' title='" .
                                    __("Last ping attempt", 'addressing') . " : "
                                    . Html::convDateTime($ping_date) . "'></i>",
                                    $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                                if ($PluginAddressingAddressing->fields["reserved_ip"] && strstr($line["pname"], "reserv")) {
                                    echo Search::showItem($output_type, "<i class='fas fa-clipboard-check fa-2x' style='color: #d56f15' title='" .
                                        __('Reserved Address', 'addressing') . "'></i>",
                                        $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                                } else {
                                    $rand = mt_rand();
                                    $params = ['id_addressing' => $PluginAddressingAddressing->getID(),
                                        'ip' => trim($ip),
                                        //                                 'root_doc' => $CFG_GLPI['root_doc'],
                                        'rand' => $rand,
                                        //                                 'width' => 1000,
                                        //                                 'height' => 550
                                    ];
                                    $reserv = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>
<i class='fas fa-clipboard fa-2x pointer' style='color: #d56f15' title='" . __("Reserve IP", 'addressing') . "'></i></a>";
                                    echo Search::showItem($output_type, "$reserv ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                                    if (isset($params) && count($params) > 0 && $output_type == Search::HTML_OUTPUT) {
                                        echo Ajax::createIframeModalWindow('reservation' . $rand,
                                            PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php?action=showForm&ip=" .
                                            $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $params['rand'],
                                            ['title' => __s('IP reservation', 'addressing'),
                                                'display' => false, 'reloadonclose' => true]);
                                    }
                                }
                            } else {
                                $content = __('Failed', 'addressing');
                                echo Search::showItem($output_type, "$content ", $item_num, $row_num);
                                if ($PluginAddressingAddressing->fields["reserved_ip"] && strstr($line["pname"], "reserv")) {
                                    $reserv = __('Reserved', 'addressing');
                                } else {
                                    $reserv = "";
                                }
                                echo Search::showItem($output_type, $reserv, $item_num, $row_num);
                            }

                        }
                     }
                  } else {
                     echo Search::showItem($output_type, " ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                  }

                   $rand    = mt_rand();
                   $comment = new PluginAddressingIpcomment();
                   $comment->getFromDBByCrit(['ipname' => $num, 'plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID()]);
                   $comments = $comment->fields['comments'] ?? '';
                   $comments = Toolbox::stripslashes_deep($comments);
                   //                  echo Search::showItem($output_type, '<textarea id="comment'.$num.'"
                   //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                   if ($output_type == Search::HTML_OUTPUT) {
                       echo Search::showItem($output_type, '<input type="text" id="comment' . $num . '" 
                      value="' . $comments . '">', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                   } else {
                       echo Search::showItem($output_type, $comments, $item_num, $row_num);
                   }
                   if ($output_type == Search::HTML_OUTPUT) {
                       echo Search::showItem($output_type, '<i id="save' . $num . '" class="fas fa-save fa-2x center pointer" style="color:forestgreen"></i>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onClick='updateComment$rand()'");
                       echo "<script>
                    
                       
                      function updateComment$rand() {
                          
                          $('#ajax_loader').show();
                          $.ajax({
                             url: '" . $CFG_GLPI["root_doc"] . PLUGIN_ADDRESSING_DIR_NOFULL . "/ajax/ipcomment.php',
                                type: 'POST',
                                data:
                                  {
                                    addressing_id:" . $PluginAddressingAddressing->getID() . ",
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
                  echo Search::showEndLine($output_type);
               }
            }

         } else if ($PluginAddressingAddressing->fields["free_ip"]) {
            $row_num++;
            $item_num = 1;
            $content  = "";

            $rand   = mt_rand();
            $params = ['id_addressing' => $PluginAddressingAddressing->getID(),
                       'ip'            => trim($ip),
                       //               'root_doc' => $CFG_GLPI['root_doc'],
                       'rand'          => $rand,
                       //               'width' => 1000,
                       //               'height' => 550
            ];

            if (!$ping) {
               echo $this->displaySearchNewLine($output_type, "free");
               $rand = mt_rand();
               $params = ['ip' => trim($ip),
                          'width'         => 450,
                          'height'        => 300,
                          'dialog_class'  => 'modal-sm'];
               $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>
<i class='fas fa-terminal fa-1x pointer' style='color: orange' title='" . __("IP ping", 'addressing') . "'></i></a>";
               echo Search::showItem($output_type, "$ping_link ", $item_num, $row_num, "class='center'");
               if (isset($params) && count($params) > 0 && $output_type == Search::HTML_OUTPUT) {
                  echo Ajax::createIframeModalWindow('ping' . $rand,
                                                     PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php?action=ping&ip=" . $params['ip'],
                                                     ['title' => __s('IP ping', 'addressing'),
                                                      'display' => false]);
               }
               echo Search::showItem($output_type, $ip, $item_num, $row_num);
               echo Search::showItem($output_type, " ", $item_num, $row_num);
               echo Search::showItem($output_type, " ", $item_num, $row_num);

                $rand    = mt_rand();
                $comment = new PluginAddressingIpcomment();
                $comment->getFromDBByCrit(['ipname' => $num, 'plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID()]);
                $comments = $comment->fields['comments'] ?? '';
                $comments = Toolbox::stripslashes_deep($comments);

               if ($output_type == Search::HTML_OUTPUT) {
                  $content = "";
                  $params = ['id_addressing' => $PluginAddressingAddressing->getID(),
                             'ip' => trim($ip),
                             //                                 'root_doc' => $CFG_GLPI['root_doc'],
                             'rand' => $rand,
                             //                                 'width' => 1000,
                             //                                 'height' => 550
                  ];
                  $reserv  = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>
<i class='fas fa-clipboard fa-2x pointer' style='color: #d56f15' title='" . __("Reserve IP", 'addressing') . "'></i></a>";
                  if (isset($params) && count($params) > 0 && $output_type == Search::HTML_OUTPUT) {
                     echo Ajax::createIframeModalWindow('reservation'.$rand,
                                                        PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php?action=showForm&ip=" . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $params['rand'],
                                                        ['title'   => __s('IP reservation', 'addressing'),
                                                         'display' => false, 'reloadonclose' => true]);
                  }
               } else {
                  $content = "";
                  $reserv  = "";
               }
               echo Search::showItem($output_type, " ", $item_num, $row_num);
               echo Search::showItem($output_type, " ", $item_num, $row_num);
               echo Search::showItem($output_type, "$reserv ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");

                $rand    = mt_rand();
                $comment = new PluginAddressingIpcomment();
                $comment->getFromDBByCrit(['ipname' => $num, 'plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID()]);
                $comments = $comment->fields['comments'] ?? '';
                $comments = Toolbox::stripslashes_deep($comments);
                //               echo Search::showItem($output_type, '<textarea id="comment'.$num.'"
                //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                if ($output_type == Search::HTML_OUTPUT) {
                    echo Search::showItem($output_type, '<input type="text" id="comment' . $num . '" 
                      value="' . $comments . '">', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                } else {
                    echo Search::showItem($output_type, $comments, $item_num, $row_num);
                }
                if ($output_type == Search::HTML_OUTPUT) {
                    echo Search::showItem($output_type, '<i id="save' . $num . '" class="fas fa-save fa-2x center pointer" style="color:forestgreen"></i>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onClick='updateComment$rand()'");
                    echo "<script>
                    
                       
                      function updateComment$rand() {
                          
                          $('#ajax_loader').show();
                          $.ajax({
                             url: '" . $CFG_GLPI["root_doc"] . PLUGIN_ADDRESSING_DIR_NOFULL . "/ajax/ipcomment.php',
                                type: 'POST',
                                data:
                                  {
                                    addressing_id:" . $PluginAddressingAddressing->getID() . ",
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
               echo Search::showEndLine($output_type);
            } else {
               if ($output_type == Search::HTML_OUTPUT) {
                  Html::glpi_flush();
               }
               $ping_action                = NOT_AVAILABLE;
               $plugin_addressing_pinginfo = new PluginAddressingPinginfo();
               if ($plugin_addressing_pinginfo->getFromDBByCrit(['plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID(),
                                                                 'ipname'                           => $num])) {
                  $ping_value  = $plugin_addressing_pinginfo->fields['ping_response'];
                  $ping_action = 1;
               } else {
                  $ping_value = 0;
                  //                  $ping_value = $this->ping($system, $ip);
                  //                  $data = [];
                  //                  $data['plugin_addressing_addressings_id'] = $PluginAddressingAddressing->getID();
                  //                  $data['ipname'] = $num;
                  //                  $data['ping_response'] = $ping_value ?? 0;
                  //                  $data['ping_date'] = date('Y-m-d H:i:s');;
                  //                  $plugin_addressing_pinginfo->add($data);

               }

               $plugin_addressing_pinginfo->getFromDBByCrit(['plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID(),
                                                             'ipname'                           => $num]);

               $content      = "";
               $reserv       = "";
               $see_ping_on  = $ping_status[0] ?? 1;
               $see_ping_off = $ping_status[1] ?? 1;
               if ($see_ping_on == 1 || $see_ping_off == 1) {

                  if ($ping_value) {
                     if ($see_ping_on == 1) {
                        $ping_response++;
                        echo $this->displaySearchNewLine($output_type, "ping_off");
                        $rand = mt_rand();
                        $params = ['ip' => trim($ip),
                                   'width'         => 450,
                                   'height'        => 300,
                                   'dialog_class'  => 'modal-sm'];
                        $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>
<i class='fas fa-terminal fa-1x pointer' style='color: orange' title='" . __("IP ping", 'addressing') . "'></i></a>";
                        echo Search::showItem($output_type, "$ping_link ", $item_num, $row_num, "class='center'");
                        if (isset($params) && count($params) > 0 && $output_type == Search::HTML_OUTPUT) {
                           echo Ajax::createIframeModalWindow('ping' . $rand,
                                                              PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php?action=ping&ip=" . $params['ip'],
                                                              ['title' => __s('IP ping', 'addressing'),
                                                               'display' => false]);
                        }
                        echo Search::showItem($output_type, $ip, $item_num, $row_num);
                        $title = __('Ping: got a response - used IP', 'addressing');
                        //                        $PluginAddressingPing_Equipment = new PluginAddressingPing_Equipment();
                        //                        $hostname = $PluginAddressingPing_Equipment->getHostnameByPing($system, $ip);
                        //                        $text = iconv(mb_detect_encoding($hostname, mb_detect_order(), true), "UTF-8", $hostname);
                        //                        $title .= "<br>".$text;

                        echo Search::showItem($output_type, $title,
                                              $item_num, $row_num);
                         if ($output_type == Search::HTML_OUTPUT) {
                             if ($ping_action == NOT_AVAILABLE) {
                                 $content = "<i class=\"fas fa-question fa-2x\" style='color: orange' title=\"" . __("Automatic action has not be launched", 'addressing') . "\"></i>";
                             } else {
                                 $content = "<i class=\"fas fa-check-square fa-2x\" style='color: darkgreen' title='" . __("Last ping attempt", 'addressing') . " : "
                                     . Html::convDateTime($plugin_addressing_pinginfo->fields['ping_date']) . "'></i>";

                             }
                         } else {
                             $content = __('Success', 'addressing');
                         }

                        $reserv = "";
                        echo Search::showItem($output_type, " ", $item_num, $row_num);
                        echo Search::showItem($output_type, " ", $item_num, $row_num);
                        echo Search::showItem($output_type, " ", $item_num, $row_num);
                        if ($ping) {
                           echo Search::showItem($output_type, "$content ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                        }
                        echo Search::showItem($output_type, "$reserv ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");

                         $rand    = mt_rand();
                         $comment = new PluginAddressingIpcomment();
                         $comment->getFromDBByCrit(['ipname' => $num, 'plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID()]);
                         $comments = $comment->fields['comments'] ?? '';
                         $comments = Toolbox::stripslashes_deep($comments);
                         //                        echo Search::showItem($output_type, '<textarea id="comment'.$num.'"
                         //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                         if ($output_type == Search::HTML_OUTPUT) {
                             echo Search::showItem($output_type, '<input type="text" id="comment' . $num . '" 
                                 value="' . $comments . '">', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                         } else {
                             echo Search::showItem($output_type, $comments, $item_num, $row_num);
                         }
                         if ($output_type == Search::HTML_OUTPUT) {
                             echo Search::showItem($output_type, '<i id="save' . $num . '" class="fas fa-save fa-2x center pointer" style="color:forestgreen"></i>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onClick='updateComment$rand()'");

                             echo "<script>
                    
                       
                      function updateComment$rand() {
                          
                          $('#ajax_loader').show();
                          $.ajax({
                             url: '" . $CFG_GLPI["root_doc"] . PLUGIN_ADDRESSING_DIR_NOFULL . "/ajax/ipcomment.php',
                                type: 'POST',
                                data:
                                  {
                                    addressing_id:" . $PluginAddressingAddressing->getID() . ",
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
                        echo Search::showEndLine($output_type);
                     }

                  } else {
                     if ($see_ping_off == 1) {
                        echo $this->displaySearchNewLine($output_type, "ping_on");
                        $rand = mt_rand();
                        $params = ['id_addressing' => $PluginAddressingAddressing->getID(),
                                   'ip' => trim($ip),
                                   'rand' => $rand,
                                   'width'         => 450,
                                   'height'        => 300,
                                   'dialog_class'  => 'modal-sm'];
                        $ping_link = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#ping$rand'>
<i class='fas fa-terminal fa-1x pointer' style='color: orange' title='" . __("IP ping", 'addressing') . "'></i></a>";
                        echo Search::showItem($output_type, "$ping_link ", $item_num, $row_num, "class='center'");
                        if (isset($params) && count($params) > 0 && $output_type == Search::HTML_OUTPUT) {
                           echo Ajax::createIframeModalWindow('ping' . $rand,
                                                              PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php?action=ping&ip=" . $params['ip'],
                                                              ['title' => __s('IP ping', 'addressing'),
                                                               'display' => false]);
                        }
                        echo Search::showItem($output_type, $ip, $item_num, $row_num);
                        echo Search::showItem($output_type, __('Ping: no response - free IP', 'addressing'),
                                              $item_num, $row_num);

                        $content = " ";
                        if ($output_type == Search::HTML_OUTPUT) {
                           if ($ping_action == NOT_AVAILABLE) {
                              $content = "<i class=\"fas fa-question fa-2x\" style='color: orange' title=\"" . __("Automatic action has not be launched", 'addressing') . "\"></i>";
                           } else {
                              $content = "<i class=\"fas fa-window-close fa-2x\" style='color: darkred' title='" . __("Last ping attempt", 'addressing') . " : "
                                         . Html::convDateTime($plugin_addressing_pinginfo->fields['ping_date']) . "'></i>";
                              $rand = mt_rand();
                              $reserv  = "<a href=\"#\" data-bs-toggle='modal' data-bs-target='#reservation$rand'>
<i class='fas fa-clipboard fa-2x pointer' style='color: #d56f15' title='" . __("Reserve IP", 'addressing') . "'></i></a>";
                              if (isset($params) && count($params) > 0 && $output_type == Search::HTML_OUTPUT) {
                                 echo Ajax::createIframeModalWindow('reservation'.$rand,
                                                                    PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php?action=showForm&ip=" . $params['ip'] . "&id_addressing=" . $params['id_addressing'] . "&rand=" . $rand,
                                                                    ['title'   => __s('IP reservation', 'addressing'),
                                                                     'display' => false, 'reloadonclose' => true]);
                              }
                           }
                        }
                        echo Search::showItem($output_type, " ", $item_num, $row_num);
                        echo Search::showItem($output_type, " ", $item_num, $row_num);
                        echo Search::showItem($output_type, " ", $item_num, $row_num);
                        if ($ping) {
                           echo Search::showItem($output_type, "$content ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");
                        }
                        echo Search::showItem($output_type, "$reserv ", $item_num, $row_num, "style='background-color:#e0e0e0' class='center'");

                         $rand    = mt_rand();
                         $comment = new PluginAddressingIpcomment();
                         $comment->getFromDBByCrit(['ipname' => $num, 'plugin_addressing_addressings_id' => $PluginAddressingAddressing->getID()]);
                         $comments = $comment->fields['comments'] ?? '';

                         $comments = Toolbox::stripslashes_deep($comments);
                         //                        echo Search::showItem($output_type, '<textarea id="comment'.$num.'"
                         //                      rows="5" cols="33">'.$comments.'</textarea>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                         if ($output_type == Search::HTML_OUTPUT) {
                             echo Search::showItem($output_type, '<input type="text" id="comment' . $num . '" 
                      value="' . $comments . '">', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onChange='updateFA$rand()'");
                         } else {
                             echo Search::showItem($output_type, $comments, $item_num, $row_num);
                         }
                         if ($output_type == Search::HTML_OUTPUT) {
                             echo Search::showItem($output_type, '<i id="save' . $num . '" class="fas fa-save fa-2x center pointer" style="color:forestgreen"></i>', $item_num, $row_num, "style='background-color:#e0e0e0' class='center' onClick='updateComment$rand()'");

                             echo "<script>
                    
                       
                      function updateComment$rand() {
                          
                          $('#ajax_loader').show();
                          $.ajax({
                             url: '" . $CFG_GLPI["root_doc"] . PLUGIN_ADDRESSING_DIR_NOFULL . "/ajax/ipcomment.php',
                                type: 'POST',
                                data:
                                  {
                                    addressing_id:" . $PluginAddressingAddressing->getID() . ",
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

                        echo Search::showEndLine($output_type);
                     }
                  }
               }
            }
         }
      }

      if ($output_type == Search::HTML_OUTPUT) {
         //div for the modal
         echo "<div id=\"plugaddr_form\"  style=\"display:none;text-align:center\"></div>";
      }
      // Display footer
      echo Search::showFooter($output_type, $PluginAddressingAddressing->getTitle());

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
   static function string2ip($s) {
      if ($s > PHP_INT_MAX) {
         $s = 2 * PHP_INT_MIN + $s;
      }
      return long2ip($s);
   }

   static function ip2string($s) {
      if ($s > PHP_INT_MAX) {
         $s = 2 * PHP_INT_MIN + $s;
      }
      return ip2long($s);
   }
}
