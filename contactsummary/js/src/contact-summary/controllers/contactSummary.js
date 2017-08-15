define([
    'contact-summary/modules/controllers',
    'contact-summary/modules/settings',
    'contact-summary/services/contact'
], function (controllers) {
    'use strict';

    /**
     * @ngdoc controller
     * @name ContactSummaryCtrl
     * @param $log
     * @param {ContactService} Contact
     * @param settings
     * @constructor
     */
    function ContactSummaryCtrl($log, Contact, settings) {
        $log.debug('Controller: ContactSummaryCtrl');

        var templateDir = settings.pathBaseUrl + settings.pathTpl;

        this.partials = {
            keyDetails: templateDir + '/include/keyDetails.html',
            keyDates: templateDir + '/include/keyDates.html'
        };

        this.ready = false;

        // Contact.get().then(function (response) {
        //     $log.debug('Contact', response);
        //     this.contact = response;
        //     this.ready = true;
        // }.bind(this));
    }

    controllers.controller('ContactSummaryCtrl', ['$log', 'ContactService', 'settings', ContactSummaryCtrl]);
});
