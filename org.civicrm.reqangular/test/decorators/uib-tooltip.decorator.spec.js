/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/decorators/uib-tooltip.decorator',
  'common/angular',
  'common/angularBootstrap'
], function (angularMocks, $uibTooltipDecorator, angular) {
  'use strict';

  describe('$uiTooltip.clickable', function () {
    var $compile, $document, $rootScope, $timeout, $uibTooltip, $provide, $window, element;

    beforeEach(function () {
      module('ui.bootstrap');
      module(function (_$provide_) {
        $provide = _$provide_;
      });
      inject(function () {
        $provide.decorator('$uibTooltip', $uibTooltipDecorator);
      });
    });
    beforeEach(inject(function (_$compile_, _$document_, _$rootScope_, _$timeout_, _$uibTooltip_, _$window_) {
      $compile = _$compile_;
      $document = _$document_;
      $rootScope = _$rootScope_;
      $timeout = _$timeout_;
      $uibTooltip = _$uibTooltip_;
      $window = _$window_;
    }));
    beforeEach(function () {
      element = angular.element('<div uib-tooltip-template="\'tooltip\'" uib-tooltip-clickable="true"><script id="tooltip" type="text/ng-template"><div class="tooltip-template">Content</div></script></div>');
      var $scope = $rootScope.$new();
      $compile(element)($scope);
      $document.find('body').append(element);
      $rootScope.$digest();
      element.trigger('mouseenter')
      $timeout.flush();
      $rootScope.$digest();

    });

    // just temporarily, so we are sure test runs
    describe('you wot?', function () {
      it('smth', function () {
        console.log($document.find('.tooltip-template:visible').length)
        expect('smth').toBe('nth');
      });
    });
  });
});
