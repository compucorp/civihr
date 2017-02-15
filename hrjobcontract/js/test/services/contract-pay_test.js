define([
  'common/angularMocks',
  'job-contract/app'
], function(Mock) {
  'use strict';

  describe('ContractPayService', function () {
    var $httpBackend, responseData, $rootScope, ContractPayService;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_ContractPayService_, _$httpBackend_, _$rootScope_) {
      ContractPayService = _ContractPayService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      responseData = {
        "is_error": 0,
        "undefined_fields": [
          "jobcontract_revision_id"
        ],
        "version": 3,
        "count": 1,
        "id": 54,
        "values": [
          {
            "id": "54",
            "pay_scale": "4",
            "is_paid": "1",
            "pay_amount": "22000.00",
            "pay_unit": "Year",
            "pay_currency": "GBP",
            "pay_annualized_est": "22000.00",
            "pay_is_auto_est": "0",
            "annual_benefits": [

            ],
            "annual_deductions": [

            ],
            "pay_cycle": "2",
            "pay_per_cycle_gross": "1833.33",
            "pay_per_cycle_net": "1833.33",
            "jobcontract_revision_id": "100"
          }
        ],
        "xdebug": {
          "peakMemory": 57663456,
          "memory": 57485784,
          "timeIndex": 1.65426301956
        }
      };

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(responseData);
      $httpBackend.whenGET(/action=get&entity=HRJobPay/).respond({});
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
        expect(ContractPayService.getOne).toBeDefined();
      });

      it('calls getOne functions and returns expected values', function () {
        ContractPayService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result.pay_amount).toEqual(responseData.values[0].pay_amount);
        });
      });
    });
  });
});
