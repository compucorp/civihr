/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'leave-absences/leave-type-wizard/form/form.module'
], function (angular) {
  'use strict';

  describe('LeaveTypeWizardForm', function () {
    var $componentController, $log;

    beforeEach(angular.mock.module('leave-type-wizard.form'));

    beforeEach(inject(function (_$componentController_, _$log_) {
      $componentController = _$componentController_;
      $log = _$log_;
    }));

    beforeEach(function () {
      spyOn($log, 'debug').and.callThrough();
    });

    beforeEach(function () {
      initComponent();
    });

    it('loads the form controller', function () {
      expect($log.debug).toHaveBeenCalledWith('Controller: LeaveTypeWizardFormController');
    });

    /**
     * Initiates the component and stores it for tests
     */
    function initComponent () {
      $componentController('leaveTypeWizardForm');
    }
  });
});
