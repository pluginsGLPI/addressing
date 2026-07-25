<?php

/*
 -------------------------------------------------------------------------
 addressing plugin for GLPI
 Copyright (C) 2016-2026 by the addressing Development Team.

 https://github.com/pluginsGLPI/addressing
 -------------------------------------------------------------------------

 LICENSE

 This file is part of addressing.

 addressing is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
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

        // Cast to int at the source: showList() is reached via displayTabContentForItem()
        // with $_GET, so $item['id'] is attacker-controlled. It is interpolated into a JS
        // function name emitted inside a <script> block (rendered |raw in the template); an
        // int cannot carry a </script> breakout or any XSS payload.
        $item_id = (int)($item['id'] ?? 0);
        $rand          = mt_rand();
        $p['readonly'] = false;

        if (is_array($options) && count($options)) {
            foreach ($options as $key => $val) {
                $p[$key] = $val;
            }
        }

        $canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]);

        if ($p['readonly']) {
            $canedit = false;
        }

        $nb = self::countForItem($item_id);

        // "Add filter" trigger script; item_id is cast to int above so it is safe to
        // interpolate into the emitted JS function name.
        $add_button_script = '';
        if ($canedit) {
            ob_start();
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
            $add_button_script = ob_get_clean();
        }

        $massiveactions_top    = '';
        $massiveactions_bottom = '';
        $checkall              = '';
        $close_form            = '';
        if ($canedit && $nb) {
            ob_start();
            Html::openMassiveActionsForm('mass' . $rand);
            $massiveactionparams = ['num_displayed'  => $nb,
                'check_items_id' => $item_id,
                'container'      => 'mass' . $rand];
            Html::showMassiveActions($massiveactionparams);
            $massiveactions_top = ob_get_clean();

            $checkall = Html::getCheckAllAsCheckbox('mass' . $rand);

            ob_start();
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            $massiveactions_bottom = ob_get_clean();

            // closeForm(false) returns the markup (including the CSRF hidden field)
            // instead of echoing it, so it can be placed by the Twig template.
            $close_form = Html::closeForm(false);
        }

        $types = Addressing::dropdownItemtype();
        $filter = new self();
        $datas = $filter->find(['plugin_addressing_addressings_id' => $item_id]);

        $rows = [];
        foreach ($datas as $filter_item) {
            $checkbox = $canedit ? Html::getMassiveActionCheckBox(__CLASS__, $filter_item['id']) : '';

            ob_start();
            echo "function viewEditFilter" . $filter_item["id"] . "$rand() {\n";
            $edit_params = ['action' => 'viewFilter',
                'items_id'   => $item_id,
                'id'         => $filter_item['id']];
            Ajax::updateItemJsCode(
                "viewfilter" . $item_id . "$rand",
                "/plugins/addressing/ajax/addressing.php",
                $edit_params
            );
            echo "};";
            $edit_script = ob_get_clean();

            // name/begin_ip/end_ip are stored as submitted by the user; leave them
            // unescaped here and let the Twig template's auto-escaping handle them
            // (this is what removes the stored XSS that existed in the legacy echo).
            $rows[] = [
                'id'          => $filter_item['id'],
                'name'        => $filter_item['name'],
                'entity_name' => Dropdown::getDropdownName('glpi_entities', $filter_item['entities_id']),
                'type_name'   => $types[$filter_item['type']] ?? '',
                'begin_ip'    => $filter_item['begin_ip'],
                'end_ip'      => $filter_item['end_ip'],
                'checkbox'    => $checkbox,
                'edit_script' => $edit_script,
            ];
        }

        TemplateRenderer::getInstance()->display('@addressing/filter_list.html.twig', [
            'item_id'               => $item_id,
            'rand'                  => $rand,
            'canedit'               => $canedit,
            'nb'                    => $nb,
            'add_button_script'     => $add_button_script,
            'massiveactions_top'    => $massiveactions_top,
            'massiveactions_bottom' => $massiveactions_bottom,
            'checkall'              => $checkall,
            'close_form'            => $close_form,
            'rows'                  => $rows,
        ]);
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
