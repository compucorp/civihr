/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/shared/components/leave-widget/leave-widget-balance.component',
  'mocks/apis/entitlement-api-mock'
], function (_) {
  describe('LeaveWidgetBalance', function () {
    var $componentController, $provide, $rootScope, $scope,
      currentAbsencePeriod, absenceTypes, ctrl, Entitlement;
    var contactId = 101;

    beforeEach(module('leave-absences.components', 'leave-absences.mocks',
    function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsencePeriodAPIMock_, _AbsenceTypeAPIMock_,
    _EntitlementAPIMock_) {
      $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('EntitlementAPI', _EntitlementAPIMock_);
    }));

    beforeEach(inject(function (_$componentController_, _$rootScope_,
    AbsencePeriod, AbsenceType, _Entitlement_) {
      $componentController = _$componentController_;
      $rootScope = _$rootScope_;
      Entitlement = _Entitlement_;

      AbsencePeriod.all().then(function (periods) {
        currentAbsencePeriod = periods[0];
        currentAbsencePeriod.current = true;
      });
      AbsenceType.all().then(function (types) {
        absenceTypes = types;
      });
      $rootScope.$digest();
      $scope = $rootScope.$new();
      spyOn($scope, '$emit').and.callThrough();
      spyOn(Entitlement, 'all').and.callThrough();
    }));

    beforeEach(function () {
      ctrl = $componentController('leaveWidgetBalance', {
        $scope: $scope
      });
    });

    it('should be defined', function () {
      expect(ctrl).toBeDefined();
    });

    describe('on init', function () {
      it('fires a leave widget child is loading event', function () {
        expect($scope.$emit).toHaveBeenCalledWith(
          'LeaveWidget::childIsLoading');
      });
    });

    describe('bindings', function () {
      describe('when absence types, current absence period, and contact id are binded', function () {
        beforeEach(function () {
          controllerOnChanges('absenceTypes', absenceTypes);
          controllerOnChanges('currentAbsencePeriod', currentAbsencePeriod);
          controllerOnChanges('contactId', contactId);
        });

        it('gets all entitlements for the contact in the current absence period', function () {
          expect(Entitlement.all).toHaveBeenCalledWith({
            contact_id: contactId,
            period_id: currentAbsencePeriod.id
          }, true);
        });

        describe('after getting all the entitlements', function () {
          var expectedEntitlements;

          beforeEach(function () {
            Entitlement.all({
              contact_id: contactId,
              period_id: currentAbsencePeriod.id
            }, true)
            .then(function (entitlements) {
              expectedEntitlements = entitlements
                .filter(function (entitlement) {
                  return entitlement.value > 0;
                })
                .map(function (entitlement) {
                  var absenceType = _.find(absenceTypes, function (type) {
                    return +entitlement.type_id === +type.id;
                  }) || {};

                  absenceType.balance = jasmine.any(Number);
                  return absenceType;
                });
            });

            $rootScope.$digest();
          });

          it('stores the absence types the user has entitlements for', function () {
            expect(ctrl.absenceTypeEntitlements).toEqual(expectedEntitlements);
          });

          it('fires a leave widget child is ready event', function () {
            expect($scope.$emit).toHaveBeenCalledWith(
              'LeaveWidget::childIsReady');
          });
        });
      });
    });

    function controllerOnChanges (bindingName, bindingValue) {
      var changes = {};
      ctrl[bindingName] = bindingValue;
      changes[bindingName] = {
        currentValue: bindingValue,
        previousValue: undefined
      };

      ctrl.$onChanges(changes);
    }
  });
});
