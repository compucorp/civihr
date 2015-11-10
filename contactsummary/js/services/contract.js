define([
    'lodash',
    'services/services',
    'services/model'
], function (_, services) {
    'use strict';

    /**
     * @param {ApiService} Api
     * @param {ModelService} Model
     * @param {ContactDetailsService} ContactDetails
     * @param $q
     * @param $log
     * @returns {ModelService|Object|ItemService|*}
     * @constructor
     */
    function ContractService(Api, Model, ContactDetails, $q, $log) {
        $log.debug('Service: Contract Service');

        ////////////////////
        // Public Members //
        ////////////////////

        /**
        * TODO: Implement a collection and extend it instead
        *
        * @ngdoc service
        * @name ContractService
        */
        //var factory = Model.createInstance();
        var factory = {};

        factory.collection = {
            items: {},
            insertItem: function (key, item) {
                this.items[key] = item;
            },
            getItem: function (key) {
                return this.items[key];
            },
            set: function (collection) {
                this.items = collection;
            },
            get: function () {
                return this.items;
            }
        };

        factory.getCollection = function () {
            return this.collection.get();
        };

        /**
         * @ngdoc method
         * @name get
         * @methodOf ContractService
         * @returns {*}
         */
        factory.get = function () {
            /** @type {(ContractService|ModelService)} */
            var self = this;

            return init().then(function () {
                //return self.getData();
                return self.getCollection();
            });
        };

        /**
         * @ngdoc method
         * @name getPrimary
         * @methodOf ContractService
         */
        factory.getPrimary = function () {
            return this.get().then(function (response) {
                var primary = {};

                angular.forEach(response, function (contract) {
                    if (contract.is_primary === '1' && contract.is_current === '1') {
                        primary = contract;
                    }
                });

                return primary;
            });
        };

        /**
         * @ngdoc method
         * @name getContracts
         * @methodOf ContractService
         * @returns {*}
         */
        factory.getContracts = function () {
            var deferred = $q.defer();

            if (_.isEmpty(contracts)) {
                ContactDetails.get()
                    .then(function (response) {
                        var data = {
                            contact_id: response.id,
                            'api.HRJobContractRevision.getcurrentrevision': { jobcontract_id: '$value.id' }
                        };

                        return Api.get('HRJobContract', data);
                    })
                    .then(function (response) {
                        var activeContracts = response.values.filter(function (contract) {
                            return parseInt(contract.deleted) === 0;
                        });

                        if (activeContracts.length === 0) {
                            return deferred.reject('No job contract found');
                        }

                        contracts = activeContracts;

                        deferred.resolve(contracts);
                    })
                    .catch(function (response) {
                        deferred.reject(response);
                    });
            } else {
                deferred.resolve(contracts);
            }

            return deferred.promise;
        };

        /**
         * @ngdoc method
         * @name getContractDetails
         * @methodOf ContractService
         * @param id
         * @returns {*}
         */
        factory.getContractDetails = function (id) {
            var addPay = function (details) {
                var pay = {};

                if (details.api_HRJobPay_get.values.length !== 0) {
                    pay.amount = details.api_HRJobPay_get.values[0].pay_amount;
                    pay.currency = details.api_HRJobPay_get.values[0].pay_currency;
                }

                details.pay = pay;
            };

            var addHours = function (details) {
                var hours = {};

                if (details.api_HRJobHour_get.values.length !== 0) {
                    hours.amount = details.api_HRJobHour_get.values[0].hours_amount;
                    hours.unit = details.api_HRJobHour_get.values[0].hours_unit;
                }

                details.hours = hours;
            };

            var data = {
                jobcontract_id: id,
                'api.HRJobPay.get': { 'jobcontract_id': id },
                'api.HRJobHour.get': { 'jobcontract_id': id }
            };

            return Api.post('HRJobDetails', data, 'get')
                .then(function (response) {
                    if (response.values.length === 0) {
                        return $q.reject('No details found for contract revision with ID ' + id);
                    }

                    var details = response.values[0];

                    addPay(details);
                    addHours(details);

                    return details;
                });
        };

        /////////////////////
        // Private Members //
        /////////////////////

        var contracts = [];

        function init() {
            var deferred = $q.defer();

            if (_.isEmpty(factory.collection.get())) {
                factory.getContracts()
                    .then(assembleContracts)
                    .finally(function () {
                        deferred.resolve();
                    });
            } else {
                deferred.resolve();
            }

            return deferred.promise;
        }

        function assembleContracts() {
            var deferred = $q.defer(), promises = [];

            angular.forEach(contracts, function (contract) {
                var assembledContract = {};

                assembledContract.id = contract.id;
                assembledContract.is_primary = contract.is_primary;
                assembledContract.is_current = contract.is_current;
                assembledContract.revision_id = null;

                if (contract.api_HRJobContractRevision_getcurrentrevision) {
                    assembledContract.revision_id = contract.api_HRJobContractRevision_getcurrentrevision.values.id;
                }

                var promise = factory.getContractDetails(contract.id)
                    .then(function (response) {
                        assembledContract.title = response.title;
                        assembledContract.start_date = response.period_start_date;
                        assembledContract.end_date = response.period_end_date;
                        assembledContract.type = response.contract_type;
                        assembledContract.pay = response.pay;
                        assembledContract.hours = response.hours;
                    })
                    .then(function () {
                        factory.collection.insertItem(contract.id, assembledContract);
                    });

                promises.push(promise);
            });

            $q.all(promises)
                .catch(function (response) {
                    $log.error('Something went wrong', response);
                })
                .finally(function () {
                    deferred.resolve();
                });

            return deferred.promise;
        }

        return factory;
    }

    services.factory('ContractService', ['ApiService', 'ModelService', 'ContactDetailsService', '$q', '$log', ContractService]);
});
