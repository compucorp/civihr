define([
    'common/lodash',
    'common/angularMocks',
    'appraisals/app'
], function (_) {
    'use strict';

    describe('AppraisalCycleInstance', function () {
        var $q, $rootScope, AppraisalCycleInstance, appraisalsAPI;
        var instanceInterface = ['init', 'attributes', 'fromAPI', 'toAPI', 'update'];

        beforeEach(module('appraisals'));
        beforeEach(inject(['$q', '$rootScope', 'AppraisalCycleInstance', 'api.appraisals',
            function (_$q_, _$rootScope_, _AppraisalCycleInstance_, _appraisalsAPI_) {
                $q = _$q_;
                $rootScope = _$rootScope_;

                AppraisalCycleInstance = _AppraisalCycleInstance_;
                appraisalsAPI = _appraisalsAPI_;
            }
        ]));

        it('has the expected interface', function () {
            expect(_.functions(AppraisalCycleInstance)).toEqual(instanceInterface);
        });

        describe('init()', function () {
            var instance;

            describe('simple initialization', function () {
                var attributes = { foo: 'foo', bar: 'bar' };

                beforeEach(function () {
                    instance = AppraisalCycleInstance.init(attributes);
                });

                it('create a new instance', function () {
                    expect(instance).toEqual(jasmine.any(Object));
                });

                it('retains the same interface', function () {
                    expect(_.functions(instance)).toEqual(instanceInterface);
                });

                it('contains the attributes passed to it', function () {
                    expect(_.values(instance)).toEqual(_.values(attributes));
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
                    expect(instance.appraisals_count).toBe(9);
                    expect(instance.completion_percentage).toBe(22)
                });
            });
        });

        describe('attributes()', function () {
            var attributes;

            beforeEach(function () {
                attributes = AppraisalCycleInstance.init({
                    foo: 'foo',
                    bar: 'bar',
                    fn: function () {}
                })
                .attributes();
            });

            it('returns only the attributes, without the methods', function () {
                expect(attributes).toEqual(jasmine.objectContaining({ foo: 'foo', bar: 'bar' }));
                expect(attributes).not.toEqual(jasmine.objectContaining({ fn: jasmine.any(Function) }));
            });

            it('returns a plain object w/o prototype', function () {
                expect(Object.getPrototypeOf(attributes)).toBe(null);
            });
        });

        describe('toAPI()', function () {
            var instance, toAPIData;

            beforeEach(function () {
                instance = AppraisalCycleInstance.init({
                    foo: 'foo',
                    cycle_start_date: '23/09/2015',
                    cycle_grade_due: '22/11/2015',
                    appraisals_count: '4'
                });
                toAPIData = instance.toAPI();
            });

            it('returns a plain object w/o prototype', function () {
                expect(Object.getPrototypeOf(toAPIData)).toBe(null);
            });

            it('filters out the appraisals_count field', function () {
                expect(Object.keys(toAPIData)).toEqual(_.without(Object.keys(instance.attributes()), 'appraisals_count'));
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
                spyOn(appraisalsAPI, 'update').and.callFake(function () {
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
                    expect(appraisalsAPI.update).toHaveBeenCalledWith(instance.toAPI());
                });

                it('reflects the updated data on its attributes', function (done) {
                    var updated = _.assign(Object.create(null), oldData, newData);

                    p.then(function () {
                        expect(instance.attributes()).toEqual(updated);
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
