define([
    'common/angular',
    'common/angularMocks',
    'appraisals/app'
], function (angular) {
    'use strict';

    describe('AppraisalCycle', function () {
        var $q, $rootScope, AppraisalCycle, appraisalsAPI, cycles;

        cycles = [
            { id: '1', type: 'type 1' }, { id: '2', type: 'type 4' },
            { id: '3', type: 'type 3' }, { id: '4', type: 'type 3' },
            { id: '5', type: 'type 2' }, { id: '6', type: 'type 3' },
            { id: '7', type: 'type 1' }, { id: '8', type: 'type 3' },
            { id: '9', type: 'type 2' }, { id: '10', type: 'type 4' }
        ];

        beforeEach(module('appraisals'));
        beforeEach(inject(['$q', '$rootScope', 'AppraisalCycle', 'api.appraisals',
            function (_$q_, _$rootScope_, _AppraisalCycle_, _appraisalsAPI_) {
                $q = _$q_;
                $rootScope = _$rootScope_;
                AppraisalCycle = _AppraisalCycle_;
                appraisalsAPI = _appraisalsAPI_;
            }
        ]));

        it('has the expected api', function () {
            expect(Object.keys(AppraisalCycle)).toEqual([
                'active', 'all', 'create', 'find', 'grades', 'statuses',
                'statusOverview', 'update', 'total', 'types'
            ]);
        });

        describe('active()', function () {
            beforeEach(function () {
                resolveApiCallTo('activeCycles').with([1, 2, 3, 4, 5]);
            });

            it('returns the active cycles', function (done) {
                AppraisalCycle.active().then(function (activeCycles) {
                    expect(appraisalsAPI.activeCycles).toHaveBeenCalled();
                    expect(activeCycles.length).toEqual(5);
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
                resolveApiCallTo('types').with(['type 1', 'type 2', 'type 3']);
            });

            it('returns the appraisal cycle types', function (done) {
                AppraisalCycle.types().then(function (types) {
                    expect(appraisalsAPI.types).toHaveBeenCalled();

                    expect(types.length).toEqual(3);
                    expect(types).toEqual(['type 1', 'type 2', 'type 3']);
                })
                .finally(done) && $rootScope.$digest();
            });
        })

        describe('statuses()', function () {
            beforeEach(function () {
                resolveApiCallTo('statuses').with(['status 1', 'status 2']);
            });

            it('returns the appraisal cycle statuses', function (done) {
                AppraisalCycle.statuses().then(function (statuses) {
                    expect(appraisalsAPI.statuses).toHaveBeenCalled();

                    expect(statuses.length).toEqual(2);
                    expect(statuses).toEqual(['status 1', 'status 2']);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('create()', function () {
            var newCycle = {
                name: 'new cycle',
                type: 'type 4',
                period: '01/01/2014 - 01/01/2015'
            };
            var newCycleSaved = angular.extend({}, newCycle, { id: '1234' });

            beforeEach(function () {
                resolveApiCallTo('create').with(newCycleSaved);
            });

            it('creates a new appraisal cycle', function (done) {
                AppraisalCycle.create(newCycle).then(function (savedCycle) {
                    expect(appraisalsAPI.create).toHaveBeenCalledWith(newCycle);
                    expect(savedCycle).toEqual(newCycleSaved);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('all()', function () {
            describe('when called without arguments', function () {
                beforeEach(function () {
                    resolveApiCallTo('all').with(cycles);
                });

                it('returns all appraisal cycles', function (done) {
                    AppraisalCycle.all().then(function (cycles) {
                        expect(appraisalsAPI.all).toHaveBeenCalled();
                        expect(cycles.length).toEqual(10);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });

            describe('when called with filters', function () {
                var typeFilter = 'type 3';

                beforeEach(function () {
                    resolveApiCallTo('all').with(cycles.filter(function (cycle) {
                        return cycle.type === typeFilter;
                    }));
                });

                it('can filter the appraisal cycles list', function (done) {
                    AppraisalCycle.all({
                        type: typeFilter
                    }).then(function (cycles) {
                        expect(appraisalsAPI.all).toHaveBeenCalledWith({ type: typeFilter }, undefined);
                        expect(cycles.length).toEqual(4);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });

            describe('when called with pagination', function () {
                var pagination = { page: 3, size: 2 };

                beforeEach(function () {
                    var start = pagination.page * pagination.size;
                    var end = start + pagination.size;

                    resolveApiCallTo('all').with(cycles.slice(start, end));
                });

                it('can paginate the appraisla cycles list', function (done) {
                    AppraisalCycle.all(null, pagination).then(function (cycles) {
                        expect(appraisalsAPI.all).toHaveBeenCalledWith(null, pagination);
                        expect(cycles.length).toEqual(2);
                    })
                    .finally(done) && $rootScope.$digest();
                });
            });
        });

        describe('find()', function () {
            var targetId = '7';

            beforeEach(function () {
                resolveApiCallTo('find').with(cycles.filter(function (cycle) {
                    return cycle.id === targetId;
                })[0]);
            });

            it('finds a cycle by id', function (done) {
                AppraisalCycle.find(targetId).then(function (cycle) {
                    expect(appraisalsAPI.find).toHaveBeenCalledWith(targetId);
                    expect(cycle.id).toBe('7');
                    expect(cycle.type).toBe('type 1');
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('update()', function () {
            var targetId = '5';
            var newData = { name: 'foo', type: 'bar', status: 'baz' };

            beforeEach(function () {
                resolveApiCallTo('update').with((function () {
                    var cycle = cycles.filter(function (cycle) {
                        return cycle.id === targetId;
                    })[0];

                    return angular.extend({}, cycle, newData);
                })());
            });

            it('updates a cycle', function (done) {
                AppraisalCycle.update(targetId, newData).then(function (cycle) {
                    expect(appraisalsAPI.update).toHaveBeenCalledWith(targetId, newData);
                    expect(cycle.id).toBe('5');
                    expect(cycle.name).toBe(newData.name);
                    expect(cycle.type).toBe(newData.type);
                    expect(cycle.status).toBe(newData.status);
                })
                .finally(done) && $rootScope.$digest();
            });
        });

        describe('total()', function () {
            beforeEach(function () {
                resolveApiCallTo('total').with(cycles.length);
            });

            it('gets the total number of cycles', function (done) {
                AppraisalCycle.total().then(function (total) {
                    expect(appraisalsAPI.total).toHaveBeenCalled();
                    expect(total).toBe(cycles.length);
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
