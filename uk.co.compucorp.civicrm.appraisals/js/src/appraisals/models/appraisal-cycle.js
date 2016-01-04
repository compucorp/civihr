define([
    'common/lodash',
    'common/moment',
    'appraisals/modules/models',
    'common/services/api/appraisals'
], function (_, moment, models) {
    'use strict';

    models.factory('AppraisalCycle', ['api.appraisals', 'AppraisalCycleInstance', function (appraisalsAPI, instance) {

        // Draft

        return {

            /**
             * Returns the active cycles
             *
             * @return {Promise}
             */
            active: function () {
                return appraisalsAPI.activeCycles();
            },

            /**
             * Returns the all the appraisal cycles
             *
             * @param {object} filters - Values the full list should be filtered by
             * @param {object} pagination
             *   `page` for the current page, `size` for number of items per page
             * @return {Promise}
             */
            all: function (filters, pagination) {
                return appraisalsAPI.all(filters, pagination);
            },

            /**
             * Creates a new appraisal cycle
             *
             * @param {object} attributes - The attributes of the cycle to be created
             * @return {Promise} - Resolves with the new cycle
             */
            create: function (attributes) {
                var cycle = instance.init(attributes).toAPI();

                return appraisalsAPI.create(cycle).then(function (newCycle) {
                    return instance.init(newCycle, true);
                });
            },

            /**
             * Finds an appraisal cycle by id
             *
             * @param {string} id
             * @return {Promise} - Resolves with the new cycle
             */
            find: function (id) {
                return appraisalsAPI.find(id).then(function (cycle) {
                    return instance.init(cycle, true);
                });
            },

            /**
             * Returns the grades data
             *
             * @return {Promise}
             */
            grades: function () {
                return appraisalsAPI.grades();
            },

            /**
             * Returns the list of all possible appraisal cycle statuses
             *
             * @return {Promise}
             */
            statuses: function () {
                return appraisalsAPI.statuses().then(function (statuses) {
                    return statuses.map(function (status) {
                        return _.pick(status, ['value', 'label']);
                    });
                });
            },

            /**
             * Returns the full appraisal cycles status overview
             *
             * @return {Promise}
             */
            statusOverview: function () {
                return appraisalsAPI.statusOverview();
            },

            /**
             * Returns the list of all possible appraisal cycle types
             *
             * @return {Promise}
             */
            total: function () {
                return appraisalsAPI.total();
            },

            /**
             * Returns the list of all possible appraisal cycle types
             *
             * @return {Promise}
             */
            types: function () {
                return appraisalsAPI.types().then(function (types) {
                    return types.map(function (types) {
                        return _.pick(types, ['value', 'label']);
                    });
                });
            },
        };
    }]);
});
