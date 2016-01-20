define([
    'common/lodash',
    'common/moment',
    'common/services/api/appraisals',
    'appraisals/modules/models-instances'
], function (_, moment, __, instances) {
    'use strict';

    instances.factory('AppraisalCycleInstance', ['$q', 'api.appraisals',
        function ($q, appraisalsAPI) {

            var DUE_DATE_FIELD_TO_STATUS_ID = {
                cycle_self_appraisal_due:    '1',
                cycle_manager_appraisal_due: '2',
                cycle_grade_due:             '3'
            };

            /**
             * Memoized version of the nextDueDate() method
             *
             * @param {string} id - A unique identifier to retrieve the memoized result
             * @param {object} dueDates - The due dates field/value pairs
             * @return {null/object}
             */
            var nextDueDate = _.memoize(function nextDueDates(id, dueDates) {
                var today, dates, date;

                today = moment();
                dates = _.chain(dueDates)
                    .transform(function (result, date, key) {
                        result[key] = moment(date, 'DD/MM/YYYY');
                    })
                    .pick(function (date) {
                        return date.isSameOrAfter(today, 'day');
                    })
                    .value();

                if (_.isEmpty(dates)) {
                    return null;
                }

                date = moment.min.apply(moment, _.values(dates));

                return {
                    date: date.format('DD/MM/YYYY'),
                    status_id: DUE_DATE_FIELD_TO_STATUS_ID[_.findKey(dates, function (date) {
                        return date === date;
                    })]
                };
            });

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

                if (this.appraisals_count > 0) {
                    this.completion_percentage = Math.round(this.statuses['5'].appraisals_count * 100 / this.appraisals_count);
                }
            }

            /**
             * Returns an object made of only the due dates properties
             *
             * @return {object}
             */
            function dueDates() {
                return _.pick(this.attributes(), function (value, key) {
                    return _.endsWith(key, '_due');
                });
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
                 * Checks if the status with the given id is overdue
                 *
                 * @param {int/string} id
                 * @return {boolean}
                 */
                isStatusOverdue: function (id) {
                    var field = _.invert(DUE_DATE_FIELD_TO_STATUS_ID)[id];

                    return moment(this[field], 'DD/MM/YYYY').isBefore(moment());
                },

                /**
                 * Returns the next available due date based on the current date,
                 * or `null` if there are no more due dates left
                 *
                 * The date is an object containing the actual date and the
                 * id of the status it belongs to
                 *
                 * @return {null/object}
                 */
                nextDueDate: function () {
                    var dates = dueDates.call(this);
                    // In case the id is not present, use the name as parth of
                    // the identifier for the memoized function
                    var id = (this.id || this.name) + _.values(dates).join('');

                    return nextDueDate(id, dates);
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
