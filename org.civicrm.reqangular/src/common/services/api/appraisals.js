define([
    'common/angular',
    'common/lodash',
    'common/modules/apis',
    'common/services/api',
    'common/services/api/option-group'
], function (angular, _, apis) {
    'use strict';

    apis.factory('api.appraisals', ['$q', '$log', 'api', 'api.optionGroup', function ($q, $log, api, optionGroupAPI) {
        $log.debug('api.appraisals');

        // Draft

        return api.extend({

            /**
             * # TO DO #
             */
            activeCycles: function () {
                $log.debug('activeCycles');

                return this.mockGET(mockedCycles().filter(function (cycle) {
                    return !!cycle.active;
                }));
            },

            /**
             * Returns:
             *   - the list of cycles, eventually filtered/paginated
             *   - the total count of the cycles based on the filters,
             *     independent of the pagination settings
             *
             * @param {object} filters - Values the full list should be filtered by
             * @param {object} pagination
             *   `page` for the current page, `size` for number of items per page
             * @return {Promise} resolves to an object with `list` and `total`
             */
            all: function (filters, pagination) {
                $log.debug('api.appraisals.all');

                var params = filters || {};

                if (pagination) {
                    params.options = {
                        offset: (pagination.page - 1) * pagination.size,
                        limit: pagination.size
                    };
                }

                return $q.all([
                    this.sendGET('AppraisalCycle', 'get', params).then(function (data) {
                        return data.values;
                    }),
                    this.sendGET('AppraisalCycle', 'getcount', _.omit(params, 'options')).then(function (data) {
                        return data.result;
                    })
                ]).then(function (results) {
                    return { list: results[0], total: results[1] };
                });
            },

            /**
             * Creates a new appraisal cycle
             *
             * @param {object} attributes - The data of the new cycle
             * @return {Promise} resolves to the newly created cycle
             */
            create: function (attributes) {
                $log.debug('api.appraisals.create');

                return this.sendPOST('AppraisalCycle', 'create', attributes)
                    .then(function (data) {
                        return data.values[0];
                    });
            },

            /**
             * Finds the appraisal cycle with the given id
             *
             * @param {string/int} id
             * @return {Promise} resolves to the found cycle
             */
            find: function (id) {
                $log.debug('api.appraisals.find');

                return this.sendGET('AppraisalCycle', 'get', { id: '' + id }, false).then(function (data) {
                    return data.values[0];
                });
            },

            /**
             * # TO DO #
             */
            grades: function () {
                $log.debug('grades');

                return this.mockGET([
                    { label: 1, value: 17 },
                    { label: 2, value: 74 },
                    { label: 3, value: 90 },
                    { label: 4, value: 30 }
                ]);
            },

            /**
             * Returns the list of all currently active status for an appraisal cycle
             *
             * @return {Promise}
             */
            statuses: function () {
                $log.debug('api.appraisals.statuses');

                return optionGroupAPI.valuesOf('appraisal_status');
            },

            /**
             * # TO DO #
             */
            statusOverview: function () {
                $log.debug('statusOverview');

                return this.mockGET({
                    steps: [
                        { contacts: 28, overdue: 0 },
                        { contacts: 40, overdue: 2 },
                        { contacts: 36, overdue: 0 },
                        { contacts: 28, overdue: 0 },
                        { contacts: 0, overdue: 0 }
                    ],
                    totalAppraisalsNumber: 248
                });
            },

            /**
             * Updates an appraisal cycle
             *
             * @param {object} attributes - The new data of the cycle
             * @return {Promise} resolves to the amended cycle
             */
            update: function (attributes) {
                $log.debug('api.appraisals.update');

                return this.sendPOST('AppraisalCycle', 'create', attributes)
                    .then(function (data) {
                        return data.values[0];
                    });
            },

            /**
             * Returns the total number of appraisal cycles
             *
             * @return {Promise} resolves to an integer
             */
            total: function () {
                $log.debug('api.appraisals.total');

                return this.sendGET('AppraisalCycle', 'getcount').then(function (data) {
                    return data.result;
                });
            },

            /**
             * Returns the list of all currently active types for an appraisal cycle
             *
             * @return {Promise}
             */
            types: function () {
                $log.debug('api.appraisals.types');

                return optionGroupAPI.valuesOf('appraisal_cycle_type');
            }
        });

        function mockedCycles() {
            return [
                {
                    id: '42131',
                    cycle_name: 'Appraisal Cycle 1',
                    status: 'Status #3',
                    type: 'Type #2',
                    active: true,
                    cycle_start_date: '01/01/2014',
                    cycle_end_date: '01/01/2015',
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
                    cycle_name: 'Appraisal Cycle 2',
                    active: true,
                    status: 'Status #1',
                    type: 'Type #1',
                    cycle_start_date: '02/02/2014',
                    cycle_end_date: '02/02/2015',
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
                    cycle_name: 'Appraisal Cycle 3',
                    active: true,
                    status: 'Status #1',
                    type: 'Type #2',
                    cycle_start_date: '03/03/2014',
                    cycle_end_date: '03/03/2015',
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
                    cycle_name: 'Appraisal Cycle 4',
                    active: true,
                    status: 'Status #3',
                    type: 'Type #3',
                    cycle_start_date: '04/04/2014',
                    cycle_end_date: '04/04/2015',
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
                    cycle_name: 'Appraisal Cycle 5',
                    active: true,
                    status: 'Status #1',
                    type: 'Type #3',
                    cycle_start_date: '05/05/2014',
                    cycle_end_date: '05/05/2015',
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
                    cycle_name: 'Appraisal Cycle 6',
                    active: false,
                    status: 'Status #1',
                    type: 'Type #1',
                    cycle_start_date: '06/06/2014',
                    cycle_end_date: '06/06/2015',
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
                    cycle_name: 'Appraisal Cycle 7',
                    active: false,
                    status: 'Status #2',
                    type: 'Type #2',
                    cycle_start_date: '07/07/2014',
                    cycle_end_date: '07/07/2015',
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
                    cycle_name: 'Appraisal Cycle 8',
                    active: true,
                    status: 'Status #2',
                    type: 'Type #1',
                    cycle_start_date: '08/08/2014',
                    cycle_end_date: '08/08/2015',
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
                    cycle_name: 'Appraisal Cycle 9',
                    active: true,
                    status: 'Status #3',
                    type: 'Type #1',
                    cycle_start_date: '09/09/2014',
                    cycle_end_date: '09/09/2015',
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
                    cycle_name: 'Appraisal Cycle 10',
                    active: true,
                    status: 'Status #1',
                    type: 'Type #4',
                    cycle_start_date: '10/10/2014',
                    cycle_end_date: '10/10/2015',
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
    }]);
});
