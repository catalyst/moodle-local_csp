/* global localCspViolationEvents:readonly */
import ModalFactory from 'core/modal_factory';
import Templates from 'core/templates';
import * as Str from 'core/str';
export const init = (enforcedOnly) => {
    /**
     * Once recording CSP violations has stopped, this function will set up and display a modal with the summary.
     * Since this is not implemented as a callback function, we have no choice but to repeatedly check, if the event
     * array has been frozen, before creating the modal.
     */
    function getModal() {
        if (!Object.isFrozen(localCspViolationEvents)) {
            window.setTimeout(getModal, 500);
        } else {
            let enforcedEvents = [];
            let reportedEvents = [];
            localCspViolationEvents.forEach((e) => {
               if (e.disposition === 'enforce') {
                   enforcedEvents.push(e);
               } else if (!enforcedOnly && e.disposition === 'report') {
                   reportedEvents.push(e);
               }
            });
            if (enforcedEvents.length + reportedEvents.length > 0) {
                ModalFactory.create({
                    type: ModalFactory.types.ALERT,
                    title: Str.get_string('modalheader', 'local_csp'),
                    body: Templates.render(
                        'local_csp/modal_body',
                        {enforcedEvents: enforcedEvents, reportedEvents: reportedEvents}
                    ),
                })
                .then(function(modal) {
                    modal.show();
                });
            }
        }
    }
    getModal();
};
