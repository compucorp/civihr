define([
    'appraisals/modules/models',
    'common/services/api/appraisal'
], function (models) {
    'use strict';

    models.factory('Appraisal', ['api.appraisal', 'AppraisalInstance', function (appraisalAPI, instance) {

        return {

            /**
             * Returns a list of appraisals, each converted to a model instance
             *
             * @param {object} filters - Values the full list should be filtered by
             * @param {object} pagination
             *   `page` for the current page, `size` for number of items per page
             * @return {Promise}
             */
            all: function (filters, pagination) {
                return appraisalAPI.all(filters, pagination).then(function (response) {
                    response.list = response.list.map(function (appraisal) {
                        return instance.init(appraisal, true);
                    });

                    return response;
                });
            },

            /**
             * Finds an appraisal by id
             *
             * @param {string} id
             * @return {Promise} - Resolves with found appraisail
             */
            find: function (id) {
                return appraisalAPI.find(id).then(function (appraisal) {
                    return instance.init(appraisal, true);
                });
            }
        };
    }]);
})
