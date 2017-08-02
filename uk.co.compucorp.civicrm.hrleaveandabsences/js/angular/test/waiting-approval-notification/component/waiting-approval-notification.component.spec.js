/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angularMocks',
    'leave-absences/waiting-approval-notification/app'
  ], function () {
    'use strict';

    describe('waitingApprovalNotification', function () {
      var $componentController, $log, $rootScope, OptionGroup, OptionGroupAPIMock;

      beforeEach(module('leave-absences.mocks', 'waiting-approval-notification'));
      beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_, _OptionGroupAPIMock_, _OptionGroup_) {
        $componentController = _$componentController_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        OptionGroupAPIMock = _OptionGroupAPIMock_;
        OptionGroup = _OptionGroup_;
        spyOn($log, 'debug');
        spyOn($rootScope, '$emit');

        spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
          return OptionGroupAPIMock.valuesOf(name);
        });

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('broadcasts an event with filter data', function () {
        expect($rootScope.$emit).toHaveBeenCalledWith('WaitingApproval:: Initialize Filters', {
          managed_by: window.Drupal.settings.currentCiviCRMUserId,
          status_id: '3'
        });
      });

      function compileComponent () {
        $componentController('waitingApprovalNotification', null, {});
        $rootScope.$digest();
      }
    });
  });
})(CRM);
