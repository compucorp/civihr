define([
    'moment',
    'modules/controllers',
    'services/contactDetails',
    'services/contract'
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
                this.primaryContract = response;
                this.primaryContract.lengthOfService = getLengthOfService(response.start_date, response.end_date);
            }.bind(this))
            .finally(function () {
                this.ready = true;
            }.bind(this));
    }

    /////////////////////
    // Private Members //
    /////////////////////

    function getLengthOfService(start, end) {
        start = moment(start, 'YYYY-MM-DD');
        end = end ? moment(end, 'YYYY-MM-DD') : moment();

        return moment.duration(end.diff(start)).humanize();
    }

    controllers.controller('KeyDetailsCtrl', ['$log', 'ContactDetailsService', 'ContractService', KeyDetailsCtrl]);
});
