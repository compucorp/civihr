define([
    'common/angular',
    'common/lodash',
    'common/angularMocks',
    'appraisals/app',
    'mocks/models/appraisal-cycle',
    'mocks/models/instances/appraisal-cycle-instance'
], function (angular, _) {
    'use strict';

    describe('AppraisalCycle', function () {
        var $q, $rootScope, AppraisalCycle, AppraisalCycleMock, AppraisalCycleInstance, appraisalsAPI, cycles;


        beforeEach(module('appraisals', 'appraisals.mocks'));
        beforeEach(inject(['$q', '$rootScope', 'AppraisalCycle', 'AppraisalCycleMock', 'AppraisalCycleInstanceMock', 'api.appraisals',
            function (_$q_, _$rootScope_, _AppraisalCycle_, _AppraisalCycleMock_, _AppraisalCycleInstanceMock_, _appraisalsAPI_) {
                $q = _$q_;
                $rootScope = _$rootScope_;
                AppraisalCycle = _AppraisalCycle_;
                AppraisalCycleMock = _AppraisalCycleMock_;
                AppraisalCycleInstance = _AppraisalCycleInstanceMock_;
                appraisalsAPI = _appraisalsAPI_;

                cycles = AppraisalCycleMock.mockedCycles();
            }
        ]));

        it('has the expected api', function () {
            expect(Object.keys(AppraisalCycle)).toEqual([
                'active', 'all', 'create', 'find', 'grades', 'statuses',
                'statusOverview', 'total', 'types'
            ]);
        });

        describe('active()', function () {
            var activeCount;

            beforeEach(function () {
                activeCount = cycles.list.filter(function (cycle) {
                    return cycle.cycle_is_active;
                }).length;

                resolveApiCallTo('total').with(activeCount);
            });

            it('returns the active cycles', function (done) {
                AppraisalCycle.active().then(function (count) {
                    expect(appraisalsAPI.total).toHaveBeenCalledWith({ cycle_is_active: true });
                    expect(count).toEqual(activeCount);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('statusOverview()', function () {
            beforeEach(function () {
                resolveApiCallTo('statusOverview').with({
                    steps: [
                        { contacts: 28, overdue: 0 },
                        { contacts: 40, overdue: 2 },
                        { contacts: 36, overdue: 0 },
                        { contacts: 28, overdue: 0 },
                        { contacts: 0, overdue: 0 }
                    ],
                    totalAppraisalsNumber: 248
                });
            });

            it('returns the status overview', function (done) {
                AppraisalCycle.statusOverview().then(function (overview) {
                    expect(appraisalsAPI.statusOverview).toHaveBeenCalled();

                    expect(Object.keys(overview)).toEqual(['steps', 'totalAppraisalsNumber']);
                    expect(overview.steps.length).toEqual(5);
                    expect(overview.totalAppraisalsNumber).toEqual(248);

                    expect(Object.keys(overview.steps[0])).toEqual(['contacts', 'overdue']);
                    expect(overview.steps[1].contacts).toEqual(40);
                    expect(overview.steps[1].overdue).toEqual(2);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('grades()', function () {
            beforeEach(function () {
                resolveApiCallTo('grades').with([
                    { label: '1', value: 30 },
                    { label: '2', value: 10 },
                    { label: '3', value: 55 },
                    { label: '4', value: 87 },
                    { label: '5', value: 54 }
                ]);
            });

            it('returns the grades data', function (done) {
                AppraisalCycle.grades().then(function (grades) {
                    expect(appraisalsAPI.grades).toHaveBeenCalled();

                    expect(grades.length).toEqual(5);
                    expect(Object.keys(grades[0])).toEqual(['label', 'value']);
                    expect(grades[0].label).toEqual('1');
                    expect(grades[0].value).toEqual(30);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('types()', function () {
            beforeEach(function () {
                resolveApiCallTo('types').with([
                    { id: '1', label: 'type 1', value: '1', weight: '1' },
                    { id: '2', label: 'type 2', value: '2', weight: '2' },
                    { id: '3', label: 'type 3', value: '3', weight: '3' }
                ]);
            });

            it('returns the appraisal cycle types', function (done) {
                AppraisalCycle.types().then(function (types) {
                    expect(appraisalsAPI.types).toHaveBeenCalled();

                    expect(types.length).toEqual(3);
                    expect(types).toEqual([
                        { label: 'type 1', value: '1' },
                        { label: 'type 2', value: '2' },
                        { label: 'type 3', value: '3' }
                    ]);
                })
                .finally(done) && $rootScope.$digest();
            });
        })

        describe('statuses()', function () {
            beforeEach(function () {
                resolveApiCallTo('statuses').with([
                    { id: '1', label: 'status 1', value: '1', weight: '1' },
                    { id: '2', label: 'status 2', value: '2', weight: '2' }
                ]);
            });

            it('returns the appraisal cycle statuses', function (done) {
                AppraisalCycle.statuses().then(function (statuses) {
                    expect(appraisalsAPI.statuses).toHaveBeenCalled();

                    expect(statuses.length).toEqual(2);
                    expect(statuses).toEqual([
                        { label: 'status 1', value: '1' },
                        { label: 'status 2', value: '2' }
                    ]);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('create()', function () {
            var newCycle = {
                name: 'new cycle',
                type: 'type 4',
                cycle_start_date: '01/01/2014',
                cycle_end_date: '01/01/2015'
            };

            beforeEach(function () {
                resolveApiCallTo('create').with(null);
            });

            it('creates a new appraisal cycle', function (done) {
                AppraisalCycle.create(newCycle).then(function () {
                    expect(appraisalsAPI.create).toHaveBeenCalled();
                })
                .finally(done) && $rootScope.$digest();
            });

            it('sanitizes the data via instance before calling the api', function (done) {
                var sanitizedData = AppraisalCycleInstance.init(newCycle).toAPI();

                AppraisalCycle.create(newCycle).then(function () {
                    expect(appraisalsAPI.create).toHaveBeenCalledWith(sanitizedData);
                })
                .finally(done) && $rootScope.$digest();
            });

            it('returns an instance of the model', function (done) {
                AppraisalCycle.create(newCycle).then(function (savedCycle) {
                    expect(AppraisalCycleInstance.isInstance(savedCycle)).toBe(true);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('all()', function () {
            describe('instances', function () {
                beforeEach(function () {
                    resolveApiCallTo('all').with(cycles);
                });

                it('returns a list of model instances', function (done) {
                    AppraisalCycle.all().then(function (cycles) {
                        expect(cycles.list.every(function (cycle) {
                            return AppraisalCycleInstance.isInstance(cycle);
                        })).toBe(true);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });

            describe('when called without arguments', function () {
                beforeEach(function () {
                    resolveApiCallTo('all').with(cycles);
                });

                it('returns all appraisal cycles', function (done) {
                    AppraisalCycle.all().then(function (cycles) {
                        expect(appraisalsAPI.all).toHaveBeenCalled();
                        expect(cycles.list.length).toEqual(10);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });

            describe('when called with filters', function () {
                var p;

                describe('falsey values', function () {
                    beforeEach(function () {
                        resolveApiCallTo('all').with({
                            list: cycles.list,
                            total: cycles.count
                        });
                    });

                    beforeEach(function () {
                        p = AppraisalCycle.all({
                            filter_1: 'a non-empty string',
                            filter_2: '',
                            filter_3: 456,
                            filter_4: 0,
                            filter_5: undefined,
                            filter_6: { foo: 'foo' },
                            filter_7: null,
                            filter_8: {}
                        });
                    });

                    it('skips falsey (null, undefined, empty string), except for 0', function (done) {
                        p.then(function () {
                            expect(appraisalsAPI.all).toHaveBeenCalledWith({
                                filter_1: 'a non-empty string',
                                filter_3: 456,
                                filter_4: 0,
                                filter_6: { foo: 'foo' },
                                filter_8: {}
                            }, undefined);
                        })
                        .finally(done) && $rootScope.$digest();
                    });
                });

                describe('simple filter', function () {
                    var filtered = null;
                    var typeFilter = 'Type #3';

                    beforeEach(function () {
                        resolveApiCallTo('all').with((function () {
                            filtered = cycles.list.filter(function (cycle) {
                                return cycle.type === typeFilter;
                            });

                            return { list: filtered, total: filtered.length };
                        })());
                    });

                    it('can filter the appraisal cycles list', function (done) {
                        AppraisalCycle.all({
                            type: typeFilter
                        }).then(function (cycles) {
                            expect(appraisalsAPI.all).toHaveBeenCalledWith({ type: typeFilter }, undefined);
                            expect(cycles.list.length).toEqual(filtered.length);
                        })
                        .finally(done) && $rootScope.$digest();
                    });
                });

                describe('date filters', function () {
                    beforeEach(function () {
                        resolveApiCallTo('all').with({
                            list: cycles.list,
                            total: cycles.count
                        });
                    });

                    describe('filter names', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.all({
                                cycle_start_date_from: jasmine.any(Date),
                                cycle_self_appraisal_due_to: jasmine.any(Date),
                                cycle_manager_appraisal_due_to: jasmine.any(Date),
                                cycle_grade_due_from: jasmine.any(Date)
                            });
                        });

                        it('converts the filter names to the correct api parameter names', function (done) {
                            p.then(function () {
                                expect(appraisalsAPI.all).toHaveBeenCalledWith({
                                    cycle_start_date: jasmine.any(Object),
                                    cycle_self_appraisal_due: jasmine.any(Object),
                                    cycle_manager_appraisal_due: jasmine.any(Object),
                                    cycle_grade_due: jasmine.any(Object)
                                }, undefined);
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });

                    describe('when searching only by "from" date', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.all({
                                cycle_start_date_from: '01/09/2016'
                            });
                        });

                        it('provides the API with the correct filter values', function (done) {
                            p.then(function () {
                                expect(appraisalsAPI.all).toHaveBeenCalledWith({
                                    cycle_start_date: { '>=': '2016-09-01' }
                                }, undefined);
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });

                    describe('when searching only by "to" date', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.all({
                                cycle_grade_due_to: '22/10/2016'
                            });
                        })

                        it('provides the API with the correct filter values', function (done) {
                            p.then(function () {
                                expect(appraisalsAPI.all).toHaveBeenCalledWith({
                                    cycle_grade_due: { '<=': '2016-10-22' }
                                }, undefined);
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });

                    describe('when searching both by "from" and "to" date', function () {
                        beforeEach(function () {
                            p = AppraisalCycle.all({
                                cycle_manager_appraisal_due_from: '01/09/2016',
                                cycle_manager_appraisal_due_to: '22/10/2016'
                            });
                        })

                        it('provides the API with the correct filter values', function (done) {
                            p.then(function () {
                                expect(appraisalsAPI.all).toHaveBeenCalledWith({
                                    cycle_manager_appraisal_due: { '>=': '2016-09-01' },
                                    cycle_manager_appraisal_due: { '<=': '2016-10-22' }
                                }, undefined);
                            })
                            .finally(done) && $rootScope.$digest();
                        });
                    });
                });
            });

            describe('when called with pagination', function () {
                var pagination = { page: 3, size: 2 };

                beforeEach(function () {
                    var start = pagination.page * pagination.size;
                    var end = start + pagination.size;

                    resolveApiCallTo('all').with((function () {
                        var paginated = cycles.list.slice(start, end);

                        return { list: paginated, total: paginated.length };
                    })());
                });

                it('can paginate the appraisla cycles list', function (done) {
                    AppraisalCycle.all(null, pagination).then(function (cycles) {
                        expect(appraisalsAPI.all).toHaveBeenCalledWith(null, pagination);
                        expect(cycles.list.length).toEqual(2);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });
        });

        describe('find()', function () {
            var targetId = '4217';

            beforeEach(function () {
                resolveApiCallTo('find').with(cycles.list.filter(function (cycle) {
                    return cycle.id === targetId;
                })[0]);
            });

            it('finds a cycle by id', function (done) {
                AppraisalCycle.find(targetId).then(function (cycle) {
                    expect(appraisalsAPI.find).toHaveBeenCalledWith(targetId);
                    expect(cycle.id).toBe('4217');
                    expect(cycle.type).toBe('Type #2');
                })
                .finally(done) && $rootScope.$digest();
            });

            it('returns an instance of the model', function (done) {
                AppraisalCycle.find(targetId).then(function (cycle) {
                    expect(AppraisalCycleInstance.isInstance(cycle)).toBe(true);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('total()', function () {
            beforeEach(function () {
                resolveApiCallTo('total').with(cycles.list.length);
            });

            it('gets the total number of cycles', function (done) {
                AppraisalCycle.total().then(function (total) {
                    expect(appraisalsAPI.total).toHaveBeenCalled();
                    expect(total).toBe(cycles.list.length);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        /**
         * Adds a `spyOn` on the given `appraisalsApi` method, and then returns
         * an object with a `.with()` method, which receives the value the
         * stubbed method must respond with
         *
         * @param {string} method
         * @return {object}
         */
        function resolveApiCallTo(method) {
            var spy = spyOn(appraisalsAPI, method);

            return {

                /**
                 * Creates a fake call for the stubbed method, that
                 * returns a promise which resolves with the given value
                 *
                 * @param {any} value
                 * @return {Promise}
                 */
                with: function (value) {
                    spy.and.callFake(function () {
                        var deferred = $q.defer();
                        deferred.resolve(value);

                        return deferred.promise;
                    });
                }
            };
        }
    });
});
