define([
    'common/moment',
    'contact-summary/modules/controllers',
    'contact-summary/services/contactDetails',
    'contact-summary/services/contract'
], function (moment, controllers) {
'use strict';

    /**
     * @ngdoc controller
     * @name KeyDetailsCtrl
     * @param $log
     * @param {ContactDetailsService} ContactDetails
     * @param {ContractService} Contract
     * @constructor
     */
    function KeyDetailsCtrl($log, ContactDetails, Contract) {
        $log.debug('Controller: KeyDetailsCtrl');

        this.ready = false;

        ContactDetails.get()
            .then(function (response) {
                this.contactDetails = response;

                return Contract.getPrimary();
            }.bind(this))
            .then(function (response) {
                if (_.isEmpty(response)) {
                    return;
                }

                this.primaryContract = response;
            }.bind(this)).then(function (response) {
                return Contract.getLengthOfServiceYmd();
            })
            .then(function (response) {
                this.lengthOfService = response;
            }
            .bind(this))
            .finally(function () {
                this.ready = true;
            }.bind(this));
    }

    /////////////////////
    // Private Members //
    /////////////////////

    controllers.controller('KeyDetailsCtrl', ['$log', 'ContactDetailsService', 'ContractService', KeyDetailsCtrl]);
});
