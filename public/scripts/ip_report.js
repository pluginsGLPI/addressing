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

import {post} from './common.js';

/**
 * Behaviours of the IP report (templates/report.html.twig and templates/report_ip_list.html.twig):
 * the switches of the filtering form, saving the comment of an address, the iframe modals of the
 * ping and of the reservation, and the manual launch of the ping of the range.
 */

const UNSAVED_CLASS = 'plugin_addressing_icon_unsaved';

function toggleLoader(visible) {
    document.getElementById('ajax_loader')?.classList.toggle('d-none', !visible);
}

// Flip a switch of the filtering form and the hidden field posted with it.
function toggleSwitch(button) {
    const name = button.getAttribute('data-addressing-toggle');
    const field = button.closest('form')?.querySelector(`input[name="${CSS.escape(name)}"]`);
    const icon = button.querySelector('i');
    if (!field || !icon) {
        return;
    }
    const enabled = field.value !== '1';
    field.value = enabled ? '1' : '0';
    icon.classList.toggle('fa-toggle-on', enabled);
    icon.classList.toggle('enabled', enabled);
    icon.classList.toggle('fa-toggle-off', !enabled);
    icon.classList.toggle('disabled', !enabled);
}

async function saveComment(button) {
    const report = button.closest('[data-addressing-report]');
    const ipname = button.getAttribute('data-addressing-save');
    const input = report?.querySelector(`[data-addressing-comment="${CSS.escape(ipname)}"]`);
    if (!report || !input) {
        return;
    }

    toggleLoader(true);
    try {
        const response = await post(report.getAttribute('data-addressing-comment-url'), {
            addressing_id: report.getAttribute('data-addressing-id'),
            ipname: ipname,
            contentC: input.value,
        });
        if (response.ok) {
            button.querySelector('i')?.classList.remove(UNSAVED_CLASS);
        }
    } finally {
        toggleLoader(false);
    }
}

async function launchPing(button) {
    const form = button.closest('form');
    toggleLoader(true);
    try {
        const response = await post(button.getAttribute('data-addressing-url'), {
            addressing_id: form?.querySelector('input[name=id]')?.value ?? '0',
        });
        if (response.ok && (await response.text()).trim() === '1') {
            window.location.reload();
        }
    } finally {
        toggleLoader(false);
    }
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-addressing-toggle]');
    if (toggle !== null) {
        event.preventDefault();
        toggleSwitch(toggle);
        return;
    }

    const save = event.target.closest('[data-addressing-save]');
    if (save !== null) {
        saveComment(save);
        return;
    }

    const ping = event.target.closest('[data-addressing-launch-ping]');
    if (ping !== null) {
        event.preventDefault();
        launchPing(ping);
    }
});

document.addEventListener('input', (event) => {
    const input = event.target.closest('[data-addressing-comment]');
    if (input === null) {
        return;
    }
    const ipname = input.getAttribute('data-addressing-comment');
    input.closest('[data-addressing-report]')
        ?.querySelector(`[data-addressing-save="${CSS.escape(ipname)}"] i`)
        ?.classList.add(UNSAVED_CLASS);
});

// Bootstrap passes the element that opened the modal as relatedTarget: it carries the URL
// loaded in the iframe, so a single modal of each kind serves every address of the report.
document.addEventListener('show.bs.modal', (event) => {
    const modal = event.target;
    if (!modal.matches('[data-addressing-iframe-modal]')) {
        return;
    }
    const url = event.relatedTarget?.closest('[data-addressing-modal-url]')?.getAttribute('data-addressing-modal-url');
    const iframe = modal.querySelector('iframe');
    if (url && iframe) {
        iframe.setAttribute('src', url);
    }
});

document.addEventListener('hide.bs.modal', (event) => {
    const modal = event.target;
    if (!modal.matches('[data-addressing-iframe-modal]')) {
        return;
    }
    if (modal.hasAttribute('data-addressing-reload-on-close')) {
        window.location.reload();
        return;
    }
    modal.querySelector('iframe')?.setAttribute('src', 'about:blank');
});
