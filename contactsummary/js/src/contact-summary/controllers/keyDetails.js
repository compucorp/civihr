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

    function formatDuration(duration) {
        var texts = [],
            days = duration.days(),
            months = duration.months(),
            years = duration.years();

        if (years > 0) {
            texts.push(years + " years");
        }

        if (months > 0) {
            texts.push(months + " months");
        }

        if (texts.length === 0) {
            texts.push(days + " days");
        }

        return texts.join(' ');
    }

    function getLengthOfService(start, end) {
        var now = moment();

        start = moment(start, 'YYYY-MM-DD');
        end = end ? moment(end, 'YYYY-MM-DD') : now;

        if(end.isAfter(now)) {
            end = now;
        }

        return formatDuration(moment.duration(end.diff(start)));
    }

    controllers.controller('KeyDetailsCtrl', ['$log', 'ContactDetailsService', 'ContractService', KeyDetailsCtrl]);
});
