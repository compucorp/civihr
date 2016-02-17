define([
    'common/lodash',
    'common/moment',
    'common/angularMocks',
    'appraisals/app'
], function (_) {
    'use strict';

    describe('AppraisalCycleInstance', function () {
        var $q, $rootScope, AppraisalCycleInstance, appraisalCycleAPI;
        var instanceInterface = ['defaultCustomData', 'dueDates', 'fromAPIFilter',
        'isStatusOverdue', 'nextDueDate', 'toAPIFilter', 'update'];

        beforeEach(module('appraisals'));
        beforeEach(inject(['$q', '$rootScope', 'AppraisalCycleInstance', 'api.appraisal-cycle',
            function (_$q_, _$rootScope_, _AppraisalCycleInstance_, _appraisalCycleAPI_) {
                $q = _$q_;
                $rootScope = _$rootScope_;

                AppraisalCycleInstance = _AppraisalCycleInstance_;
                appraisalCycleAPI = _appraisalCycleAPI_;
            }
        ]));

        it('has the expected interface', function () {
            expect(_.keys(AppraisalCycleInstance).filter(function (property) {
                return _.isFunction(AppraisalCycleInstance[property]);
            })).toEqual(instanceInterface);
        });

        describe('init()', function () {
            var instance;

            describe('simple initialization', function () {
                beforeEach(function () {
                    instance = AppraisalCycleInstance.init({});
                });

                it('contains the default custom data', function () {
                    expect(instance.appraisals_count).toBeDefined();
                    expect(instance.completion_percentage).toBeDefined();
                    expect(instance.statuses).toBeDefined();
                    expect(instance.appraisals_count).toEqual(0);
                    expect(instance.completion_percentage).toBe(0);
                    expect(instance.statuses).toEqual({});
                });
            });

            describe('when initializing with data from the API', function () {
                var attributes = {
                    foo: 'foo',
                    cycle_start_date: '2015-09-23',
                    cycle_grade_due: '2015-11-22',
                    cycle_is_active: '0',
                    'api.AppraisalCycle.getappraisalsperstep': {
                        values: [
                            {
                                appraisals_count: '7',
                                status_id: '1',
                                status_name: 'Awaiting self appraisal'
                            },
                            {
                                appraisals_count: '2',
                                status_id: '5',
                                status_name: 'Complete'
                            }
                        ]
                    }
                };

                beforeEach(function () {
                    instance = AppraisalCycleInstance.init(attributes, true);
                });

                it('normalizes the data', function () {
                    expect(instance.foo).toBe(attributes.foo);
                    expect(instance.cycle_start_date).toBe('23/09/2015');
                    expect(instance.cycle_grade_due).toBe('22/11/2015');
                    expect(instance.cycle_is_active).toBe(false);
                    expect(instance['api.AppraisalCycle.getappraisalsperstep']).not.toBeDefined();
                    expect(instance.appraisals_count).toBeDefined();
                    expect(instance.completion_percentage).toBeDefined();
                    expect(instance.statuses).toBeDefined();
                    expect(instance.appraisals_count).toBe(9);
                    expect(instance.completion_percentage).toBe(22)
                    expect(instance.statuses).toEqual({
                        '1': {
                            name: 'Awaiting self appraisal',
                            appraisals_count: '7'
                        },
                        '5': {
                            name: 'Complete',
                            appraisals_count: '2'
                        }
                    });
                });
            });
        });

        describe('dueDates()', function () {
            var instance;

            beforeEach(function () {
                instance = AppraisalCycleInstance.init({
                    cycle_start_date: '01/01/2015',
                    cycle_end_date: '31/12/2015',
                    cycle_self_appraisal_due: '31/01/2016',
                    cycle_manager_appraisal_due: '28/02/2016',
                    cycle_grade_due: '30/03/2016'
                });
            });

            it('returns only the due dates', function () {
                expect(instance.dueDates()).toEqual({
                    cycle_self_appraisal_due: '31/01/2016',
                    cycle_manager_appraisal_due: '28/02/2016',
                    cycle_grade_due: '30/03/2016'
                });
            })
        });

        describe('isStatusOverdue()', function () {
            var instance;

            beforeEach(function () {
                instance = AppraisalCycleInstance.init({
                    cycle_self_appraisal_due: '01/02/2016',
                    cycle_manager_appraisal_due: '01/03/2016',
                    cycle_grade_due: '01/04/2016'
                });

                jasmine.clock().mockDate(new Date(2016, 2, 1));
            });

            it('checks if a status is overdue given the current date', function () {
                expect(instance.isStatusOverdue('1')).toBe(true);
                expect(instance.isStatusOverdue('2')).toBe(false);
                expect(instance.isStatusOverdue('3')).toBe(false);
            });
        });

        describe('nextDueDate()', function () {
            var instance, nextDueDate;

            beforeEach(function () {
                instance = AppraisalCycleInstance.init({
                    cycle_self_appraisal_due: '01/02/2016',
                    cycle_manager_appraisal_due: '01/03/2016',
                    cycle_grade_due: '01/04/2016'
                });
            });

            describe('when there are still due date', function () {
                describe('when today is a due date', function () {
                    beforeEach(function () {
                        jasmine.clock().mockDate(new Date(2016, 1, 1));
                        nextDueDate = instance.nextDueDate();
                    });

                    it('returns today', function () {
                        expect(nextDueDate.status_id).toBe('1');
                        expect(nextDueDate.date).toBe('01/02/2016');
                    });
                });

                describe('when today is not a due date', function () {
                    beforeEach(function () {
                        jasmine.clock().mockDate(new Date(2016, 1, 2));
                        nextDueDate = instance.nextDueDate();
                    });

                    it('returns the next to come', function () {
                        expect(nextDueDate.status_id).toBe('2');
                        expect(nextDueDate.date).toBe('01/03/2016');
                    });
                });
            });

            describe('when there are no more due dates', function () {
                beforeEach(function () {
                    jasmine.clock().mockDate(new Date(2016, 5, 3));
                    nextDueDate = instance.nextDueDate();
                });

                it('returns nothing', function () {
                    expect(nextDueDate).toBe(null);
                });
            });
        });

        describe('toAPI()', function () {
            var instance, toAPIData;

            beforeEach(function () {
                instance = AppraisalCycleInstance.init({
                    foo: 'foo',
                    cycle_start_date: '23/09/2015',
                    cycle_grade_due: '22/11/2015',
                    completion_percentage: 20,
                    appraisals_count: {
                        steps: [
                            { status_id: '4', appraisals_count: 5 },
                            { status_id: '2', appraisals_count: 7 },
                        ],
                        total: 12
                    }
                });
                toAPIData = instance.toAPI();
            });

            it('filters out the custom data field', function () {
                expect(Object.keys(toAPIData)).toEqual(_.without(
                    Object.keys(instance.attributes()),
                    'appraisals_count',
                    'completion_percentage'
                ));
            });

            it('formats the dates in the YYYY-MM-DD format', function () {
                expect(toAPIData.cycle_start_date).toBe('2015-09-23');
                expect(toAPIData.cycle_grade_due).toBe('2015-11-22');
            });
        });

        describe('update()', function () {
            var instance, p;

            var oldData = {
                id: '23',
                name: 'new cycle',
                cycle_start_date: '12/11/2015',
                cycle_grade_due: '01/01/2016'
            };
            var newData = {
                name: 'newest cycle',
                cycle_grade_due: '01/02/2016'
            };

            beforeEach(function () {
                spyOn(appraisalCycleAPI, 'update').and.callFake(function () {
                    var deferred = $q.defer();
                    deferred.resolve({
                        id: '23',
                        name: 'newest cycle',
                        cycle_start_date: '2015-11-12',
                        cycle_grade_due: '2016-02-01'
                    });

                    return deferred.promise;
                });

                instance = AppraisalCycleInstance.init(oldData)
            });

            describe('when the instance has an id set', function () {
                beforeEach(function () {
                    _.assign(instance, newData);

                    p = instance.update();
                });

                it('calls the update method of the API', function () {
                    expect(appraisalCycleAPI.update).toHaveBeenCalledWith(instance.toAPI());
                });

                it('reflects the updated data on its attributes', function (done) {
                    var updated = _.assign(Object.create(null), oldData, newData);

                    p.then(function () {
                        expect(instance.attributes()).toEqual(jasmine.objectContaining(updated));
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });

            describe('when the instance does not have an id set', function () {
                beforeEach(function () {
                    _.assign(instance, newData, { id: null });

                    p = instance.update();
                });

                it('returns an error', function (done) {
                    p.catch(function (err) {
                        expect(err).toBe('ERR_UPDATE: ID_MISSING');
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });
        });
    });
});
