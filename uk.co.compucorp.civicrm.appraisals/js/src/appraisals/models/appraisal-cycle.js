define([
    'appraisals/modules/models',
    'common/services/api/appraisals'
], function (models) {
    'use strict';

    models.factory('AppraisalCycle', ['api.appraisals', function (appraisalsAPI) {

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
                return appraisalsAPI.create(attributes).then(function (newCycle) {
                    return newCycle;
                });
            },

            /**
             * Finds an appraisal cycle by id
             *
             * @param {string} id
             * @return {Promise} - Resolves with the new cycle
             */
            find: function (id) {
                return appraisalsAPI.find(id);
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
                return appraisalsAPI.statuses();
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
             * Updates the cycle with the given id
             *
             * @param {string} id
             * @param {object} attributes - The new data
             * @return {Promise}
             */
            update: function (id, attributes) {
                return appraisalsAPI.update(id, attributes).then(function (cycle) {
                    return cycle;
                });
            },

            /**
             * Returns the list of all possible appraisal cycle types
             *
             * @return {Promise}
             */
            types: function () {
                return appraisalsAPI.types();
            },
        };
    }]);
});
