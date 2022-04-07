// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/* global localCspViolationsEnforced, localCspViolationsReported */

import * as Notification from 'core/notification';
import * as Str from 'core/str';
import Templates from 'core/templates';


export const init = (msEventTimeout) => {
    const stringsRequestsEnforced = [
        {key: 'notificationenforcedheader', component: 'local_csp'},
        {key: 'notificationenforcedstart', component: 'local_csp'},
        {key: 'notificationenforcedsources', component: 'local_csp'},
    ];
    const stringsRequestsReported = [
        {key: 'notificationreportedheader', component: 'local_csp'},
        {key: 'notificationreportedstart', component: 'local_csp'},
        {key: 'notificationreportedsources', component: 'local_csp'},
    ];

    /**
     * Displays the events from either the `localCspViolationsEnforced` or the `localCspViolationsReported` array
     * in the form of a notification, clearing that array in the process.
     * @param {String} disposition
     */
    async function displayNotification(disposition) {
        let strings, events, type;
        if (disposition === 'enforce') {
            strings = await Str.get_strings(stringsRequestsEnforced);
            events = [...localCspViolationsEnforced];
            localCspViolationsEnforced.length = 0;
            type = 'error';
        } else if (disposition === 'report') {
            strings = await Str.get_strings(stringsRequestsReported);
            events = [...localCspViolationsReported];
            localCspViolationsReported.length = 0;
            type = 'warning';
        } else {
            throw "Invalid disposition! Must be either 'enforce' or 'report'.";
        }
        let message = await Templates.render(
            'local_csp/notification',
            {
                header: strings[0],
                start: strings[1],
                sourcesText: strings[2],
                events: events
            }
        );
        await Notification.addNotification({message: message, type: type});
    }

    /**
     * Calls `displayNotification` to resolve, if the `localCspViolationsEnforced` array is not empty.
     * Then calls `displayNotification` to resolve, if the `localCspViolationsReported` array is not empty.
     */
    function displayIfOccurred() {
        if (localCspViolationsEnforced.length > 0) {
            displayNotification('enforce').then(null);
        }
        if (localCspViolationsReported.length > 0) {
            displayNotification('report').then(null);
        }
    }

    /**
     * Waits for a bit before calling `displayIfOccurred`.
     * Also attaches another listener to the violation event that calls `displayIfOccurred` after a bit of waiting,
     * unless the event occurs again in the meantime.
     */
    function startDisplaying() {
        let violationEventTimeout = setTimeout(displayIfOccurred, msEventTimeout);
        document.addEventListener("securitypolicyviolation", () => {
            // There is no need to handle the event object itself here because the early event listener will push it
            // to one of the global arrays. We just need to clear and re-start the timer to display events that were
            // newly collected.
            clearTimeout(violationEventTimeout);
            violationEventTimeout = setTimeout(displayIfOccurred, msEventTimeout);
        });
    }

    startDisplaying();
};
