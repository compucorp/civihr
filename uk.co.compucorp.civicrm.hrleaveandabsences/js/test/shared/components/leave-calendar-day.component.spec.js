/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/mocks/data/absence-type.data',
  'leave-absences/mocks/data/leave-request.data',
  'leave-absences/mocks/data/option-group.data',
  'leave-absences/shared/components/leave-calendar-day.component',
  'leave-absences/manager-leave/app'
], function (_, moment, absenceTypeData, leaveRequestData, optionGroupData) {
  'use strict';

  describe('leaveCalendarDay', function () {
    var $componentController, $log, $rootScope, absenceType, absenceTypes,
      calculationUnits, calculationUnitInDays, calculationUnitInHours,
      contactData, controller, dayTypes, LeavePopup, leaveRequest, leaveRequestAttributes;

    beforeEach(module('manager-leave'));
    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_, _LeavePopup_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      absenceTypes = _.cloneDeep(absenceTypeData.all().values);
      contactData = {};
      leaveRequest = _.cloneDeep(leaveRequestData.all().values[0]);
      contactData.leaveRequests = [leaveRequest];
      contactData.leaveRequestsToShowInCell = [leaveRequest];
      contactData.leaveRequestsAttributes = {};
      contactData.leaveRequestsAttributes[leaveRequest.id] = {};
      leaveRequestAttributes = contactData.leaveRequestsAttributes[leaveRequest.id];
      calculationUnits = optionGroupData.getCollection(
        'hrleaveandabsences_absence_type_calculation_unit');
      dayTypes = optionGroupData.getCollection(
        'hrleaveandabsences_leave_request_day_type');
      LeavePopup = _LeavePopup_;
      leaveRequest = _.cloneDeep(leaveRequestData.all().values[0]);
      absenceType = _.find(absenceTypes, function (type) {
        return +type.id === +leaveRequest.type_id;
      });
      calculationUnitInDays = optionGroupData.specificObject(
        'hrleaveandabsences_absence_type_calculation_unit', 'name',
        'days').value;
      calculationUnitInHours = optionGroupData.specificObject(
        'hrleaveandabsences_absence_type_calculation_unit', 'name',
        'hours').value;

      absenceTypes.push({ id: '', title: 'Leave' });
      spyOn($log, 'debug');
      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('mapping leave request fields', function () {
      beforeEach(function () {
        compileComponent();

        absenceType = _.find(absenceTypes, function (type) {
          return +type.id === +leaveRequest.type_id;
        });
      });

      describe('basic tests', function () {
        beforeEach(function () {
          $rootScope.$digest();
        });

        it('sets the absence type title', function () {
          expect(leaveRequestAttributes['absenceTypeTitle']).toEqual(absenceType.title);
        });

        it('sets dates ready for formatting', function () {
          expect(leaveRequestAttributes.from_date).toEqual(new Date(leaveRequest.from_date));
          expect(leaveRequestAttributes.to_date).toEqual(new Date(leaveRequest.to_date));
        });

        it('sets the calculation unit', function () {
          expect(leaveRequestAttributes.unit).toEqual(jasmine.any(String));
        });
      });

      describe('when calculation unit is "days"', function () {
        var fromDateType, toDateType;

        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInDays;
          fromDateType = optionGroupData.specificObject(
            'hrleaveandabsences_leave_request_day_type', 'value',
            leaveRequest.from_date_type);
          toDateType = optionGroupData.specificObject(
            'hrleaveandabsences_leave_request_day_type', 'value',
            leaveRequest.from_date_type);

          $rootScope.$digest();
        });

        it('sets the "days" calculation unit', function () {
          expect(leaveRequestAttributes.unit).toEqual('days');
        });

        it('maps the from date type label', function () {
          expect(leaveRequestAttributes.from_date_type).toEqual(fromDateType.label);
        });

        it('maps the to date type label', function () {
          expect(leaveRequestAttributes.from_date_type).toEqual(toDateType.label);
        });
      });

      describe('when calculation unit is "hours"', function () {
        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInHours;

          $rootScope.$digest();
        });

        it('sets the "hours" calculation unit', function () {
          expect(leaveRequestAttributes.unit).toEqual('hours');
        });

        it('does not map neither "from" nor "to" date type label', function () {
          expect(leaveRequestAttributes.from_date_type).not.toBeDefined();
          expect(leaveRequestAttributes.to_date_type).not.toBeDefined();
        });
      });
    });

    /**
     * @NOTE this block tests an adhoc solution.
     * @see /shared/components/leave-calendar-day.component.js
     * resolveLeaveRequestCalculationUnit()
     * @see PCHR-3774
     */
    describe('when absence type is a generic leave type', function () {
      beforeEach(function () {
        leaveRequest.type_id = '';
      });

      describe('when leave request "from_date_type" is *not* empty', function () {
        beforeEach(function () {
          leaveRequest.from_date_type = '1';
          contactData.leaveRequests = [leaveRequest];

          compileComponent();
          $rootScope.$digest();
        });

        it('sets the "days" calculation unit', function () {
          expect(leaveRequestAttributes.unit).toEqual('days');
        });
      });

      describe('when leave request "from_date_type" is empty', function () {
        beforeEach(function () {
          delete leaveRequest.from_date_type;
          contactData.leaveRequests = [leaveRequest];

          compileComponent();
          $rootScope.$digest();
        });

        it('sets the "hours" calculation unit', function () {
          expect(leaveRequestAttributes.unit).toEqual('hours');
        });
      });
    });

    describe('Resolving the day\'s label', function () {
      var absenceType;

      beforeEach(function () {
        contactData.leaveRequest = leaveRequest;
        absenceType = _.find(absenceTypes, function (type) {
          return +type.id === +leaveRequest.type_id;
        });
      });

      describe('Accrued TOIL', function () {
        beforeEach(function () {
          leaveRequestAttributes.isAccruedTOIL = true;

          $rootScope.$digest();
        });

        it('sets day label equal to AT', function () {
          expect(leaveRequestAttributes.label).toBe('AT');
        });
      });

      describe('half day AM', function () {
        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInDays;
          leaveRequestAttributes.isAM = true;
          $rootScope.$digest();
        });

        it('sets day label equal to AM', function () {
          expect(leaveRequestAttributes.label).toBe('AM');
        });
      });

      describe('half day PM', function () {
        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInDays;
          leaveRequestAttributes.isPM = true;

          $rootScope.$digest();
        });

        it('sets day label equal to PM', function () {
          expect(leaveRequestAttributes.label).toBe('PM');
        });
      });

      describe('full day', function () {
        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInDays;

          $rootScope.$digest();
        });

        it('sets day label equal to empty string', function () {
          expect(leaveRequestAttributes.label).toBe('');
        });
      });

      describe('start date of hours request', function () {
        var time;

        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInHours;
          controller.date = leaveRequest.from_date;
          time = moment(leaveRequest.from_date).format('HH:mm');

          $rootScope.$digest();
        });

        it('sets day label equal the start time of the request', function () {
          expect(leaveRequestAttributes.label).toBe(time);
        });
      });

      describe('end date of hours request', function () {
        var time;

        beforeEach(function () {
          absenceType.calculation_unit = calculationUnitInHours;
          controller.date = leaveRequest.to_date;
          time = moment(leaveRequest.to_date).format('HH:mm');

          $rootScope.$digest();
        });

        it('sets day label equal the end time of the request', function () {
          expect(leaveRequestAttributes.label).toBe(time);
        });
      });

      describe('between dates of hours request', function () {
        beforeEach(function () {
          var dateFormat = 'YYYY-MM-DD HH:mm';
          var startDate = moment(leaveRequest.from_date).startOf('day');

          absenceType.calculation_unit = calculationUnitInHours;
          controller.date = startDate.add(1, 'days').format(dateFormat);
          controller.contactData.leaveRequests[0].to_date = startDate.add(2, 'days').format(dateFormat);

          $rootScope.$digest();
        });

        it('sets day label equal to empty string', function () {
          expect(leaveRequestAttributes.label).toBe('');
        });
      });
    });

    describe('openLeavePopup()', function () {
      var event;
      var leaveRequest = { id: _.uniqueId() };

      beforeEach(function () {
        event = jasmine.createSpyObj('event', ['stopPropagation']);
        spyOn(LeavePopup, 'openModalByID');
        controller.openLeavePopup(event, leaveRequest);
      });

      it('opens the leave request popup', function () {
        expect(LeavePopup.openModalByID).toHaveBeenCalledWith(leaveRequest.id);
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
        contactData: contactData,
        date: moment().format('YYYY-MM-DD'),
        supportData: {
          absenceTypes: absenceTypes,
          dayTypes: dayTypes,
          calculationUnits: calculationUnits
        }
      });
      controller.$onInit();
    }
  });
});
