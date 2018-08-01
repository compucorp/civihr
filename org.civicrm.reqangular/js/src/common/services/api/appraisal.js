define([
    'common/modules/apis',
    'common/services/api'
], function (apis) {
    'use strict';

    apis.factory('api.appraisal', ['$log', 'api', function ($log, api) {
        $log.debug('api.appraisal');

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
                $log.debug('api.appraisal.api');

                return this.getAll('Appraisal', filters, pagination, sort);
            },

            /**
             * Creates a new appraisal
             *
             * @param {object} attributes - The data of the new appraisal
             * @return {Promise} resolves to the newly created appraisal
             */
            create: function (attributes) {
                $log.debug('api.appraisal.create');

                return this.sendPOST('Appraisal', 'create', attributes)
                    .then(function (data) {
                        return data.values[0];
                    });
            },

            /**
             * Finds the appraisal with the given id
             *
             * @param {string/int} id
             * @return {Promise} resolves to the found appraisal
             */
            find: function (id) {
                $log.debug('api.appraisal.find');

                return this.sendGET('Appraisal', 'get', { id: '' + id }, false)
                    .then(function (data) {
                        return data.values[0];
                    });
            }
        });
    }]);
});
