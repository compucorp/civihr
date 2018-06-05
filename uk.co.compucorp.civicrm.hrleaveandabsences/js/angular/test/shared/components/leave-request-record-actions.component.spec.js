/* eslint-env amd, jasmine */

define([
  'leave-absences/manager-leave/app'
], function () {
  'use strict';

  describe('leaveRequestRecordActions', function () {
    var $log, $rootScope, $componentController, LeavePopup, beforeHashQueryParams, controller;
    var contactId = '208';

    beforeEach(module('manager-leave'));

    beforeEach(inject(function (_$log_, _$rootScope_, _$componentController_, _LeavePopup_, _beforeHashQueryParams_) {
      $log = _$log_;
      $rootScope = _$rootScope_;
      $componentController = _$componentController_;
      LeavePopup = _LeavePopup_;
      beforeHashQueryParams = _beforeHashQueryParams_;

      spyOn($log, 'debug');

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('on init', function () {
      it('has contact ID', function () {
        expect(controller.contactId).toBe(contactId);
      });

      it('has leave request options', function () {
        var options = controller.leaveRequestOptions.map(function (option) {
          return option.type;
        });

        expect(options).toEqual(['leave', 'sickness']);
      });
    });

    describe('automatic opening of a modal', function () {
      beforeEach(function () {
        spyOn(LeavePopup, 'openModal');
      });

      describe('when there is a "openModal" query string param', function () {
        describe('when the param value is "leave"', function () {
          beforeEach(function () {
            mockQueryStringAndCompile({ 'openModal': 'leave' });
          });

          it('opens the leave request modal', function () {
            expect(isModalOfType('leave')).toBe(true);
          });
        });

        describe('when the param value is "sickness"', function () {
          beforeEach(function () {
            mockQueryStringAndCompile({ 'openModal': 'sickness' });
          });

          it('opens the sickness request modal', function () {
            expect(isModalOfType('sickness')).toBe(true);
          });
        });

        describe('when the param value is "toil"', function () {
          beforeEach(function () {
            mockQueryStringAndCompile({ 'openModal': 'toil' });
          });

          it('opens the toil request modal', function () {
            expect(isModalOfType('toil')).toBe(true);
          });
        });
      });

      describe('when there is no "openModal" query string param', function () {
        beforeEach(function () {
          mockQueryStringAndCompile({ 'foo': 'bar' });
        });

        it('does not automatically open a modal', function () {
          expect(LeavePopup.openModal).not.toHaveBeenCalled();
        });
      });

      /**
       * Checks the `LeavePopup` service had been used
       * to open a modal of the given type
       *
       * @param {String} type
       * @return {Boolean}
       */
      function isModalOfType (type) {
        var leaveType = LeavePopup.openModal.calls.argsFor(0)[1];

        return leaveType === type;
      }

      /**
       * Mocks the query string by faking the value returned by the
       * beforeHashQueryParams, and then it initializes the component
       *
       * @param {Object} queryStringParams
       */
      function mockQueryStringAndCompile (queryStringParams) {
        spyOn(beforeHashQueryParams, 'parse').and.returnValue(queryStringParams);
        compileComponent();
      }
    });

    describe('openLeavePopup()', function () {
      var leaveRequest = { key: 'value' };
      var leaveType = 'some_leave_type';
      var selectedContactId = '101';

      beforeEach(function () {
        spyOn(LeavePopup, 'openModal');
        controller.openLeavePopup(leaveRequest, leaveType, selectedContactId);
      });

      it('opens the leave request popup', function () {
        expect(LeavePopup.openModal).toHaveBeenCalledWith(leaveRequest, leaveType, selectedContactId);
      });
    });

    function compileComponent () {
      controller = $componentController('leaveRequestRecordActions', null, { contactId: contactId });
      $rootScope.$digest();
    }
  });
});
