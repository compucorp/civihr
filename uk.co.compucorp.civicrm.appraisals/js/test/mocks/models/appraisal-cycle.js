define([
    'common/angular',
    'mocks/module',
    'mocks/models/instances/appraisal-cycle-instance'
], function (angular, mocks, AppraisalCycleInstanceMock) {
    'use strict';

    mocks.factory('AppraisalCycleMock', ['$q', 'AppraisalCycleInstanceMock', function ($q, AppraisalCycleInstanceMock) {

        return {
            active: jasmine.createSpy('active').and.callFake(function () {
                return promiseResolvedWith(this.mockedCycles.list.filter(function (cycle) {
                    return cycle.cycle_is_active;
                }));
            }),
            all: jasmine.createSpy('all').and.callFake(function (filters, pagination, value) {
                var list = value || this.mockedCycles().list;

                return promiseResolvedWith({
                    list: list.map(function (cycle) {
                        return AppraisalCycleInstanceMock.init(cycle);
                    }),
                    total: list.length,
                    allIds: list.map(function (cycle) {
                        return cycle.id;
                    }).join(',')
                })
            }),
            grades: jasmine.createSpy('grades').and.callFake(function (value) {
                return promiseResolvedWith(value);
            }),
            types: jasmine.createSpy('types').and.callFake(function (value) {
                return promiseResolvedWith(value);
            }),
            find: jasmine.createSpy('find').and.callFake(function (id, value) {
                var cycle = value || this.mockedCycles().list.filter(function (cycle) {
                    return cycle.id === id;
                })[0];

                return promiseResolvedWith(AppraisalCycleInstanceMock.init(cycle));
            }),
            create: jasmine.createSpy('create').and.callFake(function (attributes, value) {
                var created = value || (function () {
                    var created = angular.copy(attributes);

                    created.id = '' + Math.ceil(Math.random() * 5000);
                    created.createdAt = Date.now();

                    return created;
                })();

                return promiseResolvedWith(created);
            }),
            update: jasmine.createSpy('update').and.callFake(function (id, attributes, value) {
                var cycle = value || (function () {
                    var cycle = this.mockedCycles().list.filter(function (cycle) {
                        return cycle.id === id;
                    })[0];

                    return AppraisalCycleInstanceMock(angular.extend({}, cycle, attributes));
                }.bind(this))();

                return promiseResolvedWith(cycle);
            }),
            statusOverview: jasmine.createSpy('statusOverview').and.callFake(function (params) {
                return promiseResolvedWith(jasmine.any(Array));
            }),

            /**
             * Mocked cycles
             */
            mockedCycles: function () {
                return {
                    total: 10,
                    list: [
                        {
                            id: '42131',
                            cycle_name: 'Appraisal Cycle 1',
                            cycle_is_active: true,
                            cycle_type_id: '2',
                            cycle_start_date: '01/01/2014',
                            cycle_end_date: '01/01/2015',
                            cycle_self_appraisal_due: '01/01/2016',
                            cycle_manager_appraisal_due: '02/01/2016',
                            cycle_grade_due: '03/01/2016'
                        },
                        {
                            id: '42132',
                            cycle_name: 'Appraisal Cycle 2',
                            cycle_is_active: true,
                            cycle_type_id: '1',
                            cycle_start_date: '02/02/2014',
                            cycle_end_date: '02/02/2015',
                            cycle_self_appraisal_due: '02/02/2016',
                            cycle_manager_appraisal_due: '04/02/2016',
                            cycle_grade_due: '05/02/2016'
                        },
                        {
                            id: '42133',
                            cycle_name: 'Appraisal Cycle 3',
                            cycle_is_active: true,
                            cycle_type_id: '2',
                            cycle_start_date: '03/03/2014',
                            cycle_end_date: '03/03/2015',
                            cycle_self_appraisal_due: '06/03/2016',
                            cycle_manager_appraisal_due: '07/03/2016',
                            cycle_grade_due: '08/03/2016'
                        },
                        {
                            id: '42134',
                            cycle_name: 'Appraisal Cycle 4',
                            cycle_is_active: true,
                            cycle_type_id: '3',
                            cycle_start_date: '04/04/2014',
                            cycle_end_date: '04/04/2015',
                            cycle_self_appraisal_due: '09/04/2016',
                            cycle_manager_appraisal_due: '10/04/2016',
                            cycle_grade_due: '11/04/2016'
                        },
                        {
                            id: '42135',
                            cycle_name: 'Appraisal Cycle 5',
                            cycle_is_active: true,
                            cycle_type_id: '3',
                            cycle_start_date: '05/05/2014',
                            cycle_end_date: '05/05/2015',
                            cycle_self_appraisal_due: '12/05/2016',
                            cycle_manager_appraisal_due: '13/05/2016',
                            cycle_grade_due: '14/05/2016'
                        },
                        {
                            id: '42136',
                            cycle_name: 'Appraisal Cycle 6',
                            cycle_is_active: false,
                            cycle_type_id: '1',
                            cycle_start_date: '06/06/2014',
                            cycle_end_date: '06/06/2015',
                            cycle_self_appraisal_due: '15/06/2016',
                            cycle_manager_appraisal_due: '16/06/2016',
                            cycle_grade_due: '17/06/2016'
                        },
                        {
                            id: '4217',
                            cycle_name: 'Appraisal Cycle 7',
                            cycle_is_active: false,
                            cycle_type_id: '2',
                            cycle_start_date: '07/07/2014',
                            cycle_end_date: '07/07/2015',
                            cycle_self_appraisal_due: '18/07/2016',
                            cycle_manager_appraisal_due: '19/07/2016',
                            cycle_grade_due: '20/07/2016'
                        },
                        {
                            id: '42138',
                            cycle_name: 'Appraisal Cycle 8',
                            cycle_is_active: true,
                            cycle_type_id: '1',
                            cycle_start_date: '08/08/2014',
                            cycle_end_date: '08/08/2015',
                            cycle_self_appraisal_due: '21/08/2016',
                            cycle_manager_appraisal_due: '22/08/2016',
                            cycle_grade_due: '23/08/2016'
                        },
                        {
                            id: '42139',
                            cycle_name: 'Appraisal Cycle 9',
                            cycle_is_active: true,
                            cycle_type_id: '1',
                            cycle_start_date: '09/09/2014',
                            cycle_end_date: '09/09/2015',
                            cycle_self_appraisal_due: '24/09/2016',
                            cycle_manager_appraisal_due: '25/09/2016',
                            cycle_grade_due: '26/09/2016'
                        },
                        {
                            id: '421310',
                            cycle_name: 'Appraisal Cycle 10',
                            cycle_is_active: true,
                            cycle_type_id: '4',
                            cycle_start_date: '10/10/2014',
                            cycle_end_date: '10/10/2015',
                            cycle_self_appraisal_due: '27/10/2016',
                            cycle_manager_appraisal_due: '28/10/2016',
                            cycle_grade_due: '29/10/2016'
                        }
                    ]
                }
            }
        };

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
    }]);
})
