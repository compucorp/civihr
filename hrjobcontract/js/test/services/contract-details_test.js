define([
  'common/angularMocks',
  'job-contract/app'
], function(Mock) {
  'use strict';

  describe('ContractDetailsService', function () {
    var $httpBackend, responseData, $rootScope, ContractDetailsService;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_ContractDetailsService_, _$httpBackend_, _$rootScope_) {
      ContractDetailsService = _ContractDetailsService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      responseData = {
        "is_error": 0,
        "undefined_fields": [
          "jobcontract_revision_id"
        ],
        "version": 3,
        "count": 1,
        "id": 100,
        "values": [
          {
            "id": "100",
            "position": "PEASON-RED-sadfas",
            "title": "PEASON-RED-sadfas",
            "contract_type": "Contractor",
            "period_start_date": "2017-01-27",
            "period_end_date": "2017-02-28",
            "end_reason": "2",
            "notice_amount": "0",
            "notice_amount_employee": "0",
            "location": "Home",
            "jobcontract_revision_id": "100"
          }
        ],
        "xdebug": {
          "peakMemory": 57747720,
          "memory": 57570112,
          "timeIndex": 1.50076198578
        }
      };

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(responseData);
      $httpBackend.whenGET(/action=get&entity=HRJobDetails/).respond(responseData);
      $httpBackend.whenGET(/views.*/).respond(responseData);
    }));

    afterEach(function () {
      $httpBackend.flush();
      $rootScope.$apply();
    });

    describe('when calling getOne()', function () {
      it('makes http call', function () {
        $httpBackend.expectGET(/action=get&entity=HRJobContract/);
      });

      it("defines getOne() function", function () {
        expect(ContractDetailsService.getOne).toBeDefined();
      });

      it('calls getOne fuction and return expected values', function () {
        ContractDetailsService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result.position).toEqual(responseData.values[0].position);
        });
      });
    });
  });
});
