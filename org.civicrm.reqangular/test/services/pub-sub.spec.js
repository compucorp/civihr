/* globals angular, inject */
/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/services/pub-sub'
], function () {
  'use strict';

  describe('Pub Sub Service test', function () {
    var ctrlConstructor, $rootScope, $timeout;
    beforeEach(function () {
      var moduleA = angular.module('moduleA', ['common.services']);
      moduleA.controller('controllerA',
        function ($scope, pubSub) {
          $scope.publish = function (data) {
            pubSub.publish('changeB', data);
          };
        }
      );

      var moduleB = angular.module('moduleB', ['common.services']);
      moduleB.controller('controllerB',
        function ($scope, pubSub) {
          $scope.subscriber = pubSub.subscribe('changeB', function (data) {
            $scope.data = data;
          });
        }
      );

      module('moduleA', 'moduleB');
    });

    beforeEach(inject(function (_$controller_, _$rootScope_, _$timeout_) {
      ctrlConstructor = _$controller_;
      $rootScope = _$rootScope_;
      $timeout = _$timeout_;
    }));

    it('test pubsub behaviour when subscriber event is present', function () {
      var $scopeA = $rootScope.$new();
      var $scopeB = $rootScope.$new();
      ctrlConstructor('controllerA', { $scope: $scopeA });
      ctrlConstructor('controllerB', { $scope: $scopeB });
      $scopeA.publish('someval');
      $timeout(function () {
        expect($scopeB.data).toBe('someval');
      }, 0);
      $timeout.flush();
    });

    it('test pubsub behaviour when subscriber event is removed', function () {
      var $scopeA = $rootScope.$new();
      var $scopeB = $rootScope.$new();
      ctrlConstructor('controllerA', { $scope: $scopeA });
      ctrlConstructor('controllerB', { $scope: $scopeB });
      $scopeB.subscriber.remove();
      $scopeA.publish('someval');
      $timeout(function () {
        expect($scopeB.data).not.toBe('someval');
      }, 0);
      $timeout.flush();
    });
  });
});
