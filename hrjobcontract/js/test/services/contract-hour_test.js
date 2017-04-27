define([
  'mocks/data/contract',
  'job-contract/app'
], function(MockContract) {
  'use strict';

  describe('ContractHourService', function () {
    var $httpBackend, $rootScope, ContractHourService;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_ContractHourService_, _$httpBackend_, _$rootScope_) {
      ContractHourService = _ContractHourService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(MockContract.contractHour);
      $httpBackend.whenGET(/action=get&entity=HRJobHour/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    }));

    afterEach(function() {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('when calling getOne()', function () {
      it('makes http call', function () {
        $httpBackend.expectGET(/action=get&entity=HRJobContract/);
      });

      it("defines getOne() function", function () {
        expect(ContractHourService.getOne).toBeDefined();
      });

      it('calls getOne fuction and return expected values', function () {
        ContractHourService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result.location_standard_hours).toEqual(MockContract.contractHour.values[0].location_standard_hours);
        });
      });
    });
  });
});
