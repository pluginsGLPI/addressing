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
 * Class PluginAddressingFilter
 */
class PluginAddressingFilter extends CommonDBTM {

   static $rightname = "plugin_addressing";

   static function getTypeName($nb = 0) {

      return _n('Filter', 'Filters', $nb, 'addressing');
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'PluginAddressingAddressing') {
         if ($tabnum == 0) {
            self::showList($_GET);
         }
      }
      return true;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $nb = self::countForItem($item->fields['id']);
      return [self::createTabEntry(self::getTypeName(1), $nb)];
   }

   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();

      $forbidden[] = 'update';

      return $forbidden;
   }

   /**
    * Form of filter
    * @global type $CFG_GLPI
    * @param type $ID
    * @param type $options
    * @return boolean
    */
   function showForm($ID, $options = []) {

      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         $this->check(-1, CREATE, $options);
      }

      Html::requireJs("addressing");

      $options['formoptions']
            = "onSubmit='return plugaddr_Check(\"".__('Invalid data !!', 'addressing')."\")'";
      $options['colspan'] = 1;
      $this->showFormHeader($options);

      $addressing = new PluginAddressingAddressing();
      $addressing->getFromDB($options['items_id']);

      echo "<tr class='tab_bg_1'>";

      echo Html::hidden('id', ['value' => $ID]);
      echo Html::hidden('plugin_addressing_addressings_id', ['value' => $options['items_id']]);
      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      echo Html::input('name', ['value' => $this->fields['name'], 'size' => 40]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Entity') . "</td>";
      echo "<td>";
      Entity::dropdown(['name' => 'entities_id', 'value' => $this->fields["entities_id"]]);
      echo "</td>";
      echo "</tr>";

       echo "<tr class='tab_bg_1'>
               <td>".__("Type")."</td>
               <td>";
      $types = PluginAddressingAddressing::dropdownItemtype();
      Dropdown::showFromArray('type', $types,
                              ['value' => $this->fields["type"]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('First IP', 'addressing')."</td>"; // Subnet
      echo "<td>";
      if (empty($this->fields["begin_ip"])) {
         $this->fields["begin_ip"] = "...";
      }
      $ipexploded = explode(".", $this->fields["begin_ip"]);
      $i = 0;
      foreach ($ipexploded as $ipnum) {
         if ($ipnum > 255) {
            $ipexploded[$i] = '';
         }
         $i++;
      }

      echo Html::input('_ipdeb0', ['value' => $ipexploded[0],
                                   'id' => 'plugaddr_ipdeb0',
                                   'size' => 3,
                                   'maxlength' => 3,
                                   'class' => 'form-inline']);
      echo Html::input('_ipdeb1', ['value' => $ipexploded[0],
                                   'id' => 'plugaddr_ipdeb1',
                                   'size' => 3,
                                   'maxlength' => 3,
                                   'class' => 'form-inline']);
      echo Html::input('_ipdeb2', ['value' => $ipexploded[0],
                                   'id' => 'plugaddr_ipdeb2',
                                   'size' => 3,
                                   'maxlength' => 3,
                                   'class' => 'form-inline']);
      echo Html::input('_ipdeb3', ['value' => $ipexploded[0],
                                   'id' => 'plugaddr_ipdeb3',
                                   'size' => 3,
                                   'maxlength' => 3,
                                   'class' => 'form-inline']);

      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Last IP', 'addressing')."</td>"; // Mask
      echo "<td>";

      unset($ipexploded);
      if (empty($this->fields["end_ip"])) {
         $this->fields["end_ip"] = "...";
      }
      $ipexploded = explode(".", $this->fields["end_ip"]);
      $j = 0;
      foreach ($ipexploded as $ipnum) {
         if ($ipnum > 255) {
            $ipexploded[$j] = '';
         }
         $j++;
      }

      echo "<script type='text/javascript'>
      function test(id) {
         if (document.getElementById('plugaddr_ipfin' + id).value == '') {
            if (id == 3) {
               document.getElementById('plugaddr_ipfin' + id).value = '254';
            } else {
               document.getElementById('plugaddr_ipfin' + id).value = ".
         "document.getElementById('plugaddr_ipdeb' + id).value;
            }
         }
      }
      </script>";

      echo Html::input('_ipfin0', ['value' => $ipexploded[0],
                                   'id' => 'plugaddr_ipfin0',
                                   'size' => 3,
                                   'maxlength' => 3,
                                   'class' => 'form-inline',
                                   'onfocus'=>'test(0)']);
      echo Html::input('_ipfin1', ['value' => $ipexploded[0],
                                   'id' => 'plugaddr_ipfin1',
                                   'size' => 3,
                                   'maxlength' => 3,
                                   'class' => 'form-inline',
                                   'onfocus'=>'test(1)']);
      echo Html::input('_ipfin2', ['value' => $ipexploded[0],
                                   'id' => 'plugaddr_ipfin2',
                                   'size' => 3,
                                   'maxlength' => 3,
                                   'class' => 'form-inline',
                                   'onfocus'=>'test(2)']);
      echo Html::input('_ipfin3', ['value' => $ipexploded[0],
                                   'id' => 'plugaddr_ipfin3',
                                   'size' => 3,
                                   'maxlength' => 3,
                                   'class' => 'form-inline',
                                   'onfocus'=>'test(3)']);

      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Report for the IP Range', 'addressing')."</td>"; // Mask
      echo "<td>";
      echo Html::hidden('begin_ip', ['id' => 'plugaddr_ipdeb', 'value' => $this->fields["begin_ip"]]);
      echo Html::hidden('end_ip', ['id' => 'plugaddr_ipfin', 'value' => $this->fields["end_ip"]]);
      echo "<div id='plugaddr_range'>-</div>";
      if ($ID > 0) {
         $js = "plugaddr_Init(\"".__('Invalid data !!', 'addressing')."\");";
         echo Html::scriptBlock('$(document).ready(function() {'.$js.'});');
      }
      echo "</td>";
      echo "</tr>";

      $this->showFormButtons($options);

      return true;
   }

   /**
    * Filter list
    * @global type $CFG_GLPI
    * @param type $item
    * @param type $options
    */
   static function showList($item, $options = []) {
      global $CFG_GLPI;

      $item_id = $item['id'];
      $rand          = mt_rand();
      $p['readonly'] = false;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]);
      $style   = "class='tab_cadre_fixehov'";

      if ($p['readonly']) {
         $canedit = false;
         $style   = "class='tab_cadrehov'";
      }

      //button add filter
      if ($canedit) {
         echo "<div id='viewfilter" . $item_id . "$rand'></div>\n";

         echo "<script type='text/javascript' >\n";
         echo "function viewAddFilter" . $item_id . "$rand() {\n";
         $params = ['action' => 'viewFilter',
            'items_id'   => $item_id,
            'id'         => -1];
         Ajax::updateItemJsCode("viewfilter" . $item_id . "$rand",
                                PLUGIN_ADDRESSING_WEBDIR . "/ajax/addressing.php",
                                $params);
         echo "};";
         echo "</script>\n";
         echo "<div class='center firstbloc'>" .
         "<a class='submit btn btn-primary me-2' href='javascript:viewAddFilter" . $item_id . "$rand();'>";
         echo __('Add a filter', 'addressing') . "</a></div>\n";
      }

      echo "<div class='spaced'>";

      $nb = PluginAddressingFilter::countForItem($item['id']);

      if ($canedit && $nb) {
         Html::openMassiveActionsForm('mass' . $rand);
         $massiveactionparams = ['num_displayed'  => $nb,
            'check_items_id' => $item_id,
            'container'      => 'mass' . $rand
           ];
         Html::showMassiveActions($massiveactionparams);
      }
      if ($nb) {
         echo "<table $style>";
         echo "<tr class='noHover'>" .
         "<th colspan='" . ($canedit && $nb ? " 6 " : "5") . "'>" . self::getTypeName(2) . "</th>" .
         "</tr>\n";

         $header_begin  = "<tr>";
         $header_top    = '';
         $header_bottom = '';
         $header_end    = '';

         if ($canedit && $nb) {
            $header_top .= "<th width='10'>";
            $header_top .= Html::getCheckAllAsCheckbox('mass' . $rand);
            $header_top .= "</th>";
            $header_bottom .= "<th width='10'>";
            $header_bottom .= Html::getCheckAllAsCheckbox('mass' . $rand);
            $header_bottom .= "</th>";
         }
         $header_end .= "<th class='center b'>" . __('Name') . "</th>\n";
         $header_end .= "<th class='center b'>" . __('Entity') . "</th>\n";
         $header_end .= "<th class='center b'>" . __('Type') . "</th>\n";
         $header_end .= "<th class='center b'>" . __('First IP', 'addressing') . "</th>\n";
         $header_end .= "<th class='center b'>" . __('Last IP', 'addressing') . "</th>\n";
         $header_end .= "</tr>\n";
         echo $header_begin . $header_top . $header_end;

         //filters list
         $filter = new self();
         $datas = $filter->find(['plugin_addressing_addressings_id' => $item_id]);

         foreach ($datas as $filter_item) {
            $filter->showMinimalFilterForm($item, $filter_item, $canedit, $rand);
         }

         if ($nb) {
            echo $header_begin . $header_bottom . $header_end;
         }
         echo "</table>\n";
      }
      if ($canedit && $nb) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }

      echo "</div>\n";
   }

   /**
    * Form of an element
    * @global type $CFG_GLPI
    * @param type $item
    * @param type $filter
    * @param type $canedit
    * @param type $rand
    */
   function showMinimalFilterForm($item, $filter, $canedit, $rand) {
      global $CFG_GLPI;

      $edit = ($canedit ? "style='cursor:pointer' onClick=\"viewEditFilter"
            . $filter["id"] . "$rand();\"" : '');
      echo "<tr class='tab_bg_1' >";
      if ($canedit) {
         echo "<td width='10'>";
         Html::showMassiveActionCheckBox(__CLASS__, $filter["id"]);
         echo "\n<script type='text/javascript' >\n";
         echo "function viewEditFilter" . $filter["id"] . "$rand() {\n";
         $params = ['action' => 'viewFilter',
            'items_id'   => $item["id"],
            'id'         => $filter['id']];
         Ajax::updateItemJsCode("viewfilter" . $item["id"] . "$rand",
                                PLUGIN_ADDRESSING_WEBDIR. "/ajax/addressing.php",
                                $params);
         echo "};";
         echo "</script>\n";
         echo "</td>";
      }

      //display of data backup
      echo "<td $edit>" . $filter['name'] . "</td>";
      echo "<td $edit>" . Dropdown::getDropdownName('glpi_entities', $filter['entities_id']) . "</td>";
      $types = PluginAddressingAddressing::dropdownItemtype();
      echo "<td $edit>" . $types[$filter['type']] . "</td>";
      echo "<td $edit>" . $filter['begin_ip'] . "</td>";
      echo "<td $edit>" . $filter['end_ip'] . "</td>";
      echo "</tr>\n";
   }

   /**
    * Dropdown of filters
    * @param type $id
    * @param type $value
    */
   static function dropdownFilters($id, $value) {
      $filter = new self();
      $datas = $filter->find(['plugin_addressing_addressings_id' => $id]);
      $filters = [];
      $filters[0] = Dropdown::EMPTY_VALUE;
      foreach ($datas as $data) {
         $filters[$data['id']] = $data['name'];
      }
      Dropdown::showFromArray('filter', $filters, ['value' => $value]);
   }

   /**
    * Count of filters
    * @param type $item
    * @return type
    */
   static function countForItem($id) {
      $filter = new self();
      $datas = $filter->find(['plugin_addressing_addressings_id' => $id]);
      return count($datas);
   }

}
