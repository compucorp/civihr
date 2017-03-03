define([
  'common/angularMocks',
  'job-contract/app'
], function(Mock) {
  'use strict';

  describe('ContractPensionService', function () {
    var $httpBackend, responseData, $rootScope, ContractPensionService;

    beforeEach(module('hrjc'));

    beforeEach(inject(function (_ContractPensionService_, _$httpBackend_, _$rootScope_) {
      ContractPensionService = _ContractPensionService_;
      $httpBackend = _$httpBackend_;
      $rootScope = _$rootScope_;

      responseData = {
        "is_error": 0,
        "undefined_fields": [
          "jobcontract_revision_id"
        ],
        "version": 3,
        "count": 1,
        "id": 46,
        "values": [
          {
            "id": "46",
            "jobcontract_revision_id": "68"
          }
        ],
        "xdebug": {
          "peakMemory": 57660648,
          "memory": 57479304,
          "timeIndex": 1.44244098663
        }
      };

      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond(responseData);
      $httpBackend.whenGET(/action=get&entity=HRJobPension/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
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
        expect(ContractPensionService.getOne).toBeDefined();
      });

      it('calls getOne fuction and return expected values', function () {
        ContractPensionService.getOne({jobcontract_revision_id: 68}).then(function (result) {
          expect(result.id).toEqual(responseData.values.id);
          expect(result.jobcontract_revision_id).toEqual(responseData.values.jobcontract_revision_id);
        });
      });
    });
  });
});
