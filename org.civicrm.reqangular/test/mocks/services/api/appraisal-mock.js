define([
    'common/lodash',
    'common/mocks/module',
    'common/mocks/services/api/contact-mock',
    'common/mocks/services/api/job-role-mock',
    'common/mocks/services/api/option-group-mock'
], function (_, mocks) {
    'use strict';

    mocks.factory('api.appraisal.mock', [
        '$q', 'api.contact.mock', 'api.job-role.mock', 'api.optionGroup.mock',
        function ($q, contactAPI, jobRoleAPI, optionGroupAPI) {
            var mockedContacts = contactAPI.mockedContacts().list;
            var mockedJobRoles = jobRoleAPI.mockedJobRoles.list;
            var mockedOptionValues = optionGroupAPI.mockedOptionValues();

            return {
                all: function (filters, pagination, value) {
                    var list, start, end;

                    list = value || this.mockedAppraisals.list;

                    if (filters) {
                        list = list.filter(function (appraisal) {
                            return Object.keys(filters).every(function (key) {
                                return appraisal[key] === filters[key];
                            });
                        });
                    }

                    if (pagination) {
                        start = (pagination.page - 1) * pagination.size;
                        end = start + pagination.size;

                        list = list.slice(start, end);
                    }

                    return promiseResolvedWith({
                        list: list,
                        total: list.length,
                        allIds: list.map(function (appraisal) {
                            return appraisal.id;
                        }).join(',')
                    })
                },
                create: function (attributes, value) {
                    var created = value || (function () {
                        var created = angular.copy(attributes);

                        created.id = '' + Math.ceil(Math.random() * 5000);
                        created.createdAt = Date.now();

                        return created;
                    })();

                    return promiseResolvedWith(created);
                },
                find: function (id, value) {
                    var appraisal = value || this.mockedAppraisals.list.filter(function (appraisal) {
                        return appraisal.id === id;
                    })[0];

                    return promiseResolvedWith(appraisal);
                },
                overdue: function (filters) {
                    // Just take the first 5 appraisals, independent from the cycle id
                    var list = this.mockedAppraisals.list.slice(0, 5);

                    return promiseResolvedWith({
                        list: list,
                        total: list.length,
                        allIds: list.map(function (appraisal) {
                            return appraisal.id;
                        }).join(',')
                    })
                },

                /**
                 * Adds a spy on every method for testing purposes
                 */
                spyOnMethods: function () {
                    _.functions(this).forEach(function (method) {
                        spyOn(this, method).and.callThrough();
                    }.bind(this));
                },

                /**
                 * Mocked appraisals
                 */
                mockedAppraisals: generateAppraisals()
            }

            /**
             * Generate 10 mocked appraisals for 10 appraisal cycles
             *
             * @return {object}
             */
            function generateAppraisals() {
                var appr_count, contact, cycle_count, manager;
                var appraisals = [];

                for (cycle_count = 0; cycle_count <= 10; cycle_count++) {
                    for (appr_count = 0; appr_count <= 10; appr_count++) {
                        contact = _.sample(mockedContacts);
                        manager = _.sample(mockedContacts);

                        appraisals.push({
                            id: '' + appr_count,
                            appraisal_cycle_id: '' + cycle_count,
                            self_appraisal_due: '2016-10-' + appr_count,
                            manager_appraisal_due: '2016-11-' + (1 + appr_count),
                            grade_due: '2016-12-' + (2 + appr_count),
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '1',
                            original_id: '' + appr_count,
                            created_date: '2015-10-' + appr_count,
                            is_current: '1',
                            contact: {
                                id: contact.id,
                                display_name: contact.display_name
                            },
                            manager: {
                                id: manager.id,
                                display_name: manager.display_name
                            },
                            role: (function () {
                                var jobRole = _.find(mockedJobRoles, function (jobRole) {
                                    return jobRole['api.HRJobContract.getsingle'].contact_id === contact.id;
                                });
                                var level = _.find(mockedOptionValues.hrjc_level_type, function (level) {
                                    return level.id === jobRole.level_type;
                                });
                                var location = _.find(mockedOptionValues.hrjc_location, function (location) {
                                    return location.id === jobRole.location;
                                });

                                return {
                                    title: jobRole.title,
                                    level: {
                                        id: level.id,
                                        label: level.label,
                                        value: level.value
                                    },
                                    location: {
                                        id: location.id,
                                        label: location.label,
                                        value: location.value
                                    }
                                };
                            }())
                        });
                    }
                }

                return {
                    total: appraisals.length,
                    list: appraisals
                };
            }

            /**
             * Returns a promise that will resolve with the given value
             *
             * @param {any} value
             * @return {Promise}
             */
            function promiseResolvedWith(value) {
                var deferred = $q.defer();
                deferred.resolve(value);

                return deferred.promise;
            }
        }
    ]);
});
