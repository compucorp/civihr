/* globals inject */
/* eslint-env amd, jasmine */

define([
  'mocks/data/contract-revision',
  'mocks/data/insurance-plan-types',
  'job-contract/app'
], function (ContractMock, InsuranceMock) {
  'use strict';

  describe('ContractHealthService', function () {
    var $httpBackend, $rootScope, promise, ContractHealthService;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_ContractHealthService_, _$httpBackend_,
    _$rootScope_) {
      ContractHealthService = _ContractHealthService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;
    }));

    beforeEach(function () {
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenGET(/action=get&entity=HRJobHealth/).respond(ContractMock.contractRevision);
      $httpBackend.whenGET(/action=getoptions&entity=HRJobHealth/).respond(InsuranceMock);
      $httpBackend.whenGET(/views.*/).respond({});
    });

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('getOne()', function () {
      it('returns job contract revision id', function () {
        ContractHealthService.getOne({
          jobcontract_revision_id: 68
        }).then(function (result) {
          expect(result).toEqual(ContractMock.contractRevision.values[0]);
        });
      });
    });

    describe('getOptions()', function () {
      describe('when calling the api with paramater "hrjobcontract_health_health_plan_type"', function () {
        beforeEach(function () {
          promise = ContractHealthService.getOptions('hrjobcontract_health_health_plan_type', true);
        });

        it('returns insurance plan types list', function () {
          promise.then(function (healthInsurancePlanTypes) {
            expect(healthInsurancePlanTypes).toEqual(InsuranceMock.values);
          });
        });
      });

      describe('when calling the api with paramater "hrjobcontract_health_life_insurance_plan_type"', function () {
        beforeEach(function () {
          promise = ContractHealthService.getOptions('hrjobcontract_health_life_insurance_plan_type', true);
        });

        it('returns life insurance plan types list', function () {
          promise.then(function (lifeInsurancePlanTypes) {
            expect(lifeInsurancePlanTypes).toEqual(InsuranceMock.values);
          });
        });
      });

      describe('when called api with empty insurance type', function () {
        beforeEach(function () {
          promise = ContractHealthService.getOptions('', false);
        });

        it('returns empty list insurance plan types', function () {
          promise.then(function (result) {
            expect(result).toEqual({});
          });
        });
      });
    });
  });
});
