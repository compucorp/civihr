define([
    'appraisals/modules/models',
    'common/models/model',
    'common/services/api/appraisal'
], function (models) {
    'use strict';

    models.factory('Appraisal', [
        'Model', 'api.appraisal', 'AppraisalInstance',
        function (Model, appraisalAPI, instance) {

            return Model.extend({

                /**
                 * Returns a list of appraisals, each converted to a model instance
                 *
                 * @param {object} filters - Values the full list should be filtered by
                 * @param {object} pagination
                 *   `page` for the current page, `size` for number of items per page
                 * @return {Promise}
                 */
                all: function (filters, pagination) {
                    return appraisalAPI.all(this.processFilters(filters), pagination).then(function (response) {
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
                },

                /**
                 * Returns all the overdue appraisals
                 *
                 * @param {object} filters - Values the full list should be filtered by
                 * @return {Promise}
                 */
                overdue: function (filters) {
                    return appraisalAPI.overdue(filters)
                        .then(function (response) {
                            response.list = response.list.map(function (appraisal) {
                                return instance.init(appraisal, true);
                            });

                            return response;
                        });
                }
            });
        }
    ]);
})
