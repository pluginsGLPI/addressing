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

import {post, replaceContent} from './common.js';

/**
 * Behaviours of the forms of the plugin: the IP fields of a range and of a filter, the add and
 * edit forms of a filter, the ping of an equipment and the name check of a reservation.
 */

// templates/filter_list.html.twig: the add and edit forms are loaded under the list.
async function loadFilterForm(trigger) {
    const target = document.getElementById(trigger.getAttribute('data-addressing-filter-target'));
    if (target === null) {
        return;
    }

    const response = await post(trigger.getAttribute('data-addressing-filter-url'), {
        action: 'viewFilter',
        items_id: trigger.getAttribute('data-addressing-filter-range'),
        id: trigger.getAttribute('data-addressing-filter-form'),
    });
    if (response.ok) {
        replaceContent(target, await response.text());
    }
}

// templates/pinginfo.html.twig: the ping form of the equipment is loaded under the result.
async function loadPingForm(button) {
    const target = document.getElementById(button.getAttribute('data-addressing-ping-target'));
    if (target === null) {
        return;
    }

    const response = await post(button.getAttribute('data-addressing-ping-form'), {
        action: 'viewPingform',
        items_id: button.getAttribute('data-addressing-items-id'),
        itemtype: button.getAttribute('data-addressing-itemtype'),
    });
    if (response.ok) {
        target.innerHTML = await response.text();
        target.classList.remove('d-none');
    }
}

// templates/ping_equipment_form.html.twig
async function pingEquipment(button) {
    const form = button.closest('[data-addressing-ping-equipment]');
    const output = form?.querySelector('[data-addressing-ping-output]');
    if (!form || !output) {
        return;
    }

    const response = await post(form.getAttribute('data-addressing-ping-equipment'), {
        ip: form.querySelector('[data-addressing-ping-ip]')?.value ?? '',
        itemtype: form.getAttribute('data-addressing-itemtype'),
        items_id: form.getAttribute('data-addressing-items-id'),
    });
    // ajax/ping.php escapes every line of the probe and only joins them with <br />.
    output.innerHTML = response.ok ? await response.text() : '';
}

// templates/reserveip.html.twig: tell whether the name of the new asset is already in use.
async function checkName(input) {
    const url = new URL(input.getAttribute('data-addressing-name-check'), window.location.href);
    url.search = new URLSearchParams({
        action: 'isName',
        name: input.value,
        type: input.form?.querySelector('select[name="type"]')?.value ?? '0',
    }).toString();

    const response = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
    if (!response.ok) {
        return;
    }
    const used = (await response.json()) === true;
    document.getElementById('nameUsedItem')?.classList.toggle('d-none', !used);
    document.getElementById('nameNotUsedItem')?.classList.toggle('d-none', used);
}

document.addEventListener('click', (event) => {
    const filter = event.target.closest('[data-addressing-filter-form]');
    if (filter !== null) {
        loadFilterForm(filter);
        return;
    }

    const ping_form = event.target.closest('[data-addressing-ping-form]');
    if (ping_form !== null) {
        event.preventDefault();
        loadPingForm(ping_form);
        return;
    }

    const ping = event.target.closest('[data-addressing-ping-run]');
    if (ping !== null) {
        pingEquipment(ping);
        return;
    }

    const close = event.target.closest('[data-addressing-ping-close]');
    if (close !== null) {
        document.getElementById(close.getAttribute('data-addressing-ping-close'))?.classList.add('d-none');
    }
});

document.addEventListener('change', (event) => {
    const name = event.target.closest('[data-addressing-name-check]');
    if (name !== null) {
        checkName(name);
    }
});

// An empty byte of the last IP is prefilled from the first IP, the last one with 254.
document.addEventListener('focusin', (event) => {
    const input = event.target.closest('[data-addressing-ip="end_ip"]');
    if (input === null || input.value !== '') {
        return;
    }

    const index = input.getAttribute('data-index');
    if (index === '3') {
        input.value = '254';
        return;
    }
    const begin = (input.form ?? document).querySelector(
        `[data-addressing-ip="begin_ip"][data-index="${CSS.escape(index)}"]`,
    );
    input.value = begin?.value ?? '';
});
