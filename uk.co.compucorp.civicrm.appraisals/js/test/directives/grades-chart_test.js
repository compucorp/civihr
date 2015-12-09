define([
    'common/angularMocks',
    'appraisals/app'
], function () {
    'use strict';

    describe('crmGradesChart', function () {
        var $compile, $log, $scope, directive;

        beforeEach(module('appraisals'));
        beforeEach(inject(function ($rootScope, _$compile_, _$log_) {
            $scope = mockScope($rootScope);
            $compile = _$compile_;
            $log = _$log_;

            spyOn($log, 'debug');
            spyOn($log, 'error');

            compileDirective();
        }));

        describe('validation', function () {
            describe('when the [chart-data] parameter is not passed', function () {
                beforeEach(function () {
                    compileDirective('');
                });

                it('throws an error', function () {
                    expect($log.error.calls.count()).toEqual(1);
                });
            });

            describe('when the [chart-data] parameter is not an array', function () {
                beforeEach(function () {
                    $scope.wrongValue = {};
                    compileDirective('chart-data="wrongValue"');
                });

                it('throws an error', function () {
                    expect($log.error.calls.count()).toEqual(1);
                });
            });

            describe('when the chart data items have not the expected structure', function () {
                beforeEach(function () {
                    $scope.wrongValue = [{ foo: 'foo', bar: 'bar' }, { baz: 'baz' }];
                    compileDirective('chart-data="wrongValue"');
                });

                it('throws an error', function () {
                    expect($log.error.calls.count()).toEqual(1);
                });
            });
        });

        describe('drawing', function () {
            it('prints out an svg', function () {
                expect(directive.find('svg').length).toEqual(1);
            });

            it('displays the correct number of bars', function () {
                expect(directive.find('rect').length).toEqual($scope.chartData.length);
            });

            it('displays the bars with the correct values', function () {
                var bars = directive.find('rect');

                expect($scope.chartData.every(function (entry, index) {
                    return entry.value === bars[index].__data__.value;
                })).toBe(true);
            });

            it('displays the labels in the correct order', function () {
                var labels = directive.find('.chart-axis-y > .tick > text');

                expect($scope.chartData.every(function (entry, index) {
                    return entry.label === labels[index].textContent;
                }))
            });
        });

        /**
         * Creates and compiles the directive
         *
         * @param {object} params - The params that overrides the default ones
         */
        function compileDirective(params) {
            directive = (function (params) {
                var params = (typeof params === 'undefined') ? ' chart-data="chartData"' : params;

                return angular.element(
                    '<div crm-grades-chart ' + params + '></div>'
                );
            })(params);

            $compile(directive)($scope);
            $scope.$digest();
        }

        /**
         * Creates a mock scope with the chart data
         *
         * @param {object} $rootScope
         * @return {object}
         */
        function mockScope($rootScope) {
            var $scope = $rootScope.$new();

            $scope.chartData = [
                { label: "Value #1", value: 10 },
                { label: "Value #2", value: 20 },
                { label: "Value #3", value: 30 },
                { label: "Value #4", value: 40 },
            ];

            return $scope;
        }
    });
})
