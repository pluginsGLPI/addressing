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
use CommonGLPI;
use Dropdown;
use Entity;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Session;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class Filter
 */
class Filter extends CommonDBTM
{

    public static $rightname = "plugin_addressing";

    public static function getTypeName($nb = 0)
    {

        return _n('Filter', 'Filters', $nb, 'addressing');
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == Addressing::class) {
            if ($tabnum == 0) {
                self::showList($_GET);
            }
        }
        return true;
    }

    public static function getIcon()
    {
        return "ti ti-filter";
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nb = self::countForItem($item->fields['id']);
        return [self::createTabEntry(self::getTypeName(1), $nb)];
    }

    public function getForbiddenStandardMassiveAction()
    {

        $forbidden = parent::getForbiddenStandardMassiveAction();

        $forbidden[] = 'update';

        return $forbidden;
    }

   /**
    * Form of filter
    * @param  $ID
    * @param  $options
    * @return boolean
    */
    public function showForm($ID, $options = [])
    {

        $this->initForm($ID, $options);
        $options['colspan'] = 1;
        $options['types'] = Addressing::dropdownItemtype();
        TemplateRenderer::getInstance()->display('@addressing/filter.html.twig', [
            'item' => $this,
            'params' => $options,
        ]);

        return true;

    }

   /**
    * Filter list
    * @param  $item
    * @param  $options
    */
    public static function showList($item, $options = [])
    {

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
            $style   = "class='tab_cadre_fixe'";
        }

           //button add filter
        if ($canedit) {
            echo "<div id='viewfilter" . $item_id . "$rand'></div>\n";

            echo "<script type='text/javascript' >\n";
            echo "function viewAddFilter" . $item_id . "$rand() {\n";
            $params = ['action' => 'viewFilter',
            'items_id'   => $item_id,
            'id'         => -1];
            Ajax::updateItemJsCode(
                "viewfilter" . $item_id . "$rand",
                "/plugins/addressing/ajax/addressing.php",
                $params
            );
            echo "};";
            echo "</script>\n";
            echo "<div class='center firstbloc'>" .
            "<a class='submit btn btn-primary me-2' href='javascript:viewAddFilter" . $item_id . "$rand();'>";
            echo __('Add a filter', 'addressing') . "</a></div>\n";
        }

        echo "<div class='spaced'>";

        $nb = Filter::countForItem($item['id']);

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
    * @param  $item
    * @param  $filter
    * @param  $canedit
    * @param  $rand
    */
    public function showMinimalFilterForm($item, $filter, $canedit, $rand)
    {
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
            Ajax::updateItemJsCode(
                "viewfilter" . $item["id"] . "$rand",
                "/plugins/addressing/ajax/addressing.php",
                $params
            );
            echo "};";
            echo "</script>\n";
            echo "</td>";
        }

           //display of data backup
        echo "<td $edit>" . $filter['name'] . "</td>";
        echo "<td $edit>" . Dropdown::getDropdownName('glpi_entities', $filter['entities_id']) . "</td>";
        $types = Addressing::dropdownItemtype();
        echo "<td $edit>" . $types[$filter['type']] . "</td>";
        echo "<td $edit>" . $filter['begin_ip'] . "</td>";
        echo "<td $edit>" . $filter['end_ip'] . "</td>";
        echo "</tr>\n";
    }

   /**
    * Dropdown of filters
    * @param  $id
    * @param  $value
    */
    public static function dropdownFilters($id, $value)
    {
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
    * @param $item
    * @return int
    */
    public static function countForItem($id)
    {
        $filter = new self();
        $datas = $filter->find(['plugin_addressing_addressings_id' => $id]);
        return count($datas);
    }
}
