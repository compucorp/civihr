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
             * @param {Array} status
             *   A list of every step with appraisals, each containing
             *   its id and the number of appraisals in it
             */
            function calculateAppraisalsFigures(status) {
                this.statuses = _.reduce(status, function (accumulator, step) {
                    accumulator[step.status_id] = {
                        name: step.status_name,
                        appraisals_count: step.appraisals_count,
                    };

                    if (step.appraisals) {
                        accumulator[step.status_id].appraisals = step.appraisals;
                    }

                    this.appraisals_count += +step.appraisals_count;

                    return accumulator;
                }, {}, this);

                this.completion_percentage = Math.round(this.statuses['5'].appraisals_count * 100 / this.appraisals_count);
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
                    attributes = _.assign(this.defaultCustomData(), attributes);

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
                 * Returns the default custom data (as in, not given by the API)
                 * with its default values
                 *
                 * @return {object}
                 */
                defaultCustomData: function () {
                    return {
                        appraisals_count: 0,
                        completion_percentage: 0,
                        statuses: {}
                    };
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
                            result[key] = !!(+this[key]);
                        } else {
                            result[key] = this[key];
                        }
                    }, Object.create(null), attributes);
                },

                /**
                 * Returns the next available due date based on the current date,
                 * or `null` if there are no more due dates left
                 *
                 * @return {null/string}
                 */
                nextDueDate: function () {
                    var today = moment();
                    var nextDueDates = _.chain(this.attributes())
                        .filter(function (value, key) {
                            return _.endsWith(key, '_due');
                        })
                        .map(function (date) {
                            return moment(date, 'DD/MM/YYYY')
                        })
                        .filter(function (date) {
                            return moment(date).isAfter(today);
                        })
                        .value();

                    if (nextDueDates.length === 0) {
                        return null;
                    }

                    return moment.min.apply(moment, nextDueDates).format('DD/MM/YYYY');
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
