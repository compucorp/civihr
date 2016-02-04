define([
    'common/angular',
    'common/angularRoute',
    'common/angularUiRouter',
    'common/angularMocks',
    'common/services/compuRoute/route-decorator'
], function (angular) {
    'use strict';

    describe('compuRoute: RouteDecorator Unit Test', function () {
        var RouteDecorator, $routeProvider, $stateProvider, $route, $state;

        beforeEach(function () {
            var testNgRoute = angular.module('testNgRoute', ['ngRoute'], function () {
            });
            testNgRoute.config(['$routeProvider', function (_$routeProvider) {
                $routeProvider = _$routeProvider;
            }]);

            var testUiRouter = angular.module('testUiRouter', ['ui.router'], function () {
            });
            testUiRouter.config(['$stateProvider', function (_$stateProvider) {
                $stateProvider = _$stateProvider;
            }]);

            // Initialize test.app injector
            module('common.compuRoute', 'testNgRoute', 'testUiRouter');

            inject(['RouteDecorator', '$route', '$state', function (_RouteDecorator, _$route, _$state) {
                RouteDecorator = _RouteDecorator;
                $route = _$route;
                $state = _$state;
            }]);
        });

        it('Decorator and providers should be defined', function () {
            expect(RouteDecorator).toBeDefined();
            expect($routeProvider).toBeDefined();
            expect($stateProvider).toBeDefined();
        });

        it('enableCommonResolve function should accept either $routeProvider od $stateProvider as argument', function () {
            expect(function () {
                RouteDecorator.enableCommonResolve($routeProvider);
            }).not.toThrow();

            expect(function () {
                RouteDecorator.enableCommonResolve($stateProvider);
            }).not.toThrow();

            expect(function () {
                RouteDecorator.enableCommonResolve({});
            }).toThrow();

            expect(function () {
                RouteDecorator.enableCommonResolve('Test');
            }).toThrow();
        });

        describe('Test $routeProvider', function () {
            beforeEach(function () {
                RouteDecorator.enableCommonResolve($routeProvider);
            });

            it('Should have resolveForAll function', function () {
                expect($routeProvider.resolveForAll).toBeDefined();
            });

            beforeEach(function () {
                $routeProvider.resolveForAll({
                    test: function () {
                        return 5;
                    }
                }).when('/', {
                    template: 'Test',
                    resolve: {
                        a: 'b',
                        c: 'd',
                        e: function () {
                            return 'f';
                        }
                    }
                });
            });

            it('Should save common resolve in provider', function () {
                expect($routeProvider.commonResolve).toBeDefined();
                expect($routeProvider.commonResolve.test()).toEqual(5);
            });

            it('Each route should use common resolve provider', function () {
                // Route has common resolves
                expect($route.routes['/'].resolve.test).toBeDefined();
                expect($route.routes['/'].resolve.test()).toEqual(5);
            });

            it('Routes should still have it\'s own resolves', function () {
                expect($route.routes['/'].resolve.a).toBeDefined();
                expect($route.routes['/'].resolve.a).toEqual('b');
            });
        });

        describe('Test $stateProvider', function () {
            beforeEach(function () {
                RouteDecorator.enableCommonResolve($stateProvider);

                $stateProvider.resolveForAll({
                    test: '5'
                }).state('main', {
                    url: '/',
                    resolve: {
                        a: 'b',
                        c: 'd'
                    }
                });
            });

            it('Common resolve should be saved in $stateProvider', function () {
                expect($stateProvider.commonResolve).toBeDefined();
            });

            it('Each state should use common resolve', function () {
                expect($state.get()[1].resolve.test).toBeDefined();
                expect($state.get()[1].resolve.test).toEqual('5');
            });

            it('Each state should still have it\'s own resolve', function () {
                expect($state.get()[1].resolve.a).toBeDefined();
                expect($state.get()[1].resolve.c).toEqual('d');
            });
        });
    });
});
