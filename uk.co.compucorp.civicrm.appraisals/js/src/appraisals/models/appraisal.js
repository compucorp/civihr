define([
    'appraisals/modules/models',
    'common/services/api/appraisal'
], function (models) {
    'use strict';

    models.factory('Appraisal', ['api.appraisal', 'AppraisalInstance', function (appraisalAPI, instance) {

        return {

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
