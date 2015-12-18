define([
    'common/lodash',
    'common/angularMocks',
    'appraisals/app'
], function (_) {
    'use strict';

    describe('AppraisalCycleInstance', function () {
        var $rootScope, AppraisalCycleInstance;
        var instanceInterface = ['init', 'attributes', 'fromAPI', 'toAPI'];

        beforeEach(module('appraisals'));
        beforeEach(inject(function (_$rootScope_, _AppraisalCycleInstance_) {
            $rootScope = _$rootScope_;
            AppraisalCycleInstance = _AppraisalCycleInstance_;
        }));

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
                    cycle_grade_due: '2015-11-22'
                };

                beforeEach(function () {
                    instance = AppraisalCycleInstance.init(attributes, true);
                });

                it('normalizes the data', function () {
                    expect(instance.foo).toBe(attributes.foo);
                    expect(instance.cycle_start_date).toBe('23/09/2015');
                    expect(instance.cycle_grade_due).toBe('22/11/2015');
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
                    cycle_grade_due: '22/11/2015'
                });
                toAPIData = instance.toAPI();
            });

            it('returns a plain object w/o prototype', function () {
                expect(Object.getPrototypeOf(toAPIData)).toBe(null);
            });

            it('returns all the attributes of the instance', function () {
                expect(Object.keys(toAPIData)).toEqual(Object.keys(instance.attributes()));
            });

            it('formats the dates in the YYYY-MM-DD format', function () {
                expect(toAPIData.cycle_start_date).toBe('2015-09-23');
                expect(toAPIData.cycle_grade_due).toBe('2015-11-22');
            });
        });
    });
});
