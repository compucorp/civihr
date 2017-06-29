/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/absence-tab/app'
  ], function (angular) {
    'use strict';

    describe('absenceTabContainer', function () {
      var $compile, $log, $rootScope, $templateCache, settings, component;

      beforeEach(module('leave-absences.templates', 'absence-tab'));
      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$templateCache_, _settings_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        $templateCache = _$templateCache_;
        settings = _settings_;

        spyOn($log, 'debug');
        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      function compileComponent () {
        var $scope = $rootScope.$new();

        disableUibTab();

        component = angular.element('<absence-tab-container></absence-tab-container>');
        $compile(component)($scope);
        $scope.$digest();

        component.controller('absenceTabContainer');
      }

      /**
       * Disables the uib-tab directive, by simply renaming the tag
       *
       * The directive automatically compiled and ran the component in the
       * tab enabled by default, causing a whole lot of errors in this set
       *
       * This is a brute-force approach to solve the issues and let the test
       * focus only on the component rather than the content of the tabs
       *
       */
      function disableUibTab () {
        var tplPath = settings.pathTpl + 'components/absence-tab-container.html';
        var tpl = $templateCache.get(tplPath);

        $templateCache.put(tplPath, tpl.replace(/uib-tab/g, 'disabled-uib-tab'));
      }
    });
  });
})(CRM);
