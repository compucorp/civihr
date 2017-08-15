/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/data/absence-type-data',
  'mocks/data/leave-balance-report.data',
  'mocks/apis/absence-type-api-mock',
  'mocks/apis/leave-balance-report-api-mock',
  'leave-absences/shared/models/leave-balance-report.model',
  'leave-absences/shared/components/leave-balance-tab.component'
], function (_, absenceTypeMock, reportMockData) {
  describe('LeaveBalanceReport.component', function () {
    var $componentController, $provide, $q, $rootScope, AbsenceType, ctrl, leaveBalanceReport, notificationService, Session;
    var loggedInContactId = 101;
    var defaultReportSize = 50;

    beforeEach(module('leave-absences.mocks', 'leave-absences.models', 'leave-absences.components', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _LeaveBalanceReportAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('LeaveBalanceReportAPI', _LeaveBalanceReportAPIMock_);
    }));

    beforeEach(inject(function (_$componentController_, _$q_, _$rootScope_, _AbsenceType_, _LeaveBalanceReport_, _Session_, _notificationService_) {
      $componentController = _$componentController_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      AbsenceType = _AbsenceType_;
      leaveBalanceReport = _LeaveBalanceReport_;
      notificationService = _notificationService_;
      Session = _Session_;

      spyOn(AbsenceType, 'all').and.callThrough();
      spyOn(leaveBalanceReport, 'all').and.callThrough();
      spyOn(notificationService, 'error');
      spyOn(Session, 'get').and.returnValue($q.resolve({ contact_id: loggedInContactId }));
    }));

    describe('on init', function () {
      beforeEach(function () {
        setupController();
      });

      it('sets absence types equal to an empty array', function () {
        expect(ctrl.absenceTypes).toEqual([]);
      });

      it('sets loading report to false', function () {
        expect(ctrl.loading.report).toBe(true);
      });

      it('sets report to an empty array', function () {
        expect(ctrl.report).toEqual([]);
      });

      it('sets report count to 0', function () {
        expect(ctrl.reportCount).toBe(0);
      });

      describe('absence types', function () {
        beforeEach(function () {
          setupController();
          $rootScope.$digest();
        });

        it('loads the absence types sorted by title', function () {
          expect(AbsenceType.all).toHaveBeenCalledWith({
            options: { sort: 'title ASC' }
          });
        });

        it('stores the absence types', function () {
          expect(ctrl.absenceTypes).toEqual(absenceTypeMock.all().values);
        });
      });

      describe('session', function () {
        it('sets loading report to true', function () {
          expect(ctrl.loading.report).toBe(true);
        });

        it('loads the session', function () {
          expect(Session.get).toHaveBeenCalled();
        });
      });

      describe('balance report', function () {
        beforeEach(function () {
          setupController();
        });

        it('sets loading report to true', function () {
          expect(ctrl.loading.report).toBe(true);
        });

        describe('finishing loading the balance report', function () {
          var expected, count;

          beforeEach(function () {
            var balanceReport = reportMockData.all().values;
            count = reportMockData.all().count;

            // sets the balance report in an expected manner.
            // each record's .absence_types to be an index so it can be displayed
            // on the report in a specific order.
            expected = balanceReport.map(function (record) {
              record = Object.assign({}, record);

              record.absence_types = _.indexBy(record.absence_types, function (type) {
                return type.id;
              });

              return record;
            });

            $rootScope.$digest();
          });

          it('loads the balance report for contacts managed by the user, on page 1, with a limited amount of records', function () {
            expect(leaveBalanceReport.all).toHaveBeenCalledWith(
              { managed_by: loggedInContactId },
              { page: 1, size: defaultReportSize }
            );
          });

          it('sets loading report to false', function () {
            expect(ctrl.loading.report).toBe(false);
          });

          it('stores the the total number of records', function () {
            expect(ctrl.reportCount).toBe(count);
          });

          it('stores the report', function () {
            expect(ctrl.report.length).toEqual(expected.length);
          });

          it('indexes the leave balance absence types by id', function () {
            expect(ctrl.report).toEqual(expected);
          });
        });

        describe('error loading the leave balance', function () {
          var error = {
            error_code: 'not-found',
            error_message: 'Not Found.',
            is_error: 1
          };

          beforeEach(function () {
            leaveBalanceReport.all.and.returnValue($q.reject(error));

            setupController();
            $rootScope.$digest();
          });

          it('sets loading report to false', function () {
            expect(ctrl.loading.report).toBe(false);
          });

          it('throws an error notification', function () {
            expect(notificationService.error).toHaveBeenCalledWith('Error', error.error_message);
          });
        });
      });
    });

    function setupController () {
      ctrl = $componentController('leaveBalanceTab');
    }
  });
});
