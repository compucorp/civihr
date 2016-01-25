define([
    'common/angular',
    'common/angularMocks',
    'appraisals/app'
], function (angular) {
    'use strict';

    describe('crmShowMore', function () {
        var $compile, $log, $scope, directive;

        beforeEach(module('appraisals'));
        beforeEach(module('appraisals.templates'));
        beforeEach(inject(function ($rootScope, _$compile_, _$log_) {
            $scope = mockScope($rootScope);
            $compile = _$compile_;
            $log = _$log_;

            spyOn($log, 'debug');
            spyOn($log, 'error');

            compileDirective();
        }));

        describe('init', function () {
            it('is initialized', function () {
                expect($log.debug).toHaveBeenCalled();
            });
        });

        describe('mandatory params', function () {
            it('requires callback and done to be passed', function () {
                compileDirective({ 'callback': null, 'done': null });
                expect($log.error.calls.count()).toEqual(2);
            });
        });

        describe('show more button', function () {
            describe('when there are still items to load', function () {
                it('shows the button', function () {
                    expect(directive.find('button').length).toEqual(1);
                });
            });

            describe('when all the items are loaded', function () {
                describe('via controller property change', function () {
                    beforeEach(function () {
                        ($scope.allLoaded = true) && $scope.$digest();
                    });

                    it('removes the button', function () {
                        expect(directive.find('button').length).toEqual(0);
                    });
                });

                describe('via button clicking', function () {
                    beforeEach(function () {
                        var button = directive.find('button')

                        for (var i = 1; i <= 3; i++) {
                            button.triggerHandler('click');
                        }

                        $scope.$digest();
                    });

                    it('removes the button', function () {
                        expect(directive.find('button').length).toBe(0)
                    });
                });
            });
        });

        describe('callback', function () {
            beforeEach(function () {
                spyOn($scope, 'loadItems').and.callThrough();
                directive.find('button').triggerHandler('click');
            });

            it('calls the method on the controller', function () {
                expect($scope.loadItems).toHaveBeenCalled();
            });
        });

        /**
         * Creates a dummy scope to simulate a controller that passes
         * its property to the directive
         *
         * @param {Object} $rootScope
         * @return {Object} $scope
         */
        function mockScope($rootScope) {
            var $scope = $rootScope.$new();
            var page = 0;
            var items = (function (items) {
                for (var i = 0; i < 20; i++) { items.push(i); }
                return items;
            })([]);

            $scope.allLoaded = false;
            $scope.items = [];
            $scope.loadItems = function () {
                var startIndex = page++ * 5;
                var endIndex = startIndex + 5;

                $scope.items = $scope.items.concat(items.slice(startIndex, endIndex));

                if ($scope.items.length === items.length) {
                    $scope.allLoaded = true;
                }
            };

            $scope.loadItems();

            return $scope;
        }

        /**
         * Creates and compiles the crm-show-more directive
         *
         * @param {Object} options - Options that overrides the default ones
         */
        function compileDirective(options) {
            var params = angular.extend({
                'callback': 'loadItems()',
                'done': 'allLoaded'
            }, options);

            var paramsString = Object.keys(params).reduce(function (string, key) {
                return string + ( params[key] !== null ? (key + '="' + params[key] + '" ') : ' ' );
            }, '');

            directive = angular.element(
                '<div crm-show-more ' + paramsString + '>' +
                    '<span ng-repeat="item in items">{{item}}</span>' +
                '</div>'
            );

            $compile(directive)($scope);
            $scope.$digest();
        }
    });
});
