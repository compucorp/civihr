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
        var now = moment();

        start = moment(start, 'YYYY-MM-DD');
        end = end ? moment(end, 'YYYY-MM-DD') : now;

        if(end.isAfter(now)) {
            end = now;
        }

        var lengthOfService = moment.duration(end.diff(start));

        return {
            days: lengthOfService.days(),
            months: lengthOfService.months(),
            years: lengthOfService.years()
        };
    }

    controllers.controller('KeyDetailsCtrl', ['$log', 'ContactDetailsService', 'ContractService', KeyDetailsCtrl]);
});
