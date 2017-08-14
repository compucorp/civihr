/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/modules/routers/compu-ui-router'
], function (angular) {
  'use strict';

  describe('Test compu.ui.router Module', function () {
    var $stateProvider, $state;

    beforeEach(function () {
      var testUiRouter = angular.module('testUiRouter', ['compu.ui.router']);

      testUiRouter.config(['$stateProvider', function (_$stateProvider) {
        $stateProvider = _$stateProvider;
      }]);

            // Initialize test.app injector
      module('testUiRouter');

      inject(['$state', function (_$state) {
        $state = _$state;
      }]);
    });

    it('Module should inherit from ui.router', function () {
      expect($stateProvider).toBeDefined();
    });

    describe('Test $stateProvider', function () {
      beforeEach(function () {
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
