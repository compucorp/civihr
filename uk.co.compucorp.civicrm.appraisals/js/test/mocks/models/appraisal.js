define([
    'mocks/module',
    'mocks/models/instances/appraisal-instance'
], function (mocks) {
    'use strict';

    mocks.factory('AppraisalMock', ['$q', 'AppraisalInstanceMock', function ($q, AppraisalInstanceMock) {

        return {
            all: jasmine.createSpy('all').and.callFake(function (filters, pagination, value) {
                var list = value || this.mockedAppraisals();

                return promiseResolvedWith({
                    list: list.map(function (appraisal) {
                        return AppraisalInstanceMock.init(appraisal);
                    }),
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

                return promiseResolvedWith(AppraisalInstanceMock.init(appraisal));
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
                            self_appraisal_due: '01/01/2016',
                            manager_appraisal_due: '02/02/2016',
                            grade_due: '03/03/2016',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '2',
                            original_id: '3451',
                            created_date: '01/01/2015',
                            is_current: '1'
                        },
                        {
                            id: '3452',
                            appraisal_cycle_id: '2',
                            contact_id: '202',
                            manager_id: '202',
                            self_appraisal_due: '02/02/2016',
                            manager_appraisal_due: '03/03/2016',
                            grade_due: '04/04/2016',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '1',
                            original_id: '3452',
                            created_date: '02/02/2015',
                            is_current: '0'
                        },
                        {
                            id: '3453',
                            appraisal_cycle_id: '3',
                            contact_id: '203',
                            manager_id: '203',
                            self_appraisal_due: '03/03/2016',
                            manager_appraisal_due: '04/04/2016',
                            grade_due: '05/05/2016',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '2',
                            original_id: '3453',
                            created_date: '03/03/2015',
                            is_current: '1'
                        },
                        {
                            id: '3454',
                            appraisal_cycle_id: '4',
                            contact_id: '204',
                            manager_id: '204',
                            self_appraisal_due: '04/04/2016',
                            manager_appraisal_due: '05/05/2016',
                            grade_due: '06/06/2016',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '3',
                            original_id: '3454',
                            created_date: '04/04/2015',
                            is_current: '1'
                        },
                        {
                            id: '3455',
                            appraisal_cycle_id: '5',
                            contact_id: '205',
                            manager_id: '205',
                            self_appraisal_due: '05/05/2016',
                            manager_appraisal_due: '06/06/2016',
                            grade_due: '07/07/2016',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '1',
                            original_id: '3455',
                            created_date: '05/05/2015',
                            is_current: '1'
                        },
                        {
                            id: '3456',
                            appraisal_cycle_id: '6',
                            contact_id: '206',
                            manager_id: '206',
                            self_appraisal_due: '06/06/2016',
                            manager_appraisal_due: '07/07/2016',
                            grade_due: '08/08/2016',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '1',
                            original_id: '3458',
                            created_date: '08/08/2015',
                            is_current: '0'
                        },
                        {
                            id: '3459',
                            appraisal_cycle_id: '9',
                            contact_id: '209',
                            manager_id: '209',
                            self_appraisal_due: '09/09/2016',
                            manager_appraisal_due: '10/10/2016',
                            grade_due: '11/11/2016',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '3',
                            original_id: '3459',
                            created_date: '09/09/2015',
                            is_current: '1'
                        },
                        {
                            id: '3460',
                            appraisal_cycle_id: '10',
                            contact_id: '210',
                            manager_id: '210',
                            self_appraisal_due: '10/10/2016',
                            manager_appraisal_due: '11/11/2016',
                            grade_due: '12/12/2016',
                            due_changed: '0',
                            meeting_completed: '0',
                            approved_by_employee: '0',
                            status_id: '1',
                            original_id: '3460',
                            created_date: '10/10/2015',
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
})
