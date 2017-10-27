/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/data/absence-type-data',
  'mocks/data/leave-request-data',
  'mocks/data/option-group-mock-data',
  'leave-absences/shared/components/leave-calendar-day.component',
  'leave-absences/manager-leave/app'
], function (_, absenceTypeData, leaveRequestData, optionGroupData) {
  'use strict';

  describe('leaveCalendarDay', function () {
    var $componentController, $log, $rootScope, absenceTypes, controller,
      dayTypes, LeavePopup, leaveRequest;

    beforeEach(module('manager-leave'));
    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_,
    _LeavePopup_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      absenceTypes = absenceTypeData.all().values;
      dayTypes = optionGroupData.getCollection(
        'hrleaveandabsences_leave_request_day_type');
      LeavePopup = _LeavePopup_;
      leaveRequest = leaveRequestData.all().values[0];

      spyOn($log, 'debug');
      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('when leave request is ready', function () {
      var absenceType, fromDateType, toDateType;

      beforeEach(function () {
        controller.contactData.leaveRequest = leaveRequest;
        absenceType = _.find(absenceTypes, function (type) {
          return +type.id === +leaveRequest.type_id;
        });
        fromDateType = optionGroupData.specificObject(
          'hrleaveandabsences_leave_request_day_type', 'value',
          leaveRequest.from_date_type);
        toDateType = optionGroupData.specificObject(
          'hrleaveandabsences_leave_request_day_type', 'value',
          leaveRequest.from_date_type);

        $rootScope.$digest();
      });

      it('maps the absence type title', function () {
        expect(leaveRequest['type_id.title']).toEqual(absenceType.title);
      });

      it('maps the from date type label', function () {
        expect(leaveRequest['from_date_type.label']).toEqual(fromDateType.label);
      });

      it('maps the to date type label', function () {
        expect(leaveRequest['from_date_type.label']).toEqual(toDateType.label);
      });
    });

    describe('openLeavePopup()', function () {
      var event;
      var leaveRequest = { key: 'value' };
      var leaveType = 'some_leave_type';
      var selectedContactId = '101';
      var isSelfRecord = true;

      beforeEach(function () {
        event = jasmine.createSpyObj('event', ['stopPropagation']);
        spyOn(LeavePopup, 'openModal');
        controller.openLeavePopup(event, leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('opens the leave request popup', function () {
        expect(LeavePopup.openModal).toHaveBeenCalledWith(leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('stops the event from propagating', function () {
        expect(event.stopPropagation).toHaveBeenCalled();
      });
    });

    /**
     * Compiles and stores the component instance. Passes the contact and
     * support data to the controller.
     */
    function compileComponent () {
      var $scope = $rootScope.$new();

      controller = $componentController('leaveCalendarDay', {
        $scope: $scope
      }, {
        contactData: {},
        supportData: {
          absenceTypes: absenceTypes,
          dayTypes: dayTypes
        }
      });
    }
  });
});
