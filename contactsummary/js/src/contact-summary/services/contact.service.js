define([
    'common/lodash',
    'contact-summary/modules/contact-summary.services',
    'contact-summary/services/model.service',
    'contact-summary/services/contact-details.service',
    'contact-summary/services/contract.service'
], function (_, services) {
    'use strict';

    /**
     * @param {ModelService} Model
     * @param ContactDetails
     * @param {ContractService} Contract
     * @param $q
     * @param $log
     * @returns {Object}
     * @constructor
     */
    function ContactService($log, $q, Model, ContactDetails, Contract) {
        $log.debug('Service: ContactService');

        ////////////////////
        // Public Members //
        ////////////////////

        /**
         * @ngdoc service
         * @name ContactService
         */
        var factory = Model.createInstance();

        /**
         * @ngdoc method
         * @name get
         * @methodOf ContactService
         * @returns {*}
         */
        factory.get = function () {
            /** @type {(ContactService|ModelService)} */
            var self = this;

            return init().then(function () {
                return self.getData();
            });
        };

        return factory;

        /////////////////////
        // Private Members //
        /////////////////////

        function init() {
            var deferred = $q.defer();

            if (_.isEmpty(factory.getData())) {
                initContactDetails()
                    .then(initContract)
                    .then(function () {
                        deferred.resolve();
                    });
            } else {
                deferred.resolve();
            }

            return deferred.promise;
        }

        function initContactDetails() {
            return ContactDetails.get()
                .then(function (response) {
                    factory.setDataKey('id', response.id);
                    factory.setDataKey('dateOfBirth', response.dateOfBirth);
                    factory.setDataKey('age', response.age);
                });
        }

        function initContract() {
            return Contract.get()
                .then(function (response) {
                    factory.setDataKey('contract', response);
                });
        }
    }

    services.factory('ContactService', ['$log', '$q', 'ModelService', 'ContactDetailsService', 'ContractService', ContactService]);
});
