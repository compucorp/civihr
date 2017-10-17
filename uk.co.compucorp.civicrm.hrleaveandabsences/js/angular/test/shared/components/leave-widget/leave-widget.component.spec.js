/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/shared/components/leave-widget/leave-widget.component',
  'mocks/apis/absence-period-api-mock',
  'mocks/apis/absence-type-api-mock'
], function (_) {
  describe('LeaveWidget', function () {
    var $componentController, $provide, $rootScope, $scope, AbsencePeriod,
      AbsenceType, ctrl;

    beforeEach(module('common.mocks', 'leave-absences.components.leave-widget',
    'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsencePeriodAPIMock_,
    _AbsenceTypeAPIMock_) {
      $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
    }));

    beforeEach(inject(function (_$componentController_, $q, _$rootScope_,
    _AbsencePeriod_, _AbsenceType_) {
      $componentController = _$componentController_;
      $rootScope = _$rootScope_;
      $scope = $rootScope.$new();
      AbsencePeriod = _AbsencePeriod_;
      AbsenceType = _AbsenceType_;

      spyOn($scope, '$on').and.callThrough();
      spyOn(AbsencePeriod, 'all').and.callThrough();
      spyOn(AbsenceType, 'all').and.callThrough();
    }));

    beforeEach(function () {
      ctrl = $componentController('leaveWidget', {
        $scope: $scope
      });
    });

    it('should be defined', function () {
      expect(ctrl).toBeDefined();
    });

    describe('on init', function () {
      it('sets loading child components to false', function () {
        expect(ctrl.loading.childComponents).toBe(false);
      });

      it('sets loading component to true', function () {
        expect(ctrl.loading.component).toBe(true);
      });

      it('sets absence types equal to an empty array', function () {
        expect(ctrl.absenceTypes).toEqual([]);
      });

      it('sets current absence period to null', function () {
        expect(ctrl.currentAbsencePeriod).toBe(null);
      });

      it('watches for child components loading and ready events', function () {
        expect($scope.$on).toHaveBeenCalledWith(
          'LeaveWidget::childIsLoading', jasmine.any(Function));
        expect($scope.$on).toHaveBeenCalledWith(
          'LeaveWidget::childIsReady', jasmine.any(Function));
      });

      describe('child components', function () {
        describe('when child components are loading', function () {
          beforeEach(function () {
            $rootScope.$broadcast('LeaveWidget::childIsLoading');
            $rootScope.$broadcast('LeaveWidget::childIsLoading');
            $rootScope.$broadcast('LeaveWidget::childIsLoading');
          });

          it('sets loading child components to true', function () {
            expect(ctrl.loading.childComponents).toBe(true);
          });

          describe('when a few child components are ready', function () {
            beforeEach(function () {
              $rootScope.$broadcast('LeaveWidget::childIsReady');
              $rootScope.$broadcast('LeaveWidget::childIsReady');
            });

            it('keeps loading child components set to true', function () {
              expect(ctrl.loading.childComponents).toBe(true);
            });

            describe('when all child components are ready', function () {
              beforeEach(function () {
                $rootScope.$broadcast('LeaveWidget::childIsReady');
              });

              it('sets loading child components to false', function () {
                expect(ctrl.loading.childComponents).toBe(false);
              });
            });
          });
        });
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

      describe('after init', function () {
        beforeEach(function () { $rootScope.$digest(); });

        it('sets loading component to false', function () {
          expect(ctrl.loading.component).toBe(false);
        });
      });
    });
  });
});
