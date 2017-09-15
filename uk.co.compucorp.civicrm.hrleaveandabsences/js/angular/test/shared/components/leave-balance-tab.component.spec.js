/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/data/absence-period-data',
  'mocks/data/absence-type-data',
  'mocks/data/leave-balance-report.data',
  'mocks/apis/absence-type-api-mock',
  'mocks/apis/entitlement-api-mock',
  'leave-absences/shared/models/entitlement-model',
  'leave-absences/shared/components/leave-balance-tab.component',
  'leave-absences/shared/config'
], function (_, absencePeriodMock, absenceTypeMock, reportMockData) {
  describe('LeaveBalanceReport.component', function () {
    var $componentController, $provide, $q, $rootScope, $scope, AbsencePeriod,
      AbsenceType, ctrl, leaveBalanceReport, notificationService, Session,
      sharedSettings;
    var loggedInContactId = 101;
    var filters = { any_filter: 'any value' };
    var userRole = 'admin';

    beforeEach(module('leave-absences.mocks', 'leave-absences.models',
    'leave-absences.components', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsencePeriodAPIMock_, _AbsenceTypeAPIMock_,
    _EntitlementAPIMock_) {
      $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('EntitlementAPI', _EntitlementAPIMock_);
      $provide.value('checkPermissions', function (permission) {
        var returnValue = false;

        if (userRole === 'admin') {
          returnValue = permission === sharedSettings.permissions.admin.administer;
        }
        if (userRole === 'manager') {
          returnValue = permission === sharedSettings.permissions.ssp.manage;
        }

        return $q.resolve(returnValue);
      });
    }));

    beforeEach(inject(['shared-settings', function (_sharedSettings_) {
      sharedSettings = _sharedSettings_;
    }]));

    beforeEach(inject(function (_$componentController_, _$q_, _$rootScope_,
    _AbsencePeriod_, _AbsenceType_, _LeaveBalanceReport_, _Session_,
    _notificationService_) {
      $componentController = _$componentController_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      AbsencePeriod = _AbsencePeriod_;
      AbsenceType = _AbsenceType_;
      leaveBalanceReport = _LeaveBalanceReport_;
      notificationService = _notificationService_;
      Session = _Session_;

      spyOn(AbsencePeriod, 'all').and.callThrough();
      spyOn(AbsenceType, 'all').and.callThrough();
      spyOn(leaveBalanceReport, 'all').and.callThrough();
      spyOn(notificationService, 'error');
      spyOn(Session, 'get').and.returnValue($q.resolve({ contactId: loggedInContactId }));
    }));

    describe('on init', function () {
      beforeEach(function () {
        setupController();
      });

      it('sets absence periods to an empty array', function () {
        expect(ctrl.absencePeriods).toEqual([]);
      });

      it('sets absence types equal to an empty array', function () {
        expect(ctrl.absenceTypes).toEqual([]);
      });

      it('sets loading component to true', function () {
        expect(ctrl.loading.component).toBe(true);
      });

      it('sets loading report to true', function () {
        expect(ctrl.loading.report).toBe(true);
      });

      it('sets the logged in contact id to null', function () {
        expect(ctrl.loggedInContactId).toBe(null);
      });

      it('sets pagination page to 1', function () {
        expect(ctrl.pagination.page).toBe(1);
      });

      it('sets pagination size to 50', function () {
        expect(ctrl.pagination.size).toBe(50);
      });

      it('sets report to an empty array', function () {
        expect(ctrl.report).toEqual([]);
      });

      it('sets report count to 0', function () {
        expect(ctrl.reportCount).toBe(0);
      });

      describe('on user role load', function () {
        describe('when user is Admin', function () {
          beforeEach(function () {
            userRole = 'admin';

            setupController();
            $rootScope.$digest();
          });

          it('sets Admin role', function () {
            expect(ctrl.userRole).toBe('admin');
          });
        });

        describe('when user is Manager', function () {
          beforeEach(function () {
            userRole = 'manager';

            setupController();
            $rootScope.$digest();
          });

          it('sets Manager role', function () {
            expect(ctrl.userRole).toBe('manager');
          });
        });
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

      describe('absence periods', function () {
        beforeEach(function () {
          setupController();
          $rootScope.$digest();
        });

        it('loads the absence periods sorted by title', function () {
          expect(AbsencePeriod.all).toHaveBeenCalledWith({
            options: { sort: 'title ASC' }
          });
        });

        it('stores the absence periods', function () {
          expect(ctrl.absencePeriods.length).toEqual(absencePeriodMock.all().values.length);
        });
      });

      describe('session', function () {
        beforeEach(function () {
          $rootScope.$digest();
        });

        it('sets loading report to true', function () {
          expect(ctrl.loading.report).toBe(true);
        });

        it('loads the session', function () {
          expect(Session.get).toHaveBeenCalled();
        });

        describe('when finishing loading the session', function () {
          beforeEach(function () { $rootScope.$digest(); });

          it('stores the currently logged in contact id', function () {
            expect(ctrl.loggedInContactId).toBe(loggedInContactId);
          });
        });
      });

      describe('when finished initializing', function () {
        beforeEach(function () { $rootScope.$digest(); });

        it('stops loading the component', function () {
          expect(ctrl.loading.component).toBe(false);
        });
      });
    });

    describe('on leave balance filters updated event', function () {
      beforeEach(function () {
        setupController();
        spyOn(ctrl, 'loadReportCurrentPage');

        ctrl.pagination.page = 202;

        $rootScope.$broadcast('LeaveBalanceFilters::update', filters);
      });

      it('loads the first page of the report', function () {
        expect(ctrl.loadReportCurrentPage).toHaveBeenCalled();
        expect(ctrl.pagination.page).toBe(1);
      });
    });

    describe('loadReportCurrentPage()', function () {
      beforeEach(function () {
        setupController();
        $rootScope.$digest();
        $rootScope.$broadcast('LeaveBalanceFilters::update', filters);
      });

      it('sets loading report to true', function () {
        expect(ctrl.loading.report).toBe(true);
      });

      it('loads the balance report for contacts, on selected absence period and type, on page 1, with a limited amount of records', function () {
        expect(leaveBalanceReport.all).toHaveBeenCalledWith(filters, ctrl.pagination);
      });

      describe('finishing loading the report page', function () {
        var reportCount, expectedReport;

        beforeEach(function () {
          var balanceReport = reportMockData.all().values;
          reportCount = reportMockData.all().count;

          // sets the balance report in an expected manner.
          // each record's .absence_types to be an index so it can be displayed
          // on the report in a specific order.
          expectedReport = _.values(balanceReport).map(function (record) {
            record = Object.assign({}, record);

            record.absence_types = _.indexBy(record.absence_types, function (type) {
              return type.id;
            });

            return record;
          });

          $rootScope.$digest();
        });

        it('sets loading report to false', function () {
          expect(ctrl.loading.report).toBe(false);
        });

        it('stores the the total number of records', function () {
          expect(ctrl.reportCount).toBe(reportCount);
        });

        it('stores the report', function () {
          expect(ctrl.report.length).toEqual(expectedReport.length);
        });

        it('indexes the leave balance absence types by id', function () {
          expect(ctrl.report).toEqual(expectedReport);
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
          $rootScope.$broadcast('LeaveBalanceFilters::update', filters);
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

    /**
     * Setups the leaveBalanceTab controller for testing purposes.
     */
    function setupController () {
      $scope = $rootScope.$new();

      ctrl = $componentController('leaveBalanceTab', { $scope: $scope });
    }
  });
});
