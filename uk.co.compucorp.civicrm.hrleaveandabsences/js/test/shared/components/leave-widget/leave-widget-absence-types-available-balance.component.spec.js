/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/mocks/helpers/controller-on-changes',
  'leave-absences/mocks/apis/absence-period-api-mock',
  'leave-absences/mocks/apis/absence-type-api-mock',
  'leave-absences/mocks/apis/entitlement-api-mock',
  'leave-absences/shared/components/leave-widget/leave-widget.component'
], function (_, controllerOnChanges) {
  describe('leaveWidgetAbsenceTypesAvailableBalance', function () {
    var $componentController, $provide, $rootScope, $scope,
      absencePeriod, absenceTypes, ctrl, Entitlement;
    var childComponentName = 'leave-widget-absence-types-available-balance';
    var contactId = 101;

    beforeEach(module('leave-absences.components.leave-widget',
      'leave-absences.mocks', function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_AbsencePeriodAPIMock_, _AbsenceTypeAPIMock_, _EntitlementAPIMock_) {
      $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('EntitlementAPI', _EntitlementAPIMock_);
    }));

    beforeEach(inject(['$componentController', '$rootScope', 'AbsencePeriod', 'AbsenceType', 'Entitlement',
      function (_$componentController_, _$rootScope_, AbsencePeriod, AbsenceType, _Entitlement_) {
        $componentController = _$componentController_;
        $rootScope = _$rootScope_;
        Entitlement = _Entitlement_;

        AbsencePeriod.all().then(function (periods) {
          absencePeriod = periods[0];
        });
        AbsenceType.all().then(function (types) {
          absenceTypes = types;
        });
        $rootScope.$digest();
        $scope = $rootScope.$new();
        spyOn($scope, '$emit').and.callThrough();
        spyOn(Entitlement, 'all').and.callThrough();
      }]));

    beforeEach(function () {
      ctrl = $componentController('leaveWidgetAbsenceTypesAvailableBalance', {
        $scope: $scope
      });
      controllerOnChanges.setupController(ctrl);
      ctrl.$onInit();
    });

    it('should be defined', function () {
      expect(ctrl).toBeDefined();
    });

    describe('on init', function () {
      it('fires a leave widget child is loading event', function () {
        expect($scope.$emit).toHaveBeenCalledWith(
          'LeaveWidget::childIsLoading', childComponentName);
      });
    });

    describe('bindings', function () {
      describe('when absence types, absence period, and contact id are passed', function () {
        beforeEach(function () {
          controllerOnChanges.mockChange('absenceTypes', absenceTypes);
          controllerOnChanges.mockChange('absencePeriod', absencePeriod);
          controllerOnChanges.mockChange('contactId', contactId);
        });

        it('gets all entitlements for the contact in the absence period', function () {
          expect(Entitlement.all).toHaveBeenCalledWith({
            'contact_id': contactId,
            'period_id': absencePeriod.id,
            'type_id.is_active': true
          }, true);
        });

        describe('after getting all the entitlements', function () {
          var expectedEntitlements;

          beforeEach(function () {
            Entitlement.all({ contact_id: contactId, period_id: absencePeriod.id }, true)
              .then(function (entitlements) {
                var indexedEntitlements = _.keyBy(entitlements, 'type_id');

                expectedEntitlements = absenceTypes
                  .map(function (absenceType) {
                    var entitlement = indexedEntitlements[absenceType.id];

                    return _.assign({
                      entitlement: entitlement
                    }, absenceType);
                  })
                  .filter(function (absenceType) {
                    var hasEntitlement = absenceType.entitlement && absenceType.entitlement.value > 0;
                    var allowOveruse = absenceType.allow_overuse === '1';
                    var allowAccrual = absenceType.allow_accruals_request === '1';

                    return hasEntitlement || allowOveruse || allowAccrual;
                  });
              });

            $rootScope.$digest();
          });

          it('stores the absence types the user has entitlements for', function () {
            expect(ctrl.absenceTypes).toEqual(expectedEntitlements);
          });

          it('fires a leave widget child is ready event', function () {
            expect($scope.$emit).toHaveBeenCalledWith(
              'LeaveWidget::childIsReady', childComponentName);
          });
        });
      });
    });
  });
});
