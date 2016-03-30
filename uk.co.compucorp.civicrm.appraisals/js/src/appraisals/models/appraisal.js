define([
    'appraisals/modules/models',
    'common/models/model',
    'common/mocks/services/api/appraisal-mock' // Temporary, necessary to use the mocked API data
], function (models) {
    'use strict';

    models.factory('Appraisal', [
        '$log', 'Model', 'api.appraisal.mock', 'AppraisalInstance',
        function ($log, Model, appraisalAPI, instance) {

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
                    var promise;

                    // TEMPORARY - the way the `overdue` filter is handled is
                    // to be changed later on, this is just to return mocked data
                    if (!!filters && filters.overdue === true) {
                        promise = appraisalAPI.overdue(filters);
                    } else {
                        promise = appraisalAPI.all(this.processFilters(filters), pagination);
                    }

                    return promise.then(function (response) {
                        response.list = response.list.map(function (appraisal) {
                            return instance.init(appraisal, true);
                        });

                        return response;
                    });
                },

                /**
                 * Creates a new appraisal
                 *
                 * @param {object} attributes - The attributes of the appraisal to be created
                 * @return {Promise} - Resolves with the instance of the new appraisal
                 */
                create: function (attributes) {
                    if (!attributes.contact_id) {
                        $log.error('ERR_APPRAISAL_CREATE: CONTACT ID MISSING');
                    }

                    if (!attributes.appraisal_cycle_id) {
                        $log.error('ERR_APPRAISAL_CREATE: APPRAISAL CYCLE ID MISSING');
                    }

                    var appraisal = instance.init(attributes).toAPI();

                    return appraisalAPI.create(appraisal).then(function (newAppraisal) {
                        return instance.init(newAppraisal, true);
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
            });
        }
    ]);
})
