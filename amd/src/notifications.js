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

/* global localCspViolations */

import * as Notification from 'core/notification';
import Templates from 'core/templates';


export const init = (enforcedOnly) => {
    const msEventTimeout = 1000;
    let displayedAlready = false;

    /**
     * Takes an array of CSP violation events and displays them in the form of a warning notification.
     * @param {Array} events
     */
    async function displayNotification(events) {
        let enforcedEvents = [];
        let reportedEvents = [];
        events.forEach((event) => {
            if (event.disposition === 'enforce') {
                enforcedEvents.push(event);
            } else if (enforcedOnly === 0 && event.disposition === 'report') {
                reportedEvents.push(event);
            }
        });
        events.length = 0;
        let message = await Templates.render(
            'local_csp/notification',
            {firstDisplay: !displayedAlready, enforcedEvents: enforcedEvents, reportedEvents: reportedEvents}
        );
        displayedAlready = true;
        await Notification.addNotification({message: message, type: 'warning'});
    }

    /**
     * Calls `displayCspViolationNotification` to resolve if the `localCspViolations` array is not empty,
     * then resets (i.e. empties) it.
     */
    function displayIfOccurred() {
        if (localCspViolations.length > 0) {
            let events = [...localCspViolations];
            localCspViolations.length = 0;
            displayNotification(events).then(null);
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
            // to the global `localCspViolations` array. We just need to clear re-start the timer to display events
            // that were newly collected.
            clearTimeout(violationEventTimeout);
            violationEventTimeout = setTimeout(displayIfOccurred, msEventTimeout);
        });
    }

    startDisplaying();
};
