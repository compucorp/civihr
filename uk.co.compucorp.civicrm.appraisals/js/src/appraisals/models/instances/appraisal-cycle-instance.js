define([
    'common/lodash',
    'common/moment',
    'common/services/api/appraisals',
    'appraisals/modules/models-instances'
], function (_, moment, __, instances) {
    'use strict';

    instances.factory('AppraisalCycleInstance', ['$q', 'api.appraisals',
        function ($q, appraisalsAPI) {

            /**
             * Calculates total number of appraisals and completion percentage
             *
             * @param {Array} steps
             *   A list of every step with appraisals, each containing
             *   its id and the number of appraisals in it
             */
            function calculateAppraisalsFigures(steps) {
                var completedStep;

                this.completion_percentage = 0;
                this.appraisals_count = steps.reduce(function (total, status) {
                    (status.status_id === '5') && (completedStep = status);

                    return total + +status.appraisals_count;
                }, 0);

                if (completedStep) {
                    this.completion_percentage = Math.round(completedStep.appraisals_count * 100 / this.appraisals_count);
                }
            }

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
                        } else if (key === 'api.AppraisalCycle.getappraisalsperstep') {
                            calculateAppraisalsFigures.call(result, this[key].values);
                        } else if (key === 'cycle_is_active') {
                            // must be able to convert '0' to false
                            result.cycle_is_active = !!(+this[key]);
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
                    var blacklist = ['appraisals_count', 'completion_percentage'];

                    return _.transform(attributes, function (result, __, key) {
                        if (_.endsWith(key, '_date') || _.endsWith(key, '_due')) {
                            result[key] = moment(this[key], 'DD/MM/YYYY').format('YYYY-MM-DD');
                        } else if (_.includes(blacklist, key)) {
                            return;
                        } else {
                            result[key] = this[key];
                        }
                    }, Object.create(null), attributes);
                },

                /**
                 * Updates the instance with the new data
                 *
                 * @param {object} attributes - The new data
                 * @return {Promise}
                 *   resolved with the appraisals api update's promise
                 *   rejected if there is no id set on the instance
                 */
                update: function (attributes) {
                    var deferred = $q.defer();

                    if (!!this.id) {
                        deferred.resolve(appraisalsAPI.update(this.toAPI()).then(function (attributes) {
                            _.assign(this, this.fromAPI(attributes)); // Updates own attributes
                        }.bind(this)));
                    } else {
                        deferred.reject('ERR_UPDATE: ID_MISSING');
                    }

                    return deferred.promise;
                }
            };
        }
    ]);
});
