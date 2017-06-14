/* globals inject */
/* eslint-env amd, jasmine */

define([
  'mocks/data/contract-revision',
  'mocks/data/insurance-plan-types',
  'job-contract/app'
], function (ContractMock, InsuranceMock) {
  'use strict';

  describe('ContractHealthService', function () {
    var $httpBackend, promise, $rootScope, ContractHealthService;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_ContractHealthService_, _$httpBackend_, _$rootScope_) {
      ContractHealthService = _ContractHealthService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;
    }));

    beforeEach(function () {
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(ContractMock.contractRevision);
      $httpBackend.whenGET(/action=get&entity=HRJobHealth/).respond({});
      $httpBackend.whenGET(/action=getoptions&entity=HRJobHealth/).respond(InsuranceMock.insurancePlanTypes);
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
        }).then(function(result) {
          expect(result.id).toEqual("47");
          expect(result.jobcontract_revision_id).toEqual(ContractMock.contractRevision.values[0].jobcontract_revision_id);
        });
      });
    });

    describe('getOptions()', function () {
      describe('when calling the api with paramater "hrjobcontract_health_health_plan_type"', function () {
        beforeEach(function () {
          promise = ContractHealthService.getOptions("hrjobcontract_health_health_plan_type", true);
        });

        it('returns insurance plan types list', function () {
          promise.then(function (healthInsurancePlanTypes) {
            expect(healthInsurancePlanTypes[0]["value"]).toEqual(InsuranceMock.insurancePlanTypes.values[0]["value"]);
            expect(healthInsurancePlanTypes[1]["value"]).toEqual(InsuranceMock.insurancePlanTypes.values[1]["value"]);
          });
        });
      });

      describe('when calling the api with paramater "hrjobcontract_health_life_insurance_plan_type"', function () {
        beforeEach(function () {
          promise = ContractHealthService.getOptions("hrjobcontract_health_life_insurance_plan_type", true);
        });

        it('returns life insurance plan types list', function () {
          promise.then(function (lifeInsurancePlanTypes) {
            expect(lifeInsurancePlanTypes[0]["value"]).toEqual(InsuranceMock.insurancePlanTypes.values[0]["value"]);
            expect(lifeInsurancePlanTypes[1]["value"]).toEqual(InsuranceMock.insurancePlanTypes.values[1]["value"]);
          });
        });
      });

      describe('when called api with empty insurance type', function () {
        beforeEach(function () {
          promise = ContractHealthService.getOptions("", false);
        });

        it('returns empty list insurance plan types', function () {
          promise.then(function (result) {
            expect(result).toEqual(Object({}));
          });
        });
      });
    });
  });
});
