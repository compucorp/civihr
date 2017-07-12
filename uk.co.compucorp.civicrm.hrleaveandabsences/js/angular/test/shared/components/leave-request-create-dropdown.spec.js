/* eslint-env amd, jasmine */

define([
  'common/angular',
  'leave-absences/manager-leave/app'
], function (angular) {
  'use strict';

  describe('leaveRequestCreateDropdown', function () {
    var vm, $componentController, $log, $rootScope;
    var contactId = '208';

    beforeEach(module('manager-leave'));

    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;

      spyOn($log, 'debug');

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('on init', function () {
      it('has contact ID', function () {
        expect(vm.contactId).toBe(contactId);
      });

      it('has leave request options', function () {
        var options = vm.leaveRequestOptions.map(function (option) {
          return option.type;
        });

        expect(options).toEqual(['leave', 'sickness', 'toil']);
      });
    });

    function compileComponent () {
      vm = $componentController('leaveRequestCreateDropdown', null, { contactId: contactId });
      $rootScope.$digest();
    }
  });
});
