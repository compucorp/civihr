/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'mocks/helpers/controller-on-changes',
  'mocks/apis/leave-request-api-mock',
  'leave-absences/shared/components/leave-widget/leave-widget-absence-types-amount-taken.component'
], function (_, moment, controllerOnChanges) {
  describe('leaveWidgetHeatmap', function () {
    var $componentController, $provide, $rootScope, $scope, ctrl,
      absenceTypes, absencePeriod, OptionGroup, LeaveRequest, statusIds;
    var contactId = 101;
    var allowedLeaveStatuses = ['approved', 'admin_approved',
      'awaiting_approval', 'more_information_required'];

    beforeEach(module('leave-absences.components.leave-widget',
      'leave-absences.mocks', function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock,
    LeaveRequestAPIMock, OptionGroupAPIMock) {
      $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
      $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
      $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
      $provide.value('OptionGroup', OptionGroupAPIMock);
    }));

    beforeEach(inject(function (_$componentController_, _$rootScope_,
    AbsencePeriod, AbsenceType, _OptionGroup_, _LeaveRequest_) {
      $componentController = _$componentController_;
      $rootScope = _$rootScope_;
      OptionGroup = _OptionGroup_;
      LeaveRequest = _LeaveRequest_;

      AbsencePeriod.all().then(function (periods) {
        absencePeriod = periods[0];
      });
      AbsenceType.all().then(function (_absenceTypes_) {
        absenceTypes = _absenceTypes_;
      });
      OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
        .then(function (statuses) {
          statusIds = statuses.filter(function (status) {
            return _.includes(allowedLeaveStatuses, status.name);
          })
          .map(function (status) {
            return status.value;
          });
        });
      $rootScope.$digest();
      $scope = $rootScope.$new();
      spyOn($scope, '$emit').and.callThrough();
      spyOn(OptionGroup, 'valuesOf').and.callThrough();
      spyOn(LeaveRequest, 'all').and.callThrough();
    }));

    beforeEach(function () {
      ctrl = $componentController('leaveWidgetAbsenceTypesAmountTaken', {
        $scope: $scope
      });
      controllerOnChanges.setupController(ctrl);
    });

    it('should be defined', function () {
      expect(ctrl).toBeDefined();
    });

    describe('on init', function () {
      it('sets week heat map equal to an empty object', function () {
        expect(ctrl.heatmapValues).toEqual({});
      });

      it('fires a leave widget child is loading event', function () {
        expect($scope.$emit).toHaveBeenCalledWith(
          'LeaveWidget::childIsLoading');
      });
    });

    describe('bindings and dependencies', function () {
      describe('when contact id and absence period are bound', function () {
        var absenceTypeIds;

        beforeEach(function () {
          absenceTypeIds = absenceTypes.map(function (absenceType) {
            return absenceType.id;
          });
          controllerOnChanges.mockChange('absenceTypes', absenceTypes);
          controllerOnChanges.mockChange('contactId', contactId);
          controllerOnChanges.mockChange('absencePeriod', absencePeriod);
          $rootScope.$digest();
        });

        it('loads the leave requests statuses', function () {
          expect(OptionGroup.valuesOf)
            .toHaveBeenCalledWith('hrleaveandabsences_leave_request_status');
        });

        it('gets leave requests of the specified absence types', function () {
          expect(LeaveRequest.all).toHaveBeenCalledWith({
            contact_id: contactId,
            from_date: { '>=': absencePeriod.start_date },
            to_date: { '<=': absencePeriod.end_date },
            status_id: { IN: statusIds },
            type_id: { IN: absenceTypeIds }
          });
        });

        describe('after loading dependencies', function () {
          var expectedHeatMap = {};
          var expectedAbsenceTypes = [];

          beforeEach(function () {
            LeaveRequest.all({
              contact_id: contactId,
              from_date: { '>=': absencePeriod.start_date },
              to_date: { '<=': absencePeriod.end_date },
              status_id: { IN: statusIds },
              type_id: { IN: absenceTypeIds }
            })
            .then(function (response) {
              var requests = response.list;

              mapHeatMapValues(requests);
              mapAbsenceTypeBalances(requests);
            });
            $rootScope.$digest();

            /**
             * Finds and stores the balance for each absence type.
             *
             * @param {LeaveRequestInstance[]} requests - an array of leave requests.
             */
            function mapAbsenceTypeBalances (requests) {
              expectedAbsenceTypes = absenceTypes.map(function (absenceType) {
                var balance = requests.filter(function (request) {
                  return +request.type_id === +absenceType.id;
                })
                .reduce(function (balance, request) {
                  return balance + request.balance_change;
                }, 0);
                balance = Math.abs(balance);

                return _.assign({ balance: balance }, absenceType);
              });
            }

            /**
             * Stores the total leave balance for each day of the week.
             *
             * @param {LeaveRequestInstance[]} requests - an array of leave requests.
             */
            function mapHeatMapValues (requests) {
              requests.reduce(function (dates, request) {
                return dates.concat(request.dates);
              }, [])
              .forEach(function (date) {
                var dayOfTheWeek = moment(date.date).isoWeekday() - 1;

                if (!expectedHeatMap[dayOfTheWeek]) {
                  expectedHeatMap[dayOfTheWeek] = 0;
                }

                expectedHeatMap[dayOfTheWeek]++;
              });
            }
          });

          it('maps the leave requests days to the week heat map object', function () {
            expect(ctrl.heatmapValues).toEqual(expectedHeatMap);
          });

          it('maps the total balance for each absence type', function () {
            expect(ctrl.absenceTypes).toEqual(expectedAbsenceTypes);
          });

          it('fires a leave widget child is ready event', function () {
            expect($scope.$emit).toHaveBeenCalledWith(
              'LeaveWidget::childIsReady');
          });
        });
      });
    });
  });
});
