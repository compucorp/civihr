/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/modules/routers/compu-ng-route'
], function (angular) {
  'use strict';

  describe('Test compuNgROute module', function () {
    var $routeProvider, $route;

    beforeEach(function () {
      angular.module('testNgRoute', ['compuNgRoute'])
                .config(['$routeProvider', function (_$routeProvider) {
                  $routeProvider = _$routeProvider;
                }]);

            // Initialize test.app injector
      module('testNgRoute');

      inject(['$route', function (_$route) {
        $route = _$route;
      }]);
    });

    it('Module should inherit from ngRoute', function () {
      expect($routeProvider).toBeDefined();
    });

    describe('Test $routeProvider', function () {
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
  });
});
