/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/helpers/controller-on-changes',
  'mocks/apis/entitlement-api-mock',
  'common/mocks/services/api/contract-mock',
  'leave-absences/shared/components/leave-widget/leave-widget-absence-types-available-balance.component'
], function (_, controllerOnChanges) {
  describe('leaveWidgetAbsenceTypesAvailableBalance', function () {
    var $componentController, $provide, $rootScope, $scope,
      absencePeriod, absenceTypes, ctrl, Entitlement, jobContract;
    var childComponentName = 'leave-widget-absence-types-available-balance';
    var contactId = 101;

    beforeEach(module('leave-absences.components.leave-widget',
      'leave-absences.mocks', function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_AbsencePeriodAPIMock_, _AbsenceTypeAPIMock_,
    _EntitlementAPIMock_) {
      $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('EntitlementAPI', _EntitlementAPIMock_);
    }));

    beforeEach(inject(['$componentController', '$rootScope',
      'AbsencePeriod', 'AbsenceType', 'api.contract.mock', 'Entitlement',
      function (_$componentController_, _$rootScope_, AbsencePeriod,
      AbsenceType, Contract, _Entitlement_) {
        $componentController = _$componentController_;
        $rootScope = _$rootScope_;
        Entitlement = _Entitlement_;

        AbsencePeriod.all().then(function (periods) {
          absencePeriod = periods[0];
        });
        AbsenceType.all().then(function (types) {
          absenceTypes = types;
        });
        Contract.all().then(function (contracts) {
          jobContract = contracts[0];
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
          controllerOnChanges.mockChange('jobContract', jobContract);
        });

        it('gets all entitlements for the contact in the absence period', function () {
          expect(Entitlement.all).toHaveBeenCalledWith({
            contact_id: contactId,
            period_id: absencePeriod.id,
            type_id: { IN: getJobContractAbsenceEntitlements() }
          }, true);
        });

        describe('after getting all the entitlements', function () {
          var expectedEntitlements;

          beforeEach(function () {
            Entitlement.all({
              contact_id: contactId,
              period_id: absencePeriod.id,
              type_id: { IN: getJobContractAbsenceEntitlements() }
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

                  absenceType = _.assign({
                    balance: jasmine.any(Number)
                  }, absenceType);

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
              'LeaveWidget::childIsReady', childComponentName);
          });
        });
      });
    });

    /**
     * Returns a list of IDs of the absence types the contact has entitlements
     * for.
     *
     * @return {Array}
     */
    function getJobContractAbsenceEntitlements () {
      return jobContract.info.leave.map(function (leave) {
        return leave.leave_type;
      });
    }
  });
});
