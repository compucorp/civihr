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
                            name: 'Appraisal Cycle 1',
                            status: 'Status #3',
                            type: 'Type #2',
                            cycle_is_active: true,
                            period: { start: '01/01/2014', end: '01/01/2015' },
                            nextDue: { type: 'Manager Appraisal', date: '01/01/2021' },
                            appraisalsTotal: 100,
                            completionPercentage: 45,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 30 },
                                { name: 'Assigned to Manager', count: 22 },
                                { name: 'Awaiting Grade', count: 2 },
                                { name: 'Awaiting HR Approval', count: 1 },
                                { name: 'Complete', count: 45 }
                            ]
                        },
                        {
                            id: '42132',
                            name: 'Appraisal Cycle 2',
                            cycle_is_active: true,
                            status: 'Status #1',
                            type: 'Type #1',
                            period: { start: '02/02/2014', end: '02/02/2015' },
                            nextDue: { type: 'Self Appraisal', date: '02/02/2022' },
                            appraisalsTotal: 100,
                            completionPercentage: 55,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 30 },
                                { name: 'Assigned to Manager', count: 12 },
                                { name: 'Awaiting Grade', count: 2 },
                                { name: 'Awaiting HR Approval', count: 1 },
                                { name: 'Complete', count: 55 }
                            ]
                        },
                        {
                            id: '42133',
                            name: 'Appraisal Cycle 3',
                            cycle_is_active: true,
                            status: 'Status #1',
                            type: 'Type #2',
                            period: { start: '03/03/2014', end: '03/03/2015' },
                            nextDue: { type: 'Awaiting Grade', date: '03/03/2023' },
                            appraisalsTotal: 100,
                            completionPercentage: 35,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 40 },
                                { name: 'Assigned to Manager', count: 22 },
                                { name: 'Awaiting Grade', count: 2 },
                                { name: 'Awaiting HR Approval', count: 1 },
                                { name: 'Complete', count: 35 }
                            ]
                        },
                        {
                            id: '42134',
                            name: 'Appraisal Cycle 4',
                            cycle_is_active: true,
                            status: 'Status #3',
                            type: 'Type #3',
                            period: { start: '04/04/2014', end: '04/04/2015' },
                            nextDue: { type: 'Manager Appraisal', date: '04/04/2024' },
                            appraisalsTotal: 100,
                            completionPercentage: 65,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 20 },
                                { name: 'Assigned to Manager', count: 12 },
                                { name: 'Awaiting Grade', count: 2 },
                                { name: 'Awaiting HR Approval', count: 1 },
                                { name: 'Complete', count: 65 }
                            ]
                        },
                        {
                            id: '42135',
                            name: 'Appraisal Cycle 5',
                            cycle_is_active: true,
                            status: 'Status #1',
                            type: 'Type #3',
                            period: { start: '05/05/2014', end: '05/05/2015' },
                            nextDue: { type: 'Self Appraisal', date: '05/05/2025' },
                            appraisalsTotal: 100,
                            completionPercentage: 5,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 40 },
                                { name: 'Assigned to Manager', count: 22 },
                                { name: 'Awaiting Grade', count: 22 },
                                { name: 'Awaiting HR Approval', count: 11 },
                                { name: 'Complete', count: 5 }
                            ]
                        },
                        {
                            id: '42136',
                            name: 'Appraisal Cycle 6',
                            cycle_is_active: false,
                            status: 'Status #1',
                            type: 'Type #1',
                            period: { start: '06/06/2014', end: '06/06/2015' },
                            nextDue: null,
                            appraisalsTotal: 100,
                            completionPercentage: 100,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 0 },
                                { name: 'Assigned to Manager', count: 0 },
                                { name: 'Awaiting Grade', count: 0 },
                                { name: 'Awaiting HR Approval', count: 0 },
                                { name: 'Complete', count: 100 }
                            ]
                        },
                        {
                            id: '4217',
                            name: 'Appraisal Cycle 7',
                            cycle_is_active: false,
                            status: 'Status #2',
                            type: 'Type #2',
                            period: { start: '07/07/2014', end: '07/07/2015' },
                            nextDue: null,
                            appraisalsTotal: 100,
                            completionPercentage: 100,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 0 },
                                { name: 'Assigned to Manager', count: 0 },
                                { name: 'Awaiting Grade', count: 0 },
                                { name: 'Awaiting HR Approval', count: 0 },
                                { name: 'Complete', count: 100 }
                            ]
                        },
                        {
                            id: '42138',
                            name: 'Appraisal Cycle 8',
                            cycle_is_active: true,
                            status: 'Status #2',
                            type: 'Type #1',
                            period: { start: '08/08/2014', end: '08/08/2015' },
                            nextDue: { type: 'Self Appraisal', date: '08/08/2028' },
                            appraisalsTotal: 100,
                            completionPercentage: 15,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 40 },
                                { name: 'Assigned to Manager', count: 22 },
                                { name: 'Awaiting Grade', count: 12 },
                                { name: 'Awaiting HR Approval', count: 11 },
                                { name: 'Complete', count: 15 }
                            ]
                        },
                        {
                            id: '42139',
                            name: 'Appraisal Cycle 9',
                            cycle_is_active: true,
                            status: 'Status #3',
                            type: 'Type #1',
                            period: { start: '09/09/2014', end: '09/09/2015' },
                            nextDue: { type: 'Self Appraisal', date: '09/09/2029' },
                            appraisalsTotal: 100,
                            completionPercentage: 10,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 40 },
                                { name: 'Assigned to Manager', count: 22 },
                                { name: 'Awaiting Grade', count: 22 },
                                { name: 'Awaiting HR Approval', count: 16 },
                                { name: 'Complete', count: 10 }
                            ]
                        },
                        {
                            id: '421310',
                            name: 'Appraisal Cycle 10',
                            cycle_is_active: true,
                            status: 'Status #1',
                            type: 'Type #4',
                            period: { start: '10/10/2014', end: '10/10/2015' },
                            nextDue: { type: 'Self Appraisal', date: '10/10/2030' },
                            appraisalsTotal: 100,
                            completionPercentage: 2,
                            appraisalsCountByStep: [
                                { name: 'Self Appraisal', count: 40 },
                                { name: 'Assigned to Manager', count: 24 },
                                { name: 'Awaiting Grade', count: 22 },
                                { name: 'Awaiting HR Approval', count: 11 },
                                { name: 'Complete', count: 2 }
                            ]
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
