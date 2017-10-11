/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/shared/components/leave-widget/leave-widget.component',
  'common/mocks/models/instances/session-mock',
  'mocks/apis/absence-period-api-mock',
  'mocks/apis/absence-type-api-mock'
], function (_) {
  describe('LeaveWidget', function () {
    var $componentController, $provide, $rootScope, AbsencePeriod, AbsenceType,
      ctrl, Session;
    var loggedInContactId = 101;

    beforeEach(module('common.mocks', 'leave-absences.components',
    'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_$rootScope_, _AbsencePeriodAPIMock_,
    _AbsenceTypeAPIMock_, _SessionMock_) {
      $rootScope = _$rootScope_;
      Session = _SessionMock_;

      $provide.value('Session', Session);
      $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
    }));

    beforeEach(inject(function (_$componentController_, $q, _AbsencePeriod_,
    _AbsenceType_, Session) {
      $componentController = _$componentController_;
      AbsencePeriod = _AbsencePeriod_;
      AbsenceType = _AbsenceType_;

      spyOn(Session, 'get').and.returnValue($q.resolve({
        contactId: loggedInContactId }));
      spyOn(AbsencePeriod, 'all').and.callThrough();
      spyOn(AbsenceType, 'all').and.callThrough();
    }));

    beforeEach(function () {
      ctrl = $componentController('leaveWidget');
    });

    it('should be defined', function () {
      expect(ctrl).toBeDefined();
    });

    describe('on init', function () {
      it('sets loading component to true', function () {
        expect(ctrl.loading.component).toBe(true);
      });

      it('sets absence types equal to an empty array', function () {
        expect(ctrl.absenceTypes).toEqual([]);
      });

      it('sets current absence period to null', function () {
        expect(ctrl.currentAbsencePeriod).toBe(null);
      });

      it('sets the logged in contact id to null', function () {
        expect(ctrl.loggedInContactId).toBe(null);
      });

      describe('absence types', function () {
        beforeEach(function () { $rootScope.$digest(); });

        it('loads all absence types', function () {
          expect(AbsenceType.all).toHaveBeenCalled();
        });

        describe('after loading all absence types', function () {
          var expectedTypes;

          beforeEach(function () {
            AbsenceType.all().then(function (types) {
              expectedTypes = types;
            });
            $rootScope.$digest();
          });

          it('stores all absence types', function () {
            expect(ctrl.absenceTypes).toEqual(expectedTypes);
          });
        });
      });

      describe('current absence period', function () {
        beforeEach(function () { $rootScope.$digest(); });

        it('loads the absence periods', function () {
          expect(AbsencePeriod.all).toHaveBeenCalled();
        });

        describe('after loading all the absence periods', function () {
          var expectedPeriod;

          beforeEach(function () {
            AbsencePeriod.all().then(function (periods) {
              expectedPeriod = _.find(periods, function (period) {
                return period.current;
              });
            });

            $rootScope.$digest();
          });

          it('stores the current one', function () {
            expect(ctrl.currentAbsencePeriod).toEqual(expectedPeriod);
          });
        });
      });

      describe('session', function () {
        beforeEach(function () { $rootScope.$digest(); });

        it('loads the session', function () {
          expect(Session.get).toHaveBeenCalled();
        });

        describe('when finishing loading the session', function () {
          it('stores the currently logged in contact id', function () {
            expect(ctrl.loggedInContactId).toBe(loggedInContactId);
          });
        });
      });

      describe('after init', function () {
        beforeEach(function () { $rootScope.$digest(); });

        it('sets loading component to false', function () {
          expect(ctrl.loading.component).toBe(false);
        });
      });
    });
  });
});
