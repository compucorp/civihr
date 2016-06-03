define([
    'common/lodash',
    'common/modules/apis',
    'common/services/api',
    'common/services/api/option-group'
], function (_, apis) {
    'use strict';

    apis.factory('api.appraisal-cycle', ['$q', '$log', 'api', 'api.optionGroup', function ($q, $log, api, optionGroupAPI) {
        $log.debug('api.appraisal-cycle');

        return api.extend({

            /**
             * Returns the list of appraisals
             *
             * @param {object} filters
             * @param {object} pagination
             * @param {string} sort
             * @return {Promise}
             */
            all: function (filters, pagination, sort) {
                $log.debug('api.appraisal-cycle.all');

                return this.getAll(
                    'AppraisalCycle',
                    filters,
                    pagination,
                    sort,
                    { 'api.AppraisalCycle.getappraisalsperstep': {} }
                );
            },

            /**
             * Creates a new appraisal cycle
             *
             * @param {object} attributes - The data of the new cycle
             * @return {Promise} resolves to the newly created cycle
             */
            create: function (attributes) {
                $log.debug('api.appraisal-cycle.create');

                return this.sendPOST('AppraisalCycle', 'create', _.assign(attributes, {
                        'api.AppraisalCycle.getappraisalsperstep': {}
                    }))
                    .then(function (data) {
                        return data.values[0];
                    });
            },

            /**
             * Finds the appraisal cycle with the given id
             *
             * @param {string/int} id
             * @return {Promise} resolves to the found cycle
             */
            find: function (id) {
                $log.debug('api.appraisal-cycle.find');

                var params = {
                    id: '' + id,
                    'api.AppraisalCycle.getappraisalsperstep': {}
                };

                return this.sendGET('AppraisalCycle', 'get', params, false).then(function (data) {
                    return data.values[0];
                });
            },

            /**
             * # TO DO #
             */
            grades: function () {
                $log.debug('grades');

                return this.mockGET([
                    { label: 1, value: 17 },
                    { label: 2, value: 74 },
                    { label: 3, value: 90 },
                    { label: 4, value: 30 }
                ]);
            },

            /**
             * Returns the list of all currently active status for an appraisal cycle
             *
             * @return {Promise}
             */
            statuses: function () {
                $log.debug('api.appraisal-cycle.statuses');

                return optionGroupAPI.valuesOf('appraisal_status');
            },

            /**
             * Returns the status overview re distribution of appraisals by step
             *
             * @param {object} params
             * @return {Promise}
             */
            statusOverview: function (params) {
                $log.debug('api.appraisal-cycle.statusOverview');

                return this.sendGET('AppraisalCycle', 'getstatusoverview', params)
                    .then(function (data) {
                        return data.values;
                    });
            },

            /**
             * Updates an appraisal cycle
             *
             * @param {object} attributes - The new data of the cycle
             * @return {Promise} resolves to the amended cycle
             */
            update: function (attributes) {
                $log.debug('api.appraisal-cycle.update');

                return this.sendPOST('AppraisalCycle', 'create', _.assign(attributes, {
                        'api.AppraisalCycle.getappraisalsperstep': {}
                    }))
                    .then(function (data) {
                        return data.values[0];
                    });
            },

            /**
             * Returns the total number of appraisal cycles
             *
             * @param {object} filters - The filters to apply to the count
             * @return {Promise} resolves to an integer
             */
            total: function (filters) {
                $log.debug('api.appraisal-cycle.total');

                return this.sendGET('AppraisalCycle', 'getcount', filters || {}).then(function (data) {
                    return data.result;
                });
            },

            /**
             * Returns the list of all currently active types for an appraisal cycle
             *
             * @return {Promise}
             */
            types: function () {
                $log.debug('api.appraisal-cycle.types');

                return optionGroupAPI.valuesOf('appraisal_cycle_type');
            }
        });
    }]);
});
