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

/* global getAjaxCsrfToken */

/**
 * Helpers shared by the modules of the plugin.
 */

/**
 * POST fields to an endpoint of the plugin.
 *
 * CheckCsrfListener rejects a POST without a token, and X-Requested-With routes the request
 * through the AJAX branch, which reads the token from the header.
 *
 * @param {string}                 url
 * @param {Object<string, string>} data
 *
 * @returns {Promise<Response>}
 */
export async function post(url, data) {
    const body = new FormData();
    Object.entries(data).forEach(([key, value]) => body.append(key, value));

    return fetch(url, {
        method: 'POST',
        body: body,
        headers: {
            'X-Glpi-Csrf-Token': getAjaxCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

/**
 * Replace the content of a container, running the scripts the new markup carries: the forms
 * GLPI renders set up their dropdowns with inline scripts, which innerHTML never runs.
 *
 * @param {HTMLElement} container
 * @param {string}      html
 */
export function replaceContent(container, html) {
    container.innerHTML = html;

    container.querySelectorAll('script').forEach((original) => {
        const script = document.createElement('script');
        Array.from(original.attributes).forEach((attribute) => {
            script.setAttribute(attribute.name, attribute.value);
        });
        script.textContent = original.textContent;
        original.replaceWith(script);
    });
}
