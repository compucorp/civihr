/* eslint-env amd, jasmine */

define([
  'mocks/data/absence-type-data',
  'mocks/data/leave-balance-report-data',
  'mocks/apis/absence-type-api-mock',
  'mocks/apis/leave-balance-report-api-mock',
  'leave-absences/shared/models/leave-balance-report.model',
  'leave-absences/shared/components/leave-balance-report.component'
], function (absenceTypeMock, reportMockData) {
  describe('LeaveBalanceReport.component', function () {
    var $componentController, $provide, $q, $rootScope, AbsenceType, ctrl, leaveBalanceReport, notification, Session;
    var loggedInContactId = 101;
    var defaultReportSize = 50;

    beforeEach(module('leave-absences.mocks', 'leave-absences.models', 'leave-absences.components', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _LeaveBalanceReportAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('LeaveBalanceReportAPI', _LeaveBalanceReportAPIMock_);
    }));

    beforeEach(inject(function (_$componentController_, _$q_, _$rootScope_, _AbsenceType_, _LeaveBalanceReport_, _Session_, _notification_) {
      $componentController = _$componentController_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      AbsenceType = _AbsenceType_;
      leaveBalanceReport = _LeaveBalanceReport_;
      notification = _notification_;
      Session = _Session_;

      spyOn(AbsenceType, 'all').and.callThrough();
      spyOn(leaveBalanceReport, 'all').and.callThrough();
      spyOn(notification, 'error');
      spyOn(_Session_, 'get').and.returnValue($q.resolve({ contact_id: loggedInContactId }));
    }));

    describe('when the component is initializing', function () {
      beforeEach(function () {
        setupController();
      });

      it('sets .absenceTypes to an empty array', function () {
        expect(ctrl.absenceTypes).toEqual([]);
      });

      it('sets .loading.report to false', function () {
        expect(ctrl.loading.report).toBe(false);
      });

      it('sets .report to an empty array', function () {
        expect(ctrl.report).toEqual([]);
      });

      it('sets .reportCount to 0', function () {
        expect(ctrl.reportCount).toBe(0);
      });

      describe('when loading absence types', function () {
        beforeEach(function () {
          setupControllerAndRunOnInit();
        });

        it('calls AbsenceType.all to get a list of absence types', function () {
          expect(AbsenceType.all).toHaveBeenCalledWith({
            options: { sort: 'title ASC' }
          });
        });

        it('sets .absenceTypes to the value returned by AbsenceType.all', function () {
          expect(ctrl.absenceTypes).toEqual(absenceTypeMock.all().values);
        });
      });

      describe('when loading the session', function () {
        beforeEach(function () {
          ctrl.$onInit();
        });

        it('sets .loading.report to true', function () {
          expect(ctrl.loading.report).toBe(true);
        });

        it('calls Session.get to get contact_id value', function () {
          expect(Session.get).toHaveBeenCalled();
        });
      });

      describe('when loading the report', function () {
        beforeEach(function () {
          setupController();
          spyOn(ctrl, 'loadReportPage').and.callThrough();
          ctrl.$onInit();
          $rootScope.$digest();
        });

        // .loadReportPage() is detailed below.
        it('request page 1 of the report using .loadReportPage()', function () {
          expect(ctrl.loadReportPage).toHaveBeenCalledWith(1);
        });
      });
    });

    describe('.loadReportPage()', function () {
      beforeEach(function () {
        setupController();
      });

      describe('when loading a report`s page', function () {
        var page = 10;

        beforeEach(function () {
          setupControllerAndRunOnInit();
          ctrl.loadReportPage(page);
        });

        it('sets .loading.report to true', function () {
          expect(ctrl.loading.report).toBe(true);
        });

        it('calls leaveBalanceReport.all with the page parameters', function () {
          expect(leaveBalanceReport.all).toHaveBeenCalledWith(
            { managed_by: loggedInContactId },
            { page: page, size: defaultReportSize }
          );
        });

        describe('when finishing loading a report`s page', function () {
          var expected, count;

          beforeEach(function () {
            var absenceTypes = absenceTypeMock.all().values;
            var balanceReport = reportMockData.all().values;
            count = reportMockData.all().count;

            // reversing absence_types to see if component sorts them in the
            // proper order.
            balanceReport.forEach(function (r) {
              r.absence_types.reverse();
            });

            // sets the balance report in an expected manner.
            // each record`s .absence_types should be sorted in the same order
            // as the component`s .absenceTypes array.
            expected = balanceReport.map(function (record) {
              record = Object.assign({}, record);

              record.absence_types = absenceTypes.map(function (sortedType) {
                return record.absence_types.find(function (unsortedType) {
                  return unsortedType && parseInt(unsortedType.id) === parseInt(sortedType.id);
                });
              });

              return record;
            });

            ctrl.loadReportPage(page);
            $rootScope.$digest();
          });

          it('sets .loading.report to false', function () {
            expect(ctrl.loading.report).toBe(false);
          });

          it('sets .reportCount to the total number of records', function () {
            expect(ctrl.reportCount).toBe(count);
          });

          it('sets .report to the response of AbsenceType.all call', function () {
            expect(ctrl.report.length).toEqual(expected.length);
          });

          it('sorts .report[].absence_types in the same order as .absenceTypes', function () {
            expect(ctrl.report).toEqual(expected);
          });
        });

        describe('when there was an error loading the report`s page', function () {
          beforeEach(function () {
            leaveBalanceReport.all.and.returnValue($q.reject({
              error_code: 'not-found',
              error_message: 'Not Found.',
              is_error: 1
            }));

            ctrl.loadReportPage(page);
            $rootScope.$digest();
          });

          it('sets .loading.report to false', function () {
            expect(ctrl.loading.report).toBe(false);
          });

          it('throws an error notification', function () {
            expect(notification.error).toHaveBeenCalledWith(jasmine.any(String), jasmine.any(String));
          });
        });
      });
    });

    function setupController () {
      ctrl = $componentController('leaveBalanceReport');
    }

    function setupControllerAndRunOnInit () {
      setupController();
      ctrl.$onInit();
      $rootScope.$digest();
    }
  });
});
