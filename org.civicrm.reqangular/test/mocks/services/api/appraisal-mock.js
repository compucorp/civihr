define([
    'common/mocks/module'
], function (mocks) {
    'use strict';

    mocks.factory('api.appraisal.mock', ['$q', function ($q) {

        return {
            all: jasmine.createSpy('all').and.callFake(function (filters, pagination, value) {
                var list = value || this.mockedAppraisals();

                return promiseResolvedWith({
                    list: list,
                    total: list.length,
                    allIds: list.map(function (appraisal) {
                        return appraisal.id;
                    }).join(',')
                })
            }),
            find: jasmine.createSpy('find').and.callFake(function (id, value) {
                var appraisal = value || this.mockedAppraisals().list.filter(function (appraisal) {
                    return appraisal.id === id;
                })[0];

                return promiseResolvedWith(appraisal);
            }),

            /**
             * # DRAFT #
             *
             * Mocked appraisals
             */
            mockedAppraisals: function () {
                return {
                    total: 10,
                    list: [
                        {
                            id: '3451',
                            appraisal_cycle_id: '1',
                            contact_id: '201',
                            manager_id: '201',
                            self_appraisal_due: '2016-01-01',
                            manager_appraisal_due: '2016-02-02',
                            grade_due: '2016-03-03',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '2',
                            original_id: '3451',
                            created_date: '2015-01-01',
                            is_current: '1'
                        },
                        {
                            id: '3452',
                            appraisal_cycle_id: '2',
                            contact_id: '202',
                            manager_id: '202',
                            self_appraisal_due: '2016-02-02',
                            manager_appraisal_due: '2016-03-03',
                            grade_due: '2016-04-04',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '1',
                            original_id: '3452',
                            created_date: '2015-02-02',
                            is_current: '0'
                        },
                        {
                            id: '3453',
                            appraisal_cycle_id: '3',
                            contact_id: '203',
                            manager_id: '203',
                            self_appraisal_due: '2016-03-03',
                            manager_appraisal_due: '2016-04-04',
                            grade_due: '2016-05-05',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '2',
                            original_id: '3453',
                            created_date: '2015-03-03',
                            is_current: '1'
                        },
                        {
                            id: '3454',
                            appraisal_cycle_id: '4',
                            contact_id: '204',
                            manager_id: '204',
                            self_appraisal_due: '2016-04-04',
                            manager_appraisal_due: '2016-05-05',
                            grade_due: '2016-06-06',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '3',
                            original_id: '3454',
                            created_date: '2015-04-04',
                            is_current: '1'
                        },
                        {
                            id: '3455',
                            appraisal_cycle_id: '5',
                            contact_id: '205',
                            manager_id: '205',
                            self_appraisal_due: '2016-05-05',
                            manager_appraisal_due: '2016-06-06',
                            grade_due: '2016-07-07',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '1',
                            original_id: '3455',
                            created_date: '2015-05-05',
                            is_current: '1'
                        },
                        {
                            id: '3456',
                            appraisal_cycle_id: '6',
                            contact_id: '206',
                            manager_id: '206',
                            self_appraisal_due: '2016-06-06',
                            manager_appraisal_due: '2016-07-07',
                            grade_due: '2016-08-08',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '1',
                            original_id: '3458',
                            created_date: '2015-08-08',
                            is_current: '0'
                        },
                        {
                            id: '3459',
                            appraisal_cycle_id: '9',
                            contact_id: '209',
                            manager_id: '209',
                            self_appraisal_due: '2016-09-09',
                            manager_appraisal_due: '2016-10-10',
                            grade_due: '2016-11-11',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '3',
                            original_id: '3459',
                            created_date: '2015-09-09',
                            is_current: '1'
                        },
                        {
                            id: '3460',
                            appraisal_cycle_id: '10',
                            contact_id: '210',
                            manager_id: '210',
                            self_appraisal_due: '2016-10-10',
                            manager_appraisal_due: '2016-11-11',
                            grade_due: '2016-12-12',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '1',
                            original_id: '3460',
                            created_date: '2015-10-10',
                            is_current: '1'
                        },
                    ]
                }
            }
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
    }]);
});
