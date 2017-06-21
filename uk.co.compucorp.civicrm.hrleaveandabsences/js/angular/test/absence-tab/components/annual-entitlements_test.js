/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'common/angular',
  'common/angularMocks',
  'leave-absences/shared/config',
  'leave-absences/absence-tab/app',
  'common/mocks/services/api/contact-mock',
  'mocks/apis/absence-type-api-mock',
  'mocks/apis/absence-period-api-mock',
  'mocks/apis/entitlement-api-mock',
  'common/mocks/services/hr-settings-mock'
], function (_, moment, angular) {
  'use strict';

  describe('annualEntitlements', function () {
    var $compile, $q, $log, $rootScope, controller, Contact, ContactAPIMock,
      AbsenceType, AbsenceTypeAPIMock, AbsencePeriod, AbsencePeriodAPIMock,
      Entitlement, EntitlementAPIMock;

    beforeEach(module('leave-absences.templates', 'absence-tab',
                      'common.mocks', 'leave-absences.mocks'
    ));

    beforeEach(inject(['api.contact.mock', 'AbsenceTypeAPIMock', 'AbsencePeriodAPIMock', 'EntitlementAPIMock',
      function (_ContactAPIMock_, _AbsenceTypeAPIMock_, _AbsencePeriodAPIMock_, _EntitlementAPIMock_) {
        ContactAPIMock = _ContactAPIMock_;
        AbsenceTypeAPIMock = _AbsenceTypeAPIMock_;
        AbsencePeriodAPIMock = _AbsencePeriodAPIMock_;
        EntitlementAPIMock = _EntitlementAPIMock_;
      }]));

    beforeEach(inject(function (_$compile_, _$q_, _$log_, _$rootScope_, _Contact_, _AbsenceType_, _AbsencePeriod_, _Entitlement_) {
      $compile = _$compile_;
      $q = _$q_;
      $log = _$log_;
      $rootScope = _$rootScope_;

      Contact = _Contact_;
      AbsenceType = _AbsenceType_;
      AbsencePeriod = _AbsencePeriod_;
      Entitlement = _Entitlement_;

      spyOn($log, 'debug');
      /*
       * @TODO the way Contact model is constructed now it is not possible to use
       * the same syntax as used for other spies as the scope for Contact will be changed
       * and "this.mockedContracts" will not work
       */
      spyOn(Contact, 'all').and.callFake(function () {
        return $q.resolve(ContactAPIMock.all());
      });
      spyOn(AbsenceType, 'all').and.callFake(AbsenceTypeAPIMock.all);
      spyOn(AbsencePeriod, 'all').and.callFake(AbsencePeriodAPIMock.all);
      spyOn(Entitlement, 'all').and.callFake(EntitlementAPIMock.all);

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    it('has a contact to load for', function () {
      expect(controller.contactId).toBeDefined();
    });

    it('has absence types', function () {
      expect(controller.absenceTypes).toEqual(jasmine.any(Array));
    });

    it('has entitlements', function () {
      expect(controller.entitlements).toEqual(jasmine.any(Array));
    });

    it('has absence periods', function () {
      expect(controller.absencePeriods).toEqual(jasmine.any(Array));
    });

    it('has loaded absence periods', function () {
      expect(controller.loaded.absencePeriods).toEqual(true);
    });

    describe('absence period', function () {
      var absencePeriod, mockedAbsencePeriods;

      beforeEach(function () {
        absencePeriod = controller.absencePeriods[0];
        mockedAbsencePeriods = _.sortBy(AbsencePeriodAPIMock.all().$$state.value, function (absencePeriod) {
          return -moment(absencePeriod.start_date).valueOf();
        })[0];
      });

      it('has period', function () {
        expect(absencePeriod.period).toEqual(moment(mockedAbsencePeriods.start_date).format('YYYY'));
      });

      it('has absences', function () {
        expect(absencePeriod.absences).toEqual(jasmine.any(Array));
      });

      describe('absence', function () {
        var absence;

        beforeEach(function () {
          absence = absencePeriod.absences[0];
        });

        it('has amount', function () {
          expect(absence.amount).toEqual(jasmine.any(Number));
        });

        it('has optional comment', function () {
          expect(absence.comment).toBeDefined();
        });
      });
    });

    /**
     * Compiles the controller
     */
    function compileComponent () {
      var $scope = $rootScope.$new();
      var contactId = '202';
      var component = angular.element('<annual-entitlements contact-id="' + contactId + '"></annual-entitlements>');

      $compile(component)($scope);
      $scope.$digest();

      controller = component.controller('annualEntitlements');
    }
  });
});
