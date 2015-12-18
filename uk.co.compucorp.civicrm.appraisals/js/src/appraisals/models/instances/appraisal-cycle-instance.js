define([
    'common/lodash',
    'common/moment',
    'common/services/api/appraisals',
    'appraisals/modules/models-instances'
], function (_, moment, __, instances) {
    'use strict';

    instances.factory('AppraisalCycleInstance', ['api.appraisals',
        function (appraisalsAPI) {

            return {

                /**
                 * Creates a new instance, optionally with its data normalized
                 *
                 * @param {object} attributes - The instance data
                 * @param {boolean} fromAPI - If the data comes from the API and needs to be normalized
                 * @return {object}
                 */
                init: function (attributes, fromAPI) {
                    if (typeof fromAPI !== 'undefined' && fromAPI) {
                        attributes = this.fromAPI(attributes);
                    }

                    return _.assign(Object.create(this), attributes);
                },

                /**
                 * Creates a plain object (w/o prototype) containing
                 * only the attributes of this instance
                 *
                 * @return {object}
                 */
                attributes: function () {
                    return _.transform(this, function (result, __, key) {
                        !_.isFunction(this[key]) && (result[key] = this[key]);
                    }, Object.create(null), this);
                },

                /**
                 * Normalizes the given data in the direction API -> Model
                 *
                 * @param {object} attributes
                 * @return {object}
                 */
                fromAPI: function (attributes) {
                    return _.transform(attributes, function (result, __, key) {
                        if (_.endsWith(key, '_date') || _.endsWith(key, '_due')) {
                            result[key] = moment(this[key], 'YYYY-MM-DD').format('DD/MM/YYYY');
                        } else {
                            result[key] = this[key];
                        }
                    }, Object.create(null), attributes);
                },

                /**
                 * Normalizes the instance data in the direction Model -> API
                 *
                 * @return {object}
                 */
                toAPI: function () {
                    var attributes = this.attributes();

                    return _.transform(attributes, function (result, __, key) {
                        if (_.endsWith(key, '_date') || _.endsWith(key, '_due')) {
                            result[key] = moment(this[key], 'DD/MM/YYYY').format('YYYY-MM-DD');
                        } else {
                            result[key] = this[key];
                        }
                    }, Object.create(null), attributes);
                }
            };
        }
    ]);
});
